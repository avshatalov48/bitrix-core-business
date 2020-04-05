<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

interface IHold
{
	public function cancel(Payment $payment);

	public function confirm(Payment $payment);
}
