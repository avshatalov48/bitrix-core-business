<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync;

use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\IterationResult;
use Bitrix\Im\V2\Integration\HumanResources\Sync\Result\QueueItemResult;
use Bitrix\Im\V2\Result;

interface SyncProcessor
{
	public function dequeue(): ?Item\SyncInfo;
	public function getOrCreateWithLock(Item\SyncInfo $syncInfo): QueueItemResult;
	public function tryGetWithLock(Item\SyncInfo $syncInfo): ?Item\QueueItem;
	public function makeIteration(Item\QueueItem $item): IterationResult;
	public function finalizeSync(Item\QueueItem $item): Result;
	public function hasItemsInQueue(): bool;
}