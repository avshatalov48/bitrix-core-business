<?php

namespace Bitrix\Im\V2\Recent\Initializer;

use Bitrix\Im\V2\Recent\Initializer\Queue\QueueItem;
use Bitrix\Im\V2\Result;

class InitialiazerResult extends Result
{
	protected array $items = [];
	protected bool $hasNextStep = false;
	protected string $nextPointer = '';
	protected ?QueueItem $queueItem = null;
	protected int $selectedItemsCount = 0;

	public function getQueueItem(): ?QueueItem
	{
		return $this->queueItem;
	}

	public function setQueueItem(?QueueItem $queueItem): self
	{
		$this->queueItem = $queueItem;

		return $this;
	}

	public function getItems(): array
	{
		return $this->items;
	}

	public function setItems(array $items): self
	{
		$this->items = $items;

		return $this;
	}

	public function hasNextStep(): bool
	{
		return $this->hasNextStep;
	}

	public function setHasNextStep(bool $flag): self
	{
		$this->hasNextStep = $flag;

		return $this;
	}

	public function getNextPointer(): string
	{
		return $this->nextPointer;
	}

	public function setNextPointer(string $lastId): self
	{
		$this->nextPointer = $lastId;

		return $this;
	}

	public function setSelectedItemsCount(int $count): self
	{
		$this->selectedItemsCount = $count;

		return $this;
	}

	public function getSelectedItemsCount(): int
	{
		return $this->selectedItemsCount;
	}
}
