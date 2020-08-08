<?php

namespace Bitrix\Sender\Posting\ThreadStrategy;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Sender\PostingRecipientTable;

class TenThreadsStrategy extends AbstractThreadStrategy
{
	public const THREADS_COUNT = 10;

	protected function setFilter(): void
	{
		$this->filter = [
			'=POSTING_ID' => $this->postingId,
			'=STATUS'     => PostingRecipientTable::SEND_RESULT_NONE,
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
				'LAST_DIGIT', 'RIGHT(`sender_posting_recipient`.`ID`,1)'
			)
		];
	}
}