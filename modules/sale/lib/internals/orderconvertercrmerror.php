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