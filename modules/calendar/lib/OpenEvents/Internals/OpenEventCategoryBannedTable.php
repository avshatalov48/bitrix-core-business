<?php

namespace Bitrix\Calendar\OpenEvents\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class OpenEventCategoryBannedTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OpenEventCategoryBanned_Query query()
 * @method static EO_OpenEventCategoryBanned_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OpenEventCategoryBanned_Result getById($id)
 * @method static EO_OpenEventCategoryBanned_Result getList(array $parameters = [])
 * @method static EO_OpenEventCategoryBanned_Entity getEntity()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection createCollection()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned wakeUpObject($row)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryBanned_Collection wakeUpCollection($rows)
 */
final class OpenEventCategoryBannedTable extends DataManager
{
	use DeleteByFilterTrait;
	use InsertIgnoreTrait;

	public static function getTableName(): string
	{
		return 'b_calendar_open_event_category_banned';
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
