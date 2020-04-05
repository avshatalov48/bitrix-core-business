<?
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
use Bitrix\Main\Loader;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Config\Option;
use Bitrix\Currency\CurrencyTable;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/prolog.php');

$selfFolderUrl = $adminPage->getSelfFolderUrl();
$listUrl = $selfFolderUrl."cat_store_document_list.php?lang=".LANGUAGE_ID;
$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);

if (!($USER->CanDoOperation('catalog_read') || $USER->CanDoOperation('catalog_store')))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('catalog');
$bReadOnly = !$USER->CanDoOperation('catalog_store');

IncludeModuleLangFile(__FILE__);

if (!isset($_REQUEST['AJAX_MODE']))
{
	if ($ex = $APPLICATION->GetException())
	{
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
		ShowError($ex->GetString());
		require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}
}

$ID = (isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0);
if ($ID < 0)
	$ID = 0;

$userId = (int)$USER->GetID();
$docType = (isset($_REQUEST["DOCUMENT_TYPE"]) ? (string)$_REQUEST['DOCUMENT_TYPE'] : '');

$arSitesShop = array();
$arSitesTmp = array();

$siteIterator = SiteTable::getList(array(
	'select' => array('LID', 'NAME', 'SORT'),
	'filter' => array('=ACTIVE' => 'Y'),
	'order' => array('SORT' => 'ASC', 'LID' => 'ASC')
));
while ($site = $siteIterator->fetch())
{
	$saleSite = (string)Option::get('sale', 'SHOP_SITE_'.$site['LID']);
	if ($site['LID'] == $saleSite)
		$arSitesShop[] = array('ID' => $site['LID'], 'NAME' => $site['NAME']);
	$arSitesTmp[] = array('ID' => $site['LID'], 'NAME' => $site['NAME']);
}
unset($saleSite, $site, $siteIterator);

$rsCount = count($arSitesShop);
if($rsCount <= 0)
{
	$arSitesShop = $arSitesTmp;
	$rsCount = count($arSitesShop);
}

$rsContractors = CCatalogContractor::GetList();
$arContractors = array();
while($arContractor = $rsContractors->Fetch())
	$arContractors[] = $arContractor;
unset($arContractor, $rsContractors);

$arMeasureCode = $arResult = array();
$arStores = array();
$rsStores = CCatalogStore::GetList(array(), array("ACTIVE" => "Y"));
while($arStore = $rsStores->GetNext())
	$arStores[$arStore["ID"]] = $arStore;
unset($arStore, $rsStores);

if ($_SERVER["REQUEST_METHOD"] == "POST" && strlen($_REQUEST["Update"]) > 0 && !$bReadOnly && check_bitrix_sessid())
{
	$adminSidePanelHelper->decodeUriComponent();
	if (!$_REQUEST["cancellation"] && ($_REQUEST["save_document"] || $_REQUEST["save_and_conduct"]))
	{
		$contractorId = (isset($_REQUEST['CONTRACTOR_ID']) ? (int)$_REQUEST['CONTRACTOR_ID'] : 0);
		$currency = '';
		$result = array();
		$docId = 0;
		$currency = (!empty($_REQUEST["CAT_CURRENCY_STORE"]) ? (string)$_REQUEST["CAT_CURRENCY_STORE"] : '');

		$arGeneral = array(
			"DOC_TYPE" => $docType,
			"SITE_ID" => $_REQUEST["SITE_ID"],
			"DATE_DOCUMENT" => $_REQUEST["DOC_DATE"],
			"CREATED_BY" => $userId,
			"MODIFIED_BY" => $userId,
			"COMMENTARY" => $_REQUEST["CAT_DOC_COMMENTARY"],
		);
		if ($contractorId > 0)
			$arGeneral["CONTRACTOR_ID"] = $contractorId;
		if ($currency != '')
			$arGeneral["CURRENCY"] = $currency;
		if (isset($_REQUEST["CAT_DOCUMENT_SUM"]))
			$arGeneral["TOTAL"] = (float)$_REQUEST["CAT_DOCUMENT_SUM"];

		if ($ID > 0)
		{
			unset($arGeneral['CREATED_BY']);
			if(CCatalogDocs::update($ID, $arGeneral))
				$docId = $ID;
		}
		else
		{
			$ID = $docId = CCatalogDocs::add($arGeneral);
		}
		if($ID > 0)
		{
			$dbElement = CCatalogStoreDocsElement::getList(array(), array("DOC_ID" => $ID), false, false, array("ID"));
			while($arElement = $dbElement->Fetch())
			{
				CCatalogStoreDocsElement::delete($arElement["ID"]);
				$dbDocsBarcode = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arElement["ID"]), false, false, array("ID"));
				while($arDocsBarcode = $dbDocsBarcode->Fetch())
					CCatalogStoreDocsBarcode::delete($arDocsBarcode["ID"]);
			}
		}
		if (isset($_POST["PRODUCT"]) && is_array($_POST["PRODUCT"]) && $docId)
		{
			$arProducts = ($_POST["PRODUCT"]);
			foreach($arProducts as $key => $val)
			{
				$storeTo = $val["STORE_TO"];
				$storeFrom = $val["STORE_FROM"];

				$arAdditional = array(
					"AMOUNT" => $val["AMOUNT"],
					"ELEMENT_ID" => $val["PRODUCT_ID"],
					"PURCHASING_PRICE" => $val["PURCHASING_PRICE"],
					"STORE_TO" => $storeTo,
					"STORE_FROM" => $storeFrom,
					"ENTRY_ID" => $key,
					"DOC_ID" => $docId,
				);

				$docElementId = CCatalogStoreDocsElement::add($arAdditional);
				if ($docElementId && isset($val["BARCODE"]))
				{
					$arBarcode = array();
					if(!empty($val["BARCODE"]))
						$arBarcode = explode(', ', $val["BARCODE"]);

					if (!empty($arBarcode))
					{
						foreach($arBarcode as $barCode)
						{
							CCatalogStoreDocsBarcode::add(array("BARCODE" => $barCode, "DOC_ELEMENT_ID" => $docElementId));
						}
					}
				}
			}
		}

		if ($_REQUEST["save_document"] && $docId)
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $docId));
			$saveDocumentUrl = $selfFolderUrl."cat_store_document_edit.php?lang=".LANGUAGE_ID."&ID=".$docId;
			$saveDocumentUrl = $adminSidePanelHelper->editUrlToPublicPage($saveDocumentUrl);
			$adminSidePanelHelper->localRedirect($listUrl);
			LocalRedirect($saveDocumentUrl);
		}
	}

	if ($_REQUEST["save_and_conduct"] || $_REQUEST["cancellation"])
	{
		$result = false;
		$DB->StartTransaction();

		if ($_REQUEST["save_and_conduct"])
			$result = CCatalogDocs::conductDocument($ID, $userId);
		elseif($_REQUEST["cancellation"])
			$result = CCatalogDocs::cancellationDocument($ID, $userId);

		if ($result)
			$DB->Commit();
		else
			$DB->Rollback();

		if($ex = $APPLICATION->GetException())
		{
			$TAB_TITLE = GetMessage("CAT_DOC_".$docType);
			if($bReadOnly)
				$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("CAT_DOC_TITLE_VIEW")).". ".$TAB_TITLE.".");
			else
				$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("CAT_DOC_TITLE_EDIT")).". ".$TAB_TITLE.".");
			$strError = $ex->GetString();
			if(!empty($result) && is_array($result))
				$strError .= CCatalogStoreControlUtil::showErrorProduct($result);
			$adminSidePanelHelper->sendJsonErrorResponse($strError);
			require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
			CAdminMessage::ShowMessage($strError);
			$bVarsFromForm = true;
		}
		else
		{
			$adminSidePanelHelper->sendSuccessResponse("base");
			$adminSidePanelHelper->localRedirect($listUrl);
			LocalRedirect($listUrl);
		}
	}
}
ClearVars();
if($ID > 0)
{
	$arSelect = array(
		"ID",
		"SITE_ID",
		"DOC_TYPE",
		"CONTRACTOR_ID",
		"DATE_DOCUMENT",
		"CURRENCY",
		"STATUS",
		"COMMENTARY",
	);

	$dbResult = CCatalogDocs::getList(array(),array('ID' => $ID), false, false, $arSelect);
	if (!$dbResult->ExtractFields("str_"))
	{
		$ID = 0;
	}
	else
	{
		$docType = $str_DOC_TYPE;
		$bReadOnly = ($str_STATUS == 'Y') ? true : $bReadOnly;
	}
}

