<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

interface IPayable
{
	public function getPrice(Payment $payment);
}
