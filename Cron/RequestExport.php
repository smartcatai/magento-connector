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

use SmartCat\Client\Model\ProjectModel;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\ProjectService;
use \Throwable;

class RequestExport
{
    private $smartCatService;
    private $projectService;
    private $errorHandler;
    private $projectEntityService;

    public function __construct(
        ErrorHandler $errorHandler,
        ProjectService $projectService,
        SmartCatFacade $smartCatService,
        ProjectEntityService $projectEntityService
    ) {
        $this->errorHandler = $errorHandler;
        $this->smartCatService = $smartCatService;
        $this->projectService = $projectService;
        $this->projectEntityService = $projectEntityService;
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
                $this->requestExport($smartCatProject);
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "SmartCat API Error");
                continue;
            }

            if ($smartCatProject->getDeadline()) {
                $project->setDeadline($smartCatProject->getDeadline()->format('U'));
            }

            $this->projectService->update($project);
        }
    }

    /**
     * @param ProjectModel $smartCatProject
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function requestExport(ProjectModel $smartCatProject)
    {
        foreach ($smartCatProject->getDocuments() as $document) {
            $projectEntity = $this->projectEntityService->getEntityById($document->getExternalId());

            if (in_array(
                $projectEntity->getStatus(),
                [ProjectEntity::STATUS_EXPORT, ProjectEntity::STATUS_FAILED, ProjectEntity::STATUS_SAVED]
            )) {
                continue;
            }

            $projectEntity->setStatus($document->getStatus());

            if ($document->getStatus() == ProjectEntity::STATUS_COMPLETED) {
                $export = $this->smartCatService
                    ->getDocumentExportManager()
                    ->documentExportRequestExport(['documentIds' => [$document->getId()]]);
                $projectEntity
                    ->setTaskId($export->getId())
                    ->setStatus(ProjectEntity::STATUS_EXPORT);
            }
            $this->projectEntityService->update($projectEntity);
        }
    }
}
