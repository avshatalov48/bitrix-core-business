<?php


namespace Bitrix\Sale\Rest\Synchronization\Loader;


class BasketProperties extends Entity
{
	protected function getAdditionalFilterFileds()
	{
		$params = $this->getParams();

		if(isset($params['BASKET_ID']))
		{
			$filter = ['=BASKET_ID'=>$params['BASKET_ID']];
		}
		else
		{
			$filter = [];
		}
		return $filter;
	}
}