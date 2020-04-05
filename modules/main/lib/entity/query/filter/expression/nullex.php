<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\Entity\Query\Filter\Expression;

/**
 * Wrapper for null values in QueryFilter.
 * @package    bitrix
 * @subpackage main
 */
class NullEx extends Base
{
	public function __toString()
	{
		return 'NULL';
	}
}
