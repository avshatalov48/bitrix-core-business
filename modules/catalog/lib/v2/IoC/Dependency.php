<?php

namespace Bitrix\Catalog\v2\IoC;

use Bitrix\Catalog\v2\Converter\ProductConverter;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract;
use Bitrix\Catalog\v2\Price\PriceFactory;
use Bitrix\Catalog\v2\Price\PriceRepositoryContract;
use Bitrix\Catalog\v2\Product\ProductFactory;
use Bitrix\Catalog\v2\Product\ProductRepositoryContract;
use Bitrix\Catalog\v2\Property\PropertyFactory;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory;
use Bitrix\Catalog\v2\Section\SectionFactory;
use Bitrix\Catalog\v2\Section\SectionRepositoryContract;
use Bitrix\Catalog\v2\Sku\SkuFactory;
use Bitrix\Catalog\v2\Sku\SkuRepositoryContract;
use Bitrix\Main\NotSupportedException;

/**
 * Class Dependency
 *
 * @package Bitrix\Catalog\v2\IoC
 *
 * !!! This API is in alpha stage and is not stable. This is subject to change at any time without notice.
 * @internal
 */
final class Dependency
{
	public const CONTAINER = ContainerContract::class;
	public const IBLOCK_INFO = IblockInfo::class;
	public const PRODUCT_CONVERTER = ProductConverter::class;

	public const PRODUCT_FACTORY = ProductFactory::class;
	public const PRODUCT_REPOSITORY = ProductRepositoryContract::class;

	public const SECTION_FACTORY = SectionFactory::class;
	public const SECTION_REPOSITORY = SectionRepositoryContract::class;

	public const SKU_FACTORY = SkuFactory::class;
	public const SKU_REPOSITORY = SkuRepositoryContract::class;

	public const PROPERTY_FACTORY = PropertyFactory::class;
	public const PROPERTY_REPOSITORY = PropertyRepositoryContract::class;

	public const PROPERTY_VALUE_FACTORY = PropertyValueFactory::class;

	public const PRICE_FACTORY = PriceFactory::class;
	public const PRICE_REPOSITORY = PriceRepositoryContract::class;

	public const MEASURE_RATIO_FACTORY = MeasureRatioFactory::class;
	public const MEASURE_RATIO_REPOSITORY = MeasureRatioRepositoryContract::class;

	private function __construct()
	{
		throw new NotSupportedException(sprintf(
			'Class {%s} can not be constructed.', static::class
		));
	}
}