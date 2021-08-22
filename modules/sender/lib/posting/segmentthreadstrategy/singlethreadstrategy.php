<?php

namespace Bitrix\Sender\Posting\SegmentThreadStrategy;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Sender\PostingRecipientTable;
use Bitrix\Sender\Posting\Locker;

class SingleThreadStrategy extends AbstractThreadStrategy
{
	public const THREADS_COUNT = 1;

	/**
	 * wait while threads are calculating
	 * @return bool
	 */
	protected function checkLock()
	{
		for($i = 0; $i <= static::THREADS_COUNT; $i++)
		{
			if (Locker::lock(self::GROUP_THREAD_LOCK_KEY, $this->groupStateId))
			{
				return true;
			}
			sleep(1);
		}
		return false;
	}
}
