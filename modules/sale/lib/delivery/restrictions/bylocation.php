<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\CollectableEntity;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Location\GroupLocationTable;
use Bitrix\Sale\Location\LocationTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\Shipment;

Loc::loadMessages(__FILE__);

/**
 * Class ByLocation
 * Restricts delivery by location(s)
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByLocation extends Base
{
	public static $easeSort = 200;

	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_LOCATION_NAME");
	}

	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_DLVR_RSTR_BY_LOCATION_DESCRIPT");
	}

	protected static function getD2LClass()
	{
		return '\Bitrix\Sale\Delivery\DeliveryLocationTable';
	}

	/**
	 * This function should accept only location CODE, not ID, being a part of modern API
	 * @param string $locationCode
	 * @param array $restrictionParams
	 * @param int $deliveryId
	 * @return bool
	 */
	public static function check($locationCode, array $restrictionParams, $deliveryId = 0)
	{
		if(intval($deliveryId) <= 0)
			return true;

		if(strlen($locationCode) <= 0)
			return false;

		try
		{
			$class = static::getD2LClass();
			return $class::checkConnectionExists(
				intval($deliveryId),
				$locationCode,
				array(
					'LOCATION_LINK_TYPE' => 'AUTO'
				)
			);
		}
		catch(\Bitrix\Sale\Location\Tree\NodeNotFoundException $e)
		{
			return false;
		}
	}

	protected static function extractParams(Entity $entity)
	{
		if ($entity instanceof CollectableEntity)
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $entity->getCollection()->getOrder();
		}
		elseif ($entity instanceof Order)
		{
			/** @var \Bitrix\Sale\Order $order */
			$order = $entity;
		}

		if (!$order)
			return '';


		if(!$props = $order->getPropertyCollection())
			return '';

		if(!$locationProp = $props->getDeliveryLocation())
			return '';

		if(!$locationCode = $locationProp->getValue())
			return '';

		return $locationCode;
	}

	protected static function prepareParamsForSaving(array $params = array(), $deliveryId = 0)
	{
		$class = static::getD2LClass();
		if($deliveryId > 0)
		{
			$arLocation = array();

			if(!!\CSaleLocation::isLocationProEnabled())
			{
				if(strlen($params["LOCATION"][$class::DB_LOCATION_FLAG]))
					$LOCATION1 = explode(':', $params["LOCATION"][$class::DB_LOCATION_FLAG]);

				if(strlen($params["LOCATION"][$class::DB_GROUP_FLAG]))
					$LOCATION2 = explode(':', $params["LOCATION"][$class::DB_GROUP_FLAG]);
			}

			if (isset($LOCATION1) && is_array($LOCATION1) && count($LOCATION1) > 0)
			{
				$arLocation[$class::DB_LOCATION_FLAG] = array();
				$locationCount = count($LOCATION1);

				for ($i = 0; $i<$locationCount; $i++)
					if (strlen($LOCATION1[$i]))
						$arLocation[$class::DB_LOCATION_FLAG][] = $LOCATION1[$i];
			}

			if (isset($LOCATION2) && is_array($LOCATION2) && count($LOCATION2) > 0)
			{
				$arLocation[$class::DB_GROUP_FLAG] = array();
				$locationCount = count($LOCATION2);

				for ($i = 0; $i<$locationCount; $i++)
					if (strlen($LOCATION2[$i]))
						$arLocation[$class::DB_GROUP_FLAG][] = $LOCATION2[$i];

			}

			$class::resetMultipleForOwner($deliveryId, $arLocation);
		}

		return array();
	}

	public static function getParamsStructure($deliveryId = 0)
	{

		$result =  array(
			"LOCATION" => array(
				"TYPE" => "LOCATION_MULTI"
				//'LABEL' => Loc::getMessage("SALE_DLVR_RSTR_BY_LOCATION_LOC"),
			)
		);

		if($deliveryId > 0 )
			$result["LOCATION"]["DELIVERY_ID"] = $deliveryId;

		return $result;
	}

	public static function save(array $fields, $restrictionId = 0)
	{
		$fields["PARAMS"] = self::prepareParamsForSaving($fields["PARAMS"], $fields["SERVICE_ID"]);
		return parent::save($fields, $restrictionId);
	}

	public static function delete($restrictionId, $deliveryId = 0)
	{
		$class = static::getD2LClass();
		$class::resetMultipleForOwner($deliveryId);
		return parent::delete($restrictionId);
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
		if(strlen($shpLocCode) < 0)
			return array_keys($restrictionFields);

		$res = LocationTable::getList(array(
			'filter' => array('=CODE' => $shpLocCode),
			'select' => array('CODE', 'LEFT_MARGIN', 'RIGHT_MARGIN')
		));

		//if location doesn't exists
		if(!$shpLocParams = $res->fetch())
			return array_keys($restrictionFields);

		$result = array();
		$srvLocCodesCompat = static::getLocationsCompat($restrictionFields, $shpLocParams['LEFT_MARGIN'], $shpLocParams['RIGHT_MARGIN']);

		foreach($srvLocCodesCompat as $locCode => $deliveries)
			foreach($deliveries as $deliveryId)
				if(!in_array($deliveryId, $result))
					$result[] = $deliveryId;

		return $result;
	}

	/**
	 * @param array $restrictionFields
	 * @param $leftMargin
	 * @param $rightMargin
	 * @return array
	 */
	protected static function getLocationsCompat(array $restrictionFields, $leftMargin, $rightMargin)
	{
		$result = array();
		$groups = array();
		$class = static::getD2LClass();

		$res = $class::getList(array(
			'filter' => array(
				'=DELIVERY_ID' => array_keys($restrictionFields),
				array(
					'LOGIC' => 'OR',
					array(
						'LOGIC' => 'AND',
						'=LOCATION_TYPE' => $class::DB_LOCATION_FLAG,
						'<=LOCATION.LEFT_MARGIN' => $leftMargin,
						'>=LOCATION.RIGHT_MARGIN' => $rightMargin
					),
					array(
						'LOGIC' => 'AND',
						'=LOCATION_TYPE' => $class::DB_GROUP_FLAG
					)
				)
			)
		));

		while($d2l = $res->fetch())
		{
			if($d2l['LOCATION_TYPE'] == $class::DB_LOCATION_FLAG)
			{
				if(!is_array($result[$d2l['LOCATION_CODE']]))
					$result[$d2l['LOCATION_CODE']] = array();

				if(!in_array($d2l['DELIVERY_ID'] ,$result[$d2l['LOCATION_CODE']]))
					$result[$d2l['LOCATION_CODE']][] = $d2l['DELIVERY_ID'];
			}
			elseif($d2l['LOCATION_TYPE'] == $class::DB_GROUP_FLAG)
			{
				if(!is_array($groups[$d2l['LOCATION_CODE']]))
					$groups[$d2l['LOCATION_CODE']] = array();

				if(!in_array($d2l['DELIVERY_ID'] ,$groups[$d2l['LOCATION_CODE']]))
					$groups[$d2l['LOCATION_CODE']][] = $d2l['DELIVERY_ID'];
			}
		}

		//groups
		if(!empty($groups))
		{
			$res = GroupLocationTable::getList(array(
				'filter' => array(
					'=GROUP.CODE' => array_keys($groups),
					'<=LOCATION.LEFT_MARGIN' => $leftMargin,
					'>=LOCATION.RIGHT_MARGIN' => $rightMargin
				),
				'select' => array(
					'LOCATION_ID', 'LOCATION_GROUP_ID',
					'LOCATION_CODE' => 'LOCATION.CODE',
					'GROUP_CODE' => 'GROUP.CODE'
				)
			));

			while($loc = $res->fetch())
			{
				if(!is_array($result[$loc['LOCATION_CODE']]))
					$result[$loc['LOCATION_CODE']] = array();

				foreach($groups[$loc['GROUP_CODE']] as $srvId)
					if(!in_array($srvId, $result[$loc['LOCATION_CODE']]))
						$result[$loc['LOCATION_CODE']][] = $srvId;
			}
		}

		return $result;
	}
}