if (!isset(CCatalogDocs::$types[$docType]))
{
	$docType = '';
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}

$requiredFields = CCatalogStoreControlUtil::getFields($docType);
if(!$requiredFields || $_REQUEST["dontsave"])
{
	$adminSidePanelHelper->sendSuccessResponse("close");
	$adminSidePanelHelper->localRedirect($listUrl);
	LocalRedirect($listUrl);
}

$sTableID = "b_catalog_store_docs_".$docType;
$oSort = new CAdminSorting($sTableID, "ID", "ASC");
$lAdmin = new CAdminList($sTableID, $oSort);

$isDocumentConduct = false;

if($ID > 0 || isset($_REQUEST["AJAX_MODE"]))
{
	$arAllDocumentElement = array();
	if($ID > 0)
	{
		$dbDocument = CCatalogDocs::getList(array(), array("ID" => $ID), false, false, array("DOC_TYPE", "SITE_ID", "CONTRACTOR_ID", "CURRENCY", "TOTAL", "STATUS"));
		if($arDocument = $dbDocument->Fetch())
		{
			$isDocumentConduct = ($arDocument["STATUS"] == 'Y');
			foreach($arDocument as $key => $value)
				$arResult[$key] = $value;
			unset($key, $value);

			$arResult["DATE_DOCUMENT"] = 'now';
			$arResult["CREATED_BY"] = $arResult["MODIFIED_BY"] = $USER->GetID();
			$bReadOnly = ($arDocument["STATUS"] == 'Y') ? true : $bReadOnly;
		}
		unset($arDocument, $dbDocument);
	}
	if (isset($_REQUEST["AJAX_MODE"]))
	{
		if (!isset($arResult['STATUS']) || $arResult['STATUS'] != 'Y')
		{
			if (isset($_REQUEST['SITE_ID']))
			{
				$arResult['SITE_ID'] = (string)$_REQUEST['SITE_ID'];
				$str_SITE_ID = $arResult['SITE_ID'];
			}
			if (isset($_REQUEST['CONTRACTOR_ID']))
			{
				$arResult['CONTRACTOR_ID'] = (int)$_REQUEST['CONTRACTOR_ID'];
				$str_CONTRACTOR_ID = $arResult['CONTRACTOR_ID'];
			}
			if (isset($_REQUEST['CAT_CURRENCY_STORE']))
			{
				$arResult['CURRENCY'] = (string)$_REQUEST['CAT_CURRENCY_STORE'];
				$str_CURRENCY = $arResult['CURRENCY'];
			}
		}
	}

	if(!isset($_REQUEST["AJAX_MODE"]))
	{
		$dbDocumentElement = CCatalogStoreDocsElement::getList(array('ID' => 'ASC'), array("DOC_ID" => $ID), false, false, array("ID", "STORE_FROM", "STORE_TO", "ELEMENT_ID", "AMOUNT", "PURCHASING_PRICE", "IS_MULTIPLY_BARCODE", "RESERVED"));
		while($arDocumentElements = $dbDocumentElement->Fetch())
		{
			$arAllDocumentElement[] = $arDocumentElements;
		}
	}
	elseif(isset($_REQUEST["PRODUCT"]) && is_array($_REQUEST["PRODUCT"]) || isset($_REQUEST["ELEMENT_ID"]))
	{
		$arElements = array();
		if(isset($_REQUEST["PRODUCT"]) && is_array($_REQUEST["PRODUCT"]))
			$arElements = $_REQUEST["PRODUCT"];
		if(isset($_REQUEST["ELEMENT_ID"]) && is_array($_REQUEST["ELEMENT_ID"]))
		{
			$arElements[] = array("PRODUCT_ID" => $_REQUEST["ELEMENT_ID"][0], "SELECTED_BARCODE" => $_REQUEST["HIDDEN_BARCODE"][0], "AMOUNT" => $_REQUEST["HIDDEN_QUANTITY"][0]);
		}
		$arAllAddedProductsId = $arAjaxElementInfo = array();
		foreach($arElements as $eachAddElement)
		{
			if(isset($eachAddElement["PRODUCT_ID"]))
			{
				$arAllAddedProductsId[] = intval($eachAddElement["PRODUCT_ID"]);
			}
		}
		$dbElement = CCatalogProduct::GetList(
			array(),
			array("ID" => $arAllAddedProductsId),
			false,
			false,
			array("ID", "BARCODE_MULTI", "QUANTITY_RESERVED", 'PURCHASING_PRICE', 'PURCHASING_CURRENCY')
		);
		while($arElement = $dbElement->Fetch())
		{
			$arAjaxElementInfo[$arElement['ID']] = array(
				"IS_MULTIPLY_BARCODE" => $arElement["BARCODE_MULTI"],
				"RESERVED" => $arElement["QUANTITY_RESERVED"],
				"PURCHASING_PRICE" => $arElement["PURCHASING_PRICE"],
				"PURCHASING_CURRENCY" => $arElement["PURCHASING_CURRENCY"]
			);
		}
		if (!empty($arElements))
		{
			foreach ($arElements as &$arAjaxElement)
			{
				$elementId = $arAjaxElement["PRODUCT_ID"];
				$arAjaxElement["ELEMENT_ID"] = $arAjaxElement["PRODUCT_ID"];
				if ($arAjaxElement["SELECTED_BARCODE"] == '')
					$arAjaxElement["SELECTED_BARCODE"] = $arAjaxElement["BARCODE"];
				$arAjaxElement["BARCODE"] = array($arAjaxElement["BARCODE"]);
				if (!empty($arAjaxElementInfo[$elementId]))
				{
					$arAjaxElement["IS_MULTIPLY_BARCODE"] = $arAjaxElementInfo[$elementId]["IS_MULTIPLY_BARCODE"];
					$arAjaxElement["RESERVED"] = $arAjaxElementInfo[$elementId]["RESERVED"];
					if (
						(float)$arAjaxElement['PURCHASING_PRICE'] <= 0
						&& (float)$arAjaxElementInfo[$elementId]["PURCHASING_PRICE"] > 0
					)
					{
						$arAjaxElement["PURCHASING_PRICE"] = $arAjaxElementInfo[$elementId]["PURCHASING_PRICE"];
						$arAjaxElement["PURCHASING_CURRENCY"] = $arAjaxElementInfo[$elementId]["PURCHASING_CURRENCY"];
					}
				}
				unset($elementId);
			}
			unset($arAjaxElement);
		}

		$arAllDocumentElement = $arElements;
	}

	foreach($arAllDocumentElement as $arDocumentElement)
	{
		$arElement = $arElementBarcode = array();
		$isMultiSingleBarcode = $selectedBarcode = false;
		foreach($arDocumentElement as $key => $value)
		{
			$arElement[$key] = $value;
		}

		if($arDocumentElement["IS_MULTIPLY_BARCODE"] == 'N')
		{
			if(isset($arElement["BARCODE"]))
				unset($arElement["BARCODE"]);
			$dbDocumentStoreBarcode = CCatalogStoreBarCode::getList(array(), array("PRODUCT_ID" => $arDocumentElement["ELEMENT_ID"]));
			while($arDocumentStoreBarcode = $dbDocumentStoreBarcode->Fetch())
			{
				$arElementBarcode[] = $arDocumentStoreBarcode["BARCODE"];
			}
			if(count($arElementBarcode) > 1)
			{
				$isMultiSingleBarcode = true;

				if($bReadOnly)
					$arElementBarcode = array();
			}
		}

		if($arDocumentElement["IS_MULTIPLY_BARCODE"] == 'Y' || $isMultiSingleBarcode)
		{
			$dbDocumentElementBarcode = CCatalogStoreDocsBarcode::getList(array(), array("DOC_ELEMENT_ID" => $arDocumentElement["ID"]), false, false, array("BARCODE"));
			while($arDocumentElementBarcode = $dbDocumentElementBarcode->Fetch())
			{
				if($isMultiSingleBarcode)
				{
					$selectedBarcode = $arDocumentElementBarcode["BARCODE"];
					if(empty($arElementBarcode))
						$arElementBarcode[] = $arDocumentElementBarcode["BARCODE"];
				}
				else
				{
					$arElementBarcode[] = $arDocumentElementBarcode["BARCODE"];
				}
			}
		}

		if(!isset($arElement["BARCODE"]))
			$arElement["BARCODE"] = $arElementBarcode;
		if(!isset($arElement["SELECTED_BARCODE"]))
			$arElement["SELECTED_BARCODE"] = $selectedBarcode;
		$arResult["ELEMENT"][] = $arElement;
	}
}

