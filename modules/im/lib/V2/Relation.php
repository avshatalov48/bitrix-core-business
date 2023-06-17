<?php

namespace Bitrix\Im\V2;

use ArrayAccess;
use Bitrix\Im\Model\RelationTable;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Common\FieldAccessImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\Entity\User\User;

class Relation implements ArrayAccess, RegistryEntry, ActiveRecord
{
	use FieldAccessImplementation;
	use ActiveRecordImplementation;
	use RegistryEntryImplementation;
	use ContextCustomer;

	protected ?int $id = null;
	protected ?int $chatId = null;
	protected ?string $messageType = null;
	protected ?int $userId = null;
	protected ?int $startId = null;
	protected ?int $unreadId = null;
	protected ?int $lastId = null;
	protected ?int $lastSendId = null;
	protected ?int $lastFileId = null;
	protected ?int $lastRead = null;
	protected ?int $status = null;
	protected ?int $callStatus = null;
	protected ?string $messageStatus = null;
	protected ?bool $notifyBlock = null;
	protected ?bool $manager = null;
	protected ?int $counter = null;
	protected ?int $startCounter = null;
	protected ?User $user = null;

	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public function getPrimaryId(): ?int
	{
		return $this->getId();
	}

	public function setPrimaryId(int $primaryId): self
	{
		return $this->setId($primaryId);
	}

	public static function getDataClass(): string
	{
		return RelationTable::class;
	}

	public function getUser(): User
	{
		if (isset($this->user))
		{
			return $this->user;
		}

		$this->user = User::getInstance($this->getUserId());

		return $this->user;
	}

	public function getChat(): Chat
	{
		return Chat::getInstance($this->getChatId());
	}

