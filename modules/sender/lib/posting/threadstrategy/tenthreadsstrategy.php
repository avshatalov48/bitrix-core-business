<?php

namespace Bitrix\Sender\Posting\ThreadStrategy;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Sender\Internals\Model\PostingThreadTable;
use Bitrix\Sender\PostingRecipientTable;

class TenThreadsStrategy extends AbstractThreadStrategy
{
	public const THREADS_COUNT = 10;

	protected function setFilter(): void
	{
		parent::setFilter();
		$this->filter += [
			'=POSTING_ID' => $this->postingId,
			'@STATUS'     => [PostingRecipientTable::SEND_RESULT_NONE,PostingRecipientTable::SEND_RESULT_WAIT_ACCEPT],
			'=LAST_DIGIT' => $this->threadId,
		];
	}

	protected function setRuntime(): void
	{
		$this->runtime = [
			new ReferenceField(
				'MAILING_SUB', 'Bitrix\\Sender\\MailingSubscriptionTable', [
				'=this.CONTACT_ID'         => 'ref.CONTACT_ID',
				'=this.POSTING.MAILING_ID' => 'ref.MAILING_ID'
			], ['join_type' => 'LEFT']
			),
			new ExpressionField(
				'LAST_DIGIT', 'RIGHT(sender_posting_recipient.ID,1)'
			)
		];
	}

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

		$count = PostingThreadTable::getCount(
			[
				'=STATUS' => PostingThreadTable::STATUS_IN_PROGRESS,
			]
		);

		return $count > $maxParallelExecutions;
	}
}
