<?php

namespace Bitrix\Im\V2\Chat\Param;

use Bitrix\Im\Model\ChatParamTable;
use Bitrix\Im\V2\ActiveRecord;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;
use Bitrix\Im\V2\RegistryEntry;

Class Param implements RegistryEntry, ActiveRecord
{
	use ActiveRecordImplementation;
	use RegistryEntryImplementation;

	public const
		TYPE_STRING = 'string',
		TYPE_INT = 'integer',
		TYPE_BOOL = 'boolean',
		TYPE_JSON = 'json',
		TYPE_STRING_ARRAY = 'arrayString',
		TYPE_INT_ARRAY = 'arrayInteger'
	;

	public const PARAM_TYPES = [
		self::TYPE_STRING,
		self::TYPE_INT,
		self::TYPE_BOOL,
		self::TYPE_JSON,
		self::TYPE_STRING_ARRAY,
		self::TYPE_INT_ARRAY,
	];

	protected ?string $type = null;
	protected ?int $paramId = null;
	protected ?int $chatId = null;
	protected ?string $name = null;
	protected ?string $jsonValue = null;
	protected $value = null;
	protected bool $isHidden = false;

	public static function getDataClass(): string
	{
		return ChatParamTable::class;
	}

	public function unsetValue(): self
	{
		$this->value = null;
		$this->markDrop();

		if ($this->getRegistry())
		{
			unset($this->getRegistry()[$this->getName()]);
		}

		return $this;
	}

	public function getPrimaryId(): ?int
	{
		return $this->getParamId();
	}

	public function setPrimaryId(int $primaryId): self
	{
		return $this->setParamId($primaryId);
	}

	public function getParamId(): ?int
	{
		return $this->paramId;
	}

	public function setParamId(int $paramId): self
	{
		if (!$this->paramId)
		{
			$this->paramId = $paramId;
		}
		return $this;
	}

	public function getChatId(): ?int
	{
		return $this->chatId;
	}

	public function setChatId(int $chatId): self
	{
		if ($this->chatId != $chatId)
		{
			$this->markChanged();
		}
		$this->chatId = $chatId;
		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setName(string $name): self
	{
		$name = mb_substr(trim($name), 0, 100);
		if ($this->name != $name)
		{
			$this->markChanged();
		}
		$this->name = $name;

		return $this;
	}

	public function getType(): string
	{
		return $this->type ?? self::TYPE_STRING;
	}

	public function setType(string $type): self
	{
		switch ($type)
		{
			case self::TYPE_INT_ARRAY:
				$type = self::TYPE_INT;
				break;

			case self::TYPE_STRING_ARRAY:
				$type = self::TYPE_STRING;
		}
		if ($this->type !== $type)
		{
			$this->markChanged();
		}
		$this->type = $type;

		return $this;
	}

	public function getValue()
	{
		switch ($this->type)
		{
			case self::TYPE_INT:
				return (int)$this->value;

			case self::TYPE_BOOL:
				if (is_string($this->value))
				{
					$this->value = $this->value === 'Y';
				}
				return (bool)$this->value;

			case self::TYPE_STRING:
				return (string)$this->value;

			default:
				return $this->value;
		}
	}

	public function setValue($value): self
	{
		if ($value === null)
		{
			return $this->unsetValue();
		}

		switch ($this->type)
		{
			case self::TYPE_INT:
				$this->value = (int)$value;
				break;

			case self::TYPE_BOOL:
				if (is_string($value))
				{
					$this->value = $value === 'Y';
				}
				else
				{
					$this->value = (bool)$value;
				}
				break;

			case self::TYPE_STRING:
				$this->value = (string)$value;
				break;

			default:
				$this->value = $value;
		}

		$this->markChanged();

		return $this;
	}

	public function hasValue(): bool
	{
		return $this->value !== null;
	}

	public function getJsonValue()
	{
		return $this->jsonValue;
	}

	public function setJsonValue($value): self
	{
		$this->jsonValue = $value;
		$this->markChanged();

		return $this;
	}

	public function isHidden(): bool
	{
		return $this->isHidden;
	}

	public function setHidden(bool $isHidden): self
	{
		$this->isHidden = $isHidden;

		return $this;
	}

	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'paramId',
				'get' => 'getParamId', /** @see Self::getParamId */
				'set' => 'setParamId', /** @see Self::setParamId */
			],
			'CHAT_ID' => [
				'field' => 'chatId',
				'set' => 'setChatId', /** @see Self::setChatId */
				'get' => 'getChatId', /** @see Self::getChatId */
			],
			'TYPE' => [
				'set' => 'setType', /** @see Self::setType */
				'get' => 'getType', /** @see Self::getType */
			],
			'PARAM_NAME' => [
				'field' => 'name',
				'set' => 'setName', /** @see Self::setName */
				'get' => 'getName', /** @see Self::getName */
			],
			'PARAM_VALUE' => [
				'field' => 'value',
				'set' => 'setValue', /** @see Self::setValue */
				'get' => 'getValue', /** @see Self::getValue */
				'saveFilter' => 'saveValueFilter', /** @see Self::saveValueFilter() */
				'loadFilter' => 'loadValueFilter', /** @see Self::loadValueFilter */
			],
			'PARAM_JSON' => [
				'field' => 'jsonValue',
				'set' => 'setJsonValue', /** @see Self::setJsonValue */
				'get' => 'getJsonValue', /** @see Self::getJsonValue */
				'saveFilter' => 'saveJsonFilter', /** @see Self::saveJsonFilter */
				'loadFilter' => 'loadJsonFilter', /** @see Self::loadJsonFilter */
			],
		];
	}

	public function saveValueFilter($value)
	{
		if ($this->type === self::TYPE_BOOL)
		{
			$value = $value ? 'Y' : 'N';
		}

		return $value;
	}

	public function loadValueFilter($value)
	{
		if ($this->type === self::TYPE_BOOL)
		{
			$value = $value === 'Y';
		}

		return $value;
	}

	public function saveJsonFilter($value)
	{
		return $value;
	}

	public function loadJsonFilter($value)
	{
		return $value;
	}

	public function toRestFormat()
	{
		switch ($this->type)
		{
			case self::TYPE_BOOL:
				return $this->getValue() ? 'Y' : 'N';

			case self::TYPE_INT:
				return (string)$this->getValue();

			case self::TYPE_STRING:

			default:
				return $this->getValue();
		}
	}
}