<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync\Managers\OutgoingSectionManagerInterface;
use Bitrix\Calendar\Sync\Util\Result;

class OutgoingSectionManager implements OutgoingSectionManagerInterface
{
	/**
	 * @return Result
	 */
	public function export(): Result
	{
		return new Result();
	}
}