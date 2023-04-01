<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectNotFoundException;

class ImportEventManager
{
	private Sync\Entities\SyncEventMap $externalEventMap;
	private Core\Base\Map $syncSectionCollection;
	private IncomingEventManagerInterface $importManager;
	/**
	 * @var Core\Mappers\Factory
	 */
	private $mapperFactory;

	/**
	 * @param Sync\Factories\FactoryBase $factory
	 * @param Core\Base\Map $syncSectionCollection
	 *
	 * @throws ObjectNotFoundException
	 */
	public function __construct(Sync\Factories\FactoryBase $factory, Core\Base\Map $syncSectionCollection)
	{
		$this->importManager = $factory->getIncomingEventManager();
		$this->syncSectionCollection = $syncSectionCollection;
		$this->externalEventMap = new Sync\Entities\SyncEventMap();
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @return $this
	 *
	 * @throws Core\Base\BaseException
	 * @throws ArgumentException
	 */
	public function import(): ImportEventManager
	{
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($this->syncSectionCollection as $syncSection)
		{
			if (
				$syncSection->getSectionConnection() === null
				|| !$syncSection->getSectionConnection()->isActive()
			)
			{
				continue;
			}

			try
			{
				$result = $this->importManager->getEvents($syncSection);

				if ($result->isSuccess())
				{
					$this->handleCalendarChange(($result->getData()['externalSyncEventMap'])->getCollection());
					$this->externalEventMap->addItems(($result->getData()['externalSyncEventMap'])->getCollection());
					$syncSection
						->getSectionConnection()
						->setLastSyncDate(new Core\Base\Date())
						->setSyncToken($this->importManager->getSyncToken())
						->setPageToken($this->importManager->getPageToken())
						->setLastSyncStatus($this->importManager->getStatus())
						->setVersionId($this->importManager->getEtag())
					;
					$syncSection->setSectionConnection(
						$this->saveSectionConnection($syncSection->getSectionConnection())
					);
				}
			}
			catch (Sync\Exceptions\NotFoundException $e)
			{
				$syncSection->getSectionConnection()
					->setActive(false)
					->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['deleted']);
				$syncSection->setSectionConnection(
					$this->saveSectionConnection($syncSection->getSectionConnection())
				);
			}
			catch (Sync\Exceptions\AuthException | Sync\Exceptions\RemoteAccountException $e)
			{
				$syncSection->getSectionConnection()
					->setLastSyncStatus(Sync\Dictionary::SYNC_STATUS['failed']);
				$syncSection->setSectionConnection(
					$this->saveSectionConnection($syncSection->getSectionConnection())
				);
			}
		}

		return $this;
	}

	/**
	 * @param Sync\Connection\SectionConnection $link
	 *
	 * @return Sync\Connection\SectionConnection
	 *
	 * @throws ArgumentException
	 * @throws Core\Base\BaseException
	 */
	private function saveSectionConnection(Sync\Connection\SectionConnection $link): Sync\Connection\SectionConnection
	{
		$mapper = $this->mapperFactory->getSectionConnection();
		return $link->getId()
			? $mapper->update($link)
			: $mapper->create($link)
			;
	}

	/**
	 * @return Sync\Entities\SyncEventMap
	 */
	public function getEvents(): Sync\Entities\SyncEventMap
	{
		return $this->externalEventMap;
	}

	/**
	 * @throws ArgumentException
	 */
	private function handleCalendarChange(array $collection)
	{
		$handledCollection = $this->externalEventMap->getCollection();

		/**
		 * @var string $key
		 * @var Sync\Entities\SyncEvent $value
		 */
		foreach ($collection as $key => $value)
		{
			if (
				array_key_exists($key, $handledCollection)
				&& $value->getAction() === 'save'
			)
			{
				$this->externalEventMap->remove($key);
				$this->externalEventMap->add($value, $key);
			}
		}
	}
}
