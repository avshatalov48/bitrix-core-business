<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Util\Result;

interface SyncManagerInterface
{
	public function getServiceName(): string;
}
