<?php


namespace Bitrix\Catalog\RestView;

use Bitrix\Main\Engine;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\RestException;
use Bitrix\Catalog\Controller;
use Bitrix\Catalog\RestView;
use Bitrix\Rest\Integration\ViewManager;

final class CatalogViewManager extends ViewManager
{
	/**
	 * @param Engine\Controller $controller
	 * @return Base
	 * @throws RestException
	 */
	public function getView(Engine\Controller $controller)
	{
		if ($controller instanceof Controller\PriceType)
		{
			return new RestView\PriceType();
		}

		if ($controller instanceof Controller\PriceTypeLang)
		{
			return new RestView\PriceTypeLang();
		}

		if ($controller instanceof Controller\PriceTypeGroup)
		{
			return new RestView\PriceTypeGroup();
		}

		if ($controller instanceof Controller\RoundingRule)
		{
			return new RestView\RoundingRule();
		}

		if ($controller instanceof Controller\Enum)
		{
			return new RestView\Enum();
		}

		if ($controller instanceof Controller\Extra)
		{
			return new RestView\Extra();
		}

		if ($controller instanceof Controller\Measure)
		{
			return new RestView\Measure();
		}

		if ($controller instanceof Controller\Ratio)
		{
			return new RestView\Ratio();
		}

		if ($controller instanceof Controller\Vat)
		{
			return new RestView\Vat();
		}

		if ($controller instanceof Controller\Store)
		{
			return new RestView\Store();
		}

		if ($controller instanceof Controller\StoreProduct)
		{
			return new RestView\StoreProduct();
		}

		if ($controller instanceof Controller\Product)
		{
			return new RestView\Product();
		}

		if ($controller instanceof Controller\Catalog)
		{
			return new RestView\Catalog();
		}

		if ($controller instanceof Controller\Section)
		{
			return new RestView\Section();
		}

		if ($controller instanceof Controller\Price)
		{
			return new RestView\Price();
		}

		if ($controller instanceof Controller\ProductImage)
		{
			return new RestView\ProductImage();
		}

		if ($controller instanceof Controller\ProductProperty)
		{
			return new RestView\ProductProperty();
		}

		if ($controller instanceof Controller\ProductPropertyEnum)
		{
			return new RestView\ProductPropertyEnum();
		}

		if ($controller instanceof Controller\ProductPropertyFeature)
		{
			return new RestView\ProductPropertyFeature();
		}

		if ($controller instanceof Controller\ProductPropertySection)
		{
			return new RestView\ProductPropertySection();
		}

		if ($controller instanceof Controller\Document)
		{
			return new RestView\Document();
		}

		if ($controller instanceof Controller\Document\Element)
		{
			return new RestView\DocumentElement();
		}

		if ($controller instanceof Controller\DocumentContractor)
		{
			return new RestView\DocumentContractor();
		}

		throw new RestException('Unknown object ' . get_class($controller));
	}
}