<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class StoreSalesCenterOrderDetails extends \Bitrix\Landing\LandingBlock
{
	public function init(array $params = [])
	{
		$request = \bitrix\Main\HttpContext::getCurrent()->getRequest();
		$this->params['ORDER_ID'] = $request->get('orderId')
			? intval($request->get('orderId'))
			: null;

		// todo: need get order hash, or it in the component
	}
}