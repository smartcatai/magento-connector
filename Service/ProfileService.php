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

use SmartCat\Connector\Exception\ProfileServiceException;
use SmartCat\Connector\Helper\SmartCatFacade;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectRepository;

class ProfileService
{
    private $profileRepository;
    private $projectRepository;
    private $smartCatService;
    private $storeService;

    public function __construct(
        ProfileRepository $profileRepository,
        ProjectRepository $projectRepository,
        StoreService $storeService,
        SmartCatFacade $smartCatService
    ) {
        $this->profileRepository = $profileRepository;
        $this->projectRepository = $projectRepository;
        $this->storeService = $storeService;
        $this->smartCatService = $smartCatService;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ProfileServiceException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createFromData(array $data)
    {
        if (!empty($data[Profile::ID])) {
            $model = $this->profileRepository->getById($data[Profile::ID]);
        } else {
            $model = $this->profileRepository->create();
        }

        if (in_array($data[Profile::SOURCE_LANG], $data[Profile::TARGET_LANG])) {
            throw new ProfileServiceException(__('Source Language and Target Language are identical'));
        }

        foreach ($data[Profile::TARGET_LANG] as $language) {
            $this->storeService->createStoreByCode($language);
        }

        $data[Profile::TARGET_LANG] = implode(',', $data[Profile::TARGET_LANG]);
        $data[Profile::STAGES] = implode(',', $data[Profile::STAGES]);

        if (!empty($data[Profile::EXCLUDED_ATTRIBUTES])) {
            $data[Profile::EXCLUDED_ATTRIBUTES] = implode(',', $data[Profile::EXCLUDED_ATTRIBUTES]);
        }

        if (empty($data[Profile::NAME])) {
            $data[Profile::NAME] = __('Languages:') . ' ' . $data[Profile::SOURCE_LANG] . ' -> ' . $data[Profile::TARGET_LANG];
        }

        if (!empty($data[Profile::PROJECT_GUID])) {
            $this->checkProject($model, $data[Profile::PROJECT_GUID]);
        }

        $model->setData($data);
        $this->profileRepository->save($model);

        return $model->getId();
    }

    /**
     * @param Profile $model
     * @param $projectId
     * @throws ProfileServiceException
     */
    private function checkProject(Profile &$model, $projectId)
    {
        try {
            $projectManager = $this->smartCatService->getProjectManager();
            $smartCatProject = $projectManager->projectGet($projectId);

            $targetLanguages = $smartCatProject->getTargetLanguages();
            $sourceLanguage = $smartCatProject->getSourceLanguage();

            $model->setTargetLang($targetLanguages);
            $model->setSourceLang($sourceLanguage);
        } catch (\Throwable $e) {
            throw new ProfileServiceException(__($e->getMessage()));
        }
    }

    /**
     * @param int $profileId
     * @return \SmartCat\Connector\Api\Data\ProfileInterface|Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfileById($profileId)
    {
        return $this->profileRepository->getById($profileId);
    }

    /**
     * @param \SmartCat\Connector\Api\Data\ProjectInterface|Project $project
     * @return \SmartCat\Connector\Api\Data\ProfileInterface|Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfileByProject(Project $project)
    {
        return $this->profileRepository->getById($project->getProfileId());
    }

    /**
     * @param int $projectId
     * @return \SmartCat\Connector\Api\Data\ProfileInterface|Profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProfileByProjectId($projectId)
    {
        $project = $this->projectRepository->getById($projectId);
        return $this->profileRepository->getById($project->getProfileId());
    }
}
