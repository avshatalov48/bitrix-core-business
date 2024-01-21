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
 **/

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