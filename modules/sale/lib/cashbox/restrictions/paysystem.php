<?php
namespace Bitrix\Sale\Cashbox\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\Entity;
use Bitrix\Sale\Payment;
use Bitrix\Sale;
use Bitrix\Sale\Services\Base\Restriction;

Loc::loadMessages(__FILE__);

/**
 * Class PaySystem
 * @package Bitrix\Sale\Cashbox\Restrictions
 */
class PaySystem extends Restriction
{
	public static $easeSort = 200;
	protected static $preparedData = array();

	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage("SALE_CASHBOX_RSTR_BY_PS_TITLE");
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage("SALE_CASHBOX_RSTR_BY_PS_DESC");
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (is_array($restrictionParams) && isset($restrictionParams['PAY_SYSTEMS']))
		{
			$diff = array_diff($params, $restrictionParams['PAY_SYSTEMS']);
			return empty($diff);
		}

		return true;
	}

	/**
	 * @param Entity $entity
	 * @return array
	 */
	protected static function extractParams(Entity $entity)
	{
		$result = array();

		if ($entity instanceof Sale\Order)
		{
			$collection = $entity->getPaymentCollection();
			if ($collection)
			{
				/** @var Payment $item */
				foreach ($collection as $item)
					$result[] = $item->getPaymentSystemId();
			}
		}
		elseif ($entity instanceof Sale\Shipment)
		{
			/** @var Sale\ShipmentCollection $shipmentCollection */
			$shipmentCollection = $entity->getCollection();
			if (!$shipmentCollection)
				return $result;

			$order = $shipmentCollection->getOrder();
			if (!$order)
				return $result;

			$paymentCollection = $order->getPaymentCollection();
			if (!$paymentCollection)
				return $result;

			/** @var Payment $item */
			foreach ($paymentCollection as $item)
				$result[] = $item->getPaymentSystemId();
		}
		elseif ($entity instanceof Payment)
		{
			$result[] = $entity->getPaymentSystemId();
		}

		return $result;
	}

	/**
	 * @return array|null
	 */
	protected static function getPaySystemsList()
	{
		static $result = null;

		if($result !== null)
			return $result;

		$result = array();

		$dbResultList = Sale\PaySystem\Manager::getList(array(
			'select' => array("ID", "NAME", "ACTIVE"),
			'filter' => array("ACTIVE" => "Y"),
			'order' => array("SORT"=>"ASC", "NAME"=>"ASC")
		));

		while ($arPayType = $dbResultList->fetch())
			$result[$arPayType["ID"]] = $arPayType["NAME"];

		return $result;
	}

	/**
	 * @param int $entityId
	 * @return array
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$result =  array(
			"PAY_SYSTEMS" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_CASHBOX_RSTR_BY_PS"),
				"OPTIONS" => self::getPaySystemsList()
			)
		);

		return $result;
	}

	/**
	 * @param int $mode - RestrictionManager::MODE_CLIENT | RestrictionManager::MODE_MANAGER
	 * @return int
	 */
	public static function getSeverity($mode)
	{
		return Manager::SEVERITY_STRICT;
	}

}