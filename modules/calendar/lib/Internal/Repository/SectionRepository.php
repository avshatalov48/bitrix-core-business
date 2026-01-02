<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Internal\Repository;

use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core\Mappers\Section;
use Bitrix\Calendar\Core\Role\User;
use Bitrix\Calendar\Internals\EO_SectionConnection;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

class SectionRepository
{
	/**
	 * @param int $userId
	 * @param int $connectionId
	 * @param array $externalTypes
	 *
	 * @return int[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getExportingSectionsIds(int $userId, int $connectionId, array $externalTypes = []): array
	{
		$externalTypes[] = Section::SECTION_TYPE_LOCAL;

		$sectionDb = SectionTable::query()
			->setSelect([
				'ID',
				'EXTERNAL_TYPE',
				'SECTION_CONNECTION.CONNECTION',
			])
			->where('OWNER_ID', $userId)
			->whereIn('EXTERNAL_TYPE', array_unique($externalTypes))
			->where('CAL_TYPE', User::TYPE)
			->registerRuntimeField('SECTION_CONNECTION',
				new ReferenceField(
					'SYNC_DATA',
					SectionConnectionTable::getEntity(),
					Join::on('ref.SECTION_ID', 'this.ID'),
					['join_type' => Join::TYPE_LEFT],
				),
			)
			->exec()
		;

		$ids = [];

		while ($ormSection = $sectionDb->fetchObject())
		{
			if ($ormSection->getExternalType() === Section::SECTION_TYPE_LOCAL)
			{
				$ids[] = $ormSection->getId();
			}
			/** @var EO_SectionConnection $sectionConnection */
			elseif ($sectionConnection = $ormSection->get('SECTION_CONNECTION'))
			{
				$connection = $sectionConnection->getConnection();

				if ($connection && $connection->getId() !== $connectionId)
				{
					continue;
				}

				$ids[] = $ormSection->getId();
			}
		}

		return $ids;
	}

	/**
	 * @param Connection $connection
	 *
	 * @return \Bitrix\Calendar\Core\Section\Section[]
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getExportingSections(Connection $connection): array
	{
		$ormSections =
			SectionTable::query()
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
				])
				->where('OWNER_ID', (int)$connection->getOwner()?->getId())
				->where('CAL_TYPE', $connection->getOwner()?->getType())
				->whereIn('EXTERNAL_TYPE', [
					Section::SECTION_TYPE_LOCAL,
					$connection->getVendor()->getCode(), // TODO: Is vendor's sections needed to export?
				])
				// TODO: Is non active sections needed to export?
				->exec()
		;

		$sections = [];

		while ($ormSection = $ormSections->fetchObject())
		{
			$section = (new SectionBuilderFromDataManager($ormSection))->build();

			$sections[] = $section;
		}

		return $sections;
	}
}
