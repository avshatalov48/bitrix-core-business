<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Builders\eventcategory\EventCategoryBuilderFromObject;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryCreate;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryDelete;
use Bitrix\Calendar\EventCategory\Event\AfterEventCategoryUpdate;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventCategoryTable;
use Bitrix\Main\ORM\Query\Result;

final class EventCategory extends Mapper
{
	public function getById(int $id): ?Core\EventCategory\EventCategory
	{
		if ($this->getCacheMap()->has($id))
		{
			return $this->getCacheMap()->getItem($id);
		}

		$entity = $this->getOneEntityByFilter([
			'=ID' => $id,
		]);

		if (!is_null($entity))
		{
			$this->getCacheMap()->add($entity, $id);
		}

		return $entity;
	}

	protected function getOneEntityByFilter(array $filter): ?Core\EventCategory\EventCategory
	{
		$eventData = OpenEventCategoryTable::query()
			->setFilter($filter)
			->setSelect(['*'])
			->fetchObject();

		if ($eventData)
		{
			return $this->convertToObject($eventData);
		}

		return null;
	}

	protected function convertToObject($objectEO): ?Core\EventCategory\EventCategory
	{
		return (new EventCategoryBuilderFromObject($objectEO))->build();
	}

	protected function getEntityClass(): string
	{
		return Core\EventCategory\EventCategory::class;
	}

	protected function getDataManagerResult(array $params): Result
	{
		$params['select'] = $params['select'] ?? ["*"];

		return OpenEventCategoryTable::getList($params);
	}

	protected function getEntityName(): string
	{
		return 'event_category';
	}

	protected function createEntity($entity, array $params = []): ?Core\EventCategory\EventCategory
	{
		$arrayEntity = $this->entityToArray($entity);
		$arrayEntity['DELETED'] = $arrayEntity['DELETED'] === true;
		$result = OpenEventCategoryTable::add($arrayEntity);

		if ($result->isSuccess())
		{
			$entityId = $result->getId();
			$entity->setId($entityId);

			(new AfterEventCategoryCreate($entityId))->emit();

			return $entity;
		}

		throw new Core\Base\BaseException('Error of create event category');
	}

	/**
	 * @param Core\EventCategory\EventCategory $entity
	 */
	protected function updateEntity($entity, array $params = [
		'updateEventsCounter' => false,
	]): ?Core\EventCategory\EventCategory
	{
		$arrayEntity = $this->entityToArray($entity);

		if ($params['updateEventsCounter'] ?? null)
		{
			$arrayEntity['EVENTS_COUNT'] = $entity->getEventsCount();
		}
		$result = OpenEventCategoryTable::update(
			$entity->getId(),
			$arrayEntity
		);

		if ($result->isSuccess())
		{
			(new AfterEventCategoryUpdate($entity->getId()))->emit();

			return $entity;
		}

		throw new Core\Base\BaseException('Error of update event category');
	}

	/**
	 * @param $entity Core\EventCategory\EventCategory
	 */
	protected function deleteEntity($entity, array $params): ?Core\EventCategory\EventCategory
	{
		$entity->setDeleted(true);

		$result = OpenEventCategoryTable::update(
			$entity->getId(),
			['DELETED' => 'Y'],
		);

		if ($result->isSuccess())
		{
			(new AfterEventCategoryDelete($entity->getId()))->emit();

			return $entity;
		}

		throw new Core\Base\BaseException('Error of delete event category');
	}

	private function entityToArray(Core\EventCategory\EventCategory $entity): array
	{
		return [
			'NAME' => $entity->getName(),
			'CREATOR_ID' => $entity->getCreatorId(),
			'CLOSED' => $entity->getClosed(),
			'DESCRIPTION' => $entity->getDescription(),
			'ACCESS_CODES' => $entity->getAccessCodes() ? implode(',', $entity->getAccessCodes()) : null,
			'DELETED' => $entity->getDeleted(),
			'CHANNEL_ID' => $entity->getChannelId(),
		];
	}
}
