<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/catalog/prolog.php");

global $APPLICATION;
global $DB;
global $USER;

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_view')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

CModule::IncludeModule("catalog");
IncludeModuleLangFile(__FILE__);

$boolSubscribe = false;
if (isset($_REQUEST['subscribe']) && 'Y' == $_REQUEST['subscribe'])
	$boolSubscribe = true;

ClearVars("str_iblock_");
ClearVars("s_");
$APPLICATION->AddHeadScript('/bitrix/js/catalog/catalog_menu.js');

/**
 * @param $userId
 * @param $lid
 * @param $productId
 * @param string $productName
 * @param string $currency
 * @param array $arProduct
 * @return array|bool
 */
function GetProductSku($userId, $lid, $productId, $productName = '', $currency = '', $arProduct = array())
{
	$userId = intval($userId);

	$productId = intval($productId);
	if($productId <= 0)
		return false;

	$lid = trim($lid);
	if($lid === '')
		return false;

	$productName = trim($productName);
	$arResult = array();

	static $arCacheGroups = array();

	if(!isset($arCacheGroups[$userId]))
		$arCacheGroups[$userId] = CUser::GetUserGroup($userId);
	$arGroups = $arCacheGroups[$userId];

	if (!isset($arProduct["IBLOCK_ID"]) || 0 >= intval($arProduct["IBLOCK_ID"]))
		$arProduct["IBLOCK_ID"] = CIBlockElement::GetIBlockByID($arProduct["IBLOCK_ID"]);

	static $arOffersIblock = array();
	if(!isset($arOffersIblock[$arProduct["IBLOCK_ID"]]))
	{
		$mxResult = CCatalogSKU::GetInfoByProductIBlock($arProduct["IBLOCK_ID"]);
		if(is_array($mxResult))
			$arOffersIblock[$arProduct["IBLOCK_ID"]] = $mxResult["IBLOCK_ID"];
	}

	if($arOffersIblock[$arProduct["IBLOCK_ID"]] > 0)
	{

		static $arCacheOfferProperties = array();
		if(!is_set($arCacheOfferProperties[$arOffersIblock[$arProduct["IBLOCK_ID"]]]))
		{
			$dbOfferProperties = CIBlockProperty::GetList(
				array('SORT' => 'ASC', 'ID' => 'ASC'),
				array('IBLOCK_ID' => $arOffersIblock[$arProduct["IBLOCK_ID"]], 'ACTIVE' => 'Y', "!XML_ID" => "CML2_LINK")
			);
			while($arOfferProperties = $dbOfferProperties->Fetch())
			{
				if ('F' == $arOfferProperties['PROPERTY_TYPE'])
					continue;
				$arCacheOfferProperties[$arOffersIblock[$arProduct["IBLOCK_ID"]]][] = $arOfferProperties;
			}
		}
		$arOfferProperties = $arCacheOfferProperties[$arOffersIblock[$arProduct["IBLOCK_ID"]]];


		$arIblockOfferProps = array();
		$arIblockOfferPropsFilter = array();
		if(is_array($arOfferProperties))
		{
			foreach($arOfferProperties as $val)
			{
				$arIblockOfferProps[] = array("CODE" => $val["CODE"], "NAME" => $val["NAME"]);
				$arIblockOfferPropsFilter[] = $val["CODE"];
			}
		}

		$arOffers = CIBlockPriceTools::GetOffersArray(
			$arProduct["IBLOCK_ID"],
			$productId,
			array("ID" => "DESC"),
			array("NAME", "EXTERNAL_ID"),
			$arIblockOfferPropsFilter,
			0,
			array(),
			1,
			array(),
			$userId,
			$lid
		);

		$arSku = array();
		$arSkuId = array();
		$arImgSku = array();
		foreach($arOffers as $arOffer)
			$arSkuId[] = $arOffer['ID'];
		if(!empty($arSkuId))
		{
			$res = CIBlockElement::GetList(array(), array("ID" => $arSkuId), false, false, array("ID", "IBLOCK_ID", "NAME", "PREVIEW_PICTURE", "DETAIL_PICTURE", "DETAIL_PAGE_URL", "ACTIVE"));
			while($arOfferImg = $res->GetNext())
				$arImgSku[$arOfferImg["ID"]] = $arOfferImg;
		}
		$arOffersId = array();
		foreach($arOffers as $arOffer)
		{
			$arOffersId[] = $arOffer['ID'];
		}

		$dbCatalogProduct = CCatalogProduct::GetList(array(), array("ID" => $arOffersId));

		while($arCatalogProduct = $dbCatalogProduct->fetch())
			$arCatalogProductResult[$arCatalogProduct["ID"]] = $arCatalogProduct;

		foreach($arOffers as $arOffer)
		{
			$arSkuTmp = array();
			$active = '';
			$arOffer["CAN_BUY"] = "N";
			$arCatalogProduct = $arCatalogProductResult[$arOffer["ID"]];
			if(!empty($arCatalogProduct))
			{
				if($arCatalogProduct["CAN_BUY_ZERO"] != "Y" && ($arCatalogProduct["QUANTITY_TRACE"]=="Y" && doubleval($arCatalogProduct["QUANTITY"])<=0))
					$arOffer["CAN_BUY"] = "N";
				else
					$arOffer["CAN_BUY"] = "Y";
			}

			$arSkuTmp["ImageUrl"] = '';
			if($arOffer["CAN_BUY"] == "Y")
			{
				if(isset($arImgSku[$arOffer['ID']]) && !empty($arImgSku[$arOffer['ID']]))
				{
					if('' == $productName)
						$productName = $arImgSku[$arOffer['ID']]["~NAME"];

					$active = $arImgSku[$arOffer['ID']]["ACTIVE"];

					if($arImgSku[$arOffer['ID']]["PREVIEW_PICTURE"] != "")
						$arSkuTmp["PREVIEW_PICTURE"] = $arImgSku[$arOffer['ID']]["PREVIEW_PICTURE"];

					if($arImgSku[$arOffer['ID']]["DETAIL_PICTURE"] != "")
						$arSkuTmp["DETAIL_PICTURE"] = $arImgSku[$arOffer['ID']]["DETAIL_PICTURE"];
				}
			}
			foreach($arIblockOfferProps as $arCode)
			{
				if(is_array($arCode) && isset($arOffer["PROPERTIES"][$arCode["CODE"]]))
				{
					if (isset($arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]))
					{
						$mxValues = '';
						if ('E' == $arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]['PROPERTY_TYPE'])
						{
							if (!empty($arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]['LINK_ELEMENT_VALUE']))
							{
								$mxValues = array();
								foreach ($arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]['LINK_ELEMENT_VALUE'] as $arTempo)
									$mxValues[] = $arTempo['NAME'].' ['.$arTempo['ID'].']';
							}
						}
						elseif ('G' == $arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]['PROPERTY_TYPE'])
						{
							if (!empty($arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]['LINK_SECTION_VALUE']))
							{
								$mxValues = array();
								foreach ($arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]['LINK_SECTION_VALUE'] as $arTempo)
									$mxValues[] = $arTempo['NAME'].' ['.$arTempo['ID'].']';
							}
						}
						if (empty($mxValues))
						{
							$mxValues = $arOffer["DISPLAY_PROPERTIES"][$arCode["CODE"]]["DISPLAY_VALUE"];
						}
						$arSkuTmp[] = strip_tags(is_array($mxValues) ? implode("/ ", $mxValues) : $mxValues);
					}
					else
					{
						$arSkuTmp[] = '';
					}
				}
			}

			if(!empty($arCatalogProduct))
			{
				$arSkuTmp["BALANCE"] = $arCatalogProduct["QUANTITY"];
				$arSkuTmp["WEIGHT"] = $arCatalogProduct["WEIGHT"];
				$arSkuTmp["BARCODE_MULTI"] = $arCatalogProduct["BARCODE_MULTI"];
			}
			else
			{
				$arSkuTmp["BALANCE"] = 0;
				$arSkuTmp["WEIGHT"] = 0;
				$arSkuTmp["BARCODE_MULTI"] = 'N';
			}

			$arSkuTmp["USER_ID"] = $userId;
			$arSkuTmp["ID"] = $arOffer["ID"];
			$arSkuTmp["TYPE"] = $arOffer["CATALOG_TYPE"];
			$arSkuTmp["NAME"] = CUtil::JSEscape($arOffer["NAME"]);
			$arSkuTmp["PRODUCT_NAME"] = CUtil::JSEscape(htmlspecialcharsbx($productName));
			$arSkuTmp["PRODUCT_ID"] = $productId;
			$arSkuTmp["LID"] = CUtil::JSEscape($lid);
			$arSkuTmp["CAN_BUY"] = $arOffer["CAN_BUY"];
			$arSkuTmp["ACTIVE"] = $active;
			$arSkuTmp["EXTERNAL_ID"] = $arOffer['EXTERNAL_ID'];

			$arSku[] = $arSkuTmp;
		}
		if((!is_array($arIblockOfferProps) || empty($arIblockOfferProps)) && is_array($arSku) && !empty($arSku))
		{
			$arIblockOfferProps[0] = array("CODE" => "TITLE", "NAME" => GetMessage("SKU_TITLE"));
			foreach($arSku as $key => $val)
				$arSku[$key][0] = $val["NAME"];
		}

		$arResult["SKU_ELEMENTS"] = $arSku;
		$arResult["SKU_PROPERTIES"] = $arIblockOfferProps;
		$arResult["OFFERS_IBLOCK_ID"] = $arOffersIblock[$arProduct["IBLOCK_ID"]];
	}

	return $arResult;
}

