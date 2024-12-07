<?php

namespace Bitrix\Translate\Text;

use Bitrix\Main;
use Bitrix\Translate;

/**
 * @see \Bitrix\Main\Text
 */

class StringHelper
{
	// utf8 https://www.w3.org/International/questions/qa-forms-utf-8.en
	public const UTF8_REGEXP = '/(?:
		      [\x09\x0A\x0D\x20-\x7E]            # ASCII
		    | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
		    | \xE0[\xA0-\xBF][\x80-\xBF]         # excluding overlongs
		    | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
		    | \xED[\x80-\x9F][\x80-\xBF]         # excluding surrogates
		    | \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
		    | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
		    | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
		)+/xs';

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
			return \mb_strlen($str, $encoding);
		}

		return \strlen($str);
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
			return \mb_substr($str, $start, $length, $encoding);
		}

		return \substr($str, $start, $length);
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
		if (\function_exists('mb_strpos'))
		{
			if (empty($encoding))
			{
				$encoding = Main\Localization\Translation::getCurrentEncoding();
			}
			return \mb_strpos($haystack, $needle, $offset, $encoding);
		}

		return \strpos($haystack, $needle, $offset);
	}

	/**
	 * Special version of strtolower.
	 * @param string $str String to convert.
	 * @return string
	 */
	public static function changeCaseToLower($str)
	{
		return \mb_strtolower($str);
	}

	/**
	 * Special version of strtoupper.
	 * @param string $str String to convert.
	 * @return string
	 */
	public static function changeCaseToUpper($str)
	{
		return \mb_strtoupper($str);
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
		return \htmlspecialchars($string, $flags, $encoding, true);
	}

	/**
	 * Validates UTF-8 octet sequences:
	 * 0xxxxxxx
	 * 110xxxxx 10xxxxxx
	 * 1110xxxx 10xxxxxx 10xxxxxx
	 * 11110xxx 10xxxxxx 10xxxxxx 10xxxxxx
	 *
	 * @param string $string
	 * @return bool
	 */
	public static function validateUtf8OctetSequences($string)
	{
		return Main\Text\Encoding::detectUtf8($string, false);
	}

	/**
	 * Escapes symbols of "'$ in given string.
	 *
	 * @param string $str String to escape.
	 * @param string $enclosure Enclosure symbol " or ' and <<< for heredoc syntax.
	 * @param string $additional Additional symbols to escape.
	 *
	 * @return string
	 */
	public static function escapePhp($str, $enclosure = '"', $additional = ''): string
	{
		$w = '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*';
		//Lookaround negative lookbehind (?<!ASD)
		if ($enclosure === "'")
		{
			$str = \preg_replace("/((?<![\\\\])['{$additional}]{1})/", "\\\\$1", $str);
			// \${end of str} -> \\
			$str = \preg_replace("/((?<![\\\\])\\\\)$/", "\\\\$1", $str);
		}
		elseif ($enclosure === '"')
		{
			// " -> \"
			$str = \preg_replace("/((?<![\\\\])[\"{$additional}]{1})/", "\\\\$1", $str);
			// $x -> \$x
			$str = \preg_replace("/((?<![\\\\])[\$]{1}$w)/", "\\\\$1", $str);
			// ${ -> \${
			$str = \preg_replace("/((?<![\\\\])[\$]{1}\s*\{)/", "\\\\$1", $str);
			// \${end of str} -> \\
			$str = \preg_replace("/((?<![\\\\])\\\\)$/", "\\\\$1", $str);
		}
		elseif ($enclosure === '<<<')
		{
			// $x -> \$x
			$str = \preg_replace("/((?<![\\\\])[\$]{1}$w)/", "\\\\$1", $str);
			// ${ -> \${
			$str = \preg_replace("/((?<![\\\\])[\$]{1}\s*\{)/", "\\\\$1", $str);
		}

		return $str;
	}

	/**
	 * Removes escape symbols in given string.
	 *
	 * @param string $str String to unescape.
	 * @param string $enclosure Enclosure symbol " or '.
	 *
	 * @return string
	 */
	public static function unescapePhp($str, $enclosure = '"'): string
	{
		//Lookaround positive lookbehind (?<=ASD)
		// (?<=[\\]+)['\"\\\$]{1}

		if ($enclosure == "'")
		{
			$from = ["\\'"];
			$to = ["'"];
		}
		else
		{
			$from = ["\\\$", "\\\""];
			$to = ["\$", "\""];
		}

		return \str_replace($from, $to, $str);
	}

	/**
	 * Validate phrase for php tokens.
	 * @param string $str
	 * @param string $enclosure
	 * @return bool
	 */
	public static function hasPhpTokens($str, $enclosure = '"'): bool
	{
		$result = false;
		if (!empty($str) && is_string($str))
		{
			if ($enclosure == '<<<')
			{
				$validTokens = [\T_CONSTANT_ENCAPSED_STRING, \T_START_HEREDOC, \T_ENCAPSED_AND_WHITESPACE, \T_END_HEREDOC];
				$validChars = [];
				$tokens = \token_get_all('<'. "?php \$MESS = <<<'HTML'\n".  $str. "\nHTML;");
			}
			else
			{
				$validTokens = [\T_CONSTANT_ENCAPSED_STRING];
				$validChars = [$enclosure];
				$tokens = \token_get_all('<'. '?php $MESS = '. $enclosure. $str. $enclosure . ';');
			}
			$cnt = count($tokens);
			if ($cnt <= 5 || $cnt > 10)
			{
				return true;
			}

			for ($inx = 5, $cnt--; $inx < $cnt ; $inx++)
			{
				$token = $tokens[$inx];
				if (is_array($token))
				{
					$token[] = \token_name($token[0]);
					if (!in_array($token[0], $validTokens))
					{
						$result = true;
						break;
					}
				}
				elseif (is_string($token))
				{
					if (!in_array($token, $validChars))
					{
						$result = true;
						break;
					}
				}

			}
		}

		return $result;
	}
}