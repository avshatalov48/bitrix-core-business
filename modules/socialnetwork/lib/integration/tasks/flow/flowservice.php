<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Integration\Tasks\Flow;

use Bitrix\Main\Loader;
use Bitrix\Tasks\Flow\Provider\ProjectProvider;

class FlowService
{
	public function doesGroupHaveFlows(int $groupId): bool
	{
		if (!Loader::includeModule('tasks'))
		{
			return false;
		}

		return (new ProjectProvider())->hasFlows($groupId);
	}
}