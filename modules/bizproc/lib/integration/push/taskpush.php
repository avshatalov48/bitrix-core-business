<?php

namespace Bitrix\Bizproc\Integration\Push;

final class TaskPush extends BasePush
{
	protected static function getCommand(): string
	{
		return 'task';
	}
}