/**
 * @param $name
 * @param $property_fields
 * @param $values
 * @return bool|string
 */
function _ShowGroupPropertyFieldList($name, $property_fields, $values)
{
	if(!is_array($values)) $values = Array();

	static $linkIblockId;
	static $sections = null;
	$res = "";
	$result = "";
	$bWas = false;

	$ttl = 10000;
	$cache_id = 'catalog_store_sections';
	$obCache = new CPHPCache;
	$cache_dir = '/bx/catalog_store_sections';

	if(!$linkIblockId || ($property_fields["LINK_IBLOCK_ID"] != $linkIblockId))
	{
		$linkIblockId = $property_fields["LINK_IBLOCK_ID"];

		if(intval($linkIblockId) <= 0)
			return false;

		$obCache->Clean($cache_id, $cache_dir);
	}

	if($obCache->InitCache($ttl, $cache_id, $cache_dir))
		$res = $obCache->GetVars();
	else
	{
		if($sections === null)
			$sections = CIBlockSection::GetTreeList(Array("IBLOCK_ID" => $linkIblockId));
		while($ar = $sections->GetNext())
		{
			$res .= '<option value="'.$ar["ID"].'"';
			if(in_array($ar["ID"], $values))
			{
				$bWas = true;
				$res .= ' selected';
			}
			$res .= '>'.str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"].'</option>';
		}
		if($obCache->StartDataCache())
			$obCache->EndDataCache($res);
	}


	$result .= '<select name="'.$name.'[]" size="'.($property_fields["MULTIPLE"]=="Y" ? "5":"1").'" '.($property_fields["MULTIPLE"]=="Y"?"multiple":"").'>';
	$result .= '<option value=""'.(!$bWas?' selected':'').'>'.GetMessage("SPS_A_PROP_NOT_SET").'</option>';
	$result .= $res;
	$result .= '</select>';
	return $result;
}

/**
 * @param $ID
 * @return mixed
 */
function GetElementName($ID)
{
	$ID = (int)$ID;
	if ($ID <= 0)
		return false;
	static $cache = array();
	if (!isset($cache[$ID]))
	{
		$rsElement = CIBlockElement::GetList(array(), array("ID" => $ID, "SHOW_HISTORY" => "Y"), false, false, array("ID","IBLOCK_ID","NAME"));
		$cache[$ID] = $rsElement->GetNext();
	}
	return $cache[$ID];
}
function GetSectionName($ID)
{
	$ID = (int)$ID;
	if ($ID <= 0)
		return false;
	static $cache = array();
	if (!isset($cache[$ID]))
	{
		$rsSection = CIBlockSection::GetList(array(), array("ID"=>$ID), false, array("ID","IBLOCK_ID","NAME"));
		$cache[$ID] = $rsSection->GetNext();
	}
	return $cache[$ID];
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_REQUEST["BARCODE_AJAX"]) && $_REQUEST["BARCODE_AJAX"] === 'Y' && check_bitrix_sessid())
{
	CUtil::JSPostUnescape();
	$barcode = (isset($_REQUEST["BARCODE"])) ? htmlspecialcharsbx($_REQUEST["BARCODE"]) : "";
	$arBarCode = array();
	$arElement = array();
	$elementId = 0;

	if(strlen($barcode) > 0)
	{
		$rsBarCode = CCatalogStoreBarCode::getList(array(), array("BARCODE" => $barcode), false, false, array('PRODUCT_ID'));
		$arBarCode = $rsBarCode->Fetch();
	}

	if(isset($arBarCode["PRODUCT_ID"]))
	{
		$elementId = intval($arBarCode["PRODUCT_ID"]);
	}

	if($elementId > 0)
	{
		$dbResultList = CCatalogProduct::getList(array(), array('ID' => $elementId), false,	false, array('TYPE'));
		$arItems = $dbResultList->Fetch();
		$arParams = array("id" => $elementId, "barcode" => $barcode);
		if(isset($arItems["TYPE"]))
			$arParams['type'] = $arItems["TYPE"];
		$result = CUtil::PhpToJSObject($arParams);
		echo $result;
	}
	exit;
}

CModule::IncludeModule('fileman');
$minImageSize = array("W" => 1, "H"=>1);
$maxImageSize = array(
	"W" => COption::GetOptionString("iblock", "list_image_size"),
	"H" => COption::GetOptionString("iblock", "list_image_size"),
);

$adminMenu = new CCatalogMenu();
$adminMenu->Init('iblock');

$addDefault = "Y";

$iblockId = intval($_REQUEST["IBLOCK_ID"]);

$lid = (isset($_REQUEST['LID']) ? (string)$_REQUEST['LID'] : '');

$func_name = preg_replace("/[^a-zA-Z0-9_\-\.]/is", "", $_REQUEST["func_name"]);
$caller = (isset($_REQUEST['caller']) ? (string)$_REQUEST['caller'] : '');
$buyerId = intval($USER->GetID());
$sTableID = "tbl_product_search";
if($caller)
{
	if(!isset($_REQUEST["set_filter"]))
	{
		$addURLParam = "&set_filter=Y";
		if(!isset($_REQUEST["IBLOCK_ID"]))
		{
			$addURLParam .= CUserOptions::getOption("catalog", "product_search_".$caller, "", $buyerId);
			LocalRedirect($GLOBALS['APPLICATION']->GetCurPageParam().$addURLParam);
		}
	}

	$addURLParam = CUserOptions::getOption("catalog", "product_search_storeDocs", "", $userId);
	$sTableID .= '_'.$caller;
}

$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);

$dbIBlock = CIBlock::GetByID($iblockId);
if(!($arIBlock = $dbIBlock->Fetch()))
{
	$arFilterTmp = array("MIN_PERMISSION" => "R");

	if($lid !== '')
		$arFilterTmp["LID"] = $lid;

	$arCatalogFilter = array();
	if ($boolSubscribe)
		$arCatalogFilter['SUBSCRIPTION'] = 'Y';

	$dbItem = CCatalog::GetList(
		array(),
		$arCatalogFilter,
		false,
		false,
		array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID')
	);
	while($arItems = $dbItem->Fetch())
		$arFilterTmp["ID"][] = $arItems["IBLOCK_ID"];

	foreach(GetModuleEvents("sale", "OnProductSearchFormIBlock", true) as $arEvent)
	{
		$arFilterTmp = ExecuteModuleEventEx($arEvent, array($arFilterTmp));
	}

	$arFilterTmp['ACTIVE'] = 'Y';

	$dbIBlock = CIBlock::GetList(Array("ID" => "ASC"), $arFilterTmp);
	if($arIBlock = $dbIBlock->Fetch())
		$iblockId = intval($arIBlock["ID"]);
	else
	{
		unset($arFilterTmp["LID"]);
		$dbIBlock = CIBlock::GetList(Array("ID" => "ASC"), $arFilterTmp);
		if($arIBlock = $dbIBlock->Fetch())
			$iblockId = intval($arIBlock["ID"]);
	}
}

$bBadBlock = !CIBlockRights::UserHasRightTo($iblockId, $iblockId, "element_read");

$arBuyerGroups = CUser::GetUserGroup($buyerId);

$storeFromId = intval($_REQUEST["STORE_FROM_ID"]);

$QUANTITY = intval($QUANTITY);
if($QUANTITY <= 0)
	$QUANTITY = 1;

