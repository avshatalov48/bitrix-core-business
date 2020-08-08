<?php

use Bitrix\Catalog\v2\Converter\ProductConverter;
use Bitrix\Catalog\v2\Iblock\IblockInfo;
use Bitrix\Catalog\v2\IoC\Container;
use Bitrix\Catalog\v2\IoC\Dependency;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory;
use Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepository;
use Bitrix\Catalog\v2\MeasureRatio\SimpleMeasureRatio;
use Bitrix\Catalog\v2\Price\PriceCollection;
use Bitrix\Catalog\v2\Price\PriceFactory;
use Bitrix\Catalog\v2\Price\PriceRepository;
use Bitrix\Catalog\v2\Price\QuantityDependentPrice;
use Bitrix\Catalog\v2\Price\SimplePrice;
use Bitrix\Catalog\v2\Product\Product;
use Bitrix\Catalog\v2\Product\ProductFactory;
use Bitrix\Catalog\v2\Product\ProductRepository;
use Bitrix\Catalog\v2\Property\Property;
use Bitrix\Catalog\v2\Property\PropertyCollection;
use Bitrix\Catalog\v2\Property\PropertyFactory;
use Bitrix\Catalog\v2\Property\PropertyRepository;
use Bitrix\Catalog\v2\PropertyValue\PropertyValue;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection;
use Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory;
use Bitrix\Catalog\v2\Section\Section;
use Bitrix\Catalog\v2\Section\SectionCollection;
use Bitrix\Catalog\v2\Section\SectionFactory;
use Bitrix\Catalog\v2\Section\SectionRepository;
use Bitrix\Catalog\v2\Sku\SimpleSku;
use Bitrix\Catalog\v2\Sku\Sku;
use Bitrix\Catalog\v2\Sku\SkuCollection;
use Bitrix\Catalog\v2\Sku\SkuFactory;
use Bitrix\Catalog\v2\Sku\SkuRepository;

return [
	Dependency::CONTAINER => Container::class,

	Dependency::IBLOCK_INFO => IblockInfo::class,
	Dependency::PRODUCT_CONVERTER => ProductConverter::class,

	Dependency::PRODUCT_FACTORY => ProductFactory::class,
	Dependency::PRODUCT_REPOSITORY => ProductRepository::class,

	ProductFactory::PRODUCT => Product::class,

	Dependency::SECTION_FACTORY => SectionFactory::class,
	Dependency::SECTION_REPOSITORY => SectionRepository::class,

	SectionFactory::SECTION => Section::class,
	SectionFactory::SECTION_COLLECTION => SectionCollection::class,

	Dependency::SKU_FACTORY => SkuFactory::class,
	Dependency::SKU_REPOSITORY => SkuRepository::class,

	SkuFactory::SIMPLE_SKU => SimpleSku::class,
	SkuFactory::SKU => Sku::class,
	SkuFactory::SKU_COLLECTION => SkuCollection::class,

	Dependency::PROPERTY_FACTORY => PropertyFactory::class,
	Dependency::PROPERTY_REPOSITORY => PropertyRepository::class,

	PropertyFactory::PROPERTY => Property::class,
	PropertyFactory::PROPERTY_COLLECTION => PropertyCollection::class,

	Dependency::PROPERTY_VALUE_FACTORY => PropertyValueFactory::class,

	PropertyValueFactory::PROPERTY_VALUE => PropertyValue::class,
	PropertyValueFactory::PROPERTY_VALUE_COLLECTION => PropertyValueCollection::class,

	Dependency::PRICE_FACTORY => PriceFactory::class,
	Dependency::PRICE_REPOSITORY => PriceRepository::class,

	PriceFactory::SIMPLE_PRICE => SimplePrice::class,
	PriceFactory::QUANTITY_DEPENDENT_PRICE => QuantityDependentPrice::class,
	PriceFactory::PRICE_COLLECTION => PriceCollection::class,

	Dependency::MEASURE_RATIO_FACTORY => MeasureRatioFactory::class,
	Dependency::MEASURE_RATIO_REPOSITORY => MeasureRatioRepository::class,

	MeasureRatioFactory::SIMPLE_MEASURE_RATIO => SimpleMeasureRatio::class,
	MeasureRatioFactory::MEASURE_RATIO_COLLECTION => MeasureRatioCollection::class,
];