<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query\Filter\Expressions;

/**
 * Wrapper for null values in QueryFilter.
 * @package    bitrix
 * @subpackage main
 */
class NullExpression extends Expression
{
	public function __toString()
	{
		return 'NULL';
	}
}
