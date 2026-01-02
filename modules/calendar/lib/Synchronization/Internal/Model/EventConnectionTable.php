<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Model;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

/**
 * Class EventConnectionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventConnection_Query query()
 * @method static EO_EventConnection_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventConnection_Result getById($id)
 * @method static EO_EventConnection_Result getList(array $parameters = [])
 * @method static EO_EventConnection_Entity getEntity()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_EventConnection createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_EventConnection_Collection createCollection()
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_EventConnection wakeUpObject($row)
 * @method static \Bitrix\Calendar\Synchronization\Internal\Model\EO_EventConnection_Collection wakeUpCollection($rows)
 */
class EventConnectionTable extends DataManager
{
	public static function getTableName()
	{
		return 'b_calendar_event_connection';
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

			(new IntegerField('EVENT_ID'))
				->configureRequired(),

			(new IntegerField('CONNECTION_ID'))
				->configureRequired(),

			(new StringField('VENDOR_EVENT_ID'))
				->configureSize(255),

			(new StringField('SYNC_STATUS'))
				->configureNullable()
				->configureSize(20),

			(new IntegerField('RETRY_COUNT'))
				->configureDefaultValue(0),

			(new StringField('ENTITY_TAG'))
				->configureNullable()
				->configureSize(255),

			(new StringField('VERSION'))
				->configureNullable()
				->configureSize(255),

			(new StringField('VENDOR_VERSION_ID'))
				->configureNullable()
				->configureSize(255),

			(new StringField('RECURRENCE_ID'))
				->configureNullable()
				->configureSize(255),

			(new ArrayField('DATA'))
				->configureNullable(),
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
				'EVENT',
				EventTable::class,
				Join::on('this.EVENT_ID', 'ref.ID'),
			)),
			(new ReferenceField(
				'CONNECTION',
				DavConnectionTable::class,
				Join::on('this.CONNECTION_ID', 'ref.ID'),
			)),
		];
	}
}
