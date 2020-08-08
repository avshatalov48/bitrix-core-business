<?php


namespace Bitrix\Sale\Helpers\Admin\Blocks;


class BlockType
{
	const UNDEFINED = 0;

	const SHIPMENT_STATUS = 1;
	const SHIPMENT_BASKET = 2;
	const FINANCE_INFO = 3;
	const ADDITIONAL = 4;
	const SHIPMENT = 5;
	const PAYMENT = 6;
	const STATUS = 7;
	const BASKET = 8;
	const BUYER = 9;
	const INFO = 10;
	const MARKER = 11;
	const ANALYSIS = 12;
	const DELIVERY = 13;
	const DISCOUNT = 14;

	const SHIPMENT_BASKET_NAME = "goodsList";
	const SHIPMENT_STATUS_NAME = "shipmentStatus";
	const FINANCE_INFO_NAME = "financeinfo";
	const ADDITIONAL_NAME = "additional";
	const SHIPMENT_NAME = "shipment";
	const PAYMENT_NAME = "payment";
	const STATUS_NAME = "statusorder";
	const BASKET_NAME = "basket";
	const BUYER_NAME = "buyer";
	const INFO_NAME = "";
	const MARKER_NAME = "";
	const ANALYSIS_NAME = "analysis";
	const DELIVERY_NAME = "delivery";
	const DISCOUNT_NAME = "discount";

	const FIRST_TYPE = 1;
	const LAST_TYPE = 14;

	public static function isDefined($typeId)
	{
		if(!is_int($typeId))
		{
			$typeId = (int)$typeId;
		}

		return $typeId >= self::FIRST_TYPE && $typeId <= self::LAST_TYPE;
	}

	public static function resolveId($name)
	{
		if($name == '')
		{
			return self::UNDEFINED;
		}

		switch($name)
		{
			case self::SHIPMENT_STATUS_NAME:
				return self::SHIPMENT_STATUS;
			case self::SHIPMENT_BASKET_NAME:
				return self::SHIPMENT_BASKET;
			case self::FINANCE_INFO_NAME:
				return self::FINANCE_INFO;
			case self::ADDITIONAL_NAME:
				return self::ADDITIONAL;
			case self::SHIPMENT_NAME:
				return self::SHIPMENT;
			case self::PAYMENT_NAME:
				return self::PAYMENT;
			case self::STATUS_NAME:
				return self::STATUS;
			case self::BASKET_NAME:
				return self::BASKET;
			case self::BUYER_NAME:
				return self::BUYER;
			case self::INFO_NAME:
				return self::INFO;
			case self::MARKER_NAME:
				return self::MARKER;
			case self::ANALYSIS_NAME:
				return self::ANALYSIS;
			case self::DELIVERY_NAME:
				return self::DELIVERY;
			case self::DISCOUNT_NAME:
				return self::DISCOUNT;

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
			case self::SHIPMENT_STATUS:
				return self::SHIPMENT_STATUS_NAME;
			case self::SHIPMENT_BASKET:
				return self::SHIPMENT_BASKET_NAME;
			case self::FINANCE_INFO:
				return self::FINANCE_INFO_NAME;
			case self::ADDITIONAL:
				return self::ADDITIONAL_NAME;
			case self::SHIPMENT:
				return self::SHIPMENT_NAME;
			case self::PAYMENT:
				return self::PAYMENT_NAME;
			case self::STATUS:
				return self::STATUS_NAME;
			case self::BASKET:
				return self::BASKET_NAME;
			case self::BUYER:
				return self::BUYER_NAME;
			case self::INFO :
				return self::INFO_NAME;
			case self::MARKER :
				return self::MARKER_NAME;
			case self::ANALYSIS :
				return self::ANALYSIS_NAME;
			case self::DELIVERY :
				return self::DELIVERY_NAME;
			case self::DISCOUNT:
				return self::DISCOUNT_NAME;

			case self::UNDEFINED:
			default:
				return '';
		}
	}
}