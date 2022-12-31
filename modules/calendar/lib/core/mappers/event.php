<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Internals\EO_Event;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Data\AddResult;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use CCalendar;
use CCalendarEvent;

class Event extends Mapper
{
	/**
	 * @param array $ids
	 * @param int $ownerId
	 * @param array $fields
	 *
	 * @return Core\Event\EventCollection
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 * @deprecated is it unused?
	 */
	public function getCollectionByIds(array $ids, int $ownerId, array $fields = ['*']): Core\Event\EventCollection
	{
		$eventDM = EventTable::query()
			->whereIn('ID', $ids)
			->where('OWNER_ID', $ownerId)
			->setSelect($fields)
			->exec()
		;

		$collection = new Core\Event\EventCollection();

		while ($event = $eventDM->fetch())
		{
			$collection->add((new Core\Builders\EventBuilderFromArray($event))->build());
		}

		return $collection;
	}

	/**
	 * @param $entity
	 * @param array $params
	 * @return Core\Base\EntityInterface|null
	 * @throws ArgumentException
	 * @throws Core\Event\Tools\PropertyException
	 */
	protected function createEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$entity->setDateModified(new Core\Base\Date());
		$result = $this->save($entity, $params);

		if ($result->isSuccess())
		{
			// TODO: perhaps need to setup date create and date update
			return $this->getById($result->getId());
		}

