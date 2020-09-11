<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Bitrix24\Feature;
use \Bitrix\Landing\Site;
use \Bitrix\Landing\Site\Type;

class Knowledge
{
	/**
	 * Checks restriction for accessing to view knowledge base.
	 * @param string $code Restriction code (not used here).
	 * @param array $params Additional params.
	 * @return bool
	 */
	public static function isViewAllowed(string $code, array $params): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return true;
		}

		if (Site\Type::getCurrentScopeId() != Type::SCOPE_CODE_KNOWLEDGE)
		{
			return true;
		}

		$availableCount = Feature::getVariable(
			'landing_site_knowledge'
		);
		if ($availableCount)
		{
			if (!isset($params['ID']) || $params['ID'] <= 0)
			{
				return false;
			}
			$allowedSiteIds = [];
			$res = Site::getList([
				'select' => [
					'ID'
				],
				'order' => [
					'ID' => 'asc'
				],
				'limit' => $availableCount
			]);
			while ($row = $res->fetch())
			{
				$allowedSiteIds[] = $row['ID'];
			}
			return in_array((int)$params['ID'], $allowedSiteIds);
		}

		return true;
	}
}