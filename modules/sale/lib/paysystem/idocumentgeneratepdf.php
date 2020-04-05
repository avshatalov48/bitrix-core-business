<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

/**
 * Interface IPdfDocumentGeneratable
 * @package Bitrix\Sale\PaySystem
 */
interface IDocumentGeneratePdf extends IPdf
{
	/**
	 * @param Payment $payment
	 * @param $params
	 * @return mixed
	 */
	public function registerCallbackOnGenerate(Payment $payment, $params);
}