if (!$USER->CanDoOperation('catalog_store'))
{
	$isDocumentConduct = false;
}

$aContext = array();
if(!$bReadOnly)
{
	$aContext = array(
		/*array(
			"TEXT" => GetMessage("CAT_DOC_ADD_ITEMS"),
			"ICON" => "btn_new",
			"LINK" => "cat_store_edit.php?lang=".LANGUAGE_ID,
			"TITLE" => GetMessage("CAT_DOC_ADD_ITEMS")
		),*/
		array(
			"TEXT" => GetMessage("CAT_DOC_FIND_ITEMS"),
			"ICON" => "btn_new",
			"TITLE" => GetMessage("CAT_DOC_FIND_ITEMS"),
			"ONCLICK" => "addProductSearch(1);",
		),
		array(
			"HTML" => GetMessage(
				"CAT_DOC_LINK_FIND",
				array("#LINK#" => '<a href="javascript:void(0);" onClick="findBarcodeDivHider()">'.GetMessage('CAT_DOC_BARCODE_FIND_LINK').'</a>')
			),
		),
		array(
			"HTML" => '<div id="cat_barcode_find_div" style="display: none;">'.
						'<input type="text" id="CAT_DOC_BARCODE_FIND" style="margin: 0 10px;">'.
						'<a href="javascript:void(0);" class="adm-btn" onclick="productSearch(BX(\'CAT_DOC_BARCODE_FIND\').value);">'.GetMessage('CAT_DOC_BARCODE_FIND').'</a>'.
						'</div>',
		),
	);
}

$visibleHeaderIds = array();
$arHeaders = array(
	array(
		"id" => "IMAGE",
		"content" => GetMessage("CAT_DOC_PRODUCT_PICTURE"),
		"default" => true
	),
	array(
		"id" => "TITLE",
		"content" => GetMessage("CAT_DOC_PRODUCT_NAME"),
		"default" => true
	),
);
if ((string)Option::get('iblock', 'show_xml_id') == 'Y')
{
	$arHeaders[] = array(
		"id" => "XML_ID",
		"content" => GetMessage("CAT_DOC_PRODUCT_XML_ID"),
		"default" => true
	);
}
if(isset($requiredFields["RESERVED"]))
{
	$arHeaders[] = array(
		"id" => "RESERVED",
		"content" => GetMessage("CAT_DOC_PRODUCT_RESERVED"),
		"default" => ($requiredFields["RESERVED"]["required"] == 'Y')
	);
	$visibleHeaderIds[] = "RESERVED";
}
if(isset($requiredFields["AMOUNT"]))
{
	$arHeaders[] = array(
		"id" => "AMOUNT",
		"content" => GetMessage("CAT_DOC_PRODUCT_AMOUNT"),
		"default" => $requiredFields["AMOUNT"]["required"],
	);
	$visibleHeaderIds[] = "AMOUNT";
}
if(isset($requiredFields["NET_PRICE"]))
{
	$arHeaders[] = array(
		"id" => "PURCHASING_PRICE",
		"content" => GetMessage("CAT_DOC_PRODUCT_PRICE"),
		"default" => ($requiredFields["NET_PRICE"]["required"] == 'Y')
	);
	$visibleHeaderIds[] = "PURCHASING_PRICE";
}
if(isset($requiredFields["TOTAL"]))
{
	$arHeaders[] = array(
		"id" => "SUMM",
		"content" => GetMessage("CAT_DOC_PRODUCT_SUMM"),
		"default" => ($requiredFields["TOTAL"]["required"] == 'Y')
	);
	$visibleHeaderIds[] = "SUMM";
}
if(isset($requiredFields["STORE_FROM"]))
{
	$arHeaders[] = array(
		"id" => "STORE_FROM",
		"content" => GetMessage("CAT_DOC_STORE_FROM"),
		"default" => ($requiredFields["STORE_FROM"]["required"] == 'Y')
	);
	$visibleHeaderIds[] = "STORE_FROM";
}
if(isset($requiredFields["STORE_TO"]))
{
	$arHeaders[] = array(
		"id" => "STORE_TO",
		"content" => GetMessage("CAT_DOC_STORE_TO"),
		"default" => ($requiredFields["STORE_TO"]["required"] == 'Y')
	);
	$visibleHeaderIds[] = "STORE_TO";
}
if(isset($requiredFields["BAR_CODE"]))
{
	$arHeaders[] = array(
		"id" => "BARCODE",
		"content" => GetMessage("CAT_DOC_BARCODE"),
		"default" => ($requiredFields["BAR_CODE"]["required"] == 'Y')
	);
	$visibleHeaderIds[] = "BARCODE";
}

