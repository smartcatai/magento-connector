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
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManager;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class ProductStrategy extends AbstractStrategy
{
    private $productRepository;

    private $excludedAttributes = [
        'required_options',
        'sku',
        'has_options',
        'url_key'
    ];

    /**
     * ProductStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreManager $storeManager,
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        parent::__construct($projectEntityService, $storeManager);
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
        $exceptAttributes = array_merge($this->excludedAttributes, $profile->getExcludedAttributesArray());

        foreach ($product->getAttributes() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();

            if (in_array($attribute->getFrontendInput(), ['text', 'textarea'])
                && !in_array($attributeCode, $exceptAttributes)) {
                $data = $product->getData($attributeCode);

                if (is_array($data) || !trim($data)) {
                    continue;
                }

                $this->projectEntityService->create(
                    $project,
                    $product,
                    $profile,
                    self::getType() . '|' . $attributeCode
                );
            }
        }
    }

    /**
     * @param ProjectEntity $entity
     * @return \SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        if ($entity->getEntity() != self::getType()) {
            return null;
        }

        $product = $this->productRepository->getById($entity->getEntityId());
        $data = $product->getData($entity->getAttribute());
        $fileName = "{$entity->getAttribute()}({$product->getSku()})({$entity->getLanguage()}).html";

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @param array $products
     * @return mixed|string
     */
    public function getName(array $products)
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
     * @param $content
     * @param ProjectEntity $entity
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function setContent($content, ProjectEntity $entity)
    {
        /** @var StoreInterface[] $stores */
        $stores = $this->storeManager->getStores(true, true);

        if (!isset($stores[StoreService::getStoreCode($entity->getLanguage())])) {
            throw new \Exception("StoreView with code '{$entity->getLanguage()}' not exists. Continue.");
        }

        $product = $this->productRepository->getById(
            $entity->getEntityId(),
            false,
            $stores[StoreService::getStoreCode($entity->getLanguage())]->getId()
        );
        $product->setData($entity->getAttribute(), $content);
        $this->productRepository->save($product);
    }
}
