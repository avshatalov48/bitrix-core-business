<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class SharingLinkRuleTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> LINK_ID int mandatory
 * <li> SLOT_SIZE int mandatory
 * <li> WEEKDAYS string(32) optional
 * <li> TIME_FROM int optional
 * <li> TIME_TO int optional
 * </ul>
 *
 * @package Bitrix\Calendar
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_SharingLinkRule_Query query()
 * @method static EO_SharingLinkRule_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_SharingLinkRule_Result getById($id)
 * @method static EO_SharingLinkRule_Result getList(array $parameters = [])
 * @method static EO_SharingLinkRule_Entity getEntity()
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkRule createObject($setDefaultValues = true)
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection createCollection()
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkRule wakeUpObject($row)
 * @method static \Bitrix\Calendar\Internals\EO_SharingLinkRule_Collection wakeUpCollection($rows)
 */

class SharingLinkRuleTable extends DataManager
{
	use DeleteByFilterTrait;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_calendar_sharing_link_rule';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			new IntegerField(
				'LINK_ID',
				[
					'required' => true,
				]
			),
			new IntegerField(
				'SLOT_SIZE',
				[
					'required' => true,
				]
			),
			new StringField(
				'WEEKDAYS',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 32),
						];
					},
				]
			),
			new IntegerField('TIME_FROM'),
			new IntegerField('TIME_TO'),
		];
	}
}