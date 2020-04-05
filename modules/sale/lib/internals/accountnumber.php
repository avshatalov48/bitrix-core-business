<?php

namespace Bitrix\Sale\Internals;


use Bitrix\Main;
use Bitrix\Sale;

/**
 * Class AccountNumberGenerator
 * @package Bitrix\Sale\Internals
 */
class AccountNumberGenerator
{
	const ACCOUNT_NUMBER_SEPARATOR = "/";

	/**
	 * @param Sale\OrderBase $order
	 * @return mixed
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\NotImplementedException
	 * @throws Main\SystemException
	 */
	public static function generateForOrder(Sale\OrderBase $order)
	{
		$id = (int)$order->getId();
		if ($id <= 0)
		{
			return false;
		}

		$accountNumber = static::generateCustom($order);
		if ($accountNumber)
		{
			$dbRes = $order::getList([
				'select' => ['ID'],
				'filter' => ['=ACCOUNT_NUMBER' => $accountNumber]
			]);
			if ($dbRes->fetch())
			{
				$accountNumber = null;
			}
		}
		else
		{
			$accountNumber = static::generateBySettings($order);
		}

		if (!$accountNumber) // if no special template is used or error occured
		{
			$accountNumber = static::generateById($order);
		}

		$dbRes = $order::getList([
			'select' => ['ID'],
			'filter' => ['=ACCOUNT_NUMBER' => $accountNumber]
		]);
		if ($dbRes->fetch())
		{
			$accountNumber = static::generateForOrder($order);
		}

		return $accountNumber;
	}

	/**
	 * @param Sale\OrderBase $order
	 * @return null|string|int
	 * @throws Main\ArgumentException
	 * @throws Main\NotImplementedException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function generateBySettings(Sale\OrderBase $order)
	{
		$accountNumber = static::getNextNumber($order);
		if ($accountNumber)
		{
			$dbRes = $order::getList(['filter' => ["=ACCOUNT_NUMBER" => $accountNumber]]);
			if ($dbRes->fetch())
			{
				return null;
			}
		}

		return $accountNumber;
	}

	/**
	 * @param Sale\OrderBase $order
	 * @return int|string
	 * @throws Main\NotImplementedException
	 */
	private static function generateById(Sale\OrderBase $order)
	{
		$accountNumber = $order->getId();
		for ($i = 1; $i <= 10; $i++)
		{
			$dbRes = $order::getList([
				'select' => ['ID'],
				'filter' => ['=ACCOUNT_NUMBER' => $accountNumber]
			]);
			if ($dbRes->fetch())
			{
				$accountNumber = $order->getId()."-".$i;
			}
			else
			{
				break;
			}
		}

		return $accountNumber;
	}

	/**
	 * @param Sale\OrderBase $order
	 * @return mixed
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private static function generateCustom(Sale\OrderBase $order)
	{
		$type = Main\Config\Option::get("sale", "account_number_template", "");

		foreach(GetModuleEvents("sale", "OnBeforeOrderAccountNumberSet", true) as $arEvent)
		{
			$tmpRes = ExecuteModuleEventEx($arEvent, array($order->getId(), $type));
			if ($tmpRes !== false)
			{
				return $tmpRes;
			}
		}

		return null;
	}

	/**
	 * @param CollectableEntity $item
	 * @return null
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function generateForPayment(CollectableEntity $item)
	{
		return static::generate($item);
	}

	/**
	 * @param CollectableEntity $item
	 * @return null
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	public static function generateForShipment(CollectableEntity $item)
	{
		return static::generate($item);
	}

	/**
	 * @param CollectableEntity $item
	 *
	 * @return null
	 * @throws Main\NotSupportedException
	 * @throws Main\ObjectNotFoundException
	 */
	private static function generate(CollectableEntity $item)
	{
		$accountNumber = null;
		/** @var EntityCollection $collection */
		if (!$collection = $item->getCollection())
		{
			throw new Main\ObjectNotFoundException('Entity "Collection" not found');
		}

		if (!method_exists($collection, "getOrder"))
		{
			throw new Main\NotSupportedException();
		}

		/** @var Sale\OrderBase $order */
		if (!$order = $collection->getOrder())
		{
			throw new Main\ObjectNotFoundException('Entity "Order" not found');
		}

		$accountNumber = $order->getField('ACCOUNT_NUMBER').static::ACCOUNT_NUMBER_SEPARATOR;

		$count = 1;
		/** @var CollectableEntity $itemCollection */
		foreach ($collection as $itemCollection)
		{
			if (strval($itemCollection->getField("ACCOUNT_NUMBER")) != "")
			{
				$accountNumberIdList = explode(static::ACCOUNT_NUMBER_SEPARATOR, $itemCollection->getField("ACCOUNT_NUMBER"));

				$itemAccountNumber = trim(end($accountNumberIdList));

				if ($count <= $itemAccountNumber)
					$count = $itemAccountNumber + 1;
			}
		}

		return $accountNumber.$count;
	}

	/**
	 * Generates next account number according to the scheme selected in the module options
	 *
	 * @param Sale\OrderBase $order
	 * @return null|int|string
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private static function getNextNumber(Sale\OrderBase $order)
	{
		$accountNumber = null;

		$numeratorForOrderSettings = Main\Numerator\Numerator::getOneByType($order::getRegistryType());
		$numerator = null;
		if ($numeratorForOrderSettings)
		{
			$numerator = Main\Numerator\Numerator::load(
				$numeratorForOrderSettings['id'],
				[
					'ORDER_ID' => $order->getId()
				]);
		}
		if ($numerator)
		{
			$accountNumber = $numerator->getNext();
		}

		return $accountNumber;
	}
}