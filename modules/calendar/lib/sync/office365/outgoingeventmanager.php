<?php

namespace Bitrix\Calendar\Sync\Office365;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Main;
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

	private EventManager $eventManager;

	/**
	 * @param Office365Context $context
	 */
    public function __construct(Office365Context $context)
    {
		$this->context = $context;
		$this->eventManager = $this->context->getEventManager();
		parent::__construct($context->getConnection());
	}

	/**
	 * @throws BaseException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws Sync\Exceptions\ApiException
	 * @throws Sync\Exceptions\AuthException
	 * @throws Sync\Exceptions\ConflictException
	 * @throws Sync\Exceptions\NotFoundException
	 * @throws Sync\Exceptions\RemoteAccountException
	 */
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
			if ($syncEvent->getAction() === Dictionary::SYNC_EVENT_ACTION['delete'])
			{
				$this->deleteEvent($syncEvent, $syncSectionMap);
			}

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

	/**
	 * @param SyncSectionMap $syncSectionMap
	 * @param int $id
	 *
	 * @return SyncSection|null
	 */
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

	/**
	 * @throws BaseException
	 * @throws Main\ArgumentException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws Sync\Exceptions\ApiException
	 */
	private function saveSingle(SyncEvent $syncEvent, SyncSectionMap $syncSectionMap)
	{
		if ($syncEvent->getEventConnection() && $syncEvent->getEventConnection()->getVendorEventId())
		{
			$context = (new Sync\Util\EventContext())
				->setEventConnection($syncEvent->getEventConnection())
			;
			try
			{
				$result = $this->eventManager->update(
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
					$result = $this->eventManager->create(
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

	/**
	 * @throws BaseException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws Sync\Exceptions\ApiException
	 * @throws Sync\Exceptions\AuthException
	 * @throws Sync\Exceptions\ConflictException
	 * @throws Sync\Exceptions\NotFoundException
	 * @throws Sync\Exceptions\RemoteAccountException
	 */
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
			$this->eventManager->updateRecurrence(
				$syncEvent,
				$syncSection->getSectionConnection(),
				$context
			);
		}
		else
		{
			$this->eventManager->createRecurrence(
				$syncEvent,
				$syncSection->getSectionConnection(),
				$context
			);
		}
	}

	/**
	 * @throws BaseException
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\LoaderException
	 * @throws Sync\Exceptions\ApiException
	 * @throws Sync\Exceptions\AuthException
	 * @throws Sync\Exceptions\ConflictException
	 * @throws Sync\Exceptions\NotFoundException
	 * @throws Sync\Exceptions\RemoteAccountException
	 */
	private function deleteEvent(SyncEvent $syncEvent, SyncSectionMap $syncSectionMap): void
	{
		$syncSection = $this->getSyncSection(
			$syncSectionMap,
			$syncEvent->getEvent()->getSection()->getId()
		);

		if (!$syncSection)
		{
			return;
		}

		$context = (new Sync\Util\EventContext())->setSectionConnection($syncSection->getSectionConnection());

		$eventConnection = $syncEvent->getEventConnection();

		if ($eventConnection && $eventConnection->getVendorEventId())
		{
			$context->setEventConnection($eventConnection);

			$this->eventManager->delete(
				$syncEvent->getEvent(),
				$context,
			);
		}
	}
}
