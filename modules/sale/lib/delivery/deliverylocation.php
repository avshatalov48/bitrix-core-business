<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Sale\Delivery;

use Bitrix\Sale;

/**
 * Class DeliveryLocationTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DeliveryLocation_Query query()
 * @method static EO_DeliveryLocation_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_DeliveryLocation_Result getById($id)
 * @method static EO_DeliveryLocation_Result getList(array $parameters = [])
 * @method static EO_DeliveryLocation_Entity getEntity()
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocation createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocation_Collection createCollection()
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocation wakeUpObject($row)
 * @method static \Bitrix\Sale\Delivery\EO_DeliveryLocation_Collection wakeUpCollection($rows)
 */
class DeliveryLocationTable extends Sale\Location\Connector
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_sale_delivery2location';
	}

	public static function getLinkField()
	{
		return 'DELIVERY_ID';
	}

	public static function getLocationLinkField()
	{
		return 'LOCATION_CODE';
	}

	public static function getTargetEntityName()
	{
		return 'Bitrix\Sale\Delivery\Services\Table';
	}

	public static function getMap()
	{
		return array(
			
			'DELIVERY_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'primary' => true
			),
			'LOCATION_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'primary' => true
			),
			'LOCATION_TYPE' => array(
				'data_type' => 'string',
				'default_value' => static::DB_LOCATION_FLAG,
				'required' => true,
				'primary' => true
			),

			// virtual
			'LOCATION' => array(
				'data_type' => '\Bitrix\Sale\Location\Location',
				'reference' => array(
					'=this.LOCATION_CODE' => 'ref.CODE',
					'=this.LOCATION_TYPE' => array('?', static::DB_LOCATION_FLAG)
				)
			),
			'GROUP' => array(
				'data_type' => '\Bitrix\Sale\Location\Group',
				'reference' => array(
					'=this.LOCATION_CODE' => 'ref.CODE',
					'=this.LOCATION_TYPE' => array('?', static::DB_GROUP_FLAG)
				)
			),

			'DELIVERY' => array(
				'data_type' => static::getTargetEntityName(),
				'reference' => array(
					'=this.DELIVERY_ID' => 'ref.ID'
				)
			),
		);
	}
}
