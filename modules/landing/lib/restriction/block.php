<?php
namespace Bitrix\Landing\Restriction;

use \Bitrix\Landing\Site;
use \Bitrix\Landing\Internals;
use \Bitrix\Landing\Site\Type;
use \Bitrix\Bitrix24\Feature;
use \Bitrix\Main\Application;

class Block
{
	/**
	 * Checks dynamic block restriction.
	 * @param string $code Restriction code (not used here).
	 * @param array $params Additional params.
	 * @return bool
	 */
	public static function isDynamicEnabled(string $code, array $params): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('bitrix24'))
		{
			return true;
		}

		// @todo: make more useful in future
		$scope = Site\Type::getCurrentScopeId();
		if (
			$scope == Type::SCOPE_CODE_KNOWLEDGE ||
			$scope == Type::SCOPE_CODE_GROUP
		)
		{
			return true;
		}
		$availableCount = Feature::getVariable(
			'landing_dynamic_blocks'
		);
		if ($availableCount <= 0)
		{
			return true;
		}

		static $dynamicBlocks = null;
		$targetBlockId = isset($params['targetBlockId'])
			? intval($params['targetBlockId'])
			: 0;

		// gets actual dynamic blocks
		if ($dynamicBlocks === null)
		{
			$dynamicBlocks = [];
			// plain sql, reason for this described in task 186683
			$sql = '
					SELECT
						B.ID as ID,
						B.PARENT_ID as PARENT_ID,
						B.DATE_MODIFY as DATE_MODIFY,
						S.ID as SID,
						L.DELETED
					FROM
						' . Internals\FilterBlockTable::getTableName() .  ' FB
					LEFT JOIN
						' . Internals\BlockTable::getTableName() .  ' B
					ON 
						FB.BLOCK_ID = B.ID
					LEFT JOIN
						' . Internals\LandingTable::getTableName() .  ' L
					ON
						B.LID = L.ID
					LEFT JOIN
						' . Internals\SiteTable::getTableName() .  ' S
					ON
						L.SITE_ID = S.ID
					WHERE
						B.DELETED = "N" AND 
						L.DELETED = "N" AND
						S.DELETED = "N" AND
						S.TYPE NOT IN ("KNOWLEDGE", "GROUP")
					GROUP BY FB.BLOCK_ID
					ORDER BY B.DATE_MODIFY ASC;';
			$res = Application::getConnection()->query($sql);
			while ($row = $res->fetch())
			{
				$dynamicBlocks[$row['ID']] = $row;
			}
			// remove public blocks
			foreach ($dynamicBlocks as $dynamicBlock)
			{
				if (
					$dynamicBlock['PARENT_ID'] &&
					isset($dynamicBlocks[$dynamicBlock['PARENT_ID']])
				)
				{
					unset($dynamicBlocks[$dynamicBlock['PARENT_ID']]);
				}
			}
		}

		// allow only first $availableCount dynamic blocks
		$dynamicBlocks = array_slice($dynamicBlocks, 0, $availableCount, true);
		foreach ($dynamicBlocks as $dynamicBlock)
		{
			if (
				$dynamicBlock['ID'] == $targetBlockId ||
				$dynamicBlock['PARENT_ID'] == $targetBlockId
			)
			{
				return true;
			}
		}

		return false;
	}
}