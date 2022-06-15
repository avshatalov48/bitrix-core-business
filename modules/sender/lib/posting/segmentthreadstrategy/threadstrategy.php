<?php

namespace Bitrix\Sender\Posting\SegmentThreadStrategy;

use Bitrix\Main\ORM\Query\Result;

interface ThreadStrategy
{
	public const TEN = 'Ten';
	public const SINGLE = 'Single';

	function getOffset(): ?int;

	function fillThreads(): void;

	function lockThread(): ?int;
	function hasUnprocessedThreads(): bool;

	function getThreadId(): ?int;
	function setGroupStateId(int $groupStateId): ThreadStrategy;

	function updateStatus(string $status): bool;
//	function updateOffset(): bool;

	function lastThreadId(): int;
	function isProcessLimited(): bool;
}
