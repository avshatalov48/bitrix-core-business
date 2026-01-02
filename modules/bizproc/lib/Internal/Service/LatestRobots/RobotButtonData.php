<?php

namespace Bitrix\Bizproc\Internal\Service\LatestRobots;

/**
 * DTO for storing data related to the automation robot button.
 */
class RobotButtonData
{
	/**
	 * The number of unviewed new robots.
	 * @var int
	 */
	private int $counter;

	/**
	 * A list of string IDs of available new robots.
	 * @var string[]
	 */
	private array $availableNewRobotIds;

	/**
	 * @param int $counter The number of unviewed new robots.
	 * @param string[] $availableNewRobotIds A list of IDs of available new robots.
	 */
	public function __construct(int $counter = 0, array $availableNewRobotIds = [])
	{
		$this->counter = $counter;
		$this->availableNewRobotIds = $availableNewRobotIds;
	}

	public function getCounter(): int
	{
		return $this->counter;
	}

	public function setCounter(int $counter): RobotButtonData
	{
		$this->counter = $counter;
		return $this;
	}

	public function getAvailableNewRobotIds(): array
	{
		return $this->availableNewRobotIds;
	}

	public function setAvailableNewRobotIds(array $availableNewRobotIds): static
	{
		$this->availableNewRobotIds = $availableNewRobotIds;
		return $this;
	}
}