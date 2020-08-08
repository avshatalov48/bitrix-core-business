<?php

namespace Bitrix\Catalog\v2\Converter;

use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\v2\IoC\ContainerContract;
use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Catalog\v2\Product\BaseProduct;
use Bitrix\Catalog\v2\Sku\BaseSku;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Class ProductConverter
 *
 * @package Bitrix\Catalog\v2\Converter
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
class ProductConverter
{
	public const SIMPLE_PRODUCT = 'SIMPLE_PRODUCT';
	public const SKU_PRODUCT = 'SKU_PRODUCT';

	private $container;

	public function __construct(ContainerContract $container)
	{
		$this->container = $container;
	}

	public function convert(BaseProduct $product, string $destinationType): Result
	{
		if ($destinationType === self::SKU_PRODUCT)
		{
			return $this->convertSimpleProductToSkuProduct($product);
		}

		// if ($destinationType === self::SIMPLE_PRODUCT)
		// {
		// 	return $this->convertSkuToSimpleProduct($product);
		// }

		return (new Result())->addError(new Error(sprintf(
			'Could not convert product {%s} to type {%s}.',
			get_class($product),
			$destinationType
		)));
	}

	protected function convertSimpleProductToSkuProduct(BaseProduct $product): Result
	{
		$result = new Result();

		if ($product->isSimple() && $product->getIblockInfo()->canHaveSku())
		{
			/** @var \Bitrix\Catalog\v2\Sku\BaseSku $item */
			foreach ($product->getSkuCollection() as $item)
			{
				if (!$item->isSimple())
				{
					continue;
				}

				$skuItem = $this->convertSimpleSkuToSku($item);
				if ($skuItem)
				{
					$result->setData([
						'CONVERTED_SKU' => $skuItem,
					]);
				}
			}

			$product->setType(ProductTable::TYPE_SKU);
		}

		return $result;
	}

	protected function convertSimpleSkuToSku(BaseSku $simpleItem): BaseSku
	{
		/** @var \Bitrix\Catalog\v2\Sku\SkuFactory $skuFactory */
		$skuFactory = $this->container->get(Dependency::SKU_FACTORY, [
			Dependency::IBLOCK_INFO => $simpleItem->getIblockInfo(),
		]);

		if (!$skuFactory)
		{
			return $simpleItem;
		}

		$skuItem = $skuFactory->createEntity($skuFactory::SKU);
		if ($skuItem->isSimple())
		{
			return $simpleItem;
		}

		/** @var \Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection */
		$skuCollection = $simpleItem->getParentCollection();
		$skuCollection->remove($simpleItem)
			->clearRemoved($simpleItem)
			->add($skuItem)
		;

		$fields = array_diff_key($simpleItem->getFields(), [
			'ID' => true,
			'IBLOCK_ID' => true,
			'TYPE' => true,
		]);
		$skuItem->setFields($fields);

		$skuItem->getPriceCollection()->setValues($simpleItem->getPriceCollection()->getValues());

		$defaultRatio = $simpleItem->getMeasureRatioCollection()->findDefault();
		if ($defaultRatio)
		{
			$skuItem->getMeasureRatioCollection()->setDefault($defaultRatio->getRatio());
		}

		return $skuItem;
	}

	// ToDo currently doesn't work properly because of different calculation handlers of old api
	protected function convertSkuToSimpleProduct(BaseProduct $product): Result
	{
		$result = new Result();

		if (!$product->isSimple() && $product->getSkuCollection()->count() <= 1)
		{
			/** @var \Bitrix\Catalog\v2\Sku\BaseSku $item */
			foreach ($product->getSkuCollection() as $item)
			{
				if ($item->isSimple())
				{
					continue;
				}

				// ToDo another converter entity for Sku?
				$skuItem = $this->convertSkuToSimpleSku($item);
				if ($skuItem)
				{
					$result->setData([
						'CONVERTED_SKU' => $skuItem,
					]);
				}
			}

			$product->setType(ProductTable::TYPE_PRODUCT);
		}

		return $result;
	}

	protected function convertSkuToSimpleSku(BaseSku $skuItem): BaseSku
	{
		/** @var \Bitrix\Catalog\v2\Sku\SkuFactory $skuFactory */
		$skuFactory = $this->container->get(Dependency::SKU_FACTORY, [
			Dependency::IBLOCK_INFO => $skuItem->getIblockInfo(),
		]);

		if (!$skuFactory)
		{
			return $skuItem;
		}

		$simpleItem = $skuFactory->createEntity($skuFactory::SIMPLE_SKU);

		/** @var \Bitrix\Catalog\v2\Sku\SkuCollection $skuCollection */
		$skuCollection = $skuItem->getParentCollection();
		$skuCollection->remove($skuItem)
			->add($simpleItem)
		;

		$fields = array_diff_key($skuItem->getFields(), [
			'ID' => true,
			'IBLOCK_ID' => true,
			'TYPE' => true,
		]);
		$simpleItem->setFields($fields);

		$simpleItem->getPriceCollection()->setValues($skuItem->getPriceCollection()->getValues());

		$defaultRatio = $skuItem->getMeasureRatioCollection()->findDefault();
		if ($defaultRatio)
		{
			$simpleItem->getMeasureRatioCollection()->setDefault($defaultRatio->getRatio());
		}

		return $simpleItem;
	}
}