<?php

declare(strict_types=1);

namespace Bitrix\Forum\Integration\Socialnetwork\Collab\Entity;

use Bitrix\Forum\Message;
use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntity;
use Bitrix\Socialnetwork\Collab\Entity\CollabEntityFactory;
use Bitrix\Socialnetwork\Collab\Entity\Type\EntityType;
use Throwable;

if (!Loader::includeModule('socialnetwork'))
{
	return;
}

class CommentEntity extends CollabEntity
{
	protected ?Message $internalObject = null;

	public function __construct(int $id, mixed $internalObject = null)
	{
		if ($internalObject instanceof Message)
		{
			$this->internalObject = $internalObject;
		}

		parent::__construct($id, $internalObject);
	}

	public function getType(): EntityType
	{
		return EntityType::Comment;
	}

	public function getData(): array
	{
		return (array)$this->internalObject->getData();
	}

	protected function fillCollab(): ?Collab
	{
		$xmlId = $this->internalObject->getXmlId();

		[$entityType, $entityId] = explode('_', $xmlId);

		$entityId = (int)$entityId;

		if ($entityId <= 0)
		{
			return null;
		}

		$linkedEntity =  $this->getLinkedCollabEntity($entityType, $entityId);

		return $linkedEntity?->getCollab();
	}

	protected function getLinkedCollabEntity(string $entityType, int $entityId): ?CollabEntity
	{
		$type = match ($entityType)
		{
			'EVENT' => EntityType::CalendarEvent,
			'TASK' => EntityType::Task,
			default => null,
		};

		if ($type === null)
		{
			return null;
		}

		return CollabEntityFactory::getById($entityId, $type);
	}

	protected function checkInternalEntity(): bool
	{
		if ($this->internalObject !== null)
		{
			return true;
		}

		try
		{
			$message = new Message($this->id);
		}
		catch (Throwable)
		{
			return false;
		}

		$this->internalObject = $message;

		return true;
	}
}