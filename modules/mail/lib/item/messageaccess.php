<?php

namespace Bitrix\Mail\Item;

class MessageAccess extends Base
{
	private const FIELD_TOKEN = 'TOKEN';
	private const FIELD_SECRET = 'SECRET';
	private const FIELD_MESSAGE_ID = 'MESSAGE_ID';
	private const FIELD_MAILBOX_ID = 'MAILBOX_ID';
	private const FIELD_ENTITY_TYPE = 'ENTITY_TYPE';
	private const FIELD_ENTITY_ID = 'ENTITY_ID';
	private const FIELD_ENTITY_UF_ID = 'ENTITY_UF_ID';
	private const FIELD_ENTITY_UF_TYPE = 'ENTITY_UF_TYPE';
	private const FIELD_ENTITY_OPTIONS = 'OPTIONS';

	/** @var string */
	private $token;
	/** @var string */
	private $secret;
	/** @var int */
	private $messageId;
	/** @var int */
	private $mailboxId;
	/** @var string */
	private $entityType;
	/** @var int */
	private $entityId;
	/** @var int */
	private $entityUfId;
	/** @var string */
	private $entityUfType;
	/** @var string */
	private $options;

	protected function __construct(string $token, string $secret, int $mailboxId, int $messageId)
	{
		$this->token = $token;
		$this->secret = $secret;
		$this->mailboxId = $mailboxId;
		$this->messageId = $messageId;
	}

	public static function fromArray(array $array): self
	{
		if (!isset(
			$array[self::FIELD_TOKEN],
			$array[self::FIELD_SECRET],
			$array[self::FIELD_MAILBOX_ID],
			$array[self::FIELD_MESSAGE_ID]
		))
		{
			throw new \Bitrix\Main\SystemException('message access field error');
		}

		$item = new self($array[self::FIELD_TOKEN], $array[self::FIELD_SECRET], (int)$array[self::FIELD_MAILBOX_ID], (int)$array[self::FIELD_MESSAGE_ID]);

		$item->entityType = $array[self::FIELD_ENTITY_TYPE] ?? '';
		$item->entityId = (int)$array[self::FIELD_ENTITY_ID] ?? '';
		$item->entityUfType = $array[self::FIELD_ENTITY_UF_TYPE] ?? '';
		$item->entityUfId = (int)$array[self::FIELD_ENTITY_UF_ID] ?? '';
		$item->options = $array[self::FIELD_ENTITY_OPTIONS] ?? '';

		return $item;
	}

	/**
	 * @return string
	 */
	public function getToken(): string
	{
		return $this->token;
	}

	/**
	 * @return string
	 */
	public function getSecret(): string
	{
		return $this->secret;
	}

	/**
	 * @return int
	 */
	public function getMessageId(): int
	{
		return $this->messageId;
	}

	/**
	 * @return int
	 */
	public function getMailboxId(): int
	{
		return $this->mailboxId;
	}

	/**
	 * @return string
	 */
	public function getEntityType(): string
	{
		return $this->entityType;
	}

	/**
	 * @return int
	 */
	public function getEntityId(): int
	{
		return $this->entityId;
	}

	/**
	 * @return int
	 */
	public function getEntityUfId(): int
	{
		return $this->entityUfId;
	}

	/**
	 * @return string
	 */
	public function getEntityUfType(): string
	{
		return $this->entityUfType;
	}

	/**
	 * @return string
	 */
	public function getOptions(): string
	{
		return $this->options;
	}

	public function toArray(): array
	{
		return [
			self::FIELD_TOKEN => $this->getToken(),
			self::FIELD_SECRET => $this->getSecret(),
			self::FIELD_MESSAGE_ID => $this->getMessageId(),
			self::FIELD_MAILBOX_ID => $this->getMailboxId(),
			self::FIELD_ENTITY_TYPE => $this->getEntityType(),
			self::FIELD_ENTITY_ID => $this->getEntityId(),
			self::FIELD_ENTITY_UF_ID => $this->getEntityUfId(),
			self::FIELD_ENTITY_UF_TYPE => $this->getEntityUfType(),
			self::FIELD_ENTITY_OPTIONS => $this->getOptions(),
		];
	}
}