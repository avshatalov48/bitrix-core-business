<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\RestException;
use Bitrix\Sale\Controller\Profile;
use Bitrix\Sale\Controller\ProfileValue;

final class SaleViewManager extends ViewManager
{
	/**
	 * @param Controller $controller
	 * @return Base
	 * @throws RestException
	 */
	public function getView(Controller $controller)
	{
		$entity = null;
		if($controller instanceof Profile)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\Profile();
		}
		elseif($controller instanceof ProfileValue)
		{
			$entity = new \Bitrix\Sale\Rest\Entity\ProfileValue();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}

		return $entity;
	}
}