<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Domain;

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
		'store_v3' => 1,
		'store-chats' => 1,
		'%' => 1 // any template
	];

	/**
	 * Checks limits by template's limits.
	 * @param array $filter filter array.
	 * @param int $limit Current limit.
	 * @return bool
	 */
	private static function checkLimitByTemplates(array $filter, int $limit): bool
	{
		$templates = [];
		$sites = [];
		$currentSiteId = null;
		$templatesLimits = self::LIMIT_BY_TEMPLATES_MINIMAL;
		$templatesLimits['%'] = max($limit, 1);

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
		foreach ($sites as $row)
		{
			if (!$row['TPL_CODE'])
			{
				if (mb_strpos($row['XML_ID'], '|') !== false)
				{
					[, $row['TPL_CODE']] = explode('|', $row['XML_ID']);
				}
			}

			$exactMatch = false;
			foreach ($templatesLimits as $code => $cnt)
			{
				if (strpos($row['TPL_CODE'], $code) === 0)
				{
					$exactMatch = true;
					$row['TPL_CODE'] = $code;
					break;
				}
			}

			if (!$exactMatch)
			{
				$row['TPL_CODE'] = '%';
			}

			if (!($templates[$row['TPL_CODE']] ?? null))
			{
				$templates[$row['TPL_CODE']] = 0;
			}
			$templates[$row['TPL_CODE']]++;
		}

		// calc limits
		if ($templates)
		{
			foreach ($templates as $code => $cnt)
			{
				if (isset($templatesLimits[$code]))
				{
					if ($templatesLimits[$code] < $cnt)
					{
						return false;
					}
				}

				if ($templatesLimits['%'] < $cnt)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Checks restriction for creating and publication site.
	 * @param string $code Restriction code (not used here).
	 * @param array $params Additional params.
	 * @return bool
	 */
	public static function isCreatingAllowed(string $code, array $params): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return true;
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
			$check = \Bitrix\Landing\Site::getList([
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
	 * @return bool
	 */
	public static function isFreeDomainAllowed(): bool
	{
		// free domain is available in cloud version only
		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
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
	 * Checks restriction for site export.
	 * @return bool
	 */
	public static function isExportAllowed(): bool
	{
		if (\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return Feature::isFeatureEnabled('landing_allow_export');
		}

		return true;
	}
}