	/**
	 * @return array<array>
	 */
	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'id',
				'set' => 'setId', /** @see Relation::setId */
				'get' => 'getId', /** @see Relation::getId */
			],
			'CHAT_ID' => [
				'field' => 'chatId',
				'set' => 'setChatId', /** @see Relation::setChatId */
				'get' => 'getChatId', /** @see Relation::getChatId */
			],
			'MESSAGE_TYPE' => [
				'field' => 'messageType',
				'set' => 'setMessageType', /** @see Relation::setMessageType */
				'get' => 'getMessageType', /** @see Relation::getMessageType */
			],
			'USER_ID' => [
				'field' => 'userId',
				'set' => 'setUserId', /** @see Relation::setUserId */
				'get' => 'getUserId', /** @see Relation::getUserId */
			],
			'START_ID' => [
				'field' => 'startId',
				'set' => 'setStartId', /** @see Relation::setStartId */
				'get' => 'getStartId', /** @see Relation::getStartId */
			],
			'UNREAD_ID' => [
				'field' => 'unreadId',
				'set' => 'setUnreadId', /** @see Relation::setUnreadId */
				'get' => 'getUnreadId', /** @see Relation::getUnreadId */
			],
			'LAST_ID' => [
				'field' => 'lastId',
				'set' => 'setLastId', /** @see Relation::setLastId */
				'get' => 'getLastId', /** @see Relation::getLastId */
			],
			'LAST_SEND_ID' => [
				'field' => 'lastSendId',
				'set' => 'setLastSendId', /** @see Relation::setLastSendId */
				'get' => 'getLastSendId', /** @see Relation::getLastSendId */
			],
			'LAST_FILE_ID' => [
				'field' => 'lastFileId',
				'set' => 'setLastFileId', /** @see Relation::setLastFileId */
				'get' => 'getLastFileId', /** @see Relation::getLastFileId */
			],
			'LAST_READ' => [
				'field' => 'lastRead',
				'set' => 'setLastRead', /** @see Relation::setLastRead */
				'get' => 'getLastRead', /** @see Relation::getLastRead */
			],
			'STATUS' => [
				'field' => 'status',
				'set' => 'setStatus', /** @see Relation::setStatus */
				'get' => 'getStatus', /** @see Relation::getStatus */
			],
			'CALL_STATUS' => [
				'field' => 'callStatus',
				'set' => 'setCallStatus', /** @see Relation::setCallStatus */
				'get' => 'getCallStatus', /** @see Relation::getCallStatus */
			],
			'MESSAGE_STATUS' => [
				'field' => 'messageStatus',
				'set' => 'setMessageStatus', /** @see Relation::setMessageStatus */
				'get' => 'getMessageStatus', /** @see Relation::getMessageStatus */
			],
			'NOTIFY_BLOCK' => [
				'field' => 'notifyBlock',
				'set' => 'setNotifyBlock', /** @see Relation::setNotifyBlock */
				'get' => 'getNotifyBlock', /** @see Relation::getNotifyBlock */
			],
			'MANAGER' => [
				'field' => 'manager',
				'set' => 'setManager', /** @see Relation::setManager */
				'get' => 'getManager', /** @see Relation::getManager */
			],
			'COUNTER' => [
				'field' => 'counter',
				'set' => 'setCounter', /** @see Relation::setCounter */
				'get' => 'getCounter', /** @see Relation::getCounter */
			],
			'START_COUNTER' => [
				'field' => 'startCounter',
				'set' => 'setStartCounter', /** @see Relation::setStartCounter */
				'get' => 'getStartCounter', /** @see Relation::getStartCounter */
			],
		];
	}

	//region Getters & setters

	public function getId(): ?int
	{
		return $this->id;
	}

	public function setId(?int $id): Relation
	{
		$this->id = $id;
		return $this;
	}

	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	public function setChatId(?int $chatId): Relation
	{
		$this->chatId = $chatId;
		return $this;
	}

	public function getMessageType(): ?string
	{
		return $this->messageType;
	}

	public function setMessageType(?string $messageType): Relation
	{
		$this->messageType = $messageType;
		return $this;
	}

	public function getUserId(): ?int
	{
		return $this->userId;
	}

	public function setUserId(?int $userId): Relation
	{
		$this->userId = $userId;
		return $this;
	}

	public function getStartId(): ?int
	{
		return $this->startId;
	}

	public function setStartId(?int $startId): Relation
	{
		$this->startId = $startId;
		return $this;
	}

	public function getUnreadId(): ?int
	{
		return $this->unreadId;
	}

	public function setUnreadId(?int $unreadId): Relation
	{
		$this->unreadId = $unreadId;
		return $this;
	}

	public function getLastId(): ?int
	{
		return $this->lastId;
	}

	public function setLastId(?int $lastId): Relation
	{
		$this->lastId = $lastId;
		return $this;
	}

	public function getLastSendId(): ?int
	{
		return $this->lastSendId;
	}

	public function setLastSendId(?int $lastSendId): Relation
	{
		$this->lastSendId = $lastSendId;
		return $this;
	}

	public function getLastFileId(): ?int
	{
		return $this->lastFileId;
	}

	public function setLastFileId(?int $lastFileId): Relation
	{
		$this->lastFileId = $lastFileId;
		return $this;
	}

	public function getLastRead(): ?int
	{
		return $this->lastRead;
	}

	public function setLastRead(?int $lastRead): Relation
	{
		$this->lastRead = $lastRead;
		return $this;
	}

	public function getStatus(): ?int
	{
		return $this->status;
	}

	public function setStatus(?int $status): Relation
	{
		$this->status = $status;
		return $this;
	}

	public function getCallStatus(): ?int
	{
		return $this->callStatus;
	}

	public function setCallStatus(?int $callStatus): Relation
	{
		$this->callStatus = $callStatus;
		return $this;
	}

	public function getMessageStatus(): ?string
	{
		return $this->messageStatus;
	}

	public function setMessageStatus(?string $messageStatus): Relation
	{
		$this->messageStatus = $messageStatus;
		return $this;
	}

	public function getNotifyBlock(): ?bool
	{
		return $this->notifyBlock;
	}

	public function setNotifyBlock(?bool $notifyBlock): Relation
	{
		$this->notifyBlock = $notifyBlock;
		return $this;
	}

	public function getManager(): ?bool
	{
		return $this->manager;
	}

	public function setManager(?bool $manager): Relation
	{
		$this->manager = $manager;
		return $this;
	}

	public function getCounter(): ?int
	{
		return $this->counter;
	}

	public function setCounter(?int $counter): Relation
	{
		$this->counter = $counter;
		return $this;
	}

	public function getStartCounter(): ?int
	{
		return $this->startCounter;
	}

	public function setStartCounter(?int $startCounter): Relation
	{
		$this->startCounter = $startCounter;
		return $this;
	}

	//endregion
}