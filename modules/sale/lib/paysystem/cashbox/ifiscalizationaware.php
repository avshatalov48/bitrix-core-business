<?php

namespace Bitrix\Sale\PaySystem\Cashbox;

use Bitrix\Sale\Payment;

/**
 * Interface IFiscalizationAware
 *
 * @package Bitrix\Sale\PaySystem\Cashbox
 */
interface IFiscalizationAware
{
	public function isFiscalizationEnabled(Payment $payment): ?bool;
}
