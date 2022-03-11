<?php

namespace Bitrix\Conversion\Internals;

use Bitrix\Main\Entity;

/**
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ContextAttribute_Query query()
 * @method static EO_ContextAttribute_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ContextAttribute_Result getById($id)
 * @method static EO_ContextAttribute_Result getList(array $parameters = array())
 * @method static EO_ContextAttribute_Entity getEntity()
 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute createObject($setDefaultValues = true)
 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection createCollection()
 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute wakeUpObject($row)
 * @method static \Bitrix\Conversion\Internals\EO_ContextAttribute_Collection wakeUpCollection($rows)
 */
class ContextAttributeTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_conv_context_attribute';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('CONTEXT_ID', array('primary' => true)),
			new Entity\StringField ('NAME'      , array('primary' => true, 'size' => 30)),
			new Entity\StringField ('VALUE'     , array('primary' => true, 'size' => 30)),

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
