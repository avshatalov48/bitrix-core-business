<?php

namespace Bitrix\Calendar\Sync\Entities;

use Bitrix\Calendar\Core\Base\Map;
use Bitrix\Calendar\Sync\Dictionary;

/**
 *
 */
class SyncEventMap extends Map
{
	/**
	 * @return $this
	 */
	public function getNotSuccessSyncEvent(): SyncEventMap
	{
		return new static(array_filter($this->collection, function (SyncEvent $syncEvent) {
			return $syncEvent->getAction() !== Dictionary::SYNC_EVENT_ACTION['success'];
		}));
	}
}
