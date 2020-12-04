<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\Text;

/**
 * @deprecated Use str* functions for binary strings and mb_* for characters.
 */
class BinaryString
{
	/**
	 * Binary version of strlen.
	 * @param $str
	 * @return int
	 */
	public static function getLength($str)
	{
		return strlen($str);
	}

	/**
	 * Binary version of substr.
	 * @param $str
	 * @param $start
	 * @param array $args
	 * @return string
	 */
	public static function getSubstring($str, $start, ...$args)
	{
		return substr($str, $start, ...$args);
	}

	/**
	 * Binary version of strpos.
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return bool|int
	 */
	public static function getPosition($haystack, $needle, $offset = 0)
	{
		return strpos($haystack, $needle, $offset);
	}

	/**
	 * Binary version of strrpos.
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return bool|int
	 */
	public static function getLastPosition($haystack, $needle, $offset = 0)
	{
		return strrpos($haystack, $needle, $offset);
	}

	/**
	 * Binary version of stripos.
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return int
	 */
	public static function getPositionIgnoreCase($haystack, $needle, $offset = 0)
	{
		return stripos($haystack, $needle, $offset);
	}

	/**
	 * Binary version of strripos.
	 * @param $haystack
	 * @param $needle
	 * @param int $offset
	 * @return int
	 */
	public static function getLastPositionIgnoreCase($haystack, $needle, $offset = 0)
	{
		return strripos($haystack, $needle, $offset);
	}

	/**
	 * Binary version of strtolower.
	 * @param $str
	 * @return string
	 */
	public static function changeCaseToLower($str)
	{
		return strtolower($str);
	}
}
