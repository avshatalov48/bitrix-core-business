<?php

namespace Bitrix\Calendar\OpenEvents\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class OpenEventCategoryMutedTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OpenEventCategoryMuted_Query query()
 * @method static EO_OpenEventCategoryMuted_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OpenEventCategoryMuted_Result getById($id)
 * @method static EO_OpenEventCategoryMuted_Result getList(array $parameters = [])
 * @method static EO_OpenEventCategoryMuted_Entity getEntity()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection createCollection()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted wakeUpObject($row)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventCategoryMuted_Collection wakeUpCollection($rows)
 */
final class OpenEventCategoryMutedTable extends DataManager
{
	use DeleteByFilterTrait;
	use InsertIgnoreTrait;

	public static function getTableName(): string
	{
		return 'b_calendar_open_event_category_muted';
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
