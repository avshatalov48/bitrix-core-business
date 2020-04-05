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
			!is_array($params)
			|| !isset($params['userId'])
			|| intval($params['userId']) <= 0
			|| !isset($params['logId'])
			|| intval($params['logId']) <= 0
			|| !Loader::includeModule('im')
		)
		{
			return;
		}

		$CIMNotify = new \CIMNotify();
		$CIMNotify->markNotifyReadBySubTag(array("SONET|EVENT|".intval($params['logId'])."|".intval($params['userId'])));
	}
}
