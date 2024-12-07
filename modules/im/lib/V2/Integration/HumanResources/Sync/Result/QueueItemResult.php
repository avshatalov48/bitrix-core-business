<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\Result;

use Bitrix\Im\V2\Integration\HumanResources\Sync\Item\QueueItem;
use Bitrix\Im\V2\Result;

/**
 * @extends Result<QueueItem>
 */
class QueueItemResult extends Result
{
	protected bool $skip = false;

	public function setSkip(bool $skip): self
	{
		$this->skip = $skip;

		return $this;
	}

	public function skip(): bool
	{
		return $this->skip;
	}
}