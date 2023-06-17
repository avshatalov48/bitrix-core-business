<?php
namespace Bitrix\Sale\Services\Base;

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\NotImplementedException;
use Bitrix\Sale;
use Bitrix\Sale\TradeBindingEntity;

Loc::loadMessages(__FILE__);

/**
 * Class TradeBindingRestriction
 * @package Bitrix\Sale\Services\Base
 */
abstract class TradeBindingRestriction extends Restriction
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

	public static function getOnApplyErrorMessage(): string
	{
		return Loc::getMessage('SALE_SRV_RSTR_BY_TRADE_BINDING_ON_APPLY_ERROR_MSG');
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
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function getTradePlatformList()
	{
		Loader::includeModule('crm');

		$result = [];

		$dbRes = Sale\TradingPlatformTable::getList([
			'select' => ['CODE', 'CLASS'],
			'filter' => ['=ACTIVE' => 'Y'],
			'cache' => ['ttl' => 36000]
		]);
		while ($data = $dbRes->fetch())
		{
			$platformClassName = (string)$data['CLASS'];
			if (class_exists($platformClassName))
			{
				/** @var Sale\TradingPlatform\Platform $platformClassName */
				$platform = $platformClassName::getInstanceByCode($data['CODE']);
				if ($platform instanceof Sale\TradingPlatform\IRestriction)
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

		/** @var Sale\Order $order */
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
	 * @throws NotImplementedException
	 */
	protected static function getOrder(Sale\Internals\Entity $entity)
	{
		throw new NotImplementedException('Method '.__METHOD__.' must be overload');
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
		return (bool)static::getTradePlatformList();
	}

} 