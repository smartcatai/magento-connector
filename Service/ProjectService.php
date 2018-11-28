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
use Magento\Framework\Exception\CouldNotSaveException;
use SmartCat\Client\Model\BilingualFileImportSettingsModel;
use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectProductRepository;
use SmartCat\Connector\Model\ProjectRepository;
use \Throwable;

class ProjectService
{
    private $fileService;
    private $projectRepository;
    private $profileRepository;
    private $projectProductRepository;
    private $errorHandler;

    private $excludedAttributes = [
        'required_options',
        'sku',
        'has_options'
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
        ProjectProductRepository $projectProductRepository,
        ErrorHandler $errorHandler
    ) {
        $this->fileService = $fileService;
        $this->projectRepository = $projectRepository;
        $this->profileRepository = $profileRepository;
        $this->projectProductRepository = $projectProductRepository;
        $this->errorHandler = $errorHandler;
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
     * @param Project $project
     * @return \SmartCat\Connector\Api\Data\ProfileInterface|Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProjectProfile(Project $project)
    {
        return $this->profileRepository->getById($project->getProfileId());
    }

    /**
     * @param Product $product
     * @param string $projectGuid
     * @param $sourceLang
     * @param array $except
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function writeAttributesToFiles(Product $product, Project $project, Profile $profile)
    {
        $exceptAttributes = array_merge($this->excludedAttributes, $profile->getExcludedAttributesArray());

        foreach ($product->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (in_array($attribute->getFrontendInput(), ['text', 'textarea']) && !in_array($attributeCode, $exceptAttributes)) {
                $data = $product->getData($attributeCode);

                if (is_array($data) || !trim($data)) {
                    continue;
                }

                $this->fileService->writeFile(
                    "{$project->getUniqueId()}/{$profile->getSourceLang()}/{$attributeCode}({$product->getSku()}).html",
                    $data
                );
            }
        }
    }

    /**
     * @param Project $project
     * @param Profile $profile
     * @return CreateDocumentPropertyWithFilesModel[]
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getProjectDocumentModels(Project $project, Profile $profile)
    {
        $documentModels = [];

        $files = $this->fileService->getDirectoryFiles(
            $this->fileService->getAbsolutePath("{$project->getUniqueId()}/{$profile->getSourceLang()}")
        );

        foreach ($files as $file) {
            $documentModels[] = $this->getDocumentModel(
                $file->getPathname(),
                $file->getFilename()
            );
        }

        return $documentModels;
    }

    /**
     * @param array $products
     * @param Project $project
     * @param Profile $profile
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function attachProducts(array $products, Project $project, Profile $profile)
    {
        foreach ($products as $product) {
            if ($product instanceof Product) {
                $this->writeAttributesToFiles($product, $project, $profile);

                $projectProduct = $this->projectProductRepository->create();
                $projectProduct
                    ->setProductId($product->getId())
                    ->setProjectId($project->getId());

                try {
                    $this->projectProductRepository->save($projectProduct);
                } catch (CouldNotSaveException $e) {
                    $this->errorHandler->logError("Could not save: " . $e->getMessage());
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
                if (strlen($name) < 80 ) {
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

        return str_replace (['*', '|', '\\', ':', '"', '<', '>', '?', '/'], ' ', $name);
    }

    /**
     * @param $filePath
     * @param $fileName
     * @return CreateDocumentPropertyWithFilesModel
     */
    private function getDocumentModel($filePath, $fileName)
    {
        $bilingualFileImportSettings = new BilingualFileImportSettingsModel();
        $bilingualFileImportSettings
            ->setConfirmMode('none')
            ->setLockMode('none')
            ->setTargetSubstitutionMode('all');

        $documentModel = new CreateDocumentPropertyWithFilesModel();
        $documentModel->setBilingualFileImportSettings($bilingualFileImportSettings);
        $documentModel->attachFile($filePath, $fileName);

        return $documentModel;
    }
}
