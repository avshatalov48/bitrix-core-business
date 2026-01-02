<?php

namespace Bitrix\Bizproc\Internal\Service\LatestRobots;

use Bitrix\Main\Data\Cache;

/**
 * Service for preparing automation robot data for components.
 * Encapsulates the logic of fetching, caching, and calculating robot counters.
 */
class LatestRobotCounterService
{
	private const CACHE_TTL = 86400; // 24 hours
	private const CACHE_ID_PREFIX = 'bizproc_automation_available_robots_';
	private const CACHE_PATH = '/bizproc/automation_component/';

	private LatestRobotService $latestRobotService;

	public function __construct(LatestRobotService $latestRobotService = null)
	{
		$this->latestRobotService = $latestRobotService ?? new LatestRobotService();
	}

	/**
	 * Gathers all necessary data for the automation robot button.
	 *
	 * @param array|null $documentType
	 * @return RobotButtonData
	 */
	public function getButtonData(?array $documentType): RobotButtonData
	{
		$robotButtonData = new RobotButtonData();
		if (!$documentType)
		{
			return $robotButtonData;
		}

		$availableRobotsData = $this->getAvailableRobotsData($documentType);
		$availableRobotIds = $this->getAvailableRobotIds($availableRobotsData);

		$newRobotBaseIds = $this->latestRobotService->getNewRobots();
		$viewedRobotIds = $this->latestRobotService->getViewedNewRobotIds();

		$availableNewRobotIds = $this->filterAvailableNewRobots($availableRobotIds, $newRobotBaseIds);
		$unviewedCounter = $this->calculateUnviewedCounter($availableNewRobotIds, $viewedRobotIds);

		$robotButtonData
			->setCounter($unviewedCounter)
			->setAvailableNewRobotIds(array_values($availableNewRobotIds))
		;

		return $robotButtonData;
	}

	/**
	 * Gets available robots data, using cache.
	 *
	 * @param array $documentType
	 * @return array
	 */
	private function getAvailableRobotsData(array $documentType): array
	{
		$cache = Cache::createInstance();
		$cacheId = self::CACHE_ID_PREFIX . md5(serialize($documentType));

		$result = [];

		if ($cache->initCache(self::CACHE_TTL, $cacheId, self::CACHE_PATH))
		{
			$result = $cache->getVars();
		} elseif ($cache->startDataCache())
		{
			$result = \Bitrix\Bizproc\Automation\Engine\Template::getAvailableRobots($documentType);
			$cache->endDataCache($result);
		}

		return $result;
	}

	/**
	 * Collects a full list of available robot IDs, including simple and composite ones (ClassName@groupId).
	 *
	 * @param array $availableRobots An array of robot data from Template::getAvailableRobots.
	 * @return string[] An array of robot string IDs.
	 */
	private function getAvailableRobotIds(array $availableRobots): array
	{
		$robotIds = [];

		foreach ($availableRobots as $activity)
		{
			if (($activity['EXCLUDED'] ?? false) === true)
			{
				continue;
			}

			$class = $activity['CLASS'] ?? basename($activity['PATH_TO_ACTIVITY']);
			if (!$class)
			{
				continue;
			}

			$settings = $activity['ROBOT_SETTINGS'] ?? [];
			$hasTitleGroup = isset($settings['TITLE_GROUP']) && is_array($settings['TITLE_GROUP']);
			$hasTitleCategory = isset($settings['TITLE_CATEGORY']) && is_array($settings['TITLE_CATEGORY']);

			if ($hasTitleGroup || $hasTitleCategory)
			{
				$titleMap = $hasTitleGroup ? $settings['TITLE_GROUP'] : $settings['TITLE_CATEGORY'];
				$groupsByTitle = [];
				foreach ($titleMap as $groupId => $title)
				{
					$groupsByTitle[$title][] = $groupId;
				}

				foreach ($groupsByTitle as $groups)
				{
					if (!empty($groups))
					{
						$firstGroupId = $groups[0];
						$robotIds[] = $class . '@' . $firstGroupId;
					}
				}
			} else
			{
				$robotIds[] = $class;
			}
		}

		return $robotIds;
	}

	/**
	 * Filters the list of all available robots, leaving only those that are "new".
	 *
	 * @param string[] $availableRobotIds The full list of available robot IDs.
	 * @param string[] $newRobotBaseIds The list of base IDs for "new" robots.
	 * @return string[] The full IDs of available "new" robots.
	 */
	private function filterAvailableNewRobots(array $availableRobotIds, array $newRobotBaseIds): array
	{
		$availableNewRobots = [];
		$newRobotLookup = array_flip(array_map('strtolower', $newRobotBaseIds));

		foreach ($availableRobotIds as $availableId)
		{
			$availableIdLower = strtolower($availableId);
			$baseId = strstr($availableIdLower, '@', true) ? : $availableIdLower;

			if (isset($newRobotLookup[$baseId]))
			{
				$availableNewRobots[] = $availableId;
			}
		}

		return $availableNewRobots;
	}

	/**
	 * Calculates the number of unviewed robots.
	 *
	 * @param string[] $availableNewRobotIds The full IDs of available "new" robots.
	 * @param string[] $viewedRobotIds The full IDs of already viewed robots.
	 * @return int The number of unviewed robots.
	 */
	private function calculateUnviewedCounter(array $availableNewRobotIds, array $viewedRobotIds): int
	{
		if (empty($availableNewRobotIds))
		{
			return 0;
		}

		$unviewedNewRobots = array_diff(
			array_map('strtolower', $availableNewRobotIds),
			array_map('strtolower', $viewedRobotIds)
		);

		return count($unviewedNewRobots);
	}
}