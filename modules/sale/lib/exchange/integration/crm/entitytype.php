<?php
namespace Bitrix\Sale\Exchange\Integration\CRM;

class EntityType
{
	const UNDEFINED = 0;
	const DEAL = 2;
	const CONTACT = 3;
	const COMPANY = 4;
	const ACTIVITY = 6;
	const ORDER = 14;
	const ORDER_SHIPMENT = 16;
	const ORDER_PAYMENT = 17;

	const DEAL_NAME = 'DEAL';
	const CONTACT_NAME = 'CONTACT';
	const COMPANY_NAME = 'COMPANY';
	const ACTIVITY_NAME = 'ACTIVITY';
	const ORDER_NAME = 'ORDER';
	const ORDER_SHIPMENT_NAME = 'ORDER_SHIPMENT';
	const ORDER_PAYMENT_NAME = 'ORDER_PAYMENT';

	public static function isDefined($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		return ($typeId == static::DEAL
			|| $typeId == static::COMPANY
			|| $typeId == static::COMPANY
			|| $typeId == static::ACTIVITY
			|| $typeId == static::ORDER
			|| $typeId == static::ORDER_SHIPMENT
			|| $typeId == static::ORDER_PAYMENT);
	}

	public static function resolveId($name)
	{
		if($name == '')
		{
			return self::UNDEFINED;
		}

		switch($name)
		{
			case self::DEAL_NAME:
				return self::DEAL;
			case self::CONTACT_NAME:
				return self::CONTACT;
			case self::COMPANY_NAME:
				return self::COMPANY;
			case self::ACTIVITY_NAME:
				return self::ACTIVITY;
			case self::ORDER_NAME:
				return self::ORDER;
			case self::ORDER_SHIPMENT_NAME:
				return self::ORDER_SHIPMENT;
			case self::ORDER_PAYMENT_NAME:
				return self::ORDER_PAYMENT;

			default:
				return self::UNDEFINED;
		}
	}

	public static function resolveName($typeId)
	{
		if(!is_numeric($typeId))
		{
			return '';
		}

		$typeId = intval($typeId);
		if($typeId <= 0)
		{
			return '';
		}

		switch($typeId)
		{
			case self::DEAL:
				return self::DEAL_NAME;
			case self::CONTACT:
				return self::CONTACT_NAME;
			case self::COMPANY:
				return self::COMPANY_NAME;
			case self::ACTIVITY:
				return self::ACTIVITY_NAME;
			case self::ORDER:
				return self::ORDER_NAME;
			case self::ORDER_SHIPMENT:
				return self::ORDER_SHIPMENT_NAME;
			case self::ORDER_PAYMENT:
				return self::ORDER_PAYMENT_NAME;

			case self::UNDEFINED:
			default:
				return '';
		}
	}
}