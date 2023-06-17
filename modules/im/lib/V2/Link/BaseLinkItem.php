<?php

namespace Bitrix\Im\V2\Link;

use Bitrix\Im\V2\ActiveRecord;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\FieldAccessImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\RegistryEntry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\RestEntity;
use Bitrix\Main\Type\DateTime;

abstract class BaseLinkItem implements LinkItem, \ArrayAccess, ActiveRecord, RegistryEntry
{
	use FieldAccessImplementation;
	use ActiveRecordImplementation;
	use RegistryEntryImplementation;

	protected ?int $id = null;
	protected int $authorId;
	protected ?int $messageId = null;
	protected int $chatId;
	protected ?int $entityId;
	protected DateTime $dateCreate;
	protected RestEntity $entity;

	/**
	 * @return string|RestEntity
	 */
	abstract public static function getEntityClassName(): string;
	abstract protected static function getEntityIdFieldName(): string;

	/**
	 * @param RestEntity $entity
	 * @param Message $message
	 * @return static
	 */
	public static function linkEntityToMessage(RestEntity $entity, Message $message): self
	{
		$link = new static();

		return $link->setEntity($entity)->setMessageInfo($message);
	}

	public function toRestFormatIdOnly(): array
	{
		return [
			'linkId' => $this->getPrimaryId(),
			'chatId' => $this->getChatId(),
			static::getEntityClassName()::getRestEntityName() . 'Id' => $this->getEntityId()
		];
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		$data = new PopupData([new UserPopupItem([$this->getAuthorId()])], $excludedList);

		return $data->mergeFromEntity($this->getEntity(), $excludedList);
	}

	//region Getters & setters

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(int $id): BaseLinkItem
	{
		$this->id = $id;
		return $this;
	}

	public function getPrimaryId(): ?int
	{
		return $this->getId();
	}

	public function setPrimaryId(int $primaryId): self
	{
		$this->setId($primaryId);
		return $this;
	}

	public function getAuthorId(): int
	{
		return $this->authorId;
	}

	public function setAuthorId(int $authorId): BaseLinkItem
	{
		$this->authorId = $authorId;
		return $this;
	}

	public function getMessageId(): ?int
	{
		return $this->messageId;
	}

	public function setMessageId(?int $messageId): BaseLinkItem
	{
		$this->messageId = $messageId;
		return $this;
	}

	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function setChatId(int $chatId): BaseLinkItem
	{
		$this->chatId = $chatId;
		return $this;
	}

	public function getEntityId(): ?int
	{
		return $this->entityId;
	}

	public function setEntityId(?int $entityId): BaseLinkItem
	{
		$this->entityId = $entityId;
		return $this;
	}

	public function getDateCreate(): DateTime
	{
		return $this->dateCreate;
	}

	public function setDateCreate(DateTime $dateCreate): BaseLinkItem
	{
		$this->dateCreate = $dateCreate;
		return $this;
	}

	/**
	 * @return RestEntity
	 */
	public function getEntity(): RestEntity
	{
		return $this->entity;
	}

	/**
	 * @param RestEntity $entity
	 * @return static
	 */
	public function setEntity(RestEntity $entity): self
	{
		$this->entity = $entity;
		$this->setEntityId($entity->getId());

		return $this;
	}

	public function setMessageInfo(Message $message): self
	{
		$this->setMessageId($message->getMessageId());
		$this->setChatId($message->getChatId());
		$this->setAuthorId($message->getAuthorId());

		return $this;
	}

	//endregion

	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'id',
				'set' => 'setId', /** @see BaseLinkItem::setId */
				'get' => 'getId', /** @see BaseLinkItem::getId */
			],
			'MESSAGE_ID' => [
				'field' => 'messageId',
				'set' => 'setMessageId', /** @see BaseLinkItem::setMessageId */
				'get' => 'getMessageId', /** @see BaseLinkItem::getMessageId */
			],
			'CHAT_ID' => [
				'field' => 'chatId',
				'set' => 'setChatId', /** @see BaseLinkItem::setChatId */
				'get' => 'getChatId', /** @see BaseLinkItem::getChatId */
			],
			'DATE_CREATE' => [
				'field' => 'dateCreate',
				'set' => 'setDateCreate', /** @see BaseLinkItem::setDateCreate */
				'get' => 'getDateCreate', /** @see BaseLinkItem::getDateCreate */
			],
			'AUTHOR_ID' => [
				'field' => 'authorId',
				'set' => 'setAuthorId', /** @see BaseLinkItem::setAuthorId */
				'get' => 'getAuthorId', /** @see BaseLinkItem::getAuthorId */
			],
			static::getEntityIdFieldName() => [
				'field' => 'entityId',
				'set' => 'setEntityId', /** @see BaseLinkItem::setEntityId */
				'get' => 'getEntityId', /** @see BaseLinkItem::getEntityId */
			]
		];
	}

	public function toRestFormat(array $option = []): array
	{
		$startId = Chat::getInstance($this->getChatId())->getStartId();

		return [
			'id' => $this->getPrimaryId(),
			'messageId' => ($this->getMessageId() >= $startId) ? $this->getMessageId() : 0,
			'chatId' => $this->getChatId(),
			'authorId' => $this->getAuthorId(),
			'dateCreate' => $this->getDateCreate()->format('c'),
			static::getEntityClassName()::getRestEntityName() => $this->getEntity()->toRestFormat($option),
		];
	}
}