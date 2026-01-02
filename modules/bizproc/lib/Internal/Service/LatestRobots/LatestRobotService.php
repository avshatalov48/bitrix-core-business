<?php

namespace Bitrix\Bizproc\Internal\Service\LatestRobots;

use Bitrix\Main\Type\Date;

use Bitrix\Bizproc\Internal\Config\Storage;
use Bitrix\Bizproc\Internal\Repository\RobotIndexRepository;

/**
 * Class LatestRobotService
 *
 * Provides retrieval of robots added or updated within a specified lookback period.
 */
final class LatestRobotService
{
	private const DEFAULT_LOOKBACK_DAYS = 14;
	private RobotIndexRepository $robotIndexRepository;
	private Storage $configStorage;

	public function __construct(
		RobotIndexRepository $robotIndexRepository = new RobotIndexRepository(),
		Storage $configStorage = new Storage()
	)
	{
		$this->robotIndexRepository = $robotIndexRepository;
		$this->configStorage = $configStorage;
	}

	/**
	 * Retrieves all robots changed in the last $lookbackDays days.
	 *
	 * @param int|null $lookbackDays Number of days to look back; defaults to "DEFAULT_LOOKBACK_DAYS".
	 * @return array<int, string> List of robot index records.
	 */
	public function getNewRobots(int $lookbackDays = null): array
	{
		$days = $lookbackDays ?? self::DEFAULT_LOOKBACK_DAYS;
		$threshold = self::calculateThresholdDateTime($days);

		$rawRobotList = $this->robotIndexRepository->fetchRobotsChangedSinceThreshold($threshold);

		return array_values($this->prepareRawRobots($rawRobotList));
	}

	public function getViewedNewRobotIds(): array
	{
		return $this->configStorage->getViewedNewRobotIds();
	}

	public function upsertViewedNewRobotIds(array $viewedNewRobotIds): void
	{
		$existingIds = $this->getViewedNewRobotIds();
		$viewedNewRobotIds = array_map('strtolower', $viewedNewRobotIds);
		$mergedIds = array_values(array_unique(array_merge($existingIds, $viewedNewRobotIds)));

		$this->configStorage->setViewedNewRobotIds($mergedIds);
	}

	private function prepareRawRobots(array $rawRobotList): array
	{
		return array_column($rawRobotList, 'ROBOT_CODE');
	}

	/**
	 * Builds a DateTime instance representing the cutoff timestamp.
	 *
	 * @param int $days
	 * @return Date
	 */
	private static function calculateThresholdDateTime(int $days): Date
	{
		return (new Date())->add("-$days days");
	}
}