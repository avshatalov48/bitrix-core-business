<?php

namespace Bitrix\Mail;

use Bitrix\Mail\Internals\MessageAccessTable;
use \Bitrix\Crm\ActivityTable;

/**
 * @see \Bitrix\Mail\Helper\MessageAccess
 */
class MessageAccess
{
	// supported entity types
	public const ENTITY_TYPE_IM_CHAT = MessageAccessTable::ENTITY_TYPE_IM_CHAT;
	public const ENTITY_TYPE_CALENDAR_EVENT = MessageAccessTable::ENTITY_TYPE_CALENDAR_EVENT;

	/** @var int */
	private $userId;

	/** @var \Bitrix\Mail\Item\Message */
	private $message;

	/** @var IMessageAccessStorage */
	private $storage;

	protected function __construct(int $userId, \Bitrix\Mail\Item\Message $message, IMessageAccessStorage $storage)
	{
		$this->userId = $userId;
		$this->message = $message;
		$this->storage = $storage;
	}

	public static function createForMessage(\Bitrix\Mail\Item\Message $message, int $userId): self
	{
		$storage = new \Bitrix\Mail\Storage\MessageAccess();
		return new self($userId, $message, $storage);
	}

	public static function createByMessageId(int $messageId, int $userId): self
	{
		$messageStorage = new \Bitrix\Mail\Storage\Message();
		$message = $messageStorage->getMessage($messageId);
		return self::createForMessage($message, $userId);
	}

	/**
	 * @param \Bitrix\Mail\Item\Message|int $message message id or message item
	 * @return static
	 * @throws \Exception
	 */
	public static function createForCurrentUser($message): self
	{
		global $USER;
		$userId = $USER->GetID();

		if (!$userId)
		{
			throw new \Bitrix\Main\SystemException('message access: user id error');
		}

		return $message instanceof \Bitrix\Mail\Item\Message
			? self::createForMessage($message, $userId)
			: self::createByMessageId((int)$message, $userId);
	}

	public function isOwner(): bool
	{
		return (bool)self::getUserMailbox($this->getMessage()->getMailboxId(), $this->getUserId());
	}

	public static function getCrmEntityOwner($activityId): array
	{
		return ActivityTable::getList([
			'select' => [
				'OWNER_ID',
				'OWNER_TYPE_ID',
			],
			'filter' => [
				'==ID' => $activityId,
			],
			'limit' => 1,
		])->fetch();
	}

	public function getEntitiesForType($entityType): array
	{
		$collection = $this->getCollection($this->getMessage());

		$bindings = [];

		/** @var \Bitrix\Mail\Item\MessageAccess $item */
		foreach ($collection as $item)
		{
			if ($item->getEntityType() === $entityType)
			{
				$bindings[] = $item->getEntityId();
			}
		}

		return $bindings;
	}

	/**
	 * @return bool
	 * @todo optionally implement for other entity types
	 */
	public function canViewMessage(): bool
	{
		if ($this->isOwner())
		{
			return true;
		}
		
		$collection = $this->getCollection($this->getMessage());

		/** @var \Bitrix\Mail\Item\MessageAccess $item */
		foreach ($collection as $item)
		{
			switch ($item->getEntityType())
			{
				case self::ENTITY_TYPE_IM_CHAT:
					if (\Bitrix\Mail\Helper\MessageAccess::checkAccessForChat($item->getEntityId(), $this->getUserId()))
					{
						return true;
					}
					break;
				case self::ENTITY_TYPE_CALENDAR_EVENT:
					if (\Bitrix\Mail\Helper\MessageAccess::checkAccessForCalendarEvent($item->getEntityId(), $this->getUserId()))
					{
						return true;
					}
					break;
			}
		}

		return false;
	}

	public function canModifyMessage(): bool
	{
		return $this->isOwner();
	}

	private function getMessage(): \Bitrix\Mail\Item\Message
	{
		return $this->message;
	}

	private function getUserId(): int
	{
		return $this->userId;
	}

	private function getStorage(): IMessageAccessStorage
	{
		return $this->storage;
	}

	private function getCollection(\Bitrix\Mail\Item\Message $item): \Bitrix\Mail\Collection\MessageAccess
	{
		return $this->getStorage()->getCollectionForMessage($item);
	}

	private static function getUserMailbox(int $mailboxId, int $userId)
	{
		return MailboxTable::getUserMailbox($mailboxId, $userId);
	}

}