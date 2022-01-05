<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\RestException;
use \Bitrix\Crm\RestView;
use \Bitrix\Crm\Controller;

final class CrmViewManager extends ViewManager
{
	/**
	 * @param Engine\Controller $controller
	 * @return Base
	 * @throws RestException
	 */
	public function getView(Engine\Controller $controller)
	{
		if($controller instanceof Controller\Enum)
		{
			$entity = new RestView\Enum();
		}
		elseif($controller instanceof Controller\OrderEntity)
		{
			$entity = new RestView\OrderEntity();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}

		return $entity;
	}
}