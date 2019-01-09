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

namespace SmartCat\Connector\Controller\Adminhtml\Localize;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use SmartCat\Connector\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use SmartCat\Connector\Service\ProjectService;
use Magento\Ui\Component\MassAction\Filter;

class Product extends AbstractController
{
    private $productRepository;
    private $productCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProjectService $projectService
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ProfileRepositoryInterface|null $profileRepository
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProjectService $projectService,
        ProductCollectionFactory $productCollectionFactory,
        ProfileRepositoryInterface $profileRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $filter, $projectService, $profileRepository);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getModels()
    {
        $models = [];
        $productsCollection = $this->filter->getCollection($this->productCollectionFactory->create());

        foreach ($productsCollection as $productFromCollection) {
            $models[] = $this->productRepository->getById($productFromCollection->getId(), false, 1);
        }

        return $models;
    }

    /**
     * @return string
     */
    public function getRedirectPath()
    {
        return "catalog/product/index";
    }
}
