<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Web;

class HttpCookies extends \Bitrix\Main\Type\Dictionary
{
	public function __construct(array $values = null)
	{
		parent::__construct($values);
	}

	public function toString()
	{
		$str = "";
		foreach($this->values as $name => $value)
		{
			$str .= ($str == ""? "" : "; ").rawurlencode($name)."=".rawurlencode($value);
		}
		return $str;
	}

	public function addFromString($str)
	{
		if (($pos = mb_strpos($str, ';')) !== false && $pos > 0)
		{
			$cookie = trim(mb_substr($str, 0, $pos));
		}
		else
		{
			$cookie = trim($str);
		}
		$arCookie = explode('=', $cookie, 2);

		$this[rawurldecode($arCookie[0])] = rawurldecode($arCookie[1]);
	}
}
