<?php

namespace Bitrix\Calendar\Sync\Entities;

use Bitrix\Calendar\Core\Mappers\Section;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Dictionary;

class SyncSectionMap extends Core\Base\Map
{
	public function getNonLocalSections(): SyncSectionMap
	{
		return new self(array_filter($this->collection, function ($item) {
			/** @var SyncSection $item */
			return $item->getVendorName() !== Section::SECTION_TYPE_LOCAL;
		}));
	}

	public function getLocalSections(): SyncSectionMap
	{
		return new self(array_filter($this->collection, function ($item) {
			/** @var SyncSection $item */
			return $item->getVendorName() === Section::SECTION_TYPE_LOCAL;
		}));
	}

	/**
	 * @return SyncSectionMap
	 */
	public function getSuccessSyncSection(): SyncSectionMap
	{
		return new self(array_filter($this->collection, function ($syncSection) {
			/** @var SyncSection $syncSection */
			return $syncSection->getAction() === Dictionary::SYNC_SECTION_ACTION['success'];
		}));
	}

	/**
	 * @return SyncSectionMap
	 */
	public function getActiveSections()
	{
		return new static(array_filter(
			$this->collection,
			static fn ($item) => ($item->getAction() !== Dictionary::SYNC_EVENT_ACTION['delete']))
		);
	}
}
