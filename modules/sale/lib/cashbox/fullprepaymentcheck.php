<?php

namespace Bitrix\Sale\Cashbox;

use Bitrix\Main;
use Bitrix\Sale\Order;

Main\Localization\Loc::loadMessages(__FILE__);

/**
 * Class FullPrepaymentCheck
 * @package Bitrix\Sale\Cashbox
 */
class FullPrepaymentCheck extends Check
{
	/**
	 * @return string
	 */
	public static function getType()
	{
		return 'fullprepayment';
	}

	/**
	 * @throws Main\NotImplementedException
	 * @return string
	 */
	public static function getCalculatedSign()
	{
		return static::CALCULATED_SIGN_INCOME;
	}

	/**
	 * @return string
	 */
	public static function getName()
	{
		return Main\Localization\Loc::getMessage('SALE_CASHBOX_FULLPREPAYMENT_NAME');
	}

	/**
	 * @return string
	 */
	public static function getSupportedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
	}

	/**
	 * @return string
	 */
	public static function getSupportedRelatedEntityType()
	{
		return static::SUPPORTED_ENTITY_TYPE_SHIPMENT;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ArgumentTypeException
	 * @throws Main\LoaderException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	protected function extractDataInternal()
	{
		$result = parent::extractDataInternal();

		foreach ($result['PRODUCTS'] as $i => $item)
		{
			$result['PRODUCTS'][$i]['PAYMENT_OBJECT'] = static::PAYMENT_OBJECT_PAYMENT;
		}

		foreach ($result['DELIVERY'] as $i => $item)
		{
			$result['DELIVERY'][$i]['PAYMENT_OBJECT'] = static::PAYMENT_OBJECT_PAYMENT;
		}

		return $result;
	}

}