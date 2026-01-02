<?php

namespace Bitrix\Bizproc\Internal\Service\LatestRobots;

use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

use Bitrix\Bizproc\Internal\Repository\RobotIndexRepository;

/**
 * Class RobotVersionIndexer
 *
 * Schedules and performs full reindexing of robot versions based on a time interval.
 */
final class RobotVersionIndexer
{
	private const MODULE_ID = 'bizproc';
	private const OPTION_LAST_RUN_TIMESTAMP = 'last_robot_index_timestamp';
	private const REINDEX_INTERVAL_SECONDS = 86400; // 24 hours
	private RobotIndexRepository $robotIndexRepository;

	public function __construct(RobotIndexRepository $robotIndexRepository = new RobotIndexRepository())
	{
		$this->robotIndexRepository = $robotIndexRepository;
	}

	/**
	 * Checks if reindexing interval has elapsed and triggers a full reindex.
	 *
	 * @return void
	 */
	public function ensureFreshIndex(): void
	{
		$lastRunTimestamp = (int)Option::get(self::MODULE_ID, self::OPTION_LAST_RUN_TIMESTAMP, 0);
		$todayTimestamp = (new \Bitrix\Main\Type\DateTime())->getTimestamp();

		if ($todayTimestamp - $lastRunTimestamp < self::REINDEX_INTERVAL_SECONDS)
		{
			return;
		}

		$this->performFullReindex();
	}

	/**
	 * Performs full reindex of all robot activities.
	 *
	 * @return void
	 */
	private function performFullReindex(): void
	{
		if (!Loader::includeModule(self::MODULE_ID))
		{
			return;
		}

		$runtime = \CBPRuntime::getRuntime();
		$robotActivities = $runtime->searchActivitiesByType('robot_activity');

		$robots = [];
		foreach ($robotActivities as $activity)
		{
			$robots[] = [
				'ROBOT_CODE' => $activity['CLASS'],
				'VERSION' => (int)($activity['VERSION'] ?? 0),
			];
		}

		$this->robotIndexRepository->upsertRobotVersions($robots);

		$todayTimestamp = (new \Bitrix\Main\Type\DateTime())->getTimestamp();

		Option::set(self::MODULE_ID, self::OPTION_LAST_RUN_TIMESTAMP, (string)$todayTimestamp);
	}
}
