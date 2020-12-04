<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Main\Text;

use Bitrix\Main\Application;

class UtfSafeString
{
	public static function getLastPosition($haystack, $needle)
	{
		if (Application::isUtfMode())
		{
			//mb_strrpos does not work on invalid UTF-8 strings
			$ln = mb_strlen($needle);
			for ($i = mb_strlen($haystack) - $ln; $i >= 0; $i--)
			{
				if (mb_substr($haystack, $i, $ln) == $needle)
				{
					return $i;
				}
			}
			return false;
		}

		return mb_strrpos($haystack, $needle);
	}

	public static function rtrimInvalidUtf($string)
	{
		//valid UTF-8 octet sequences
		//0xxxxxxx
		//110xxxxx 10xxxxxx
		//1110xxxx 10xxxxxx 10xxxxxx
		//11110xxx 10xxxxxx 10xxxxxx 10xxxxxx

		$last4bytes = substr($string, -3);
		$reversed = array_reverse(unpack("C*", $last4bytes));
		if (($reversed[0] & 0x80) === 0x00) //ASCII
			return $string;
		elseif (($reversed[0] & 0xC0) === 0xC0) //Start of utf seq (cut it!)
			return substr($string, 0, -1);
		elseif (($reversed[1] & 0xE0) === 0xE0) //Start of utf seq (longer than 2 bytes)
			return substr($string, 0, -2);
		elseif (($reversed[2] & 0xE0) === 0xF0) //Start of utf seq (longer than 3 bytes)
			return substr($string, 0, -3);
		return $string;
	}

	/**
	 * Escapes 4-bytes UTF sequences.
	 *
	 * @param $string
	 * @return string
	 */
	public static function escapeInvalidUtf($string)
	{
		$escape = function($matches)
		{
			return (isset($matches[2])? '?' : $matches[1]);
		};

		return preg_replace_callback('/([\x00-\x7F]+
			|[\xC2-\xDF][\x80-\xBF]
			|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF])
			|([\x80-\xFF])/x', $escape, $string
		);
	}


	/**
	 * Pads utf string as str_pad.
	 * Using parameters like native str_pad().
	 *
	 * @param $string
	 * @param $padLength
	 * @param string $padString
	 * @param int $padType
	 * @return string
	 */
	public static function pad($string, $padLen, $padStr = ' ', $padType = STR_PAD_RIGHT)
	{
		$strLength = mb_strlen($string);
		$padStrLength = mb_strlen($padStr);
		if (!$strLength && ($padType == STR_PAD_RIGHT || $padType == STR_PAD_LEFT))
		{
			$strLength = 1; // @debug
		}
		if (!$padLen || !$padStrLength || $padLen <= $strLength)
		{
			return $string;
		}

		$result = null;
		$repeat = ceil(($padLen - $strLength) / $padStrLength);
		if ($padType == STR_PAD_RIGHT)
		{
			$result = $string . str_repeat($padStr, $repeat);
			$result = mb_substr($result, 0, $padLen);
		}
		else if ($padType == STR_PAD_LEFT)
		{
			$result = str_repeat($padStr, $repeat) . $string;
			$result = mb_substr($result, -$padLen);
		}
		else if ($padType == STR_PAD_BOTH)
		{
			$length = ($padLen - $strLength) / 2;
			$repeat = ceil($length / $padStrLength);
			$result = mb_substr(str_repeat($padStr, $repeat), 0, floor($length))
				.$string
				.mb_substr(str_repeat($padStr, $repeat), 0, ceil($length));
		}

		return $result;
	}

	/**
	 * Checks array of strings or string for invalid unicode symbols.
	 * If input data does not contain invalid characters, returns TRUE; otherwise, returns FALSE.
	 *
	 * @param array|string $data Input data to validate.
	 *
	 * @return boolean
	 */
	public static function checkEncoding($data)
	{
		if (!Application::isUtfMode())
		{
			return true;
		}

		if (!is_string($data) && !is_array($data))
		{
			return true;
		}

		if (is_string($data))
		{
			return mb_check_encoding($data);
		}

		foreach ($data as $value)
		{
			if (!static::checkEncoding($value))
			{
				return false;
			}
		}

		return true;
	}
}