<?php

namespace Bitrix\Catalog\v2\Section;

use Bitrix\Catalog\v2\BaseCollection;
use Bitrix\Catalog\v2\BaseEntity;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Class SectionRepository
 *
 * @package Bitrix\Catalog\v2\Section
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class SectionRepository implements SectionRepositoryContract
{
	/** @var \Bitrix\Catalog\v2\Section\SectionFactory */
	protected $factory;

	public function __construct(SectionFactory $factory)
	{
		$this->factory = $factory;
	}

	public function getEntityById(int $id): ?BaseEntity
	{
		if ($id <= 0)
		{
			throw new \OutOfRangeException($id);
		}

		$entities = $this->getEntitiesBy([
			'filter' => [
				'=ID' => $id,
			],
		]);

		return reset($entities) ?: null;
	}

	// ToDo custom load section entities by filter?
	public function getEntitiesBy($params): array
	{
		$entities = [];

		foreach ($this->getList((array)$params) as $item)
		{
			$entities[] = $this->createEntity($item);
		}

		return $entities;
	}

	public function save(BaseEntity ...$entities): Result
	{
		$result = new Result();

		/** @var \Bitrix\Catalog\v2\Product\BaseProduct $parentEntity */
		$parentEntity = null;
		$sections = [];

		/** @var \Bitrix\Catalog\v2\Section\Section $section */
		foreach ($entities as $section)
		{
			if ($parentEntity && $parentEntity !== $section->getParent())
			{
				$result->addError(new Error('Saving should only be done with sections of a common parent.'));
			}

			if ($parentEntity === null)
			{
				$parentEntity = $section->getParent();
			}

			$sections[] = $section->getValue();
		}

		if (!$parentEntity)
		{
			$result->addError(new Error('Parent entity not found while saving sections.'));
		}

		if (!($parentEntity instanceof BaseProduct))
		{
			$result->addError(new Error(sprintf(
				'Parent entity of section must be an instance of {%s}.',
				BaseProduct::class
			)));
		}

		if (!empty($sections) && $result->isSuccess())
		{
			\CIBlockElement::setElementSection(
				$parentEntity->getId(),
				$sections,
				$parentEntity->isNew(),
				0 // $arIBlock["RIGHTS_MODE"] === "E"? $arIBlock["ID"]: 0
			);
		}

		return $result;
	}

	public function delete(BaseEntity ...$entities): Result
	{
		// ToDo: Implement delete() method.
		return new Result();
	}

	public function getCollectionByProduct(BaseProduct $product): SectionCollection
	{
		if ($product->isNew())
		{
			return $this->createCollection();
		}

		$result = $this->getListByProductId($product->getId());

		return $this->createCollection($result);
	}

	protected function getListByProductId(int $productId): array
	{
		$result = [];

		$sectionIdsIterator = \CIBlockElement::getElementGroups(
			$productId,
			true,
			['ID']
		);
		while ($section = $sectionIdsIterator->fetch())
		{
			$result[] = [
				'VALUE' => (int)$section['ID'],
			];
		}

		return $result;
	}

	// ToDo getList for "get by filter" sections
	protected function getList(array $params): array
	{
		return [];
	}

	protected function createEntity(array $fields): Section
	{
		$entity = $this->factory->createEntity();

		$entity->initFields($fields);

		return $entity;
	}

	protected function createCollection(array $entityFields = []): SectionCollection
	{
		$collection = $this->factory->createCollection();

		foreach ($entityFields as $fields)
		{
			$section = $this->createEntity($fields);
			$collection->add($section);
		}

		return $collection;
	}
}