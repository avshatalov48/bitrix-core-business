<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;


/**
 * Class OrderChangeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderChange_Query query()
 * @method static EO_OrderChange_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderChange_Result getById($id)
 * @method static EO_OrderChange_Result getList(array $parameters = [])
 * @method static EO_OrderChange_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderChange createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderChange_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderChange wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderChange_Collection wakeUpCollection($rows)
 */
class OrderChangeTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_order_change';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required'   => true
			),
			'TYPE' => array(
				'data_type' => 'string',
				'required'   => true
			),
			'DATA'  => array(
				'data_type' => 'string'
			),
			'DATE_CREATE'  => array(
				'data_type' => 'datetime',
				'default_value' => new Main\Type\DateTime(),
				'required'   => true
			),
			'DATE_MODIFY'  => array(
				'data_type' => 'datetime',
				'default_value' => new Main\Type\DateTime(),
				'required'   => true
			),
			'USER_ID'  => array(
				'data_type' => 'integer',
			),
			'ENTITY'  => array(
				'data_type' => 'string'
			),
			'ENTITY_ID'  => array(
				'data_type' => 'integer'
			),
		);
	}
}