<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


class ShipmentItem extends Entity
{
	protected function getAdditionalFilterFileds()
	{
		$params = $this->getParams();

		if(isset($params['ORDER_DELIVERY_ID']))
		{
			$filter = ['=ORDER_DELIVERY_ID'=>$params['ORDER_DELIVERY_ID']];
		}
		else
		{
			$filter = [];
		}
		return $filter;
	}
}