if(!$bBadBlock)
{
	$arFilterFields = array(
		"IBLOCK_ID",
		"filter_section",
		"filter_subsections",
		"filter_id_start",
		"filter_id_end",
		"filter_timestamp_from",
		"filter_timestamp_to",
		"filter_active",
		"filter_intext",
		"filter_product_name",
		"filter_xml_id",
		"filter_code"
	);
	$lAdmin->InitFilter($arFilterFields);

	if($iblockId <= 0)
	{
		$dbItem = CCatalog::GetList(array(), array("IBLOCK_TYPE_ID" => "catalog"));
		$arItems = $dbItem->Fetch();
		$iblockId = intval($arItems["ID"]);
	}

	//filter props
	$dbrFProps = CIBlockProperty::GetList(
		array(
			"SORT" => "ASC",
			"NAME" => "ASC"
		),
		array(
			"IBLOCK_ID" => $iblockId,
			"ACTIVE" => "Y",
			"FILTRABLE" => "Y",
			"!PROPERTY_TYPE" => "F",
			"CHECK_PERMISSIONS" => "N",
		)
	);

	$arProps = $arPrices = array();
	while ($arProp = $dbrFProps->GetNext())
	{
		$arProp["PROPERTY_USER_TYPE"] = (!empty($arProp["USER_TYPE"]) ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
		$arProps[] = $arProp;
	}

	//filter sku props
	$arSKUProps = array();
	$arCatalog = CCatalogSKU::GetInfoByProductIBlock($iblockId);

	if (!empty($arCatalog))
	{
		$dbrFProps = CIBlockProperty::GetList(
			array(
				"SORT" => "ASC",
				"NAME" => "ASC"
			),
			array(
				"IBLOCK_ID" => $arCatalog["IBLOCK_ID"],
				"ACTIVE" => "Y",
				"FILTRABLE" => "Y",
				"!PROPERTY_TYPE" => "F",
				"CHECK_PERMISSIONS" => "N",
			)
		);

		while($arProp = $dbrFProps->GetNext())
		{
			if($arCatalog['SKU_PROPERTY_ID'] == $arProp['ID'])
				continue;
			$arProp["PROPERTY_USER_TYPE"] = (!empty($arProp["USER_TYPE"]) ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
			$arSKUProps[] = $arProp;
		}
	}

	$arFilter = array(
		"IBLOCK_ID" => $iblockId,
		"SECTION_ID" => $_REQUEST['filter_section'],
		"ACTIVE" => ($orderForm ? "Y" : $_REQUEST['filter_active']),
		"WF_PARENT_ELEMENT_ID" => false,
	);
	if('' != trim($_REQUEST['filter_product_name']))
		$arFilter["%NAME"] = $_REQUEST['filter_product_name'];
	if('' != trim($_REQUEST['filter_intext']))
		$arFilter["%SEARCHABLE_CONTENT"] = $_REQUEST['filter_intext'];
	$arFilter["SHOW_NEW"] = "Y";

	if(!empty($arProps))
	{
		foreach($arProps as $arProp)
		{
			$value = ${"filter_el_property_".$arProp["ID"]};

			if(array_key_exists("AddFilterFields", $arProp["PROPERTY_USER_TYPE"]))
			{
				call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
					$arProp,
					array("VALUE" => "filter_el_property_".$arProp["ID"]),
					&$arFilter,
					&$filtered,
				));
			}
			elseif(is_array($value) || strlen($value))
			{
				if($value === "NOT_REF")
					$value = false;
				$arFilter["?PROPERTY_".$arProp["ID"]] = $value;
			}
		}
	}

	if(!empty($arSKUProps))
	{
		$arSubQuery = array("IBLOCK_ID" => $arCatalog['IBLOCK_ID']);

		for($i = 0, $intPropCount = count($arSKUProps); $i < $intPropCount; $i++)
		{
			if(('Y' == $arSKUProps[$i]["FILTRABLE"]) && ('F' != $arSKUProps[$i]["PROPERTY_TYPE"]) && ($arCatalog['SKU_PROPERTY_ID'] != $arSKUProps[$i]["ID"]))
			{
				if(array_key_exists("AddFilterFields", $arSKUProps[$i]["PROPERTY_USER_TYPE"]))
				{
					call_user_func_array($arSKUProps[$i]["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
						$arSKUProps[$i],
						array("VALUE" => "filter_sub_el_property_".$arSKUProps[$i]["ID"]),
						&$arSubQuery,
						&$filtered,
					));
				}
				else
				{
					$value = ${"filter_sub_el_property_".$arSKUProps[$i]["ID"]};
					if(strlen($value) || is_array($value))
					{
						if($value === "NOT_REF")
							$value = false;
						$arSubQuery["?PROPERTY_".$arSKUProps[$i]["ID"]] = $value;
					}
				}
			}
		}
	}

	if(!empty($arSKUProps) && sizeof($arSubQuery) > 1)
	{
		$arFilter['ID'] = CIBlockElement::SubQuery('PROPERTY_'.$arCatalog['SKU_PROPERTY_ID'], $arSubQuery);
	}

	if(intval($_REQUEST['filter_section']) < 0 || strlen($_REQUEST['filter_section']) <= 0)
		unset($arFilter["SECTION_ID"]);
	elseif($_REQUEST['filter_subsections'] == "Y")
	{
		if($arFilter["SECTION_ID"]==0)
			unset($arFilter["SECTION_ID"]);
		else
			$arFilter["INCLUDE_SUBSECTIONS"] = "Y";
	}

	if(!empty($_REQUEST["filter_id_start"])) $arFilter[">=ID"] = $_REQUEST["filter_id_start"];
	if(!empty($_REQUEST["filter_id_end"])) $arFilter["<=ID"] = $_REQUEST["filter_id_end"];
	if(!empty($_REQUEST["filter_timestamp_from"])) $arFilter["DATE_MODIFY_FROM"] = $_REQUEST["filter_timestamp_from"];
	if(!empty($_REQUEST["filter_timestamp_to"])) $arFilter["DATE_MODIFY_TO"] = $_REQUEST["filter_timestamp_to"];
	if(!empty($_REQUEST["filter_xml_id"])) $arFilter["XML_ID"] = $_REQUEST["filter_xml_id"];
	if(!empty($_REQUEST["filter_code"])) $arFilter["CODE"] = $_REQUEST["filter_code"];

	//select subsection
	if($arFilter["SECTION_ID"] > 0)
		$arFilter["INCLUDE_SUBSECTIONS"] = "Y";

	$arNavParams = array("nPageSize" => CAdminResult::GetNavSize($sTableID));

	$dbResultList = CIBlockElement::GetList(
		array($_REQUEST["by"] => $_REQUEST["order"]),
		$arFilter,
		false,
		$arNavParams
	);

	$dbResultList = new CAdminResult($dbResultList, $sTableID);
	$dbResultList->NavStart();

	$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("sale_prod_search_nav")));
	$balanceTitle = ($storeFromId > 0) ? GetMessage("SOPS_BALANCE") : GetMessage("SOPS_BALANCE2");
	$arHeaders = array(
		array("id" => "ID", "content" => "ID", "sort" => "id", "default" => true),
		array("id" => "ACTIVE", "content" => GetMessage("SOPS_ACTIVE"), "sort" => "ACTIVE", "default" => true),
		array("id" => "DETAIL_PICTURE", "default" => true, "content" => GetMessage("SPS_FIELD_DETAIL_PICTURE"), "align" => "center"),
		array("id" => "NAME", "content" => GetMessage("SPS_NAME"), "sort" => "name", "default" => true),
		array("id" => "QUANTITY", "content" => GetMessage("SOPS_QUANTITY"), "default" => true),
		array("id" => "BALANCE", "content" => $balanceTitle, "sort" => "", "default" => true, "align" => "right"),
	);
	$arHeaders[] = array("id" => "CODE", "content" => GetMessage("SPS_FIELD_CODE"), "sort" => "code");
	$arHeaders[] = array("id" => "EXTERNAL_ID", "content" => GetMessage("SPS_FIELD_XML_ID"), "sort" => "external_id");
	$arHeaders[] = array("id" => "SHOW_COUNTER", "content" => GetMessage("SPS_FIELD_SHOW_COUNTER"), "sort" => "show_counter", "align" => "right");
	$arHeaders[] = array("id" => "SHOW_COUNTER_START", "content" => GetMessage("SPS_FIELD_SHOW_COUNTER_START"), "sort" => "show_counter_start", "align" => "right");
	$arHeaders[] = array("id" => "PREVIEW_PICTURE", "content" => GetMessage("SPS_FIELD_PREVIEW_PICTURE"), "align" => "right");
	$arHeaders[] = array("id" => "PREVIEW_TEXT", "content" => GetMessage("SPS_FIELD_PREVIEW_TEXT"));
	$arHeaders[] = array("id" => "DETAIL_TEXT", "content" => GetMessage("SPS_FIELD_DETAIL_TEXT"));
	foreach($arProps as $prop)
	{
		$arHeaders[] = array("id" => "PROPERTY_".$prop['ID'], "content" => $prop['NAME'], "align" => ($prop["PROPERTY_TYPE"] == 'N'?"right":"left"), "sort" => ($prop["MULTIPLE"] != 'Y'? "PROPERTY_".$prop['ID'] : ""));
	}

	$rsPrice = CCatalogGroup::GetListEx(array("SORT"=>"ASC"), array(), false, false, array("ID", "NAME", "NAME_LANG", "BASE"));
	while($price = $rsPrice->Fetch())
	{
		$arPrices[] = $price;
		$arHeaders[] = array("id" => "PRICE".$price["ID"], "content" => htmlspecialcharsex(!empty($price["NAME_LANG"]) ? $price["NAME_LANG"] : $price["NAME"]), "default" => ($price["BASE"] == 'Y') ? true : false,);
	}
	$arHeaders[] = array("id" => "ACT", "content" => GetMessage("SPS_FIELD_ACTION"), "default" => true);

	$lAdmin->AddHeaders($arHeaders);

	$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
	if(!in_array('ACT', $arSelectedFields))
		$arSelectedFields[] = 'ACT';

	$arSelectedProps = array();
	foreach($arProps as $prop)
	{
		if($key = array_search("PROPERTY_".$prop['ID'], $arSelectedFields))
		{
			$arSelectedProps[] = $prop;
			$arSelect[$prop['ID']] = Array();
			$props = CIBlockProperty::GetPropertyEnum($prop['ID']);
			while($res = $props->Fetch())
				$arSelect[$prop['ID']][$res["ID"]] = $res["VALUE"];
			unset($arSelectedFields[$key]);
		}
	}

	if(!in_array("ID", $arSelectedFields))
		$arSelectedFields[] = "ID";

	$arSelectedFields[] = "LANG_DIR";
	$arSelectedFields[] = "LID";
	$arSelectedFields[] = "WF_PARENT_ELEMENT_ID";
	$arSelectedFields[] = "ACT";

	if(in_array("LOCKED_USER_NAME", $arSelectedFields))
		$arSelectedFields[] = "WF_LOCKED_BY";
	if(in_array("USER_NAME", $arSelectedFields))
		$arSelectedFields[] = "MODIFIED_BY";
	if(in_array("CREATED_USER_NAME", $arSelectedFields))
		$arSelectedFields[] = "CREATED_BY";
	if(in_array("PREVIEW_TEXT", $arSelectedFields))
		$arSelectedFields[] = "PREVIEW_TEXT_TYPE";
	if(in_array("DETAIL_TEXT", $arSelectedFields))
		$arSelectedFields[] = "DETAIL_TEXT_TYPE";

	$arSelectedFields[] = "LOCK_STATUS";
	$arSelectedFields[] = "WF_NEW";
	$arSelectedFields[] = "WF_STATUS_ID";
	$arSelectedFields[] = "DETAIL_PAGE_URL";
	$arSelectedFields[] = "SITE_ID";
	$arSelectedFields[] = "CODE";
	$arSelectedFields[] = "EXTERNAL_ID";
	$arSelectedFields[] = "NAME";
	$arSelectedFields[] = "XML_ID";
	$arSelectedFields[] = "IBLOCK_ID";

	$arDiscountCoupons = array();

	$arSku = array();
	$OfferIblockId = "";

	CCatalogDiscountSave::Disable();
	$arCatalogProductResult = array();
	$arPricesResult = array();
	$arCatalogProduct = $arItemsResult = $arPricesResult = array();
	while($arItems = $dbResultList->Fetch())
	{
		$arCatalogProduct[] = $arItems["ID"];
		$arItems['DETAIL_PAGE_URL'] = CIBlock::ReplaceDetailUrl($arItems['DETAIL_PAGE_URL'], $arItems, false, "E");
		$arItemsResult[$arItems['ID']] = $arItems;
	}

	if (!empty($arCatalogProduct))
	{
		foreach($arPrices as $price)
		{
			$dbPrice = CPrice::GetList(array(), array('PRODUCT_ID' => $arCatalogProduct, 'CATALOG_GROUP_ID' => $price['ID']), false, false, array('PRODUCT_ID', 'PRICE'));
			while($arPrice = $dbPrice->fetch())
			{
				$arPricesResult[$price['ID']][$arPrice["PRODUCT_ID"]] = $arPrice["PRICE"];
			}
		}

		$dbCatalogProduct = CCatalogProduct::GetList(array(), array("ID" => $arCatalogProduct));
		while($oneProduct = $dbCatalogProduct->fetch())
			$arCatalogProductResult[$oneProduct["ID"]] = $oneProduct;
		$existSku = CCatalogSKU::getExistOffers($arCatalogProduct);
		foreach ($existSku as $productID => $existOffers)
		{
			if (isset($arCatalogProductResult[$productID]))
				$arCatalogProductResult[$productID] = array();
			$arCatalogProductResult[$productID]['EXIST_SKU'] = $existOffers;
		}
		unset($existOffers, $productID, $existSku);
	}

	foreach($arItemsResult as $productId => $arItems)
	{
		$arCatalogProduct = array(
			'EXIST_SKU' => false
		);
		if (isset($arCatalogProductResult[$productId]))
			$arCatalogProduct = $arCatalogProductResult[$productId];
		//only for store documents skip sets
		if ($caller == "storeDocs" && isset($arCatalogProduct["TYPE"]) && $arCatalogProduct["TYPE"] == CCatalogProduct::TYPE_SET)
			continue;
		$row = &$lAdmin->AddRow($arItems["ID"], $arItems);
		$isProductExistSKU = false;
		if (!$boolSubscribe)
			$isProductExistSKU = $arCatalogProduct['EXIST_SKU'];
		$arResult = array();
		if ($isProductExistSKU)
		{
			$arResult = GetProductSku($buyerId, $lid, $arItems["ID"], $arItems["NAME"], '', $arItems);
			$arSKUId = $arSKUPricesResult = array();
			if (isset($arResult["SKU_ELEMENTS"]) && !empty($arResult["SKU_ELEMENTS"]) && is_array($arResult["SKU_ELEMENTS"]))
			{
				foreach($arResult["SKU_ELEMENTS"] as $sku)
					$arSKUId[] = $sku["ID"];
			}

			foreach($arPrices as $price)
			{
				$dbPrice = CPrice::getList(array(), array('PRODUCT_ID' => $arSKUId, 'CATALOG_GROUP_ID' => $price['ID']), false, false, array('PRODUCT_ID', 'PRICE'));
				while($arPrice = $dbPrice->fetch())
				{
					$arSKUPricesResult[$price['ID']][$arPrice["PRODUCT_ID"]] = $arPrice["PRICE"];
				}
			}

			$active = ($arItems["ACTIVE"] == 'Y' ? GetMEssage('SPS_PRODUCT_ACTIVE') : GetMEssage('SPS_PRODUCT_NO_ACTIVE'));
			if (!empty($arResult["SKU_ELEMENTS"]))
			{
				$OfferIblockId = $arResult["OFFERS_IBLOCK_ID"];
				$row->AddField("ACTIVE", $active);
				$row->AddField("ACT", '<input type="button" onclick="fShowSku(this, '.CUtil::PhpToJSObject($arResult["SKU_ELEMENTS"]).');" name="btn_show_sku_'.$arItems["ID"].'" value="'.GetMessage("SPS_SKU_SHOW").'">');
				$row->AddViewField("PREVIEW_PICTURE", CFileInput::Show('NO_FIELDS['.$arItems['ID'].'][PREVIEW_PICTURE]', $arItems['PREVIEW_PICTURE'], array(
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => $maxImageSize,
					"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				)));
				$row->AddViewField("DETAIL_PICTURE", CFileInput::Show('NO_FIELDS['.$arItems['ID'].'][DETAIL_PICTURE]', $arItems['DETAIL_PICTURE'], array(
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => $maxImageSize,
					"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				)));
				$arProperties = array();
				if(!empty($arSelectedProps))
				{
					$rsProperties = CIBlockElement::GetProperty($iblockId, $arItems["ID"]);
					while($ar = $rsProperties->Fetch())
					{
						if(!array_key_exists($ar["ID"], $arProperties))
							$arProperties[$ar["ID"]] = array();
						if($ar["PROPERTY_TYPE"] === "L")
							$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE_ENUM"];
						else
							$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE"];
					}
				}
				foreach($arSelectedProps as $aProp)
				{
					$v = '';
					foreach($arProperties[$aProp['ID']] as $property_value_id => $property_value)
					{
						$res = '';
						if($aProp['PROPERTY_TYPE'] == 'F')
							$res = CFileInput::Show('NO_FIELDS['.$property_value_id.']', $property_value, array(
									"IMAGE" => "Y",
									"PATH" => "Y",
									"FILE_SIZE" => "Y",
									"DIMENSIONS" => "Y",
									"IMAGE_POPUP" => "Y",
									"MAX_SIZE" => $maxImageSize,
									"MIN_SIZE" => $minImageSize,
								), array(
									'upload' => false,
									'medialib' => false,
									'file_dialog' => false,
									'cloud' => false,
									'del' => false,
									'description' => false,
								)
							);
						elseif($aProp['PROPERTY_TYPE'] == 'G')
						{
							$t = GetSectionName($property_value);
							if($t)
								$res = $t['NAME'].' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($t['IBLOCK_ID'], $t['ID'])).'" title="'.GetMessage("SPS_ELSEARCH_SECTION_EDIT").'">'.$t['ID'].'</a>]';
						}
						elseif($aProp['PROPERTY_TYPE'] == 'E')
						{
							$t = GetElementName($property_value);
							if($t)
							{
								$res = $t['NAME'].' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'])).'" title="'.GetMessage("SPS_ELSEARCH_ELEMENT_EDIT").'">'.$t['ID'].'</a>]';
							}
						}
						else
						{
							$res = htmlspecialcharsex($property_value);
						}

						if($res != "")
							$v .= ($v!=''?' / ':'').$res;
					}

					if($v != "")
						$row->AddViewField("PROPERTY_".$aProp['ID'], $v);
					unset($arSelectedProps[$aProp['ID']]["CACHE"]);
				}
				foreach($arResult["SKU_ELEMENTS"] as $val)
				{
					$skuProperty = "";
					$arSkuProperty = array();
					foreach($val as $kk => $vv)
					{
						if(is_int($kk) && strlen($vv) > 0)
						{
							if($skuProperty != "")
								$skuProperty .= " <br> ";
							$skuProperty .= '<span style="color: grey;">'.$arResult["SKU_PROPERTIES"][$kk]["NAME"].'</span>: '.$vv;
							$arSkuProperty[$arResult["SKU_PROPERTIES"][$kk]["NAME"]] = $vv;
						}
					}

					$arSku[] = $val["ID"];
					$row =& $lAdmin->AddRow($val["ID"], $val);
					$row->AddField("NAME", $skuProperty.'<input type="hidden" name="prd" id="sku-'.$val["ID"].'">');
					$row->AddViewField("DETAIL_PICTURE", CFileInput::Show('NO_FIELDS['.$val['ID'].'][DETAIL_PICTURE]', $val['DETAIL_PICTURE'], array(
						"IMAGE" => "Y",
						"PATH" => "Y",
						"FILE_SIZE" => "Y",
						"DIMENSIONS" => "Y",
						"IMAGE_POPUP" => "Y",
						"MAX_SIZE" => $maxImageSize,
						"MIN_SIZE" => $minImageSize,
					), array(
						'upload' => false,
						'medialib' => false,
						'file_dialog' => false,
						'cloud' => false,
						'del' => false,
						'description' => false,
					)));

					$row->AddField("ID", "&nbsp;&nbsp;".$arItems["ID"]."-".$val["ID"]);
					foreach($arPrices as $price)
					{
						$row->AddViewField("PRICE".$price['ID'], $arSKUPricesResult[$price['ID']][$val["ID"]]);
					}
					if($addDefault == "Y" || ($val["CAN_BUY"] == "Y" && $addDefault == "N"))
					{
						$arCatalogProduct["BARCODE"] = '';
						if($arCatalogProduct["BARCODE_MULTI"] == 'N')
						{
							$dbBarCodes = CCatalogStoreBarCode::getList(array(), array("PRODUCT_ID" => $val["ID"]));
							while($arBarCode = $dbBarCodes->Fetch())
							{
								$arCatalogProduct["BARCODE"][] = $arBarCode["BARCODE"];
							}
						}
						if(is_array($arCatalogProduct["BARCODE"]))
							$arCatalogProduct["BARCODE"] = implode(', ', $arCatalogProduct["BARCODE"]);

						$balance = FloatVal($val["BALANCE"]);

						$arParams = array();
						$arParams['id'] = $val["ID"];
						$arParams['type'] = $val["TYPE"];
						$arParams['quantity'] = CUtil::JSEscape($quantity);
						$arParams["barcode"] = CUtil::JSEscape($arCatalogProduct["BARCODE"]);

						$arParams = CUtil::PhpToJSObject($arParams);
						foreach(GetModuleEvents("sale", "OnProductSearchForm", true) as $arEvent)
						{
							$arParams = ExecuteModuleEventEx($arEvent, array($val["ID"], $arParams));
						}
						$arParams = "var el".$val["ID"]." = ".$arParams;

						$countField = '<input type="text" name="quantity_'.$val["ID"].'" id="quantity_'.$val["ID"].'" value="1" size="3" />';
						$active = GetMEssage('SPS_PRODUCT_ACTIVE');
						$act = '<script type="text/javascript">'.$arParams.'</script><input class="addBtn" type="button" onclick="SelEl(el'.$val["ID"].', '.$val["ID"].')" name="btn_select_'.$val["ID"].'" id="btn_select_'.$val["ID"].'" value="'.GetMessage("SPS_SELECT").'" />';
					}
					else
					{
						$countField = "&nbsp;";
						$balance = "&nbsp;";
						$active = GetMEssage('SPS_PRODUCT_NO_ACTIVE');
						$act = GetMessage("SPS_CAN_BUY_NOT_PRODUCT");
					}
					$active = ($val["ACTIVE"] == 'Y' ? GetMEssage('SPS_PRODUCT_ACTIVE') : GetMEssage('SPS_PRODUCT_NO_ACTIVE'));

					$row->AddField("ACT", $act);
					$row->AddField("QUANTITY", $countField);
					$row->AddField("BALANCE", $balance);
					$row->AddField("ACTIVE", $active);
				}
			}
			else
			{
				$row->AddField("ACTIVE", $active);
				$row->AddField("ACT", '<input class="addBtn" type="button" name="btn_show_sku_'.$arItems["ID"].'" value="'.GetMessage("SKU_EMPTY").'" title="'.GetMessage('SKU_EMPTY_TITLE').'">');
				$row->AddViewField("PREVIEW_PICTURE", CFileInput::Show('NO_FIELDS['.$arItems['ID'].'][PREVIEW_PICTURE]', $arItems['PREVIEW_PICTURE'], array(
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => $maxImageSize,
					"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				)));
				$row->AddViewField("DETAIL_PICTURE", CFileInput::Show('NO_FIELDS['.$arItems['ID'].'][DETAIL_PICTURE]', $arItems['DETAIL_PICTURE'], array(
					"IMAGE" => "Y",
					"PATH" => "Y",
					"FILE_SIZE" => "Y",
					"DIMENSIONS" => "Y",
					"IMAGE_POPUP" => "Y",
					"MAX_SIZE" => $maxImageSize,
					"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => false,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => false,
					'del' => false,
					'description' => false,
				)));
				$arProperties = array();
				if(!empty($arSelectedProps))
				{
					$rsProperties = CIBlockElement::GetProperty($iblockId, $arItems["ID"]);
					while($ar = $rsProperties->Fetch())
					{
						if(!array_key_exists($ar["ID"], $arProperties))
							$arProperties[$ar["ID"]] = array();
						if($ar["PROPERTY_TYPE"] === "L")
							$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE_ENUM"];
						else
							$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE"];
					}
				}
				foreach($arSelectedProps as $aProp)
				{
					$v = '';
					foreach($arProperties[$aProp['ID']] as $property_value_id => $property_value)
					{
						$res = '';
						if($aProp['PROPERTY_TYPE'] == 'F')
							$res = CFileInput::Show('NO_FIELDS['.$property_value_id.']', $property_value, array(
									"IMAGE" => "Y",
									"PATH" => "Y",
									"FILE_SIZE" => "Y",
									"DIMENSIONS" => "Y",
									"IMAGE_POPUP" => "Y",
									"MAX_SIZE" => $maxImageSize,
									"MIN_SIZE" => $minImageSize,
								), array(
									'upload' => false,
									'medialib' => false,
									'file_dialog' => false,
									'cloud' => false,
									'del' => false,
									'description' => false,
								)
							);
						elseif($aProp['PROPERTY_TYPE'] == 'G')
						{
							$t = GetSectionName($property_value);
							if($t)
								$res = $t['NAME'].' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($t['IBLOCK_ID'], $t['ID'])).'" title="'.GetMessage("SPS_ELSEARCH_SECTION_EDIT").'">'.$t['ID'].'</a>]';
						}
						elseif($aProp['PROPERTY_TYPE'] == 'E')
						{
							$t = GetElementName($property_value);
							if($t)
							{
								$res = $t['NAME'].' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'])).'" title="'.GetMessage("SPS_ELSEARCH_ELEMENT_EDIT").'">'.$t['ID'].'</a>]';
							}
						}
						else
						{
							$res = htmlspecialcharsex($property_value);
						}

						if($res != "")
							$v .= ($v!=''?' / ':'').$res;
					}

					if($v != "")
						$row->AddViewField("PROPERTY_".$aProp['ID'], $v);
					unset($arSelectedProps[$aProp['ID']]["CACHE"]);
				}
			}
		}
		else
		{
			$fieldValue = "";
			$nearestQuantity = $QUANTITY;
			$amountToStore = 0;
			if($storeFromId > 0)
			{
				$dbStoreProduct = CCatalogStoreProduct::GetList(array(), array("PRODUCT_ID" => $arItems["ID"], "STORE_ID" => $storeFromId));
				if($arStoreProduct = $dbStoreProduct->Fetch())
				{
					$amountToStore = $arStoreProduct["AMOUNT"];
				}
			}

			$arCatalogProduct["BARCODE"] = '';
			if($arCatalogProduct["BARCODE_MULTI"] == 'N')
			{
				$dbBarCodes = CCatalogStoreBarCode::getList(array(), array("PRODUCT_ID" => $arItems["ID"]));
				while($arBarCode = $dbBarCodes->Fetch())
				{
					$arCatalogProduct["BARCODE"][] = $arBarCode["BARCODE"];
				}
			}

			if(is_array($arCatalogProduct["BARCODE"]))
				$arCatalogProduct["BARCODE"] = implode(', ', $arCatalogProduct["BARCODE"]);

			$balance = ($storeFromId > 0) ? FloatVal($arCatalogProduct["QUANTITY"])." / ".FloatVal($amountToStore) : FloatVal($arCatalogProduct["QUANTITY"]);
			$row->AddField("BALANCE", $balance);
			$row->AddViewField("PREVIEW_PICTURE", CFileInput::Show('NO_FIELDS['.$arItems['ID'].'][PREVIEW_PICTURE]', $arItems['PREVIEW_PICTURE'], array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
			), array(
				'upload' => false,
				'medialib' => false,
				'file_dialog' => false,
				'cloud' => false,
				'del' => false,
				'description' => false,
			)));
			$row->AddViewField("DETAIL_PICTURE", CFileInput::Show('NO_FIELDS['.$arItems['ID'].'][DETAIL_PICTURE]', $arItems['DETAIL_PICTURE'], array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
			), array(
				'upload' => false,
				'medialib' => false,
				'file_dialog' => false,
				'cloud' => false,
				'del' => false,
				'description' => false,
			)));
			$bCanBuy = true;
			if($arCatalogProduct["CAN_BUY_ZERO"] != "Y" && ($arCatalogProduct["QUANTITY_TRACE"] == "Y" && doubleval($arCatalogProduct["QUANTITY"]) <= 0))
				$bCanBuy = false;
			if($addDefault == "Y" || ($bCanBuy && $addDefault == "N"))
			{
				$arParams = array(
					'id' => $arItems["ID"],
					'quantity' => $QUANTITY,
					'type' => $arCatalogProduct["TYPE"],
					'barcode' => $arCatalogProduct["BARCODE"],
					'name' => $arItems['NAME']
				);

				if ($boolSubscribe)
				{
					$arParams['url'] = $arItems['DETAIL_PAGE_URL'];
				}

				$arParams = CUtil::PhpToJSObject($arParams);

				foreach(GetModuleEvents("sale", "OnProductSearchForm", true) as $arEvent)
				{
					$arParams = ExecuteModuleEventEx($arEvent, Array($arItems["ID"], $arParams));
				}

				$arParams = "var el".$arItems["ID"]." = ".$arParams;

				$act = '<script type="text/javascript">'.$arParams.'</script><input class="addBtn" type="button" onClick="SelEl(el'.$arItems["ID"].', '.$arItems["ID"].')" name="btn_select_'.$arItems["ID"].'" id="btn_select_'.$arItems["ID"].'" value="'.GetMessage("SPS_SELECT").'">';
				$countField = '<input type="text" name="quantity_'.$arItems["ID"].'" id="quantity_'.$arItems["ID"].'" value="1" size="3">';
			}
			else
			{
				$act = GetMessage("SPS_CAN_BUY_NOT_PRODUCT");
				$countField = "&nbsp;";
			}
			$active = ($arItems["ACTIVE"] == 'Y' ? GetMEssage('SPS_PRODUCT_ACTIVE') : GetMEssage('SPS_PRODUCT_NO_ACTIVE'));
			$row->AddField("ACT", $act);
			$row->AddField("QUANTITY", $countField);
			$row->AddField("ACTIVE", $active);

			$arProperties = array();
			if(!empty($arSelectedProps))
			{
				$rsProperties = CIBlockElement::GetProperty($iblockId, $arItems["ID"]);
				while($ar = $rsProperties->Fetch())
				{
					if(!array_key_exists($ar["ID"], $arProperties))
						$arProperties[$ar["ID"]] = array();
					if($ar["PROPERTY_TYPE"] === "L")
						$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE_ENUM"];
					else
						$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar["VALUE"];
				}
			}
			foreach($arSelectedProps as $aProp)
			{
				$v = '';
				foreach($arProperties[$aProp['ID']] as $property_value_id => $property_value)
				{
					$res = '';
					if($aProp['PROPERTY_TYPE'] == 'F')
						$res = CFileInput::Show('NO_FIELDS['.$property_value_id.']', $property_value, array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
								"MAX_SIZE" => $maxImageSize,
								"MIN_SIZE" => $minImageSize,
							), array(
								'upload' => false,
								'medialib' => false,
								'file_dialog' => false,
								'cloud' => false,
								'del' => false,
								'description' => false,
							)
						);
					elseif($aProp['PROPERTY_TYPE'] == 'G')
					{
						$t = GetSectionName($property_value);
						if($t)
							$res = $t['NAME'].' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($t['IBLOCK_ID'], $t['ID'])).'" title="'.GetMessage("SPS_ELSEARCH_SECTION_EDIT").'">'.$t['ID'].'</a>]';
					}
					elseif($aProp['PROPERTY_TYPE'] == 'E')
					{
						$t = GetElementName($property_value);
						if($t)
						{
							$res = $t['NAME'].' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'])).'" title="'.GetMessage("SPS_ELSEARCH_ELEMENT_EDIT").'">'.$t['ID'].'</a>]';
						}
					}
					else
					{
						$res = htmlspecialcharsex($property_value);
					}

					if($res != "")
						$v .= ($v!=''?' / ':'').$res;
				}

				if($v != "")
					$row->AddViewField("PROPERTY_".$aProp['ID'], $v);
				unset($arSelectedProps[$aProp['ID']]["CACHE"]);
			}
			foreach($arPrices as $price)
			{
				$row->AddViewField("PRICE".$price['ID'], $arPricesResult[$price['ID']][$arItems["ID"]]);
			}
		}
	}

	CCatalogDiscountSave::Enable();

	$lAdmin->BeginEpilogContent();

	?>
	<script type="text/javascript">
	<?if(!empty($arSku))
	{
		foreach($arSku as $k => $v)
		{
			?>
			if(BX('sku-<?=$v?>'))
				BX.hide(BX('sku-<?=$v?>').parentNode.parentNode);
			<?
		}
	}
	?>
	</script>
	<?
	$lAdmin->EndEpilogContent();
}
else
{
	ShowError(GetMessage("SPS_NO_PERMS").".");
}
$lAdmin->AddAdminContextMenu(array());

