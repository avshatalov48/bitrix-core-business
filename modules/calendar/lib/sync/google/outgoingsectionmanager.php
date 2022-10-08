<?php

namespace Bitrix\Calendar\Sync\Google;

use Bitrix\Calendar\Sync\Managers\OutgoingSectionManagerInterface;
use Bitrix\Calendar\Sync\Util\Result;

class OutgoingSectionManager extends Manager implements OutgoingSectionManagerInterface
{

	public function export(): Result
	{
		return new Result();
	}
}
