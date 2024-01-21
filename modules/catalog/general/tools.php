<?php

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Catalog;
use Bitrix\Crm;

class CCatalogTools
{
	public static function updateModuleTasksAgent(): string
	{
		if (!class_exists('\catalog', false))
		{
			require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/install/index.php');
		}
		if (class_exists('\catalog', false))
		{
			$moduleDescr = new \catalog();
			$moduleDescr->InstallTasks();
			unset($moduleDescr);
		}

		return '';
	}

	public static function updatePropertyFeaturesBitrix24Agent(): string
	{
		if (!Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			return '';
		}
		Main\Config\Option::set('iblock', 'property_features_enabled', 'Y', '');
		if (Iblock\Model\PropertyFeature::isPropertyFeaturesExist())
		{
			return '';
		}
		if (!Loader::includeModule('crm'))
		{
			return '';
		}
		$catalogId = Crm\Product\Catalog::getDefaultId()();
		if ($catalogId === null)
		{
			return '';
		}

		$catalogProperties = [
			'ARTNUMBER' => [
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
			],
			'MANUFACTURER' => [
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
			],
			'MATERIAL' => [
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
			],
		];

		$iterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID',
				'CODE',
			],
			'filter' => [
				'=IBLOCK_ID' => $catalogId,
				'@CODE' => array_keys($catalogProperties),
			]
		]);
		while ($row = $iterator->fetch())
		{
			$result = Iblock\Model\PropertyFeature::setFeatures(
				$row['ID'],
				$catalogProperties[$row['CODE']]
			);
		}
		unset($result, $row, $iterator);
		unset($catalogProperties);

		$offerCatalogId = \Bitrix\Crm\Product\Catalog::getDefaultOfferId();
		if ($offerCatalogId === null)
		{
			return '';
		}

		$offerProperties = [
			'ARTNUMBER' => [
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_LIST_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'iblock',
					'FEATURE_ID' => Iblock\Model\PropertyFeature::FEATURE_ID_DETAIL_PAGE_SHOW,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY,
					'IS_ENABLED' => 'Y',
				]
			],
			'COLOR_REF' => [
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY,
					'IS_ENABLED' => 'Y',
				],
			],
			'SIZES_SHOES' => [
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY,
					'IS_ENABLED' => 'Y',
				],
			],
			'SIZES_CLOTHES' => [
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_OFFER_TREE_PROPERTY,
					'IS_ENABLED' => 'Y',
				],
				[
					'MODULE_ID' => 'catalog',
					'FEATURE_ID' => Catalog\Product\PropertyCatalogFeature::FEATURE_ID_BASKET_PROPERTY,
					'IS_ENABLED' => 'Y',
				],
			]
		];

		$iterator = Iblock\PropertyTable::getList([
			'select' => [
				'ID',
				'CODE',
			],
			'filter' => [
				'=IBLOCK_ID' => $offerCatalogId,
				'@CODE' => array_keys($offerProperties),
			]
		]);
		while ($row = $iterator->fetch())
		{
			$result = Iblock\Model\PropertyFeature::setFeatures(
				$row['ID'],
				$offerProperties[$row['CODE']]
			);
		}
		unset($result, $row, $iterator);
		unset($offerProperties);
		unset($offerCatalogId, $catalogId);

		return '';
	}
}
