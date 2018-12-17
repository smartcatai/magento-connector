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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Model\ProjectEntityRepository;
use SmartCat\Connector\Model\ProjectRepository;
use \Throwable;

class ProjectService
{
    private $fileService;
    private $projectRepository;
    private $profileRepository;
    private $projectEntityRepository;
    private $errorHandler;
    private $productRepository;
    private $searchCriteriaBuilder;

    private $excludedAttributes = [
        'required_options',
        'sku',
        'has_options',
        'url_key'
    ];

    /**
     * ProjectService constructor.
     * @param FileService $fileService
     * @param ProjectRepository $projectRepository
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        FileService $fileService,
        ProjectRepository $projectRepository,
        ProfileRepository $profileRepository,
        ProductRepository $productRepository,
        ProjectEntityRepository $projectEntityRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ErrorHandler $errorHandler
    ) {
        $this->fileService = $fileService;
        $this->projectRepository = $projectRepository;
        $this->profileRepository = $profileRepository;
        $this->projectEntityRepository = $projectEntityRepository;
        $this->errorHandler = $errorHandler;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param array $products
     * @param Profile $profile
     * @return Project
     * @throws SmartCatHttpException
     */
    public function create(array $products, Profile $profile)
    {
        $project = $this->projectRepository->create();
        $project
            ->setProfileId($profile->getId())
            ->setElement($this->getGeneratedProjectName($products))
            ->setTranslate($profile->getSourceLang() . ' -> ' . $profile->getTargetLang())
            ->setStatus(Project::STATUS_WAITING);

        if ($profile->getProjectGuid()) {
            $project->setGuid($profile->getProjectGuid());
        }

        try {
            $this->projectRepository->save($project);
            $this->attachProducts($products, $project, $profile);
        } catch (Throwable $e) {
            $message = $this->errorHandler->handleError($e, "Error save project to db");
            throw new SmartCatHttpException($message, $e->getCode(), $e->getPrevious());
        }

        return $project;
    }

    /**
     * @param Project $model
     * @return bool
     */
    public function update(Project $model)
    {
        try {
            if ($model->hasDataChanges()) {
                $this->projectRepository->save($model);
            }
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * @param int $projectId
     * @return ProjectEntity[]
     */
    public function getProjectEntities(int $projectId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(ProjectEntity::PROJECT_ID, $projectId)
            ->addFilter(ProjectEntity::STATUS, ProjectEntity::STATUS_NEW)
            ->create();
        $list = $this->projectEntityRepository->getList($searchCriteria)->getItems();

        return $list;
    }

    /**
     * @param \SmartCat\Connector\Api\Data\ProjectInterface|Project $project
     * @return \SmartCat\Connector\Api\Data\ProfileInterface|Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProjectProfile(Project $project)
    {
        return $this->profileRepository->getById($project->getProfileId());
    }

    /**
     * @param Project $project
     * @return CreateDocumentPropertyWithFilesModel[]
     * @throws NoSuchEntityException
     */
    public function getProjectDocumentModels(Project $project)
    {
        $documentModels = [];
        $entities = $this->getProjectEntities($project->getId());

        foreach ($entities as $entity) {
            $product = $this->productRepository->getById($entity->getEntityId());

            $type = explode('|', $entity->getType());
            $data = $product->getData($type[1]);

            $fileName = "{$type[1]}({$product->getSku()}).html";
            $file = fopen("php://temp", "r+");
            fputs($file, $data);
            rewind($file);
            $documentModels[] = $this->getDocumentModel($file, $fileName, $entity->getId());
        }

        return $documentModels;
    }

    /**
     * @param array $products
     * @param Project $project
     */
    public function attachProducts(array $products, Project $project, Profile $profile)
    {
        $exceptAttributes = array_merge($this->excludedAttributes, $profile->getExcludedAttributesArray());

        foreach ($products as $product) {
            if ($product instanceof Product) {
                foreach ($product->getAttributes() as $attribute) {
                    $attributeCode = $attribute->getAttributeCode();

                    if (in_array($attribute->getFrontendInput(), ['text', 'textarea'])
                        && !in_array($attributeCode, $exceptAttributes)) {
                        $data = $product->getData($attributeCode);

                        if (is_array($data) || !trim($data)) {
                            continue;
                        }

                        $projectProduct = $this->projectEntityRepository->create();
                        $projectProduct
                            ->setEntityId($product->getId())
                            ->setType('product|' . $attributeCode)
                            ->setStatus(ProjectEntity::STATUS_NEW)
                            ->setProjectId($project->getId());

                        try {
                            $this->projectEntityRepository->save($projectProduct);
                        } catch (CouldNotSaveException $e) {
                            $this->errorHandler->logError("Could not save: " . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $products
     * @return bool|string
     */
    private function getGeneratedProjectName(array $products)
    {
        $name = null;
        
        foreach ($products as $product) {
            if ($product instanceof Product) {
                if (strlen($name) < 80) {
                    $name .= $product->getName();
                } else {
                    break;
                }
                $name .= ', ';
            }
        }

        if (strlen($name) > 99) {
            $name = substr($name, 0, 99);
        } else {
            $name = substr($name, 0, -2);
        }

        return str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], ' ', $name);
    }

    /**
     * @param $filePath
     * @param $fileName
     * @return CreateDocumentPropertyWithFilesModel
     */
    private function getDocumentModel($filePath, $fileName, $externalId)
    {
        $bilingualFileImportSettings = new BilingualFileImportSettingsModel();
        $bilingualFileImportSettings
            ->setConfirmMode('none')
            ->setLockMode('none')
            ->setTargetSubstitutionMode('all');

        $documentModel = new CreateDocumentPropertyWithFilesModel();
        $documentModel->setBilingualFileImportSettings($bilingualFileImportSettings);
        $documentModel->setExternalId($externalId);
        $documentModel->attachFile($filePath, $fileName);

        return $documentModel;
    }
}
