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

namespace SmartCat\Connector\Controller\Adminhtml\Entity;

use Magento\Backend\App\Action;
use Magento\Framework\App\ObjectManager;
use SmartCat\Connector\Service\ProfileService;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\ProjectService;

class SyncAll extends Action
{
    private const PROJECT_ENTITY_MODEL = "SmartCat\Connector\Model\ProjectEntity";
    private $projectEntityService;
    private $projectEntityModel;

    /**
     * Category constructor.
     * @param Action\Context $context
     * @param ProfileService $profileService
     * @param ProjectService $projectService
     */
    public function __construct(
        Action\Context $context,
        ProjectEntityService $projectEntityService
    )
    {
        $this->projectEntityService = $projectEntityService;
        $objectManager = ObjectManager::getInstance();
        $this->projectEntityModel = $objectManager->create(self::PROJECT_ENTITY_MODEL);
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $redirectFactory = $this->resultRedirectFactory->create();
        $failedDocuments = $this->getFailedDocumentsIds($this->projectEntityModel->getCollection());

        if (empty($failedDocuments)) {
            $this->messageManager->addWarningMessage(__('Failed documents list is empty'));
        } else {
            $sync = $this->syncDocuments($failedDocuments);
            if (empty($sync)) {
                $this->messageManager->addSuccessMessage(__('Sync of failed documents was successful'));
            } else {
                $this->messageManager->addErrorMessage(__('Error syncing documents: ') . implode(", ", $sync));
            }
        }

        return $redirectFactory->setPath('smartcat_connector/dashboard/index');
    }

    private function getFailedDocumentsIds($collection)
    {
        $failedDocumentsIds = [];
        foreach ($collection as $entity) {
            if ($entity->getStatus() === "failed") {
                $failedDocumentsIds[] = $entity->getId();
            }
        }
        return $failedDocumentsIds;
    }

    private function syncDocuments($failedDocuments)
    {
        $failedSync = [];
        foreach ($failedDocuments as $documentId) {
            try {
                $this->projectEntityService->sync($documentId);
            } catch (\Throwable $e) {
                $failedSync[] = $documentId;
            }
        }
        return $failedSync;
    }
}
