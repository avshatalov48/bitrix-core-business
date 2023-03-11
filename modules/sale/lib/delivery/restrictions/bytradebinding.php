<?php
namespace Bitrix\Sale\Delivery\Restrictions;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\TradeBindingEntity;

Loc::loadMessages(__FILE__);

/**
 * Class TradeBinding
 * @package Bitrix\Sale\Delivery\Restrictions
 */
class ByTradeBinding extends Base
{
	/**
	 * @return string
	 */
	public static function getClassTitle()
	{
		return Loc::getMessage('SALE_SRV_RSTR_BY_TRADE_BINDING_NAME');
	}

	/**
	 * @return string
	 */
	public static function getClassDescription()
	{
		return Loc::getMessage('SALE_SRV_RSTR_BY_TRADE_BINDING_DESC');
	}

	/**
	 * @param int $entityId
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getParamsStructure($entityId = 0)
	{
		$result =  array(
			"TRADE_BINDING" => array(
				"TYPE" => "ENUM",
				'MULTIPLE' => 'Y',
				"LABEL" => Loc::getMessage("SALE_SRV_RSTR_BY_TRADE_BINDING_LIST"),
				"OPTIONS" => self::getTradePlatformList()
			)
		);

		return $result;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getTradePlatformList()
	{
		$result = [];

		$dbRes = Sale\TradingPlatformTable::getList(['select' => ['CODE', 'CLASS']]);
		while ($data = $dbRes->fetch())
		{
			/** @var Sale\TradingPlatform\Platform $platformClassName */
			$platformClassName = $data['CLASS'];

			if (!empty($platformClassName) && class_exists($platformClassName))
			{
				$platform = $platformClassName::getInstanceByCode($data['CODE']);
				if ($platform
					&& $platform instanceof Sale\TradingPlatform\Landing\Landing
				)
				{
					$result[$platform->getId()] = $platform->getRealName();
				}
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Internals\Entity $entity
	 * @return array|bool|mixed
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentTypeException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function extractParams(Sale\Internals\Entity $entity)
	{
		$result = [];

		$order = static::getOrder($entity);

		if ($order === null)
		{
			return $result;
		}

		$collection = $order->getTradeBindingCollection();

		/** @var TradeBindingEntity $entity */
		foreach ($collection as $entity)
		{
			$tradeBinding = $entity->getTradePlatform();
			if (
				$tradeBinding
				&& !in_array($tradeBinding->getId(), $result)
			)
			{
				$result[] = $tradeBinding->getId();
			}
		}

		return $result;
	}

	/**
	 * @param Sale\Internals\Entity $entity
	 * @return Sale\Order|null
	 */
	protected static function getOrder(Sale\Internals\Entity $entity)
	{
		if ($entity instanceof Sale\Shipment)
		{
			/** @var \Bitrix\Sale\ShipmentCollection $collection */
			$collection = $entity->getCollection();

			/** @var \Bitrix\Sale\Order $order */
			return $collection->getOrder();
		}
		elseif ($entity instanceof Sale\Order)
		{
			/** @var \Bitrix\Sale\Order $order */
			return $entity;
		}

		return null;
	}

	/**
	 * @param $params
	 * @param array $restrictionParams
	 * @param int $serviceId
	 * @return bool
	 */
	public static function check($params, array $restrictionParams, $serviceId = 0)
	{
		if (is_array($restrictionParams) && isset($restrictionParams['TRADE_BINDING']))
		{
			$diff = array_diff($params, $restrictionParams['TRADE_BINDING']);
			return empty($diff);
		}

		return true;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function isAvailable()
	{
		return count(static::getTradePlatformList()) > 0;
	}

}
