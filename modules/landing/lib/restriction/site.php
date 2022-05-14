<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;
use \Bitrix\Landing\Manager;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Domain;
use \Bitrix\Landing\Rights;
use \Bitrix\Landing\Site as SiteCore;
use Bitrix\Main\Loader;
use \Bitrix\Main\Type\DateTime;

class Site
{
	/**
	 * Allowed days for use free domain after downgrade plan.
	 */
	const FREE_DOMAIN_GRACE_DAYS = 14;

	/**
	 * When limit is minimal we allow public within these limits.
	 */
	private const LIMIT_BY_TEMPLATES_MINIMAL = [
		'store-chats' => 1,
	];

	/**
	 * Templates, than no increase common limit
	 */
	private const OVER_LIMIT_TEMPLATES = [
		'store-chats'
	];

	/**
	 * names for special templates
	 */
	private const NEW_STORE_CODE = 'store_v3';

	/**
	 * Checks limits by template's limits.
	 * @param array $filter filter array.
	 * @param int $limit Current limit.
	 * @return bool
	 */
	private static function checkLimitByTemplates(array $filter, int $limit): bool
	{
		$sites = [];
		$currentSiteId = null;
		$currentSiteTemplate = null;

		if (isset($filter['!ID']))
		{
			$currentSiteId = $filter['!ID'];
		}

		// get all sites (active) and group by templates
		$res = \Bitrix\Landing\Site::getList([
			'select' => [
				'ID', 'XML_ID', 'TPL_CODE'
			],
			'filter' => $filter
		]);
		while ($row = $res->fetch())
		{
			$sites[] = $row;
		}

		// current site
		if ($currentSiteId)
		{
			$res = \Bitrix\Landing\Site::getList([
				'select' => [
					'ID', 'XML_ID', 'TPL_CODE'
				],
				'filter' => [
					'CHECK_PERMISSIONS' => 'N',
					'ID' => $currentSiteId
				]
			]);
			if ($row = $res->fetch())
			{
				$sites[] = $row;
			}
		}

		// calc templates
		$templates = [];
		$templatesCount = 0;
		$templatesLimits = self::LIMIT_BY_TEMPLATES_MINIMAL;
		// $templatesLimits['%'] = max($limit, 1);
		$limit = max($limit, 1);
		foreach ($sites as $row)
		{
			if (!$row['TPL_CODE'])
			{
				if (mb_strpos($row['XML_ID'], '|') !== false)
				{
					[, $row['TPL_CODE']] = explode('|', $row['XML_ID']);
				}
			}

			// store-chat-dark === store-chat-light === store-chat
			foreach ($templatesLimits as $code => $cnt)
			{
				if (strpos($row['TPL_CODE'], $code) === 0)
				{
					$row['TPL_CODE'] = $code;
					break;
				}
			}

			if ($currentSiteId && $currentSiteId === $row['ID'])
			{
				$currentSiteTemplate = $row['TPL_CODE'];
			}

			if (!($templates[$row['TPL_CODE']] ?? null))
			{
				$templates[$row['TPL_CODE']] = 0;
			}
			$templates[$row['TPL_CODE']]++;

			if (!in_array($row['TPL_CODE'], self::OVER_LIMIT_TEMPLATES, true))
			{
				$templatesCount++;
			}
		}

		// special limit for store v3
		if (
			$currentSiteTemplate
			&& $currentSiteTemplate === self::NEW_STORE_CODE
			&& self::isNew2021Tariff()
		)
		{
			\CBitrixComponent::includeComponentClass('bitrix:landing.site_master');
			$optionName = \LandingSiteMasterComponent::getShopInstallCountOptionName($currentSiteTemplate);
			if ((int)Manager::getOption($optionName, 0) <= 1)
			{
				$limit++;
			}
		}

		// calc limits
		if (
			$currentSiteTemplate
			&& in_array($row['TPL_CODE'], self::OVER_LIMIT_TEMPLATES, true)
		)
		{
			return true;
		}

		if ($templates)
		{
			foreach ($templatesLimits as $code => $templateLimit)
			{
				if (
					$templates[$code]
					&& $templates[$code] > $templateLimit
				)
				{
					return false;
				}
			}
		}

		if ($limit < $templatesCount)
		{
			return false;
		}

		return true;
	}

