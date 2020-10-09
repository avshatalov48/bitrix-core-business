<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Exchange\Integration;

class SaleViewManager extends ViewManager
{
	/**
	 * @param Controller $controller
	 * @return View\Base
	 * @throws RestException
	 */
	public function getView(Controller $controller)
	{
		$entity = null;
		if($controller instanceof Integration\Controller\StatisticProvider)
		{
			$entity = new Integration\RestView\StatisticProvider();
		}
		elseif($controller instanceof Integration\Controller\Statistic)
		{
			$entity = new Integration\RestView\Statistic();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}
		return $entity;
	}
}