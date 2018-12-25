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
use SmartCat\Connector\Api\Data\ProjectEntitySearchResultsFactory;
use SmartCat\Connector\Model\ResourceModel\ProjectEntity\CollectionFactory as ProjectEntityCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use SmartCat\Connector\Model\ResourceModel\ProjectEntity as ResourceProjectEntity;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;

class ProjectEntityRepository
{
    private $extensionAttributesJoinProcessor;
    private $projectEntityCollectionFactory;
    private $dataObjectHelper;
    private $searchResultsFactory;
    private $dataObjectProcessor;
    private $resource;
    private $projectProductFactory;

    private $collectionProcessor;

    /**
     * @param ResourceProjectEntity $resource
     * @param ProjectEntityFactory $projectProductFactory
     * @param ProjectEntityCollectionFactory $projectProductCollectionFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceProjectEntity $resource,
        ProjectEntityFactory $projectProductFactory,
        ProjectEntityCollectionFactory $projectProductCollectionFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ProjectEntitySearchResultsFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->projectProductFactory = $projectProductFactory;
        $this->projectEntityCollectionFactory = $projectProductCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param ProjectEntity $projectProduct
     * @return ProjectEntity
     * @throws CouldNotSaveException
     */
    public function save(ProjectEntity $projectProduct)
    {
        try {
            $this->resource->save($projectProduct);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ProjectEntity: %1',
                $exception->getMessage()
            ));
        }
        return $projectProduct;
    }

    /**
     * @param $projectProductId
     * @return ProjectEntity
     * @throws NoSuchEntityException
     */
    public function getById($projectProductId)
    {
        $projectProduct = $this->projectProductFactory->create();
        $this->resource->load($projectProduct, $projectProductId);
        if (!$projectProduct->getId()) {
            throw new NoSuchEntityException(__('ProjectEntity with id "%1" does not exist.', $projectProductId));
        }
        return $projectProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->projectEntityCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @param $type
     * @param $entityId
     * @param $status
     * @return \SmartCat\Connector\Model\ResourceModel\ProjectEntity|null
     */
    public function getItemByTypeIdStatus($type, $entityId, $status)
    {
        $collection = $this->projectEntityCollectionFactory->create();
        $collection
            ->addFilter(ProjectEntity::TYPE, $type)
            ->addFilter(ProjectEntity::STATUS, $status)
            ->addFilter(ProjectEntity::ENTITY_ID, $entityId);

        $item = $collection->setCurPage(1)->setPageSize(1)->getItems();

        return $item[0] ?? null;
    }

    /**
     * @param ProjectEntity $projectProduct
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ProjectEntity $projectProduct)
    {
        try {
            $this->resource->delete($projectProduct);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the ProjectEntity: %1',
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
     * @return ProjectEntity
     */
    public function create($data = [])
    {
        return $this->projectProductFactory->create($data);
    }
}
