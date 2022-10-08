<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync\Util\Result;

interface OutgoingSectionManagerInterface
{
	public function export(): Result;
}
