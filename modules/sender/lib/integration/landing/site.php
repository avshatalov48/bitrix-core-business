<?php

namespace Bitrix\Sender\Integration\Landing;

class Site
{
	private static function canUse(): bool
	{
		return \Bitrix\Main\Loader::includeModule('landing');
	}

	/**
	 * Return site page and store public urls
	 * @return array
	 */
	public static function getLandingAndStorePublicUrls(): array
	{
		if (!static::canUse())
		{
			return [];
		}

		$siteIds = \Bitrix\Landing\Site::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'@TYPE' => ['PAGE', 'STORE'],
			],
		]);
		$siteIds = array_column($siteIds->fetchAll(), 'ID');

		if (empty($siteIds))
		{
			return [];
		}

		return array_values((array)\Bitrix\Landing\Site::getPublicUrl($siteIds));
	}
}