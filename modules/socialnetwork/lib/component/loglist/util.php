<?php
namespace Bitrix\Socialnetwork\Component\LogList;

class Util
{
	public static function checkEmptyParamInteger(&$params, $paramName, $defaultValue)
	{
		$params[$paramName] = (isset($params[$paramName]) && intval($params[$paramName]) > 0 ? intval($params[$paramName]) : $defaultValue);
	}

	public static function checkEmptyParamString(&$params, $paramName, $defaultValue)
	{
		$params[$paramName] = (isset($params[$paramName]) && trim($params[$paramName]) <> '' ? trim($params[$paramName]) : $defaultValue);
	}

	public static function getRequest()
	{
		return \Bitrix\Main\Context::getCurrent()->getRequest();
	}

	public static function checkUserAuthorized()
	{
		global $USER;
		return (isset($USER) && is_object($USER) ? $USER->isAuthorized() : false);
	}

	public static function getCollapsedPinnedPanelItemsLimit()
	{
		return 3;
	}
}
?>