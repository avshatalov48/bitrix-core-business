<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Calendar\Sync\Entities\SyncSection;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Calendar\Sync\Managers\OutgoingEventManagerInterface;
use Bitrix\Calendar\Sync\Util\Result;

class OutgoingEventManager extends AbstractManager implements OutgoingEventManagerInterface
{
	use Sync\Internals\HasContextTrait;

	private array $map = [];

    /**
	 * @param Office365Context $context
	 */
    public function __construct(Office365Context $context)
    {
		$this->context = $context;
		$this->eventManager = $this->context->getEventManager();
		parent::__construct($context->getConnection());

	}

	public function export(
		Sync\Entities\SyncEventMap $syncEventMap,
		Sync\Entities\SyncSectionMap $syncSectionMap
	): Sync\Util\Result
	{
		$result = new Sync\Util\Result();
		$result->setData([
			'syncEventMap' => $syncEventMap,
		]);

		/** @var SyncEvent $syncEvent */
		foreach ($syncEventMap as $syncEvent)
		{
			if (
				$syncEvent->getEventConnection()
				&& ($syncEvent->getEvent()->getVersion() === $syncEvent->getEventConnection()->getVersion())
			)
			{
				continue;
			}

			if ($syncEvent->isRecurrence())
			{
				$this->saveRecurrence($syncEvent, $syncSectionMap);
			}
			else
			{
				$this->saveSingle($syncEvent, $syncSectionMap);
			}
		}

		return new Result();
	}

	private function getSyncSection(SyncSectionMap $syncSectionMap, int $id): ?SyncSection
	{
		if (!array_key_exists($id, $this->map))
		{
			$this->map[$id] = null;
			/** @var SyncSection $syncSection */
			foreach ($syncSectionMap as $syncSection)
			{
				if ($syncSection->getSection()->getId() === $id)
				{
					$this->map[$id] = $syncSection;
					break;
				}
			}
		}

		return $this->map[$id];
	}

	private function saveSingle(SyncEvent $syncEvent, SyncSectionMap $syncSectionMap)
	{
		if ($syncEvent->getEventConnection() && $syncEvent->getEventConnection()->getVendorEventId())
		{
			$context = (new Sync\Util\EventContext())
				->setEventConnection($syncEvent->getEventConnection())
			;
			try
			{
				$result = $this->context->getEventManager()->update(
					$syncEvent->getEvent(),
					$context
				);
				if ($result->isSuccess())
				{
					$data = $result->getData();
					$syncEvent
						->setAction(Sync\Dictionary::SYNC_STATUS['success'])
						->getEventConnection()
							->setEntityTag($data['event']['etag'])
							->setVendorVersionId($data['event']['version'])
							->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['success'])
					;
				}
			}
			catch(Sync\Exceptions\NotFoundException $e)
			{
				$syncEvent
					->setAction(Sync\Dictionary::SYNC_STATUS['success'])
					->getEventConnection()
					->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['deleted'])
				;
			}
		}
		else
		{
			$syncSection = $this->getSyncSection(
				$syncSectionMap,
				$syncEvent->getEvent()->getSection()->getId()
			);
			if ($syncSection && $syncSection->getSectionConnection()->isActive())
			{
				$context = (new Sync\Util\EventContext())
					->setSectionConnection($syncSection->getSectionConnection())
				;
				try
				{
					$result = $this->context->getEventManager()->create(
						$syncEvent->getEvent(),
						$context
					);
					if ($result->isSuccess())
					{
						$data = $result->getData();
						$eventConnection = (new Sync\Connection\EventConnection())
							->setEvent($syncEvent->getEvent())
							->setConnection($syncSection->getSectionConnection()->getConnection())
							->setVendorEventId($data['event']['id'])
							->setVendorVersionId($data['event']['version'])
							->setEntityTag($data['event']['etag'])
							->setVersion($syncEvent->getEvent()->getVersion())
							->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['success'])
						;
						$syncEvent
							->setAction(Sync\Dictionary::SYNC_STATUS['success'])
							->setEventConnection($eventConnection);
					}
				}
				catch(Sync\Exceptions\NotFoundException $e)
				{
					$syncSection->getSectionConnection()->setActive(false);
					$syncEvent->setAction(Sync\Dictionary::SYNC_STATUS['success']);
				}
			}
		}
	}

	private function saveRecurrence(SyncEvent $syncEvent, SyncSectionMap $syncSectionMap)
	{
		$syncSection = $this->getSyncSection(
			$syncSectionMap,
			$syncEvent->getEvent()->getSection()->getId()
		);
		$context = (new Sync\Util\EventContext())
			->setSectionConnection($syncSection->getSectionConnection());
		if ($syncEvent->getEventConnection() && $syncEvent->getEventConnection()->getVendorEventId())
		{
			$context->setEventConnection($syncEvent->getEventConnection());
			$result = $this->context->getEventManager()->updateRecurrence(
				$syncEvent,
				$syncSection->getSectionConnection(),
				$context
			);
		}
		else
		{
			$result = $this->context->getEventManager()->createRecurrence(
				$syncEvent,
				$syncSection->getSectionConnection(),
				$context
			);
		}
	}
}