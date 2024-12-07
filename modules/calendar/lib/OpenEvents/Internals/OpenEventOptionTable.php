<?php

namespace Bitrix\Calendar\OpenEvents\Internals;

use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption as OpenEventOption;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Entity\TextField;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Query\Join;

/**
 * Class OpenEventOptionTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OpenEventOption_Query query()
 * @method static EO_OpenEventOption_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OpenEventOption_Result getById($id)
 * @method static EO_OpenEventOption_Result getList(array $parameters = [])
 * @method static EO_OpenEventOption_Entity getEntity()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection createCollection()
 * @method static \Bitrix\Calendar\OpenEvents\Internals\Entity\OpenEventOption wakeUpObject($row)
 * @method static \Bitrix\Calendar\OpenEvents\Internals\EO_OpenEventOption_Collection wakeUpCollection($rows)
 */
final class OpenEventOptionTable extends DataManager
{
	public static function getTableName(): string
	{
		return 'b_calendar_open_event_option';
	}

	public static function getObjectClass()
	{
		return OpenEventOption::class;
	}

	public static function getMap(): array
	{
		return [
			(new IntegerField('ID'))
				->configurePrimary()
				->configureAutocomplete()
			,
			(new IntegerField('EVENT_ID'))
				->configureRequired()
			,
			(new IntegerField('CATEGORY_ID'))
				->configureRequired()
			,
			new IntegerField('THREAD_ID'),
			new TextField('OPTIONS'),
			(new IntegerField('ATTENDEES_COUNT'))
				->configureRequired()
				->configureDefaultValue(0)
			,
			new ReferenceField(
				'EVENT',
				EventTable::class,
				Join::on('this.EVENT_ID', 'ref.ID')
			),
			new ReferenceField(
				'CATEGORY',
				OpenEventCategoryTable::class,
				Join::on('this.CATEGORY_ID', 'ref.ID')
			),
		];
	}
}