$lAdmin->CheckListMode();


/****************************************************************************/
/***********  MAIN PAGE  ****************************************************/
/****************************************************************************/

$APPLICATION->SetTitle(GetMessage("SPS_SEARCH_TITLE"));
CJSCore::Init(array('admin_interface'));
$APPLICATION->AddHeadScript('/bitrix/js/main/dd.js');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
?>
<script type="text/javascript">
BX.InitializeAdmin();
</script>
<script type="text/javascript">
function SelEl(arParams, el)
{
	var count = 1, i;
	if(BX('quantity_'+el))
		count = BX('quantity_'+el).value;

	arParams['quantity'] = count;

	window.opener.<?= $func_name ?>(<?= intval($index) ?>, arParams, <?= intval($iblockId) ?>);
	BX('btn_select_'+el).value ='<?=GetMessageJS("SPS_PRODUCT_SELECTED")?>';
	var formId = BX('sessid').parentNode.id;
	var allButtons = BX(formId).querySelectorAll('.adm-list-table-cell input.addBtn');
	if (!!allButtons)
	{
		for (i = 0; i < allButtons.length; i++)
		{
			allButtons[i].disabled = true;
		}
		setTimeout(function(){
			for (i = 0; i < allButtons.length; i++)
			{
				allButtons[i].disabled = false;
			}
		}, 2000);
	}
}