$lAdmin->AddHeaders($arHeaders);
if (!empty($visibleHeaderIds))
{
	foreach ($visibleHeaderIds as $headerId)
		$lAdmin->AddVisibleHeaderColumn($headerId);
	unset($headerId);
}

$isDisable = $bReadOnly ? " disabled" : "";
$maxId = 0;
if(is_array($arResult["ELEMENT"]))
{
	foreach($arResult["ELEMENT"] as $code => $value)
	{
		$storesTo = $storesFrom = '';
		$isMultiply = ('Y' == $value["IS_MULTIPLY_BARCODE"]);
		$arProductInfo = CCatalogStoreControlUtil::getProductInfo($value["ELEMENT_ID"]);
		if(is_array($arProductInfo))
			$value = array_merge($value, $arProductInfo);

		$arRes['ID'] = (int)$code;
		$maxId = ($arRes['ID'] > $maxId) ? $arRes['ID'] : $maxId;
		foreach($arStores as $key => $val)
		{
			$selectedTo = ($value['STORE_TO'] == $val['ID']) ? " selected " : " ";
			$selectedFrom = ($value['STORE_FROM'] == $val['ID']) ? " selected " : " ";
			$store = ($val["TITLE"] != '') ? $val["TITLE"]." (".$val["ADDRESS"].")" : $val["ADDRESS"];
			$storesTo .= '<option'.$selectedTo.'value="'.$val['ID'].'">'.$store.'</option>';
			$storesFrom .= '<option'.$selectedFrom.'value="'.$val['ID'].'">'.$store.'</option>';
		}
		$arRows[$arRes['ID']] = $row =& $lAdmin->AddRow($arRes['ID']);
		$row->AddViewField("IMAGE", CFile::ShowImage($value['DETAIL_PICTURE'], 80, 80, "border=0", "", true));
		if ($value['EDIT_PAGE_URL'])
		{
			$editPageUrl = $value['EDIT_PAGE_URL'];
			$editPageUrl = $adminSidePanelHelper->editUrlToPublicPage($editPageUrl);
			$value['EDIT_PAGE_URL'] = $editPageUrl;
		}
		$row->AddViewField("TITLE", '<a target="_top" href ="'.$value['EDIT_PAGE_URL'].'"> '.$value['NAME'].'</a><input value="'.$value['ELEMENT_ID'].'" type="hidden" name="PRODUCT['.$arRes['ID'].'][PRODUCT_ID]" id="PRODUCT_ID_'.$arRes['ID'].'">');
		$row->AddViewField('XML_ID', $value['XML_ID']);
		$readOnly = ($isMultiply && !$bReadOnly) ? ' readonly' : '';
		if(isset($value['BARCODE']) && $isMultiply)
		{
			$barcodeCount = 0;
			$tmpBarcodeCount = count($value['BARCODE']);
			if (1 < $tmpBarcodeCount)
			{
				$barcodeCount = $tmpBarcodeCount;
			}
			elseif (1 == $tmpBarcodeCount)
			{
				if (isset($value['BARCODE'][0]) && $value['BARCODE'][0] != '')
					$barcodeCount = count(explode(', ', $value['BARCODE'][0]));
			}
			unset($tmpBarcodeCount);
		}
		elseif(!$isMultiply)
		{
			$barcodeCount = count($value['BARCODE']);
		}
		else
		{
			$barcodeCount = $value['AMOUNT'];
		}
		if(isset($requiredFields["AMOUNT"]))
			$row->AddViewField("AMOUNT", '<div><input type="hidden" id="CAT_DOC_AMOUNT_HIDDEN_'.$arRes['ID'].'" value="'.$barcodeCount.'" onchange="recalculateSum('.$arRes['ID'].');"> <input name="PRODUCT['.$arRes['ID'].'][AMOUNT]" onchange="recalculateSum('.$arRes['ID'].');" id="CAT_DOC_AMOUNT_'.$arRes['ID'].'" value="'.$value['AMOUNT'].'" type="text" size="10"'.$isDisable.'></div>');
		if(isset($requiredFields["NET_PRICE"]))
			$row->AddViewField("PURCHASING_PRICE", '<div> <input name="PRODUCT['.$arRes['ID'].'][PURCHASING_PRICE]" onchange="recalculateSum('.$arRes['ID'].');" id="CAT_DOC_PURCHASING_PRICE_'.$arRes['ID'].'" value="'.$value['PURCHASING_PRICE'].'" type="text" size="10"'.$isDisable.'></div>');
		if(isset($requiredFields["TOTAL"]))
			$row->AddViewField("SUMM", '<div id="CAT_DOC_SUMM_'.$arRes['ID'].'">'.doubleval($value['AMOUNT']) * doubleval($value['PURCHASING_PRICE']).'</div><input value="'.doubleval($value['AMOUNT']) * doubleval($value['PURCHASING_PRICE']).'" type="hidden" name="PRODUCT['.$arRes['ID'].'][SUMM]" id="PRODUCT['.$arRes['ID'].'][SUMM]">');
		if(isset($requiredFields["STORE_FROM"]))
			$row->AddViewField("STORE_FROM", '<select style="max-width:300px; width:300px;" name="PRODUCT['.$arRes['ID'].'][STORE_FROM]" id="CAT_DOC_STORE_FROM_'.$arRes['ID'].'"'.$isDisable.'>'.$storesFrom.'</select>');
		if(isset($requiredFields["STORE_TO"]))
			$row->AddViewField("STORE_TO", '<select style="max-width:300px; width:300px;" name="PRODUCT['.$arRes['ID'].'][STORE_TO]" id="CAT_DOC_STORE_TO_'.$arRes['ID'].'"'.$isDisable.'>'.$storesTo.'</select>');
		if(isset($requiredFields["RESERVED"]))
			$row->AddViewField("RESERVED", '<div > <input readonly name="PRODUCT['.$arRes['ID'].'][RESERVED]" id="CAT_DOC_RESERVED_'.$arRes['ID'].'" value="'.$value['RESERVED'].'" type="text" size="10"'.$isDisable.'></div>');
		if(isset($requiredFields["BAR_CODE"]) && isset($value['BARCODE']) && is_array($value['BARCODE']))
		{
			$barcode = implode(", ", $value['BARCODE']);
			if($isMultiply)
			{
				$readOnly = ($bReadOnly) ? ' readonly' : '';
				$buttonValue = ($bReadOnly) ? GetMessage('CAT_DOC_BARCODES_VIEW') : GetMessage('CAT_DOC_BARCODES_ENTER');
				if(empty($barcode))
					$barcode = '';//GetMessage('CAT_DOC_POPUP_TITLE');
				$inputBarcode = '<input type="button" value="'.$buttonValue.'" onclick="enterBarcodes('.$arRes['ID'].');"><input '.$readOnly.' type="hidden" value="'.htmlspecialcharsbx($barcode).'" type="text" name="PRODUCT['.$arRes['ID'].'][BARCODE]" id="PRODUCT['.$arRes['ID'].'][BARCODE]" onchange="recalculateSum('.$arRes['ID'].');" size="20">';
			}
			elseif(count($value['BARCODE']) < 2)
				$inputBarcode = htmlspecialcharsbx($barcode);
			else
			{
				$inputBarcode = '<select style="max-width:150px; width:150px;" id="PRODUCT['.$arRes['ID'].'][BARCODE]" name="PRODUCT['.$arRes['ID'].'][BARCODE]"> ';
				foreach($value['BARCODE'] as $singleCode)
				{
					$selected = ($value["SELECTED_BARCODE"] == $singleCode) ? ' selected' : '';
					$inputBarcode .= '<option value="'.htmlspecialcharsbx($singleCode).'"'.$selected.'>'.htmlspecialcharsbx($singleCode).'</option>';
				}
				$inputBarcode .= '</select>';
			}
			$row->AddViewField("BARCODE", '<div id="CAT_BARCODE_DIV_BIND_'.$arRes['ID'].'" align="center">'.$inputBarcode.'</div>');
		}
		$arActions = array();
		if (!$bReadOnly)
		{
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("CAT_DOC_DEL"),
				"ACTION" => "if(confirm('".GetMessageJS('CAT_DOC_CONFIRM_DELETE')."')) deleteRow(".$arRes['ID'].")"
			);
			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("CAT_DOC_COPY"),
				"ACTION" => "copyRow(null, ".CUtil::PhpToJSObject(array('id' => $value["ELEMENT_ID"], 'parent' => $arRes['ID'])).")"
			);
		}
		$row->AddActions($arActions);
		$row->bReadOnly = true;
	}
}

