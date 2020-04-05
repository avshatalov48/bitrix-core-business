<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Sender\Integration\VoxImplant;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

use Bitrix\Sender\Transport;

Loc::loadMessages(__FILE__);

/**
 * Class Limiter
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class Limiter implements Transport\iLimiter
{
	/** @var array $parameters Parameters. */
	private $parameters = array();

	/**
	 * Limiter constructor.
	 */
	public function __construct()
	{
		$this->setParameter('textView', true);
	}
	/**
	 * Get max.
	 *
	 * @return integer
	 */
	public function getLimit()
	{
		return Option::get('sender', '~call_limit', 5);
	}

	/**
	 * Get current.
	 *
	 * @return integer
	 */
	public function getCurrent()
	{
		return CallLogTable::getActualCallCount();
	}

	/**
	 * Get caption.
	 *
	 * @return string|null
	 */
	public function getCaption()
	{
		return '';
	}

	/**
	 * Get parameter.
	 *
	 * @param string $name Name.
	 * @return mixed|null
	 */
	public function getParameter($name)
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
	}

	/**
	 * Set parameter.
	 *
	 * @param string $name Name.
	 * @param mixed $value Value.
	 * @return $this
	 */
	public function setParameter($name, $value)
	{
		$this->parameters[$name] = $value;
		return $this;
	}

	/**
	 * Get unit name.
	 *
	 * @return string
	 */
	public function getUnitName()
	{
		return Loc::getMessage('SENDER_INTEGRATION_CALL_LIMITER_UNIT_NAME');
	}

	/**
	 * Get unit.
	 * Examples:
	 * "14 d" is equals 14 days;
	 * "d" is equals 1 day.
	 *
	 * @return string
	 */
	public function getUnit()
	{
		return null;
	}
}