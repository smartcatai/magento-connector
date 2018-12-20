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

namespace SmartCat\Connector\Service;

use SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel;
use SmartCat\Connector\Exception\SmartCatHttpException;
use SmartCat\Connector\Helper\ErrorHandler;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectRepository;
use SmartCat\Connector\Service\Strategy\StrategyInterface;
use SmartCat\Connector\Service\Strategy\StrategyLoader;
use \Throwable;

class ProjectService
{
    private $projectRepository;
    private $profileRepository;
    private $errorHandler;
    private $strategyLoader;
    private $projectEntityService;

    /**
     * ProjectService constructor.
     * @param ProjectRepository $projectRepository
     * @param ErrorHandler $errorHandler
     */
    public function __construct(
        ProjectRepository $projectRepository,
        ProfileRepository $profileRepository,
        StrategyLoader $strategyLoader,
        ErrorHandler $errorHandler,
        ProjectEntityService $projectEntityService
    ) {
        $this->projectRepository = $projectRepository;
        $this->profileRepository = $profileRepository;
        $this->errorHandler = $errorHandler;
        $this->strategyLoader = $strategyLoader;
        $this->projectEntityService = $projectEntityService;
    }

    /**
     * @param array $models
     * @param Profile $profile
     * @return Project
     * @throws SmartCatHttpException
     */
    public function create(array $models, Profile $profile)
    {
        if (empty($models)) {
            throw new SmartCatHttpException(__('Models array is empty'));
        }

        $strategy = $this->strategyLoader->getStrategyByModel(get_class($models[0]));

        $project = $this->projectRepository->create();
        $project
            ->setProfileId($profile->getId())
            ->setElement($strategy->getName($models))
            ->setTranslate($profile->getSourceLang() . ' -> ' . $profile->getTargetLang())
            ->setStatus(Project::STATUS_WAITING);

        if ($profile->getProjectGuid()) {
            $project->setGuid($profile->getProjectGuid());
        }

        try {
            $this->projectRepository->save($project);
            foreach ($models as $model) {
                $strategy->attach($model, $project, $profile);
            }
        } catch (Throwable $e) {
            $message = $this->errorHandler->handleError($e, "Error save project to db");
            throw new SmartCatHttpException($message, $e->getCode(), $e->getPrevious());
        }

        return $project;
    }

    /**
     * @param Project $model
     * @return bool
     */
    public function update(Project $model)
    {
        try {
            if ($model->hasDataChanges()) {
                $this->projectRepository->save($model);
            }
        } catch (Throwable $e) {
            return false;
        }

        return true;
    }

    /**
     * @param \SmartCat\Connector\Api\Data\ProjectInterface|Project $project
     * @return \SmartCat\Connector\Api\Data\ProfileInterface|Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProjectProfile(Project $project)
    {
        return $this->profileRepository->getById($project->getProfileId());
    }

    /**
     * @param Project $project
     * @return CreateDocumentPropertyWithFilesModel[]
     */
    public function getProjectDocumentModels(Project $project)
    {
        $documentModels = [];
        $entities = $this->projectEntityService->getProjectEntities($project);

        foreach ($entities as $entity) {
            /** @var StrategyInterface $strategy */
            $strategy = $this->strategyLoader->getStrategyByType($entity->getEntity());
            $documentModels[] = $strategy->getDocumentModel($entity);
        }

        return $documentModels;
    }
}
