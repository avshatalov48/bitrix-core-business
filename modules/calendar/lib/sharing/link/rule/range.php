<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

class Range
{
	/** @var ?int $from */
	protected ?int $from = null;

	/** @var ?int $to */
	protected ?int $to = null;

	/** @var ?array<int> $weekdays */
	protected ?array $weekdays = null;

	public function getFrom(): ?int
	{
		return $this->from;
	}

	public function setFrom(int $from): self
	{
		$this->from = $from;

		return $this;
	}

	public function getTo(): ?int
	{
		return $this->to;
	}

	public function setTo(int $to): self
	{
		$this->to = $to;

		return $this;
	}

	public function getWeekdays(): ?array
	{
		return $this->weekdays;
	}

	public function setWeekdays(array $weekdays): self
	{
		$this->weekdays = $weekdays;

		return $this;
	}
}