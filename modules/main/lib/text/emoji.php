<?php
namespace Bitrix\Main\Text;
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */
class Emoji
{
	private static $emojiPattern = '%(?:
		\xF0[\x90-\xBF][\x80-\xBF]{2} # planes 1-3
		| [\xF1-\xF3][\x80-\xBF]{3} # planes 4-15
		| \xF4[\x80-\x8F][\x80-\xBF]{2} # plane 16
	)%xs';

	public static function encode($text)
	{
		return self::replace($text, function ($m) {
			return ":".bin2hex($m[0]).":";
		});
	}

	public static function decode($text)
	{
		return preg_replace_callback("/:([A-F0-9]{8}):/isu", function ($m)
		{
			$result = hex2bin($m[1]);

			if (preg_match(self::$emojiPattern, $result))
			{
				return $result;
			}

			return $m[0];
		}, $text);
	}

	/**
	 * @param string $text
	 * @param callable $callback
	 *
	 * @return string|string[]|null
	 */
	public static function replace($text, $callback)
	{
		return preg_replace_callback(self::$emojiPattern, $callback, $text);
	}

	public static function getSaveModificator()
	{
		return array(
			array(__CLASS__, 'encode')
		);
	}

	public static function getFetchModificator()
	{
		return array(
			array(__CLASS__, 'decode')
		);
	}
}
