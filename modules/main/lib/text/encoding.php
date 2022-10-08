<?php
namespace Bitrix\Main\Text;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;

class Encoding
{
	/**
	 * Converts data from a source encoding to a target encoding.
	 *
	 * @param string|array|\SplFixedArray $data The data to convert. From main 16.0.10 data can be an array.
	 * @param string $charsetFrom The source encoding.
	 * @param string $charsetTo The target encoding.
	 * @return string|array|\SplFixedArray|bool Returns converted data or false on error.
	 */
	public static function convertEncoding($data, $charsetFrom, $charsetTo)
	{
		$charsetFrom = static::resolveAlias($charsetFrom);
		$charsetTo = static::resolveAlias($charsetTo);

		if ((string)$charsetFrom == '' || (string)$charsetTo == '')
		{
			// incorrect encoding
			return $data;
		}

		if (strcasecmp($charsetFrom, $charsetTo) == 0)
		{
			// no need to convert
			return $data;
		}

		try
		{
			// mb_encoding_aliases emits an E_WARNING level error if encoding is unknown
			if (@mb_encoding_aliases($charsetFrom) === false || @mb_encoding_aliases($charsetTo) === false)
			{
				// unknown encoding
				return $data;
			}
		}
		catch(\ValueError $e)
		{
			return $data;
		}

		return self::convert($data, $charsetFrom, $charsetTo);
	}

	protected static function resolveAlias($alias)
	{
		static $map = array(
			'csksc56011987' => 'euc-kr',
			'ks_c_5601-1987' => 'euc-kr',
			'ks_c_5601-1989' => 'euc-kr',
			'ksc5601' => 'euc-kr',
			'ksc_5601' => 'euc-kr',
			'windows-1257' => 'ISO-8859-13',
		);

		if(is_string($alias))
		{
			$alias = strtolower(trim($alias));
			if(isset($map[$alias]))
			{
				return $map[$alias];
			}
		}

		return $alias;
	}

	protected static function convert($data, $charsetFrom, $charsetTo)
	{
		if (is_array($data) || $data instanceof \SplFixedArray)
		{
			//let's do a recursion
			if ($data instanceof \SplFixedArray)
			{
				$result = clone $data;
			}
			else
			{
				$result = [];
			}
			foreach ($data as $key => $value)
			{
				$newKey = self::convert($key, $charsetFrom, $charsetTo);
				$newValue = self::convert($value, $charsetFrom, $charsetTo);

				$result[$newKey] = $newValue;
			}
			return $result;
		}
		elseif (is_string($data))
		{
			if ($data == '')
			{
				return '';
			}
			return static::convertByMbstring($data, $charsetFrom, $charsetTo);
		}
		return $data;
	}

	/**
	 * @deprecated Deprecated in main 16.0.10. Use Encoding::convertEncoding().
	 * @param $data
	 * @param $charsetFrom
	 * @param $charsetTo
	 * @return string|array|\SplFixedArray|bool
	 */
	public static function convertEncodingArray($data, $charsetFrom, $charsetTo)
	{
		return self::convertEncoding($data, $charsetFrom, $charsetTo);
	}

	/**
	 * @param string $string
	 * @return bool|string
	 */
	public static function convertEncodingToCurrent($string)
	{
		$isUtf8String = self::detectUtf8($string);
		$isUtf8Config = Application::isUtfMode();

		$from = '';
		$to = '';
		if ($isUtf8Config && !$isUtf8String)
		{
			$from = static::getCurrentEncoding();
			$to = 'UTF-8';
		}
		elseif (!$isUtf8Config && $isUtf8String)
		{
			$from = 'UTF-8';
			$to = static::getCurrentEncoding();
		}

		if ($from !== $to)
		{
			$string = self::convertEncoding($string, $from, $to);
		}

		return $string;
	}

	/**
	 * @param string $string
	 * @return bool|string
	 */
	public static function convertToUtf($string)
	{
		if (self::detectUtf8($string))
		{
			return $string;
		}

		$from = '';
		$to = '';
		if (!Application::isUtfMode())
		{
			$from = static::getCurrentEncoding();
			$to = 'UTF-8';
		}

		if ($from !== $to)
		{
			$string = self::convertEncoding($string, $from, $to);
		}

		return $string;
	}

	protected static function getCurrentEncoding(): string
	{
		$currentCharset = null;

		$context = Application::getInstance()->getContext();
		if ($context != null)
		{
			$culture = $context->getCulture();
			if ($culture != null)
			{
				$currentCharset = $culture->getCharset();
			}
		}

		if ($currentCharset == null)
		{
			$currentCharset = Configuration::getValue("default_charset");
		}

		if ($currentCharset == null)
		{
			$currentCharset = "Windows-1251";
		}

		return $currentCharset;
	}

	/**
	 * @param string $string
	 * @param bool $replaceHex
	 * @return bool
	 */
	public static function detectUtf8($string, $replaceHex = true)
	{
		//http://mail.nl.linux.org/linux-utf8/1999-09/msg00110.html

		if ($replaceHex)
		{
			$string = preg_replace_callback(
				"/(%)([\\dA-F]{2})/i",
				function ($match) {
					return chr(hexdec($match[2]));
				},
				$string
			);
		}

		//valid UTF-8 octet sequences
		//0xxxxxxx
		//110xxxxx 10xxxxxx
		//1110xxxx 10xxxxxx 10xxxxxx
		//11110xxx 10xxxxxx 10xxxxxx 10xxxxxx

		$prevBits8and7 = 0;
		$isUtf = 0;
		foreach (unpack("C*", $string) as $byte)
		{
			$hiBits8and7 = $byte & 0xC0;
			if ($hiBits8and7 == 0x80)
			{
				if ($prevBits8and7 == 0xC0)
				{
					$isUtf++;
				}
				elseif (($prevBits8and7 & 0x80) == 0x00)
				{
					$isUtf--;
				}
			}
			elseif ($prevBits8and7 == 0xC0)
			{
				$isUtf--;
			}
			$prevBits8and7 = $hiBits8and7;
		}
		return ($isUtf > 0);
	}

	protected static function convertByMbstring($data, $charsetFrom, $charsetTo)
	{
		//For UTF-16 we have to detect the order of bytes
		//Default for mbstring extension is Big endian
		//Little endian have to pointed explicitly
		if (strtoupper($charsetFrom) == "UTF-16")
		{
			$ch = substr($data, 0, 1);
			if ($ch == "\xFF" && substr($data, 1, 1) == "\xFE")
			{
				//If Little endian found - cutoff BOF bytes and point mbstring to this fact explicitly
				$res = mb_convert_encoding(substr($data, 2), $charsetTo, "UTF-16LE");
			}
			elseif ($ch == "\xFE" && substr($data, 1, 1) == "\xFF")
			{
				//If it is Big endian, just remove BOF bytes
				$res = mb_convert_encoding(substr($data, 2), $charsetTo, $charsetFrom);
			}
			else
			{
				//Otherwise, assime Little endian without BOF
				$res = mb_convert_encoding($data, $charsetTo, "UTF-16LE");
			}
		}
		else
		{
			$res = mb_convert_encoding($data, $charsetTo, $charsetFrom);
		}

		return $res;
	}
}
