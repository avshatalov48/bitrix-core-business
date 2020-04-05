<?php


namespace Bitrix\Rest\Integration;


use Bitrix\Main\Engine\Controller;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\RestException;

final class CatalogViewManager extends ViewManager
{
	/**
	 * @param Controller $controller
	 * @return Base
	 * @throws RestException
	 */
	public function getView(Controller $controller)
	{
		$entity = null;
		if($controller instanceof \Bitrix\Catalog\Controller\PriceType)
		{
			$entity = new \Bitrix\Catalog\RestView\PriceType();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\RoundingRule)
		{
			$entity = new \Bitrix\Catalog\RestView\RoundingRule();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Enum)
		{
			$entity = new \Bitrix\Catalog\RestView\Enum();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Extra)
		{
			$entity = new \Bitrix\Catalog\RestView\Extra();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Measure)
		{
			$entity = new \Bitrix\Catalog\RestView\Measure();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Ratio)
		{
			$entity = new \Bitrix\Catalog\RestView\Ratio();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Vat)
		{
			$entity = new \Bitrix\Catalog\RestView\Vat();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Store)
		{
			$entity = new \Bitrix\Catalog\RestView\Store();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Product)
		{
			$entity = new \Bitrix\Catalog\RestView\Product();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Catalog)
		{
			$entity = new \Bitrix\Catalog\RestView\Catalog();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Section)
		{
			$entity = new \Bitrix\Catalog\RestView\Section();
		}
		elseif($controller instanceof \Bitrix\Catalog\Controller\Price)
		{
			$entity = new \Bitrix\Catalog\RestView\Price();
		}
		else
		{
			throw new RestException('Unknown object ' . get_class($controller));
		}

		return $entity;
	}
}