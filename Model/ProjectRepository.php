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
use SmartCat\Connector\Api\ProjectRepositoryInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use SmartCat\Connector\Api\Data\ProjectInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;

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

    private $collectionProcessor;
    private $filterBuilder;
    private $filterGroupBuilder;
    private $searchCriteriaBuilder;

    /**
     * @param ResourceProject $resource
     * @param ProjectFactory $projectFactory
     * @param ProjectInterfaceFactory $dataProjectFactory
     * @param ProjectCollectionFactory $projectCollectionFactory
     * @param ProjectSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DataObjectProcessor $dataObjectProcessor
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
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DataObjectProcessor $dataObjectProcessor,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->projectFactory = $projectFactory;
        $this->projectCollectionFactory = $projectCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dataProjectFactory = $dataProjectFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \SmartCat\Connector\Api\Data\ProjectInterface $project
    ) {
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

    /**
     * @return array|Project[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOpenedProjects()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(Project::STATUS, [Project::STATUS_CREATED, Project::STATUS_IN_PROGRESS], "in")
            ->create();

        $projects = $this->getList($searchCriteria)->getItems();


        return $projects;
    }

    /**
     * @return array|Project[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNotBuildedProjects()
    {
        $nullGroup = $this->filterBuilder
            ->setField(Project::IS_STATS_BUILDED)
            ->setConditionType('null')
            ->create();

        $falseGroup = $this->filterBuilder
            ->setField(Project::IS_STATS_BUILDED)
            ->setConditionType('eq')
            ->setValue(0)
            ->create();

        $filterOr = $this->filterGroupBuilder
            ->addFilter($falseGroup)
            ->addFilter($nullGroup)
            ->create();

        $inGroup = $this->filterBuilder
            ->setField(Project::STATUS)
            ->setConditionType('in')
            ->setValue([Project::STATUS_IN_PROGRESS, Project::STATUS_COMPLETED, Project::STATUS_CREATED])
            ->create();

        $filterOr2 = $this->filterGroupBuilder
            ->addFilter($inGroup)
            ->create();

        $searchCriteria = $this->searchCriteriaBuilder->setFilterGroups([$filterOr, $filterOr2])->create();
        $projects = $this->getList($searchCriteria)->getItems();

        return $projects;
    }

    /**
     * @return array|Project[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getWaitingProjects()
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(Project::STATUS, Project::STATUS_WAITING)
            ->create();

        $projects = $this->getList($searchCriteria)->getItems();

        return $projects;
    }
}
