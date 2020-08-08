<?php

namespace Bitrix\Sender\Posting\ThreadStrategy;

class ThreadStrategyContext
{
	public static function buildStrategy($type):?IThreadStrategy
	{
		switch($type)
		{
			case IThreadStrategy::TEN:
				return new TenThreadsStrategy();
			break;
			case IThreadStrategy::SINGLE:
			default:
				return new SingleThreadStrategy();
			break;
		}
	}
}