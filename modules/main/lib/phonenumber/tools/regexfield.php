<?php

namespace Bitrix\Main\PhoneNumber\Tools;

use Bitrix\Main\SystemException;

class RegexField extends XmlField
{
	public function decodeValue($value)
	{
		return static::validateRegex($value, true);
	}

	public static function validateRegex($regex, $removeWhitespace = false)
	{
		$compressedRegex = $removeWhitespace ? preg_replace('/\\s/', '', $regex) : $regex;

		// Match regex against an empty string to check the regex is valid
		if (preg_match('/'.$compressedRegex.'/', '') === false)
		{
			throw new SystemException("Regex error: ".preg_last_error());
		}

		return $compressedRegex;
	}

}