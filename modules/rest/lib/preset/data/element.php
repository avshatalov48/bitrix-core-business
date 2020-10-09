<?php

namespace Bitrix\Rest\Preset\Data;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Web\Json;
use Bitrix\Rest\Dictionary\Integration;

/**
 * Class Element
 * @package Bitrix\Rest\Preset\Data
 */
class Element
{
	private const CACHE_TIME = 86400;
	private const CACHE_DIR = '/rest/integration/element/';
	public const DEFAULT_APPLICATION = 'application';
	public const DEFAULT_IN_WEBHOOK = 'in-hook';
	public const DEFAULT_OUT_WEBHOOK = 'out-hook';

	/**
	 * @param $code string
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	public static function get($code) : array
	{
		$result = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'item' . $code . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$remoteDictionary = new Integration();
			$dictionary = $remoteDictionary->toArray();
			if (!empty($dictionary))
			{
				$dictionaryCode = array_column($dictionary, 'code');
				$key = array_search($code, $dictionaryCode, true);
				if ($key !== false)
				{
					$el = $dictionary[$key];
					if (!empty($el['option']))
					{
						$data = Json::decode(base64_decode($el['option']));
						if (is_array($data))
						{
							$data['CODE'] = $data['ELEMENT_CODE'];
							$result = $data;
						}
					}
				}
			}

			$cache->endDataCache($result);
		}

		return $result;
	}

	/**
	 * @param $sectionCode string
	 *
	 * @return array
	 * @throws ArgumentException
	 */
	public static function getList($sectionCode) : array
	{
		$result = [];
		$cache = Cache::createInstance();
		if ($cache->initCache(static::CACHE_TIME, 'section' . $sectionCode . LANGUAGE_ID, static::CACHE_DIR))
		{
			$result = $cache->getVars();
		}
		elseif ($cache->startDataCache())
		{
			$dictionary = new Integration();

			foreach ($dictionary as $el)
			{
				if (!empty($el['option']))
				{
					$data = Json::decode(base64_decode($el['option']));
					if (is_array($data) && $sectionCode === $data['SECTION_CODE'])
					{
						$data['CODE'] = $data['ELEMENT_CODE'];
						$result[$data['CODE']] = $data;
					}
				}
			}

			$cache->endDataCache($result);
		}

		return $result;
	}
}