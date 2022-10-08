<?php

namespace PHPSTORM_META
{

	registerArgumentsSet(
		'bitrix_catalog_service_container_dependencies',
		\Bitrix\Catalog\v2\IoC\Dependency::CONTAINER,
		\Bitrix\Catalog\v2\IoC\Dependency::IBLOCK_INFO,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_CONVERTER,
		\Bitrix\Catalog\v2\IoC\Dependency::REPOSITORY_FACADE,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::SECTION_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::SECTION_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::SKU_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::SKU_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_VALUE_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FEATURE_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FEATURE_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PRICE_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::PRICE_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::IMAGE_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::IMAGE_REPOSITORY,
		\Bitrix\Catalog\v2\IoC\Dependency::MEASURE_RATIO_FACTORY,
		\Bitrix\Catalog\v2\IoC\Dependency::MEASURE_RATIO_REPOSITORY,
		'sku.tree',
		'integration.seo.facebook.facade',
		'integration.seo.facebook.product.processor',
		'integration.seo.facebook.product.repository',
	);
	expectedArguments(
		\Bitrix\Catalog\v2\IoC\ServiceContainer::get(),
		0,
		argumentsSet('bitrix_catalog_service_container_dependencies')
	);
	expectedArguments(
		\Bitrix\Catalog\v2\IoC\ServiceContainer::make(),
		0,
		argumentsSet('bitrix_catalog_service_container_dependencies')
	);

	override(\Bitrix\Catalog\v2\IoC\ServiceContainer::get(0), map([
		\Bitrix\Catalog\v2\IoC\Dependency::CONTAINER => \Bitrix\Catalog\v2\IoC\Container::class,

		\Bitrix\Catalog\v2\IoC\Dependency::IBLOCK_INFO => \Bitrix\Catalog\v2\Iblock\IblockInfo::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_CONVERTER => \Bitrix\Catalog\v2\Converter\ProductConverter::class,
		\Bitrix\Catalog\v2\IoC\Dependency::REPOSITORY_FACADE => \Bitrix\Catalog\v2\Facade\Repository::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_FACTORY => \Bitrix\Catalog\v2\Product\ProductFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_REPOSITORY => \Bitrix\Catalog\v2\Product\ProductRepository::class,

		\Bitrix\Catalog\v2\Product\ProductFactory::PRODUCT => \Bitrix\Catalog\v2\Product\Product::class,

		\Bitrix\Catalog\v2\IoC\Dependency::SECTION_FACTORY => \Bitrix\Catalog\v2\Section\SectionFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::SECTION_REPOSITORY => \Bitrix\Catalog\v2\Section\SectionRepository::class,

		\Bitrix\Catalog\v2\Section\SectionFactory::SECTION => \Bitrix\Catalog\v2\Section\Section::class,
		\Bitrix\Catalog\v2\Section\SectionFactory::SECTION_COLLECTION => \Bitrix\Catalog\v2\Section\SectionCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::SKU_FACTORY => \Bitrix\Catalog\v2\Sku\SkuFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::SKU_REPOSITORY => \Bitrix\Catalog\v2\Sku\SkuRepository::class,

		\Bitrix\Catalog\v2\Sku\SkuFactory::SIMPLE_SKU => \Bitrix\Catalog\v2\Sku\SimpleSku::class,
		\Bitrix\Catalog\v2\Sku\SkuFactory::SKU => \Bitrix\Catalog\v2\Sku\Sku::class,
		\Bitrix\Catalog\v2\Sku\SkuFactory::SKU_COLLECTION => \Bitrix\Catalog\v2\Sku\SkuCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FACTORY => \Bitrix\Catalog\v2\Property\PropertyFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_REPOSITORY => \Bitrix\Catalog\v2\Property\PropertyRepository::class,

		\Bitrix\Catalog\v2\Property\PropertyFactory::PROPERTY => \Bitrix\Catalog\v2\Property\Property::class,
		\Bitrix\Catalog\v2\Property\PropertyFactory::PROPERTY_COLLECTION => \Bitrix\Catalog\v2\Property\PropertyCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_VALUE_FACTORY => \Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory::class,

		\Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory::PROPERTY_VALUE => \Bitrix\Catalog\v2\PropertyValue\PropertyValue::class,
		\Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory::PROPERTY_VALUE_COLLECTION => \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FEATURE_FACTORY => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FEATURE_REPOSITORY => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureRepository::class,

		\Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory::PROPERTY_FEATURE => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeature::class,
		\Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory::PROPERTY_FEATURE_COLLECTION => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PRICE_FACTORY => \Bitrix\Catalog\v2\Price\PriceFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PRICE_REPOSITORY => \Bitrix\Catalog\v2\Price\PriceRepository::class,

		\Bitrix\Catalog\v2\Price\PriceFactory::SIMPLE_PRICE => \Bitrix\Catalog\v2\Price\SimplePrice::class,
		\Bitrix\Catalog\v2\Price\PriceFactory::QUANTITY_DEPENDENT_PRICE => \Bitrix\Catalog\v2\Price\QuantityDependentPrice::class,
		\Bitrix\Catalog\v2\Price\PriceFactory::PRICE_COLLECTION => \Bitrix\Catalog\v2\Price\PriceCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::IMAGE_FACTORY => \Bitrix\Catalog\v2\Image\ImageFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::IMAGE_REPOSITORY => \Bitrix\Catalog\v2\Image\ImageRepository::class,

		\Bitrix\Catalog\v2\Image\ImageFactory::DETAIL_IMAGE => \Bitrix\Catalog\v2\Image\DetailImage::class,
		\Bitrix\Catalog\v2\Image\ImageFactory::PREVIEW_IMAGE => \Bitrix\Catalog\v2\Image\PreviewImage::class,
		\Bitrix\Catalog\v2\Image\ImageFactory::MORE_PHOTO_IMAGE => \Bitrix\Catalog\v2\Image\MorePhotoImage::class,
		\Bitrix\Catalog\v2\Image\ImageFactory::IMAGE_COLLECTION => \Bitrix\Catalog\v2\Image\ImageCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::MEASURE_RATIO_FACTORY => \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::MEASURE_RATIO_REPOSITORY => \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepository::class,

		\Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory::SIMPLE_MEASURE_RATIO => \Bitrix\Catalog\v2\MeasureRatio\SimpleMeasureRatio::class,
		\Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory::MEASURE_RATIO_COLLECTION => \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection::class,

		'sku.tree' => \Bitrix\Catalog\Component\SkuTree::class,
		'integration.seo.facebook.facade' => \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookFacade::class,
		'integration.seo.facebook.product.processor' => \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookProductProcessor::class,
		'integration.seo.facebook.product.repository' => \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookProductRepository::class,
	]));

