<?php

namespace Bitrix\Sale;

abstract class ShopSitesController
{
	public static function getShops(): array
	{
		$shops = [];
		$siteIterator = \Bitrix\Main\SiteTable::getList([
			'select' => ['LID', 'NAME', 'SORT'],
			'order' => ['SORT' => 'ASC'],
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