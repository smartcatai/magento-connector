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
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Module;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\ProjectService;
use SmartCat\Connector\Service\SmartCatService;
use \Throwable;

class SendProjects
{
    private $smartCatService;
    private $projectService;
    private $errorHandler;
    private $projectEntityService;
    private $profileService;

    public function __construct(
        SmartCatService $smartCatService,
        ProjectService $projectService,
        ProfileService $profileService,
        ErrorHandler $errorHandler,
        ProjectEntityService $projectEntityService
    ) {
        $this->smartCatService = $smartCatService;
        $this->errorHandler = $errorHandler;
        $this->projectService = $projectService;
        $this->profileService = $profileService;
        $this->projectEntityService = $projectEntityService;
    }

    public function execute()
    {
        if (!$this->smartCatService->checkCredentials()) {
            return;
        }

        $projects = $this->projectService->getWaitingProjects();

        foreach ($projects as $project) {
            try {
                $profile = $this->profileService->getProfileByProject($project);

                if ($profile->getProjectGuid()) {
                    $this->updateProject($project, $profile);
                } else {
                    $this->createProject($project, $profile);
                }
            } catch (Throwable $e) {
                $this->errorHandler->handleError($e, "SmartCat sending project error");
                continue;
            }
        }
    }

    /**
     * @param Project $project
     * @param Profile $profile
     */
    private function createProject(Project $project, Profile $profile)
    {
        try {
            $projectModel = $this->smartCatService->createProject($project, $profile);
            $this->smartCatService->addDocuments($projectModel, $project);
        } catch (Throwable $e) {
            $this->errorHandler->handleProjectError($e, $project, "SmartCat create project error");
            return;
        }

        $project
            ->setGuid($projectModel->getId())
            ->setStatus($projectModel->getStatus());

        if ($projectModel->getDeadline()) {
            $project->setDeadline($projectModel->getDeadline()->format('U'));
        }

        $this->projectService->update($project);

        // If Vendor ID exists - update project and set vendor
        if ($profile->getVendor()) {
            try {
                $this->smartCatService->updateProject($projectModel, Module::EXTERNAL_TAG, $profile->getVendor());
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "SmartCat error update project to vendor");
                return;
            }
        }
    }

    /**
     * @param Project $project
     * @param Profile $profile
     */
    private function updateProject(Project $project, Profile $profile)
    {
        try {
            $projectModel = $this->smartCatService->getProject($profile->getProjectGuid());
            $projectDocuments = $this->projectService->getProjectDocumentModels($project);

            $this->smartCatService->updateDocuments(
                $projectModel->getDocuments(),
                $projectDocuments,
                $projectModel->getId()
            );
        } catch (Throwable $e) {
            $this->errorHandler->handleProjectError($e, $project, "SmartCat update project error");
            return;
        }

        if (empty($projectDocuments)) {
            $project->setStatus($projectModel->getStatus());
        }

        if ($projectModel->getDeadline()) {
            $project->setDeadline($projectModel->getDeadline()->format('U'));
        }

        if ($projectModel->getExternalTag() != Module::EXTERNAL_TAG) {
            try {
                $this->smartCatService->updateProject($projectModel, Module::EXTERNAL_TAG);
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "SmartCat error update project external tag");
                return;
            }
        }

        $this->projectService->update($project);
    }
}
