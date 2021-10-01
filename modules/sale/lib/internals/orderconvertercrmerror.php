<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class OrderConverterCrmErrorTable
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ORDER_ID int mandatory
 * <li> ERROR string(255) optional
 * </ul>
 *
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderConverterCrmError_Query query()
 * @method static EO_OrderConverterCrmError_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_OrderConverterCrmError_Result getById($id)
 * @method static EO_OrderConverterCrmError_Result getList(array $parameters = array())
 * @method static EO_OrderConverterCrmError_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderConverterCrmError createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderConverterCrmError_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderConverterCrmError wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderConverterCrmError_Collection wakeUpCollection($rows)
 */
class OrderConverterCrmErrorTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_order_converter_crm_error';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('ORDER_CONVERTER_ERROR_ENTITY_ID_FIELD'),
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('ORDER_CONVERTER_ERROR_ENTITY_ORDER_ID_FIELD'),
			),
			'ERROR' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('ORDER_CONVERTER_ERROR_ENTITY_ERROR_FIELD'),
			),
		);
	}
}