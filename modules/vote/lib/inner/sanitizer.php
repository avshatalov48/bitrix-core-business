<?php
namespace Bitrix\Vote\Inner;

class Sanitizer
{
	private static \CBXSanitizer $sanitizer;

	protected static function getSanitizer(): \CBXSanitizer
	{
		if (!isset(static::$sanitizer))
		{
			$sanitizer = new \CBXSanitizer();
			$sanitizer->applyDoubleEncode(false);
			$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
			static::$sanitizer = $sanitizer;
		}
		return static::$sanitizer;
	}

	public static function cleanText(?string $text): string
	{
		if (empty($text) || mb_strpos($text, '<') === false)
		{
			return strval($text);
		}
		return static::getSanitizer()->sanitizeHtml($text);
	}
}