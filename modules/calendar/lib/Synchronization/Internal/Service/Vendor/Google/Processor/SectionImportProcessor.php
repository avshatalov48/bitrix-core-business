<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Processor;

use Bitrix\Calendar\Core\Property\ColorHelper;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\CalendarListEntryResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Google\Dto\CalendarListResponse;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;

class SectionImportProcessor
{
	/**
	 * @var array<string, SectionConnection>
	 */
	private array $map = [];

	public function __construct(
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly \Bitrix\Calendar\Core\Mappers\Section $sectionMapper
	)
	{
	}

	private function buildSectionConnectionMap(int $connectionId): void
	{
		$this->map = [];

		$sectionConnections = $this->sectionConnectionRepository->getByConnection($connectionId);

		/** @var SectionConnection $sectionConnection */
		foreach ($sectionConnections as $sectionConnection)
		{
			$this->map[$sectionConnection->getVendorSectionId()] = $sectionConnection;
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	public function partialImport(Connection $connection, CalendarListResponse $calendarList): void
	{
		$this->buildSectionConnectionMap($connection->getId());

		foreach ($calendarList->getItems() as $item)
		{
			if (isset($this->map[$item->id]))
			{
				$sectionConnection = $this->map[$item->id];

				if ($item->deleted)
				{
					$this->deleteSection($sectionConnection);
				}
				else
				{
					$this->updateSection($sectionConnection, $item);
				}
			}
			elseif (!$item->deleted)
			{
				$this->createSection($item, $connection);
			}
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	public function fullImport(Connection $connection, CalendarListResponse $calendarList): void
	{
		$this->partialImport($connection, $calendarList);

		foreach ($calendarList->getItems() as $item)
		{
			if (isset($this->map[$item->id]) && !$item->deleted)
			{
				unset($this->map[$item->id]);
			}
		}

		foreach ($this->map as $sectionConnection)
		{
			$this->deleteSection($sectionConnection);
		}
	}

	/**
	 * @throws PersistenceException
	 * @throws ArgumentException
	 */
	private function createSection(CalendarListEntryResponse $item, Connection $connection): void
	{
		$section = (new Section())
			->setName($item->summary)
			->setColor($item->backgroundColor ?: ColorHelper::getOurColorRandom())
			->setOwner($connection->getOwner())
			->setCreator($connection->getOwner())
			->setExternalType(
				\Bitrix\Calendar\Sync\Google\Dictionary::ACCESS_ROLE_TO_EXTERNAL_TYPE[$item->accessRole]
			)
			->setType(\Bitrix\Calendar\Core\Event\Tools\Dictionary::CALENDAR_TYPE[User::TYPE])
			->setIsActive(true)
			->setDescription($item->description)
		;

		$section = $this->sectionMapper->create($section);

		\CCalendarSect::SavePermissions(
			$section->getId(),
			\CCalendarSect::GetDefaultAccess(
				$section->getType(),
				$section->getOwner()->getId()
			)
		);

		$sectionConnection = (new SectionConnection())
			->setOwner($connection->getOwner())
			->setSection($section)
			->setConnection($connection)
			->setVendorSectionId($item->id)
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			->setVersionId($item->etag)
			->setPrimary($item->primary)
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	/**
	 * @throws PersistenceException
	 */
	private function updateSection(SectionConnection $sectionConnection, CalendarListEntryResponse $item): void
	{
		$section = $sectionConnection->getSection();

		if (!$section)
		{
			return;
		}

		if (!$section->isLocal())
		{
			$section->setName($item->summary);
		}

		$section->setDescription($item->description);

		if ($item->backgroundColor)
		{
			$section->setColor($item->backgroundColor);
		}

		$section = $this->sectionMapper->update($section);

		$sectionConnection
			->setSection($section)
			->setVersionId($item->etag)
			->setPrimary($item->primary)
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	private function deleteSection(SectionConnection $sectionConnection): void
	{
		if ($sectionConnection->getSection() && !$sectionConnection->getSection()->isLocal())
		{
			$this->sectionMapper->delete($sectionConnection->getSection(), ['softDelete' => false]);
		}
	}
}
