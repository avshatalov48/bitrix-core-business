<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\Text;

class BinaryString
{
	/**
	 * Binary version of strlen.
	 * @param $str
	 * @return int
	 */
	public static function getLength($str)
	{
		return function_exists('mb_strlen') ? mb_strlen($str, 'latin1') : strlen($str);
	}

	/**
	 * Binary version of substr.
	 * @param $str
	 * @param $start
	 * @return string
	 */
	public static function getSubstring($str, $start)
	{
		if(function_exists('mb_substr'))
		{
			$length = (func_num_args() > 2? func_get_arg(2) : self::getLength($str));
			return mb_substr($str, $start, $length, 'latin1');
		}
		if(func_num_args() > 2)
		{
			return substr($str, $start, func_get_arg(2));
		}
		return substr($str, $start);
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
		if (defined("BX_UTF"))
		{
			if (function_exists("mb_orig_strpos"))
			{
				return mb_orig_strpos($haystack, $needle, $offset);
			}

			return mb_strpos($haystack, $needle, $offset, "latin1");
		}

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
		if (defined("BX_UTF"))
		{
			if (function_exists("mb_orig_strrpos"))
			{
				return mb_orig_strrpos($haystack, $needle, $offset);
			}

			return mb_strrpos($haystack, $needle, $offset, "latin1");
		}

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
		if (defined("BX_UTF"))
		{
			if (function_exists("mb_orig_stripos"))
			{
				return mb_orig_stripos($haystack, $needle, $offset);
			}

			return mb_stripos($haystack, $needle, $offset, "latin1");
		}

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
		if (defined("BX_UTF"))
		{
			if (function_exists("mb_orig_strripos"))
			{
				return mb_orig_strripos($haystack, $needle, $offset);
			}

			return mb_strripos($haystack, $needle, $offset, "latin1");
		}

		return strripos($haystack, $needle, $offset);
	}

	/**
	 * Binary version of strtolower.
	 * @param $str
	 * @return string
	 */
	public static function changeCaseToLower($str)
	{
		if (defined("BX_UTF"))
		{
			if (function_exists("mb_orig_strtolower"))
			{
				return mb_orig_strtolower($str);
			}

			return mb_strtolower($str, "latin1");
		}

		return strtolower($str);
	}
}