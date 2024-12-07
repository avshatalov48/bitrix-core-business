<?php

namespace Bitrix\Calendar\OpenEvents\Internals;

use Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;

/**
 * Class EventCategoryAttendeeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OpenEventCategoryAttendee_Query query()
 * @method static EO_OpenEventCategoryAttendee_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OpenEventCategoryAttendee_Result getById($id)
 * @method static EO_OpenEventCategoryAttendee_Result getList(array $parameters = [])
 * @method static EO_OpenEventCategoryAttendee_Entity getEntity()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection createCollection()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategoryAttendee wakeUpObject($row)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryAttendeeCollection wakeUpCollection($rows)
 */
final class OpenEventCategoryAttendeeTable extends DataManager
{
	use DeleteByFilterTrait;
	use InsertIgnoreTrait;

	public static function getTableName(): string
	{
		return 'b_calendar_open_event_category_attendee';
	}

	public static function getObjectClass(): string
	{
		return OpenEventCategoryAttendee::class;
	}

	public static function getCollectionClass(): string
	{
		return OpenEventCategoryAttendeeCollection::class;
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('USER_ID'))
				->configureRequired()
			,
			(new IntegerField('CATEGORY_ID'))
				->configureRequired()
			,
		];
	}
}
