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

namespace SmartCat\Connector\Service\Strategy;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\UrlInterface;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class CategoryStrategy extends AbstractStrategy
{
    private $categoryRepository;
    private $categoryFactory;
    private $typeTag = 'all';

    /**
     * CategoryStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param StoreService $storeService
     * @param CategoryRepository $categoryRepository
     * @param CategoryFactory $categoryFactory
     * @param UrlInterface $urlManager
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreService $storeService,
        CategoryRepository $categoryRepository,
        CategoryFactory $categoryFactory,
        UrlInterface $urlManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($projectEntityService, $storeService, $urlManager);
    }

    /**
     * @return string[]
     */
    public static function getAppliedClasses()
    {
        return [Category::class];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param Project $project
     * @param Profile $profile
     * @return void
     */
    public function attach($model, Project $project, Profile $profile)
    {
        $this->projectEntityService->create($project, $model, $profile, self::getEntityName(), $this->typeTag);
    }

    /**
     * @param ProjectEntity $entity
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        $data = [];

        $store = $this->storeService->getStoreById($entity->getSourceStore());

        /** @var Category[] $categories */
        $categories = $this->categoryFactory->create()
            ->addAttributeToSelect('*')
            ->setStore($store);

        foreach ($categories as $category) {
            if ($category->getId() == 1) {
                continue;
            }

            $data = array_merge($data, ["id_{$category->getId()}" => [
                "name" => $category->getName(),
                "description" => $category->getData('description'),
                "meta_description" => $category->getData('meta_description'),
                "meta_keywords" => $category->getData('meta_keywords'),
                "meta_title" => $category->getData('meta_title'),
            ]]);
        }

        $data = json_encode($data);
        $fileName = "({$entity->getTargetLang()})" . self::EXTENSION;

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @return string
     */
    public static function getEntityName()
    {
        return 'category';
    }

    /**
     * @param array $strings
     * @return string
     */
    public function getElementNames(array $strings)
    {
        $strings = ['Categories'];

        return parent::getElementNames($strings);
    }

    /**
     * @param $jsonContent
     * @param ProjectEntity $entity
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function setContent($jsonContent, ProjectEntity $entity): bool
    {
        $data = $this->decodeJsonParameters($jsonContent);

        foreach ($data as $id => $content) {
            $index = explode('_', $id);

            if (count($index) == 2) {
                /** @var Category $category */
                $category = $this->categoryRepository->get($index[1], $entity->getTargetStore());

                //https://github.com/magento/magento2/issues/15215
                $this->storeService->setCurrentStore($entity->getTargetStore());

                $category
                    ->setData('name', $content["name"])
                    ->setData('description', $content["description"])
                    ->setData('meta_description', $content["meta_description"])
                    ->setData('meta_title', $content["meta_title"])
                    ->setData('meta_keywords', $content["meta_keywords"]);

                $this->categoryRepository->save($category);
            }
        }

        return true;
    }

    /**
     * @param $entityId
     * @return string
     */
    public function getEntityNormalName($entityId)
    {
        return 'All categories';
    }

    /**
     * @param $entityId
     * @return string
     */
    public function getUrlToEntity($entityId)
    {
        return $this->urlManager->getUrl('catalog/category/index');
    }
}
