<?php

namespace Bitrix\Sale\PaySystem;

use Bitrix\Main\Request;
use Bitrix\Sale\Payment;

interface IPrePayable
{
	public function initPrePayment(Payment $payment = null, Request $request);

	public function getProps();

	public function payOrder($orderData = array());

	public function setOrderConfig($orderData = array());

	public function basketButtonAction($orderData);
}
