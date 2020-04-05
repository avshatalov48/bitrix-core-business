<?php

namespace Bitrix\Translate\Text;

use Bitrix\Main;
use Bitrix\Translate;

/**
 * @see \Bitrix\Main\Text
 */

class StringHelper
{
	/**
	 * Special version of strlen.
	 * @param string $str String to measure.
	 * @param string $encoding Defines encoding used in conversion.
	 * @return int
	 */
	public static function getLength($str, $encoding = null)
	{
		if (Translate\Config::isUtfMode())
		{
			if (empty($encoding))
			{
				$encoding = Main\Localization\Translation::getCurrentEncoding();
			}
			return mb_strlen($str, $encoding);
		}

		return Main\Text\BinaryString::getLength($str);
	}

	/**
	 * Special version of substr.
	 * @param string $str String to convert.
	 * @param int $start Starting position.
	 * @param int $length Count characters to extract.
	 * @param string $encoding Defines encoding used in conversion.
	 * @return string
	 */
	public static function getSubstring($str, $start, $length, $encoding = null)
	{
		if (Translate\Config::isUtfMode())
		{
			if (empty($encoding))
			{
				$encoding = Main\Localization\Translation::getCurrentEncoding();
			}
			return mb_substr($str, $start, $length, $encoding);
		}

		return Main\Text\BinaryString::getSubstring($str, $start, $length);
	}

	/**
	 * Special version of strpos.
	 * @param string $haystack String to analyze.
	 * @param string $needle String to find.
	 * @param int $offset The search offset.
	 * @param string $encoding Defines encoding used in conversion.
	 * @return bool|int
	 */
	public static function getPosition($haystack, $needle, $offset = 0, $encoding = null)
	{
		if (function_exists('mb_strpos'))
		{
			if (empty($encoding))
			{
				$encoding = Main\Localization\Translation::getCurrentEncoding();
			}
			return mb_strpos($haystack, $needle, $offset, $encoding);
		}

		return Main\Text\BinaryString::getPosition($haystack, $needle, $offset);
	}

	/**
	 * Special version of strtolower.
	 * @param string $str String to convert.
	 * @param string $encoding Defines encoding used in conversion.
	 * @return string
	 */
	public static function changeCaseToLower($str, $encoding = null)
	{
		if (function_exists('mb_strtolower'))
		{
			if (empty($encoding))
			{
				$encoding = Main\Localization\Translation::getCurrentEncoding();
			}
			return mb_strtolower($str, $encoding);
		}

		return strtolower($str);
	}

	/**
	 * Special version of strtoupper.
	 * @param string $str String to convert.
	 * @param string $encoding Defines encoding used in conversion.
	 * @return string
	 */
	public static function changeCaseToUpper($str, $encoding = null)
	{
		if (function_exists('mb_strtoupper'))
		{
			if (empty($encoding))
			{
				$encoding = Main\Localization\Translation::getCurrentEncoding();
			}
			return mb_strtoupper($str, $encoding);
		}

		return strtoupper($str);
	}

	/**
	 * Convert special characters to HTML entities.
	 * @param string $string The string  being converted.
	 * @param int $flags A bitmask mask which specify how to handle quotes.
	 * @param string $encoding Defines encoding used in conversion.
	 * @return string
	 */
	public static function htmlSpecialChars($string, $flags = ENT_COMPAT, $encoding = null)
	{
		if (empty($encoding))
		{
			$encoding = Main\Localization\Translation::getCurrentEncoding();
		}
		return htmlspecialchars($string, $flags, $encoding, true);
	}
}