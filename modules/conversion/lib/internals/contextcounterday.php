<?php

namespace Bitrix\Conversion\Internals;

use Bitrix\Main\Entity;

/**
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ContextCounterDay_Query query()
 * @method static EO_ContextCounterDay_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ContextCounterDay_Result getById($id)
 * @method static EO_ContextCounterDay_Result getList(array $parameters = array())
 * @method static EO_ContextCounterDay_Entity getEntity()
 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay createObject($setDefaultValues = true)
 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection createCollection()
 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay wakeUpObject($row)
 * @method static \Bitrix\Conversion\Internals\EO_ContextCounterDay_Collection wakeUpCollection($rows)
 */
class ContextCounterDayTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_conv_context_counter_day';
	}

	public static function getMap()
	{
		return array(
			new Entity\DateField   ('DAY'       , array('primary'  => true)),
			new Entity\IntegerField('CONTEXT_ID', array('primary'  => true)),
			new Entity\StringField ('NAME'      , array('primary'  => true, 'size' => 30)),
			new Entity\FloatField  ('VALUE'     , array('required' => true)),

			new Entity\ReferenceField('CONTEXT', 'ContextTable',
				array('=this.CONTEXT_ID' => 'ref.ID'),
				array('join_type' => 'INNER')
			),
		);
	}

	public static function getFilePath()
	{
		return __FILE__;
	}
}
