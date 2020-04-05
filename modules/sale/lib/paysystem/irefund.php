<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

interface IRefund
{
	public function refund(Payment $payment, $refundableSum);
}
