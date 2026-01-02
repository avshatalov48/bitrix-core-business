<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Model;

use Bitrix\Calendar\Internals\EO_SectionConnection_Entity;
use Bitrix\Calendar\Internals\EO_SectionConnection_Query;
use Bitrix\Calendar\Internals\EO_SectionConnection_Result;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

/**
 * Class SectionConnectionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SectionConnection_Query query()
 * @method static EO_SectionConnection_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SectionConnection_Result getById($id)
 * @method static EO_SectionConnection_Result getList(array $parameters = [])
 * @method static EO_SectionConnection_Entity getEntity()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_SectionConnection createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_SectionConnection_Collection createCollection()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_SectionConnection wakeUpObject($row)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_SectionConnection_Collection wakeUpCollection($rows)
 */
class SectionConnectionTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_calendar_section_connection';
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	public static function getMap(): array
	{
		return array_merge(
			static::getScalarMap(),
			static::getReferenceMap(),
		);
	}

	private static function getScalarMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),

			(new IntegerField('SECTION_ID'))
				->configureRequired(),

			(new IntegerField('CONNECTION_ID'))
				->configureRequired(),

			(new StringField('VENDOR_SECTION_ID'))
				->configureRequired()
				->configureSize(255),

			(new TextField('SYNC_TOKEN'))
				->configureNullable(),

			(new TextField('PAGE_TOKEN'))
				->configureNullable(),

			(new BooleanField('ACTIVE'))
				->configureRequired()
				->configureStorageValues('N', 'Y')
				->configureDefaultValue('Y'),

			(new DatetimeField('LAST_SYNC_DATE'))
				->configureNullable(),

			(new StringField('LAST_SYNC_STATUS'))
				->configureNullable()
				->configureSize(10),

			(new StringField('VERSION_ID'))
				->configureNullable()
				->configureSize(255),

			(new BooleanField('IS_PRIMARY'))
				->configureRequired()
				->configureStorageValues('N', 'Y')
				->configureDefaultValue('N'),
		];
	}

	/**
	 * @throws SystemException
	 * @throws ArgumentException
	 */
	private static function getReferenceMap(): array
	{
		return [
			(new ReferenceField(
				'SECTION',
				SectionTable::class,
				Join::on('this.SECTION_ID', 'ref.ID'),
			)),
			(new ReferenceField(
				'CONNECTION',
				DavConnectionTable::class,
				Join::on('this.CONNECTION_ID', 'ref.ID'),
			)),
		];
	}
}
