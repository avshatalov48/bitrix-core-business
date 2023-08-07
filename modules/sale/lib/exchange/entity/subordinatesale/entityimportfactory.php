<?php
namespace Bitrix\Sale\Exchange\Entity\SubordinateSale;

use Bitrix\Main;
use Bitrix\Sale\Exchange;

/**
 * Class EntityImportFactory
 * @package Bitrix\Sale\Exchange\Entity
 * @internal
 */
class EntityImportFactory
{
	/**
	 * @param $entityTypeID
	 * @param null $parentEntityContext
	 * @return Exchange\ImportBase
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotSupportedException
	 */
	public static function create($entityTypeID, $parentEntityContext = null)
	{
		if(!is_int($entityTypeID))
		{
			$entityTypeID = (int)$entityTypeID;
		}

		if(!Exchange\EntityType::IsDefined($entityTypeID))
		{
			throw new Main\ArgumentOutOfRangeException('Is not defined', Exchange\EntityType::FIRST, Exchange\EntityType::LAST);
		}

		if($entityTypeID === Exchange\EntityType::ORDER)
		{
			return new Exchange\Entity\SubordinateSale\Order(null);
		}
		elseif($entityTypeID === Exchange\EntityType::SHIPMENT)
		{
			return new Exchange\Entity\SubordinateSale\Shipment($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::PAYMENT_CASH)
		{
			return new Exchange\Entity\PaymentCashImport($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::PAYMENT_CASH_LESS)
		{
			return new Exchange\Entity\PaymentCashLessImport($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::PAYMENT_CARD_TRANSACTION)
		{
			return new Exchange\Entity\PaymentCardImport($parentEntityContext);
		}
		elseif($entityTypeID === Exchange\EntityType::USER_PROFILE)
		{
			return new Exchange\Entity\UserProfileImport();
		}
		else
		{
			throw new Main\NotSupportedException("Entity type: '".Exchange\EntityType::ResolveName($entityTypeID)."' is not supported in current context");
		}
	}
}