<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Connector\Filter;

use Bitrix\Main\Entity\ExpressionField;

/**
 * Class RuntimeFilter
 * @package Bitrix\Sender\Connector\Filter
 */
class RuntimeFilter
{
	/** @var  string $key Key. */
	protected $key;

	/** @var  string $value Value. */
	protected $value;

	/** @var ExpressionField[] $runtime Runtime */
	protected $runtime = [];

	/**
	 * Set filter.
	 *
	 * @param string $key Key.
	 * @param string $value Value.
	 * @return $this
	 */
	public function setFilter($key, $value)
	{
		$this->key = $key;
		$this->value = $value;
		return $this;
	}

	/**
	 * Add runtime field.
	 *
	 * @param array $field Field.
	 * @return $this
	 */
	public function addRuntime(array $field)
	{
		$this->runtime[] = $field;
		return $this;
	}

	/**
	 * Get runtime.
	 *
	 * @return ExpressionField[]
	 */
	public function getRuntime()
	{
		return $this->runtime;
	}

	/**
	 * Get key.
	 *
	 * @return string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * Get value.
	 *
	 * @return string
	 */
	public function getValue()
	{
		return $this->value;
	}
}