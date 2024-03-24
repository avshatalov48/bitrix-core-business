<?php

namespace Bitrix\Landing\Site\Update;

use Bitrix\Landing\Internals\DomainTable;
use Bitrix\Landing\Manager;
use Bitrix\Main\SystemException;

/**
 * BY domains need to be created in a new online domain zone, replacing existing sites and shops.
 */
class DomainBy extends Update
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
		$domainId = (int)$site['DOMAIN_ID'];
		$domainName = self::getDomainName($domainId);
		if ($domainName !== '')
		{
			$isNeedSiteDomainUpdate = self::isNeedSiteDomainUpdate($domainName);
			$isNeedShopDomainUpdate = self::isNeedShopDomainUpdate($domainName);
			if (!$isNeedSiteDomainUpdate && !$isNeedShopDomainUpdate)
			{
				return true;
			}

			if ($isNeedSiteDomainUpdate && !self::updateDomain($domainId, $domainName, 'site', $site))
			{
				return false;
			}
			if ($isNeedShopDomainUpdate && !self::updateDomain($domainId, $domainName, 'shop', $site))
			{
				return false;
			}
		}

		return true;
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

	/**
	 * Only .by domains, that created by Bitrix
	 *
	 * @param string $domainName
	 * @return bool
	 */
	protected static function isNeedSiteDomainUpdate(string $domainName): bool
	{
		if (str_ends_with($domainName, self::DOMAIN_SITE_BY))
		{
			return true;
		}

		return false;
	}

	/**
	 * Only .by domains, that created by Bitrix
	 *
	 * @param string $domainName
	 * @return bool
	 */
	protected static function isNeedShopDomainUpdate(string $domainName): bool
	{
		if (str_ends_with($domainName, self::DOMAIN_SHOP_BY))
		{
			return true;
		}

		return false;
	}

	/**
	 * Create new random domain, save in table
	 *
	 * @param int $domainId
	 * @param string $domainName
	 * @param string $type
	 * @param array $site - site data array
	 *
	 * @return bool
	 */
	protected static function updateDomain(int $domainId, string $domainName, string $type, array $site): bool
	{
		try
		{
			$siteController = Manager::getExternalSiteController();
			$publicUrl = Manager::getPublicationPath($site['ID']);
			if ($siteController)
			{
				$prevDomainName = $domainName;
				if ($type === 'site')
				{
					$domainName = str_replace(self::DOMAIN_SITE_BY, self::DOMAIN_SITE_ONLINE, $domainName);
				}
				if ($type === 'shop')
				{
					$domainName = str_replace(self::DOMAIN_SHOP_BY, self::DOMAIN_SHOP_ONLINE, $domainName);
				}

				if ($domainName && !$siteController::isDomainExists($domainName))
				{
					$resDomain = DomainTable::update($domainId, [
						'DOMAIN' => $domainName,
						'PREV_DOMAIN' => $prevDomainName,
					]);

					$siteController::updateDomain(
						$prevDomainName,
						$domainName,
						$publicUrl
					);

					return $resDomain->isSuccess();
				}

				$domainName = $siteController::addRandomDomain(
					$publicUrl,
					$site['TYPE'],
					'by'
				);
				$resDomain = DomainTable::update($domainId, [
					'DOMAIN' => $domainName,
					'PREV_DOMAIN' => $prevDomainName,
				]);

				$siteController::updateDomain(
					$prevDomainName,
					$domainName,
					$publicUrl
				);

				return $resDomain->isSuccess();
			}
		}
		catch (SystemException)
		{
		}

		return false;
	}
}
