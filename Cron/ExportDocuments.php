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

namespace SmartCat\Connector\Cron;

use Http\Client\Common\Exception\ClientErrorException;
use Magento\Framework\Exception\NoSuchEntityException;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\Strategy\StrategyLoader;
use \Throwable;

class ExportDocuments
{
    private $smartCatService;
    private $errorHandler;
    private $projectEntityService;
    private $strategyLoader;

    public function __construct(
        ErrorHandler $errorHandler,
        SmartCatFacade $smartCatService,
        ProjectEntityService $projectEntityService,
        StrategyLoader $strategyLoader
    ) {
        $this->errorHandler = $errorHandler;
        $this->smartCatService = $smartCatService;
        $this->projectEntityService = $projectEntityService;
        $this->strategyLoader = $strategyLoader;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->smartCatService->checkCredentials()) {
            return;
        }

        $entities = $this->projectEntityService->getExportingEntities();

        foreach ($entities as $entity) {
            try {
                $response = $this->smartCatService
                    ->getDocumentExportManager()
                    ->documentExportDownloadExportResult($entity->getTaskId());
            } catch (Throwable $e) {
                $status = ($e instanceof ClientErrorException)
                    ? ProjectEntity::STATUS_COMPLETED : ProjectEntity::STATUS_FAILED;
                $entity->setStatus($status);
                $this->errorHandler
                    ->logError("Document export error: Task id {$entity->getTaskId()} {$e->getMessage()}");
                $this->projectEntityService->update($entity);
                continue;
            }

            if ($response->getStatusCode() != 200) {
                continue;
            }

            $content = $response->getBody()->getContents();
            $strategy = $this->strategyLoader->getStrategyByType($entity->getEntity());

            try {
                $strategy->setContent($content, $entity);
            } catch (Throwable $e) {
                $this->errorHandler
                    ->logError("Can't save content to entity {$entity->getId()}. Error: {$e->getMessage()}");

                $this->errorHandler
                    ->logError($e->getTraceAsString());

                if ($e instanceof NoSuchEntityException) {
                    $entity->setStatus(ProjectEntity::STATUS_FAILED);
                    $this->projectEntityService->update($entity);
                }

                continue;
            }

            $entity
                ->setStatus(ProjectEntity::STATUS_SAVED)
                ->setTaskId(null);

            $this->projectEntityService->update($entity);
        }
    }
}
