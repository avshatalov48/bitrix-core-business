<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Class ExcludeLocation
 * Exclude delivery by location(s)
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ExcludeLocation extends ByLocation
{
	public static $easeSort = 200;


	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_EX_LOCATION_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_EX_LOCATION_DESCRIPT");
	}

	protected static function getD2LClass()
	{
		return '\Bitrix\Sale\Delivery\DeliveryLocationExcludeTable';
	}

	/**
	 * This function should accept only location CODE, not ID, being a part of modern API
	 * @inheritdoc
	 */
	public static function check($locationCode, array $restrictionParams, $deliveryId = 0)
	{
		return !parent::check($locationCode, $restrictionParams, $deliveryId);
	}

	public static function getParamsStructure($deliveryId = 0)
	{
		$result =  array(
			"LOCATION" => array(
				"TYPE" => "LOCATION_MULTI_EXCLUDE"
			)
		);

		if($deliveryId > 0 )
		{
			$result["LOCATION"]["DELIVERY_ID"] = $deliveryId;
		}

		return $result;
	}

	/**
	 * @param Shipment $shipment
	 * @param array $restrictionFields
	 * @return array
	 */
	public static function filterServicesArray(Shipment $shipment, array $restrictionFields)
	{
		if(empty($restrictionFields))
			return array();

		$shpLocCode = self::extractParams($shipment);

		//if location not defined in shipment
		if($shpLocCode === '')
			return array_keys($restrictionFields);

		$res = LocationTable::getList(array(
			'filter' => array('=CODE' => $shpLocCode),
			'select' => array('CODE', 'LEFT_MARGIN', 'RIGHT_MARGIN')
		));

		//if location doesn't exists
		if(!$shpLocParams = $res->fetch())
			return array_keys($restrictionFields);

		$srvLocCodesCompat = static::getLocationsCompat($restrictionFields, $shpLocParams['LEFT_MARGIN'], $shpLocParams['RIGHT_MARGIN']);

		foreach($srvLocCodesCompat as $locCode => $deliveries)
		{
			foreach($deliveries as $deliveryId)
			{
				if(isset($restrictionFields[$deliveryId]))
				{
					unset($restrictionFields[$deliveryId]);
				}
			}
		}

		return array_keys($restrictionFields);
	}
}