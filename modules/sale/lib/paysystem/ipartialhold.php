<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

interface IPartialHold extends IHold
{
	public function confirm(Payment $payment, $sum = 0);
}
