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

namespace SmartCat\Connector\Magento\Cron;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\DocumentModel;
use SmartCat\Client\Model\ProjectChangesModel;
use SmartCat\Connector\Magento\Helper\ErrorHandler;
use SmartCat\Connector\Magento\Helper\SmartCatFacade;
use SmartCat\Connector\Magento\Model\Profile;
use SmartCat\Connector\Magento\Model\Project;
use SmartCat\Connector\Magento\Model\ProjectRepository;
use SmartCat\Connector\Magento\Service\ProjectService;
use \Throwable;

class ProjectsSending
{
    private $smartCatService;
    private $projectService;
    private $errorHandler;
    private $searchCriteriaBuilder;
    private $projectRepository;

    /**
     * ProjectsSending constructor.
     * @param SmartCatFacade $smartCatService
     * @param ProjectService $projectService
     * @param ProjectRepository $projectRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        SmartCatFacade $smartCatService,
        ProjectService $projectService,
        ProjectRepository $projectRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ErrorHandler $errorHandler
    ) {
        $this->smartCatService = $smartCatService;
        $this->errorHandler = $errorHandler;
        $this->projectService = $projectService;
        $this->projectRepository = $projectRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $criteria = $this->searchCriteriaBuilder->addFilter(Project::STATUS, Project::STATUS_WAITING)->create();

        try {
            $projects = $this->projectRepository->getList($criteria)->getItems();
        } catch (Throwable $e) {
            $this->errorHandler->handleError($e, "SmartCat project list error");
            return;
        }

        foreach ($projects as $project) {
            try {
                $profile = $this->projectService->getProjectProfile($project);

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
    private function createProject(Project $project, Profile $profile) {
        // Create and send project model to smartcat api
        $projectManager = $this->smartCatService->getProjectManager();

        $newProjectModel = (new CreateProjectModel())
            ->setName($project->getElement())
            ->setDescription('Magento SmartCat Connector. Project: ' . $project->getUniqueId())
            ->setSourceLanguage($profile->getSourceLang())
            ->setTargetLanguages($profile->getTargetLangArray())
            ->setWorkflowStages($profile->getStagesArray())
            ->setExternalTag('source:Magento')
            ->setAssignToVendor(false);

        try {
            $projectModel = $projectManager->projectCreateProject($newProjectModel);
            $projectManager->projectAddDocument([
                'projectId' => $projectModel->getId(),
                'documentModel' => $this->projectService->getProjectDocumentModels($project, $profile)
            ]);
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
                ->setVendorAccountId($profile->getVendor());
            try {
                $projectManager->projectUpdateProject($projectModel->getId(), $projectChanges);
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
    private function updateProject(Project $project, Profile $profile) {
        $projectManager = $this->smartCatService->getProjectManager();
        $documentManager = $this->smartCatService->getDocumentManager();

        try {
            $projectModel = $projectManager->projectGet($profile->getProjectGuid());
            $smartCatDocuments = $projectModel->getDocuments();
            $projectDocuments = $this->projectService->getProjectDocumentModels($project, $profile);

            $smartCatNameDocuments = array_map(function (DocumentModel $value) {
                return $value->getName();
            }, $smartCatDocuments);

            foreach ($projectDocuments as $projectDocument) {
                $index = array_search(str_replace('.html', '', $projectDocument->getFile()['fileName']), $smartCatNameDocuments);
                if ($index !== false) {
                    $this->waitingCompleteDocumentStatus($smartCatDocuments[$index]->getId());
                    $documentManager->documentUpdate([
                        'documentId' => $smartCatDocuments[$index]->getId(),
                        'uploadedFile' => $projectDocument->getFile()
                    ]);
                } else {
                    $projectManager->projectAddDocument([
                        'projectId' => $projectModel->getId(),
                        'documentModel' => [$projectDocument]
                    ]);
                }
            }
        } catch (Throwable $e) {
            $this->errorHandler->handleProjectError($e, $project, "SmartCat update project error");
            return;
        }

        $project
            ->setStatus($projectModel->getStatus());

        if ($projectModel->getDeadline()) {
            $project->setDeadline($projectModel->getDeadline()->format('U'));
        }

        $this->projectService->update($project);
    }

    /**
     * @param $documentId
     * @param int $attempt
     * @return bool
     * @throws LocalizedException
     */
    private function waitingCompleteDocumentStatus($documentId, $attempt = 1)
    {
        if ($attempt > 120) {
            throw new LocalizedException(__('120 attempts to get document failed'));
        }

        $document = $this->smartCatService->getDocumentManager()->documentGet(['documentId' => $documentId]);

        if ($document->getDocumentDisassemblingStatus() != "success") {
            sleep(1);
            return $this->waitingCompleteDocumentStatus($documentId, $attempt++);
        }

        return true;
    }
}
