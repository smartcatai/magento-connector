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

use Magento\Cms\Api\Data;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\ResourceModel\Block as ResourceBlock;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class BlockRepository extends \Magento\Cms\Model\BlockRepository
{
    private $searchCriteriaBuilder;

    /**
     * BlockRepository constructor.
     * @param ResourceBlock $resource
     * @param BlockFactory $blockFactory
     * @param Data\BlockInterfaceFactory $dataBlockFactory
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param Data\BlockSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ResourceBlock $resource,
        BlockFactory $blockFactory,
        Data\BlockInterfaceFactory $dataBlockFactory,
        BlockCollectionFactory $blockCollectionFactory,
        Data\BlockSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct(
            $resource,
            $blockFactory,
            $dataBlockFactory,
            $blockCollectionFactory,
            $searchResultsFactory,
            $dataObjectHelper,
            $dataObjectProcessor,
            $storeManager,
            $collectionProcessor
        );
    }

    /**
     * @param $identifier
     * @param null $storeId
     * @return array|Data\BlockInterface[]
     */
    public function getListByIdentifier($identifier, $storeId = null)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(Block::IDENTIFIER, $identifier)->create();

        $items = $this->getList($searchCriteria)->getItems();

        if ($storeId) {
            $items = array_filter($items, function ($item) use ($storeId) {
                /** @var Block $item */
                return in_array($storeId, $item->getStoreId());
            });
        }

        return $items;
    }
}
