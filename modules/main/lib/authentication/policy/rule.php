<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Policy;

abstract class Rule implements \JsonSerializable
{
	protected $title;
	protected $value;
	protected $options = [
		'type' => 'text',
		'size' => 5,
	];
	protected $groupId = 0;

	/**
	 * Rule constructor.
	 * @param string $title
	 * @param mixed $value
	 * @param array|null $options
	 */
	public function __construct($title, $value = 0, array $options = null)
	{
		$this->title = $title;
		$this->value = $value;
		if($options !== null)
		{
			$this->options = $options;
		}
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Assigns the value if it follows the rule.
	 * @param mixed $value
	 * @return bool
	 */
	public function assignValue($value): bool
	{
		if ($this->compare($value))
		{
			$this->value = $value;
			return true;
		}
		return false;
	}

	/**
	 * Checks if the value follows the rule.
	 * @param mixed $value
	 * @return bool
	 */
	abstract public function compare($value): bool;

	/**
	 * @return array
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	/**
	 * @return int
	 */
	public function getGroupId(): int
	{
		return $this->groupId;
	}

	/**
	 * @param int $groupId
	 * @return Rule
	 */
	public function setGroupId(int $groupId): Rule
	{
		$this->groupId = $groupId;
		return $this;
	}

	/**
	 * JsonSerializable::jsonSerialize — Specify data which should be serialized to JSON
	 * @return array
	 */
	public function jsonSerialize()
	{
		$class = get_class($this);
		$class = substr($class, (int)strrpos($class, '\\') + 1);

		return [
			'value' => $this->value,
			'type' => $class,
		];
    }
}
