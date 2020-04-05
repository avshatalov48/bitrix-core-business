<?php
use \Bitrix\Main\Localization\Loc as Loc;
use \Bitrix\Main\SystemException as SystemException;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CBitrixComponent::includeComponentClass("bitrix:catalog.viewed.products");

Loc::loadMessages(__FILE__);

class CCatalogRecommendedProductsComponent extends CCatalogViewedProductsComponent
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
		$params = parent::onPrepareComponentParams($params);
		if(!isset($params["CACHE_TIME"]))
			$params["CACHE_TIME"] = 86400;

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

		if(!isset($params['PROPERTY_LINK']) || !strlen($params['PROPERTY_LINK']) )
		{
			$params['PROPERTY_LINK'] = 'RECOMMEND';
		}
		else
		{
			$params['PROPERTY_LINK'] = trim($params['PROPERTY_LINK']);
		}

		if(!isset($params['OFFERS_PROPERTY_LINK']) || !strlen($params['OFFERS_PROPERTY_LINK']) )
		{
			$params['OFFERS_PROPERTY_LINK'] = 'RECOMMEND';
		}
		else
		{
			$params['OFFERS_PROPERTY_LINK'] = trim($params['OFFERS_PROPERTY_LINK']);
		}

		return $params;
	}


	/**
	 * @override
	 *
	 * @return bool
	 */
	protected function extractDataFromCache()
	{
		if($this->arParams['CACHE_TYPE'] == 'N')
			return false;

		global $USER;

		return !($this->StartResultCache(false, $USER->GetGroups()));
	}

	protected function prepareData()
	{
		if ($this->arParams['ID'] <= 0)
		{
			$this->arParams['ID'] = CIBlockFindTools::getElementID (
				$this->arParams["ID"],
				$this->arParams["CODE"],
				false,
				false,
				array(
					"IBLOCK_ID" => $this->arParams["IBLOCK_ID"],
					"IBLOCK_LID" => SITE_ID,
					"IBLOCK_ACTIVE" => "Y",
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
					"MIN_PERMISSION" => 'R'
				)
			);
		}
		if ($this->arParams['ID'] <= 0)
			throw new SystemException(Loc::getMessage("CATALOG_RECOMMENDED_PRODUCTS_COMPONENT_PRODUCT_ID_REQUIRED"));

		parent::prepareData();
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
	 * Get Linked product ids
	 *
	 * @param $productId
	 * @param $propertyName
	 *
	 * @return array
	 */
	protected function getRecommendedIds($productId, $propertyName)
	{
		if(!$productId)
			return array();

		$elementIterator = CIBlockElement::getList(
			array(),
			array("ID" => $productId),
			false,
			false,
			array("ID", "IBLOCK_ID")
		);

		$linked = array();
		if($element = $elementIterator->getNextElement())
		{
			$props = $element->getProperties();
			$linked = $props[$propertyName]['VALUE'];
		}

		if(empty($linked))
			$linked = -1;

		$productIterator = CIBlockElement::getList(
			array(),
			array("ID" => $linked),
			false,
			array("nTopCount" => $this->arParams['PAGE_ELEMENT_COUNT']),
			array("ID")
		);

		$ids = array();
		while($item = $productIterator->fetch())
			$ids[] =  $item['ID'];

		return $ids;
	}
	/**
	 * @override
	 * @return integer[]
	 */
	protected function getProductIds()
	{
		if(!$this->arParams['ID'])
			return array();

		$info = CCatalogSku::GetProductInfo($this->arParams['ID']);

		if($info) // SKU
		{
			$ids = $this->getRecommendedIds($this->arParams['ID'], $this->arParams['OFFERS_PROPERTY_LINK']);

			if(!count($ids))
			{
				$ids = $this->getRecommendedIds($info['ID'], $this->arParams['PROPERTY_LINK']);
			}
		}
		else
		{
			$ids = $this->getRecommendedIds($this->arParams['ID'], $this->arParams['PROPERTY_LINK']);
		}

		$this->arResult['RECOMMENDED_IDS'] = $ids;

		return $ids;
	}

	/**
	 * Check action variable.
	 *
	 * @param array $params			Component params.
	 * @return string
	 */
	protected function prepareActionVariable($params)
	{
		$actionVariable = (isset($params['ACTION_VARIABLE']) ? trim($params['ACTION_VARIABLE']) : '');
		if ($actionVariable === '' || !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $actionVariable))
			$actionVariable = 'action_crp';
		return $actionVariable;
	}
}