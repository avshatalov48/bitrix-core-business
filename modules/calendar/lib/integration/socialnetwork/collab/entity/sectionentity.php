<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab\Entity;

use Bitrix\Calendar\Core\Event\Tools\Dictionary;
use Bitrix\Calendar\Core\Mappers;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;

if (!Loader::includeModule('socialnetwork'))
{
	return;
}

final class SectionEntity extends CollabEntity
{
	protected ?Section $internalObject = null;

	public function __construct(int $id, mixed $internalObject = null)
	{
		if ($internalObject instanceof Section)
		{
			$this->internalObject = $internalObject;
		}

		parent::__construct($id, $internalObject);
	}

	public function getType(): EntityType
	{
		return EntityType::CalendarSection;
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
		if (
			$this->internalObject?->getType() !== Dictionary::CALENDAR_TYPE['group']
			|| !$this->internalObject?->getOwner()?->getId()
		)
		{
			return null;
		}

		return $this->collabRegistry->get($this->internalObject?->getOwner()?->getId());
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
		$internalObject = $mapperFactory->getSection()->getById($this->id);

		if (!$internalObject)
		{
			return false;
		}

		$this->internalObject = $internalObject;

		return true;
	}
}
