<?php

namespace Bitrix\Sender\Posting\SegmentThreadStrategy;

use Bitrix\Sender\Posting\SegmentThreadStrategy\ThreadStrategy;

class ThreadStrategyContext
{
	public static function buildStrategy($type):?ThreadStrategy
	{
		switch($type)
		{
			case ThreadStrategy::TEN:
				return new TenThreadsStrategy();
			break;
			case ThreadStrategy::SINGLE:
			default:
				return new SingleThreadStrategy();
			break;
		}
	}
}
