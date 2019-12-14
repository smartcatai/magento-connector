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

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use SmartCat\Connector\Model\BlockRepository;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use SmartCat\Connector\Model\Profile;
use SmartCat\Connector\Model\Project;
use SmartCat\Connector\Model\ProjectEntity;
use SmartCat\Connector\Service\ProjectEntityService;
use SmartCat\Connector\Service\StoreService;

class BlockStrategy extends AbstractStrategy
{
    private $typeTag = 'parameters';
    private $blockRepository;
    private $blockFactory;

    /**
     * BlockStrategy constructor.
     * @param ProjectEntityService $projectEntityService
     * @param StoreService $storeService
     * @param BlockRepository $blockRepository
     * @param BlockFactory $blockFactory
     * @param UrlInterface $urlManager
     */
    public function __construct(
        ProjectEntityService $projectEntityService,
        StoreService $storeService,
        BlockRepository $blockRepository,
        BlockFactory $blockFactory,
        UrlInterface $urlManager
    ) {
        $this->blockRepository = $blockRepository;
        $this->blockFactory = $blockFactory;
        parent::__construct($projectEntityService, $storeService, $urlManager);
    }

    /**
     * @return string[]
     */
    public static function getAppliedClasses()
    {
        return [Block::class];
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $model
     * @param Project $project
     * @param Profile $profile
     * @return void
     */
    public function attach($model, Project $project, Profile $profile)
    {
        $this->projectEntityService->create($project, $model, $profile, self::getEntityName(), $this->typeTag);
    }

    /**
     * @param ProjectEntity $entity
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDocumentModel(ProjectEntity $entity)
    {
        if ($entity->getEntity() != self::getEntityName()) {
            return null;
        }

        $block = $this->blockRepository->getById($entity->getEntityId());

        $data = $this->encodeJsonParameters($block);
        $fileName = "{$block->getTitle()}({$entity->getTargetLang()})" . self::EXTENSION;

        return $this->getDocumentFile($data, $fileName, $entity);
    }

    /**
     * @param Block $block
     * @return false|string
     */
    private function encodeJsonParameters(Block $block)
    {
        $jsonBlock = [
            'content' => $block->getContent(),
            'title' => $block->getTitle()
        ];

        return json_encode($jsonBlock);
    }

    /**
     * @param Block[] $models
     * @return string
     */
    public function getElementNames(array $models)
    {
        $names = [];

        foreach ($models as $model) {
            if ($model instanceof Block) {
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
        return 'block';
    }

    /**
     * @param $content
     * @param ProjectEntity $entity
     * @return bool
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function setContent($content, ProjectEntity $entity): bool
    {
        $block = $this->getBlock($entity);

        if ($entity->getType() == $this->typeTag) {
            $parameters = $this->decodeJsonParameters($content);

            $block
                ->setContent($parameters['content'])
                ->setTitle($parameters['title']);

            $this->blockRepository->save($block);

            $entity->setTargetEntityId($block->getId());
            $this->projectEntityService->update($entity);

            return true;
        }

        return false;
    }

    /**
     * @param $projectEntityId
     * @return string
     */
    public function getEntityNormalName($projectEntityId)
    {
        try {
            $entity = $this->projectEntityService->getEntityById($projectEntityId);

            return $this->blockRepository->getById($entity->getEntityId())->getTitle();
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
        return $this->urlManager->getUrl('cms/block/edit', ['block_id' => $entityId]);
    }

    /**
     * @param ProjectEntity $entity
     * @return Block
     * @throws NoSuchEntityException
     */
    private function getBlock(ProjectEntity $entity)
    {
        if ($entity->getTargetEntityId()) {
            return $this->blockRepository->getById($entity->getTargetEntityId());
        }

        $block = $this->blockRepository->getById($entity->getEntityId());

        $newIdentifier = $block->getIdentifier() . '_' . $entity->getTargetLang();
        $duplicate = $this->blockRepository->getListByIdentifier($newIdentifier);

        if (!empty($duplicate)) {
            $newBlock = array_shift($duplicate);
        } else {
            $newBlock = $this->blockFactory->create();
            $newBlock
                ->setIsActive(true)
                ->setStoreId([$entity->getTargetStore()])
                ->setIdentifier($newIdentifier);
        }

        return $newBlock;
    }
}
