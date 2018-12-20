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

use Magento\Cms\Model\PageRepository;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use SmartCat\Connector\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action\Context;
use SmartCat\Connector\Service\ProjectService;
use Magento\Ui\Component\MassAction\Filter;

class Page extends AbstractController
{
    private $pageRepository;
    private $pageCollectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProjectService $projectService
     * @param PageCollectionFactory $pageCollectionFactory
     * @param ProfileRepositoryInterface|null $profileRepository
     * @param PageRepository $pageRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProjectService $projectService,
        PageCollectionFactory $pageCollectionFactory,
        ProfileRepositoryInterface $profileRepository,
        PageRepository $pageRepository
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageCollectionFactory = $pageCollectionFactory;
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
        $productsCollection = $this->filter->getCollection($this->pageCollectionFactory->create());

        foreach ($productsCollection as $productFromCollection) {
            $models[] = $this->pageRepository->getById($productFromCollection->getId());
        }

        return $models;
    }
}
