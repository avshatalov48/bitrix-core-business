<?php

namespace Bitrix\Catalog\v2\IoC;

use Bitrix\Catalog\v2\Converter\ProductConverter;
use Bitrix\Catalog\v2\Facade\Repository;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepositoryContract;
use Bitrix\Catalog\v2\Price\PriceFactory;
use Bitrix\Catalog\v2\Price\PriceRepositoryContract;
use Bitrix\Catalog\v2\Image\ImageFactory;
use Bitrix\Catalog\v2\Image\ImageRepositoryContract;
use Bitrix\Catalog\v2\Product\ProductFactory;
use Bitrix\Catalog\v2\Product\ProductRepositoryContract;
use Bitrix\Catalog\v2\Property\PropertyFactory;
use Bitrix\Catalog\v2\Property\PropertyRepositoryContract;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory;
use Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureRepositoryContract;
use Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory;
use Bitrix\Catalog\v2\Section\SectionFactory;
use Bitrix\Catalog\v2\Section\SectionRepositoryContract;
use Bitrix\Catalog\v2\Sku\SkuFactory;
use Bitrix\Catalog\v2\Sku\SkuRepositoryContract;
use Bitrix\Catalog\v2\Barcode\BarcodeFactory;
use Bitrix\Catalog\v2\Barcode\BarcodeRepositoryContract;
use Bitrix\Catalog\v2\StoreProduct\StoreProductFactory;
use Bitrix\Catalog\v2\StoreProduct\StoreProductRepositoryContract;
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
	public const REPOSITORY_FACADE = Repository::class;

	public const PRODUCT_FACTORY = ProductFactory::class;
	public const PRODUCT_REPOSITORY = ProductRepositoryContract::class;

	public const SECTION_FACTORY = SectionFactory::class;
	public const SECTION_REPOSITORY = SectionRepositoryContract::class;

	public const SKU_FACTORY = SkuFactory::class;
	public const SKU_REPOSITORY = SkuRepositoryContract::class;

	public const PROPERTY_FACTORY = PropertyFactory::class;
	public const PROPERTY_REPOSITORY = PropertyRepositoryContract::class;

	public const PROPERTY_VALUE_FACTORY = PropertyValueFactory::class;

	public const PROPERTY_FEATURE_FACTORY = PropertyFeatureFactory::class;
	public const PROPERTY_FEATURE_REPOSITORY = PropertyFeatureRepositoryContract::class;

	public const PRICE_FACTORY = PriceFactory::class;
	public const PRICE_REPOSITORY = PriceRepositoryContract::class;

	public const IMAGE_FACTORY = ImageFactory::class;
	public const IMAGE_REPOSITORY = ImageRepositoryContract::class;

	public const MEASURE_RATIO_FACTORY = MeasureRatioFactory::class;
	public const MEASURE_RATIO_REPOSITORY = MeasureRatioRepositoryContract::class;

	public const BARCODE_FACTORY = BarcodeFactory::class;
	public const BARCODE_REPOSITORY = BarcodeRepositoryContract::class;

	public const STORE_PRODUCT_FACTORY = StoreProductFactory::class;
	public const STORE_PRODUCT_REPOSITORY = StoreProductRepositoryContract::class;

	private function __construct()
	{
		throw new NotSupportedException(sprintf(
			'Class {%s} can not be constructed.', static::class
		));
	}
}
