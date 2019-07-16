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

namespace SmartCat\Connector\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Response\Http\FileFactory;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends Action
{
    private $resultRaw;
    private $fileFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Raw $resultRaw
     */
    public function __construct(
        Context $context,
        Raw $resultRaw,
        FileFactory $fileFactory
    ) {
        $this->resultRaw = $resultRaw;
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute()
    {
        try {
            $fileName = 'magento_log_file.log';

            $file = $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => 'system.log'
                ],
                DirectoryList::LOG,
                'application/octet-stream',
                null
            );
        } catch (\Exception $exception) {
            var_dump($exception);
        }

        return $this->resultRaw->renderResult($file);
    }
}