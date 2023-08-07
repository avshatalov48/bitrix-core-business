<?php

namespace Bitrix\Landing\Site\Update;

use Bitrix\Landing\Internals\DomainTable;
use Bitrix\Landing\Internals\LandingTable;
use Bitrix\Landing\Manager;
use Bitrix\Main\SystemException;

/**
 * UA domains now is disabled. Need create new domains, replace exists in sites and form.
 */
class DomainUa extends Update
{
	protected const DOMAIN_UA = '.bitrix24site.ua';
	protected const OPTION_IS_FORM_REBUILD = 'is_ua_forms_rebuild';

	/**
	 * Entry point. Returns true on success.
	 * @param int $siteId Site id.
	 * @return bool
	 */
	public static function update(int $siteId): bool
	{
		$site = self::getId($siteId);
		$domainId = (int)$site['DOMAIN_ID'];


		if (!self::isNeedDomainUpdate($domainId))
		{
			return true;
		}

		if (
			!self::updateDomain($domainId, $site)
			|| !self::unPublicationPages($siteId)
		)
		{
			return false;
		}

		if (self::isNeedFormsUpdate())
		{
			self::updateForms();
		}

		return true;
	}

	/**
	 * Only .ua domains, that created by Bitrix
	 *
	 * @param int $domainId
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected static function isNeedDomainUpdate(int $domainId): bool
	{
		$res = DomainTable::getList([
			'select' => [
				'ID', 'DOMAIN',
			],
			'filter' => [
				'ID' => $domainId,
			],
		]);
		if ($row = $res->fetch())
		{
			// todo: check if it is our domain?
			$domainLength = strlen(self::DOMAIN_UA);

			return substr($row['DOMAIN'], -1 * $domainLength) === self::DOMAIN_UA;
		}

		return false;
	}

	/**
	 * Create new random domain, save in table
	 *
	 * @param int $domainId
	 * @param array $site - site data array
	 * @return bool
	 */
	protected static function updateDomain(int $domainId, array $site): bool
	{
		try
		{
			$siteController = Manager::getExternalSiteController();
			if ($siteController)
			{
				$publicUrl = Manager::getPublicationPath($site['ID']);
				$zone = Manager::getZone();
				$domainName = $siteController::addRandomDomain(
					$publicUrl,
					$site['TYPE'],
					$zone === 'ua' ? 'eu' : $zone
				);


				if ($domainName)
				{
					// todo: need set prev id?
					$resDomain = DomainTable::update($domainId, [
						'DOMAIN' => $domainName,
					]);

					return $resDomain->isSuccess();
				}
			}
		}
		catch (SystemException $ex)
		{
		}

		return false;
	}

	/**
	 * Set pages in current site as non-public, for update in first manually publication
	 *
	 * @param int $siteId
	 * @return bool
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	protected static function unPublicationPages(int $siteId): bool
	{
		$res = LandingTable::getList([
			'select' => ['ID'],
			'filter' => [
				'SITE_ID' => $siteId,
				'ACTIVE' => 'Y',
			],
		]);
		while ($landing = $res->fetch())
		{
			$resUpdate = LandingTable::update($landing['ID'], [
				'ACTIVE' => 'N',
				'PUBLIC' => 'N',
			]);

			if (!$resUpdate->isSuccess())
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Need just once
	 * @return bool
	 */
	protected static function isNeedFormsUpdate(): bool
	{
		$option = Manager::getOption(self::OPTION_IS_FORM_REBUILD, 'N');


		return $option === 'N';
	}

	/**
	 * Rebuild form and button scripts, analytic
	 * @return bool
	 */
	protected static function updateForms(): bool
	{
		\CAgent::AddAgent(
			'\\Bitrix\\Crm\\UI\\Webpack\\Guest::rebuildAgent();',
			"crm",
			"N",
			60,
			"",
			"Y",
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 60, "FULL")
		);

		\CAgent::AddAgent(
			'\\Bitrix\\Crm\\WebForm\\Manager::updateScriptCacheAgent();',
			"crm",
			"N",
			60,
			"",
			"Y",
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 100, "FULL")
		);

		\CAgent::AddAgent(
			'\\Bitrix\\Crm\\SiteButton\\Manager::updateScriptCacheAgent();',
			"crm",
			"N",
			60,
			"",
			"Y",
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 300, "FULL")
		);

		\CAgent::AddAgent(
			'\\Bitrix\\Crm\\UI\\Webpack\\CallTracker::rebuildAgent();',
			"crm",
			"N",
			60,
			"",
			"Y",
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 400, "FULL")
		);

		\CAgent::AddAgent(
			'\\Bitrix\\Crm\\UI\\Webpack\\CallTrackerEditor::rebuildAgent();',
			"crm",
			"N",
			60,
			"",
			"Y",
			\ConvertTimeStamp(time() + \CTimeZone::GetOffset() + 450, "FULL")
		);

		Manager::setOption(self::OPTION_IS_FORM_REBUILD, 'Y');

		// now is always true. If error - rebuild forms manually, not by updater
		return true;
	}
}