function showCanBuy()
{
	alert('<?=GetMessageJS("SPS_CAN_BUY_NOT")?>');
}

function fShowSku(el, sku)
{
	for(var i in sku)
	{
		if(sku.hasOwnProperty(i) && BX('sku-'+sku[i]['ID']))
		{
			if(BX('sku-'+sku[i]['ID']).parentNode.parentNode.style.display == "none")
			{
				BX.addClass(BX('sku-'+sku[i]['ID']).parentNode.parentNode, "border_sku");

				BX.show(BX('sku-'+sku[i]['ID']).parentNode.parentNode);
				BX(el).value = '<?=GetMessageJS("SPS_SKU_HIDE")?>';
			}
			else
			{
				BX.removeClass(BX('sku-'+sku[i]['ID']).parentNode.parentNode, "border_sku");
				BX.hide(BX('sku-'+sku[i]['ID']).parentNode.parentNode);
				BX(el).value = '<?=GetMessageJS("SPS_SKU_SHOW")?>';
			}
		}
	}
}

function checkParameters(f)
{
	var deleteEmptyInputs = f.querySelectorAll('div.adm-filter-box-sizing input, select'),
		i;
	if (!!deleteEmptyInputs)
	{
		for (i = 0; i < deleteEmptyInputs.length; i++)
		{
			if (deleteEmptyInputs[i].value == '')
			{
				deleteEmptyInputs[i].parentNode.removeChild(deleteEmptyInputs[i]);
			}
		}
	}
	if(BX('filter_lid_span'))
		BX('filter_lid_span').parentNode.removeChild(BX('filter_lid_span'));
	f.submit();
}

