<?php

namespace Bitrix\Calendar\OpenEvents\Internals;

use Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory;
use Bitrix\Main\Entity\BooleanField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\Type\DateTime;

/**
 * Class OpenEventCategoryTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OpenEventCategory_Query query()
 * @method static EO_OpenEventCategory_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OpenEventCategory_Result getById($id)
 * @method static EO_OpenEventCategory_Result getList(array $parameters = [])
 * @method static EO_OpenEventCategory_Entity getEntity()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection createCollection()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventCategory wakeUpObject($row)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Collection\OpenEventCategoryCollection wakeUpCollection($rows)
 */
final class OpenEventCategoryTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_calendar_open_event_category';
	}

	public static function getObjectClass(): string
	{
		return OpenEventCategory::class;
	}

	public static function getCollectionClass(): string
	{
		return OpenEventCategoryCollection::class;
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new StringField('NAME'))
				->configureRequired()
			,
			(new IntegerField('CREATOR_ID'))
				->configureRequired()
			,
			(new BooleanField('CLOSED'))
				->configureDefaultValue('N')
				->configureStorageValues('N', 'Y')
			,
			new TextField('DESCRIPTION'),
			new TextField('ACCESS_CODES'),
			(new BooleanField('DELETED'))
				->configureDefaultValue('N')
				->configureStorageValues('N', 'Y')
			,
			(new IntegerField('CHANNEL_ID'))
				->configureRequired()
			,
			(new IntegerField('EVENTS_COUNT'))
				->configureRequired()
				->configureDefaultValue(0)
			,
			(new DatetimeField('DATE_CREATE'))
				->configureRequired()
				->configureDefaultValue(static fn() => new DateTime())
			,
			(new DatetimeField('LAST_ACTIVITY'))
				->configureRequired()
				->configureDefaultValue(static fn() => new DateTime())
			,
		];
	}
}
