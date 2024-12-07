<?php

namespace Bitrix\Catalog\v2\Integration\Landing;

use Bitrix\Landing\Internals\SiteTable;
use Bitrix\Landing\Site;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;

final class ShopManager
{
	private bool $isLandingIncluded;
	private const EXCLUDED_SITES = [
		'store-chats-dark',
		'store-chats-light',
		'store-chats',
	];

	public function __construct()
	{
		$this->isLandingIncluded = Loader::includeModule('landing');
	}

	public function areTherePublishedShops(): bool
	{
		return !empty($this->getPublishedShopsIds());
	}

	private function getPublishedShopsIds(): array
	{
		if (!$this->isLandingIncluded)
		{
			return [];
		}

		$result = [];

		$activeShopsList = SiteTable::getList([
			'select' => [
				'ID',
				'TYPE',
				'ACTIVE',
				'TPL_CODE',
			],
			'filter' => [
				'=TYPE' => 'STORE',
				'=ACTIVE' => 'Y',
			],
		]);

		while ($site = $activeShopsList->fetch())
		{
			if (in_array($site['TPL_CODE'], self::EXCLUDED_SITES, true))
			{
				continue;
			}

			$result[] = (int)$site['ID'];
		}

		return $result;
	}

	public function unpublishShops(): Result
	{
		$result = new Result();

		if (!$this->isLandingIncluded)
		{
			$result->addError(new Error('The landing module is not installed'));

			return $result;
		}

		$publishedShopsIds = $this->getPublishedShopsIds();
		foreach ($publishedShopsIds as $publishedShopsId)
		{
			$siteResult = Site::unpublic((int)$publishedShopsId);
			if (!$siteResult->isSuccess())
			{
				$result->addErrors($siteResult->getErrors());
			}
		}

		return $result;
	}
}
