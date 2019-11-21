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

use Magento\Framework\Api\SearchCriteriaBuilder;
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
    private $searchCriteriaBuilder;
    private $smartCatService;
    private $storeService;

    /**
     * ProfileService constructor.
     * @param ProfileRepository $profileRepository
     * @param ProjectRepository $projectRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreService $storeService
     * @param SmartCatFacade $smartCatService
     */
    public function __construct(
        ProfileRepository $profileRepository,
        ProjectRepository $projectRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreService $storeService,
        SmartCatFacade $smartCatService
    ) {
        $this->profileRepository = $profileRepository;
        $this->projectRepository = $projectRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeService = $storeService;
        $this->smartCatService = $smartCatService;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ProfileServiceException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createFromData(array &$data)
    {
        if (!empty($data[Profile::ID])) {
            $model = $this->profileRepository->getById($data[Profile::ID]);
        } else {
            $model = $this->profileRepository->create();
        }

        if (empty($data[Profile::TARGETS])) {
            throw new ProfileServiceException(__('Targets are not set'));
        }

        if (in_array($data[Profile::SOURCE_LANG], array_column($data[Profile::TARGETS], Profile::TARGET_LANG))) {
            throw new ProfileServiceException(__('Source Language and Target Language are identical'));
        }

        if (in_array($data[Profile::SOURCE_STORE], array_column($data[Profile::TARGETS], Profile::TARGET_STORE))) {
            throw new ProfileServiceException(__('Source Store and Target Store are identical'));
        }

        foreach ($data[Profile::TARGETS] as $key => $target) {
            if ($target[Profile::TARGET_STORE] == 0) {
                $storeId = $this->storeService->createStoreByCode($target[Profile::TARGET_LANG]);

                if ($storeId == 0) {
                    throw new ProfileServiceException(
                        __('There has been a problem creating a new Store View. Please check the list of existing Store Views.')
                    );
                }

                $data[Profile::TARGETS][$key][Profile::TARGET_STORE] = $storeId;
            }
        }

        if (empty($data[Profile::NAME])) {
            $data[Profile::NAME] =
                __('Languages:') . ' ' . $data[Profile::SOURCE_LANG] . ' -> ' . implode(', ', array_column($data[Profile::TARGETS], Profile::TARGET_LANG));
        }

        $data[Profile::TARGETS] = json_encode($data[Profile::TARGETS]);
        $data[Profile::STAGES] = implode(',', $data[Profile::STAGES]);

        if (!empty($data[Profile::VENDOR]) && $data[Profile::VENDOR] != 0) {
            try {
                $vendorsList = $this->smartCatService->getDirectoriesManager()
                    ->directoriesGet(['type' => 'vendor'])
                    ->getItems();

                foreach ($vendorsList as $vendor) {
                    if ($vendor->getId() == $data[Profile::VENDOR]) {
                        $data[Profile::VENDOR_NAME] = $vendor->getName();
                        break;
                    }
                }
            } catch (\Throwable $e) {
                $data[Profile::VENDOR_NAME] = null;
            }
        }

        $model->setData($data);

        if (!empty($data[Profile::PROJECT_GUID])) {
            $this->checkProject($model, $data[Profile::PROJECT_GUID]);
        }

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
        } catch (\Throwable $e) {
            throw new ProfileServiceException(__($e->getMessage()));
        }

        if (!empty(array_diff($targetLanguages, $model->getTargetLangs()))) {
            throw new ProfileServiceException(__('Target languages are not identical with project from Smartcat'));
        }

        if ($sourceLanguage !== $model->getSourceLang()) {
            throw new ProfileServiceException(__('Source languages are not identical with project from Smartcat'));
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

    /**
     * @return array|Profile[]
     */
    public function getAllProfiles()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();

        try {
            /** @var Profile[] $profiles */
            $profiles = $this->profileRepository->getList($searchCriteria)->getItems();
        } catch (\Throwable $e) {
            return [];
        }

        return $profiles;
    }

    /**
     * @param Profile $model
     * @return bool
     */
    public function update(Profile $model)
    {
        try {
            if ($model->hasDataChanges()) {
                $this->profileRepository->save($model);
            }
        } catch (\Throwable $e) {
            return false;
        }

        return true;
    }
}
