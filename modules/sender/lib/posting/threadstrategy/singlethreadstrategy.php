<?php

namespace Bitrix\Sender\Posting\ThreadStrategy;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sender\PostingRecipientTable;

class SingleThreadStrategy extends AbstractThreadStrategy
{
	public const THREADS_COUNT = 1;

	protected function setFilter():void
	{
		$this->filter = [
			'=POSTING_ID' => $this->postingId,
			'=STATUS'     => PostingRecipientTable::SEND_RESULT_NONE
		];
	}

	protected function setRuntime():void
	{
		$this->runtime = [
			new ReferenceField(
				'MAILING_SUB', 'Bitrix\\Sender\\MailingSubscriptionTable', [
				'=this.CONTACT_ID'         => 'ref.CONTACT_ID',
				'=this.POSTING.MAILING_ID' => 'ref.MAILING_ID'
			], ['join_type' => 'LEFT']
			)
		];
	}

	/**
	 * wait while threads are calculating
	 * @return bool
	 */
	protected function checkLock()
	{
		for($i = 0; $i <= static::THREADS_COUNT; $i++)
		{
			if ($this->lock())
			{
				return true;
			}
			sleep(1);
		}
		return false;
	}
}