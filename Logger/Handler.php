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

namespace SmartCat\Connector\Logger;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use SmartCat\Connector\Module;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * File name
     * @var string
     */
    protected $fileName = Module::LOG_FILE_NAME;

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param null $filePath
     * @param null $fileName
     * @throws \Exception
     */
    public function __construct(DriverInterface $filesystem, $filePath = null, $fileName = null)
    {
        $logDirectory = DirectoryList::getDefaultConfig()[DirectoryList::LOG][DirectoryList::PATH];
        $this->fileName = $logDirectory . DIRECTORY_SEPARATOR . Module::LOG_FILE_NAME;
        parent::__construct($filesystem, $filePath, $fileName);
    }
}
