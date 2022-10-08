<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core\Mappers\Section;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Entities\SyncSectionMap;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Dav\Internals\EO_DavConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Calendar\Core;
use Bitrix\Calendar\Sync;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

class SyncSectionFactory
{
	protected Core\Mappers\SectionConnection $sectionConnectionMapper;
	protected Core\Mappers\Section $sectionMapper;

	public function __construct()
	{
		Loader::includeModule('dav');
		/** @var Core\Mappers\Factory $mapperHelper */
		$mapperHelper = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$this->sectionConnectionMapper = $mapperHelper->getSectionConnection();
		$this->sectionMapper = $mapperHelper->getSection();
	}

	/**
	 * @param FactoryBase $factory
	 * @return SyncSectionMap
	 */
	public function getSyncSectionMapByFactory(FactoryBase $factory): SyncSectionMap
	{
		$syncSectionMap = new SyncSectionMap();
		$connection = $factory->getConnection();
		$connectionId = $connection->getId();
		$ownerId = $connection->getOwner()->getId();
		$this->getLocalSyncSectionMapByUserId(
			$ownerId,
			$connectionId,
			$syncSectionMap
		);
		$this->getExternalSyncSectionMapByUserId(
			$ownerId,
			$connectionId,
			$syncSectionMap,
			$factory->getSectionManager()->getAvailableExternalType()
		);

		return $syncSectionMap;
	}

	/**
	 * @param int $userId
	 * @param int $connectionId
	 * @param array $externalType
	 *
	 * @return Sync\Entities\SyncSectionMap
	 *
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getLocalSyncSectionMapByUserId(
		int $userId,
		int $connectionId,
		Sync\Entities\SyncSectionMap $syncSectionMap
	): void
	{
		$sectionDb = SectionTable::query()
			->where('OWNER_ID', $userId)
			->where('EXTERNAL_TYPE', Core\Mappers\Section::SECTION_TYPE_LOCAL)
			->where('CAL_TYPE', Core\Role\User::TYPE)
			->registerRuntimeField('SECTION_CONNECTION',
				new ReferenceField(
					'SYNC_DATA',
					SectionConnectionTable::getEntity(),
					Join::on('ref.SECTION_ID', 'this.ID')->where('ref.CONNECTION_ID', $connectionId),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->registerRuntimeField('CONNECTION',
				new ReferenceField(
					'CONNECTION',
					DavConnectionTable::getEntity(),
					Join::on('ref.ID', 'this.SECTION_CONNECTION.CONNECTION_ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->setSelect([
				'ID',
				'NAME',
				'XML_ID',
				'ACTIVE',
				'DESCRIPTION',
				'COLOR',
				'CAL_TYPE',
				'OWNER_ID',
				'EXTERNAL_TYPE',
				'CONNECTION.ACCOUNT_TYPE',
				'SECTION_CONNECTION.*',
				'SECTION_CONNECTION.SECTION',
				'SECTION_CONNECTION.CONNECTION',
			])
			->exec()
		;

		while ($sectionDM = $sectionDb->fetchObject())
		{
			$sectionId = null;
			$syncSection = new Sync\Entities\SyncSection();

			$section = (new SectionBuilderFromDataManager($sectionDM))->build();
			$syncSection
				->setSection($section)
				->setAction('success')
			;

			/** @var EO_SectionConnection $sectionConnectionDM */
			if ($sectionConnectionDM = $sectionDM->get('SECTION_CONNECTION'))
			{
				$sectionConnection = (new Sync\Builders\BuilderSectionConnectionFromDM($sectionConnectionDM))->build();

				$sectionConnection->setSection($section);
				$syncSection->setSectionConnection($sectionConnection);
				$sectionId = $sectionConnection->getVendorSectionId();
			}

			/** @var EO_DavConnection $connectionDM */
			if ($sectionDM->getExternalType() !== Section::SECTION_TYPE_LOCAL && ($connectionDM = $sectionDM->get('CONNECTION')))
			{
				$syncSection->setVendorName($connectionDM->getAccountType());
			}
			else
			{
				$syncSection->setVendorName($sectionDM->getExternalType() ?? Section::SECTION_TYPE_LOCAL);
			}

			$sectionId = $sectionId ?? (string)$section->getId();

			$syncSectionMap->add($syncSection, $sectionId);
		}
	}

	/**
	 * @param int $userId
	 * @param int $connectionId
	 * @param Sync\Entities\SyncSectionMap $syncSectionMap
	 * @param array $externalType
	 * @return Sync\Entities\SyncSectionMap
	 * @throws ArgumentException
	 * @throws ObjectException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getExternalSyncSectionMapByUserId(
		int $userId,
		int $connectionId,
		Sync\Entities\SyncSectionMap $syncSectionMap,
		array $externalType
	): void
	{
		$sectionDb = SectionTable::query()
			->where('OWNER_ID', $userId)
			->whereIn('EXTERNAL_TYPE', $externalType)
			->where('CAL_TYPE', Core\Role\User::TYPE)
			->registerRuntimeField('SECTION_CONNECTION',
				new ReferenceField(
					'SYNC_DATA',
					SectionConnectionTable::getEntity(),
					Join::on('ref.SECTION_ID', 'this.ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->registerRuntimeField('CONNECTION',
				new ReferenceField(
					'CONNECTION',
					DavConnectionTable::getEntity(),
					Join::on('ref.ID', 'this.SECTION_CONNECTION.CONNECTION_ID'),
					['join_type' => Join::TYPE_LEFT]
				)
			)
			->setSelect([
				'ID',
				'NAME',
				'XML_ID',
				'ACTIVE',
				'DESCRIPTION',
				'COLOR',
				'CAL_TYPE',
				'OWNER_ID',
				'EXTERNAL_TYPE',
				'CONNECTION.ACCOUNT_TYPE',
				'SECTION_CONNECTION.*',
				'SECTION_CONNECTION.SECTION',
				'SECTION_CONNECTION.CONNECTION',
			])
			->exec()
		;

		while ($sectionDM = $sectionDb->fetchObject())
		{
			$sectionId = null;
			$syncSection = new Sync\Entities\SyncSection();

			$section = (new SectionBuilderFromDataManager($sectionDM))->build();
			$syncSection
				->setSection($section)
				->setAction('success')
			;

			/** @var EO_SectionConnection $sectionConnectionDM */
			if ($sectionConnectionDM = $sectionDM->get('SECTION_CONNECTION'))
			{
				$sectionConnection = (new Sync\Builders\BuilderSectionConnectionFromDM($sectionConnectionDM))->build();
				if (
					($sectionConnection->getConnection() !== null)
					&& ($sectionConnection->getConnection()->getId() !== $connectionId)
				)
				{
					continue;
				}

				$sectionConnection->setSection($section);
				$syncSection->setSectionConnection($sectionConnection);
				$sectionId = $sectionConnection->getVendorSectionId();
			}
			else
			{
				continue;
			}

			/** @var EO_DavConnection $connectionDM */
			if ($sectionDM->getExternalType() !== Section::SECTION_TYPE_LOCAL && ($connectionDM = $sectionDM->get('CONNECTION')))
			{
				$syncSection->setVendorName($connectionDM->getAccountType());
			}
			else
			{
				$syncSection->setVendorName($sectionDM->getExternalType() ?? Section::SECTION_TYPE_LOCAL);
			}

			$sectionId = $sectionId ?? (string)$section->getId();

			$syncSectionMap->add($syncSection, $sectionId);
		}
	}
}
