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

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class ProductStrategy extends AbstractStrategy
{
    private $productRepository;
    private $profileService;
    private $parametersTag = 'parameters';

    private $excludedAttributes = [
        'required_options',
        'sku',
        'has_options',
        'url_key'
    ];

    /**
     * ProductStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param StoreService $storeService
     * @param ProfileService $profileService
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreService $storeService,
        ProfileService $profileService,
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->profileService = $profileService;
        parent::__construct($projectEntityService, $storeService);
    }

    /**
     * @return array|string[]
     */
    public static function getAppliedClasses()
    {
        return [Product::class, Product\Interceptor::class];
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'product';
    }

    /**
     * @param Product $product
     * @param Project $project
     * @param Profile $profile
     * @return void
     */
    public function attach($product, Project $project, Profile $profile)
    {
        $this->projectEntityService->create(
            $project,
            $product,
            $profile,
            self::getType() . '|' . $this->parametersTag
        );
    }

    /**
     * @param ProjectEntity $entity
     * @return \SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        if ($entity->getEntity() != self::getType()) {
            return null;
        }

        $attributes = [];

        /** @var Product $product */
        $product = $this->productRepository->getById($entity->getEntityId());
        $profile = $this->profileService->getProfileByProjectId($entity->getProjectId());

        $exceptAttributes = array_merge($this->excludedAttributes, $profile->getExcludedAttributesArray());

        foreach ($product->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (in_array($attribute->getFrontendInput(), ['text', 'textarea'])
                && !in_array($attributeCode, $exceptAttributes)) {
                $data = $product->getData($attributeCode);

                if (is_array($data) || !trim($data)) {
                    continue;
                }

                $attributes = array_merge($attributes, [$attributeCode => $data]);
            }
        }

        $data = json_encode($attributes);
        $fileName = "{$product->getSku()}({$entity->getLanguage()}).json";

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @param Product[] $products
     * @return mixed|string
     */
    public function getName(array $products)
    {
        $names = [];

        foreach ($products as $product) {
            if ($product instanceof Product) {
                $names[] = $product->getName();
            }
        }

        return parent::getName($names);
    }

    /**
     * @param string $content
     * @param ProjectEntity $entity
     * @return bool
     * @throws NoSuchEntityException
     */
    public function setContent($content, ProjectEntity $entity): bool
    {
        $storeID = $this->storeService->getStoreIdByCode($entity->getLanguage());

        if ($storeID === null) {
            return false;
        }

        if ($entity->getAttribute() == $this->parametersTag) {
            $attributes = $this->decodeJsonParameters($content);

            /** @var Product $product */
            $product = $this->productRepository->getById($entity->getEntityId());

            foreach ($attributes as $attributeCode => $attributeContent) {
                $product->addAttributeUpdate($attributeCode, $attributeContent, $storeID);
            }

            return true;
        }

        return false;
    }
}
