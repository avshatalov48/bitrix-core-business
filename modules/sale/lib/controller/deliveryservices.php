<?php


namespace Bitrix\Sale\Controller;


use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Sale\Result;

class DeliveryServices extends Controller
{
	//region Actions
	public function getActiveListAction()
	{
		$deliveryServices = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
		return new Page('DELIVERY_SERVICES', $deliveryServices, count($deliveryServices));
	}
	//endregion

	protected function checkPermissionEntity($name)
	{
		return new Result();
	}
}