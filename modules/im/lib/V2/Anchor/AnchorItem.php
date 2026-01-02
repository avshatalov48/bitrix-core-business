<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Anchor;

use Bitrix\Im\Model\AnchorTable;
use Bitrix\Im\V2\ActiveRecord;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\Message\Reaction\ReactionItem;
use Bitrix\Im\V2\RegistryEntry;
use Bitrix\Im\V2\Rest\RestConvertible;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\InArrayValidator;

final class AnchorItem implements RegistryEntry, ActiveRecord, RestConvertible
{
	use RegistryEntryImplementation;
	use ActiveRecordImplementation;

	public const MENTION = 'MENTION';
	public const REACTION = 'REACTION';

	public const ALLOWED_ANCHORS = [
		self::MENTION => self::MENTION,
		self::REACTION => self::REACTION,
	];

	private int $id;
	private int $chatId;
	private int $messageId;
	private int $userId;
	private int $fromUserId;
	private string $type;
	private ?string $subType = null;

	// runtime fields
	private int $parentChatId = 0;
	private int $parentMessageId = 0;

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

	public function setChatId(int $chatId): self
	{
		$this->chatId = $chatId;

		return $this;
	}

	public function getMessageId(): int
	{
		return $this->messageId;
	}

	public function setMessageId(int $messageId): self
	{
		$this->messageId = $messageId;

		return $this;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function setUserId(int $userId): self
	{
		$this->userId = $userId;

		return $this;
	}

	public function getFromUserId(): int
	{
		return $this->fromUserId;
	}

	public function setFromUserId(int $fromUserId): self
	{
		$this->fromUserId = $fromUserId;

		return $this;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function setType(string $type): self
	{
		$this->type = $type;

		return $this;
	}

	public function getSubType(): ?string
	{
		return $this->subType;
	}

	public function setSubType(?string $subType): self
	{
		$this->subType = $subType;

		return $this;
	}

	public function getParentChatId(): int
	{
		return $this->parentChatId;
	}

	public function setParentChatId(int $parentChatId): self
	{
		$this->parentChatId = $parentChatId;

		return $this;
	}

	public function getParentMessageId(): int
	{
		return $this->parentMessageId;
	}

	public function setParentMessageId(int $parentMessageId): self
	{
		$this->parentMessageId = $parentMessageId;

		return $this;
	}

	public static function getDataClass(): string
	{
		return AnchorTable::class;
	}

	public static function getRestEntityName(): string
	{
		return 'anchor';
	}

	public function toRestFormat(array $option = []): ?array
	{
		return [
			'chatId' => $this->getChatId(),
			'messageId' => $this->getMessageId(),
			'userId' => $this->getUserId(),
			'fromUserId' => $this->getFromUserId(),
			'type' => $this->getType(),
			'subType' => $this->getSubType(),
			'parentChatId' => $this->getParentChatId(),
			'parentMessageId' => $this->getParentMessageId(),
		];
	}

	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'id',
				'set' => 'setPrimaryId',
				'get' => 'getPrimaryId',
			],
			'CHAT_ID' => [
				'field' => 'chatId',
				'set' => 'setChatId',
				'get' => 'getChatId',
			],
			'MESSAGE_ID' => [
				'field' => 'messageId',
				'set' => 'setMessageId',
				'get' => 'getMessageId',
			],
			'USER_ID' => [
				'field' => 'userId',
				'set' => 'setUserId',
				'get' => 'getUserId',
			],
			'FROM_USER_ID' => [
				'field' => 'fromUserId',
				'set' => 'setFromUserId',
				'get' => 'getFromUserId',
			],
			'TYPE' => [
				'field' => 'type',
				'set' => 'setType',
				'get' => 'getType',
				'beforeSave' => 'beforeSaveType',
			],
			'SUB_TYPE' => [
				'field' => 'subType',
				'get' => 'getSubType',
				'set' => 'setSubType',
				'default' => null,
				'beforeSave' => 'beforeSaveSubType',
			],
		];
	}


	private function beforeSaveType(): ValidationResult
	{
		return (new InArrayValidator(self::ALLOWED_ANCHORS))->validate($this->type);
	}

	private function beforeSaveSubType(): ValidationResult
	{
		$result = new ValidationResult();

		if ($this->type === self::MENTION)
		{
			if ($this->subType === null)
			{
				return $result;
			}

			return $result->addError(new ValidationError(
				'Wrong sub type for mention',
				'subType'
			));
		}

		if ($this->type === self::REACTION)
		{
			return (new InArrayValidator(ReactionItem::ALLOWED_REACTION))->validate($this->subType);
		}

		return $result;
	}
}