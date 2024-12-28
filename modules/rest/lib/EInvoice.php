<?php

namespace Bitrix\Rest;

use Bitrix\Main;
use Bitrix\Rest\Engine\Access;

class EInvoice
{
	public const APP_TAG = 'e-invoice';

	public static function isAvailable(): bool
	{
		return Access::isAvailable() && in_array(Main\Application::getInstance()->getLicense()->getRegion(), ['de', 'it', 'pl']);
	}

	public static function getApplicationList(): array
	{
		$cache = Main\Application::getInstance()->getCache();
		$cacheId = self::APP_TAG . '_marketplace';
		$cacheTtl = 60 * 60 * 24; // 24 hour
		$cachePath = '/rest/einvoice/';
		$region = Main\Application::getInstance()->getLicense()->getRegion();
		$tags = [self::APP_TAG, $region];

		if ($cache->initCache($cacheTtl, $cacheId, $cachePath))
		{
			$result = $cache->getVars();
		}
		else
		{
			$result = Marketplace\Client::getByTag($tags)['ITEMS'] ?? [];
			$cache->startDataCache();
			$cache->endDataCache($result);
		}

		return $result;
	}

	public static function getInstalledApplications(): array
	{
		$applicationList = self::getApplicationList();
		$codes = [];

		foreach ($applicationList as $application)
		{
			if (isset($application['CODE']))
			{
				$codes[] = $application['CODE'];
			}
		}

		if (empty($codes))
		{
			return [];
		}

		$result = AppTable::query()
			->whereIn('CODE', $codes)
			->setSelect([
				'*',
				'MENU_NAME' => 'LANG.MENU_NAME',
				'MENU_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
				'MENU_NAME_LICENSE' => 'LANG_LICENSE.MENU_NAME',
			])
			->exec();

		return $result->fetchAll();
	}
}