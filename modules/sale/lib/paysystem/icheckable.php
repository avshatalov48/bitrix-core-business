<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

interface ICheckable
{
	/**
	 * @param Payment $payment
	 * @return ServiceResult
	 */
	public function check(Payment $payment);
}
