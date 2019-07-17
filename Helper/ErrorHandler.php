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
use Magento\Framework\Exception\LocalizedException;
use Psr\Http\Message\ResponseInterface;
use SmartCat\Connector\Logger\Logger;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectRepository;
use \Throwable;

class ErrorHandler
{
    private $logger;
    private $projectRepository;

    /**
     * ErrorHandler constructor.
     * @param Logger $logger
     * @param ProjectRepository $projectRepository
     */
    public function __construct(Logger $logger, ProjectRepository $projectRepository)
    {
        $this->logger = $logger;
        $this->projectRepository = $projectRepository;
    }

    /**
     * @param Throwable $e
     * @param Project $project
     * @param string $message
     * @return string
     */
    public function handleProjectError(Throwable $e, Project $project, $message = '')
    {
        $message = $this->handleError($e, $message);

        if ($e instanceof ServerErrorException) {
            return $message;
        }

        $project
            ->setStatus(Project::STATUS_FAILED)
            ->setComment($message);

        try {
            $this->projectRepository->save($project);
        } catch (LocalizedException $e) {
        }

        return $message;
    }

    /**
     * @param Throwable $e
     * @param string $message
     * @return string
     */
    public function handleError(Throwable $e, $message = '')
    {
        if (($e instanceof ClientErrorException) || ($e instanceof ServerErrorException)) {
            $message = $this->getMessageByStatusCode($e->getResponse(), $message);
        } else {
            $message = "{$message}: {$e->getMessage()}";
        }

        $this->logger->error($message);

        return $message;
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logError($message, $context = [])
    {
        $this->logger->error($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logInfo($message, $context = [])
    {
        $this->logger->info($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logWarning($message, $context = [])
    {
        $this->logger->warning($message, $context);
    }

    /**
     * @param $message
     * @param array $context
     */
    public function logDebug($message, $context = [])
    {
        $this->logger->debug($message, $context);
    }

    /**
     * @param ResponseInterface $response
     * @param $message
     * @return \Magento\Framework\Phrase
     */
    private function getMessageByStatusCode(ResponseInterface $response, $message)
    {
        switch ($response->getStatusCode()) {
            case 401:
                $message = __("Smartcat credentials are incorrect. Please check configuration settings.");
                break;
            case 404:
                $message = __("Smartcat project not found.");
                break;
            default:
                $body = str_replace('\r\n', ' ', $response->getBody()->getContents());
                $message = "{$message}: {$response->getStatusCode()} {$body}";
                break;
        }

        return $message;
    }
}
