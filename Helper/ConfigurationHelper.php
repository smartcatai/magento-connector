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

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use SmartCat\Client\SmartCat;

class ConfigurationHelper extends AbstractHelper
{
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param Context $context
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    /**
     * @param string $scope
     * @return mixed
     */
    public function getToken($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        $secret = $this->scopeConfig->getValue(
            'general/smartcat_localization/token',
            $scope
        );

        $secret = $this->encryptor->decrypt($secret);

        return $secret;
    }

    /**
     * @param string $scope
     * @return mixed
     */
    public function getApplicationId($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->getValue(
            'general/smartcat_localization/application_id',
            $scope
        );
    }

    /**
     * @param string $scope
     * @return string
     */
    public function getServer($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        $host = SmartCat::SC_EUROPE;

        switch ($this->scopeConfig->getValue('general/smartcat_localization/server', $scope)) {
            case 'usa':
                $host = SmartCat::SC_USA;
                break;
            case 'asia':
                $host = SmartCat::SC_ASIA;
                break;
        }

        return $host;
    }
}
