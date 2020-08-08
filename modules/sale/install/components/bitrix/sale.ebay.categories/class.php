<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\SystemException;
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CEbayCategoriesLink extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$requiredParams = array( "CATEGORY_INPUT_NAME", "TOP_CATEGORY_SELECT_NAME",  "BITRIX_CATEGORY_ID", "IBLOCK_ID", "BITRIX_CATEGORY_PROPS_SN");

		foreach($requiredParams as $param)
			if(!isset($arParams[$param]))
				throw new SystemException("Required param ".$param." not defined!");

		return $arParams;
	}

	protected function getMappedEbayCategoryId($bitrixCategoryId)
	{
		$catMapRes = \Bitrix\Sale\TradingPlatform\MapTable::getList(array(
			"filter" => array(
				"ENTITY_ID" => \Bitrix\Sale\TradingPlatform\Ebay\MapHelper::getCategoryEntityId($this->arParams["IBLOCK_ID"]),
				"VALUE_INTERNAL" => $bitrixCategoryId
			)
		));

		if($arMapRes = $catMapRes->fetch())
			$result = $arMapRes["VALUE_EXTERNAL"];
		else
			$result = "";

		return $result;
	}

	protected function getTopCategories()
	{
		$categoriesRes = \Bitrix\Sale\TradingPlatform\Ebay\CategoryTable::getList( array(
			'select' =>array('CATEGORY_ID', 'NAME', 'LEVEL'),
			'order' => array('NAME' =>'ASC'),
			'filter' => array('LEVEL' => 1)
		));

		$topCatList = array();

		while($category = $categoriesRes->fetch())
			$topCatList[$category["CATEGORY_ID"]] = $category["NAME"];

		return $topCatList;
	}

	protected function getCategoryAndParentsInfo($categoryId)
	{
		$categories = Bitrix\Sale\TradingPlatform\Ebay\CategoryTable::getCategoryParents($categoryId);

		foreach($categories as $catLevel => $category)
		{
			$categories[$catLevel]["CHILDREN"] = array();

			$categoriesRes = \Bitrix\Sale\TradingPlatform\Ebay\CategoryTable::getList( array(
				'select' =>array('CATEGORY_ID', 'NAME'),
				'order' => array('NAME' =>'ASC'),
				'filter' => array('PARENT_ID' => $category["CATEGORY_ID"])
			));

			while($cat = $categoriesRes->fetch())
				if($cat["CATEGORY_ID"] != $category["CATEGORY_ID"])
					$categories[$catLevel]["CHILDREN"][$cat["CATEGORY_ID"]] =  $cat;
		}

		return $categories;
	}

	protected function getVariationsValues($iblockId, $ebayCategoryId, $ebayCategoryVariations)
	{
		$result = array();
		$mappedEbayCategoryVar = array();
		$catVarEntId = \Bitrix\Sale\TradingPlatform\Ebay\MapHelper::getCategoryVariationEntityId($iblockId, $ebayCategoryId);

		$catMapVarRes = \Bitrix\Sale\TradingPlatform\MapTable::getList(array(
			"filter" => array(
				"ENTITY_ID" => $catVarEntId
			)
		));

		while($arMapRes = $catMapVarRes->fetch())
			$mappedEbayCategoryVar[$arMapRes["VALUE_EXTERNAL"]] =  $arMapRes["VALUE_INTERNAL"];

		foreach($ebayCategoryVariations as $variation)
			if($variation["REQUIRED"] == "Y" && (!array_key_exists($variation["ID"], $mappedEbayCategoryVar)))
				$result[$variation["ID"]] = "";

		$result = $result+$mappedEbayCategoryVar;
		$result[''] = '';

		return $result;
	}

	public function executeComponent()
	{
		if(!CModule::IncludeModule('sale'))
		{
			ShowError("Module sale not installed!");
			return;
		}

		if(!CModule::IncludeModule('catalog'))
		{
			ShowError("Module catalog not installed!");
			return;
		}

		$this->arResult["BITRIX_CATEGORY_ID"] = isset($this->arParams["BITRIX_CATEGORY_ID"]) ? intval($this->arParams["BITRIX_CATEGORY_ID"]) : 0;
		$this->arResult["IBLOCK_ID"] =isset($this->arParams["IBLOCK_ID"]) ? $this->arParams["IBLOCK_ID"] : 0;

		if(isset($this->arParams["EBAY_CATEGORY_ID"]))
			$this->arResult["EBAY_CATEGORY_ID"] = $this->arParams["EBAY_CATEGORY_ID"];
		else
			$this->arResult["EBAY_CATEGORY_ID"] = $this->getMappedEbayCategoryId($this->arResult["BITRIX_CATEGORY_ID"]);

		$this->arResult["TOP_CATEGORIES_LIST"] = $this->getTopCategories();
		$this->arResult["VARIATIONS_BLOCK_ID"] = 'SALE_EBAY_CAT_'.$this->arResult["EBAY_CATEGORY_ID"].'_VARIATIONS';

		$siteRes= CIBlock::GetSite($this->arResult["IBLOCK_ID"]); //todo: It can be many sites for one iblock.

		if($site = $siteRes->Fetch())
			$this->arResult["SITE_ID"] = $site["LID"];
		else
			$this->arResult["SITE_ID"] = "";

		if($this->arResult["EBAY_CATEGORY_ID"] <> '')
		{
			$this->arResult["CATEGORY_AND_PARENTS_INFO"] = $this->getCategoryAndParentsInfo($this->arResult["EBAY_CATEGORY_ID"]);

			if(isset($this->arResult["CATEGORY_AND_PARENTS_INFO"][1]["CATEGORY_ID"]))
				$this->arResult["TOP_CATEGORY_ID"] = $this->arResult["CATEGORY_AND_PARENTS_INFO"][1]["CATEGORY_ID"];

			$this->arResult["EBAY_CATEGORY_VARIATIONS"] = \Bitrix\Sale\TradingPlatform\Ebay\Helper::getEbayCategoryVariations($this->arResult["EBAY_CATEGORY_ID"], $this->arResult["SITE_ID"]);

			if(isset($this->arParams["VARIATIONS_VALUES"]))
				$this->arResult["VARIATIONS_VALUES"] = $this->arParams["VARIATIONS_VALUES"];
			else
				$this->arResult["VARIATIONS_VALUES"] = $this->getVariationsValues($this->arResult["IBLOCK_ID"], $this->arResult["EBAY_CATEGORY_ID"], $this->arResult["EBAY_CATEGORY_VARIATIONS"]);
		}
		else
		{
			$this->arResult["VARIATIONS_VALUES"] = array('' => '');
			$this->arResult["EBAY_CATEGORY_VARIATIONS"] = array();
		}

		$categoryProps = \CIBlockSectionPropertyLink::GetArray($this->arParams["IBLOCK_ID"], $this->arParams["BITRIX_CATEGORY_ID"]);

		$rsProps =  \CIBlockProperty::GetList(array(
			"SORT"=>"ASC",
			'ID' => 'ASC',
		), array(
			"IBLOCK_ID" => $this->arParams["IBLOCK_ID"],
			"CHECK_PERMISSIONS" => "N",
			"ACTIVE"=>"Y",
			"MULTIPLE" => "N"
		));

		while ($arProp = $rsProps->Fetch())
		{
			if(isset($categoryProps[$arProp["ID"]]))
			{
				$this->arResult["CATEGORY_PROPS"][$arProp["ID"]] = $categoryProps[$arProp["ID"]];
				$this->arResult["CATEGORY_PROPS"][$arProp["ID"]]["NAME"] = $arProp["NAME"];
			}
		}

		$this->arResult["IBLOCK_IDS"] = array(
			$this->arParams["IBLOCK_ID"] => Loc::getMessage("SALE_EBAY_SEC_CATEGORY_PROP"),
		);

		$arOffers = \CCatalogSKU::GetInfoByProductIBlock($this->arParams["IBLOCK_ID"]);

		if(is_array($arOffers) && !empty($arOffers))
		{
			$this->arResult["OFFERS_IBLOCK_ID"] = $arOffers["IBLOCK_ID"];
			$offerProps = \CIBlockSectionPropertyLink::GetArray($arOffers["IBLOCK_ID"], $this->arParams["BITRIX_CATEGORY_ID"]);

			$rsProps =  \CIBlockProperty::GetList(array(
				"SORT"=>"ASC",
				'ID' => 'ASC',
			), array(
				"IBLOCK_ID" => $arOffers["IBLOCK_ID"],
				"CHECK_PERMISSIONS" => "N",
				"ACTIVE"=>"Y",
				"MULTIPLE" => "N"
			));

			while ($arProp = $rsProps->Fetch())
			{
				if(isset($offerProps[$arProp["ID"]]))
				{
					$this->arResult["CATEGORY_OFFERS_PROPS"][$arProp["ID"]] = $offerProps[$arProp["ID"]];
					$this->arResult["CATEGORY_OFFERS_PROPS"][$arProp["ID"]]["NAME"] = $arProp["NAME"];
				}
			}

			$this->arResult["IBLOCK_IDS"][$arOffers["IBLOCK_ID"]] = Loc::getMessage("SALE_EBAY_SEC_OFFERS_PROP");
		}

		$this->IncludeComponentTemplate();

	}
}