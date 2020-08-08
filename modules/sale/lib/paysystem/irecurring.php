<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Request,
	Bitrix\Sale\Payment;

/**
 * Interface IRecurring
 *
 * @package Bitrix\Sale\PaySystem
 */
interface IRecurring
{
	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 */
	public function repeatRecurrent(Payment $payment, Request $request = null): ServiceResult;

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return ServiceResult
	 */
	public function cancelRecurrent(Payment $payment, Request $request = null): ServiceResult;

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	public function isRecurring(Payment $payment): bool;
}