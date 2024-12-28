<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab\Entity;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;

if (!Loader::includeModule('socialnetwork'))
{
	return;
}

final class EventEntity extends CollabEntity
{
	protected ?Event $internalObject = null;

	public function __construct(int $id, mixed $internalObject = null)
	{
		if ($internalObject instanceof Event)
		{
			$this->internalObject = $internalObject;
		}

		parent::__construct($id, $internalObject);
	}

	public function getType(): EntityType
	{
		return EntityType::CalendarEvent;
	}

	public function getData(): array
	{
		return $this->internalObject->toArray();
	}

	/**
	 * @return Collab|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected function fillCollab(): ?Collab
	{
		if ($this->internalObject->getSpecialLabel() !== Dictionary::EVENT_TYPE['collab'])
		{
			return null;
		}

		if (
			$this->internalObject->isBaseEvent()
			&& $this->internalObject->getCalendarType() !== Dictionary::CALENDAR_TYPE['group']
		)
		{
			return null;
		}

		if ($this->internalObject->isBaseEvent())
		{
			$collabId = $this->internalObject->getOwner()->getId();
		}
		else
		{
			$parentEvent = EventTable::query()
				->setSelect(['CAL_TYPE', 'OWNER_ID'])
				->where('ID', $this->internalObject->getParentId())
				->fetchObject()
			;

			if ($parentEvent->getCalType() !== Dictionary::CALENDAR_TYPE['group'])
			{
				return null;
			}

			$collabId = $parentEvent->getOwnerId();
		}

		return $this->collabRegistry->get($collabId);
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	protected function checkInternalEntity(): bool
	{
		if ($this->internalObject !== null)
		{
			return true;
		}

		/** @var Mappers\Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$internalObject = $mapperFactory->getEvent()->getById($this->id);

		if (!$internalObject)
		{
			return false;
		}

		$this->internalObject = $internalObject;

		return true;
	}
}