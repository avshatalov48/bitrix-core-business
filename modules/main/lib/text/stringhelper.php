<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\Text;

/**
 * String functions
 * @package    bitrix
 * @subpackage main
 */
class StringHelper
{
	/**
	 * Regular uppercase with result cache
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function strtoupper($str)
	{
		static $cache;

		if (empty($cache[$str]))
		{
			$cache[$str] = mb_strtoupper($str);
		}

		return $cache[$str];
	}

	/**
	 * Changes registry from CamelCase to snake_case
	 *
	 * @param $str
	 *
	 * @return string
	 */
	public static function camel2snake($str)
	{
		return mb_strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $str));
	}

	/**
	 * Changes registry from snake_case or SNAKE_CASE to CamelCase
	 *
	 * @param $str
	 *
	 * @return mixed
	 */
	public static function snake2camel($str)
	{
		$str = str_replace('_', ' ', mb_strtolower($str));
		return str_replace(' ', '', ucwords($str));
	}
}
