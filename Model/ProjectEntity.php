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

namespace SmartCat\Connector\Model;

class ProjectEntity extends \Magento\Framework\Model\AbstractModel
{
    const PROJECT_ID = 'project_id';
    const TYPE = 'type';
    const ENTITY_ID = 'entity_id';
    const STATUS = 'status';
    const DOCUMENT_ID = 'document_id';
    const TASK_ID = 'task_id';
    const ID = 'id';
    const TARGET_LANG = 'target_lang';

    const STATUS_NEW = 'new';
    const STATUS_SENDED = 'sended';
    const STATUS_EXPORT = 'export';
    const STATUS_SAVED = 'saved';
    const STATUS_FAILED = 'failed';

    const STATUS_COMPLETED = 'completed';
    const STATUS_IN_PROGRESS = 'inProgress';
    const STATUS_CREATED = 'created';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(\SmartCat\Connector\Model\ResourceModel\ProjectEntity::class);
    }

    /**
     * @return array
     */
    public static function getSmartCatStatuses()
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED
        ];
    }

    /**
     * @return array
     */
    public static function getSelfStatuses()
    {
        return [
            self::STATUS_NEW,
            self::STATUS_SENDED,
            self::STATUS_EXPORT,
            self::STATUS_SAVED,
            self::STATUS_FAILED
        ];
    }

    /**
     * Get project_id
     * @return string
     */
    public function getProjectId()
    {
        return $this->getData(self::PROJECT_ID);
    }

    /**
     * Set project_id
     * @param string $projectId
     * @return ProjectEntity
     */
    public function setProjectId($projectId)
    {
        return $this->setData(self::PROJECT_ID, $projectId);
    }

    /**
     * Get entity_id
     * @return string
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Set entity_id
     * @param string $entityId
     * @return ProjectEntity
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return ProjectEntity
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get entity_id
     * @return string
     */
    public function getDocumentId()
    {
        return $this->getData(self::DOCUMENT_ID);
    }

    /**
     * Set document_id
     * @param string $documentId
     * @return ProjectEntity
     */
    public function setDocumentId($documentId)
    {
        return $this->setData(self::DOCUMENT_ID, $documentId);
    }

    /**
     * Get task_id
     * @return string
     */
    public function getTaskId()
    {
        return $this->getData(self::TASK_ID);
    }

    /**
     * Set task_id
     * @param string $taskId
     * @return ProjectEntity
     */
    public function setTaskId($taskId)
    {
        return $this->setData(self::TASK_ID, $taskId);
    }

    /**
     * Get type
     * @return string
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get target lang
     * @return string
     */
    public function getTargetLang()
    {
        return $this->getData(self::TARGET_LANG);
    }

    /**
     * Set target lang
     * @param $targetLang
     * @return $this
     */
    public function setTargetLang($targetLang)
    {
        return $this->setData(self::TARGET_LANG, $targetLang);
    }

    /**
     * @return string|null
     */
    public function getAttribute()
    {
        return explode('|', $this->getType())[1] ?? null;
    }

    /**
     * @return string|null
     */
    public function getEntity()
    {
        return explode('|', $this->getType())[0] ?? null;
    }

    /**
     * Set type
     * @param string $type
     * @return ProjectEntity
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Get id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }
}
