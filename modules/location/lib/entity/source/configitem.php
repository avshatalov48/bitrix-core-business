<?php

namespace Bitrix\Location\Entity\Source;

use Bitrix\Location\Exception\RuntimeException;

/**
 * Class ConfigItem
 * @package Bitrix\Location\Entity\Source
 * @internal
 */
final class ConfigItem
{
	public const STRING_TYPE = 'string';
	public const BOOL_TYPE = 'bool';

	/** @var string */
	private $code;

	/** @var string */
	private $type;

	/** @var bool */
	private $isVisible = true;

	/** @var int */
	private $sort = 1000;

	/** @var mixed|null */
	private $value;

	/**
	 * ConfigItem constructor.
	 * @param string $code
	 * @param string $type
	 */
	public function __construct(string $code, string $type)
	{
		$this->code = $code;

		if (!in_array($type, [static::STRING_TYPE, static::BOOL_TYPE]))
		{
			throw new RuntimeException(sprintf('Unexpected config item type - %s', $type));
		}
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return $this->code;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}

	/**
	 * @return int
	 */
	public function getSort(): int
	{
		return $this->sort;
	}

	/**
	 * @param int $sort
	 * @return ConfigItem
	 */
	public function setSort(int $sort): ConfigItem
	{
		$this->sort = $sort;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isVisible(): bool
	{
		return $this->isVisible;
	}

	/**
	 * @param bool $isVisible
	 * @return ConfigItem
	 */
	public function setIsVisible(bool $isVisible): ConfigItem
	{
		$this->isVisible = $isVisible;

		return $this;
	}

	/**
	 * @return mixed|null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed|null $value
	 * @return ConfigItem
	 */
	public function setValue($value): ConfigItem
	{
		$this->value = $value;

		return $this;
	}
}
