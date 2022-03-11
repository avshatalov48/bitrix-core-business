<?php

namespace Bitrix\Conversion\Internals;

use Bitrix\Main\Entity;

/**
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Context_Query query()
 * @method static EO_Context_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Context_Result getById($id)
 * @method static EO_Context_Result getList(array $parameters = array())
 * @method static EO_Context_Entity getEntity()
 * @method static \Bitrix\Conversion\Internals\EO_Context createObject($setDefaultValues = true)
 * @method static \Bitrix\Conversion\Internals\EO_Context_Collection createCollection()
 * @method static \Bitrix\Conversion\Internals\EO_Context wakeUpObject($row)
 * @method static \Bitrix\Conversion\Internals\EO_Context_Collection wakeUpCollection($rows)
 */
class ContextTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_conv_context';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('ID'      , array('primary'  => true, 'autocomplete' => true)),
			new Entity\StringField ('SNAPSHOT', array('required' => true, 'size' => 64)),
		);
	}

	public static function getFilePath()
	{
		return __FILE__;
	}
}
