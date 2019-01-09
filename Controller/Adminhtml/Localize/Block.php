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

use Magento\Cms\Model\BlockRepository;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory as BlockCollectionFactory;
use SmartCat\Connector\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action\Context;
use SmartCat\Connector\Service\ProjectService;
use Magento\Ui\Component\MassAction\Filter;

class Block extends AbstractController
{
    private $blockRepository;
    private $blockCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProjectService $projectService
     * @param BlockCollectionFactory $blockCollectionFactory
     * @param ProfileRepositoryInterface|null $profileRepository
     * @param BlockRepository $blockRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProjectService $projectService,
        BlockCollectionFactory $blockCollectionFactory,
        ProfileRepositoryInterface $profileRepository,
        BlockRepository $blockRepository
    ) {
        $this->blockRepository = $blockRepository;
        $this->blockCollectionFactory = $blockCollectionFactory;
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
        $productsCollection = $this->filter->getCollection($this->blockCollectionFactory->create());

        foreach ($productsCollection as $productFromCollection) {
            $models[] = $this->blockRepository->getById($productFromCollection->getId());
        }

        return $models;
    }

    /**
     * @return string
     */
    public function getRedirectPath()
    {
        return "cms/block/index";
    }
}