</script>

<table width="100%">
<tr>
<td valign="top" align="left" width="240">
	<div style="overflow-x: auto;max-width:220px;">
	<?
/*
* @param $arCatalog
* @param $urlCurrent
* @return mixed
*/
function fReplaceUrl($arCatalog, $urlCurrent)
			{
				$urlCurrentDefault = $urlCurrent;

				foreach($arCatalog as $key => $submenu)
				{
					$arUrlAdd = array("set_filter" => "Y");

					$url = $submenu["url"];
					$urlParse = parse_url($url);
					$arUrlTag = explode("&", $urlParse["query"]);

					foreach($arUrlTag as $tag)
					{
						$tmp = explode("=", $tag);
						if($tmp[0] == "IBLOCK_ID" || $tmp[0] == "find_section_section")
						{
							if($tmp[0] == "find_section_section")
								$tmp[0] = "filter_section";

							$urlCurrent = CHTTP::urlDeleteParams($urlCurrent, array($tmp[0]));
							$arUrlAdd[$tmp[0]] = $tmp[1];
						}
					}

					$url = CHTTP::urlAddParams($urlCurrent, $arUrlAdd, array("encode","skip_empty"));
					$arCatalog[$key]["url"] = $url;

					if(isset($submenu["items"]) && count($submenu["items"]) > 0)
					{
						$subCatal = fReplaceUrl($submenu["items"], $urlCurrentDefault);
						$arCatalog[$key]["items"] = $subCatal;
					}
				}

				return $arCatalog;
			}

