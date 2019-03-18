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
    private $parametersTag = 'all';

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
        $this->projectEntityService->create(
            $project,
            $model,
            $profile,
            self::getType() . '|' . $this->parametersTag
        );
    }

    /**
     * @param ProjectEntity $entity
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        $data = [];

        /** @var Category[] $categories */
        $categories = $this->categoryFactory->create()
            ->addAttributeToSelect('*')
            ->setProductStoreId(0);

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
        $fileName = "({$entity->getTargetLang()}).json";

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'category';
    }

    /**
     * @param $jsonContent
     * @param ProjectEntity $entity
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function setContent($jsonContent, ProjectEntity $entity): bool
    {
        $storeID = $this->storeService->getStoreIdByCode($entity->getTargetLang());

        if ($storeID === null) {
            return false;
        }

        $data = $this->decodeJsonParameters($jsonContent);

        foreach ($data as $id => $content) {
            $index = explode('_', $id);

            if (count($index) == 2) {
                /** @var Category $category */
                $category = $this->categoryRepository->get($index[1], $storeID);

                $category
                    ->setData('name', $content["name"])
                    ->setData('description', $content["description"])
                    ->setData('meta_description', $content["meta_description"])
                    ->setData('meta_title', $content["meta_title"])
                    ->setData('meta_keywords', $content["meta_keywords"]);

                $category->save();
            }
        }

        return true;
    }

    /**
     * @param $entityId
     * @return string
     */
    public function getEntityName($entityId)
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
