<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Main\Text;

class HtmlFilter
{
	public static function encode($string, $flags = ENT_COMPAT, $doubleEncode = true)
	{
		return htmlspecialchars($string, $flags, (defined("BX_UTF") ? "UTF-8" : "ISO-8859-1"), $doubleEncode);
	}
}