$urlCurrent = $APPLICATION->GetCurPageParam();
$arCatalog = CCatalogAdmin::get_sections_menu('', $iblockId, 2, 0);

$arCatalog = fReplaceUrl($arCatalog, $urlCurrent);

foreach($arCatalog as $submenu)
{
	$adminMenu->_SetActiveItems($submenu, array());
	$adminMenu->Show($submenu, 0, $urlCurrent);
}
?>
	</div>
</td>
<td valign="top" align="left" style="border-left: 1px solid rgb(164, 185, 204);padding-left:15px;">
	<form name="find_form" method="GET" onsubmit="checkParameters(this); return false;" action="<?echo $APPLICATION->GetCurPage()?>?" accept-charset="<? echo LANG_CHARSET; ?>">
		<input type="hidden" name="__BX_CRM_QUERY_STRING_PREFIX" value="<?echo $APPLICATION->GetCurPage() ?>?">
		<input type="hidden" name="field_name" value="<?echo htmlspecialcharsbx($field_name)?>">
		<input type="hidden" name="field_name_name" value="<?echo htmlspecialcharsbx($field_name_name)?>">
		<input type="hidden" name="field_name_url" value="<?echo htmlspecialcharsbx($field_name_url)?>">
		<input type="hidden" name="alt_name" value="<?echo htmlspecialcharsbx($alt_name)?>">
		<input type="hidden" name="form_name" value="<?echo htmlspecialcharsbx($form_name)?>">
		<input type="hidden" name="func_name" value="<?echo htmlspecialcharsbx($func_name)?>">
		<input type="hidden" name="index" value="<?echo htmlspecialcharsbx($index)?>">
		<input type="hidden" name="BUYER_ID" value="<?echo htmlspecialcharsbx($buyerId)?>">
		<input type="hidden" name="QUANTITY" value="<?echo htmlspecialcharsbx($QUANTITY)?>">
		<input type="hidden" name="lang" value="<?echo LANGUAGE_ID?>">
		<input type="hidden" id="LID" name="LID" value="<?echo htmlspecialcharsbx($lid)?>">
		<input type="hidden" id="caller" name="caller" value="<?echo htmlspecialcharsbx($caller)?>">
		<input type="hidden" name="subscribe" value="<? echo ($boolSubscribe ? 'Y' : 'N'); ?>">
<?
	if ($orderForm)
	{
		?><input type="hidden" name="from" value="order"><?
	}
	$arFindFields = array(
		"find_iblock_id" => GetMessage("SPS_CATALOG"),
		"find_id" => "ID (".GetMessage("SPS_ID_FROM_TO").")",
		"find_xml_id" => GetMessage("SPS_XML_ID"),
		"find_code" => GetMessage("SPS_CODE"),
		"find_time" => GetMessage("SPS_TIMESTAMP")
	);
	if (!$orderForm)
	{
		$arFindFields["find_active"] = GetMessage("SPS_ACTIVE");
	}
	$arFindFields["find_name"] = GetMessage("SPS_NAME");
	$arFindFields["find_descr"] = GetMessage("SPS_DESCR");

	if (!empty($arProps))
	{
		foreach($arProps as $arProp)
			$arFindFields["find_prop_".$arProp["ID"]] = $arProp["NAME"];
	}

	if (!empty($arSKUProps))
	{
		foreach($arSKUProps as $arProp)
		{
			if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"] != "F")
				$arFindFields["IBLIST_A_SUB_PROP_".$arProp["ID"]] = $arProp["NAME"];
		}
	}
	$oFilter = new CAdminFilter(
		$sTableID."_filter",
		$arFindFields
	);
	$oFilter->SetDefaultRows("find_iblock_id");

	$oFilter->Begin();
	?>
<tr>
	<td><?= GetMessage("SPS_CATALOG") ?>:</td>
	<td>
		<select name="IBLOCK_ID" onchange="BX('LID').value = BX('LID_'+this.value).value">
		<?
	$strShowOffersIBlock = COption::GetOptionString('catalog', 'product_form_show_offers_iblock');
	$catalogID = $arLid = array();
	$arCatalogFilter = array();
	if ($boolSubscribe)
	$arCatalogFilter['SUBSCRIPTION'] = 'Y';
	$dbItem = CCatalog::GetList(
	array(),
	$arCatalogFilter,
	false,
	false,
	array('IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID', 'SUBSCRIPTION')
	);
	while($arItems = $dbItem->Fetch())
	{
	$arItems['IBLOCK_ID'] = intval($arItems['IBLOCK_ID']);
	$arItems['PRODUCT_IBLOCK_ID'] = intval($arItems['PRODUCT_IBLOCK_ID']);
	if ('N' == $arItems['SUBSCRIPTION'] && 0 < $arItems['PRODUCT_IBLOCK_ID'])
	{
		$catalogID[$arItems['PRODUCT_IBLOCK_ID']] = true;
		if ('Y' == $strShowOffersIBlock)
		{
			$catalogID[$arItems['IBLOCK_ID']] = true;
		}
	}
	else
	{
		$catalogID[$arItems['IBLOCK_ID']] = true;
	}
	}
	$db_iblocks = CIBlock::GetList(
	array("ID" => "ASC"),
	array("ID" => array_keys($catalogID), 'ACTIVE' => 'Y'),
	false
	);
	while($db_iblocks->ExtractFields("str_iblock_"))
	{
	$arLid[$str_iblock_ID] = $str_iblock_LID;
	?><option value="<?=$str_iblock_ID?>"<?if($iblockId==$str_iblock_ID)echo " selected"?>><?=$str_iblock_NAME?> [<?=$str_iblock_LID?>] (<?=$str_iblock_ID?>)</option><?
	}