	protected static function isNew2021Tariff(): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return false;
		}

		return in_array(
			\CBitrix24::getLicenseType(),
			['basic', 'std', 'pro']
		);
	}

	/**
	 * Checks restriction for creating and publication site.
	 * @param string $code Restriction code (not used here).
	 * @param array $params Additional params.
	 * @return bool
	 */
	public static function isCreatingAllowed(string $code, array $params): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return true;
		}

		if (
			$params['action_type'] === 'publication'
			&& Manager::licenseIsFreeSite($params['type'])
			&& !Manager::isFreePublicAllowed()
		)
		{
			if (!isset($params['filter']['!ID']))
			{
				return false;
			}

			$siteId = $params['filter']['!ID'];
			$site = SiteCore::getList([
				'select' => ['ID' , 'DATE_CREATE'],
				'filter' => ['ID' => $siteId],
			])->fetch();
			$dateWhenClosedFree = DateTime::createFromTimestamp('1646238000');  //02.03.2022 16:20:00
			if ($site['DATE_CREATE']->getTimestamp() >= $dateWhenClosedFree->getTimestamp())
			{
				return false;
			}
		}

		$optPrefix = 'landing_site_';
		$optSuffix = ($params['action_type'] == 'publication') ? '_publication' : '';
		$variableCode = $optPrefix . strtolower($params['type']) . $optSuffix;
		$limit = (int) Feature::getVariable($variableCode);

		if ($limit)
		{
			$filter = [
				'CHECK_PERMISSIONS' => 'N',
				'=TYPE' => $params['type'],
				'=SPECIAL' => 'N'
			];

			if ($params['action_type'] == 'publication')
			{
				$filter['=ACTIVE'] = 'Y';
			}

			if (
				isset($params['filter']) &&
				is_array($params['filter'])
			)
			{
				$filter = array_merge(
					$filter,
					$params['filter']
				);
			}

			if ($params['action_type'] === 'publication' && $params['type'] === 'STORE')
			{
				return self::checkLimitByTemplates($filter, $limit);
			}

			$check = SiteCore::getList([
				'select' => [
					'CNT' => new Entity\ExpressionField('CNT', 'COUNT(*)')
				],
				'filter' => $filter,
				'group' => []
			])->fetch();
			if ($check && $check['CNT'] >= $limit)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks restriction for free domain.
	 * @param string $code Restriction code (not used here).
	 * @param array $params Additional params.
	 * @return bool
	 */
	public static function isFreeDomainAllowed(string $code, array $params): bool
	{
		// free domain is available in cloud version only
		if (!Loader::includeModule('bitrix24'))
		{
			return false;
		}

		$availableCount = Feature::getVariable(
			'landing_free_domain'
		);
		if ($availableCount === null)
		{
			return false;
		}
		if (($params['trueOnNotNull'] ?? false))
		{
			return true;
		}
		if ($availableCount > 0)
		{
			$check = Domain::getList([
				'select' => [
					'CNT' => new Entity\ExpressionField('CNT', 'COUNT(*)')
				],
				'filter' => [
					'!PROVIDER' => null
				],
				'group' => []
			])->fetch();
			if ($check && $check['CNT'] >= $availableCount)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * System method for deactivate all free domains.
	 * @param bool $setActive Set domains and sites active / not active.
	 * @param int $executeAfterSeconds Delayed execution (in seconds).
	 * @return void
	 */
	public static function manageFreeDomains(bool $setActive, int $executeAfterSeconds = 0): void
	{
		$methodName = __CLASS__ . '::' . __FUNCTION__ . '(' . ($setActive ? 'true' : 'false') . ');';

		if ($executeAfterSeconds > 0)
		{
			$dateTime = new DateTime();
			\CAgent::addAgent(
				$methodName,
				'landing', 'N', 0, '', 'Y',
				$dateTime->add('+' . $executeAfterSeconds . ' seconds')
			);
			return;
		}
		if ($setActive)
		{
			\CAgent::removeAgent($methodName, 'landing');
		}

		Rights::setGlobalOff();
		$res = SiteCore::getList([
			'select' => [
				'ID',
				'ACTIVE',
				'DOMAIN_ID'
			],
			'filter' => [
				'=DOMAIN.ACTIVE' => $setActive ? 'N' : 'Y',
				'!DOMAIN.PROVIDER' => null
			]
		]);
		while ($site = $res->fetch())
		{
			if ($site['ACTIVE'] === ($setActive ? 'N' : 'Y'))
			{
				SiteCore::update($site['ID'], [
					'ACTIVE' => $setActive ? 'Y' : 'N'
				])->isSuccess();
			}
			Domain::update($site['DOMAIN_ID'], [
				'ACTIVE' => $setActive ? 'Y' : 'N'
			])->isSuccess();
		}
		Rights::setGlobalOn();
	}

	/**
	 * Returns suspended time of free domain.
	 * @return int
	 */
	public static function getFreeDomainSuspendedTime(): int
	{
		$tariffTtl = 0;
		$resetFreeTime = Manager::getOption('reset_to_free_time');
		if ($resetFreeTime)
		{
			$tariffTtl = $resetFreeTime + self::FREE_DOMAIN_GRACE_DAYS * 86400;
		}

		return $tariffTtl;
	}

	/**
	 * Checks restriction for site export.
	 * @return bool
	 */
	public static function isExportAllowed(): bool
	{
		if (Loader::includeModule('bitrix24'))
		{
			return Feature::isFeatureEnabled('landing_allow_export');
		}

		return true;
	}
}