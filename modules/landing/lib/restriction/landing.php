<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;
use \Bitrix\Main\Entity;

class Landing
{
	/**
	 * Checks restriction for creating and publication page.
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

		$optPrefix = 'landing_page_';
		$optSuffix = ($params['action_type'] == 'publication') ? '_publication' : '';
		$variableCode = $optPrefix . strtolower($params['type']) . $optSuffix;
		$limit = (int) Feature::getVariable($variableCode);

		if ($limit)
		{
			$filter = [
				'CHECK_PERMISSIONS' => 'N',
				'=SITE.TYPE' => $params['type'],
				'!=SITE.SPECIAL' => 'Y'
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
			$check = \Bitrix\Landing\Landing::getList([
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
}