?>
	</select>
	<div id="filter_lid_span">
	<?
	foreach($arLid as $iblock => $lidId)
	{
		?><input type="hidden" id="LID_<?=$iblock?>" name="LID_<?=$iblock?>" value="<?echo $lidId?>"><?
	}
?>
	</div>
	</td>
</tr>
<tr>
	<td>ID (<?= GetMessage("SPS_ID_FROM_TO") ?>):</td>
	<td>
		<input type="text" name="filter_id_start" size="10" value="<?echo htmlspecialcharsex($_REQUEST['filter_id_start'])?>">
		...
		<input type="text" name="filter_id_end" size="10" value="<?echo htmlspecialcharsex($_REQUEST['filter_id_end'])?>">
	</td>
</tr>
<tr>
	<td nowrap><?= GetMessage("SPS_XML_ID") ?>:</td>
	<td nowrap>
		<input type="text" name="filter_xml_id" size="50" value="<?echo htmlspecialcharsex(${"filter_xml_id"})?>">
	</td>
</tr>

<tr>
	<td nowrap><?= GetMessage("SPS_CODE") ?>:</td>
	<td nowrap>
		<input type="text" name="filter_code" size="50" value="<?echo htmlspecialcharsex(${"filter_code"})?>">
	</td>
</tr>
<tr>
	<td nowrap><?= GetMessage("SPS_TIMESTAMP") ?>:</td>
	<td nowrap><? echo CalendarPeriod("filter_timestamp_from", htmlspecialcharsex($_REQUEST['filter_timestamp_from']), "filter_timestamp_to", htmlspecialcharsex($_REQUEST['filter_timestamp_to']), "form1")?></td>
</tr>
<?
	if (!$orderForm)
	{
?>
<tr>
	<td nowrap><?= GetMessage("SPS_ACTIVE") ?>:</td>
	<td nowrap>
		<select name="filter_active">
			<option value=""><?=htmlspecialcharsex("(".GetMessage("SPS_ANY").")")?></option>
			<option value="Y"<?if($_REQUEST['filter_active']=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("SPS_YES"))?></option>
			<option value="N"<?if($_REQUEST['filter_active']=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("SPS_NO"))?></option>
		</select>
	</td>
</tr>
<?
	}
?>
				<tr>
					<td nowrap><?= GetMessage("SPS_NAME") ?>:</td>
					<td nowrap>
						<input type="text" name="filter_product_name" value="<?echo htmlspecialcharsex($_REQUEST['filter_product_name'])?>" size="30">
					</td>
				</tr>
				<tr>
					<td nowrap><?= GetMessage("SPS_DESCR") ?>:</td>
					<td nowrap>
						<input type="text" name="filter_intext" size="50" value="<?echo htmlspecialcharsex(${"filter_intext"})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
					</td>
				</tr>

			<?if(!empty($arProps)):
				foreach($arProps as $arProp):
				?>
				<tr>
					<td><?=$arProp["NAME"]?>:</td>
					<td>
						<?if(array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
							echo "<script type='text/javascript'>var arClearHiddenFields = [];</script>";
							echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
								$arProp,
								array("VALUE" => "filter_el_property_".$arProp["ID"]),
							));
						elseif($arProp["PROPERTY_TYPE"] == 'S'):?>
							<input type="text" name="filter_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"filter_el_property_".$arProp["ID"]})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
						<?elseif($arProp["PROPERTY_TYPE"] == 'N' || $arProp["PROPERTY_TYPE"] == 'E'):?>
							<input type="text" name="filter_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"filter_el_property_".$arProp["ID"]})?>" size="30">
						<?elseif($arProp["PROPERTY_TYPE"] == 'L'):?>
							<select name="filter_el_property_<?=$arProp["ID"]?>">
								<option value=""><?echo GetMessage("SPS_VALUE_ANY")?></option>
								<option value="NOT_REF"><?echo GetMessage("SPS_A_PROP_NOT_SET")?></option><?
								$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PROPERTY_ID" => $arProp["ID"]));
								while($arPEnum = $dbrPEnum->GetNext()):
								?>
									<option value="<?=$arPEnum["ID"]?>"<?if(${"filter_el_property_".$arProp["ID"]} == $arPEnum["ID"])echo " selected"?>><?=$arPEnum["VALUE"]?></option>
								<?
								endwhile;
						?></select>
						<?
						elseif($arProp["PROPERTY_TYPE"] == 'G'):
							echo _ShowGroupPropertyFieldList('filter_el_property_'.$arProp["ID"], $arProp, ${'filter_el_property_'.$arProp["ID"]});
						endif;
						?>
					</td>
				</tr>
				<?endforeach;
			endif;

			if(!empty($arSKUProps)):
				foreach($arSKUProps as $arProp)
				{
					if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"] != "F" && $arCatalog['SKU_PROPERTY_ID'] != $arProp['ID'])
					{
						?>
						<tr>
							<td><? echo ('' != $strSKUName ? $strSKUName.' - ' : ''); ?><? echo $arProp["NAME"]?>:</td>
							<td>
								<?if(array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
									echo "<script type='text/javascript'>var arClearHiddenFields = [];</script>";
									echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
										$arProp,
										array("VALUE" => "find_sub_el_property_".$arProp["ID"]),
									));
								elseif($arProp["PROPERTY_TYPE"] == 'S'):?>
									<input type="text" name="filter_sub_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"filter_sub_el_property_".$arProp["ID"]})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
								<?elseif($arProp["PROPERTY_TYPE"] == 'N' || $arProp["PROPERTY_TYPE"] == 'E'):?>
									<input type="text" name="filter_sub_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"filter_sub_el_property_".$arProp["ID"]})?>" size="30">
								<?elseif($arProp["PROPERTY_TYPE"] == 'L'):?>
									<select name="filter_sub_el_property_<?=$arProp["ID"]?>">
										<option value=""><?echo GetMessage("SPS_VALUE_ANY")?></option>
										<option value="NOT_REF"><?echo GetMessage("SPS_A_PROP_NOT_SET")?></option><?
										$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PROPERTY_ID" => $arProp["ID"]));
										while($arPEnum = $dbrPEnum->GetNext()):
										?>
											<option value="<?=$arPEnum["ID"]?>"<?if(${"filter_sub_el_property_".$arProp["ID"]} == $arPEnum["ID"])echo " selected"?>><?=$arPEnum["VALUE"]?></option>
										<?
										endwhile;
								?></select>
								<?
								elseif($arProp["PROPERTY_TYPE"] == 'G'):
									echo _ShowGroupPropertyFieldList('filter_sub_el_property_'.$arProp["ID"], $arProp, ${'filter_sub_el_property_'.$arProp["ID"]});
								endif;
								?>
							</td>
						</tr>
						<?
					}
				}
			endif;

			$oFilter->Buttons();
			?>
			<input type="submit" name="set_filter" value="<?echo GetMessage("prod_search_find")?>" title="<?echo GetMessage("prod_search_find_title")?>">
			<input type="submit" name="del_filter" value="<?echo GetMessage("prod_search_cancel")?>" title="<?echo GetMessage("prod_search_cancel_title")?>">
			<?
			$oFilter->End();
			?>
		</form>
		<?
		$lAdmin->DisplayList();
		if(isset($_REQUEST["set_filter"]) && $_REQUEST["set_filter"] === 'Y')
		{
			if(isset($_REQUEST["IBLOCK_ID"]))
			{
				CUserOptions::SetOption("catalog", "product_search_".$caller, "&IBLOCK_ID=".intval($_REQUEST["IBLOCK_ID"])."&filter_section=".intval($_REQUEST["filter_section"]), false, $buyerId);
			}
		}
		?>
		<br>
		<input type="button" class="typebutton" value="<?= GetMessage("SPS_CLOSE") ?>" onClick="window.close();">
	</td>
</tr>
</table>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_popup_admin.php");?>