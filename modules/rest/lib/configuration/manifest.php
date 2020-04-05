<?php

namespace Bitrix\Rest\Configuration;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager;

class Manifest
{

	const ON_REST_APP_CONFIGURATION_GET_MANIFEST = 'OnRestApplicationConfigurationGetManifest';
	private static $manifestList = [];

	public static function getList()
	{
		if(empty(static::$manifestList))
		{
			$event = new Event('rest', static::ON_REST_APP_CONFIGURATION_GET_MANIFEST);
			EventManager::getInstance()->send($event);
			foreach ($event->getResults() as $eventResult)
			{
				$manifestList = $eventResult->getParameters();
				if(is_array($manifestList))
				{
					static::$manifestList = array_merge(static::$manifestList, $manifestList);
				}
			}
		}

		return static::$manifestList;
	}

	public static function get($code)
	{
		$result = null;
		if($code != '')
		{
			$manifestList = static::getList();
			$key = array_search($code, array_column($manifestList, 'CODE'));
			if($key !== false)
			{
				$result = $manifestList[$key];
			}
		}

		return $result;
	}
}