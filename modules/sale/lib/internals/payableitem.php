<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Sale;

/**
 * Class PayableItemTable
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_PayableItem_Query query()
 * @method static EO_PayableItem_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_PayableItem_Result getById($id)
 * @method static EO_PayableItem_Result getList(array $parameters = [])
 * @method static EO_PayableItem_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_PayableItem createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_PayableItem_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_PayableItem wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_PayableItem_Collection wakeUpCollection($rows)
 */
class PayableItemTable extends Main\ORM\Data\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_order_payment_item';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		global $DB;

		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'ENTITY_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'ENTITY_TYPE' => [
				'data_type' => 'enum',
				'required' => true,
				'values' => [
					Sale\Registry::ENTITY_BASKET_ITEM,
					Sale\Registry::ENTITY_SHIPMENT,
				]
			],
			'PAYMENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'DATE_INSERT' => [
				'data_type' => 'datetime'
			],
			'DATE_INSERT_SHORT' => [
				'data_type' => 'datetime',
				'expression' => [
					$DB->datetimeToDateFunction('%s'), 'DATE_INSERT'
				]
			],
			'QUANTITY' => [
				'data_type' => 'float',
				'required' => true,
			],
			'XML_ID' => [
				'data_type' => 'string'
			],
			//
			'PAYMENT' => new Reference(
				'PAYMENT',
				PaymentTable::class,
				Join::on('this.PAYMENT_ID', 'ref.ID')
			),
			'BASKET' => new Reference(
				'BASKET',
				BasketTable::class,
				Join::on('this.ENTITY_ID', 'ref.ID')->where('this.ENTITY_TYPE', Sale\Registry::ENTITY_BASKET_ITEM)
			),
			'SHIPMENT' => new Reference(
				'SHIPMENT',
				ShipmentTable::class,
				Join::on('this.ENTITY_ID', 'ref.ID')->where('this.ENTITY_TYPE', Sale\Registry::ENTITY_SHIPMENT)
			),
		];
	}
}