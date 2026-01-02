<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Processor;

// TODO: Remove API from sync when we switch to the new one
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Property\ColorHelper;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Icloud\Helper;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\CalendarListEntryResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\ICloud\Dto\CalendarListResponse;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Repository\Exception\PersistenceException;
use CCalendarSect;

class SectionImportProcessor
{
	/**
	 * @var array<string, SectionConnection>
	 */
	private array $map = [];

	public function __construct(
		private readonly SectionConnectionRepository $sectionConnectionRepository,
		private readonly \Bitrix\Calendar\Core\Mappers\Section $sectionMapper,
	)
	{
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	public function import(Connection $connection, CalendarListResponse $calendarList): void
	{
		$this->buildSectionConnectionMap($connection->getId());

		foreach ($calendarList->getItems() as $item)
		{
			if (!isset($this->map[$item->id]))
			{
				$this->createSection($item, $connection);

				continue;
			}

			$sectionConnection = $this->map[$item->id];

			if ($sectionConnection->getVersionId() !== $item->etag)
			{
				$this->updateSection($sectionConnection, $item);
			}

			unset($this->map[$item->id]);
		}

		foreach ($this->map as $sectionConnection)
		{
			$this->deleteSection($sectionConnection);
		}
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	private function createSection(CalendarListEntryResponse $item, Connection $connection): void
	{
		$section =
			(new Section())
				->setName($item->name)
				->setColor($item->backgroundColor ?: ColorHelper::getOurColorRandom())
				->setOwner($connection->getOwner())
				->setCreator($connection->getOwner())
				->setExternalType(Helper::ACCOUNT_TYPE)
				->setType(\Bitrix\Calendar\Core\Event\Tools\Dictionary::CALENDAR_TYPE[User::TYPE])
				->setIsActive(true)
				->setDescription($item->description)
		;

		$section = $this->sectionMapper->create($section);

		CCalendarSect::SavePermissions(
			$section->getId(),
			CCalendarSect::GetDefaultAccess(
				$section->getType(),
				$section->getOwner()->getId(),
			),
		);

		$sectionConnection =
			(new SectionConnection())
				->setOwner($connection->getOwner())
				->setSection($section)
				->setConnection($connection)
				->setVendorSectionId($item->id)
				->setLastSyncDate(new Date())
				->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
				->setVersionId($item->etag)
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	/**
	 * @throws ArgumentException
	 * @throws PersistenceException
	 */
	private function updateSection(SectionConnection $sectionConnection, CalendarListEntryResponse $item): void
	{
		$section = $sectionConnection->getSection();

		if ($section === null)
		{
			throw new ArgumentException('Section connection has no section');
		}

		if (!$section->isLocal())
		{
			$section->setName($item->name);
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
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	private function deleteSection(SectionConnection $sectionConnection): void
	{
		if (!$sectionConnection->getSection() || $sectionConnection->getSection()->isLocal())
		{
			return;
		}

		$this->sectionMapper->delete($sectionConnection->getSection(), ['softDelete' => false]);
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
}
