<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\Dictionary\IntegrationSection;

/**
 * Class Section
 * @package Bitrix\Rest\Preset\Data
 */
class Section
{
	private const CACHE_TIME = 86400;
	private const CACHE_DIR = '/rest/integration/section/';

	/**
	 * @return array
	 * @throws ArgumentException
	 */
	public static function get() : array
	{
		$result = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'sectionsIndex' . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$dictionary = new IntegrationSection();
			foreach ($dictionary as $el)
			{
				if (!empty($el['option']))
				{
					$data = Json::decode(base64_decode($el['option']));
					if (is_array($data))
					{
						$data['CODE'] = $data['SECTION_CODE'];
						$result[$data['CODE']] = $data;
					}
				}
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}