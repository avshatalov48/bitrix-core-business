<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Im\Model\EO_MessageParam;
use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\V2\Result;
use Bitrix\Im\V2\ActiveRecord;
use Bitrix\Im\V2\RegistryEntry;
use Bitrix\Im\V2\Common\ActiveRecordImplementation;
use Bitrix\Im\V2\Common\RegistryEntryImplementation;

/**
 * Message Parameter Class.
 */
class Param implements MessageParameter, RegistryEntry, ActiveRecord
{
	use ActiveRecordImplementation
	{
		load as defaultLoad;
	}
	use RegistryEntryImplementation;

	public const
		TYPE_STRING = 'String',
		TYPE_INT = 'Integer',
		TYPE_BOOL = 'Boolean',
		TYPE_STRING_ARRAY = 'ArrayString',
		TYPE_INT_ARRAY = 'ArrayInteger',
		TYPE_DATE_TIME = 'DateTime',
		TYPE_JSON = 'Json'
	;

	protected ?string $type = null;

	protected ?int $paramId = null;

	protected ?int $messageId = null;

	protected ?string $name = null;

	/** @var mixed|null */
	protected $value = null;

	/** @var mixed|null */
	protected ?string $jsonValue = null;

	/** @var mixed|null */
	protected $defaultValue = null;

	/**
	 * @param int|array|EO_MessageParam|null $source
	 */
	public function __construct($source = null)
	{
		if (!empty($source))
		{
			$this->load($source);
		}
	}

	/**
	 * @param int|array|EntityObject $source
	 */
	public function load($source): Result
	{
		$result = $this->defaultLoad($source);
		if ($result->isSuccess())
		{
			$checkType = $this->getType();
			$this->detectType();
			if ($this->type !== $checkType)
			{
				$this->setValue($this->value);
			}

			$type = Params::getType($this->name);
			if (isset($type['loadValueFilter']) && is_callable($type['loadValueFilter']))
			{
				$this->value = \call_user_func($type['loadValueFilter'], $this->value);
			}
		}

		return $result;
	}

	//region Param value

	/**
	 * @param mixed $value
	 * @return static
	 */
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

		$defaultValue = $this->getDefaultValue();
		if ($this->value === $defaultValue)
		{
			return $this->unsetValue();
		}

		$this->markChanged();

