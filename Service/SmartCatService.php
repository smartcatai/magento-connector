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

namespace SmartCat\Connector\Service;

use Http\Client\Common\Exception\ClientErrorException;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Client\Model\CreateProjectModel;
use SmartCat\Client\Model\DocumentModel;
use SmartCat\Client\Model\ProjectChangesModel;
use SmartCat\Client\Model\ProjectModel;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Module;

class SmartCatService
{
    private $smartCat;
    private $projectService;
    private $projectEntityService;

    public function __construct(
        SmartCatFacade $smartCat,
        ProjectService $projectService,
        ProjectEntityService $projectEntityService
    ) {
        $this->smartCat = $smartCat;
        $this->projectEntityService = $projectEntityService;
        $this->projectService = $projectService;
    }

    /**
     * @param Project $project
     * @param Profile $profile
     * @return ProjectModel
     * @throws \Exception
     */
    public function createProject(Project $project, Profile $profile)
    {
        $newProjectModel = (new CreateProjectModel())
            ->setName($project->getElement())
            ->setDescription('Magento SmartCat Connector. Product: ' . $project->getUniqueId())
            ->setSourceLanguage($profile->getSourceLang())
            ->setTargetLanguages($profile->getTargetLangArray())
            ->setWorkflowStages($profile->getStagesArray())
            ->setExternalTag(Module::EXTERNAL_TAG)
            ->setAssignToVendor(false);

        $projectModel = $this->smartCat->getProjectManager()->projectCreateProject($newProjectModel);

        return $projectModel;
    }

    /**
     * @param ProjectModel $projectModel
     * @param Project $project
     */
    public function addDocuments(ProjectModel $projectModel, Project $project)
    {
        $smartcatDocuments = $this->smartCat->getProjectManager()->projectAddDocument([
            'projectId' => $projectModel->getId(),
            'documentModel' => $this->projectService->getProjectDocumentModels($project)
        ]);

        foreach ($smartcatDocuments as $smartcatDocument) {
            $projectEntity = $this->projectEntityService->getEntityById($smartcatDocument->getExternalId());

            if (!$projectEntity) {
                continue;
            }

            $projectEntity
                ->setStatus($smartcatDocument->getStatus())
                ->setDocumentId($smartcatDocument->getId());
            $this->projectEntityService->update($projectEntity);
        }
    }

    /**
     * @param ProjectModel $projectModel
     * @param $externalTag
     * @param $vendorId
     */
    public function updateProject(ProjectModel $projectModel, $externalTag, $vendorId = null)
    {
        $projectChanges = (new ProjectChangesModel())
            ->setName($projectModel->getName())
            ->setDescription($projectModel->getDescription())
            ->setExternalTag($externalTag);

        if ($vendorId) {
            $projectChanges->setVendorAccountIds([$vendorId]);
        }

        $this->smartCat->getProjectManager()->projectUpdateProject($projectModel->getId(), $projectChanges);
    }

    /**
     * @param $smartCatDocuments DocumentModel[]
     * @return array
     */
    private function getProjectDocumentsExternalIds($smartCatDocuments)
    {
        $smartCatNameDocuments = array_map(function (DocumentModel $value) {
            return $value->getExternalId();
        }, $smartCatDocuments);

        return $smartCatNameDocuments;
    }

    /**
     * @param $guid
     * @return ProjectModel
     */
    public function getProject($guid)
    {
        return $this->smartCat->getProjectManager()->projectGet($guid);
    }

    /**
     * @param DocumentModel[] $smartCatDocuments
     * @param CreateDocumentPropertyWithFilesModel[] $projectDocuments
     * @param $projectId string
     */
    public function updateDocuments($smartCatDocuments, $projectDocuments, $projectId)
    {
        $smartCatDocumentNames = $this->getProjectDocumentsExternalIds($smartCatDocuments);

        foreach ($projectDocuments as $projectDocument) {
            $index = array_search($projectDocument->getExternalId(), $smartCatDocumentNames);
            $entity = $this->projectEntityService->getEntityById($projectDocument->getExternalId());

            if (!$entity) {
                continue;
            }

            try {
                if ($index !== false) {
                    /** @var DocumentModel $resDocument */
                    $resDocument = $this->smartCat->getDocumentManager()->documentUpdate([
                        'documentId' => $smartCatDocuments[$index]->getId(),
                        'uploadedFile' => $projectDocument->getFile()
                    ]);
                } else {
                    $resDocument = $this->smartCat->getProjectManager()->projectAddDocument([
                        'projectId' => $projectId,
                        'documentModel' => [$projectDocument]
                    ]);
                }

                $entity
                    ->setStatus($resDocument->getStatus())
                    ->setDocumentId($resDocument->getId());
            } catch (ClientErrorException $e) {
                continue;
            }

            $this->projectEntityService->update($entity);
        }
    }

    /**
     * @return bool
     */
    public function checkCredentials(): bool
    {
        return $this->smartCat->checkCredentials();
    }
}
