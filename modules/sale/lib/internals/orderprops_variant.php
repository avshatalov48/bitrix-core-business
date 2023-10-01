<?php

namespace Bitrix\Sale\Internals;

use	Bitrix\Main\Entity\DataManager,
	Bitrix\Main\Entity\StringField,
	Bitrix\Main\Entity\IntegerField;

/**
 * Class OrderPropsVariantTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderPropsVariant_Query query()
 * @method static EO_OrderPropsVariant_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderPropsVariant_Result getById($id)
 * @method static EO_OrderPropsVariant_Result getList(array $parameters = [])
 * @method static EO_OrderPropsVariant_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsVariant createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsVariant_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsVariant wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderPropsVariant_Collection wakeUpCollection($rows)
 */
class OrderPropsVariantTable extends DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_order_props_variant';
	}

	public static function getMap()
	{
		return array(
			new IntegerField('ID', array('primary' => true, 'autocomplete' => true)),
			new IntegerField('ORDER_PROPS_ID', array('required' => true)),
			new StringField ('NAME', array('required' => true)),
			new StringField ('VALUE'),
			new IntegerField('SORT', array('default_value' => 100)),
			new StringField ('DESCRIPTION'),
			new StringField ('XML_ID'),
		);
	}

	public static function generateXmlId()
	{
		return uniqid('bx_');
	}
}
