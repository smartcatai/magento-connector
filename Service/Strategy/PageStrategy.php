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
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\PageRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\Store;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class PageStrategy extends AbstractStrategy
{
    private $pageRepository;
    private $pageFactory;
    private $parametersTag = 'parameters';

    /**
     * PageStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param PageRepository $pageRepository
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreService $storeService,
        PageRepository $pageRepository,
        PageFactory $pageFactory
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $pageFactory;
        parent::__construct($projectEntityService, $storeService);
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

        $data = $this->encodeJsonParameters($page);
        $fileName = "{$page->getTitle()}({$entity->getLanguage()}).json";

         return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @param Page $page
     * @return false|string
     */
    private function encodeJsonParameters(Page $page)
    {
        $parameters = [
            'title' => $page->getTitle(),
            'meta_title' => $page->getMetaTitle(),
            'meta_keywords' => $page->getMetaKeywords(),
            'meta_description' => $page->getMetaDescription(),
            'content_heading' => $page->getContentHeading(),
            'content' => $page->getContent()
        ];

        return json_encode($parameters);
    }

    /**
     * @param Page[] $models
     * @return string
     */
    public function getName(array $models)
    {
        $names = [];

        foreach ($models as $model) {
            if ($model instanceof Page) {
                $names[] = $model->getTitle();
            }
        }

        return parent::getName($names);
    }

    /**
     * @return string
     */
    public static function getType()
    {
        return 'page';
    }

    /**
     * @param string $content
     * @param ProjectEntity $entity
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function setContent($content, ProjectEntity $entity): bool
    {
        $storeID = $this->storeService->getStoreIdByCode($entity->getLanguage());

        if ($storeID === null) {
            return false;
        }

        $page = $this->pageRepository->getById($entity->getEntityId());

        if ($entity->getAttribute() == $this->parametersTag) {
            $parameters = $this->decodeJsonParameters($content);
            $newPage = $this->pageFactory->create();
            $newPage
                ->setStoreId([$storeID])
                ->setTitle($parameters['title'])
                ->setMetaTitle($parameters['meta_title'])
                ->setMetaDescription($parameters['meta_description'])
                ->setMetaKeywords($parameters['meta_keywords'])
                ->setContentHeading($parameters['content_heading'])
                ->setContent($parameters['content'])
                ->setIsActive(true)
                ->setIdentifier($page->getIdentifier() . '_' . $entity->getLanguage())
                ->setPageLayout($page->getPageLayout());

            $this->pageRepository->save($newPage);

            return true;
        }

        return false;
    }
}
