<?php

namespace Bitrix\Sale\Controller;

use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Sale\Result;
use Bitrix\Sale\Delivery;

class DeliveryServices extends ControllerBase
{
	//region Actions
	public function getActiveListAction()
	{
		$whiteList = [
			'ID',
			'CODE',
			'PARENT_ID',
			'NAME',
			'ACTIVE',
			'DESCRIPTION',
			'SORT',
			'LOGOTIP',
			'CURRENCY',
			'XML_ID',
		];

		$result = [];
		$deliveryServices = Delivery\Services\Manager::getActiveList();

		foreach ($deliveryServices as $deliveryService)
		{
			$result[] = array_intersect_key($deliveryService, array_flip($whiteList));
		}

		return new Page('DELIVERY_SERVICES', $result, count($result));
	}
	//endregion

	protected function checkPermissionEntity($name, $arguments=[])
	{
		return new Result();
	}
}
