<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Mappers\Event as EventMapper;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Exception\DtoValidationException;
use Bitrix\Calendar\Synchronization\Internal\Repository\EventConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Logger\RequestLogger;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\EventResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\Event\ExternalEventData;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\AbstractEventImportProcessor;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor\EventConnection\EventConnectionBuilder;
use Bitrix\Calendar\Synchronization\Internal\Service\Processor\Event\EventData;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use Bitrix\Main\SystemException;

class EventImportProcessor extends AbstractEventImportProcessor
{
	public function __construct(
		private readonly EventConnectionBuilder $eventConnectionBuilder,
		private readonly RequestLogger $logger,
		EventConnectionRepository $eventConnectionRepository,
		EventMapper $eventMapper,
	)
	{
		parent::__construct($eventConnectionRepository, $eventMapper);
	}

	/**
	 * @throws ArgumentException
	 * @throws BaseException
	 * @throws DtoValidationException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws PersistenceException
	 * @throws SystemException
	 */
	public function import(SectionConnection $sectionConnection, EventListResponse $eventList): void
	{
		$ids = $this->getEventConnectionIds($eventList->getItems());

		$this->buildLocalEventMap($ids, (int)$sectionConnection->getConnection()?->getId());

		$externalEvents = $this->buildEventsTree($eventList->getItems(), $sectionConnection);

		foreach ($externalEvents as $vendorId => $externalEventData)
		{
			if (is_int($vendorId))
			{
				$this->logger->warning(
					'Unexpected vendor ID type in iCLoud event import: ' . gettype($vendorId),
					[
						'vendorId' => $vendorId,
						'externalEventData' => $externalEventData,
					]
				);
			}

			$localEventData = $this->map->get((string)$vendorId);

			if ($this->shouldEventBeProceed($externalEventData, $localEventData, $eventList->nextSyncToken))
			{
				$this->importEventData($externalEventData, $localEventData, null);
			}
		}
	}

	/**
	 * @param EventResponse[] $eventResponses
	 *
	 * @return string[]
	 */
	private function getEventConnectionIds(array $eventResponses): array
	{
		$ids = [];

		foreach ($eventResponses as $eventResponse)
		{
			$ids[] = $eventResponse->id;
		}

		return array_unique($ids);
	}

	/**
	 * @param EventResponse[] $items
	 * @param SectionConnection $sectionConnection
	 *
	 * @return ExternalEventData[]
	 *
	 * @throws ArgumentException
	 * @throws DtoValidationException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function buildEventsTree(array $items, SectionConnection $sectionConnection): array
	{
		$tree = [];

		$orphans = [];

		foreach ($items as $item)
		{
			$eventConnection = $this->eventConnectionBuilder->build($item, $sectionConnection);

			$externalData = new ExternalEventData($item, $eventConnection);

			$tree[$item->id] = $externalData;

			$this->addInstancesToTree($item, $sectionConnection, $tree, $orphans);
		}

		return $this->addOrphansToTree($orphans, $tree);
	}

	/**
	 * @throws ArgumentException
	 * @throws DtoValidationException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	private function addInstancesToTree(
		EventResponse $masterItem,
		SectionConnection $sectionConnection,
		array $tree,
		array &$orphans,
	): void
	{
		$id = $masterItem->id;

		$instancesData = $masterItem->getOverriddenInstancesData();

		foreach ($instancesData as $instanceData)
		{
			$instanceItem = new EventResponse(
				$masterItem->href,
				$masterItem->etag,
				$masterItem->status,
				$instanceData,
			);

			$recurringEventId = $instanceData['RECURRING_EVENT_ID'] ?? null;

			if ($recurringEventId === null)
			{
				continue;
			}

			$eventConnection = $this->eventConnectionBuilder->build($instanceItem, $sectionConnection);

			$externalData = new ExternalEventData($instanceItem, $eventConnection);

			if (isset($tree[$recurringEventId]))
			{
				$tree[$recurringEventId]->addInstance($externalData);

				if (!empty($orphans[$id]))
				{
					unset($orphans[$id]);
				}
			}
			else
			{
				$orphans[$recurringEventId][$id] = $externalData;
			}
		}
	}

	/**
	 * @param array $orphans
	 * @param ExternalEventData[] $tree
	 *
	 * @return ExternalEventData[]
	 */
	private function addOrphansToTree(array $orphans, array $tree): array
	{
		foreach ($orphans as $items)
		{
			/** @var ExternalEventData $externalEventData */
			foreach ($items as $externalEventData)
			{
				$tree[$externalEventData->eventConnection->getVendorEventId()] = $externalEventData;
			}
		}

		return $tree;
	}

	private function shouldEventBeProceed(
		ExternalEventData $externalEventData,
		?EventData $localEventData,
		?string $syncToken,
	): bool
	{
		$eventResponse = $externalEventData->item;

		if ($eventResponse->isEmpty() && !$externalEventData->isDeleted())
		{
			return false;
		}

		if (!$syncToken && $eventResponse->isOld())
		{
			return false;
		}

		if (!$localEventData)
		{
			return true;
		}

		if (!$localEventData->getEvent()->canBeChanged())
		{
			return false;
		}

		if ($externalEventData->getEvent()->isInstance())
		{
			return true;
		}

		return $this->shouldEventBeSynchronized($externalEventData, $localEventData);
	}

	private function shouldEventBeSynchronized(ExternalEventData $externalEventData, EventData $localEventData): bool
	{
		$externalEventConnection = $externalEventData->eventConnection;

		$localEventConnection = $localEventData->eventConnection;

		if ($externalEventConnection->hasIdenticalVersion($localEventConnection))
		{
			return false;
		}

		if ($externalEventConnection->getEntityTag() !== $localEventConnection->getEntityTag())
		{
			return true;
		}

		if ($externalEventData->getEvent()->isRecurrence())
		{
			$externalInstances = $externalEventData->getInstances();

			foreach ($externalInstances as $externalInstance)
			{
				$localInstance = $localEventData->getSameInstance($externalInstance);

				if (!$localInstance)
				{
					return true;
				}

				$externalInstanceConnection = $externalInstance->eventConnection;

				$localInstanceConnection = $localInstance->eventConnection;

				if ($externalInstanceConnection->getEntityTag() !== $localInstanceConnection->getEntityTag())
				{
					return true;
				}
			}
		}

		return false;
	}

	protected function shouldEnrichFromSameLocal(EventData $externalData): bool
	{
		$localEventData = $this->map->get($externalData->eventConnection->getVendorEventId());

		return
			$localEventData !== null
			&& $localEventData->getEvent()->getId() !== $externalData->getEvent()->getRecurrenceId()
		;
	}

	/**
	 * @throws BaseException
	 */
	protected function mergeEventData(
		EventData $externalEventData,
		EventData $localEventData,
		int $id = null,
		int $eventConnectionId = null,
	): void
	{
		$externalEventData->getEvent()->setAccessibility($localEventData->getEvent()->getAccessibility());

		parent::mergeEventData($externalEventData, $localEventData, $id, $eventConnectionId);
	}

	protected function buildSavingParams(EventData $externalEvent): array
	{
		return [
			'userId' => (int)$externalEvent->getEvent()->getOwner()?->getId(),
			'bAffectToDav' => false, // Used to prevent synchronization with calDav again
			'bSilentAccessMeeting' => true,
			'autoDetectSection' => false,
			'originalFrom' => $externalEvent->eventConnection->getConnection()->getVendor()->getCode(),
		];
	}
}
