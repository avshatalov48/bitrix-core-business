<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Sync;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync\Exceptions\ConflictException;
use Bitrix\Calendar\Sync\Util\Result;

class OutgoingSectionManager
{
	protected Sync\Factories\FactoryBase $factory;
	protected Sync\Entities\SyncSectionMap $sectionMap;

	/**
	 * @param Sync\Factories\FactoryBase $factory
	 * @param Sync\Entities\SyncSectionMap $sectionMap
	 */
	public function __construct(Sync\Factories\FactoryBase $factory, Sync\Entities\SyncSectionMap $sectionMap)
	{
		$this->factory = $factory;
		$this->sectionMap = $sectionMap;
	}

	/**
	 * @return $this
	 */
	public function export(): self
	{
		/** @var Sync\Entities\SyncSection $syncSection */
		foreach ($this->sectionMap as $key => $syncSection)
		{
			if ($syncSection->getSectionConnection() === null)
			{
				$result = $this->safeCreate($syncSection->getSection());
			}
			else
			{
				$result = $this->safeUpdate($syncSection);
			}

			if ($result->isSuccess())
			{
				$exportedSyncSection = $result->getData()['syncSection'];
				if ($exportedSyncSection)
				{
					$this->sectionMap->updateItem($exportedSyncSection,
						$exportedSyncSection->getSectionConnection()->getVendorSectionId());
					$this->sectionMap->remove($key);
				}
			}
		}

		return $this;
	}

	/**
	 * @return Sync\Entities\SyncSectionMap
	 */
	public function getSyncSectionMap(): Sync\Entities\SyncSectionMap
	{
		return $this->sectionMap;
	}

	/**
	 * @param Core\Section\Section $section
	 * @return Result
	 */
	private function safeCreate(Core\Section\Section $section): Sync\Util\Result
	{
		$result = new Result();
		$counter = 0;
		$originalName = $section->getName();
		$sectionManager = $this->factory->getSectionManager();
		do
		{
			try
			{
				$result = $sectionManager->create($section, new Sync\Util\SectionContext([]));
				$success = true;
			}
			catch (ConflictException $e)
			{
				$counter++;
				$section->setName($originalName . " ($counter)");
				$success = false;
			}
		}
		while (!$success);

		$section->setName($originalName);

		return $result;
	}

	private function safeUpdate(Sync\Entities\SyncSection $syncSection): Sync\Util\Result
	{
		try
		{
			$result = $this->factory->getSectionManager()->update(
				$syncSection->getSection(),
				$this->prepareContextForUpdate($syncSection)
			);
		}
		catch (Sync\Exceptions\NotFoundException $e)
		{
			$this->clearBrokenSyncSection($syncSection);
			$result = $this->safeCreate($syncSection->getSection());
		}

		return $result;
	}

	/**
	 * @param Sync\Entities\SyncSection $syncSection
	 *
	 * @return void
	 * TODO: move it in a special class
	 */
	private function clearBrokenSyncSection(Sync\Entities\SyncSection $syncSection)
	{
		global $DB;
		$sql = "DELETE link FROM b_calendar_event_connection link
				inner join b_calendar_event as event ON event.ID=link.EVENT_ID
			where event.SECTION_ID = '" . $syncSection->getSection()->getId() . "'
			and link.CONNECTION_ID = '". $syncSection->getSectionConnection()->getConnection()->getId() ."'
		";
		$DB->Query($sql);
		(new Core\Mappers\SectionConnection())->delete(
			$syncSection->getSectionConnection(),
			['softDelete' => false]
		);
		$syncSection->setSectionConnection(null);
	}

	/**
	 * @param Sync\Entities\SyncSection $syncSection
	 *
	 * @return Sync\Util\SectionContext
	 */
	private function prepareContextForUpdate(Sync\Entities\SyncSection $syncSection)
	{
		return (new Sync\Util\SectionContext())->setSectionConnection($syncSection->getSectionConnection());
	}
}
