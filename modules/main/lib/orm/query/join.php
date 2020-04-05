<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query;

use Bitrix\Main\ORM\Query\Filter\ConditionTree as Filter;

/**
 * Class for nice description of join reference. Alias to Query::filter().
 * @package    bitrix
 * @subpackage main
 */
class Join
{
	const TYPE_INNER = 'INNER';
	const TYPE_LEFT = 'LEFT';
	const TYPE_LEFT_OUTER = 'LEFT OUTER';
	const TYPE_RIGHT = 'RIGHT';
	const TYPE_RIGHT_OUTER = 'RIGHT OUTER';

	/**
	 * Short alias to init filter with whereColumn.
	 * @see Filter::whereColumn()
	 *
	 * @param array ...$condition
	 *
	 * @return Filter
	 */
	public static function on()
	{
		$condition = func_get_args();
		return call_user_func_array(array(new Filter, 'whereColumn'), $condition);
	}

	public static function getTypes()
	{
		return [
			static::TYPE_INNER,
			static::TYPE_LEFT,
			static::TYPE_LEFT_OUTER,
			static::TYPE_RIGHT,
			static::TYPE_RIGHT_OUTER,
		];
	}
}
