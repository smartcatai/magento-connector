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

use Magento\Eav\Model\Attribute;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute\FrontendLabelFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class AttributesStrategy extends AbstractStrategy
{
    private $attributeRepository;
    private $searchCriteriaBuilder;
    private $attributeFrontendLabelFactory;
    private $parametersTag = 'all';

    /**
     * AttributesStrategy constructor.
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param ProjectEntityService $projectEntityService
     * @param FrontendLabelFactory $attributeFrontendLabelFactory
     * @param StoreService $storeService
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ProjectEntityService $projectEntityService,
        FrontendLabelFactory $attributeFrontendLabelFactory,
        StoreService $storeService
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeFrontendLabelFactory = $attributeFrontendLabelFactory;
        parent::__construct($projectEntityService, $storeService);
    }

    /**
     * @return string[]
     */
    public static function getAppliedClasses()
    {
        return [Attribute::class];
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
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        $data = [];
        $searchCriteria = $this->searchCriteriaBuilder->create();

        /** @var Attribute[] $attributesList */
        $attributesList = $this->attributeRepository
            ->getList('catalog_product', $searchCriteria)
            ->getItems();

        foreach ($attributesList as $attribute) {
            $data = array_merge($data, [$attribute->getName() => $attribute->getStoreLabel(0)]);
        }

        $data = json_encode($data);
        $fileName = "({$entity->getLanguage()}).json";

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'attributes';
    }

    /**
     * @param string $content
     * @param ProjectEntity $entity
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function setContent($content, ProjectEntity $entity): bool
    {
        $storeID = $this->storeService->getStoreIdByCode($entity->getLanguage());

        if ($storeID === null) {
            return false;
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();

        /** @var Attribute[] $attributesList */
        $attributesList = $this->attributeRepository
            ->getList('catalog_product', $searchCriteria)
            ->getItems();

        $attributeNames = array_map(function (Attribute $attribute) {
            return $attribute->getName();
        }, $attributesList);

        $data = $this->decodeJsonParameters($content);

        foreach ($data as $name => $label) {
            $index = array_search($name, $attributeNames);

            if ($index !== false) {
                $attributesList[$index]->setFrontendLabels([
                    $this->attributeFrontendLabelFactory->create()
                        ->setStoreId($storeID)
                        ->setLabel($label)
                ]);

                $this->attributeRepository->save($attributesList[$index]);
            }
        }
    }
}