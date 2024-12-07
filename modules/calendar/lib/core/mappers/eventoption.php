<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\EntityInterface;
use Bitrix\Calendar\Core\Builders\EventOption\EventOptionBuilderFromObject;
use Bitrix\Calendar\OpenEvents\Internals\OpenEventOptionTable;
use Bitrix\Main\ORM\Query\Result;

final class EventOption extends Mapper
{
	protected function getEntityClass(): string
	{
		return Core\eventoption\EventOption::class;
	}

	protected function getDataManagerResult(array $params): Result
	{
		$params['select'] = $params['select'] ?? ["*"];

		return OpenEventOptionTable::getList($params);
	}

	protected function getOneEntityByFilter(array $filter): ?object
	{
		$eventData = OpenEventOptionTable::query()
			->setFilter($filter)
			->setSelect(['*'])
			->fetchObject();

		if ($eventData)
		{
			return $this->convertToObject($eventData);
		}

		return null;
	}

	protected function convertToObject($objectEO): ?EntityInterface
	{
		return (new EventOptionBuilderFromObject($objectEO))->build();
	}

	protected function getEntityName(): string
	{
		return 'event_option';
	}

	protected function createEntity($entity, array $params = []): ?EntityInterface
	{
		$arrayEntity = $this->entityToArray($entity);
		$result = OpenEventOptionTable::add($arrayEntity);

		if ($result->isSuccess())
		{
			$entity->setId($result->getId());

			return $entity;
		}

		throw new Core\Base\BaseException('Error of create event option');
	}

	/**
	 * @param Core\EventOption\EventOption $entity
	 */
	protected function updateEntity($entity, array $params = [
		'updateAttendeesCounter' => false,
	]): ?EntityInterface
	{
		$arrayEntity = $this->entityToArray($entity);

		if ($params['updateAttendeesCounter'] ?? null)
		{
			$arrayEntity['ATTENDEES_COUNT'] = $entity->getAttendeesCount();
		}

		$result = OpenEventOptionTable::update(
			$entity->getId(),
			$arrayEntity
		);

		if ($result->isSuccess())
		{
			return $entity;
		}

		throw new Core\Base\BaseException('Error of update event option');
	}

	protected function deleteEntity(EntityInterface $entity, array $params): ?EntityInterface
	{
		throw new Core\Base\BaseException('Delete of event option not implemented');
	}

	private function entityToArray($entity): array
	{
		return [
			'EVENT_ID' => $entity->getEventId(),
			'CATEGORY_ID' => $entity->getCategoryId(),
			'THREAD_ID' => $entity->getThreadId(),
			'OPTIONS' => json_encode($entity->getOptions()),
		];
	}
}
