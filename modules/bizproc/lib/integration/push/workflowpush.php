<?php

namespace Bitrix\Bizproc\Integration\Push;

final class WorkflowPush extends BasePush
{
	protected static function getCommand(): string
	{
		return 'workflow';
	}
}
