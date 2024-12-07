<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\Flow\Path;

use Bitrix\Main\Loader;
use Bitrix\Tasks\Flow\FlowFeature;
use Bitrix\Tasks\Flow\Path\FlowPathMaker;

class FlowPath
{
	public static function get(int $userId, int $groupId): string
	{
		if (!Loader::includeModule('tasks'))
		{
			return '';
		}

		$demoSuffix = FlowFeature::isFeatureEnabledByTrial() ? 'Y' : 'N';

		return (new FlowPathMaker(ownerId: $userId))
			->addQueryParam('GROUP_ID', $groupId)
			->addQueryParam('apply_filter', 'Y')
			->addQueryParam('ta_cat', 'flows')
			->addQueryParam('ta_sec', 'tasks')
			->addQueryParam('ta_sub', \Bitrix\Tasks\Helper\Analytics::SUB_SECTION['group_card'])
			->addQueryParam('ta_el', \Bitrix\Tasks\Helper\Analytics::ELEMENT['section_button'])
			->addQueryParam('p1', 'isDemo_' . $demoSuffix)
			->makeEntitiesListPath();
	}
}