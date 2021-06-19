<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main\Loader;

class Subscription
{
	public static function onContentViewed(array $params)
	{
		if (
			!isset($params['userId'], $params['logId'])
			|| !is_array($params)
			|| (int)$params['userId'] <= 0
			|| (int)$params['logId'] <= 0
			|| !Loader::includeModule('im')
		)
		{
			return;
		}

		$CIMNotify = new \CIMNotify();
		$CIMNotify->markNotifyReadBySubTag(array("SONET|EVENT|".(int)$params['logId']."|".(int)$params['userId']));
	}
}
