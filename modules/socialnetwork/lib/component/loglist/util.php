<?php

namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Socialnetwork\ComponentHelper;

class Util extends \Bitrix\Socialnetwork\Component\LogListCommon\Util

{
	public static function checkEmptyParamInteger(&$params, $paramName, $defaultValue): void
	{
		ComponentHelper::checkEmptyParamInteger($params, $paramName, $defaultValue);
	}

	public static function checkEmptyParamString(&$params, $paramName, $defaultValue): void
	{
		ComponentHelper::checkEmptyParamString($params, $paramName, $defaultValue);
	}

	public static function checkUserAuthorized(): bool
	{
		global $USER;
		return (isset($USER) && is_object($USER) ? $USER->isAuthorized() : false);
	}
}
