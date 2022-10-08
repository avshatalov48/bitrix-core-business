<?php

namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Sync\Entities;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;

class OutgoingEventManager implements OutgoingEventManagerInterface
{
	/**
	 * @param Entities\SyncEventMap $syncEventMap
	 * @param Entities\SyncSectionMap $syncSectionMap
	 * @return Result
	 */
	public function export(
		Entities\SyncEventMap $syncEventMap,
		Entities\SyncSectionMap $syncSectionMap
	): Result
	{
		return new Result();
	}
}