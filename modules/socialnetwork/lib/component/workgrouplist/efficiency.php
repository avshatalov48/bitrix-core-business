<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

use Bitrix\Main\Loader;
use Bitrix\Tasks\Internals\Effective;

class Efficiency
{
	public static function fillEfficiency(array $params = []): array
	{
		$result = [];

		if (
			!isset($params['groupIdList'])
			|| !is_array($params['groupIdList'])
			|| !Loader::includeModule('tasks')
		)
		{
			return $result;
		}

		$result = Effective::getAverageEfficiencyForGroups(
			null,
			null,
			0,
			$params['groupIdList']
		);

		return $result;
	}
}
