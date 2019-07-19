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

namespace SmartCat\Connector\Helper;

use Http\Client\Common\Exception\ClientErrorException;
use Http\Client\Common\Exception\ServerErrorException;
use Magento\Framework\Model\AbstractModel;
use Psr\Http\Message\ResponseInterface;
use SmartCat\Connector\Logger\Logger;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Model\ProjectEntityRepository;
use SmartCat\Connector\Model\ProjectRepository;
use \Throwable;

class ErrorHandler
{
    private $logger;
    private $projectRepository;
    private $entityRepository;

    /**
     * ErrorHandler constructor.
     * @param Logger $logger
     * @param ProjectRepository $projectRepository
     * @param ProjectEntityRepository $entityRepository
     */
    public function __construct(Logger $logger, ProjectRepository $projectRepository, ProjectEntityRepository $entityRepository)
    {
        $this->logger = $logger;
        $this->projectRepository = $projectRepository;
        $this->entityRepository = $entityRepository;
    }

    /**
     * @param Throwable $e
     * @param Project $project
     * @param string $message
     * @return string
     */
    public function handleProjectError(Throwable $e, Project $project, $message = '')
    {
        $message = $this->handleError($e, $message, ['project' => $project]);

        if ($e instanceof ServerErrorException) {
            return $message;
        }

        $project
            ->setStatus(Project::STATUS_FAILED)
            ->setComment($message);

        $entities = $this->entityRepository->getItemsByProject($project);

        foreach ($entities as $entity) {
            try {
                $entity->setStatus(ProjectEntity::STATUS_FAILED);
                $this->entityRepository->save($entity);
                $this->logInfo(
                    "Set project entity status to failed because parent project was failed",
                    ['entity' => $entity, 'project' => $project, 'exception' => $e]

                );
            } catch (Throwable $e) {
                $this->logWarning(
                    "Can't save project entity",
                    ['entity' => $entity, 'project' => $project, 'exception' => $e]
                );
                continue;
            }
        }

        try {
            $this->projectRepository->save($project);
        } catch (Throwable $e) {
            $this->logWarning(
                "Can't save project",
                ['project' => $project, 'exception' => $e]
            );
        }

        return $message;
    }

    /**
     * @param Throwable $e
     * @param string $message
     * @param array $context
     * @return string
     */
    public function handleError(Throwable $e, $message = '', $context = [])
    {
        if (($e instanceof ClientErrorException) || ($e instanceof ServerErrorException)) {
            $message = $this->getMessageByStatusCode($e->getResponse(), $message);
        } else {
            $message = "{$message}: {$e->getMessage()}";
        }

        $this->logError($message, array_merge($context, ['exception' => $e]));

        return $message;
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logError($message, $context = [])
    {
        $this->logger->error($message, $this->generateContext($context));
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logInfo($message, $context = [])
    {
        $this->logger->info($message, $this->generateContext($context));
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logWarning($message, $context = [])
    {
        $this->logger->warning($message, $this->generateContext($context));
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logDebug($message, $context = [])
    {
        $this->logger->debug($message, $this->generateContext($context));
    }

    /**
     * @param array $context
     * @return array
     */
    private function generateContext(array $context) {
        foreach ($context as $key => &$value) {
            if ($value instanceof Throwable) {
                $value = [
                    'message' => $value->getMessage(),
                    'trace' => explode("\n", $value->getTraceAsString())
                ];
            } elseif ($value instanceof AbstractModel) {
                $value = $value->getData();
            } elseif ($value instanceof ResponseInterface) {
                $value = [
                    'statusCode' => $value->getStatusCode(),
                    'body' => $value->getBody()->getContents(),
                ];
            }
        }

        return $context;
    }

    /**
     * @param ResponseInterface $response
     * @param $message
     * @return string
     */
    private function getMessageByStatusCode(ResponseInterface $response, $message)
    {
        $additionalMessage = " ";

        switch ($response->getStatusCode()) {
            case 401:
                $additionalMessage = " Smartcat credentials are incorrect. Please check configuration settings. ";
                break;
            case 404:
                $additionalMessage = " Smartcat project not found. ";
                break;
        }

        $body = str_replace('\r\n', ' ', $response->getBody()->getContents());
        $message = "{$message}: {$response->getStatusCode()}{$additionalMessage}{$body}";

        return $message;
    }
}
