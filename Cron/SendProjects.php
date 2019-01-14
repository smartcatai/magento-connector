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
use Magento\Framework\Api\SearchCriteriaBuilder;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\DocumentModel;
use SmartCat\Client\Model\ProjectChangesModel;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\ProjectService;
use \Throwable;

class SendProjects
{
    private $smartCatService;
    private $projectService;
    private $errorHandler;
    private $searchCriteriaBuilder;
    private $projectEntityService;
    private $profileService;

    public function __construct(
        SmartCatFacade $smartCatService,
        ProjectService $projectService,
        ProfileService $profileService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ErrorHandler $errorHandler,
        ProjectEntityService $projectEntityService
    ) {
        $this->smartCatService = $smartCatService;
        $this->errorHandler = $errorHandler;
        $this->projectService = $projectService;
        $this->profileService = $profileService;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->projectEntityService = $projectEntityService;
    }

    public function execute()
    {
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
     * @param \SmartCat\Connector\Api\Data\ProjectInterface|Project $project
     * @param Profile $profile
     */
    private function createProject(Project $project, Profile $profile)
    {
        // Create and send project model to smartcat api
        $projectManager = $this->smartCatService->getProjectManager();

        $newProjectModel = (new CreateProjectModel())
            ->setName($project->getElement())
            ->setDescription('Magento SmartCat Connector. Product: ' . $project->getUniqueId())
            ->setSourceLanguage($profile->getSourceLang())
            ->setTargetLanguages($profile->getTargetLangArray())
            ->setWorkflowStages($profile->getStagesArray())
            ->setExternalTag('source:Magento')
            ->setAssignToVendor(false);

        try {
            $projectModel = $projectManager->projectCreateProject($newProjectModel);
            $smartcatDocuments = $projectManager->projectAddDocument([
                'projectId' => $projectModel->getId(),
                'documentModel' => $this->projectService->getProjectDocumentModels($project)
            ]);

            foreach ($smartcatDocuments as $smartcatDocument) {
                $projectEntity = $this->projectEntityService->getEntityById($smartcatDocument->getExternalId());
                $projectEntity
                    ->setStatus($smartcatDocument->getStatus())
                    ->setDocumentId($smartcatDocument->getId());
                $this->projectEntityService->update($projectEntity);
            }
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
            $projectChanges = (new ProjectChangesModel())
                ->setName($projectModel->getName())
                ->setDescription($projectModel->getDescription())
                ->setVendorAccountIds([$profile->getVendor()]);
            try {
                $projectManager->projectUpdateProject($projectModel->getId(), $projectChanges);
            } catch (Throwable $e) {
                $this->errorHandler->handleProjectError($e, $project, "SmartCat error update project to vendor");
                return;
            }
        }
    }

    /**
     * @param \SmartCat\Connector\Api\Data\ProjectInterface|Project $project
     * @param Profile $profile
     */
    private function updateProject(Project $project, Profile $profile)
    {
        $projectManager = $this->smartCatService->getProjectManager();
        $documentManager = $this->smartCatService->getDocumentManager();

        try {
            $projectModel = $projectManager->projectGet($profile->getProjectGuid());
            $smartCatDocuments = $projectModel->getDocuments();
            $projectDocuments = $this->projectService->getProjectDocumentModels($project);

            $smartCatNameDocuments = array_map(function (DocumentModel $value) {
                return $value->getName();
            }, $smartCatDocuments);
        } catch (Throwable $e) {
            $this->errorHandler->handleProjectError($e, $project, "SmartCat update project error");
            return;
        }

        foreach ($projectDocuments as $projectDocument) {
            $index = array_search($projectDocument->getFile()['fileName'], $smartCatNameDocuments);

            try {
                $entity = $this->projectEntityService->getEntityById($projectDocument->getExternalId());
            } catch (Throwable $e) {
                $this->errorHandler->logError("SmartCat update project error: {$e->getMessage()}");
                continue;
            }

            try {
                if ($index !== false) {
                    /** @var DocumentModel $resDocument */
                    $resDocument = $documentManager->documentUpdate([
                        'documentId' => $smartCatDocuments[$index]->getId(),
                        'uploadedFile' => $projectDocument->getFile()
                    ]);
                } else {
                    $resDocument = $projectManager->projectAddDocument([
                        'projectId' => $projectModel->getId(),
                        'documentModel' => [$projectDocument]
                    ]);
                }

                $project->setIsStatisticsBuilded(false);
                $entity
                    ->setStatus($resDocument->getStatus())
                    ->setDocumentId($resDocument->getId());
            } catch (Throwable $e) {
                $this->errorHandler->logError("SmartCat update project error: {$e->getMessage()}");

                if ($e instanceof ClientErrorException) {
                    continue;
                }

                $entity->setStatus(ProjectEntity::STATUS_FAILED);
            }
            $this->projectEntityService->update($entity);
        }

        $projectDocuments = $this->projectService->getProjectDocumentModels($project);

        if (empty($projectDocuments)) {
            $project->setStatus($projectModel->getStatus());
        }

        if ($projectModel->getDeadline()) {
            $project->setDeadline($projectModel->getDeadline()->format('U'));
        }

        $this->projectService->update($project);
    }
}