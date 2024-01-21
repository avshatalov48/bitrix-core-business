<?php

namespace Bitrix\Socialnetwork\Controller;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\EventService\EventDictionary;
use Bitrix\Socialnetwork\Internals\EventService\Service;
use Bitrix\Socialnetwork\Internals\Space\Counter;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\Space\List\SpaceListMode;

class Space extends Base
{
	/**
	 * Returns total counters for each metric
	 * Ex.
	 * total: 2,
	 * spaces: [{
	 * 		id:1,
	 *		total: 2,
	 * 		metrics: {countersTasksTotal: 2, countersCalendarTotal: 0, countersWorkGroupRequestTotal: 0, countersLiveFeedTotal: 0}
	 * },{....}]
	 * @return array
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function getTotalCountersAction(): array
	{
		return Counter::getInstance($this->userId)
			->getMemberSpaceCounters();
	}

	/**
	 * Marks all counters as read
	 * @param array $params
	 * @return void
	 */
	public function readAllAction(int $space = 0, string $feature = Dictionary::FEATURE_DISCUSSIONS): void
	{
		if (!array_key_exists($feature, Dictionary::AVAILABLE_FEATURES))
		{
			return;
		}

		switch ($feature)
		{
			case Dictionary::FEATURE_GENERAL:
			case Dictionary::FEATURE_DISCUSSIONS:
				Service::addEvent(
					EventDictionary::EVENT_SPACE_LIVEFEED_READ_ALL, [
						'USER_ID' => $this->userId,
						'GROUP_ID' => $space,
						'FEATURE_ID' => $feature,
					]
				);
				break;
		}
	}

	/**
	 * Saves Space List State option value
	 * @param string $spacesListState
	 * @return void
	 */
	public function saveListSateAction(string $spacesListState): void
	{
		SpaceListMode::setOption($this->getCurrentUser()->getId(), $spacesListState);

		if (Loader::includeModule('intranet'))
		{
			\Bitrix\Intranet\Composite\CacheProvider::deleteUserCache();
		}
	}
}