if(isset($row))
	unset($row);

$lAdmin->AddGroupActionTable(
	array(
		'summ' => array(
			'type' => 'html',
			'value' => ''
		)
	),
	array("disable_action_target" => true)
);


$lAdmin->AddAdminContextMenu($aContext, false, true);
$lAdmin->CheckListMode();

$errorMessage = "";
$bVarsFromForm = false;

$TAB_TITLE = GetMessage("CAT_DOC_".$docType);
if($ID > 0)
{
	if($bReadOnly)
		$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("CAT_DOC_TITLE_VIEW")).". ".$TAB_TITLE.".");
	else
		$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("CAT_DOC_TITLE_EDIT")).". ".$TAB_TITLE.".");
}
else
{
	$APPLICATION->SetTitle(GetMessage("CAT_DOC_NEW").". ".$TAB_TITLE);
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
CJSCore::Init(array('file_input', 'currency'));
$APPLICATION->SetAdditionalCSS('/bitrix/panel/catalog/catalog_store_docs.css');
if ($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_catalog_measure", "", "str_");

$aMenu = array(
	array(
		"TEXT" => GetMessage("CAT_DOC_LIST"),
		"ICON" => "btn_list",
		"LINK" => $listUrl
	)
);

$context = new CAdminContextMenu($aMenu);
$context->Show();

$currencyList = array();
$currencyIterator = CurrencyTable::getList(array(
	'select' => array('CURRENCY')
));
while ($currency = $currencyIterator->fetch())
{
	$currencyFormat = CCurrencyLang::GetFormatDescription($currency['CURRENCY']);
	$currencyList[] = array(
		'CURRENCY' => $currency['CURRENCY'],
		'FORMAT' => array(
			'FORMAT_STRING' => $currencyFormat['FORMAT_STRING'],
			'DEC_POINT' => $currencyFormat['DEC_POINT'],
			'THOUSANDS_SEP' => $currencyFormat['THOUSANDS_SEP'],
			'DECIMALS' => $currencyFormat['DECIMALS'],
			'THOUSANDS_VARIANT' => $currencyFormat['THOUSANDS_VARIANT'],
			'HIDE_ZERO' => $currencyFormat['HIDE_ZERO']
		)
	);
}
unset($currencyFormat, $currency, $currencyIterator);

CAdminMessage::ShowMessage($errorMessage);
$actionUrl = $APPLICATION->GetCurPage()."?lang=".LANGUAGE_ID."&DOCUMENT_TYPE=".htmlspecialcharsbx($docType);
$actionUrl = $adminSidePanelHelper->setDefaultQueryParams($actionUrl);
?>
<form enctype="multipart/form-data" method="POST" action="<?=$actionUrl?>" id="form_b_catalog_store_docs" name="form_b_catalog_store_docs">
	<?echo GetFilterHiddens("filter_");?>
	<input type="hidden" name="Update" value="Y">
	<input type="hidden" name="lang" value="<?echo LANGUAGE_ID; ?>">
	<input type="hidden" name="ID" value="<?echo $ID ?>">
	<input type="hidden" name="DOCUMENT_TYPE" id="DOCUMENT_TYPE" value="<? echo htmlspecialcharsbx($docType);?>">
	<input type="hidden" name="productAdd" id="productAdd" value="N">
	<input value="<?=$maxId?>" type="hidden" id="ROW_MAX_ID">
	<?=bitrix_sessid_post()?>
	<div class="adm-detail-block" id="tabControl_layout">
		<div class="adm-detail-content-wrap">
			<div class="adm-detail-content-item-block">
				<table class="adm-detail-content-table edit-table" id="cat-doc-table">
					<tbody>
					<?if($ID > 0):?>
						<tr>
							<td width="40%" class="adm-detail-content-cell-l"><span class="cat-doc-status-left-<?=$str_STATUS?>"><?=GetMessage('CAT_DOC_STATUS')?>:</span></td>
							<td width="60%" class="adm-detail-content-cell-r">
								<span class="cat-doc-status-right-<?=$str_STATUS?>">
									<?=GetMessage('CAT_DOC_EXECUTION_'.$str_STATUS)?>
								</span>
							</td>
						</tr>
					<?endif;?>
					<tr class="adm-detail-required-field">
						<td width="40%" class="adm-detail-content-cell-l"><?=GetMessage('CAT_DOC_DATE')?>:</td>
						<td width="60%" class="adm-detail-content-cell-r">
							<?if($bReadOnly):?>
								<?=$str_DATE_DOCUMENT?>
							<?else:?>
								<?= CalendarDate("DOC_DATE", (isset($str_DATE_DOCUMENT)) ? $str_DATE_DOCUMENT : date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()), "form_catalog_document_form", "15", "class=\"typeinput\""); ?>
							<?endif;?>
						</td>
					</tr>
					<tr class="adm-detail-required-field">
						<td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("CAT_DOC_SITE_ID") ?>:</td>
						<td width="60%" class="adm-detail-content-cell-r">
							<select id="SITE_ID" name="SITE_ID" <?=$isDisable?>>
							<?foreach($arSitesShop as $key => $val)
							{
								$selected = ($val['ID'] == $str_SITE_ID) ? 'selected' : '';
								echo"<option ".$selected." value=".htmlspecialcharsbx($val['ID']).">".htmlspecialcharsbx($val["NAME"]." (".$val["ID"].")")."</option>";
							}
							?>
							</select>
						</td>
					</tr>
					<?if(isset($requiredFields["CONTRACTOR"])):?>
						<tr class="adm-detail-required-field">
							<td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("CAT_DOC_CONTRACTOR") ?>:</td>
							<td width="60%" class="adm-detail-content-cell-r">
								<?if(count($arContractors) > 0 && is_array($arContractors)):?>
									<select style="max-width:300px"  name="CONTRACTOR_ID" <?=$isDisable?>>
									<?foreach($arContractors as $key => $val)
									{
										$selected = ($val['ID'] == $str_CONTRACTOR_ID) ? 'selected' : '';
										$companyName = ($val["PERSON_TYPE"] == CONTRACTOR_INDIVIDUAL) ? htmlspecialcharsbx($val["PERSON_NAME"]) : htmlspecialcharsbx($val["COMPANY"]." (".$val["PERSON_NAME"].")");
										echo"<option ".$selected." value=".$val['ID'].">".$companyName."</option>";
									}
									?>
									</select>
								<?else:?>
									<?
										$contractorEditUrl = $selfFolderUrl."cat_contractor_edit.php?lang=".LANGUAGE_ID;
										$contractorEditUrl = $adminSidePanelHelper->editUrlToPublicPage($contractorEditUrl);
									?>
									<a target="_top" href="<?=$contractorEditUrl?>"><?=GetMessage("CAT_DOC_CONTRACTOR_ADD")?></a>
								<?endif;?>
							</td>
						</tr>
					<?endif;?>
					<?if(isset($requiredFields["CURRENCY"])):?>
						<tr class="adm-detail-required-field">
							<td width="40%" class="adm-detail-content-cell-l"><?= GetMessage("CAT_DOC_CURRENCY") ?>:</td>
							<td width="60%" class="adm-detail-content-cell-r"><? echo CCurrency::SelectBox("CAT_CURRENCY_STORE", $str_CURRENCY, "", true, "", "onChange=\"recalculateSum(0);\" id='CAT_CURRENCY_STORE'".$isDisable);?></td>
						</tr>
					<?endif;?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
<?

$aTabs = array();

$tabControl = new CAdminTabControl("storeDocument_".$docType, $aTabs);
$tabControl->Begin();

$lAdmin->DisplayList();
?>
<div class="adm-detail-content-item-block">
	<span style="vertical-align: top">	<?echo GetMessage("CAT_DOC_COMMENT") ?>: </span>
	<textarea cols="120" rows="4" class="typearea" name="CAT_DOC_COMMENTARY" <?=$isDisable?> wrap="virtual"><?= $str_COMMENTARY ?></textarea>
</div>
<?
$tabControl->Buttons(
	array(
		"disabled" => $bReadOnly,
		"btnSave" => false,
		"btnApply" => false,
		"btnCancel" => false,
		"back_url" => $listUrl,
	)
);

if ($adminSidePanelHelper->isSidePanelFrame())
{
	if(!$bReadOnly && !$isDocumentConduct)
	{
		?>
		<span style="display:inline-block; width:20px; height: 22px;"></span>
		<input type="button" class="adm-btn-save" name="save_and_conduct" value="<?echo GetMessage("CAT_DOC_ADD_CONDUCT") ?>">
		<input type="button" class="adm-btn" name="save_document" value="<?echo GetMessage("CAT_DOC_SAVE") ?>">
		<?
	}
	elseif($isDocumentConduct)
	{
		?>
		<span class="hor-spacer"></span>
		<input type="button" class="adm-btn" name="cancellation" value="<?echo GetMessage("CAT_DOC_CANCELLATION") ?>">
		<?
	}
	?>
	<input type="button" class="adm-btn" name="dontsave" value="<?echo GetMessage("CAT_DOC_CANCEL") ?>">
	<?
}
else
{
	if(!$bReadOnly && !$isDocumentConduct)
	{
		?>
		<span style="display:inline-block; width:20px; height: 22px;"></span>
		<input type="submit" class="adm-btn-save" name="save_and_conduct" value="<?echo GetMessage("CAT_DOC_ADD_CONDUCT") ?>">
		<input type="submit" class="adm-btn" name="save_document" value="<?echo GetMessage("CAT_DOC_SAVE") ?>">
		<?
	}
	elseif($isDocumentConduct)
	{
		?>
		<span class="hor-spacer"></span>
		<input type="hidden" name="cancellation" id="cancellation" value = "0">
		<input type="button" class="adm-btn" onclick="if(confirm('<?=GetMessage("CAT_DOC_CANCELLATION_CONFIRM")?>')) {BX('cancellation').value = 1; BX('form_b_catalog_store_docs').submit();}" value="<?echo GetMessage("CAT_DOC_CANCELLATION") ?>">
		<?
	}
	?>
	<input type="submit" class="adm-btn" name="dontsave" id="dontsave" value="<?echo GetMessage("CAT_DOC_CANCEL") ?>">
	<?
}

$tabControl->End();
?></form>
<script type="text/javascript">
BX.Currency.setCurrencies(<? echo CUtil::PhpToJSObject($currencyList, false, true, true); ?>);
if (typeof showTotalSum === 'undefined')
{
	function showTotalSum()
	{
		<?if(isset($requiredFields["TOTAL"])):?>
		if(BX('<?=$sTableID?>'))
		{
			if(BX('<?=$sTableID?>'+'_footer'))
			{
				BX('<?=$sTableID?>'+'_footer').appendChild((BX.create('DIV', {
					props : {
						id : "CAT_DOCUMENT_SUMM"
					},
					style : {
						paddingLeft: '30%',
						marginTop: '5px',
						verticalAlign: 'middle',
						display: 'inline-block'
					},
					children : [
						BX.create('span', {
							props : {
								id : "CAT_DOCUMENT_SUMM_SPAN"
							},
							text : '<?=GetMessageJS('CAT_DOC_TOTAL')?>',
							style : {
								fontSize: '14px',
								fontWeight: 'bold'
							}
						}),
						BX.create('input', {
							props : {
								type : "hidden",
								name : "CAT_DOCUMENT_SUM",
								id : "CAT_DOCUMENT_SUM",
								value : 0
							}
						})
					]
				})));
				var maxId = BX('ROW_MAX_ID').value;
				for(var i = 0; i <= maxId; i++)
				{
					recalculateSum(i);
				}
			}
		}
		<?endif;?>
	}

	function deleteRow(id)
	{
		if(BX('PRODUCT_ID_'+id))
		{
			var trDelete = (BX('PRODUCT_ID_'+id).parentNode.parentNode);
			if(trDelete)
			{
				trDelete.parentNode.removeChild(trDelete);
				recalculateSum(0);
			}
		}
	}

	function findBarcodeDivHider()
	{
		var findBarcodeDiv = BX('cat_barcode_find_div');
		if(findBarcodeDiv)
		{
			if(findBarcodeDiv.style.display == 'none')
			{
				findBarcodeDiv.style.display = 'block';
				BX('CAT_DOC_BARCODE_FIND').focus();
			}
			else
				findBarcodeDiv.style.display = 'none'
		}
	}

	function addProductSearch()
	{
		var store = 0,
			lid = '',
			popup;
		if(BX("CAT_DOC_STORE_FROM"))
			store = BX("CAT_DOC_STORE_FROM").value;
		if(BX("SITE_ID"))
			lid = BX("SITE_ID").value;
		popup = makeProductSearchDialog({
			caller: 'storeDocs',
			lang: '<?=LANGUAGE_ID?>',
			site_id: lid,
			callback: 'addRow',
			store_id: store
		});
		popup.Show();
	}

	function makeProductSearchDialog(params)
	{
		var caller = params.caller || '',
			lang = params.lang || 'ru',
			site_id = params.site_id || '',
			callback = params.callback || '',
			store_id = params.store_id || '0';

		var popup = new BX.CDialog({
			content_url: '<?=$selfFolderUrl?>cat_product_search_dialog.php?lang='+lang+'&LID='+site_id+'&caller=' + caller + '&func_name='+callback+'&STORE_FROM_ID='+store_id,
			height: Math.max(500, window.innerHeight-400),
			width: Math.max(800, window.innerWidth-400),
			draggable: true,
			resizable: true,
			min_height: 500,
			min_width: 800
		});
		BX.addCustomEvent(popup, 'onWindowRegister', BX.defer(function(){
			popup.Get().style.position = 'fixed';
			popup.Get().style.top = (parseInt(popup.Get().style.top) - BX.GetWindowScrollPos().scrollTop) + 'px';
		}));
		return popup;
	}

	function addRow(index, arElement)
	{
		var obProductAdd,
			hiddenDiv;

		obProductAdd = BX('productAdd');
		if (!!obProductAdd)
			obProductAdd.value = 'Y';

		if (typeof index === 'object')
		{
			if (index!==null )
				arElement = index;
		}
		hiddenDiv = BX('ELEMENT_ID_DIV');
		if(hiddenDiv == null)
		{
			hiddenDiv = BX('form_b_catalog_store_docs').appendChild(BX.create(
				'DIV',
				{
					props: {
						id: 'ELEMENT_ID_DIV',
						name: 'ELEMENT_ID_DIV'
					}
				}
			));
		}

		if(!arElement.quantity && arElement.parent)
		{
			arElement.quantity = BX('CAT_DOC_AMOUNT_'+arElement.parent).value;
		}
		var hidden = hiddenDiv.appendChild(BX.create(
			'INPUT',
			{
				props: {
					type: 'hidden',
					name: 'ELEMENT_ID[]',
					value: arElement.id
				},
				html: '<input type="hidden" name="HIDDEN_BARCODE[]" value="' + arElement.barcode + '">' +
					'<input type="hidden" name="HIDDEN_QUANTITY[]" value="' + arElement.quantity + '">' +
					'<input type="hidden" name="AJAX_MODE" value="Y">'
			}
		));

		BX('form_b_catalog_store_docs').submit();
	}

	function copyRow(index, arElement)
	{
		var obProductAdd = BX('productAdd');
		if (!!obProductAdd)
			obProductAdd.disabled = true;
		addRow(index, arElement);
	}

	function productSearch(barcode)
	{
		var dateURL = '<?=bitrix_sessid_get()?>&BARCODE_AJAX=Y&BARCODE='+barcode+'&lang=<? echo LANGUAGE_ID; ?>';

		BX.showWait();
		BX.ajax.post('<?=$selfFolderUrl?>cat_store_product_search.php', dateURL, fSearchProductResult);
	}

	function fSearchProductResult(result)
	{
		BX.closeWait();
		BX("CAT_DOC_BARCODE_FIND").value = '';
		BX("CAT_DOC_BARCODE_FIND").focus();

		var arBarCodes = [],
			obProductAdd;
		if (result.length > 0)
		{
			var res = eval( '('+result+')' );
			if(res['id'] > 0)
			{
				res['quantity'] = 1;
				obProductAdd = BX('productAdd');
				if (!!obProductAdd)
					obProductAdd.disabled = true;
				addRow(null, res, null, arBarCodes);
			}
		}
	}

	function enterBarcodes(id)
	{
		var amount;
		if(BX('CAT_DOC_AMOUNT_HIDDEN_'+id))
			amount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value, 10);
		else
			amount = 0;
		if(isNaN(amount))
			amount = 0;
		maxId = amount;

		var
			content = BX.create('DIV', {
				props: {id : 'BARCODE_DIV_'+id },
				children: [
					BX.create('input', {
						props : {
							className: "BARCODE_INPUT_GREY", id : "BARCODE_INPUT_" + id, value : ""
						}
					}),
					BX.create('input', {
						props : {
							type : 'button', className: "BARCODE_INPUT_button", id : "BARCODE_INPUT_BUTTON_" + id, value : '<?=GetMessageJS('CAT_DOC_ADD')?>' /*disabled: (maxId >= BX('CAT_DOC_AMOUNT_'+id).value)*/
						},
						style : {
							marginLeft: '5px'
						},
						events : {
							click : function()
							{
								if(BX("BARCODE_INPUT_" + id).value.replace(/^\s+|\s+$/g, '') !== '' && !<?=intval($bReadOnly)?>)
								{
									amount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value, 10);
									if(isNaN(amount))
										amount = 0;
									for(var j = 0; j <= 100500; j++)
									{
										if(!BX("BARCODE["+id+"]["+j+"]"))
										{
											counter = j;
											break;
										}
									}
									BX('BARCODE_DIV_'+id).appendChild(BX.create('DIV', {
										props : {
											id : "BARCODE_DIV_INPUT_" + id
										},
										style : {
											padding: '6px'
										},
										children : [
											BX.create('span', {
												props : {
													id : "BARCODE_SPAN_INPUT_" + id
												},
												text : BX('BARCODE_INPUT_'+id).value.replace(/^\s+|\s+$/g, ''),
												style : {
													fontSize: '12'
												}
											}),
											BX.create('input', {
												props : {
													type : 'hidden',
													id : "BARCODE["+id+"]["+counter+"]",
													name : "BARCODE["+id+"]["+counter+"]",
													value : BX('BARCODE_INPUT_'+id).value
												}
											}),
											BX.create('a', {
												props : {
													className : 'split-delete-item',  tabIndex : '-1', href : 'javascript:void(0);', id : "BARCODE_DELETE["+id+"]["+counter+"]"
												},
												events : {
													click : function()
													{
														if(!<?=intval($bReadOnly)?>)
														{
															var deleteNode = this.parentNode;
															if(deleteNode)
																deleteNode.parentNode.removeChild(deleteNode);
															amount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value, 10);
															if(isNaN(amount))
																amount = 0;
															BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value = amount - 1;
															if(BX("BARCODE_INPUT_BUTTON_" + id) && BX("CAT_DOC_AMOUNT_HIDDEN_" + id) && BX('CAT_DOC_AMOUNT_'+id).value > BX("CAT_DOC_AMOUNT_HIDDEN_" + id).value)
																BX("BARCODE_INPUT_BUTTON_" + id).disabled = false;
														}
													}
												},
												style : {
													marginLeft: '8px',
													verticalAlign: '-3'
												}
											})
										]
									}));
									BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value = amount + 1;
									maxId = amount + 1;
									if(maxId >= BX('CAT_DOC_AMOUNT_'+id).value)
										BX("BARCODE_INPUT_BUTTON_" + id).disabled = true;
								}
								BX('BARCODE_INPUT_'+id).value = '';
								BX('BARCODE_INPUT_'+id).focus();
							}
						}
					})
				]
			}),
			formBarcodes = BX.PopupWindowManager.create("catalog-popup-barcodes-"+id, BX("CAT_BARCODE_DIV_BIND_"+id), {
				offsetTop : -50,
				offsetLeft : -50,
				autoHide : false,
				closeByEsc : true,
				closeIcon : false,
				draggable: {
					restrict: true
				},
				content : content
			});
		if(!BX("BARCODE_DIV_INPUT_"+id))
		{
			var savedBarcodes = '';
			if(BX("PRODUCT["+id+"][BARCODE]").value !== '')
				savedBarcodes = BX("PRODUCT["+id+"][BARCODE]").value.split(', ');
			if(savedBarcodes !== '')
			{
				var barCodeAmount = parseInt(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value);
				BX("BARCODE_INPUT_BUTTON_" + id).disabled = (savedBarcodes.length >= BX('CAT_DOC_AMOUNT_'+id).value);
				for(i in savedBarcodes)
				{
					if(savedBarcodes.hasOwnProperty(i) && savedBarcodes[i] != undefined && savedBarcodes[i] != '<?=GetMessage('CAT_DOC_POPUP_TITLE')?>')
					{
						BX('BARCODE_DIV_'+id).appendChild(BX.create('DIV', {
							props : {
								id : "BARCODE_DIV_INPUT_" + id
							},
							style : {
								padding: '6px'
							},
							children : [
								BX.create('span', {
									props : {
										id : "BARCODE_SPAN_INPUT_" + id
									},
									text : savedBarcodes[i],
									style : {
										fontSize: '12'
									}
								}),
								BX.create('input', {
									props : {
										type : 'hidden',
										id : "BARCODE["+id+"]["+i+"]",
										name : "BARCODE["+id+"]["+i+"]",
										value : savedBarcodes[i]
									}
								}),
								BX.create('a', {
									props : {
										className : 'split-delete-item',  tabIndex : '-1', href : 'javascript:void(0);'
									},
									events : {
										click : function()
										{
											if(!<?=intval($bReadOnly)?>)
											{
												var deleteNode = this.parentNode;
												if(deleteNode)
													deleteNode.parentNode.removeChild(deleteNode);
												amount = parseFloat(BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value);
												if(isNaN(amount))
													amount = 0;
												BX('CAT_DOC_AMOUNT_HIDDEN_'+id).value = amount - 1;
												if(BX("BARCODE_INPUT_BUTTON_" + id) && BX("CAT_DOC_AMOUNT_HIDDEN_" + id) && BX('CAT_DOC_AMOUNT_'+id).value > BX("CAT_DOC_AMOUNT_HIDDEN_" + id).value)
													BX("BARCODE_INPUT_BUTTON_" + id).disabled = false;
											}
										}
									},
									style : {
										marginLeft: '8px',
										verticalAlign: '-3'
									}
								})
							]
						}));
					}
				}
			}
		}

		formBarcodes.setButtons([
			<?if(!$bReadOnly):?>
			new BX.PopupWindowButton({
				text : "<?=GetMessage('CAT_DOC_SAVE')?>",
				className : "",
				events : {
					click : function()
					{
						var barcodes = '';
						if(maxId > 0)
						{
							for(var i = 0; i <= maxId; i++)
							{
								if(BX("BARCODE["+id+"]["+i+"]"))
								{
									if(barcodes !== '')
										barcodes = barcodes + ', ';
									if(BX("BARCODE["+id+"]["+i+"]").value !== '')
										barcodes = barcodes + BX("BARCODE["+id+"]["+i+"]").value;
								}
							}
						}

						BX("PRODUCT["+id+"][BARCODE]").value = barcodes;
						recalculateSum(id);
						formBarcodes.close();
					}
				}
			}),
			<?else:?>
			new BX.PopupWindowButton({
				text : "<?=GetMessage('CAT_DOC_CANCEL')?>",
				className : "",
				events : {
					click : function()
					{
						formBarcodes.close();
					}
				}
			})
			<?endif;?>
		]);

		formBarcodes.show();
		if(BX('BARCODE_INPUT_'+id))
			BX('BARCODE_INPUT_'+id).focus();
		<?if($bReadOnly):?>
		var addBarcodeButtons = document.querySelectorAll('.BARCODE_INPUT_button, .BARCODE_INPUT_GREY');
		[].forEach.call(addBarcodeButtons, function disableButtons(item) {
			item.disabled = true;
		});
		var addBarcodeDelBut = document.querySelectorAll('a.split-delete-item');
		[].forEach.call(addBarcodeDelBut, function hideElements(item) {
			item.style.display = 'none';
		});
		<?endif;?>
	}

	function recalculateSum(id)
	{
		<?if(isset($requiredFields["TOTAL"])):?>
		var amount = 0;
		var price = 0;
		if(BX('CAT_DOC_AMOUNT_'+id) && !isNaN(parseFloat(BX('CAT_DOC_AMOUNT_'+id).value)))
			amount = parseFloat(BX('CAT_DOC_AMOUNT_'+id).value);
		if(BX('CAT_DOC_PURCHASING_PRICE_'+id) && !isNaN(parseFloat(BX('CAT_DOC_PURCHASING_PRICE_'+id).value)))
			price = parseFloat(BX('CAT_DOC_PURCHASING_PRICE_'+id).value);
		if(BX('CAT_DOC_SUMM_'+id))
			BX('CAT_DOC_SUMM_'+id).innerHTML = BX.Currency.currencyFormat(amount * price, BX('CAT_CURRENCY_STORE').value, false);
		if(BX('PRODUCT['+id+'][SUMM]'))
			BX('PRODUCT['+id+'][SUMM]').value = (amount * price);
		var maxId = BX('ROW_MAX_ID').value;
		var totalSum = 0;
		for(var i = 0; i <= maxId; i++)
		{
			if(BX('PRODUCT['+i+'][SUMM]'))
			{
				totalSum = totalSum + Number(BX('PRODUCT['+i+'][SUMM]').value);
			}
		}
		if(isNaN(totalSum))
			totalSum = 0;
		if(BX("CAT_DOCUMENT_SUMM_SPAN"))
			BX("CAT_DOCUMENT_SUMM_SPAN").innerHTML = '<?=GetMessage('CAT_DOC_TOTAL')?>' + ': ' + BX.Currency.currencyFormat(totalSum, BX('CAT_CURRENCY_STORE').value, true);
		else
			showTotalSum();
		if(BX("CAT_DOCUMENT_SUM"))
			BX("CAT_DOCUMENT_SUM").value = totalSum;
		<?endif;?>
		if(BX("BARCODE_INPUT_BUTTON_" + id) && BX("CAT_DOC_AMOUNT_HIDDEN_" + id) && BX('CAT_DOC_AMOUNT_'+id).value > BX("CAT_DOC_AMOUNT_HIDDEN_" + id).value)
			BX("BARCODE_INPUT_BUTTON_" + id).disabled = false;
		else if(BX("BARCODE_INPUT_BUTTON_" + id))
			BX("BARCODE_INPUT_BUTTON_" + id).disabled = true;

	}
}
<?
$readyFunc = array();
if (isset($requiredFields["TOTAL"]))
{
	$readyFunc[] = 'showTotalSum();';
}
if (isset($_REQUEST['AJAX_MODE']) && !empty($_POST['productAdd']) && $_POST['productAdd'] == 'Y')
{
	$readyFunc[] = 'addProductSearch();';
}

if (!empty($readyFunc))
{
?>
	BX.ready(BX.defer(function(){
	<? echo implode("\n", $readyFunc); ?>
	}));
<?
}
unset($readyFunc);
?>
</script>
<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");