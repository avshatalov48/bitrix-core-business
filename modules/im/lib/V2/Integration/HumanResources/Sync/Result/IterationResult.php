<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\Result;

use Bitrix\Im\V2\Result;

class IterationResult extends Result
{
	protected bool $hasMore = true;

	public function setHasMore(bool $hasMore): self
	{
		$this->hasMore = $hasMore;

		return $this;
	}

	public function hasMore(): bool
	{
		return $this->hasMore;
	}
}