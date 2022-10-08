<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Connection\Connection;

interface StartSynchronization
{
	public function start(): ?Connection;
}
