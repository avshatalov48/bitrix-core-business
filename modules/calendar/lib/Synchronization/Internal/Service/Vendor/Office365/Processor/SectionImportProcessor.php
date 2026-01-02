<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Processor;

use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Office365\Helper;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Repository\SectionConnectionRepository;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\CalendarListResponse;
use Bitrix\Calendar\Synchronization\Internal\Service\Vendor\Office365\Dto\CalendarResponse;
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
	 * @throws PersistenceException
	 */
	public function import(Connection $connection, CalendarListResponse $calendarList): void
	{
		$this->buildSectionConnectionMap($connection->getId());

		foreach ($calendarList->getItems() as $item)
		{
			if (isset($this->map[$item->id]))
			{
				$sectionConnection = $this->map[$item->id];

				$this->updateSection($sectionConnection, $item);

				unset($this->map[$item->id]);
			}
			else
			{
				$this->createSection($item, $connection);
			}
		}

		foreach ($this->map as $sectionConnection)
		{
			$this->deleteSection($sectionConnection);
		}
	}

	/**
	 * @throws PersistenceException
	 */
	private function createSection(CalendarResponse $item, Connection $connection): void
	{
		$section = (new Section())
			->setName($item->name)
			->setColor($item->getLocalColor())
			->setExternalType(Helper::ACCOUNT_TYPE)
			->setOwner($connection->getOwner())
			->setCreator($connection->getOwner())
			->setType(\Bitrix\Calendar\Core\Event\Tools\Dictionary::CALENDAR_TYPE[User::TYPE])
			->setIsActive(true)
		;

		try
		{
			$section = $this->sectionMapper->create($section);
		}
		catch (ArgumentException $e)
		{
			throw new PersistenceException($e->getMessage(), $e);
		}

		\CCalendarSect::SavePermissions(
			$section->getId(),
			\CCalendarSect::GetDefaultAccess(
				$section->getType(),
				$section->getOwner()->getId()
			)
		);

		$sectionConnection = (new SectionConnection())
			->setSection($section)
			->setConnection($connection)
			->setOwner($connection->getOwner())
			->setVendorSectionId($item->id)
			->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
			->setVersionId($item->changeKey)
			->setPrimary($item->isDefaultCalendar)
		;

		$this->sectionConnectionRepository->save($sectionConnection);
	}

	/**
	 * @throws PersistenceException
	 */
	private function updateSection(SectionConnection $sectionConnection, CalendarResponse $item): void
	{
		$section = $sectionConnection->getSection();

		if (!$section)
		{
			return;
		}

		if (!$section->isLocal())
		{
			$section->setName($item->name);
		}

		if ($color = $item->getLocalColor())
		{
			$section->setColor($color);
		}

		$section = $this->sectionMapper->update($section);

		$sectionConnection
			->setSection($section)
			->setVersionId($item->changeKey)
			->setPrimary($item->isDefaultCalendar)
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
