<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


class Shipment extends Entity
{
	protected function getAdditionalFilterFileds()
	{
		$params = $this->getParams();

		if(isset($params['ORDER_ID']))
		{
			$filter = ['=ORDER_ID'=>$params['ORDER_ID']];
		}
		else
		{
			$filter = [];
		}
		return $filter;
	}
}