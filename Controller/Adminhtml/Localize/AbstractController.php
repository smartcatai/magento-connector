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

use SmartCat\Connector\Api\ProfileRepositoryInterface;
use Magento\Backend\App\Action\Context;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Model\Profile;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;
use SmartCat\Connector\Service\ProjectService;
use Magento\Ui\Component\MassAction\Filter;

abstract class AbstractController extends \Magento\Backend\App\Action
{
    private $profileRepository;
    private $projectService;
    protected $filter;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ProjectService $projectService
     * @param ProfileRepositoryInterface|null $profileRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ProjectService $projectService,
        ProfileRepositoryInterface $profileRepository
    ) {
        $this->profileRepository = $profileRepository;
        $this->projectService = $projectService;
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
        $redirectPage = $this->getRedirectPath();

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

            return $resultFactory->setPath($redirectPage);
        }

        $models = $this->getModels();

        if (empty($models)) {
            $this->messageManager->addErrorMessage(__('Not found selected items'));
            return $resultFactory->setPath($redirectPage);
        }

        try {
            if ($profile->getBatchSend()) {
                $this->projectService->create($models, $profile);
            } else {
                foreach ($models as $model) {
                    $this->projectService->create([$model], $profile);
                }
            }
        } catch (SmartCatHttpException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultFactory->setPath($redirectPage);
        }

        $this->messageManager->addSuccessMessage(__('All selected items were sent to localization'));

        return $resultFactory->setPath($redirectPage);
    }

    /**
     * @return array
     */
    public function getModels()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getRedirectPath()
    {
        return "*/*/index";
    }
}
