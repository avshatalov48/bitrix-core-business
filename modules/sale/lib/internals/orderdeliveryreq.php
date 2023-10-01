<?php
namespace Bitrix\Sale\Internals;


use Bitrix\Main;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class OrderDeliveryReqTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ORDER_ID int mandatory
 * <li> DELIVERY_LOCATION string(50) optional
 * <li> DATE_REQUEST datetime optional
 * <li> PARAMS string optional
 * </ul>
 *
 * @package Bitrix\Sale\Delivery
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_OrderDeliveryReq_Query query()
 * @method static EO_OrderDeliveryReq_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_OrderDeliveryReq_Result getById($id)
 * @method static EO_OrderDeliveryReq_Result getList(array $parameters = [])
 * @method static EO_OrderDeliveryReq_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_OrderDeliveryReq createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_OrderDeliveryReq_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_OrderDeliveryReq wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_OrderDeliveryReq_Collection wakeUpCollection($rows)
 */

class OrderDeliveryReqTable extends Main\Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sale_order_delivery_req';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Main\Localization\Loc::getMessage('ORDERDELIVERY_ENTITY_ID_FIELD'),
			),
			'ORDER_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Main\Localization\Loc::getMessage('ORDERDELIVERY_ENTITY_ORDER_ID_FIELD'),
			),
			'DELIVERY_LOCATION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateLocation'),
				'title' => Main\Localization\Loc::getMessage('ORDERDELIVERY_ENTITY_HID_FIELD'),
			),
			'DATE_REQUEST' => array(
				'data_type' => 'datetime',
				'title' => Main\Localization\Loc::getMessage('ORDERDELIVERY_ENTITY_DATE_REQUEST_FIELD'),
			),
			'PARAMS' => array(
				'data_type' => 'text',
				'serialized' => true,
				'title' => Main\Localization\Loc::getMessage('ORDERDELIVERY_ENTITY_PARAMS_FIELD'),
			),
			'SHIPMENT_ID' => array(
				'data_type' => 'integer',
				'title' => Main\Localization\Loc::getMessage('ORDERDELIVERY_ENTITY_SHIPMENT_ID_FIELD'),
			)
		);
	}

	public static function validateLocation()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
}