<?php

namespace Bitrix\Conversion\Internals;

use Bitrix\Main\Entity;

/**
 * @internal
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ContextEntityItem_Query query()
 * @method static EO_ContextEntityItem_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ContextEntityItem_Result getById($id)
 * @method static EO_ContextEntityItem_Result getList(array $parameters = array())
 * @method static EO_ContextEntityItem_Entity getEntity()
 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem createObject($setDefaultValues = true)
 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection createCollection()
 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem wakeUpObject($row)
 * @method static \Bitrix\Conversion\Internals\EO_ContextEntityItem_Collection wakeUpCollection($rows)
 */
class ContextEntityItemTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_conv_context_entity_item';
	}

	public static function getMap()
	{
		return array(
			new Entity\IntegerField('CONTEXT_ID', array('primary' => true)),
			new Entity\StringField ('ENTITY'    , array('primary' => true, 'size' => 30)),
			new Entity\StringField ('ITEM'      , array('primary' => true, 'size' => 30)),

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
