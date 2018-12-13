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
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\ProfileRepository;

class ProfileService
{
    private $profileRepository;
    private $storeService;

    public function __construct(
        ProfileRepository $profileRepository,
        StoreService $storeService
    ) {
        $this->profileRepository = $profileRepository;
        $this->storeService = $storeService;
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ProfileServiceException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createFromData(array $data)
    {
        //TODO сheck project id with this profile

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

        $model->setData($data);
        $this->profileRepository->save($model);

        return $model->getId();
    }
}
