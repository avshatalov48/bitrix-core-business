<?php

namespace Bitrix\Calendar\Core\Queue\Agent;

class AgentEntity
{
	private string $name;
	private string $module;
	private int $delay;
	private int $interval;
	private int $escalatedInterval;

	public function __construct(
		string $name,
		string $module = 'calendar',
		int $delay = 0,
		int $interval = 3600,
		int $escalatedInterval = 60
	)
	{
		$this->name = $name;
		$this->module = $module;
		$this->delay = $delay;
		$this->interval = $interval;
		$this->escalatedInterval = $escalatedInterval;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getModule(): string
	{
		return $this->module;
	}

	/**
	 * @return int
	 */
	public function getDelay(): int
	{
		return $this->delay;
	}

	/**
	 * @return int
	 */
	public function getInterval(): int
	{
		return $this->interval;
	}

	/**
	 * @return int
	 */
	public function getEscalatedInterval(): int
	{
		return $this->escalatedInterval;
	}
}