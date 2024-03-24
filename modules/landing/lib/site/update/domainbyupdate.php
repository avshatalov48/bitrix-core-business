<?php

namespace Bitrix\Landing\Site\Update;

use Bitrix\Landing\Internals\DomainTable;
use Bitrix\Landing\Manager;

/**
 * BY domains must be updated under the control of a new online domain zone, after replacing existing sites and stores.
 */
class DomainByUpdate extends Update
{
	protected const DOMAIN_SITE_BY = '.bitrix24site.by';
	protected const DOMAIN_SHOP_BY = '.bitrix24shop.by';
	protected const DOMAIN_SITE_ONLINE = '.b24site.online';
	protected const DOMAIN_SHOP_ONLINE = '.b24shop.online';

	/**
	 * Entry point. Returns true on success.
	 *
	 * @param int $siteId Site id.
	 *
	 * @return bool
	 */
	public static function update(int $siteId): bool
	{
		$site = self::getId($siteId);
		self::updateDomainIfNeeded($site);

		return true;
	}

	/**
	 * Only reg domain
	 *
	 * @param string $domainName
	 * @param int $domainId
	 * @return bool
	 */
	protected static function updateDomainIfNeeded(array $site): void
	{
		$domainId = (int)$site['DOMAIN_ID'];
		$domainName = self::getDomainName($domainId);
		$siteController = Manager::getExternalSiteController();
		$publicUrl = Manager::getPublicationPath($site['ID']);

		//for site
		$domainSite = '';
		$prevDomainSite = '';
		if (str_ends_with($domainName, self::DOMAIN_SITE_ONLINE))
		{
			$res = DomainTable::getList([
				'select' => [
					'ID', 'DOMAIN', 'PREV_DOMAIN',
				],
				'filter' => [
					'ID' => $domainId,
				],
			]);
			if (($row = $res->fetch()) && isset($row['DOMAIN']))
			{
				$domainSite =  $row['DOMAIN'];
				$prevDomainSite =  $row['PREV_DOMAIN'];
			}
			if ($domainSite !== '' && $prevDomainSite !== '' && str_ends_with($prevDomainSite, self::DOMAIN_SITE_BY))
			{
				$subDomainSite = str_replace(self::DOMAIN_SITE_ONLINE, '', $domainSite);
				$prevSubDomainSite = str_replace(self::DOMAIN_SITE_BY, '', $prevDomainSite);
				if ($subDomainSite === $prevSubDomainSite)
				{
					$siteController::updateDomain(
						$prevDomainSite,
						$domainSite,
						$publicUrl
					);
				}
			}
		}

		//for shop
		$domainShop = '';
		$prevDomainShop = '';
		if (str_ends_with($domainName, self::DOMAIN_SHOP_ONLINE))
		{
			$res = DomainTable::getList([
				'select' => [
					'ID', 'DOMAIN', 'PREV_DOMAIN',
				],
				'filter' => [
					'ID' => $domainId,
				],
			]);
			if (($row = $res->fetch()) && isset($row['DOMAIN']))
			{
				$domainShop =  $row['DOMAIN'];
				$prevDomainShop =  $row['PREV_DOMAIN'];
			}
			if ($domainShop !== '' && $prevDomainShop !== '' && str_ends_with($prevDomainShop, self::DOMAIN_SHOP_BY))
			{
				$subDomainShop = str_replace(self::DOMAIN_SHOP_ONLINE, '', $domainShop);
				$prevSubDomainShop = str_replace(self::DOMAIN_SHOP_BY, '', $prevDomainShop);
				if ($subDomainShop === $prevSubDomainShop)
				{
					$siteController::updateDomain(
						$prevDomainShop,
						$domainShop,
						$publicUrl
					);
				}
			}
		}
	}

	protected static function getDomainName(int $domainId): string
	{
		$res = DomainTable::getList([
			'select' => [
				'ID', 'DOMAIN',
			],
			'filter' => [
				'ID' => $domainId,
			],
		]);
		if (($row = $res->fetch()) && isset($row['DOMAIN']))
		{
			return $row['DOMAIN'];
		}

		return '';
	}
}
