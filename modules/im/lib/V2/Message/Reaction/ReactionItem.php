<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\Model\ReactionTable;
use Bitrix\Im\V2\ActiveRecord;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\ContextCustomer;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\Entity\User\UserPopupItem;
use Bitrix\Im\V2\RegistryEntry;
use Bitrix\Im\V2\Rest\PopupData;
use Bitrix\Im\V2\Rest\PopupDataAggregatable;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;

class ReactionItem implements RegistryEntry, ActiveRecord, RestConvertible, PopupDataAggregatable
{
	use ContextCustomer;
	use RegistryEntryImplementation;
	use ActiveRecordImplementation;

	public const LIKE = 'LIKE';
	public const KISS = 'KISS';
	public const LAUGH = 'LAUGH';
	public const WONDER = 'WONDER';
	public const CRY = 'CRY';
	public const ANGRY = 'ANGRY';
	public const FACEPALM = 'FACEPALM';

	public const ALLOWED_REACTION = [
		self::LIKE,
		self::KISS,
		self::LAUGH,
		self::WONDER,
		self::CRY,
		self::ANGRY,
		self::FACEPALM,
	];

	private int $id;
	private int $chatId;
	private int $messageId;
	private int $userId;
	private string $reaction;
	private DateTime $dateCreate;

	public function __construct($source = null)
	{
		$this->initByDefault();

		if (!empty($source))
		{
			$this->load($source);
		}
	}

	public static function getByMessage(int $messageId, string $reaction, int $userId): ?self
	{
		$reactionObject = ReactionTable::query()
			->setSelect(['*'])
			->where('MESSAGE_ID', $messageId)
			->where('REACTION', $reaction)
			->where('USER_ID', $userId)
			->fetchObject()
		;

		if ($reactionObject === null)
		{
			return null;
		}

		return new static($reactionObject);
	}

	public function getPopupData(array $excludedList = []): PopupData
	{
		return new PopupData([new UserPopupItem([$this->getUserId()])], $excludedList);
	}

	public function getLocName(?string $languageId = null): ?string
	{
		return Loc::getMessage("IM_MESSAGE_REACTION_NAME_{$this->reaction}", null, $languageId);
	}

	public function getPrimaryId(): ?int
	{
		return $this->id ?? null;
	}

	public function setPrimaryId(int $primaryId): self
	{
		$this->id = $primaryId;

		return $this;
	}

	public function getChatId(): int
	{
		return $this->chatId;
	}

	public function setChatId(int $chatId): ReactionItem
	{
		$this->chatId = $chatId;
		return $this;
	}

	public function getMessageId(): int
	{
		return $this->messageId;
	}

	public function setMessageId(int $messageId): ReactionItem
	{
		$this->messageId = $messageId;
		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int $userId): ReactionItem
	{
		$this->userId = $userId;
		return $this;
	}

	public function getReaction(): string
	{
		return $this->reaction;
	}

	public function setReaction(string $reaction): ReactionItem
	{
		$this->reaction = $reaction;
		return $this;
	}

	public function getDateCreate(): DateTime
	{
		return $this->dateCreate;
	}

	public function setDateCreate(DateTime $dateCreate): ReactionItem
	{
		$this->dateCreate = $dateCreate;
		return $this;
	}

	public function getDefaultReaction(): string
	{
		return self::LIKE;
	}

	public function beforeSaveReaction(): Result
	{
		return static::validateReaction($this->reaction);
	}

	public function getDefaultDateCreate(): DateTime
	{
		return new DateTime();
	}

	public static function validateReaction(string $reaction): Result
	{
		$result = new Result();

		if (!in_array($reaction, self::ALLOWED_REACTION, true))
		{
			$result->addError(new ReactionError(ReactionError::NOT_FOUND));
		}

		return $result;
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
				'set' => 'setPrimaryId', /** @see ReactionItem::setPrimaryId */
				'get' => 'getPrimaryId', /** @see ReactionItem::getPrimaryId */
			],
			'CHAT_ID' => [
				'field' => 'chatId',
				'set' => 'setChatId', /** @see ReactionItem::setChatId */
				'get' => 'getChatId', /** @see ReactionItem::getChatId */
			],
			'MESSAGE_ID' => [
				'field' => 'messageId',
				'set' => 'setMessageId', /** @see ReactionItem::setMessageId */
				'get' => 'getMessageId', /** @see ReactionItem::getMessageId */
			],
			'USER_ID' => [
				'field' => 'userId',
				'set' => 'setUserId', /** @see ReactionItem::setUserId */
				'get' => 'getUserId', /** @see ReactionItem::getUserId */
			],
			'REACTION' => [
				'field' => 'reaction',
				'set' => 'setReaction', /** @see ReactionItem::setReaction */
				'get' => 'getReaction', /** @see ReactionItem::getReaction */
				'default' => 'getDefaultReaction', /** @see ReactionItem::getDefaultReaction */
				'beforeSave' => 'beforeSaveReaction', /** @see ReactionItem::beforeSaveReaction */
			],
			'DATE_CREATE' => [
				'field' => 'dateCreate',
				'get' => 'getDateCreate',  /** @see ReactionItem::getDateCreate */
				'set' => 'setDateCreate',  /** @see ReactionItem::setDateCreate */
				'default' => 'getDefaultDateCreate', /** @see ReactionItem::getDefaultDateCreate */
			],
		];
	}

	public static function getDataClass(): string
	{
		return ReactionTable::class;
	}

	public static function getRestEntityName(): string
	{
		return 'reaction';
	}

	public function toRestFormat(array $option = []): array
	{
		return [
			'id' => $this->getPrimaryId(),
			'messageId' => $this->getMessageId(),
			'userId' => $this->getUserId(),
			'reaction' => $this->getReaction(),
			'dateCreate' => $this->getDateCreate()->format('c'),
		];
	}
}