<?php
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\Loader as Loader;
use \Bitrix\Main\SystemException as SystemException;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

Loc::loadMessages(__FILE__);

class CSaleRecommendedProductsComponent extends CCatalogViewedProductsComponent
{
	/**
	 * @override
	 */
	public function onIncludeComponentLang()
	{
		parent::onIncludeComponentLang();
		$this->includeComponentLang(basename(__FILE__));
	}
	/**
	 * @param $params
	 * @override
	 * @return array
	 */
	public function onPrepareComponentParams($params)
	{
		if(Loader::includeModule("catalog"))
		{
			$cache = new CPHPCache();
			$cacheTtl = 86400;
			$cacheID = 'sale_catalog_recommended_products';
			$cacheDir = '/sale/sale_recommended_products';
			if($cache->InitCache($cacheTtl, $cacheID, $cacheDir))
			{
				$rows = $cache->GetVars();
			}
			else
			{
				$cache->StartDataCache();
				$rows = array();
				$catalogIterator = \Bitrix\Catalog\CatalogIblockTable::getList(array(
					'select' => array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID'),
					'order' => array('IBLOCK_ID' => 'ASC')
				));
				while ($row = $catalogIterator->fetch())
				{
					$iblockId = (int)$row['IBLOCK_ID'];
					$rows[$iblockId] = $iblockId;
					$iblockId = (int)$row['PRODUCT_IBLOCK_ID'];
					if ($iblockId > 0)
						$rows[$iblockId] = $iblockId;
					unset($iblockId);
				}
				unset($row, $catalogIterator);
				if(defined("BX_COMP_MANAGED_CACHE") && !empty($rows))
				{
					global $CACHE_MANAGER;
					$CACHE_MANAGER->StartTagCache($cacheDir);
					foreach ($rows as $id)
						CIBlock::registerWithTagCache($id);
					$CACHE_MANAGER->EndTagCache();
				}
				$cache->EndDataCache($rows);
			}

			if (!empty($rows))
			{
				foreach ($rows as $id)
					$params['SHOW_PRODUCTS_'.$id] = true;
				unset($id);
			}
		}

		$params = parent::onPrepareComponentParams($params);

		if(!isset($params["CACHE_TIME"]))
			$params["CACHE_TIME"] = 86400;

		$params["DETAIL_URL"] = trim($params["DETAIL_URL"]);

		$params["MIN_BUYES"] = intval($params["MIN_BUYES"]);
		if($params["MIN_BUYES"] <= 0)
			$params["MIN_BUYES"] = 2;

		if(isset($params['ID']))
			$params['ID'] = (int)$params["ID"];
		else
			$params['ID'] = -1;

		if(isset($params['CODE']))
			$params['CODE'] = trim($params['CODE']);
		else
			$params['CODE'] = '';

		if(isset($params['IBLOCK_ID']))
			$params['IBLOCK_ID'] = (int)$params['IBLOCK_ID'];
		else
			$params['IBLOCK_ID'] = -1;

		$params['ID'] = CIBlockFindTools::getElementID (
			$params["ID"],
			$params["CODE"],
			false,
			false,
			array(
				"IBLOCK_ID" => $params["IBLOCK_ID"],
				"IBLOCK_LID" => SITE_ID,
				"IBLOCK_ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
				//"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			)
		);

		if($params["ID"] <= 0)
		{
			$this->errors[] = Loc::getMessage("SRP_PRODUCT_ID_REQUIRED");
		}

		return $params;
	}


	/**
	 * @override
	 * @return bool
	 */
	protected function extractDataFromCache()
	{
		if($this->arParams['CACHE_TYPE'] == 'N')
			return false;

		global $USER;
		return !($this->StartResultCache(false, $USER->GetGroups()));
	}

	protected function putDataToCache()
	{
		$this->endResultCache();
	}

	protected function abortDataCache()
	{
		$this->AbortResultCache();
	}

	/**
	 * @override
	 * @return integer[]
	 */
	protected function getProductIds()
	{
		$productIds = array();
		$productIterator = CSaleProduct::GetProductList(
			$this->arParams["ID"],
			$this->arParams["MIN_BUYES"],
			$this->arParams["PAGE_ELEMENT_COUNT"],
			true
		);

		if($productIterator)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->RegisterTag("sale_product_buy");
			while($product = $productIterator->fetch())
			{
				$productIds[] = $product['PARENT_PRODUCT_ID'];
			}
		}
		return $productIds;
	}


	/**
	 * @override
	 * @throws Exception
	 */
	protected function checkModules()
	{
		parent::checkModules();
		if(!$this->isSale)
			throw new SystemException(Loc::getMessage("CVP_SALE_MODULE_NOT_INSTALLED"));
	}

	/**
	 * @override
	 */
	protected function formatResult()
	{
		parent::formatResult();
		if(empty($this->arResult['ITEMS']))
			$this->arResult = array();
		else
			$this->arResult['ID'] = is_array($this->items) ? array_keys($this->items) : array();
	}


}
?>