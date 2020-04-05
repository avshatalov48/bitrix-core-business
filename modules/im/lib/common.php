<?php
namespace Bitrix\Im;

class Common
{
	public static function getPublicDomain()
	{
		return (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".((defined("SITE_SERVER_NAME") && strlen(SITE_SERVER_NAME) > 0) ? SITE_SERVER_NAME : \Bitrix\Main\Config\Option::get("main", "server_name", $_SERVER['SERVER_NAME']));
	}

	public static function objectEncode($params)
	{
		if (is_array($params))
		{
			array_walk_recursive($params, function(&$item, $key){
				if ($item instanceof \Bitrix\Main\Type\DateTime)
				{
					$item = date('c', $item->getTimestamp());
				}
			});
		}

		return \CUtil::PhpToJSObject($params);
	}

	public static function getCacheUserPostfix($id)
	{
		return '/'.substr(md5($id),2,2).'/'.intval($id);
	}

	public static function isChatId($id)
	{
		return $id && preg_match('/^chat[0-9]{1,}$/i', $id);
	}

	public static function isDialogId($id)
	{
		return $id && preg_match('/^[0-9]{1,}|chat[0-9]{1,}$/i', $id);
	}

	public static function getUserId($userId = null)
	{
		if (is_null($userId) && is_object($GLOBALS['USER']))
		{
			$userId = $GLOBALS['USER']->getId();
		}

		$userId = intval($userId);
		if (!$userId)
		{
			return false;
		}

		return $userId;
	}
}

