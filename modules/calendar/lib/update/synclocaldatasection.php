<?php

namespace Bitrix\Calendar\Update;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Entities\SyncEventMap;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Google\ImportManager;
use Bitrix\Calendar\Sync\GoogleApiBatch;
use Bitrix\Calendar\Sync\GoogleApiPush;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Update\Stepper;
use Bitrix\Main\Type;
use Bitrix\Calendar\Util;


class SyncLocalDataSection
{
	/**
	 * @throws \Bitrix\Main\ObjectException
	 */
	public function export(SyncEventMap $syncEventMap, SyncSectionMap $syncSectionMap)
	{
		// $syncEventMap = new SyncEventMap();
		/** @var SyncSection $syncSection */
		foreach ($syncSectionMap as $syncSection)
		{
			if ($syncSection->getSectionConnection()->getVendorSectionId() !== null)
			{
				GoogleApiPush::setBlockPush(GoogleApiPush::TYPE_SECTION, $syncSection->getSection()->getId());
				if (
					$events = \CCalendarEvent::getLocalBatchEvent(
						$syncSection->getSection()->getOwner()->getId(),
						$syncSection->getSection()->getId(),
						$this->getSyncTimestamp())
				)
				{
					$syncedEvents = (new GoogleApiBatch())
						->syncLocalEvents(
							$events,
							$syncSection->getSection()->getOwner()->getId(),
							$syncSection->getSectionConnection()->getVendorSectionId()
						);

					$this->updateEventsBatch($syncedEvents);

					continue;
				}

				if (
					$recurrentEvents = \CCalendarEvent::getLocalBatchRecurrentEvent(
						$syncSection->getSection()->getOwner()->getId(),
						$syncSection->getSection()->getId(),
						$this->getSyncTimestamp())
				)
				{

					$syncedEvents = (new GoogleApiBatch())->syncLocalEvents(
						$recurrentEvents,
						$syncSection->getSection()->getOwner()->getId(),
						$syncSection->getSectionConnection()->getVendorSectionId()
					);
					$this->updateEventsBatch($syncedEvents);

					continue;
				}

				if (
					$instances = \CCalendarEvent::getLocalBatchInstances(
						$syncSection->getSection()->getOwner()->getId(),
						$syncSection->getSection()->getId(),
						$this->getSyncTimestamp())
				)
				{
					$syncedInstances = (new GoogleApiBatch())->syncLocalInstances(
						$instances,
						$syncSection->getSection()->getOwner()->getId(),
						$syncSection->getSectionConnection()->getVendorSectionId()
					);

					$this->updateEventsBatch($syncedInstances);

					continue;
				}

				GoogleApiPush::setUnblockPush(GoogleApiPush::TYPE_SECTION, $syncSection->getSection()->getId());

				// $pushOptionEnabled = \COption::GetOptionString('calendar', 'sync_by_push', false);
				// if ($pushOptionEnabled || \CCalendar::IsBitrix24())
				// {
				// 	GoogleApiPush::deletePushChannel(['ENTITY_TYPE' => 'SECTION', 'ENTITY_ID' => $syncSection->getSection()->getId()]);
				// 	GoogleApiPush::checkSectionsPush(
				// 		[$syncSection->getSection()],
				// 		$syncSection->getSection()->getOwner()->getId(),
				// 		$syncSection->getSectionConnection()->getConnection()->getId()
				// 	);
				// }
			}
		}

		// todo move to DataExchangeManager::exchange
		// Util::addPullEvent(
		// 	'process_sync_connection',
		// 	(int)$section['OWNER_ID'],
		// 	[
		// 		'vendorName' => 'google',
		// 		'stage' => 'events_created',
		// 	]
		// );
	}

	/**
	 * @param array $eventsBatch
	 */
	private function updateEventsBatch(array $eventsBatch): void
	{
		\CCalendarEvent::updateBatchEventFields($eventsBatch, ['DAV_XML_ID', 'G_EVENT_ID', 'CAL_DAV_LABEL', 'ORIGINAL_DATE_FROM', 'SYNC_STATUS']);
	}

	/**
	 * @return int
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function getSyncTimestamp(): int
	{
		return (new Date())->sub(ImportManager::SYNC_EVENTS_DATE_INTERVAL)->getTimestamp();
	}
}
