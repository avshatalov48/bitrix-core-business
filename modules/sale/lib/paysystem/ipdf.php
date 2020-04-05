<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Sale\Payment;

/**
 * Interface IPdf
 * @package Bitrix\Sale\PaySystem
 */
interface IPdf
{
	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	public function getContent(Payment $payment);

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	public function getFile(Payment $payment);

	/**
	 * @param Payment $payment
	 * @return mixed
	 */
	public function isGenerated(Payment $payment);
}
