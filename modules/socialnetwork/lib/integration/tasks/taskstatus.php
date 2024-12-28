<?php

namespace Bitrix\Socialnetwork\Integration\Tasks;

use Bitrix\Main\Loader;
use Bitrix\Tasks\Internals\Task\Status;

class TaskStatus
{
	public static function getCompletedStatus(): int
	{
		if (!Loader::includeModule('tasks'))
		{
			return 0;
		}

		return Status::COMPLETED;
	}
}
