<?php
namespace Bitrix\Calendar\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class OriginalRecursionTable
 *
 * Fields:
 * <ul>
 * <li> PARENT_EVENT_ID int mandatory
 * <li> ORIGINAL_RECURSION_EVENT_ID int mandatory
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventOriginalRecursion_Query query()
 * @method static EO_EventOriginalRecursion_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventOriginalRecursion_Result getById($id)
 * @method static EO_EventOriginalRecursion_Result getList(array $parameters = [])
 * @method static EO_EventOriginalRecursion_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_EventOriginalRecursion createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_EventOriginalRecursion wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_EventOriginalRecursion_Collection wakeUpCollection($rows)
 */

final class EventOriginalRecursionTable extends DataManager
{
	use MergeTrait;
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_calendar_event_original_recursion';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			(new IntegerField('PARENT_EVENT_ID'))
				->configurePrimary()
			,
			(new IntegerField('ORIGINAL_RECURSION_EVENT_ID'))
				->configureRequired()
			,
		];
	}
}