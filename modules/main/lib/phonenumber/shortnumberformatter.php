<?php

namespace Bitrix\Main\PhoneNumber;

/**
 * Formatter for invalid short numbers, like 32-12-43, just to make them look pretty.
 * @package Bitrix\Main\PhoneNumber
 */
class ShortNumberFormatter
{
	protected static $templates = [
		3 => 'x-xx',
		4 => 'xx-xx',
		5 => 'x-xx-xx',
		6 => 'xx-xx-xx',
		7 => 'xxx-xx-xx'
	];

	/**
	 * Pretty prints some invalid short number.
	 * @param PhoneNumber $phoneNumber Phone number.
	 * @return string
	 */
	public static function format(PhoneNumber $phoneNumber)
	{
		$rawNumber = $phoneNumber->getNationalNumber();
		$template = static::$templates[mb_strlen($rawNumber)];
		if(!$template)
		{
			return $rawNumber;
		}

		$pattern = preg_replace("/[^x]/", "", $template);
		$pattern = str_replace("x", "(\\d)", $pattern);
		$pattern = "/" . $pattern . "/";
		$i = 0;
		$format = preg_replace_callback(
			"/x/",
			function () use (&$i)
			{
				return "$" . ++$i;
			},
			$template
		);

		$result = preg_replace($pattern, $format, $rawNumber);
		if($phoneNumber->getExtensionSeparator())
		{
			$result .= $phoneNumber->getExtensionSeparator() . " " . $phoneNumber->getExtension();
		}
		return $result;
	}

	/**
	 * Return true if the phone number could be formatted using this formatter and false otherwise.
	 * @param PhoneNumber $phoneNumber Phone number.
	 * @return bool
	 */
	public static function isApplicable(PhoneNumber $phoneNumber)
	{
		if($phoneNumber->isValid() || $phoneNumber->hasPlus())
			return false;

		$rawNumber = $phoneNumber->getNationalNumber();
		return preg_match("/^\d{3,7}$/", $rawNumber);
	}
}