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

use SmartCat\Connector\Api\Data\ProjectSearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use SmartCat\Connector\Model\ResourceModel\Project as ResourceProject;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use SmartCat\Connector\Model\ResourceModel\Project\CollectionFactory as ProjectCollectionFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use SmartCat\Connector\Api\ProjectRepositoryInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use SmartCat\Connector\Api\Data\ProjectInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use SmartCat\Connector\Module;

class ProjectRepository implements ProjectRepositoryInterface
{
    protected $extensionAttributesJoinProcessor;
    protected $dataProjectFactory;
    protected $projectFactory;
    protected $dataObjectHelper;
    protected $projectCollectionFactory;
    protected $searchResultsFactory;
    protected $dataObjectProcessor;
    protected $resource;

    private $storeManager;
    private $collectionProcessor;


    /**
     * @param ResourceProject $resource
     * @param ProjectFactory $projectFactory
     * @param ProjectInterfaceFactory $dataProjectFactory
     * @param ProjectCollectionFactory $projectCollectionFactory
     * @param ProjectSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceProject $resource,
        ProjectFactory $projectFactory,
        ProjectInterfaceFactory $dataProjectFactory,
        ProjectCollectionFactory $projectCollectionFactory,
        ProjectSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->projectFactory = $projectFactory;
        $this->projectCollectionFactory = $projectCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProjectFactory = $dataProjectFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \SmartCat\Connector\Api\Data\ProjectInterface $project
    ) {
        /* if (empty($project->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $project->setStoreId($storeId);
        } */
        try {
            $this->resource->save($project);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the project: %1',
                $exception->getMessage()
            ));
        }
        return $project;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($projectId)
    {
        $project = $this->projectFactory->create();
        $this->resource->load($project, $projectId);
        if (!$project->getId()) {
            throw new NoSuchEntityException(__('Project with id "%1" does not exist.', $projectId));
        }
        return $project;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->projectCollectionFactory->create();

        //$condition = sprintf("%s.%s = %s.%s", Module::PROJECT_TABLE_NAME, Project::PROFILE_ID, Module::PROFILE_TABLE_NAME, Profile::PROFILE_ID);
        //$collection->join(Module::PROFILE_TABLE_NAME, $condition);
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \SmartCat\Connector\Api\Data\ProjectInterface $project
    ) {
        try {
            // TODO add removing directories of this project
            $this->resource->delete($project);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Project: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($projectId)
    {
        return $this->delete($this->getById($projectId));
    }

    /**
     * @param array $data
     * @return Project
     */
    public function create($data = [])
    {
        return $this->projectFactory->create($data);
    }
}