		return null;
	}

	/**
	 * @param $entity
	 * @param array $params
	 * @return Core\Base\EntityInterface|null
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 * @throws Core\Event\Tools\PropertyException
	 */
	protected function updateEntity($entity, array $params = []): ?Core\Base\EntityInterface
	{
		$entity->setDateModified(new Core\Base\Date());
		$data = $this->convertToArray($entity);

		$params = array_merge($params, [
			'arFields' => $data,
			'originalFrom' => $params['originalFrom'] ?? null,
			'checkPermissions' => false,
			'userId' => $params['userId']
				?? $entity->getOwner() ? $entity->getOwner()->getId() : null,
		]);

		// TODO: in the future change it to call EventTable::update()
		if (($id = CCalendar::SaveEvent($params)) && is_numeric($id))
		{
			return $this->getById((int)$id);
		}

		return null;
	}

	/**
	 * @param Core\Event\Event $entity
	 * @param array $params
	 *
	 * @return Core\Base\EntityInterface|null
	 *
	 * @throws Core\Base\BaseException
	 * @throws Core\Event\Tools\PropertyException
	 */
	protected function deleteEntity(Core\Base\EntityInterface $entity, array $params): ?Core\Base\EntityInterface
	{
		$params = array_merge($params,[
			'Event' => $this->prepareArrayForDelete($entity),
			'id' => $entity->getId(),
			'checkPermissions' => false,
			'bMarkDeleted' => true,
			'userId' => $params['userId']
				?? $entity->getOwner() ? $entity->getOwner()->getId() : null,
		]);

		if (CCalendar::DeleteEvent($entity->getId(), true, $params) && !empty($params['bMarkDeleted']))
		{
			$entity->setIsDeleted(true);

			return $entity;
		}

		return null;
	}

	/**
	 * @param Core\Event\Event $event
	 *
	 * @return array|null
	 */
	private function prepareArrayForDelete(Core\Event\Event $event): ?array
	{
		try
		{
			return $this->convertToArray($event);
		}
		catch(\Throwable $e)
		{
		    return null;
		}
	}

	/**
	 * @param Core\Event\Event $event
	 * @param array $params
	 *
	 * @return AddResult
	 *
	 * @throws Core\Event\Tools\PropertyException
	 */
	private function save(Core\Event\Event $event, array $params = []): AddResult
	{
		$data = $this->convertToArray($event);

		$params = array_merge($params, [
			'arFields' => $data,
			'checkPermissions' => false,
			'userId' => $event->getOwner() ? $event->getOwner()->getId() : null, // todo how get userId ?
		]);

		$id = CCalendar::SaveEvent($params);

		$result = new AddResult();
		if ($id)
		{
			$result->setPrimary($id);
		}
		else
		{
			$result->addError(new Error('Error of create event'));
		}

		return $result;
	}

	/**
	 * @param Core\Event\Properties\RemindCollection $collection
	 * @param Core\Base\Date $start
	 *
	 * @return array
	 *
	 * @throws Core\Event\Tools\PropertyException
	 */
	private function prepareReminders(Core\Event\Properties\RemindCollection $collection, Core\Base\Date $start): array
	{
		$result = [];
		$collection->setEventStart(clone $start);
		$collection->deDuplicate();
		/** @var Core\Event\Properties\Remind $reminder */
		foreach ($collection->getCollection() as $reminder)
		{
			$remind = [
				'before' => null,
				'count' => null,
				'time' => null,
				'value' => null,
			];
			if ($reminder->getDaysBefore() !== null)
			{
				$remind['type'] = Core\Event\Properties\Remind::UNIT_DAY_BEFORE;
				$remind['before'] = $reminder->getDaysBefore();
				$remind['time'] = $reminder->getTimeOffset();
			}
			elseif ($reminder->isSimpleType())
			{
				$remind['type'] = str_replace('minutes', 'min', $reminder->getUnits()) ;
				$remind['count'] = $reminder->getTime();
			}
			else
			{
				$remind['type'] = Core\Event\Properties\Remind::UNIT_DATES;
				$remind['value'] = $reminder->getSpecificTime();
			}
			$result[] = $remind;
		}

		return $result;
	}

	/**
	 * @param Core\Event\Event $event
	 *
	 * @return array
	 *
	 * @throws Core\Event\Tools\PropertyException
	 */
	private function convertToArray(Core\Event\Event $event): array
	{
		return [
			'ID'                 => $event->getId(),
			'ACTIVE'             => $event->isActive() ? 'Y' : 'N',
			'DELETED'            => $event->isDeleted() ? 'Y' : 'N',
			'DT_SKIP_TIME'       => $event->isFullDayEvent() ? 'Y' : 'N',
			'DAV_XML_ID'         => $event->getUid(),
			'TZ_FROM'            => (string)$event->getStartTimeZone(),
			'TZ_TO'              => (string)$event->getEndTimeZone(),
			'NAME'               => $event->getName(),
			'DATE_FROM'          => (string)$event->getStart(),
			'DATE_TO'            => (string)$event->getEnd(),
			'ORIGINAL_DATE_FROM' => $this->prepareOriginalDateFrom($event),
			'DESCRIPTION'        => $event->getDescription(),
			'ACCESSIBILITY'      => $event->getAccessibility(),
			'PRIVATE_EVENT'      => $event->isPrivate(), // TODO: add converter
			'IMPORTANCE'         => $event->getImportance(), // TODO: add converter
			'OWNER_ID'           => $event->getOwner() ? $event->getOwner()->getId() : null,
			'CREATED_BY'         => $event->getOwner() ? $event->getOwner()->getId() : null,
			'CAL_TYPE'           => $event->getCalendarType(),
			'EVENT_TYPE'         => $event->getSpecialLabel(),
			'DATE_CREATE'        => $event->getDateCreate() ? (string) $event->getDateCreate() : (string)new Core\Base\Date(),
			'LOCATION'           => $event->getLocation() ? $event->getLocation()->getActualLocation() : '',
			'REMIND'             => ($event->getRemindCollection() && $event->getRemindCollection()->count())
				? $this->prepareReminders($event->getRemindCollection(), $event->getStart())
				: null,
			'RRULE'              => $event->isRecurrence()
				? $event->getRecurringRule()->toArray()
				: null,
			'EXDATE'             => $event->getExcludedDateCollection()
				? $event->getExcludedDateCollection()->toString()
				: null,
			'RECURRENCE_ID'      => $event->getRecurrenceId(),
			'IS_MEETING'         => $event->isMeeting(),
			'MEETING_STATUS'     => $event->getMeetingStatus(),
			'MEETING_HOST'       => $event->getEventHost() ? $event->getEventHost()->getId() : null,
			'MEETING'            => $event->getMeetingDescription() ? $event->getMeetingDescription()->getFields() : null,
			'ATTENDEES_CODES'    => $event->getAttendeesCollection()->getFields()['attendeesCodesCollection'],
			'SECTIONS' 			 => $event->getSection() ? [$event->getSection()->getId()] : null,
			'SECTION_ID'         => $event->getSection() ? $event->getSection()->getId() : null,
			'RELATIONS' 		 => $event->getRelations() ? $event->getRelations()->getFields() : null,
		];
	}

	/**
	 * @param array $filter
	 *
	 * @return object|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getOneEntityByFilter(array $filter): ?object
	{
		if ($eventData = EventTable::query()
			->setFilter($filter)
			->setSelect(['*'])
			->fetchObject()
		)
		{
			return $this->convertToObject($eventData);
		}

		return null;
	}

	/**
	 * @param EO_Event $objectEO
	 *
	 * @return Core\Section\Section
	 */
	protected function convertToObject($objectEO): Core\Base\EntityInterface
	{
		return (new Core\Builders\EventBuilderFromEntityObject($objectEO))->build();
	}

	/**
	 * @return string
	 */
	protected function getEntityName(): string
	{
		return 'event';
	}

	/**
	 * @return string
	 */
	protected function getMapClass(): string
	{
		return Core\Event\EventMap::class;
	}

	/**
	 * @param array $params
	 *
	 * @return Result
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	protected function getDataManagerResult(array $params): Result
	{
		$params['select'] = $params['select'] ?? ["*"];
		return EventTable::getList($params);
	}
	/**
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getMapFullChainByParentId(int $id, int $ownerId, array $fields = ['*']): Core\Event\EventMap
	{
		$eventDM = EventTable::query()
			->where(Query::filter()
				->logic('or')
				->where('PARENT_ID', $id)
				->where('RECURRENCE_ID', $id)
			)
			->where('OWNER_ID', $ownerId)
			->setSelect($fields)
			->exec()
		;

		$eventMap = new Core\Event\EventMap();

		while ($event = $eventDM->fetch())
		{
			$eventMap->add((new Core\Builders\EventBuilderFromArray($event))->build(), (int)$event['ID']);
		}

		return $eventMap;
	}

	/**
	 * @param int $id
	 * @param array|null $additionalParams
	 *
	 * @return Core\Event\Event|null
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getEntity(int $id, ?array $additionalParams = null): ?Core\Event\Event
	{
		$eventDM = EventTable::query()
			->where('ID', $id)
		;

		if ($additionalParams)
		{
			//filter
			if (isset($additionalParams['filter'])
				&& is_iterable($additionalParams['filter'])
			)
			{
				foreach ($additionalParams['filter'] as $filter)
				{
					$eventDM->addFilter($filter['key'], $filter['value']);
				}
			}

			//select
			if (isset($additionalParams['fields'])
				&& is_array($additionalParams['fields'])
			)
			{
				$eventDM->setSelect($additionalParams['fields']);
			}
			else
			{
				$eventDM->setSelect(['*']);
			}
		}
		else
		{
			$eventDM->setSelect(['*']);
		}

		$event = $eventDM->exec()->fetchObject();

		return $event
				? (new Core\Builders\EventBuilderFromEntityObject($event))->build()
				: null
		;
	}

	/**
	 * @param Core\Event\Event $event
	 * @param array $fields
	 * @return Core\Event\Event
	 * @throws Core\Base\BaseException
	 */
	public function patch(Core\Event\Event $event, array $fields): Core\Event\Event
	{
		$eventFields = [
			'EXDATE' => (string)$event->getExcludedDateCollection(),
		];

		$result = EventTable::update(
			$event->getId(),
			array_intersect_key($eventFields, array_flip($fields))
		);

		if ($result->isSuccess())
		{
			return $event;
		}

		throw new Core\Base\BaseException('do not patch event');
	}

	/**
	 * @param array $fields
	 *
	 * @return Core\Event\Event|mixed
	 *
	 * @throws ArgumentException
	 */
	public function getByArray(array $fields)
	{
		if ($this->getCacheMap()->has($fields['ID']))
		{
			return $this->getCacheMap()->getItem($fields['ID']);
		}

		$entity = $this->convertFromArray($fields);
		$this->getCacheMap()->add($entity, $entity->getId());

		return $entity;
	}

	/**
	 * @param array $fields
	 *
	 * @return Core\Event\Event
	 */
	protected function convertFromArray(array $fields): Core\Event\Event
	{
		return (new Core\Builders\EventBuilderFromArray($fields))->build();
	}

	/**
	 * @return string
	 */
	protected function getEntityClass(): string
	{
		return Core\Event\Event::class;
	}

	/**
	 * @param Core\Event\Event $event
	 *
	 * @return string|null
	 */
	private function prepareOriginalDateFrom(Core\Event\Event $event): ?string
	{
		$result = null;
		if ($event->getOriginalDateFrom())
		{
			if ($event->getStartTimeZone())
			{
				$event->getOriginalDateFrom()->setTimezone($event->getStartTimeZone()->getTimeZone());
			}
			$result = (string)$event->getOriginalDateFrom();
		}

		return $result;
	}
}
