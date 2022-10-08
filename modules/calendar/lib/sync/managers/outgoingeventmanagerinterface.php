<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync;

interface OutgoingEventManagerInterface
{
	public function export(
		Sync\Entities\SyncEventMap $syncEventMap,
		Sync\Entities\SyncSectionMap $syncSectionMap
	): Sync\Util\Result;
}
