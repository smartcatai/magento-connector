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

namespace SmartCat\Connector\Controller\Adminhtml\Profile;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use SmartCat\Connector\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action\Context;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Model\Profile;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\ObjectManager;
use SmartCat\Connector\Service\ProjectService;
use Magento\Ui\Component\MassAction\Filter;

class Localize extends \Magento\Backend\App\Action
{
    private $productRepository;
    private $profileRepository;
    private $productCollectionFactory;
    private $projectService;
    private $filter;

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
        ProfileRepositoryInterface $profileRepository = null,
        ProductRepositoryInterface $productRepository = null
    ) {
        $this->productRepository = $productRepository
            ?: ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        $this->profileRepository = $profileRepository
            ?: ObjectManager::getInstance()->create(ProfileRepositoryInterface::class);
        $this->projectService = $projectService;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Localize action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws NotFoundException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();
        $productsCollection = $this->filter->getCollection($this->productCollectionFactory->create());

        /** @var \Magento\Backend\Model\View\Result\Redirect\Interceptor $resultFactory */
        $resultFactory = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        if (!$request->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        $profileId = $request->getParam(Profile::ID);

        /** @var Profile $profile */
        try {
            $profile = $this->profileRepository->getById($profileId);
        } catch (\Throwable $e) {
            $this->messageManager->addErrorMessage(__('Profile not found'));

            return $resultFactory->setPath('catalog/product/index');
        }

        $products = [];

        if ($productsCollection->getSize() == 0) {
            $this->messageManager->addErrorMessage(__('Not found selected products'));
            return $resultFactory->setPath('catalog/product/index');
        } else {
            foreach ($productsCollection as $productFromCollection) {
                $products[] = $this->productRepository->getById($productFromCollection->getId(), false, 1);
            }
        }

        try {
            if ($profile->getBatchSend()) {
                $this->projectService->create($products, $profile);
            } else {
                foreach ($products as $product) {
                    $this->projectService->create([$product], $profile);
                }
            }
        } catch (SmartCatHttpException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultFactory->setPath('catalog/product/index');
        }

        $this->messageManager->addSuccessMessage(__('All selected products sended on localize'));

        return $resultFactory->setPath('catalog/product/index');
    }
}
