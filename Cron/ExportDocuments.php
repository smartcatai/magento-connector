<?php
/**
 * SmartCat Translate Connector
 * Copyright (C) 2017 SmartCat
 *
 * This file is part of SmartCat/Connector.
 *
 * SmartCat/Connector is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace SmartCat\Connector\Cron;

use Http\Client\Common\Exception\ClientErrorException;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\ProjectService;
use SmartCat\Connector\Service\Strategy\StrategyLoader;
use \Throwable;

class ExportDocuments
{
    private $smartCatService;
    private $projectService;
    private $errorHandler;
    private $projectEntityService;
    private $strategyLoader;

    public function __construct(
        ErrorHandler $errorHandler,
        ProjectService $projectService,
        SmartCatFacade $smartCatService,
        ProjectEntityService $projectEntityService,
        StrategyLoader $strategyLoader
    ) {
        $this->errorHandler = $errorHandler;
        $this->smartCatService = $smartCatService;
        $this->projectService = $projectService;
        $this->projectEntityService = $projectEntityService;
        $this->strategyLoader = $strategyLoader;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $smartCatProject = null;
        $projectManager = $this->smartCatService->getProjectManager();
        $projects = $this->projectService->getOpenedProjects();

        /** @var Project $project */
        foreach ($projects as $project) {
            try {
                $smartCatProject = $projectManager->projectGet($project->getGuid());
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "SmartCat API Error");
                continue;
            }

            if (!$this->exportDocuments()) {
                continue;
            }

            $project
                ->setStatus($smartCatProject->getStatus());

            if ($smartCatProject->getDeadline()) {
                $project->setDeadline($smartCatProject->getDeadline()->format('U'));
            }

            $this->projectService->update($project);
        }
    }

    /**
     * @return bool
     */
    private function exportDocuments()
    {
        $entities = $this->projectEntityService->getExportingEntities();

        foreach ($entities as $entity) {
            try {
                $response = $this->smartCatService
                    ->getDocumentExportManager()
                    ->documentExportDownloadExportResult($entity->getTaskId());
            } catch (Throwable $e) {
                $status = ($e instanceof ClientErrorException)
                    ? ProjectEntity::STATUS_COMPLETED : ProjectEntity::STATUS_FAILED;
                $entity->setStatus($status);
                $this->errorHandler
                    ->logError("SmartCat API Error: An error occurred on document export " . $e->getMessage());
                $this->projectEntityService->update($entity);
                continue;
            }

            if ($response->getStatusCode() != 200) {
                continue;
            }

            $content = $response->getBody()->getContents();
            $strategy = $this->strategyLoader->getStrategyByType($entity->getEntity());

            if (!$strategy->setContent($content, $entity)) {
                continue;
            }

            $entity
                ->setStatus(ProjectEntity::STATUS_SAVED)
                ->setTaskId(null);

            $this->projectEntityService->update($entity);
        }

        return true;
    }
}
