<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class __CIBlockTV
{
	public static function Prepare($Value)
	{
		return str_replace(array("\r\n", "\r", "\n"), array("<br>", "<br>", "<br>"), CUtil::addslashes(htmlspecialcharsbx($Value)));
	}
}

class CBitrixIBlockTV extends CBitrixComponent
{
}
