<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\Data\Cache;
use Bitrix\Rest\Dictionary;

/**
 * Class Placement
 * @package Bitrix\Rest\Preset\Dictionary
 */
class Placement
{
	private const CACHE_TIME = 86400;
	private const CACHE_DIR = '/rest/integration/data/placement/';

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
			$placementDictionary = new Dictionary\Placement();
			foreach ($placementDictionary as $event)
			{
				$result[] = [
					'id' => $event['code'],
					'name' => !empty($event['name']) ? $event['name'] . ' (' . $event['code'] . ')' : $event['code'],
					'descr' => !empty($event['descr']) ? $event['descr'] : '',
				];
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}