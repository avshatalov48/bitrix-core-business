<?php
namespace Bitrix\Sale\Delivery\Requests;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ShipmentTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> SHIPMENT_ID int mandatory *
 * <li> REQUEST_ID int optional
 * <li> EXTERNAL_ID int optional
 * <li> ERROR_DESCRIPTION string
 * </ul>
 *
 * @package Bitrix\Sale\Delivery\Requests
 **/

class ShipmentTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_delivery_req_shp';
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
				'title' => Loc::getMessage('SALE_DLVR_REQ_SHP_TBL_ID_FIELD'),
			),
			'SHIPMENT_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('SALE_DLVR_REQ_SHP_TBL_SHIPMENT_ID_FIELD'),
			),
			'REQUEST_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('SALE_DLVR_REQ_SHP_TBL_REQUEST_ID_FIELD'),
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateType'),
				'title' => Loc::getMessage('SALE_DLVR_REQ_SHP_TBL_EXTERNAL_ID_FIELD'),
			),
			'ERROR_DESCRIPTION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateErrorDescription'),
				'title' => Loc::getMessage('SALE_DLVR_REQ_SHP_TBL_ERROR_DESCRIPTION_FIELD'),
			),
			'SHIPMENT' => array(
				'data_type' => '\Bitrix\Sale\Internals\ShipmentTable',
				'reference' => array('=this.SHIPMENT_ID' => 'ref.ID'),
			),
			'REQUEST' => array(
				'data_type' => '\Bitrix\Sale\Delivery\Requests\RequestTable',
				'reference' => array('=this.REQUEST_ID' => 'ref.ID'),
			)
		);
	}

	/**
	 * @return array
	 */
	public static function validateType()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * @return array
	 */
	public static function validateErrorDescription()
	{
		return array(
			new Entity\Validator\Length(null, 2048),
		);
	}

	public static function setShipment(array $fields)
	{
		$res = self::getList(array('filter' => array('=SHIPMENT_ID' => $fields['SHIPMENT_ID'])));

		if($row = $res->fetch())
			$result = self::update($row['ID'], $fields);
		else
			$result = self::add($fields);

		return $result;
	}
}
