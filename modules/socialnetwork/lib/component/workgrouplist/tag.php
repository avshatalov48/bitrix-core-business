<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\Entity\Query;
use Bitrix\Socialnetwork\WorkgroupTagTable;

class Tag
{
	public static function fillTags(array $params = []): array
	{
		$result = [];

		if (
			!isset($params['groupIdList'])
			|| !is_array($params['groupIdList'])
		)
		{
			return $result;
		}

		$groupIdList = Util::filterNumericIdList($params['groupIdList']);
		if (empty($groupIdList))
		{
			return $result;
		}

		$query = new Query(WorkgroupTagTable::getEntity());
		$records = $query
			->setSelect([
				'GROUP_ID',
				'NAME',
			])
			->whereIn('GROUP_ID', $groupIdList)
			->exec()->fetchCollection();

		foreach ($records as $record)
		{
			$tag = (string)$record->get('NAME');
			$groupId = (int)$record->get('GROUP_ID');

			if (!isset($result[$groupId]))
			{
				$result[$groupId] = [];
			}

			$result[$groupId][] = $tag;
		}

		return $result;
	}
}
