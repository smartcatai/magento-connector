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

use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Service\ProjectService;

class BuildStatistics
{
    private $smartCatService;
    private $projectService;
    private $errorHandler;

    public function __construct(
        ErrorHandler $errorHandler,
        ProjectService $projectService,
        SmartCatFacade $smartCatService
    ) {
        $this->errorHandler = $errorHandler;
        $this->smartCatService = $smartCatService;
        $this->projectService = $projectService;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->smartCatService->checkCredentials()) {
            return;
        }

        $projectManager = $this->smartCatService->getProjectManager();
        $smartCatProject = null;

        $projects = $this->projectService->getNotBuildedProjects();

        /** @var Project $project */
        foreach ($projects as $project) {
            try {
                $smartCatProject = $projectManager->projectGet($project->getGuid());
            } catch (\Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "Smartcat API Error");
            }

            if (!$smartCatProject) {
                continue;
            }

            if (!in_array($smartCatProject->getStatus(), [Project::STATUS_IN_PROGRESS, Project::STATUS_CREATED])) {
                $project->setIsStatisticsBuilded(true);
            } else {
                $documents = $smartCatProject->getDocuments();
                $completedDocuments = 0;

                foreach ($documents as $document) {
                    if ($document->getDocumentDisassemblingStatus() == 'success') {
                        $completedDocuments++;
                    }
                }

                // Build project statistics
                if (count($documents) == $completedDocuments && $completedDocuments > 0) {
                    try {
                        $projectManager->projectBuildStatistics($smartCatProject->getId());
                        $project->setIsStatisticsBuilded(true);
                    } catch (\Throwable $e) {
                        $this->errorHandler->handleProjectError($e, $project, "Smartcat API Error");
                    }
                }
            }

            $this->projectService->update($project);
        }
    }
}
