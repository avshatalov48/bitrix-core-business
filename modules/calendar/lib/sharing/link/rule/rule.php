<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

class Rule
{
	/** @var ?int $slotSize */
	protected ?int $slotSize = null;

	/** @var ?array<Range> $ranges */
	protected ?array $ranges = null;

	public function getSlotSize(): ?int
	{
		return $this->slotSize;
	}

	public function setSlotSize(int $slotSize): self
	{
		$this->slotSize = $slotSize;

		return $this;
	}

	public function getRanges(): ?array
	{
		return $this->ranges;
	}

	public function setRanges(array $ranges): self
	{
		$this->ranges = $ranges;

		return $this;
	}
}