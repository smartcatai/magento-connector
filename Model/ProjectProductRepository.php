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

namespace SmartCat\Connector\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use SmartCat\Connector\Api\Data\ProjectProductSearchResultsFactory;
use SmartCat\Connector\Model\ResourceModel\ProjectProduct\CollectionFactory as ProjectProductCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use SmartCat\Connector\Model\ResourceModel\ProjectProduct as ResourceProjectProduct;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

class ProjectProductRepository
{
    protected $extensionAttributesJoinProcessor;
    protected $projectProductCollectionFactory;
    protected $dataObjectHelper;
    protected $dataProjectProductFactory;
    protected $searchResultsFactory;
    protected $dataObjectProcessor;
    protected $resource;
    protected $projectProductFactory;

    private $storeManager;
    private $collectionProcessor;

    /**
     * @param ResourceProjectProduct $resource
     * @param ProjectProductFactory $projectProductFactory
     * @param ProjectProductCollectionFactory $projectProductCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceProjectProduct $resource,
        ProjectProductFactory $projectProductFactory,
        ProjectProductCollectionFactory $projectProductCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ProjectProductSearchResultsFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->projectProductFactory = $projectProductFactory;
        $this->projectProductCollectionFactory = $projectProductCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param ProjectProduct $projectProduct
     * @return ProjectProduct
     * @throws CouldNotSaveException
     */
    public function save(ProjectProduct $projectProduct)
    {
        try {
            $this->resource->save($projectProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ProjectProduct: %1',
                $exception->getMessage()
            ));
        }
        return $projectProduct;
    }

    /**
     * @param $projectProductId
     * @return ProjectProduct
     * @throws NoSuchEntityException
     */
    public function getById($projectProductId)
    {
        $projectProduct = $this->projectProductFactory->create();
        $this->resource->load($projectProduct, $projectProductId);
        if (!$projectProduct->getId()) {
            throw new NoSuchEntityException(__('ProjectProduct with id "%1" does not exist.', $projectProductId));
        }
        return $projectProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->projectProductCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param ProjectProduct $projectProduct
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProjectProduct $projectProduct)
    {
        try {
            $this->resource->delete($projectProduct);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ProjectProduct: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @param $projectProductId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($projectProductId)
    {
        return $this->delete($this->getById($projectProductId));
    }

    /**
     * @param array $data
     * @return ProjectProduct
     */
    public function create($data = [])
    {
        return $this->projectProductFactory->create($data);
    }
}
