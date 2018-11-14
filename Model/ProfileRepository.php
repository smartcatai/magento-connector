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
use SmartCat\Connector\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use SmartCat\Connector\Api\ProfileRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;
use SmartCat\Connector\Api\Data\ProfileSearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use SmartCat\Connector\Model\ResourceModel\Profile as ResourceProfile;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\DataObjectHelper;
use SmartCat\Connector\Api\Data\ProfileInterfaceFactory;

class ProfileRepository implements ProfileRepositoryInterface
{

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    protected $profileCollectionFactory;

    protected $dataObjectHelper;

    protected $dataProfileFactory;

    protected $searchResultsFactory;

    private $collectionProcessor;
    protected $dataObjectProcessor;

    protected $resource;

    protected $profileFactory;


    /**
     * @param ResourceProfile $resource
     * @param ProfileFactory $profileFactory
     * @param ProfileInterfaceFactory $dataProfileFactory
     * @param ProfileCollectionFactory $profileCollectionFactory
     * @param ProfileSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     */
    public function __construct(
        ResourceProfile $resource,
        ProfileFactory $profileFactory,
        ProfileInterfaceFactory $dataProfileFactory,
        ProfileCollectionFactory $profileCollectionFactory,
        ProfileSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor
    ) {
        $this->resource = $resource;
        $this->profileFactory = $profileFactory;
        $this->profileCollectionFactory = $profileCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataProfileFactory = $dataProfileFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \SmartCat\Connector\Api\Data\ProfileInterface $profile
    ) {
        /* if (empty($profile->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $profile->setStoreId($storeId);
        } */
        try {
            $this->resource->save($profile);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the profile: %1',
                $exception->getMessage()
            ));
        }
        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($profileId)
    {
        $profile = $this->profileFactory->create();
        $this->resource->load($profile, $profileId);
        if (!$profile->getId()) {
            throw new NoSuchEntityException(__('Profile with id "%1" does not exist.', $profileId));
        }
        return $profile;
    }

    public function getModelById($profileId)
    {
        $profile = $this->profileFactory->create();
        $this->resource->load($profile, $profileId);

        return $profile;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->profileCollectionFactory->create();
        
        //$this->extensionAttributesJoinProcessor->process(
        //    $collection,
        //    \SmartCat\Connector\Api\Data\ProfileInterface::class
        //);
        
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
        \SmartCat\Connector\Api\Data\ProfileInterface $profile
    ) {
        try {
            $this->resource->delete($profile);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Profile: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($profileId)
    {
        return $this->delete($this->getById($profileId));
    }

    /**
     * @param array $data
     * @return Profile
     */
    public function create($data = [])
    {
        return $this->profileFactory->create($data);
    }
}
