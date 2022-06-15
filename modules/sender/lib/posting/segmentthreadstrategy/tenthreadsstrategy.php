<?php

namespace Bitrix\Sender\Posting\SegmentThreadStrategy;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Internals\Model\GroupThreadTable;
use Bitrix\Sender\PostingRecipientTable;

class TenThreadsStrategy extends AbstractThreadStrategy
{
	public const THREADS_COUNT = 10;

	/**
	 * Returns false if sending not available
	 * @return bool
	 */
	public function isProcessLimited(): bool
	{
		$maxParallelExecutions = \COption::GetOptionInt(
			"sender",
			"max_parallel_threads",
			10
		);

		$count = GroupThreadTable::getCount(
			[
				'=STATUS' => GroupThreadTable::STATUS_IN_PROGRESS,
			]
		);

		return $count > $maxParallelExecutions;
	}
}
