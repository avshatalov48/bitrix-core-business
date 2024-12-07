<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */
namespace Bitrix\Main\Text;

class HtmlFilter
{
	public static function encode($string, $flags = ENT_COMPAT, $doubleEncode = true)
	{
		return htmlspecialchars((string)$string, $flags, "UTF-8", $doubleEncode);
	}
}