	override(\Bitrix\Catalog\v2\IoC\ServiceContainer::make(0), map([
		\Bitrix\Catalog\v2\IoC\Dependency::CONTAINER => \Bitrix\Catalog\v2\IoC\Container::class,

		\Bitrix\Catalog\v2\IoC\Dependency::IBLOCK_INFO => \Bitrix\Catalog\v2\Iblock\IblockInfo::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_CONVERTER => \Bitrix\Catalog\v2\Converter\ProductConverter::class,
		\Bitrix\Catalog\v2\IoC\Dependency::REPOSITORY_FACADE => \Bitrix\Catalog\v2\Facade\Repository::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_FACTORY => \Bitrix\Catalog\v2\Product\ProductFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PRODUCT_REPOSITORY => \Bitrix\Catalog\v2\Product\ProductRepository::class,

		\Bitrix\Catalog\v2\Product\ProductFactory::PRODUCT => \Bitrix\Catalog\v2\Product\Product::class,

		\Bitrix\Catalog\v2\IoC\Dependency::SECTION_FACTORY => \Bitrix\Catalog\v2\Section\SectionFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::SECTION_REPOSITORY => \Bitrix\Catalog\v2\Section\SectionRepository::class,

		\Bitrix\Catalog\v2\Section\SectionFactory::SECTION => \Bitrix\Catalog\v2\Section\Section::class,
		\Bitrix\Catalog\v2\Section\SectionFactory::SECTION_COLLECTION => \Bitrix\Catalog\v2\Section\SectionCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::SKU_FACTORY => \Bitrix\Catalog\v2\Sku\SkuFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::SKU_REPOSITORY => \Bitrix\Catalog\v2\Sku\SkuRepository::class,

		\Bitrix\Catalog\v2\Sku\SkuFactory::SIMPLE_SKU => \Bitrix\Catalog\v2\Sku\SimpleSku::class,
		\Bitrix\Catalog\v2\Sku\SkuFactory::SKU => \Bitrix\Catalog\v2\Sku\Sku::class,
		\Bitrix\Catalog\v2\Sku\SkuFactory::SKU_COLLECTION => \Bitrix\Catalog\v2\Sku\SkuCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FACTORY => \Bitrix\Catalog\v2\Property\PropertyFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_REPOSITORY => \Bitrix\Catalog\v2\Property\PropertyRepository::class,

		\Bitrix\Catalog\v2\Property\PropertyFactory::PROPERTY => \Bitrix\Catalog\v2\Property\Property::class,
		\Bitrix\Catalog\v2\Property\PropertyFactory::PROPERTY_COLLECTION => \Bitrix\Catalog\v2\Property\PropertyCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_VALUE_FACTORY => \Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory::class,

		\Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory::PROPERTY_VALUE => \Bitrix\Catalog\v2\PropertyValue\PropertyValue::class,
		\Bitrix\Catalog\v2\PropertyValue\PropertyValueFactory::PROPERTY_VALUE_COLLECTION => \Bitrix\Catalog\v2\PropertyValue\PropertyValueCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FEATURE_FACTORY => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PROPERTY_FEATURE_REPOSITORY => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureRepository::class,

		\Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory::PROPERTY_FEATURE => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeature::class,
		\Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureFactory::PROPERTY_FEATURE_COLLECTION => \Bitrix\Catalog\v2\PropertyFeature\PropertyFeatureCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::PRICE_FACTORY => \Bitrix\Catalog\v2\Price\PriceFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::PRICE_REPOSITORY => \Bitrix\Catalog\v2\Price\PriceRepository::class,

		\Bitrix\Catalog\v2\Price\PriceFactory::SIMPLE_PRICE => \Bitrix\Catalog\v2\Price\SimplePrice::class,
		\Bitrix\Catalog\v2\Price\PriceFactory::QUANTITY_DEPENDENT_PRICE => \Bitrix\Catalog\v2\Price\QuantityDependentPrice::class,
		\Bitrix\Catalog\v2\Price\PriceFactory::PRICE_COLLECTION => \Bitrix\Catalog\v2\Price\PriceCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::IMAGE_FACTORY => \Bitrix\Catalog\v2\Image\ImageFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::IMAGE_REPOSITORY => \Bitrix\Catalog\v2\Image\ImageRepository::class,

		\Bitrix\Catalog\v2\Image\ImageFactory::DETAIL_IMAGE => \Bitrix\Catalog\v2\Image\DetailImage::class,
		\Bitrix\Catalog\v2\Image\ImageFactory::PREVIEW_IMAGE => \Bitrix\Catalog\v2\Image\PreviewImage::class,
		\Bitrix\Catalog\v2\Image\ImageFactory::MORE_PHOTO_IMAGE => \Bitrix\Catalog\v2\Image\MorePhotoImage::class,
		\Bitrix\Catalog\v2\Image\ImageFactory::IMAGE_COLLECTION => \Bitrix\Catalog\v2\Image\ImageCollection::class,

		\Bitrix\Catalog\v2\IoC\Dependency::MEASURE_RATIO_FACTORY => \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory::class,
		\Bitrix\Catalog\v2\IoC\Dependency::MEASURE_RATIO_REPOSITORY => \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioRepository::class,

		\Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory::SIMPLE_MEASURE_RATIO => \Bitrix\Catalog\v2\MeasureRatio\SimpleMeasureRatio::class,
		\Bitrix\Catalog\v2\MeasureRatio\MeasureRatioFactory::MEASURE_RATIO_COLLECTION => \Bitrix\Catalog\v2\MeasureRatio\MeasureRatioCollection::class,

		'sku.tree' => \Bitrix\Catalog\Component\SkuTree::class,
		'integration.seo.facebook.facade' => \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookFacade::class,
		'integration.seo.facebook.product.processor' => \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookProductProcessor::class,
		'integration.seo.facebook.product.repository' => \Bitrix\Catalog\v2\Integration\Seo\Facebook\FacebookProductRepository::class,
	]));
}
