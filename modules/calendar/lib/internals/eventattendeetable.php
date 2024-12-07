<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\TextField;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class EventAttendeeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventAttendee_Query query()
 * @method static EO_EventAttendee_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventAttendee_Result getById($id)
 * @method static EO_EventAttendee_Result getList(array $parameters = [])
 * @method static EO_EventAttendee_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EventAttendee createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_EventAttendee_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EventAttendee wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_EventAttendee_Collection wakeUpCollection($rows)
 */
class EventAttendeeTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_calendar_event_attendee';
	}

	public static function getObjectClass()
	{
		return EventAttendee::class;
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete(),
			(new IntegerField('OWNER_ID'))
				->configureRequired(true),
			(new IntegerField('CREATED_BY'))
				->configureRequired(true),
			(new StringField('MEETING_STATUS'))
				->configureRequired(true)
				->configureSize(1),
			(new BooleanField('DELETED'))
				->configureValues('N', 'Y')
				->configureDefaultValue('N'),
			new IntegerField('SECTION_ID'),
			new ReferenceField(
				'SECTION',
				SectionTable::class,
				Join::on('this.SECTION_ID', 'ref.ID'),
			),
			(new StringField('COLOR'))
				->configureSize(10)
				->configureNullable(),
			new TextField('REMIND'),
			(new StringField('DAV_EXCH_LABEL'))
				->configureNullable(),
			(new StringField('SYNC_STATUS'))
				->configureSize(20),
			(new IntegerField('EVENT_ID'))
				->configureRequired(true),
			new ReferenceField(
				'EVENT',
				EventTable::class,
				Join::on('this.EVENT_ID', 'ref.ID')
			),
		];
	}
}