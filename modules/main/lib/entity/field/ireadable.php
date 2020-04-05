<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\Entity\Field;

/**
 * Interface for Entity Fields to be filtered by Query.
 * @package Bitrix\Main\Entity\Query\Filter
 */
interface IReadable
{
	/**
	 * Should return raw SQL with escaped and quoted value.
	 *
	 * @param mixed $value
	 *
	 * @return string
	 */
	public function convertValueToDb($value);
}