		return $this;
	}

	/**
	 * @return mixed|null
	 */
	public function getDefaultValue()
	{
		if ($this->defaultValue === null)
		{
			$type = Params::getType($this->name);
			if (isset($type['default']))
			{
				$value = $type['default'];

				switch ($this->type)
				{
					case self::TYPE_INT:
						$this->defaultValue = (int)$value;
						break;

					case self::TYPE_BOOL:
						$this->defaultValue = (bool)$value;
						break;

					case self::TYPE_STRING:
						$this->defaultValue = (string)$value;
						break;

					default:
						$this->defaultValue = $value;
				}
			}
		}

		return $this->defaultValue;
	}

	/**
	 * @return bool
	 */
	public function hasValue(): bool
	{
		return $this->value !== null;
	}

	/**
	 * @return mixed|null
	 */
	public function getValue()
	{
		if ($this->value === null)
		{
			return $this->getDefaultValue();
		}
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

	/**
	 * @param mixed $value
	 * @return static
	 */
	public function addValue($value): self
	{
		return $this->setValue($value);
	}

	/**
	 * @return static
	 */
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

	public function isHidden(): bool
	{
		return Params::getType($this->name)['isHidden'] ?? false;
	}

	/**
	 * @return string|array|null
	 */
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

	/**
	 * @return mixed
	 */
	public function toPullFormat()
	{
		return $this->toRestFormat();
	}

	//endregion

	//region Setters & Getters

	public function setParamId(int $paramId): self
	{
		if (!$this->paramId)
		{
			$this->paramId = $paramId;
		}
		return $this;
	}

	public function getParamId(): ?int
	{
		return $this->paramId;
	}

	public function setMessageId(int $messageId): self
	{
		if ($this->messageId != $messageId)
		{
			$this->markChanged();
		}
		$this->messageId = $messageId;
		return $this;
	}

	public function getMessageId(): ?int
	{
		return $this->messageId;
	}

	public function setName(string $name): self
	{
		$name = mb_substr(trim($name), 0, 100);
		if ($this->name != $name)
		{
			$this->markChanged();
		}
		$this->name = $name;
		$this->detectType();

		return $this;
	}

	public function getName(): ?string
	{
		return $this->name;
	}

	public function setType(string $type): self
	{
		switch ($type)
		{
			case Param::TYPE_INT_ARRAY:
				$type = Param::TYPE_INT;
				break;

			case Param::TYPE_STRING_ARRAY:
				$type = Param::TYPE_STRING;
		}
		if ($this->type != $type)
		{
			$this->markChanged();
		}
		$this->type = $type;

		return $this;
	}

	public function getType(): string
	{
		return $this->type ?? Param::TYPE_STRING;
	}

	public function detectType(): self
	{
		if (empty($this->type) && !empty($this->name))
		{
			$type = Params::getType($this->name);
			$this->setType($type['type'] ?? Param::TYPE_STRING);
		}

		return $this;
	}

	public function setJsonValue($value): self
	{
		$this->jsonValue = $value;
		$this->markChanged();

		return $this;
	}

	public function getJsonValue()
	{
		return $this->jsonValue;
	}

	//endregion

	//region Data storage

	/**
	 * @return array<array>
	 */
	protected static function mirrorDataEntityFields(): array
	{
		return [
			'ID' => [
				'primary' => true,
				'field' => 'paramId',
				'get' => 'getParamId', /** @see Param::getParamId */
				'set' => 'setParamId', /** @see Param::setParamId */
			],
			'MESSAGE_ID' => [
				'field' => 'messageId',
				'set' => 'setMessageId', /** @see Param::setMessageId */
				'get' => 'getMessageId', /** @see Param::getMessageId */
			],
			'TYPE' => [
				'set' => 'setType', /** @see Param::setType */
				'get' => 'getType', /** @see Param::getType */
			],
			'PARAM_NAME' => [
				'field' => 'name',
				'set' => 'setName', /** @see Param::setName */
				'get' => 'getName', /** @see Param::getName */
			],
			'PARAM_VALUE' => [
				'field' => 'value',
				'set' => 'setValue', /** @see Param::setValue */
				'get' => 'getValue', /** @see Param::getValue */
				'saveFilter' => 'saveValueFilter', /** @see Param::saveValueFilter */
				'loadFilter' => 'loadValueFilter', /** @see Param::loadValueFilter */
			],
			'PARAM_JSON' => [
				'field' => 'jsonValue',
				'set' => 'setJsonValue', /** @see Param::setJsonValue */
				'get' => 'getJsonValue', /** @see Param::getJsonValue */
				'saveFilter' => 'saveJsonFilter', /** @see Param::saveJsonFilter */
				'loadFilter' => 'loadJsonFilter', /** @see Param::loadJsonFilter */
			],
		];
	}

	/**
	 * @return string|DataManager;
	 */
	public static function getDataClass(): string
	{
		return MessageParamTable::class;
	}

	/**
	 * @return int|null
	 */
	public function getPrimaryId(): ?int
	{
		return $this->getParamId();
	}

	/**
	 * @param int $primaryId
	 * @return static
	 */
	public function setPrimaryId(int $primaryId): self
	{
		return $this->setParamId($primaryId);
	}

	public function isValid(): Result
	{
		return new Result();
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveValueFilter($value)
	{
		$type = Params::getType($this->name);

		if (
			isset($type['saveValueFilter'])
			&& ($saveFilter = $type['saveValueFilter'])
		)
		{
			if (is_string($saveFilter) && is_callable([$this, $saveFilter]))
			{
				$value = $this->$saveFilter($value);
			}
			elseif (is_callable($saveFilter))
			{
				$value = call_user_func($saveFilter, $value);
			}
		}
		elseif ($type['type'] == Param::TYPE_BOOL)
		{
			$value = $value ? 'Y' : 'N';
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadValueFilter($value)
	{
		$type = Params::getType($this->name);

		if (
			isset($type['loadValueFilter'])
			&& ($loadFilter = $type['loadValueFilter'])
		)
		{
			if (is_string($loadFilter) && is_callable([$this, $loadFilter]))
			{
				$value = $this->$loadFilter($value);
			}
			elseif (is_callable($loadFilter))
			{
				$value = call_user_func($loadFilter, $value);
			}
		}
		elseif (($type['type'] ?? null) == Param::TYPE_BOOL) //TODO replace to normal variant
		{
			$value = $value == 'Y';
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveJsonFilter($value)
	{
		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadJsonFilter($value)
	{
		return $value;
	}

	//endregion
}