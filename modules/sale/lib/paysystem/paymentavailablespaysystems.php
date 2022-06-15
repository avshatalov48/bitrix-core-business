<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use Bitrix\Sale\Internals\PaymentPaySystemAvailableTable;
use Bitrix\Sale\Internals\PaymentTable;
use Bitrix\Sale\Internals\PaySystemActionTable;

Loc::loadMessages(__FILE__);

/**
 * A service for working with linking specific pay systems to payments.
 * 
 * Binding allows you to limit the client to the selected list of pay systems that he can use for payment.
 */
class PaymentAvailablesPaySystems
{
	/**
	 * Clearing bindings
	 *
	 * @param int $paymentId
	 * @return Result
	 */
	public static function clearBindings(int $paymentId)
	{
		return self::setBindings($paymentId, []);
	}
	
	/**
	 * Set available pay systems for payment.
	 * 
	 * Bindings added to this payment earlier will be deleted. 
	 *
	 * @param int $paymentId
	 * @param int[] $paySystemIds if the array is empty, the bindings will be cleared
	 * @return Result
	 */
	public static function setBindings(int $paymentId, array $paySystemIds)
	{
		$result = new Result();
		
		// check existing payment
		$existPayment = PaymentTable::getCount([
			'=ID' => $paymentId,
		]) > 0;
		if (!$existPayment)
		{
			$result->addError(
				new Error(Loc::getMessage('SALE_PS_PAYMENT_AVAILABLES_PAYSYSTEMS_NOT_FOUND_PAYMENT'))
			);
			return $result;
		}
		
		// grouping existing items by added & deleted
		$existPaySystemIds = [];
		$existRows = PaymentPaySystemAvailableTable::getList([
			'select' => [
				'ID',
				'PAY_SYSTEM_ID',
			],
			'filter' => [
				'=PAYMENT_ID' => $paymentId,
			],
		]);
		
		$deletedIds = [];
		$paySystemIds = array_map('intval', $paySystemIds);
		foreach ($existRows as $row)
		{
			$rowPaySystemId = (int)$row['PAY_SYSTEM_ID'];
			if (in_array($rowPaySystemId, $paySystemIds, true))
			{
				$existPaySystemIds[] = $rowPaySystemId;
			}
			else
			{
				$deletedIds[] = (int)$row['ID'];
			}
		}
		$addedPaySystemIds = array_diff($paySystemIds, $existPaySystemIds);
		
		// delete old pay systems
		foreach ($deletedIds as $deleteId)
		{
			$deleteResult = PaymentPaySystemAvailableTable::delete($deleteId);
			foreach ($deleteResult->getErrors() as $err)
			{
				$result->addError($err);
			}
		}
		
		// add new exist pay systems
		$paySystems = PaySystemActionTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=ID' => $addedPaySystemIds,
			],
		]);
		foreach ($paySystems as $item)
		{
			$addResult = PaymentPaySystemAvailableTable::add([
				'PAYMENT_ID' => $paymentId,
				'PAY_SYSTEM_ID' => (int)$item['ID'],
			]);
			foreach ($addResult->getErrors() as $err)
			{
				$result->addError($err);
			}
		}
		
		return $result;
	}
	
	/**
	 * Pay systems available for payment
	 *
	 * @param int $paymentId
	 * @return int[] pay system ids
	 */
	public static function getAvailablePaySystemIdsByPaymentId(int $paymentId)
	{
		$ret = [];
		
		$rows = PaymentPaySystemAvailableTable::getList([
			'select' => [
				'PAY_SYSTEM_ID',
			],
			'filter' => [
				'=PAYMENT_ID' => $paymentId,
			],
		]);
		foreach ($rows as $row)
		{
			$ret[] = (int)$row['PAY_SYSTEM_ID'];
		}
		
		return $ret;
	}
}