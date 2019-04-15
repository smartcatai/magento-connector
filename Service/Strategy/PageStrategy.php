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
use SmartCat\Connector\Model\PageRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class PageStrategy extends AbstractStrategy
{
    private $pageRepository;
    private $pageFactory;
    private $typeTag = 'parameters';

    /**
     * PageStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param StoreService $storeService
     * @param PageRepository $pageRepository
     * @param PageFactory $pageFactory
     * @param UrlInterface $urlManager
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreService $storeService,
        PageRepository $pageRepository,
        PageFactory $pageFactory,
        UrlInterface $urlManager
    ) {
        $this->pageRepository = $pageRepository;
        $this->pageFactory = $pageFactory;
        parent::__construct($projectEntityService, $storeService, $urlManager);
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
        $this->projectEntityService->create($project, $model, $profile, self::getEntityName(), $this->typeTag);
    }

    /**
     * @param ProjectEntity $entity
     * @return \SmartCat\Client\Model\CreateDocumentPropertyWithFilesModel|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        if ($entity->getEntity() != self::getEntityName()) {
            return null;
        }

        $page = $this->pageRepository->getById($entity->getEntityId());

        $data = $this->encodeJsonParameters($page);
        $fileName = "{$page->getTitle()}({$entity->getTargetLang()}).json";

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
    public function getElementNames(array $models)
    {
        $names = [];

        foreach ($models as $model) {
            if ($model instanceof Page) {
                $names[] = $model->getTitle();
            }
        }

        return parent::getElementNames($names);
    }

    /**
     * @return string
     */
    public static function getEntityName()
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
        $storeID = $this->storeService->getStoreIdByCode($entity->getTargetLang());

        if ($storeID === null) {
            return false;
        }

        $page = $this->pageRepository->getById($entity->getEntityId());

        if ($entity->getType() == $this->typeTag) {
            $parameters = $this->decodeJsonParameters($content);
            $duplicate = $this->pageRepository->getListByIdentifier($page->getIdentifier(), $storeID);

            if (!empty($duplicate)) {
                $newPage = array_shift($duplicate);
            } else {
                $newPage = $this->pageFactory->create();

                $newPage
                    ->setStoreId([$storeID])
                    ->setIsActive(true)
                    ->setIdentifier($page->getIdentifier())
                    ->setPageLayout($page->getPageLayout());
            }

            $newPage
                ->setTitle($parameters['title'])
                ->setMetaTitle($parameters['meta_title'])
                ->setMetaDescription($parameters['meta_description'])
                ->setMetaKeywords($parameters['meta_keywords'])
                ->setContentHeading($parameters['content_heading'])
                ->setContent($parameters['content']);

            $this->pageRepository->save($newPage);

            return true;
        }

        return false;
    }

    /**
     * @param $entityId
     * @return string
     */
    public function getEntityNormalName($entityId)
    {
        try {
            return $this->pageRepository->getById($entityId)->getTitle();
        } catch (\Throwable $e) {
        }

        return '';
    }

    /**
     * @param $entityId
     * @return string
     */
    public function getUrlToEntity($entityId)
    {
        return $this->urlManager->getUrl('cms/page/edit', ['page_id' => $entityId]);
    }
}
