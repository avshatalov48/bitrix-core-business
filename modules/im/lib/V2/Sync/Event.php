<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\Model\EO_Log_Collection;
use Bitrix\Main\Type\DateTime;

class Event
{
	public const DELETE_EVENT = 'delete';
	public const COMPLETE_DELETE_EVENT = 'completeDelete';
	public const ADD_EVENT = 'add';
	public const READ_ALL_EVENT = 'readAll';
	public const CHAT_ENTITY = 'chat';
	public const PIN_MESSAGE_ENTITY = 'pin';
	public const MESSAGE_ENTITY = 'message';
	public const UPDATED_MESSAGE_ENTITY = 'updatedMessage';

	private ?int $id;
	private DateTime $dateCreate;
	private ?DateTime $dateDelete;
	public string $entityType;
	public int $entityId;
	public string $eventName;

	public function __construct(
		string $eventName,
		string $entityType,
		int $entityId,
		?DateTime $dateDelete = null,
		?DateTime $dateCreate = null,
		?int $id = null
	)
	{
		$dateCreate ??= new DateTime();
		if ($dateDelete === null)
		{
			$dateDelete = clone $dateCreate;
			$dateDelete->add(Logger::DEFAULT_EXPIRY_INTERVAL);
		}
		$this->eventName = $eventName;
		$this->entityType = $entityType;
		$this->entityId = $entityId;
		$this->dateDelete = $dateDelete;
		$this->dateCreate = $dateCreate;
		$this->id = $id;
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getDateCreate(): DateTime
	{
		return $this->dateCreate;
	}

	public function getDateDelete(): ?DateTime
	{
		return $this->dateDelete;
	}

	/**
	 * @param EO_Log_Collection $logCollection
	 * @return self[]
	 */
	public static function initByOrmEntities(EO_Log_Collection $logCollection): array
	{
		$events = [];

		foreach ($logCollection as $logItem)
		{
			$events[] = new self(
				$logItem->getEvent(),
				$logItem->getEntityType(),
				$logItem->getEntityId(),
				$logItem->getDateCreate(),
				$logItem->getDateDelete(),
				$logItem->getId()
			);
		}

		return $events;
	}
}