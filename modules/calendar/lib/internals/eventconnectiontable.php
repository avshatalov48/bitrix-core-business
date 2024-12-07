<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\SystemException;

// TODO: localization
// TODO: add class description

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
 * @method static \Bitrix\Calendar\Internals\EO_EventConnection createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_EventConnection_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_EventConnection wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_EventConnection_Collection wakeUpCollection($rows)
 */
class EventConnectionTable extends DataManager
{
	// without data field
	public const defaultSelect = [
		'ID',
		'EVENT_ID',
		'CONNECTION_ID',
		'VENDOR_EVENT_ID',
		'SYNC_STATUS',
		'RETRY_COUNT',
		'ENTITY_TAG',
		'VERSION',
		'VENDOR_VERSION_ID',
		'RECURRENCE_ID',
	];
	public static function getTableName()
	{
		return 'b_calendar_event_connection';
	}

	/**
	 * @return array
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public static function getMap()
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('EVENT_ID'))
				->configureRequired()
			,
			(new IntegerField('CONNECTION_ID'))
				->configureRequired()
			,
			(new StringField('VENDOR_EVENT_ID'))
				->configureSize(255)
			,
			(new StringField('SYNC_STATUS'))
				->configureNullable()
				->configureSize(20)
			,
			(new IntegerField('RETRY_COUNT'))
				->configureDefaultValue(0)
			,
			(new StringField('ENTITY_TAG'))
				->configureNullable()
				->configureSize(255)
			,
			(new StringField('VENDOR_VERSION_ID'))
				->configureNullable()
				->configureSize(255)
			,
			(new StringField('VERSION'))
				->configureNullable()
				->configureSize(255)
			,
			(new ArrayField('DATA'))
				->configureNullable()
			,
			(new StringField('RECURRENCE_ID'))
				->configureNullable()
				->configureSize(255)
			,
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
