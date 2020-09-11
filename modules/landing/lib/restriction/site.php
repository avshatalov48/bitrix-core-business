<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;
use \Bitrix\Main\Entity;
use \Bitrix\Landing\Domain;

class Site
{
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
			//@fixme: tmp
			if ($params['action_type'] == 'publication')
			{
				$filter['!XML_ID'] = '%|store-chats-%';
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