<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Transport;

/**
 * Interface iLimiter
 * @package Bitrix\Sender\Transport
 */
interface iLimiter
{
	const MONTHS = 'months';
	const DAYS = 'days';
	const HOURS = 'hours';
	const MINUTES = 'minutes';
	/**
	 * Get max.
	 *
	 * @return integer
	 */
	public function getLimit();

	/**
	 * Get current.
	 *
	 * @return integer
	 */
	public function getCurrent();

	/**
	 * Get unit name.
	 *
	 * @return string
	 */
	public function getUnitName();

	/**
	 * Get unit.
	 * Examples:
	 * "14 d" is equals 14 days;
	 * "d" is equals 1 day.
	 *
	 * @return string
	 */
	public function getUnit();

	/**
	 * Get caption.
	 *
	 * @return string|null
	 */
	public function getCaption();

	/**
	 * Get parameter.
	 *
	 * @param string $name Name.
	 * @return mixed|null
	 */
	public function getParameter($name);

	/**
	 * Set parameter.
	 *
	 * @param string $name Name.
	 * @param mixed $value Value.
	 * @return $this
	 */
	public function setParameter($name, $value);
}