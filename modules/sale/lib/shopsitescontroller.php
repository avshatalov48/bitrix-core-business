<?php

namespace Bitrix\Sale;

abstract class ShopSitesController
{
	public static function getShops(): array
	{
		static $shops = null;
		if (is_array($shops))
		{
			return $shops;
		}

		$shops = [];
		$siteIterator = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID', 'NAME', 'SORT'],
			'order' => ['SORT' => 'ASC'],
			'cache' => ['ttl' => 86400],
		]);
		while ($site = $siteIterator->fetch())
		{
			$saleSite = \Bitrix\Main\Config\Option::get('sale', 'SHOP_SITE_'.$site['LID']);
			if ($site['LID'] === $saleSite)
			{
				$shops[] = $site;
			}
		}

		return $shops;
	}
}