<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\Data\Cache;
use Bitrix\Rest\Dictionary;
use CRestUtil;

/**
 * Class Webhook
 * @package Bitrix\Rest\Preset\Data
 */
class Webhook
{
	private const CACHE_TIME = 86400;
	private const CACHE_DIR = '/rest/integration/data/webhook/';

	/**
	 * @return array
	 */
	public static function getList() : array
	{
		$result = [];

		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'all' . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$eventDictionary = new Dictionary\WebHook();
			$eventDictionaryResult = [];
			foreach ($eventDictionary as $event)
			{
				$eventDictionaryResult[mb_strtoupper($event['code'])] = $event;
			}
			$eventList = CRestUtil::getEventList();
			$eventDistinctId = [];
			foreach ($eventList as $type => $events)
			{
				foreach ($events as $event)
				{
					if (array_key_exists($event, $eventDictionaryResult) && !in_array(mb_strtoupper($event), $eventDistinctId))
					{
						$event = mb_strtoupper($event);
						$eventDistinctId[] = $event;

						$result[] = [
							'id' => $event,
							'name' => !empty($eventDictionaryResult[$event]['name'])
								? $eventDictionaryResult[$event]['name'] . ' (' . $event . ')' : $event,
							'descr' => !empty($eventDictionaryResult[$event]['descr'])
								? $eventDictionaryResult[$event]['descr'] : '',
						];
					}
				}
			}
			$cache->endDataCache($result);
		}

		return $result;
	}
}