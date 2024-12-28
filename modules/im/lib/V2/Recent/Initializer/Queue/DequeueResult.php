<?php

namespace Bitrix\Im\V2\Recent\Initializer\Queue;

use Bitrix\Im\V2\Result;

class DequeueResult extends Result
{
	protected bool $hasMore = true;
	protected ?QueueItem $queueItem = null;

	public function hasMore(): bool
	{
		return $this->hasMore;
	}

	public function setHasMore(bool $hasMore): self
	{
		$this->hasMore = $hasMore;

		return $this;
	}

	public function getQueueItem(): ?QueueItem
	{
		return $this->queueItem;
	}

	public function setQueueItem(?QueueItem $queueItem): self
	{
		$this->queueItem = $queueItem;

		return $this;
	}
}
