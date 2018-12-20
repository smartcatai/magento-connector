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

namespace SmartCat\Connector\Service\Strategy;

use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageRepository;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;

class PageStrategy extends AbstractStrategy
{
    private $pageRepository;
    private $parametersTag = 'parameters';

    /**
     * PageStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param PageRepository $pageRepository
     */
    public function __construct(ProjectEntityService $projectEntityService, PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
        parent::__construct($projectEntityService);
    }

    /**
     * @return array|string[]
     */
    public static function getAppliedClasses()
    {
        return [Page::class];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param Project $project
     * @param Profile $profile
     */
    public function attach($model, Project $project, Profile $profile)
    {
        if (trim($model->getData(Page::CONTENT))) {
            $this->projectEntityService->create($project, $model, $profile, self::getType() . '|' . Page::CONTENT);
        }
        $this->projectEntityService->create($project, $model, $profile, self::getType() . '|' . $this->parametersTag);
    }

    /**
     * @param ProjectEntity $entity
     * @return \SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        if ($entity->getEntity() != self::getType()) {
            return null;
        }

        $page = $this->pageRepository->getById($entity->getEntityId());

        switch ($entity->getAttribute()) {
            case $this->parametersTag:
                $data = $this->getJsonParameters($page);
                $fileName = "{$entity->getAttribute()}({$page->getTitle()})({$entity->getLanguage()}).json";
                break;
            case Page::CONTENT:
                $data = $page->getContent();
                $fileName = "{$entity->getAttribute()}({$page->getTitle()})({$entity->getLanguage()}).html";
                break;
            default:
                return null;
        }

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @param Page $page
     * @return false|string
     */
    private function getJsonParameters(Page $page)
    {
        $parameters = [
            'title' => $page->getTitle(),
            'meta_title' => $page->getMetaTitle(),
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'content_heading' => $page->getContentHeading()
        ];

        return json_encode($parameters);
    }

    /**
     * @param array|\Magento\Framework\Model\AbstractModel[] $models
     * @return string
     */
    public function getName(array $models)
    {
        $name = null;

        foreach ($models as $model) {
            if ($model instanceof Page) {
                if (strlen($name) < 80) {
                    $name .= $model->getTitle();
                } else {
                    break;
                }
                $name .= ', ';
            }
        }

        if (strlen($name) > 99) {
            $name = substr($name, 0, 99);
        } else {
            $name = substr($name, 0, -2);
        }

        return str_replace(['*', '|', '\\', ':', '"', '<', '>', '?', '/'], ' ', $name);
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'page';
    }
}
