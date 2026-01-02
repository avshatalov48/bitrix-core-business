<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Repository\Mapper;

use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Synchronization\Internal\Entity\SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Model\EO_SectionConnection;
use Bitrix\Calendar\Synchronization\Internal\Model\SectionConnectionTable;
use Bitrix\Main\ObjectException;
use Bitrix\Main\Type\DateTime;

class SectionConnectionMapper
{
	/**
	 * @throws ObjectException
	 * @todo Add system exception handling
	 */
	public function convertFromOrm(EO_SectionConnection $ormModel): SectionConnection
	{
		$sectionConnection = new SectionConnection();

		if ($ormSection = $ormModel->getSection())
		{
			// @todo Remove old dependency
			$sectionBuilder = new SectionBuilderFromDataManager($ormSection);

			$section = $sectionBuilder->build();

			$sectionConnection
				->setSection($section)
				->setOwner($section->getOwner())
			;
		}

		if ($ormConnection = $ormModel->getConnection())
		{
			// @todo Remove old dependency
			$connectionBuilder = new BuilderConnectionFromDM($ormConnection);

			$connection = $connectionBuilder->build();

			$sectionConnection->setConnection($connection);
		}

		$sectionConnection
			->setId($ormModel->getId())
			->setVendorSectionId($ormModel->getVendorSectionId())
			->setSyncToken($ormModel->getSyncToken())
			->setPageToken($ormModel->getPageToken())
			->setActive($ormModel->getActive())
			->setLastSyncDate(new Date($ormModel->getLastSyncDate()))
			->setLastSyncStatus($ormModel->getLastSyncStatus())
			->setVersionId($ormModel->getVersionId())
			->setPrimary($ormModel->getIsPrimary())
		;

		return $sectionConnection;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertToOrm(SectionConnection $entity): EO_SectionConnection
	{
		$ormSectionConnection = $entity->getId()
			? EO_SectionConnection::wakeUp($entity->getId())
			: SectionConnectionTable::createObject();

		$ormSectionConnection
			->setSectionId($entity->getSection()?->getId())
			->setConnectionId($entity->getConnection()->getId())
			->setVendorSectionId($entity->getVendorSectionId())
			->setSyncToken($entity->getSyncToken())
			->setPageToken($entity->getPageToken())
			->setActive($entity->isActive())
			->setLastSyncDate(
				new DateTime(
					$entity->getLastSyncDate()->toString(),
					$entity->getLastSyncDate()->getDateTimeFormat()
				)
			)
			->setLastSyncStatus($entity->getLastSyncStatus())
			->setVersionId($entity->getVersionId())
			->setIsPrimary($entity->isPrimary());

		return $ormSectionConnection;
	}
}
