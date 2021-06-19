<?php

use Bitrix\Main\HttpContext;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class StoreSalesCenterOrderDetails extends \Bitrix\Landing\LandingBlock
{
	public function init(array $params = [])
	{
		$request = HttpContext::getCurrent()->getRequest();
		$this->params['ORDER_ID'] = $request->get('orderId')
			? (int)$request->get('orderId')
			: null;
		$this->params['PAYMENT_ID'] = $request->get('paymentId')
			? (int)$request->get('paymentId')
			: null;

		// todo: need get order hash, or it in the component
	}
}