<?php

namespace Bitrix\Calendar\Core\Handlers;


use Bitrix\Calendar\Core\Base\Collection;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Internals\EventConnectionTable;

class IdentifierEventHandler extends HandlerBase
{
	private Collection $collection;

	public function __construct(SyncEventMap $collection)
	{
		$this->collection = $collection;
	}

	/**
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function __invoke(): array
	{
		return EventConnectionTable::query()
			->whereIn('VENDOR_EVENT_ID', array_keys($this->collection->getCollection()))
			->setSelect(['VENDOR_EVENT_ID', 'ENTITY_TAG'])
			->exec()
			->fetchAll()
		;
	}
}
