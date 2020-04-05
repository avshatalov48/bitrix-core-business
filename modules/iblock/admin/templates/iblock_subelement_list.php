<?
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @global CMain $APPLICATION */
/** @global string $strSubIBlockType */
/** @global int $intSubPropValue */
/** @global int $strSubTMP_ID */
/** @global array $arCatalog */
/** @global array $arSubIBlock */
/** @global string $by */
/** @global string $order */
/** @global array $FIELDS_del */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Iblock,
	Bitrix\Currency,
	Bitrix\Catalog;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/*
* B_ADMIN_SUBELEMENTS
* if defined and equal 1 - working, another die
* B_ADMIN_SUBELEMENTS_LIST - true/false
* if not defined - die
* if equal true - get list mode
* 	include prolog and epilog
* other - get simple html
*
* need variables
* 		$strSubElementAjaxPath - path for ajax
* 		$strSubIBlockType - iblock type
* 		$arSubIBlockType - iblock type array
* 		$intSubIBlockID - iblock ID
* 		$arSubIBlock	- array with info about iblock
*		$boolSubWorkFlow - workflow and iblock in workflow
*		$boolSubBizproc - business process and iblock in business
*		$boolSubCatalog - catalog and iblock in catalog
*		$arSubCatalog - info about catalog (with product_iblock_id and sku_property_id info)
*		$intSubPropValue - ID for filter
*		$strSubTMP_ID - string identifier for link with new product ($intSubPropValue = 0, in edit form send -1)
*
*
*created variables
*		$arSubElements - array subelements for product with ID = 0
*/
if (!defined('B_ADMIN_SUBELEMENTS') || 1 != B_ADMIN_SUBELEMENTS)
	return '';
if (!defined('B_ADMIN_SUBELEMENTS_LIST'))
	return '';

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/iblock/admin/iblock_element_admin.php");
IncludeModuleLangFile(__FILE__);

$strSubElementAjaxPath = trim($strSubElementAjaxPath);
$strSubIBlockType = trim($strSubIBlockType);
$intSubIBlockID = (int)$intSubIBlockID;
if ($intSubIBlockID <= 0)
	return;
$boolSubWorkFlow = ($boolSubWorkFlow === true);
$boolSubBizproc = ($boolSubBizproc === true);
$boolSubCatalog = ($boolSubCatalog === true);
$boolSubSearch = false;
$boolSubCurrency = false;
$arCurrencyList = array();
$subuniq_id = 0;
$strUseStoreControl = '';
$strSaveWithoutPrice = '';
$boolCatalogRead = false;
$boolCatalogPrice = false;
$boolCatalogPurchasInfo = false;
$catalogPurchasInfoEdit = false;
$boolCatalogSet = false;
$productTypeList = array();
if ($boolSubCatalog)
{
	$strUseStoreControl = COption::GetOptionString('catalog', 'default_use_store_control');
	$strSaveWithoutPrice = COption::GetOptionString('catalog','save_product_without_price');
	$boolCatalogRead = $USER->CanDoOperation('catalog_read');
	$boolCatalogPrice = $USER->CanDoOperation('catalog_price');
	$boolCatalogPurchasInfo = $USER->CanDoOperation('catalog_purchas_info');
	$boolCatalogSet = CBXFeatures::IsFeatureEnabled('CatCompleteSet');
	$productTypeList = CCatalogAdminTools::getIblockProductTypeList($intSubIBlockID, true);
	if ($boolCatalogPurchasInfo)
		$catalogPurchasInfoEdit = $boolCatalogPrice && $strUseStoreControl != 'Y';
}

define("MODULE_ID", "iblock");
define("ENTITY", "CIBlockDocument");
define("DOCUMENT_TYPE", "iblock_".$intSubIBlockID);

if (isset($_REQUEST['mode']) && ($_REQUEST['mode']=='list' || $_REQUEST['mode']=='frame'))
	CFile::DisableJSFunction(true);

$intSubPropValue = (int)$intSubPropValue;

$strSubTMP_ID = (int)$strSubTMP_ID;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/classes/general/subelement.php');

$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
$minImageSize = array("W" => 1, "H"=>1);
$maxImageSize = array(
	"W" => $listImageSize,
	"H" => $listImageSize,
);
unset($listImageSize);
$useCalendarTime = (string)Main\Config\Option::get('iblock', 'list_full_date_edit') == 'Y';

$dbrFProps = CIBlockProperty::GetList(
	array(
		"SORT" => "ASC",
		"NAME" => "ASC"
	),
	array(
		"IBLOCK_ID" => $intSubIBlockID,
		"ACTIVE" => "Y",
		"CHECK_PERMISSIONS" => "N"
	)
);

$arProps = array();
while($arProp = $dbrFProps->GetNext())
{
	$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
	$arProps[] = $arProp;
}

$sTableID = "tbl_iblock_sub_element_".md5($strSubIBlockType.".".$intSubIBlockID);

$arHideFields = array('PROPERTY_'.$arCatalog['SKU_PROPERTY_ID']);
$lAdmin = new CAdminSubList($sTableID,false,$strSubElementAjaxPath,$arHideFields);
if (!isset($by))
	$by = 'ID';
if (!isset($order))
	$order = 'asc';
$by = strtoupper($by);
switch ($by)
{
	case 'ID':
		$arOrder = array('ID' => $order);
		break;
	case 'CATALOG_TYPE':
		$arOrder = array('CATALOG_TYPE' => $order, 'CATALOG_BUNDLE' => $order, 'ID' => 'ASC');
		break;
	default:
		$arOrder = array($by => $order, 'ID' => 'ASC');
		break;
}

// only sku property filter
$arFilterFields = array(
	"find_el_property_".$arCatalog['SKU_PROPERTY_ID'],
);

$find_section_section = -1;

$section_id = intval($find_section_section);
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;
$sThisSectionUrl = '';

// simple filter
$arFilter = array(
	"IBLOCK_ID" => $intSubIBlockID,
);

if (0 < $intSubPropValue)
	$arFilter["=PROPERTY_".$arSubCatalog['SKU_PROPERTY_ID']] = $intSubPropValue;
else
{
	$arFilter["=PROPERTY_".$arSubCatalog['SKU_PROPERTY_ID']] = $intSubPropValue;
}
$arFilter["CHECK_PERMISSIONS"] = "Y";
$arFilter["MIN_PERMISSION"] = "R";

if (defined('B_ADMIN_SUBELEMENTS_LIST') && true === B_ADMIN_SUBELEMENTS_LIST)
{
	if ($lAdmin->EditAction())
	{
		if (is_array($_FILES['FIELDS']))
			CAllFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);
		if (is_array($FIELDS_del))
			CAllFile::ConvertFilesToPost($FIELDS_del, $_POST['FIELDS'], "del");

		foreach ($_POST['FIELDS'] as $subID => $arFields)
		{
			if (!$lAdmin->IsUpdated($subID))
				continue;
			$subID = (int)$subID;
			if ($subID <= 0)
				continue;

			$arRes = CIBlockElement::GetByID($subID);
			$arRes = $arRes->Fetch();
			if (!$arRes)
				continue;

			$WF_ID = $subID;
			if ($boolSubWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($subID);
				if ($WF_ID != $subID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if ($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $subID;
				}

				if ($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR1")." (ID:".$subID.")", $subID);
					continue;
				}
			}
			elseif ($boolSubBizproc)
			{
				if (CIBlockDocument::IsDocumentLocked($subID, ""))
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $subID)), $subID);
					continue;
				}
			}

			if (
				$boolSubWorkFlow
			)
			{
				if (!CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
					continue;
				}
				$STATUS_PERMISSION = 2;
				// change is under workflow find status and its permissions
				if (!CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_any_wf_status"))
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]);
				if ($STATUS_PERMISSION < 2)
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
					continue;
				}

				// status change  - check permissions
				if (isset($arFields["WF_STATUS_ID"]))
				{
					if (CIBlockElement::WF_GetStatusPermission($arFields["WF_STATUS_ID"])<1)
					{
						$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR2")." (ID:".$subID.")", $subID);
						continue;
					}
				}
			}
			elseif ($bBizproc)
			{
				$bCanWrite = CIBlockDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$USER->GetID(),
					$subID,
					array(
						"IBlockId" => $intSubIBlockID,
						'IBlockRightsMode' => $arSubIBlock['RIGHTS_MODE'],
						'UserGroups' => $USER->GetUserGroupArray(),
					)
				);

				if (!$bCanWrite)
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
					continue;
				}
			}
			elseif (!CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
			{
				$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
				continue;
			}

			if (!is_array($arFields["PROPERTY_VALUES"]))
				$arFields["PROPERTY_VALUES"] = array();
			$bFieldProps = array();
			foreach ($arFields as $k=>$v)
			{
				if (
					$k != "PROPERTY_VALUES"
					&& strncmp($k, "PROPERTY_", 9) == 0
				)
				{
					$prop_id = substr($k, 9);
					$arFields["PROPERTY_VALUES"][$prop_id] = $v;
					unset($arFields[$k]);
					$bFieldProps[$prop_id]=true;
				}
			}
			if (!empty($bFieldProps))
			{
				//We have to read properties from database in order not to delete its values
				if (!$boolSubWorkFlow)
				{
					$dbPropV = CIBlockElement::GetProperty($intSubIBlockID, $subID, "sort", "asc", array("ACTIVE"=>"Y"));
					while ($arPropV = $dbPropV->Fetch())
					{
						if (!array_key_exists($arPropV["ID"], $bFieldProps) && $arPropV["PROPERTY_TYPE"] != "F")
						{
							if (!array_key_exists($arPropV["ID"], $arFields["PROPERTY_VALUES"]))
								$arFields["PROPERTY_VALUES"][$arPropV["ID"]] = array();

							$arFields["PROPERTY_VALUES"][$arPropV["ID"]][$arPropV["PROPERTY_VALUE_ID"]] = array(
								"VALUE" => $arPropV["VALUE"],
								"DESCRIPTION" => $arPropV["DESCRIPTION"],
							);
						}
					}
				}
			}
			else
			{
				//We will not update property values
				unset($arFields["PROPERTY_VALUES"]);
			}

			//All not displayed required fields from DB
			foreach ($arSubIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if (
					$field["IS_REQUIRED"] === "Y"
					&& !array_key_exists($FIELD_ID, $arFields)
					&& $FIELD_ID !== "DETAIL_PICTURE"
					&& $FIELD_ID !== "PREVIEW_PICTURE"
				)
					$arFields[$FIELD_ID] = $arRes[$FIELD_ID];
			}
			if ($arRes["IN_SECTIONS"] == "Y")
			{
				$arFields["IBLOCK_SECTION"] = array();
				$rsSections = CIBlockElement::GetElementGroups($arRes["ID"], true, array('ID', 'IBLOCK_ELEMENT_ID'));
				while ($arSection = $rsSections->Fetch())
					$arFields["IBLOCK_SECTION"][] = $arSection["ID"];
			}

			$arFields["MODIFIED_BY"] = $USER->GetID();
			$ib = new CIBlockElement();
			$DB->StartTransaction();

			if (!$ib->Update($subID, $arFields, true, true, true))
			{
				$lAdmin->AddUpdateError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#"=>$subID, "#ERROR_TEXT#"=>$ib->LAST_ERROR)), $subID);
				$DB->Rollback();
			}
			else
			{
				$DB->Commit();
			}

			if ($boolSubCatalog)
			{
				if (
					$boolCatalogPrice && CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_price")
				)
				{
					$arCatalogProduct = array();
					if (isset($arFields['CATALOG_WEIGHT']) && '' != $arFields['CATALOG_WEIGHT'])
						$arCatalogProduct['WEIGHT'] = $arFields['CATALOG_WEIGHT'];

					if (isset($arFields['CATALOG_WIDTH']) && '' != $arFields['CATALOG_WIDTH'])
						$arCatalogProduct['WIDTH'] = $arFields['CATALOG_WIDTH'];
					if (isset($arFields['CATALOG_LENGTH']) && '' != $arFields['CATALOG_LENGTH'])
						$arCatalogProduct['LENGTH'] = $arFields['CATALOG_LENGTH'];
					if (isset($arFields['CATALOG_HEIGHT']) && '' != $arFields['CATALOG_HEIGHT'])
						$arCatalogProduct['HEIGHT'] = $arFields['CATALOG_HEIGHT'];

					if (isset($arFields['CATALOG_VAT_INCLUDED']) && !empty($arFields['CATALOG_VAT_INCLUDED']))
						$arCatalogProduct['VAT_INCLUDED'] = $arFields['CATALOG_VAT_INCLUDED'];
					if (isset($arFields['CATALOG_QUANTITY_TRACE']) && !empty($arFields['CATALOG_QUANTITY_TRACE']))
						$arCatalogProduct['QUANTITY_TRACE'] = $arFields['CATALOG_QUANTITY_TRACE'];
					if (isset($arFields['CATALOG_MEASURE']) && is_string($arFields['CATALOG_MEASURE']) && (int)$arFields['CATALOG_MEASURE'] > 0)
						$arCatalogProduct['MEASURE'] = $arFields['CATALOG_MEASURE'];

					if ($catalogPurchasInfoEdit)
					{
						if (
							isset($arFields['CATALOG_PURCHASING_PRICE']) && is_string($arFields['CATALOG_PURCHASING_PRICE']) && $arFields['CATALOG_PURCHASING_PRICE'] != ''
							&& isset($arFields['CATALOG_PURCHASING_CURRENCY']) && is_string($arFields['CATALOG_PURCHASING_CURRENCY']) && $arFields['CATALOG_PURCHASING_CURRENCY'] != ''
						)
						{
							$arCatalogProduct['PURCHASING_PRICE'] = $arFields['CATALOG_PURCHASING_PRICE'];
							$arCatalogProduct['PURCHASING_CURRENCY'] = $arFields['CATALOG_PURCHASING_CURRENCY'];
						}
					}

					if ($strUseStoreControl != 'Y')
					{
						if (isset($arFields['CATALOG_QUANTITY']) && '' != $arFields['CATALOG_QUANTITY'])
							$arCatalogProduct['QUANTITY'] = $arFields['CATALOG_QUANTITY'];
					}

					$product = Catalog\ProductTable::getList(array(
						'select' => array('ID', 'SUBSCRIBE_ORIG'),
						'filter' => array('=ID' => $subID)
					))->fetch();
					if (empty($product))
					{
						$arCatalogProduct['ID'] = $subID;
						CCatalogProduct::Add($arCatalogProduct, false);
					}
					else
					{
						if (!empty($arCatalogProduct))
						{
							if ($strUseStoreControl != 'Y')
								$arCatalogProduct['SUBSCRIBE'] = $product['SUBSCRIBE_ORIG'];
							CCatalogProduct::Update($subID, $arCatalogProduct);
						}
					}
					unset($product);

					if (isset($arFields['CATALOG_MEASURE_RATIO']))
					{
						$newValue = trim($arFields['CATALOG_MEASURE_RATIO']);
						if ($newValue != '')
						{
							$intRatioID = 0;
							$ratio = Catalog\MeasureRatioTable::getList(array(
								'select' => array('ID', 'PRODUCT_ID'),
								'filter' => array('=PRODUCT_ID' => $subID, '=IS_DEFAULT' => 'Y'),
							))->fetch();
							if (!empty($ratio))
								$intRatioID = (int)$ratio['ID'];
							if ($intRatioID > 0)
								$ratioResult = CCatalogMeasureRatio::update($intRatioID, array('RATIO' => $newValue));
							else
								$ratioResult = CCatalogMeasureRatio::add(array('PRODUCT_ID' => $subID, 'RATIO' => $newValue, 'IS_DEFAULT' => 'Y'));
						}
						unset($newValue);
					}
				}
			}
		}

		if ($boolSubCatalog)
		{
			if ($boolCatalogPrice && (isset($_POST["CATALOG_PRICE"]) || isset($_POST["CATALOG_CURRENCY"])))
			{
				$CATALOG_PRICE = $_POST["CATALOG_PRICE"];
				$CATALOG_CURRENCY = $_POST["CATALOG_CURRENCY"];
				$CATALOG_EXTRA = $_POST["CATALOG_EXTRA"];
				$CATALOG_PRICE_ID = $_POST["CATALOG_PRICE_ID"];
				$CATALOG_QUANTITY_FROM = $_POST["CATALOG_QUANTITY_FROM"];
				$CATALOG_QUANTITY_TO = $_POST["CATALOG_QUANTITY_TO"];
				$CATALOG_PRICE_old = $_POST["CATALOG_old_PRICE"];
				$CATALOG_CURRENCY_old = $_POST["CATALOG_old_CURRENCY"];

				$arCatExtraUp = array();
				$db_extras = CExtra::GetList(array("ID" => "ASC"));
				while ($extras = $db_extras->Fetch())
					$arCatExtraUp[$extras["ID"]] = $extras["PERCENTAGE"];

				$arBaseGroup = CCatalogGroup::GetBaseGroup();
				$arCatalogGroupList = CCatalogGroup::GetListArray();
				foreach ($CATALOG_PRICE as $elID => $arPrice)
				{
					if (!(CIBlockElementRights::UserHasRightTo($intSubIBlockID, $elID, "element_edit_price")
						&& CIBlockElementRights::UserHasRightTo($intSubIBlockID, $elID, "element_edit")))
						continue;
					//1 Find base price ID
					//2 If such a column is displayed then
					//	check if it is greater than 0
					//3 otherwise
					//	look up it's value in database and
					//	output an error if not found or found less or equal then zero
					$bError = false;

					if ($strSaveWithoutPrice != 'Y')
					{
						if (isset($arPrice[$arBaseGroup['ID']]))
						{
							if ($arPrice[$arBaseGroup['ID']] < 0)
							{
								$bError = true;
								$lAdmin->AddUpdateError($elID.': '.GetMessage('IB_CAT_NO_BASE_PRICE'), $elID);
							}
						}
						else
						{
							$arBasePrice = CPrice::GetBasePrice(
								$elID,
								$CATALOG_QUANTITY_FROM[$elID][$arBaseGroup['ID']],
								$CATALOG_QUANTITY_FROM[$elID][$arBaseGroup['ID']],
								false
							);

							if (!is_array($arBasePrice) || $arBasePrice['PRICE'] < 0)
							{
								$bError = true;
								$lAdmin->AddGroupError($elID.': '.GetMessage('IB_CAT_NO_BASE_PRICE'), $elID);
							}
						}
					}
					if ($bError)
						continue;

					$arCurrency = $CATALOG_CURRENCY[$elID];

					if (!empty($arCatalogGroupList))
					{
						foreach ($arCatalogGroupList as $arCatalogGroup)
						{
							if ($arPrice[$arCatalogGroup["ID"]] != $CATALOG_PRICE_old[$elID][$arCatalogGroup["ID"]]
								|| $arCurrency[$arCatalogGroup["ID"]] != $CATALOG_CURRENCY_old[$elID][$arCatalogGroup["ID"]])
							{
								if ('Y' == $arCatalogGroup["BASE"]) // if base price check extra for other prices
								{
									$arFields = array(
										"PRODUCT_ID" => $elID,
										"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
										"PRICE" => $arPrice[$arCatalogGroup["ID"]],
										"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
										"QUANTITY_FROM" => $CATALOG_QUANTITY_FROM[$elID][$arCatalogGroup["ID"]],
										"QUANTITY_TO" => $CATALOG_QUANTITY_TO[$elID][$arCatalogGroup["ID"]],
									);
									if (is_string($arFields['PRICE']))
										$arFields['PRICE'] = str_replace(',', '.', $arFields['PRICE']);
									if ($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
										CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
									elseif ((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
										CPrice::Update($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
									elseif ($arFields["PRICE"] >= 0)
										CPrice::Add($arFields);

									$arPrFilter = array(
										"PRODUCT_ID" => $elID,
									);
									if ($arPrice[$arCatalogGroup["ID"]] >= 0)
									{
										$arPrFilter["!CATALOG_GROUP_ID"] = $arCatalogGroup["ID"];
										$arPrFilter["+QUANTITY_FROM"] = "1";
										$arPrFilter["!EXTRA_ID"] = false;
									}
									$db_res = CPrice::GetListEx(
										array(),
										$arPrFilter,
										false,
										false,
										array("ID", "PRODUCT_ID", "CATALOG_GROUP_ID", "PRICE", "CURRENCY", "QUANTITY_FROM", "QUANTITY_TO", "EXTRA_ID")
									);
									while ($ar_res = $db_res->Fetch())
									{
										$arFields = array(
											"PRICE" => $arPrice[$arCatalogGroup["ID"]]*(1+$arCatExtraUp[$ar_res["EXTRA_ID"]]/100) ,
											"EXTRA_ID" => $ar_res["EXTRA_ID"],
											"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
											"QUANTITY_FROM" => $ar_res["QUANTITY_FROM"],
											"QUANTITY_TO" => $ar_res["QUANTITY_TO"]
										);
										if ($arFields["PRICE"] <= 0)
											CPrice::Delete($ar_res["ID"]);
										else
											CPrice::Update($ar_res["ID"], $arFields);
									}
								}
								elseif (!isset($CATALOG_EXTRA[$elID][$arCatalogGroup["ID"]]))
								{
									$arFields = array(
										"PRODUCT_ID" => $elID,
										"CATALOG_GROUP_ID" => $arCatalogGroup["ID"],
										"PRICE" => $arPrice[$arCatalogGroup["ID"]],
										"CURRENCY" => $arCurrency[$arCatalogGroup["ID"]],
										"QUANTITY_FROM" => $CATALOG_QUANTITY_FROM[$elID][$arCatalogGroup["ID"]],
										"QUANTITY_TO" => $CATALOG_QUANTITY_TO[$elID][$arCatalogGroup["ID"]]
									);
									if (is_string($arFields['PRICE']))
										$arFields['PRICE'] = str_replace(',', '.', $arFields['PRICE']);
									if ($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
										CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
									elseif ((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
										CPrice::Update((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
									elseif ($arFields["PRICE"] >= 0)
										CPrice::Add($arFields);
								}
							}
						}
						unset($arCatalogGroup);
					}
				}
				unset($arCatalogGroupList);
			}

			if ($intSubPropValue > 0)
			{
				$productIblock = CIBlockElement::GetIBlockByID($intSubPropValue);
				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($productIblock, $intSubPropValue);
				$ipropValues->clearValues();
				\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($productIblock, $intSubPropValue);
				unset($productIblock);
			}
		}
	}

	if (($arID = $lAdmin->GroupAction()))
	{
		if ($_REQUEST['action_target']=='selected')
		{
			$rsData = CIBlockElement::GetList($arOrder, $arFilter, false, false, array('ID'));
			while($arRes = $rsData->Fetch())
				$arID[] = $arRes['ID'];
		}

		foreach ($arID as $subID)
		{
			$subID = (int)$subID;
			if ($subID <= 0)
				continue;

			$arRes = CIBlockElement::GetByID($subID);
			$arRes = $arRes->Fetch();
			if (!$arRes)
				continue;

			$WF_ID = $subID;
			if ($boolSubWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($subID);
				if ($WF_ID != $subID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if ($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $subID;
				}

				if ($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR1")." (ID:".$subID.")", $subID);
					continue;
				}
			}
			elseif ($boolSubBizproc)
			{
				if (CIBlockDocument::IsDocumentLocked($subID, "") && !($_REQUEST['action']=='unlock' && CBPDocument::IsAdmin()))
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR_LOCKED", array("#ID#" => $subID)), $subID);
					continue;
				}
			}

			$bPermissions = false;
			//delete and modify can:
			if ($boolSubWorkFlow)
			{
				//For delete action we have to check all statuses in element history
				$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"], $_REQUEST['action']=="delete"? $subID: false);
				if ($STATUS_PERMISSION >= 2)
				$bPermissions = true;
			}
			elseif ($boolSubBizproc)
			{
				$bCanWrite = CIBlockDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$USER->GetID(),
					$subID,
					array(
						"IBlockId" => $intSubIBlockID,
						'IBlockRightsMode' => $arSubIBlock['RIGHTS_MODE'],
						'UserGroups' => $USER->GetUserGroupArray(),
					)
				);

				if ($bCanWrite)
					$bPermissions = true;
			}
			else
			{
				$bPermissions = true;
			}

			if (!$bPermissions)
			{
				$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
				continue;
			}

			switch($_REQUEST['action'])
			{
			case "delete":
				if (CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_delete"))
				{
					@set_time_limit(0);
					$DB->StartTransaction();
					$APPLICATION->ResetException();
					if (!CIBlockElement::Delete($subID))
					{
						$DB->Rollback();
						if ($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR")." [".$ex->GetString()."]", $subID);
						else
							$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR"), $subID);
					}
					else
					{
						$DB->Commit();
					}
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLOCK_DELETE_ERROR")." [".$subID."]", $subID);
				}
				break;
			case "activate":
			case "deactivate":
				if (CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
				{
					$ob = new CIBlockElement();
					$arFields = array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
					if (!$ob->Update($subID, $arFields, true))
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$ob->LAST_ERROR, $subID);
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
				}
				break;
			case "wf_status":
				if ($boolSubWorkFlow)
				{
					$new_status = intval($_REQUEST["wf_status_id"]);
					if (
						$new_status > 0
					)
					{
						if (CIBlockElement::WF_GetStatusPermission($new_status) > 0
							|| CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit_any_wf_status")
						)
						{
							if ($arRes["WF_STATUS_ID"] != $new_status)
							{
								$obE = new CIBlockElement();
								$res = $obE->Update($subID, array(
									"WF_STATUS_ID" => $new_status,
									"MODIFIED_BY" => $USER->GetID(),
								), true);
								if (!$res)
									$lAdmin->AddGroupError(GetMessage("IBEL_A_SAVE_ERROR", array("#ID#" => $subID, "#ERROR_TEXT#" => $obE->LAST_ERROR)), $subID);
							}
						}
						else
						{
							$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
						}
					}
				}
				break;
			case "lock":
				if ($bWorkFlow && !CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
				}
				CIBlockElement::WF_Lock($subID);
				break;
			case "unlock":
				if ($bWorkFlow && !CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
					continue;
				}
				if ($bBizproc)
					call_user_func(array(ENTITY, "UnlockDocument"), $subID, "");
				else
					CIBlockElement::WF_UnLock($subID);
				break;
			case 'clear_counter':
				if (CIBlockElementRights::UserHasRightTo($intSubIBlockID, $subID, "element_edit"))
				{
					$ob = new CIBlockElement();
					$arFields = array('SHOW_COUNTER' => false, 'SHOW_COUNTER_START' => false);
					if (!$ob->Update($subID, $arFields, true))
						$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR").$ob->LAST_ERROR, $subID);
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBEL_A_UPDERR3")." (ID:".$subID.")", $subID);
				}
				break;
			}
		}
	}
}

CJSCore::Init(array('translit'));

if (true == B_ADMIN_SUBELEMENTS_LIST)
	CJSCore::Init(array('date'));

$arHeader = array();
if ($boolSubCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_TYPE",
		"content" => GetMessage("IBEL_CATALOG_TYPE"),
		"title" => GetMessage('IBEL_CATALOG_TYPE_TITLE'),
		"align" => "right",
		"sort" => "CATALOG_TYPE",
		"default" => true,
	);
}
$arHeader[] = array("id"=>"NAME", "content"=>GetMessage("IBLOCK_FIELD_NAME"), "sort"=>"name", "default"=>true);

$arHeader[] = array("id"=>"ACTIVE", "content"=>GetMessage("IBLOCK_FIELD_ACTIVE"), "sort"=>"active", "default"=>true, "align"=>"center");
$arHeader[] = array("id"=>"SORT", "content"=>GetMessage("IBLOCK_FIELD_SORT"), "sort"=>"sort", "default"=>true, "align"=>"right");
$arHeader[] = array("id"=>"TIMESTAMP_X", "content"=>GetMessage("IBLOCK_FIELD_TIMESTAMP_X"), "sort"=>"timestamp_x");
$arHeader[] = array("id"=>"USER_NAME", "content"=>GetMessage("IBLOCK_FIELD_USER_NAME"), "sort"=>"modified_by");
$arHeader[] = array("id"=>"DATE_CREATE", "content"=>GetMessage("IBLOCK_EL_ADMIN_DCREATE"), "sort"=>"created");
$arHeader[] = array("id"=>"CREATED_USER_NAME", "content"=>GetMessage("IBLOCK_EL_ADMIN_WCREATE2"), "sort"=>"created_by");

$arHeader[] = array("id"=>"CODE", "content"=>GetMessage("IBEL_A_CODE"), "sort"=>"code");
$arHeader[] = array("id"=>"EXTERNAL_ID", "content"=>GetMessage("IBEL_A_EXTERNAL_ID"), "sort"=>"external_id");
$arHeader[] = array("id"=>"TAGS", "content"=>GetMessage("IBEL_A_TAGS"), "sort"=>"tags");

if ($boolSubWorkFlow)
{
	$arHeader[] = array("id"=>"WF_STATUS_ID", "content"=>GetMessage("IBLOCK_FIELD_STATUS"), "sort"=>"status", "default"=>true);
	$arHeader[] = array("id"=>"WF_NEW", "content"=>GetMessage("IBEL_A_EXTERNAL_WFNEW"), "sort"=>"");
	$arHeader[] = array("id"=>"LOCK_STATUS", "content"=>GetMessage("IBEL_A_EXTERNAL_LOCK"), "default"=>true);
	$arHeader[] = array("id"=>"LOCKED_USER_NAME", "content"=>GetMessage("IBEL_A_EXTERNAL_LOCK_BY"));
	$arHeader[] = array("id"=>"WF_DATE_LOCK", "content"=>GetMessage("IBEL_A_EXTERNAL_LOCK_WHEN"));
	$arHeader[] = array("id"=>"WF_COMMENTS", "content"=>GetMessage("IBEL_A_EXTERNAL_COM"));
}

$arHeader[] = array("id"=>"ID", "content"=>'ID', "sort"=>"id", "default"=>true, "align"=>"right");
$arHeader[] = array("id"=>"SHOW_COUNTER", "content"=>GetMessage("IBEL_A_EXTERNAL_SHOWS"), "sort"=>"show_counter", "align"=>"right");
$arHeader[] = array("id"=>"SHOW_COUNTER_START", "content"=>GetMessage("IBEL_A_EXTERNAL_SHOW_F"), "sort"=>"show_counter_start", "align"=>"right");
$arHeader[] = array("id"=>"PREVIEW_PICTURE", "content"=>GetMessage("IBEL_A_EXTERNAL_PREV_PIC"), "sort" => "has_preview_picture");
$arHeader[] = array("id"=>"PREVIEW_TEXT", "content"=>GetMessage("IBEL_A_EXTERNAL_PREV_TEXT"));
$arHeader[] = array("id"=>"DETAIL_PICTURE", "content"=>GetMessage("IBEL_A_EXTERNAL_DET_PIC"), "sort" => "has_detail_picture");
$arHeader[] = array("id"=>"DETAIL_TEXT", "content"=>GetMessage("IBEL_A_EXTERNAL_DET_TEXT"));


foreach ($arProps as &$arFProps)
{
	if ($arSubCatalog['SKU_PROPERTY_ID'] != $arFProps['ID'])
		$arHeader[] = array("id"=>"PROPERTY_".$arFProps['ID'], "content"=>$arFProps['NAME'], "align"=>($arFProps["PROPERTY_TYPE"]=='N'?"right":"left"), "sort" => ($arFProps["MULTIPLE"]!='Y'? "PROPERTY_".$arFProps['ID'] : ""));
}
if (isset($arFProps))
	unset($arFProps);

$arWFStatus = array();
if ($boolSubWorkFlow)
{
	$rsWF = CWorkflowStatus::GetDropDownList('Y');
	while($arWF = $rsWF->GetNext())
		$arWFStatus[$arWF["~REFERENCE_ID"]] = $arWF["~REFERENCE"];
}

if ($boolSubCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_AVAILABLE",
		"content" => GetMessage("IBEL_CATALOG_AVAILABLE"),
		"title" => GetMessage("IBEL_CATALOG_AVAILABLE_TITLE_EXT"),
		"align" => "center",
		"sort" => "CATALOG_AVAILABLE",
		"default" => true,
	);
	$arHeader[] = array(
		"id" => "CATALOG_QUANTITY",
		"content" => GetMessage("IBEL_CATALOG_QUANTITY_EXT"),
		"align" => "right",
		"sort" => "CATALOG_QUANTITY",
	);
	$arHeader[] = array(
		"id" => "CATALOG_QUANTITY_RESERVED",
		"content" => GetMessage("IBEL_CATALOG_QUANTITY_RESERVED"),
		"align" => "right"
	);
	$arHeader[] = array(
		"id" => "CATALOG_MEASURE_RATIO",
		"content" => GetMessage("IBEL_CATALOG_MEASURE_RATIO"),
		"title" => GetMessage('IBEL_CATALOG_MEASURE_RATIO_TITLE'),
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_MEASURE",
		"content" => GetMessage("IBEL_CATALOG_MEASURE"),
		"title" => GetMessage('IBEL_CATALOG_MEASURE_TITLE'),
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_QUANTITY_TRACE",
		"content" => GetMessage("IBEL_CATALOG_QUANTITY_TRACE"),
		"align" => "right",
	);
	$arHeader[] = array(
		"id" => "CATALOG_WEIGHT",
		"content" => GetMessage("IBEL_CATALOG_WEIGHT"),
		"align" => "right",
		"sort" => "CATALOG_WEIGHT",
	);
	$arHeader[] = array(
		"id" => "CATALOG_WIDTH",
		"content" => GetMessage("IBEL_CATALOG_WIDTH"),
		"title" => "",
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_LENGTH",
		"content" => GetMessage("IBEL_CATALOG_LENGTH"),
		"title" => "",
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_HEIGHT",
		"content" => GetMessage("IBEL_CATALOG_HEIGHT"),
		"title" => "",
		"align" => "right",
		"default" => false,
	);
	$arHeader[] = array(
		"id" => "CATALOG_VAT_INCLUDED",
		"content" => GetMessage("IBEL_CATALOG_VAT_INCLUDED"),
		"align" => "right",
	);
	if ($boolCatalogPurchasInfo)
	{
		$arHeader[] = array(
			"id" => "CATALOG_PURCHASING_PRICE",
			"content" => GetMessage("IBEL_CATALOG_PURCHASING_PRICE"),
			"title" => "",
			"align" => "right",
			"sort" => "CATALOG_PURCHASING_PRICE",
			"default" => false,
		);
	}
	if ($strUseStoreControl == "Y")
	{
		$arHeader[] = array(
			"id" => "CATALOG_BAR_CODE",
			"content" => GetMessage("IBEL_CATALOG_BAR_CODE"),
			"title" => "",
			"align" => "right",
			"default" => false,
		);
	}

	$arCatGroup = CCatalogGroup::GetListArray();
	if (!empty($arCatGroup))
	{
		foreach ($arCatGroup as $priceType)
		{
			$arHeader[] = array(
				"id" => "CATALOG_GROUP_".$priceType["ID"],
				"content" => htmlspecialcharsEx(!empty($priceType["NAME_LANG"]) ? $priceType["NAME_LANG"] : $priceType["NAME"]),
				"align" => "right",
				"sort" => "CATALOG_PRICE_".$priceType["ID"],
				"default" => false,
			);
		}
		unset($priceType);
	}

	$arCatExtra = array();
	$db_extras = CExtra::GetList(array("ID" =>"ASC"));
	while ($extras = $db_extras->Fetch())
		$arCatExtra[$extras['ID']] = $extras;
	unset($extras, $db_extras);
}

if($bCatalog)
{
	$arHeader[] = array(
		"id" => "SUBSCRIPTIONS",
		"content" => GetMessage("IBLOCK_FIELD_SUBSCRIPTIONS"),
		"default" => false,
	);
}

if ($boolSubBizproc)
{
	$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array("iblock", "CIBlockDocument", "iblock_".$intSubIBlockID));
	foreach ($arWorkflowTemplates as $arTemplate)
	{
		$arHeader[] = array(
			"id" => "WF_".$arTemplate["ID"],
			"content" => $arTemplate["NAME"],
		);
	}
	$arHeader[] = array(
		"id" => "BIZPROC",
		"content" => GetMessage("IBEL_A_BP_H"),
	);
	$arHeader[] = array(
		"id" => "BP_PUBLISHED",
		"content" => GetMessage("IBLOCK_FIELD_BP_PUBLISHED"),
		"sort" => "status",
		"default" => true,
	);
}

$lAdmin->AddHeaders($arHeader);

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();

$arSelectedProps = array();
$selectedPropertyIds = array();
$arSelect = array();
foreach ($arProps as $i => $arProperty)
{
	$k = array_search("PROPERTY_".$arProperty['ID'], $arSelectedFields);
	if ($k !== false)
	{
		$arSelectedProps[] = $arProperty;
		$selectedPropertyIds[] = $arProperty['ID'];
		if ($arProperty["PROPERTY_TYPE"] == "L")
		{
			$arSelect[$arProperty['ID']] = array();
			$rs = CIBlockProperty::GetPropertyEnum($arProperty['ID']);
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = $ar["VALUE"];
		}
		elseif ($arProperty["PROPERTY_TYPE"] == "G")
		{
			$arSelect[$arProperty['ID']] = array();
			$rs = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$arProperty["LINK_IBLOCK_ID"]), array("ID", "NAME", "DEPTH_LEVEL"));
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"];
		}
		unset($arSelectedFields[$k]);
	}
}

if (!in_array("ID", $arSelectedFields))
	$arSelectedFields[] = "ID";
if (!in_array("CREATED_BY", $arSelectedFields))
	$arSelectedFields[] = "CREATED_BY";

$arSelectedFields[] = "LANG_DIR";
$arSelectedFields[] = "LID";
$arSelectedFields[] = "WF_PARENT_ELEMENT_ID";

if (in_array("LOCKED_USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "WF_LOCKED_BY";
if (in_array("USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "MODIFIED_BY";
if (in_array("CREATED_USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "CREATED_BY";
if (in_array("PREVIEW_TEXT", $arSelectedFields))
	$arSelectedFields[] = "PREVIEW_TEXT_TYPE";
if (in_array("DETAIL_TEXT", $arSelectedFields))
	$arSelectedFields[] = "DETAIL_TEXT_TYPE";

$arSelectedFields[] = "LOCK_STATUS";
$arSelectedFields[] = "WF_NEW";
$arSelectedFields[] = "WF_STATUS_ID";
$arSelectedFields[] = "DETAIL_PAGE_URL";
$arSelectedFields[] = "SITE_ID";
$arSelectedFields[] = "CODE";
$arSelectedFields[] = "EXTERNAL_ID";

if ($boolSubCatalog)
{
	if(in_array("CATALOG_QUANTITY_TRACE", $arSelectedFields))
		$arSelectedFields[] = "CATALOG_QUANTITY_TRACE_ORIG";
	if (in_array('CATALOG_TYPE', $arSelectedFields) && $boolCatalogSet)
		$arSelectedFields[] = 'CATALOG_BUNDLE';
	$boolPriceInc = false;
	if ($boolCatalogPurchasInfo)
	{
		if (in_array("CATALOG_PURCHASING_PRICE", $arSelectedFields))
		{
			$arSelectedFields[] = "CATALOG_PURCHASING_CURRENCY";
			$boolPriceInc = true;
		}
	}
	if (is_array($arCatGroup) && !empty($arCatGroup))
	{
		foreach ($arCatGroup as &$CatalogGroups)
		{
			if (in_array("CATALOG_GROUP_".$CatalogGroups["ID"], $arSelectedFields))
			{
				$arFilter["CATALOG_SHOP_QUANTITY_".$CatalogGroups["ID"]] = 1;
				$boolPriceInc = true;
			}
		}
		unset($CatalogGroups);
	}
	if ($boolPriceInc)
	{
		$boolSubCurrency = Loader::includeModule('currency');
		if ($boolSubCurrency)
			$arCurrencyList = array_keys(Currency\CurrencyManager::getCurrencyList());
	}
	unset($boolPriceInc);
}

$arSelectedFieldsMap = array();
foreach ($arSelectedFields as $field)
	$arSelectedFieldsMap[$field] = true;

$measureList = array(0 => ' ');
if (isset($arSelectedFieldsMap['CATALOG_MEASURE']))
{
	$measureIterator = CCatalogMeasure::getList(array(), array(), false, false, array('ID', 'MEASURE_TITLE', 'SYMBOL_RUS'));
	while($measure = $measureIterator->Fetch())
		$measureList[$measure['ID']] = ($measure['SYMBOL_RUS'] != '' ? $measure['SYMBOL_RUS'] : $measure['MEASURE_TITLE']);
	unset($measure, $measureIterator);
}

if (!(false == B_ADMIN_SUBELEMENTS_LIST && $bCopy))
{
	$wf_status_id = "";

	if(isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "excel")
		$arNavParams = false;
	else
		$arNavParams = array("nPageSize"=>CAdminSubResult::GetNavSize($sTableID, 20, $lAdmin->GetListUrl(true)));

	$rsData = CIBlockElement::GetList(
		$arOrder,
		$arFilter,
		false,
		$arNavParams,
		$arSelectedFields
	);
	$rsData = new CAdminSubResult($rsData, $sTableID, $lAdmin->GetListUrl(true));
	$wf_status_id = false;

	$rsData->NavStart();
	$lAdmin->NavText($rsData->GetNavPrint(htmlspecialcharsbx($arSubIBlock["ELEMENTS_NAME"])));

	function GetElementName($ID)
	{
		$ID = intval($ID);
		static $cache = array();
		if (!isset($cache[$ID]))
		{
			$rsElement = CIBlockElement::GetList(array(), array("ID"=>$ID, "SHOW_HISTORY"=>"Y"), false, false, array("ID","IBLOCK_ID","NAME"));
			$cache[$ID] = $rsElement->GetNext();
		}
		return $cache[$ID];
	}
	function GetIBlockTypeID($intSubIBlockID)
	{
		$intSubIBlockID = (int)$intSubIBlockID;
		if (0 > $intSubIBlockID)
			$intSubIBlockID = 0;
		static $cache = array();
		if (!isset($cache[$intSubIBlockID]))
		{
			$rsIBlock = CIBlock::GetByID($intSubIBlockID);
			if (!($cache[$intSubIBlockID] = $rsIBlock->GetNext()))
				$cache[$intSubIBlockID] = array("IBLOCK_TYPE_ID"=>"");
		}
		return $cache[$intSubIBlockID]["IBLOCK_TYPE_ID"];
	}

	$arRows = array();
	$listItemId = array();

	$boolSubSearch = Loader::includeModule('search');

	$boolOldOffers = false;
	while ($arRes = $rsData->NavNext(true, "f_"))
	{
		$arRes_orig = $arRes;
		// in workflow mode show latest changes
		if ($boolSubWorkFlow)
		{
			$LAST_ID = CIBlockElement::WF_GetLast($arRes['ID']);
			if ($LAST_ID!=$arRes['ID'])
			{
				$rsData2 = CIBlockElement::GetList(
					array(),
					array(
						"ID"=>$LAST_ID,
						"SHOW_HISTORY"=>"Y"
						),
					false,
					array("nTopCount"=>1),
					$arSelectedFields
				);
				if (isset($arCatGroup))
				{
					$arRes_tmp = Array();
					foreach ($arRes as $vv => $vval)
					{
						if (substr($vv, 0, 8) == "CATALOG_")
							$arRes_tmp[$vv] = $arRes[$vv];
					}
				}

				$arRes = $rsData2->NavNext(true, "f_");
				if (isset($arCatGroup))
					$arRes = array_merge($arRes, $arRes_tmp);

				$f_ID = $arRes_orig["ID"];
			}
			$lockStatus = $arRes_orig['LOCK_STATUS'];
		}
		elseif ($boolSubBizproc)
		{
			$lockStatus = CIBlockDocument::IsDocumentLocked($f_ID, "") ? "red" : "green";
		}
		else
		{
			$lockStatus = "";
		}

		if (isset($arSelectedFieldsMap["CATALOG_QUANTITY_TRACE"]))
		{
			$arRes['CATALOG_QUANTITY_TRACE'] = $arRes['CATALOG_QUANTITY_TRACE_ORIG'];
			$f_CATALOG_QUANTITY_TRACE = $f_CATALOG_QUANTITY_TRACE_ORIG;
		}

		if (isset($arSelectedFieldsMap['CATALOG_MEASURE']))
		{
			$arRes['CATALOG_MEASURE'] = (int)$arRes['CATALOG_MEASURE'];
			if ($arRes['CATALOG_MEASURE'] < 0)
				$arRes['CATALOG_MEASURE'] = 0;
		}

		$arRes['lockStatus'] = $lockStatus;
		$arRes["orig"] = $arRes_orig;

		$edit_url = CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$arRes_orig['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		);
		$arRows[$f_ID] = $row = $lAdmin->AddRow($f_ID, $arRes, $edit_url, GetMessage("IB_SE_L_EDIT_ELEMENT"), true);

		$listItemId[] = $f_ID;

		$boolEditPrice = false;
		$boolEditPrice = CIBlockElementRights::UserHasRightTo($intSubIBlockID, $f_ID, "element_edit_price");

		$row->AddViewField("ID",$f_ID);

		if ($f_LOCKED_USER_NAME)
			$row->AddViewField("LOCKED_USER_NAME", '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_WF_LOCKED_BY.'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$f_LOCKED_USER_NAME.'</a>');
		if ($f_USER_NAME)
			$row->AddViewField("USER_NAME", '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_MODIFIED_BY.'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$f_USER_NAME.'</a>');
		$row->AddViewField("CREATED_USER_NAME", '<a href="/bitrix/admin/user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_CREATED_BY.'" title="'.GetMessage("IBEL_A_USERINFO").'">'.$f_CREATED_USER_NAME.'</a>');

		if ($boolSubWorkFlow || $boolSubBizproc)
		{
			$lamp = '<span class="adm-lamp adm-lamp-in-list adm-lamp-'.$lockStatus.'"></span>';
			if ($lockStatus=='red' && $arRes_orig['LOCKED_USER_NAME']!='')
				$row->AddViewField("LOCK_STATUS", $lamp.$arRes_orig['LOCKED_USER_NAME']);
			else
				$row->AddViewField("LOCK_STATUS", $lamp);
		}

		if ($boolSubBizproc)
			$row->AddCheckField("BP_PUBLISHED", false);

		$row->arRes['props'] = array();
		$arProperties = array();
		if (!empty($arSelectedProps))
		{
			$rsProperties = CIBlockElement::GetProperty($intSubIBlockID, $arRes['ID'], 'id', 'asc', array('ID' => $selectedPropertyIds));
			while($ar = $rsProperties->GetNext())
			{
				if (!array_key_exists($ar["ID"], $arProperties))
					$arProperties[$ar["ID"]] = array();
				$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar;
			}
			unset($ar);
			unset($rsProperties);
		}

		foreach ($arSelectedProps as $aProp)
		{
			$arViewHTML = array();
			$arEditHTML = array();
			if (strlen($aProp["USER_TYPE"])>0)
				$arUserType = CIBlockProperty::GetUserType($aProp["USER_TYPE"]);
			else
				$arUserType = array();
			$max_file_size_show=100000;

			$last_property_id = false;
			foreach ($arProperties[$aProp["ID"]] as $prop_id => $prop)
			{
				$prop['PROPERTY_VALUE_ID'] = intval($prop['PROPERTY_VALUE_ID']);
				$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].'][VALUE]';
				$DESCR_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].'][DESCRIPTION]';
				//View part
				if (array_key_exists("GetAdminListViewHTML", $arUserType))
				{
					$arViewHTML[] = call_user_func_array($arUserType["GetAdminListViewHTML"],
						array(
							$prop,
							array(
								"VALUE" => $prop["~VALUE"],
								"DESCRIPTION" => $prop["~DESCRIPTION"]
							),
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $DESCR_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							),
						));
				}
				elseif ($prop['PROPERTY_TYPE']=='N')
					$arViewHTML[] = $prop["VALUE"];
				elseif ($prop['PROPERTY_TYPE']=='S')
					$arViewHTML[] = $prop["VALUE"];
				elseif ($prop['PROPERTY_TYPE']=='L')
					$arViewHTML[] = $prop["VALUE_ENUM"];
				elseif ($prop['PROPERTY_TYPE']=='F')
				{
					$arViewHTML[] = CFileInput::Show('NO_FIELDS['.$prop['PROPERTY_VALUE_ID'].']', $prop["VALUE"], array(
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
				}
				elseif ($prop['PROPERTY_TYPE']=='G')
				{
					if (intval($prop["VALUE"])>0)
					{
						$rsSection = CIBlockSection::GetList(
							array(),
							array("ID" => $prop["VALUE"]),
							false,
							array('ID', 'NAME', 'IBLOCK_ID')
						);
						if ($arSection = $rsSection->GetNext())
						{
							$arViewHTML[] = $arSection['NAME'].
							' [<a href="'.
							htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($arSection['IBLOCK_ID'], $arSection['ID'])).
							'" title="'.GetMessage("IBEL_A_SEC_EDIT").'">'.$arSection['ID'].'</a>]';
						}
					}
				}
				elseif ($prop['PROPERTY_TYPE']=='E')
				{
					if ($t = GetElementName($prop["VALUE"]))
					{
						$arViewHTML[] = $t['NAME'].
						' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'], array(
						'WF' => 'Y'
						))).'" title="'.GetMessage("IBEL_A_EL_EDIT").'">'.$t['ID'].'</a>]';
					}
				}
				//Edit Part
				$bUserMultiple = $prop["MULTIPLE"] == "Y" && array_key_exists("GetPropertyFieldHtmlMulty", $arUserType);
				if ($bUserMultiple)
				{
					if ($last_property_id != $prop["ID"])
					{
						$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']';
						$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtmlMulty"], array(
							$prop,
							$arProperties[$prop["ID"]],
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $VALUE_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							)
						));
					}
				}
				elseif (array_key_exists("GetPropertyFieldHtml", $arUserType))
				{
					$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtml"],
						array(
							$prop,
							array(
								"VALUE" => $prop["~VALUE"],
								"DESCRIPTION" => $prop["~DESCRIPTION"],
							),
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $DESCR_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							),
						));
				}
				elseif ($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
				{
					if ($prop["ROW_COUNT"] > 1)
						$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'">'.$prop["VALUE"].'</textarea>';
					else
						$html = '<input type="text" name="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="'.$prop["COL_COUNT"].'">';
					if ($prop["WITH_DESCRIPTION"] == "Y")
						$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").
							'<input type="text" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'" size="18"></span>';
					$arEditHTML[] = $html;
				}
				elseif ($prop['PROPERTY_TYPE']=='L' && ($last_property_id!=$prop["ID"]))
				{
					$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][]';
					$arValues = array();
					foreach ($arProperties[$prop["ID"]] as $g_prop)
					{
						$g_prop = intval($g_prop["VALUE"]);
						if ($g_prop > 0)
							$arValues[$g_prop] = $g_prop;
					}
					if ($prop['LIST_TYPE']=='C')
					{
						if ($prop['MULTIPLE'] == "Y" || count($arSelect[$prop['ID']]) == 1)
						{
							$html = '<input type="hidden" name="'.$VALUE_NAME.'" value="">';
							foreach ($arSelect[$prop['ID']] as $value => $display)
							{
								$html .= '<input type="checkbox" name="'.$VALUE_NAME.'" id="subid'.$subuniq_id.'" value="'.$value.'"';
								if (array_key_exists($value, $arValues))
									$html .= ' checked';
								$html .= '>&nbsp;<label for="subid'.$subuniq_id.'">'.$display.'</label><br>';
								$subuniq_id++;
							}
						}
						else
						{
							$html = '<input type="radio" name="'.$VALUE_NAME.'" id="subid'.$subuniq_id.'" value=""';
							if (count($arValues) < 1)
								$html .= ' checked';
							$html .= '>&nbsp;<label for="subid'.$subuniq_id.'">'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</label><br>';
							$subuniq_id++;
							foreach ($arSelect[$prop['ID']] as $value => $display)
							{
								$html .= '<input type="radio" name="'.$VALUE_NAME.'" id="subid'.$subuniq_id.'" value="'.$value.'"';
								if (array_key_exists($value, $arValues))
									$html .= ' checked';
								$html .= '>&nbsp;<label for="subid'.$subuniq_id.'">'.$display.'</label><br>';
								$subuniq_id++;
							}
						}
					}
					else
					{
						$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
						$html .= '<option value=""'.(count($arValues) < 1? ' selected': '').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
						foreach ($arSelect[$prop['ID']] as $value => $display)
						{
							$html .= '<option value="'.$value.'"';
							if (array_key_exists($value, $arValues))
								$html .= ' selected';
							$html .= '>'.$display.'</option>'."\n";
						}
						$html .= "</select>\n";
					}
					$arEditHTML[] = $html;
				}
				elseif ($prop['PROPERTY_TYPE']=='F' && ($last_property_id!=$prop["ID"]))
				{
					if($prop['MULTIPLE'] == "Y")
					{
						$arOneFileControl = array();
						foreach($arProperties[$prop["ID"]] as $g_prop)
						{
							$arOneFileControl[] = CFileInput::Show(
								'NO_FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$g_prop['PROPERTY_VALUE_ID'].']',
								$g_prop["VALUE"],
								array(
									"IMAGE" => "Y",
									"PATH" => "Y",
									"FILE_SIZE" => "Y",
									"DIMENSIONS" => "Y",
									"IMAGE_POPUP" => "Y",
									"MAX_SIZE" => $maxImageSize,
									"MIN_SIZE" => $minImageSize,
								),
								false,
								array(
									'upload' => false,
									'medialib' => false,
									'file_dialog' => false,
									'cloud' => false,
									'del' => false,
									'description' => false,
								)
							);
						}
						if (!empty($arOneFileControl))
						{
							$arEditHTML[] = implode('<br>', $arOneFileControl);
						}
					}
					else
					{
						$arEditHTML[] = CFileInput::Show(
							$VALUE_NAME,
							$prop["VALUE"],
							array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
								"MAX_SIZE" => $maxImageSize,
								"MIN_SIZE" => $minImageSize,
								),
							array(
								'upload' => false,
								'medialib' => false,
								'file_dialog' => false,
								'cloud' => false,
								'del' => false,
								'description' => false,
							)
						);
					}
				}
				elseif (($prop['PROPERTY_TYPE']=='G') && ($last_property_id!=$prop["ID"]))
				{
					$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][]';
					$arValues = array();
					foreach ($arProperties[$prop["ID"]] as $g_prop)
					{
						$g_prop = intval($g_prop["VALUE"]);
						if ($g_prop > 0)
							$arValues[$g_prop] = $g_prop;
					}
					$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
					$html .= '<option value=""'.(count($arValues) < 1? ' selected': '').'>'.GetMessage("IBLOCK_ELEMENT_EDIT_NOT_SET").'</option>';
					foreach ($arSelect[$prop['ID']] as $value => $display)
					{
						$html .= '<option value="'.$value.'"';
						if (array_key_exists($value, $arValues))
							$html .= ' selected';
						$html .= '>'.$display.'</option>'."\n";
					}
					$html .= "</select>\n";
					$arEditHTML[] = $html;
				}
				elseif ($prop['PROPERTY_TYPE']=='E')
				{
					$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].']';
					$fixIBlock = $prop["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID'];
					if ($t = GetElementName($prop["VALUE"]))
					{
						$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" >'.$t['NAME'].'</span>';
					}
					else
					{
						$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					}
					unset($windowTableId);
					unset($fixIBlock);
				}
				$last_property_id = $prop['ID'];
			}
			$table_id = md5($f_ID.':'.$aProp['ID']);
			if ($aProp["MULTIPLE"] == "Y")
			{
				$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0][VALUE]';
				$DESCR_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0][DESCRIPTION]';
				if (array_key_exists("GetPropertyFieldHtmlMulty", $arUserType))
				{
				}
				elseif (('F' != $prop['PROPERTY_TYPE']) && array_key_exists("GetPropertyFieldHtml", $arUserType))
				{
					$arEditHTML[] = call_user_func_array($arUserType["GetPropertyFieldHtml"],
						array(
							$prop,
							array(
								"VALUE" => "",
								"DESCRIPTION" => "",
							),
							array(
								"VALUE" => $VALUE_NAME,
								"DESCRIPTION" => $DESCR_NAME,
								"MODE"=>"iblock_element_admin",
								"FORM_NAME"=>"form_".$sTableID,
							),
						));
				}
				elseif ($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
				{
					if ($prop["ROW_COUNT"] > 1)
						$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'"></textarea>';
					else
						$html = '<input type="text" name="'.$VALUE_NAME.'" value="" size="'.$prop["COL_COUNT"].'">';
					if ($prop["WITH_DESCRIPTION"] == "Y")
						$html .= ' <span title="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC").'">'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_DESC_1").'<input type="text" name="'.$DESCR_NAME.'" value="" size="18"></span>';
					$arEditHTML[] = $html;
				}
				elseif ($prop['PROPERTY_TYPE']=='F')
				{
				}
				elseif ($prop['PROPERTY_TYPE']=='E')
				{
					$VALUE_NAME = 'FIELDS['.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0]';
					$fixIBlock = $prop["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID'];
					$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					unset($windowTableId);
					unset($fixIBlock);
				}

				if ($prop["PROPERTY_TYPE"]!=="F" && $prop["PROPERTY_TYPE"]!=="G" && $prop["PROPERTY_TYPE"]!=="L" && !$bUserMultiple)
					$arEditHTML[] = '<input type="button" value="'.GetMessage("IBLOCK_ELEMENT_EDIT_PROP_ADD").'" onClick="addNewRow(\'tb'.$table_id.'\')">';
			}
			if (count($arViewHTML) > 0)
				$row->AddViewField("PROPERTY_".$aProp['ID'], implode(" / ", $arViewHTML)."&nbsp;");
			if (count($arEditHTML) > 0)
				$row->arRes['props']["PROPERTY_".$aProp['ID']] = array("table_id"=>$table_id, "html"=>$arEditHTML);
		}

		if ($boolSubCatalog)
		{
			if (isset($arCatGroup) && !empty($arCatGroup))
			{
				$row->arRes['price'] = array();
				foreach ($arCatGroup as &$CatGroup)
				{
					if (isset($arSelectedFieldsMap["CATALOG_GROUP_".$CatGroup["ID"]]))
					{
						$price = "";
						$sHTML = "";
						$selectCur = "";
						$extraId = (isset($arRes['CATALOG_EXTRA_ID_'.$CatGroup['ID']]) ? (int)$arRes['CATALOG_EXTRA_ID_'.$CatGroup['ID']] : 0);
						if (!isset($arCatExtra[$extraId]))
							$extraId = 0;
						if ($boolSubCurrency)
						{
							$price = htmlspecialcharsEx(CCurrencyLang::CurrencyFormat(
								$arRes["CATALOG_PRICE_".$CatGroup["ID"]],
								$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]],
								true
							));
							if ($extraId > 0)
							{
								$price .= ' <span title="'.
									htmlspecialcharsbx(GetMessage(
										'IBSEL_CATALOG_EXTRA_DESCRIPTION',
										array('#VALUE#' => $arCatExtra[$extraId]['NAME'])
									)).
									'">(+'.$arCatExtra[$extraId]['PERCENTAGE'].'%)</span>';
							}
							if ($boolCatalogPrice && $boolEditPrice)
							{
								$selectCur = '<select name="CATALOG_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']" id="CATALOG_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']"';
								if ($CatGroup["BASE"]=="Y")
									$selectCur .= ' onchange="top.SubChangeBaseCurrency('.$f_ID.')"';
								elseif ($extraId > 0)
									$selectCur .= ' disabled="disabled" readonly="readonly"';
								$selectCur .= '>';
								foreach ($arCurrencyList as &$currencyCode)
								{
									$selectCur .= '<option value="'.$currencyCode.'"';
									if ($currencyCode == $arRes["CATALOG_CURRENCY_".$CatGroup["ID"]])
										$selectCur .= ' selected';
									$selectCur .= '>'.$currencyCode.'</option>';
								}
								unset($currencyCode);
								$selectCur .= '</select>';
							}
						}
						else
						{
							$price = htmlspecialcharsEx($arRes["CATALOG_PRICE_".$CatGroup["ID"]]." ".$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]]);
						}

						$row->AddViewField("CATALOG_GROUP_".$CatGroup["ID"], $price);

						if ($boolCatalogPrice && $boolEditPrice)
						{
							$sHTML = '<input type="text" size="9" id="CATALOG_PRICE['.$f_ID.']['.$CatGroup["ID"].']" name="CATALOG_PRICE['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_PRICE_".$CatGroup["ID"]].'"';
							if ($CatGroup["BASE"]=="Y")
								$sHTML .= ' onchange="top.SubChangeBasePrice('.$f_ID.')"';
							elseif ($extraId > 0)
								$sHTML .= ' disabled readonly';
							$sHTML .= '> '.$selectCur;
							if ($extraId > 0)
								$sHTML .= '<input type="hidden" id="CATALOG_EXTRA['.$f_ID.']['.$CatGroup["ID"].']" name="CATALOG_EXTRA['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_EXTRA_ID_".$CatGroup["ID"]].'">';

							$sHTML .= '<input type="hidden" name="CATALOG_old_PRICE['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_PRICE_".$CatGroup["ID"]].'">';
							$sHTML .= '<input type="hidden" name="CATALOG_old_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_CURRENCY_".$CatGroup["ID"]].'">';
							$sHTML .= '<input type="hidden" name="CATALOG_PRICE_ID['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_PRICE_ID_".$CatGroup["ID"]].'">';
							$sHTML .= '<input type="hidden" name="CATALOG_QUANTITY_FROM['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_QUANTITY_FROM_".$CatGroup["ID"]].'">';
							$sHTML .= '<input type="hidden" name="CATALOG_QUANTITY_TO['.$f_ID.']['.$CatGroup["ID"].']" value="'.$arRes["CATALOG_QUANTITY_TO_".$CatGroup["ID"]].'">';

							$row->arRes['price']["CATALOG_GROUP_".$CatGroup["ID"]] = $sHTML;
						}
						unset($extraId);
					}
				}
				unset($CatGroup);
			}
			if (isset($arSelectedFieldsMap['CATALOG_MEASURE_RATIO']))
			{
				$row->arRes['CATALOG_MEASURE_RATIO'] = ' ';
			}
		}

		if ($boolSubBizproc)
		{
			$arDocumentStates = CBPDocument::GetDocumentStates(
				array("iblock", "CIBlockDocument", "iblock_".$intSubIBlockID),
				array("iblock", "CIBlockDocument", $f_ID)
			);

			$arRes["CURRENT_USER_GROUPS"] = $USER->GetUserGroupArray();
			if ($arRes["CREATED_BY"] == $USER->GetID())
				$arRes["CURRENT_USER_GROUPS"][] = "Author";
			$row->arRes["CURRENT_USER_GROUPS"] = $arRes["CURRENT_USER_GROUPS"];

			$arStr = array();
			$arStr1 = array();
			foreach ($arDocumentStates as $kk => $vv)
			{
				$canViewWorkflow = CIBlockDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::ViewWorkflow,
					$USER->GetID(),
					$f_ID,
					array("AllUserGroups" => $arRes["CURRENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates, "WorkflowId" => $kk)
				);
				if (!$canViewWorkflow)
					continue;

				$arStr1[$vv["TEMPLATE_ID"]] = $vv["TEMPLATE_NAME"];
				$arStr[$vv["TEMPLATE_ID"]] .= "<a href=\"/bitrix/admin/bizproc_log.php?ID=".$kk."\">".(strlen($vv["STATE_TITLE"]) > 0 ? $vv["STATE_TITLE"] : $vv["STATE_NAME"])."</a><br />";

				if (strlen($vv["ID"]) > 0)
				{
					$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $vv["ID"]);
					foreach ($arTasks as $arTask)
					{
						$arStr[$vv["TEMPLATE_ID"]] .= GetMessage("IBEL_A_BP_TASK").":<br /><a href=\"bizproc_task.php?id=".$arTask["ID"]."\" title=\"".$arTask["DESCRIPTION"]."\">".$arTask["NAME"]."</a><br /><br />";
					}
				}
			}

			$str = "";
			foreach ($arStr as $k => $v)
			{
				$row->AddViewField("WF_".$k, $v);
				$str .= "<b>".(strlen($arStr1[$k]) > 0 ? $arStr1[$k] : GetMessage("IBEL_A_BP_PROC"))."</b>:<br />".$v."<br />";
			}

			$row->AddViewField("BIZPROC", $str);
		}
	}

	$boolIBlockElementAdd = CIBlockSectionRights::UserHasRightTo($intSubIBlockID, $find_section_section, "section_element_bind");

$availQuantityTrace = COption::GetOptionString('catalog', 'default_quantity_trace');
$arQuantityTrace = array(
	"D" => GetMessage("IBEL_DEFAULT_VALUE")." (".($availQuantityTrace == 'Y' ? GetMessage("IBEL_YES_VALUE") : GetMessage("IBEL_NO_VALUE")).")",
	"Y" => GetMessage("IBEL_YES_VALUE"),
	"N" => GetMessage("IBEL_NO_VALUE"),
);

if (!empty($arRows))
{
	$arRowKeys = array_keys($arRows);
	if ($strUseStoreControl == "Y" && in_array("CATALOG_BAR_CODE", $arSelectedFields))
	{
		$productsWithBarCode = array();
		$rsProducts = Catalog\ProductTable::getList(array(
			'select' => array('ID', 'BARCODE_MULTI'),
			'filter' => array('@ID' => $arRowKeys)
		));
		while ($product = $rsProducts->fetch())
		{
			if (isset($arRows[$product["ID"]]))
			{
				if ($product["BARCODE_MULTI"] == "Y")
					$arRows[$product["ID"]]->arRes["CATALOG_BAR_CODE"] = GetMessage("IBEL_CATALOG_BAR_CODE_MULTI");
				else
					$productsWithBarCode[] = $product["ID"];
			}
		}
		if (!empty($productsWithBarCode))
		{
			$rsProducts = CCatalogStoreBarCode::getList(array(), array(
				"PRODUCT_ID" => $productsWithBarCode,
			));
			while ($product = $rsProducts->Fetch())
			{
				if (isset($arRows[$product["PRODUCT_ID"]]))
				{
					$arRows[$product["PRODUCT_ID"]]->arRes["CATALOG_BAR_CODE"] = htmlspecialcharsEx($product["BARCODE"]);
				}
			}
		}
	}

	if (isset($arSelectedFieldsMap['CATALOG_MEASURE_RATIO']))
	{
		$iterator = Catalog\MeasureRatioTable::getList(array(
			'select' => array('ID', 'PRODUCT_ID', 'RATIO'),
			'filter' => array('@PRODUCT_ID' => $arRowKeys, '=IS_DEFAULT' => 'Y')
		));
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['PRODUCT_ID'];
			if (isset($arRows[$id]))
				$arRows[$id]->arRes['CATALOG_MEASURE_RATIO'] = $row['RATIO'];
			unset($id);
		}
		unset($row, $iterator);
	}
}

	$arElementOps = CIBlockElementRights::UserHasRightTo(
		$intSubIBlockID,
		array_keys($arRows),
		"",
		CIBlockRights::RETURN_OPERATIONS
	);
	/** @var CAdminListRow $row */
	foreach ($arRows as $f_ID => $row)
	{
		$edit_url = CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$row->arRes['orig']['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		);

		if (array_key_exists("PREVIEW_PICTURE", $arSelectedFieldsMap))
		{
			$row->AddFileField("PREVIEW_PICTURE", array(
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
		}
		if (array_key_exists("DETAIL_PICTURE", $arSelectedFieldsMap))
		{
			$row->AddFileField("DETAIL_PICTURE", array(
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
		}
		if (array_key_exists("PREVIEW_TEXT", $arSelectedFieldsMap))
			$row->AddViewField("PREVIEW_TEXT", ($row->arRes["PREVIEW_TEXT_TYPE"]=="text" ? htmlspecialcharsEx($row->arRes["PREVIEW_TEXT"]) : HTMLToTxt($row->arRes["PREVIEW_TEXT"])));
		if (array_key_exists("DETAIL_TEXT", $arSelectedFieldsMap))
			$row->AddViewField("DETAIL_TEXT", ($row->arRes["DETAIL_TEXT_TYPE"]=="text" ? htmlspecialcharsEx($row->arRes["DETAIL_TEXT"]) : HTMLToTxt($row->arRes["DETAIL_TEXT"])));

		if (isset($arElementOps[$f_ID]) && isset($arElementOps[$f_ID]["element_edit"]))
		{
			if (isset($arElementOps[$f_ID]) && isset($arElementOps[$f_ID]["element_edit_price"]))
			{
				if (isset($row->arRes['price']) && is_array($row->arRes['price']))
					foreach ($row->arRes['price'] as $price_id => $sHTML)
						$row->AddEditField($price_id, $sHTML);
			}

			$row->AddCheckField("ACTIVE");
			$row->AddInputField("NAME", array('size'=>'35'));
			$row->AddViewField("NAME", '<div class="iblock_menu_icon_elements"></div>'.htmlspecialcharsEx($row->arRes["NAME"]));
			$row->AddInputField("SORT", array('size'=>'3'));
			$row->AddInputField("CODE");
			$row->AddInputField("EXTERNAL_ID");
			if ($boolSubSearch)
			{
				$row->AddViewField("TAGS", htmlspecialcharsEx($row->arRes["TAGS"]));
				$row->AddEditField("TAGS", InputTags("FIELDS[".$f_ID."][TAGS]", $row->arRes["TAGS"], $arSubIBlock["SITE_ID"]));
			}
			else
			{
				$row->AddInputField("TAGS");
			}
			if ($arWFStatus)
			{
				$row->AddSelectField("WF_STATUS_ID", $arWFStatus);
				if ($row->arRes['orig']['WF_NEW']=='Y' || $row->arRes['WF_STATUS_ID']=='1')
					$row->AddViewField("WF_STATUS_ID", htmlspecialcharsEx($arWFStatus[$row->arRes['WF_STATUS_ID']]));
				else
					$row->AddViewField("WF_STATUS_ID", '<a href="'.$edit_url.'" title="'.GetMessage("IBEL_A_ED_TITLE").'">'.htmlspecialcharsEx($arWFStatus[$row->arRes['WF_STATUS_ID']]).'</a> / <a href="'.'iblock_element_edit.php?ID='.$row->arRes['orig']['ID'].(!isset($arElementOps[$f_ID]) || !isset($arElementOps[$f_ID]["element_edit_any_wf_status"])?'&view=Y':'').$sThisSectionUrl.'" title="'.GetMessage("IBEL_A_ED2_TITLE").'">'.htmlspecialcharsEx($arWFStatus[$row->arRes['orig']['WF_STATUS_ID']]).'</a>');
			}
			if (array_key_exists("PREVIEW_TEXT", $arSelectedFieldsMap))
			{
				$sHTML = '<input type="radio" name="FIELDS['.$f_ID.'][PREVIEW_TEXT_TYPE]" value="text" id="'.$f_ID.'PREVIEWtext"';
				if ($row->arRes["PREVIEW_TEXT_TYPE"]!="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_ID.'PREVIEWtext">text</label> /';
				$sHTML .= '<input type="radio" name="FIELDS['.$f_ID.'][PREVIEW_TEXT_TYPE]" value="html" id="'.$f_ID.'PREVIEWhtml"';
				if ($row->arRes["PREVIEW_TEXT_TYPE"]=="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_ID.'PREVIEWhtml">html</label><br>';
				$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$f_ID.'][PREVIEW_TEXT]">'.htmlspecialcharsbx($row->arRes["PREVIEW_TEXT"]).'</textarea>';
				$row->AddEditField("PREVIEW_TEXT", $sHTML);
			}
			if (array_key_exists("DETAIL_TEXT", $arSelectedFieldsMap))
			{
				$sHTML = '<input type="radio" name="FIELDS['.$f_ID.'][DETAIL_TEXT_TYPE]" value="text" id="'.$f_ID.'DETAILtext"';
				if ($row->arRes["DETAIL_TEXT_TYPE"]!="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_ID.'DETAILtext">text</label> /';
				$sHTML .= '<input type="radio" name="FIELDS['.$f_ID.'][DETAIL_TEXT_TYPE]" value="html" id="'.$f_ID.'DETAILhtml"';
				if ($row->arRes["DETAIL_TEXT_TYPE"]=="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_ID.'DETAILhtml">html</label><br>';

				$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$f_ID.'][DETAIL_TEXT]">'.htmlspecialcharsbx($row->arRes["DETAIL_TEXT"]).'</textarea>';
				$row->AddEditField("DETAIL_TEXT", $sHTML);
			}
			foreach ($row->arRes['props'] as $prop_id => $arEditHTML)
				$row->AddEditField($prop_id, '<table id="tb'.$arEditHTML['table_id'].'" border=0 cellpadding=0 cellspacing=0><tr><td nowrap>'.implode("</td></tr><tr><td nowrap>", $arEditHTML['html']).'</td></tr></table>');

			if (isset($arElementOps[$f_ID]["element_edit_price"]) && $boolCatalogPrice)
			{
				if ($strUseStoreControl == "Y")
				{
					$row->AddInputField("CATALOG_QUANTITY", false);
				}
				else
				{
					$row->AddInputField("CATALOG_QUANTITY");
				}
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $arQuantityTrace);
				$row->AddInputField("CATALOG_WEIGHT");
				$row->AddInputField('CATALOG_WIDTH');
				$row->AddInputField('CATALOG_HEIGHT');
				$row->AddInputField('CATALOG_LENGTH');
				$row->AddCheckField("CATALOG_VAT_INCLUDED");
				if ($boolCatalogPurchasInfo)
				{
					$price = '';
					if ((float)$row->arRes['CATALOG_PURCHASING_PRICE'] > 0)
					{
						if ($boolSubCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
					if ($catalogPurchasInfoEdit && $boolSubCurrency)
					{
						$editFieldCode = '<input type="hidden" name="FIELDS_OLD['.$f_ID.'][CATALOG_PURCHASING_PRICE]" value="'.$row->arRes['CATALOG_PURCHASING_PRICE'].'">';
						$editFieldCode .= '<input type="hidden" name="FIELDS_OLD['.$f_ID.'][CATALOG_PURCHASING_CURRENCY]" value="'.$row->arRes['CATALOG_PURCHASING_CURRENCY'].'">';
						$editFieldCode .= '<input type="text" size="5" name="FIELDS['.$f_ID.'][CATALOG_PURCHASING_PRICE]" value="'.$row->arRes['CATALOG_PURCHASING_PRICE'].'">';
						$editFieldCode .= '<select name="FIELDS['.$f_ID.'][CATALOG_PURCHASING_CURRENCY]">';
						foreach ($arCurrencyList as &$currencyCode)
						{
							$editFieldCode .= '<option value="'.$currencyCode.'"';
							if ($currencyCode == $row->arRes['CATALOG_PURCHASING_CURRENCY'])
								$editFieldCode .= ' selected';
							$editFieldCode .= '>'.$currencyCode.'</option>';
						}
						$editFieldCode .= '</select>';
						$row->AddEditField('CATALOG_PURCHASING_PRICE', $editFieldCode);
						unset($editFieldCode);
					}
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO");
			}
			elseif ($boolCatalogRead)
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddInputField("CATALOG_QUANTITY", false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $arQuantityTrace, false);
				$row->AddInputField("CATALOG_WEIGHT", false);
				$row->AddInputField('CATALOG_WIDTH', false);
				$row->AddInputField('CATALOG_HEIGHT', false);
				$row->AddInputField('CATALOG_LENGTH', false);
				$row->AddCheckField("CATALOG_VAT_INCLUDED", false);
				if ($boolCatalogPurchasInfo)
				{
					$price = '';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($boolSubCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO", false);
			}
		}
		else
		{
			$row->AddCheckField("ACTIVE", false);
			$row->AddViewField("NAME", '<div class="iblock_menu_icon_elements"></div>'.htmlspecialcharsEx($row->arRes["NAME"]));
			$row->AddInputField("SORT", false);
			$row->AddInputField("CODE", false);
			$row->AddInputField("EXTERNAL_ID", false);
			$row->AddViewField("TAGS", htmlspecialcharsEx($row->arRes["TAGS"]));
			if ($arWFStatus)
			{
				$row->AddViewField("WF_STATUS_ID", htmlspecialcharsEx($arWFStatus[$row->arRes['WF_STATUS_ID']]));
			}
			if ($boolSubCatalog)
			{
				$row->AddCheckField('CATALOG_AVAILABLE', false);
				$row->AddInputField("CATALOG_QUANTITY", false);
				$row->AddSelectField("CATALOG_QUANTITY_TRACE", $arQuantityTrace, false);
				$row->AddInputField("CATALOG_WEIGHT", false);
				$row->AddInputField('CATALOG_WIDTH', false);
				$row->AddInputField('CATALOG_HEIGHT', false);
				$row->AddInputField('CATALOG_LENGTH', false);
				$row->AddCheckField("CATALOG_VAT_INCLUDED", false);
				if ($boolCatalogPurchasInfo)
				{
					$price = '';
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($boolSubCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					unset($price);
				}
				$row->AddInputField("CATALOG_MEASURE_RATIO", false);
			}
		}
		if (isset($arSelectedFieldsMap['CATALOG_TYPE']))
		{
			$strProductType = '';
			if ($intSubPropValue <= 0 && $row->arRes['CATALOG_TYPE'] == Catalog\ProductTable::TYPE_FREE_OFFER)
				$row->arRes['CATALOG_TYPE'] = Catalog\ProductTable::TYPE_OFFER;
			if (isset($productTypeList[$row->arRes["CATALOG_TYPE"]]))
				$strProductType = $productTypeList[$row->arRes["CATALOG_TYPE"]];
			if ($row->arRes['CATALOG_BUNDLE'] == 'Y' && $boolCatalogSet)
				$strProductType .= ('' != $strProductType ? ', ' : '').GetMessage('IBEL_CATALOG_TYPE_MESS_GROUP');
			$row->AddViewField('CATALOG_TYPE', $strProductType);
		}
		if ($bCatalog && isset($arSelectedFieldsMap['CATALOG_MEASURE']))
		{
			if (isset($arElementOps[$f_ID]["element_edit_price"]) && $boolCatalogPrice)
			{
				$row->AddSelectField('CATALOG_MEASURE', $measureList);
			}
			else
			{
				$measureTitle = (isset($measureList[$row->arRes['CATALOG_MEASURE']])
					? $measureList[$row->arRes['CATALOG_MEASURE']]
					: $measureList[0]
				);
				$row->AddViewField('CATALOG_MEASURE', $measureTitle);
				unset($measureTitle);
			}
		}

		$arActions = array();

		$actionEdit = $lAdmin->getRowAction(CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$row->arRes['orig']['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		));
		$actionCopy = $lAdmin->getRowAction(CIBlock::GetAdminSubElementEditLink(
			$intSubIBlockID,
			$intSubPropValue,
			$row->arRes['orig']['ID'],
			array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID, 'action' => 'copy'),
			$sThisSectionUrl,
			defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1
		));

		if($row->arRes['ACTIVE'] == 'Y')
		{
			$arActive = array(
				"TEXT" => GetMessage("IBSEL_A_DEACTIVATE"),
				"ACTION" => $lAdmin->ActionDoGroup($row->arRes['orig']['ID'], "deactivate", $sThisSectionUrl),
				"ONCLICK" => "",
			);
		}
		else
		{
			$arActive = array(
				"TEXT" => GetMessage("IBSEL_A_ACTIVATE"),
				"ACTION" => $lAdmin->ActionDoGroup($row->arRes['orig']['ID'], "activate", $sThisSectionUrl),
				"ONCLICK" => "",
			);
		}
		$clearCounter = array(
			"TEXT" => GetMessage('IBSEL_A_CLEAR_COUNTER'),
			"TITLE" => GetMessage('IBSEL_A_CLEAR_COUNTER_TITLE'),
			"ACTION" => "if (confirm('".GetMessageJS("IBSEL_A_CLEAR_COUNTER_CONFIRM")."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], "clear_counter", $sThisSectionUrl),
			"ONCLICK" => ""
		);

		if (
			$boolSubWorkFlow
		)
			{
				if (isset($arElementOps[$f_ID])
					&& isset($arElementOps[$f_ID]["element_edit_any_wf_status"])
			)
			{
				$STATUS_PERMISSION = 2;
			}
			else
			{
				$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($row->arRes["WF_STATUS_ID"]);
			}
			$intMinPerm = 2;

			$arUnLock = array(
				"ICON" => "unlock",
				"TEXT" => GetMessage("IBEL_A_UNLOCK"),
				"TITLE" => GetMessage("IBLOCK_UNLOCK_ALT"),
				"ACTION" => "if (confirm('".GetMessageJS("IBLOCK_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], "unlock", $sThisSectionUrl)
			);

			if ($row->arRes['orig']['LOCK_STATUS'] == "red")
			{
				if (CWorkflow::IsAdmin())
					$arActions[] = $arUnLock;
			}
			else
			{
				if (
					isset($arElementOps[$f_ID])
					&& isset($arElementOps[$f_ID]["element_edit"])
					&& (2 <= $STATUS_PERMISSION)
				)
				{
					if ($row->arRes['orig']['LOCK_STATUS'] == "yellow")
					{
						$arActions[] = $arUnLock;
						$arActions[] = array("SEPARATOR"=>true);
					}

					$arActions[] = array(
						"ICON" => "edit",
						"TEXT" => GetMessage("IBEL_A_CHANGE"),
						"DEFAULT" => true,
						"ACTION" => $actionEdit
					);
					$arActions[] = $arActive;
					$arActions[] = array('SEPARATOR' => 'Y');
					$arActions[] = $clearCounter;
					$arActions[] = array('SEPARATOR' => 'Y');
				}

				if ($boolIBlockElementAdd
					&& (2 <= $STATUS_PERMISSION)
				)
				{
					$arActions[] = array(
						"ICON" => "copy",
						"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
						"ACTION" => $actionCopy
					);
				}

				if (
					isset($arElementOps[$f_ID])
					&& isset($arElementOps[$f_ID]["element_delete"])
					&& (2 <= $STATUS_PERMISSION)
				)
				{
					if (!isset($arElementOps[$f_ID]["element_edit_any_wf_status"]))
						$intMinPerm = CIBlockElement::WF_GetStatusPermission($row->arRes["WF_STATUS_ID"], $f_ID);
					if (2 <= $intMinPerm)
					{
						if (!empty($arActions))
							$arActions[] = array("SEPARATOR"=>true);
						$arActions[] = array(
							"ICON" => "delete",
							"TEXT" => GetMessage('MAIN_DELETE'),
							"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
							"ACTION" => "if (confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], "delete", $sThisSectionUrl)
						);

					}
				}
			}
		}
		elseif ($boolSubBizproc)
		{
			$bWritePermission = CIBlockDocument::CanUserOperateDocument(
				CBPCanUserOperateOperation::WriteDocument,
				$USER->GetID(),
				$f_ID,
				array(
					"IBlockId" => $intSubIBlockID,
					"AllUserGroups" => $row->arRes["CURRENT_USER_GROUPS"],
					"DocumentStates" => $arDocumentStates,
				)
			);

			$bStartWorkflowPermission = CIBlockDocument::CanUserOperateDocument(
				CBPCanUserOperateOperation::StartWorkflow,
				$USER->GetID(),
				$f_ID,
				array(
					"IBlockId" => $intSubIBlockID,
					"AllUserGroups" => $row->arRes["CURRENT_USER_GROUPS"],
					"DocumentStates" => $arDocumentStates,
				)
			);

			if ($row->arRes['lockStatus'] == "red")
			{
				if (CBPDocument::IsAdmin())
				{
					$arActions[] = Array(
						"ICON" => "unlock",
						"TEXT" => GetMessage("IBEL_A_UNLOCK"),
						"TITLE" => GetMessage("IBEL_A_UNLOCK_ALT"),
						"ACTION" => "if (confirm('".GetMessageJS("IBEL_A_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_ID, "unlock", $sThisSectionUrl),
					);
				}
			}
			elseif ($bWritePermission)
			{
				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => GetMessage("IBEL_A_CHANGE"),
					"DEFAULT" => true,
					"ACTION" => $actionEdit
				);
				$arActions[] = $arActive;
				$arActions[] = array('SEPARATOR' => 'Y');
				$arActions[] = $clearCounter;
				$arActions[] = array('SEPARATOR' => 'Y');

				$arActions[] = array(
					"ICON" => "copy",
					"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION" => $actionCopy
				);

				$arActions[] = array("SEPARATOR" => true);
				$arActions[] = array(
					"ICON" => "delete",
					"TEXT" => GetMessage('MAIN_DELETE'),
					"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
					"ACTION" => "if (confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", $sThisSectionUrl),
				);
			}
		}
		else
		{
			if (
				isset($arElementOps[$f_ID])
				&& isset($arElementOps[$f_ID]["element_edit"])
			)
			{
				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => GetMessage("IBEL_A_CHANGE"),
					"DEFAULT" => true,
					"ACTION" => $actionEdit
				);
				$arActions[] = $arActive;
				$arActions[] = array('SEPARATOR' => 'Y');
				$arActions[] = $clearCounter;
				$arActions[] = array('SEPARATOR' => 'Y');
			}

			if ($boolIBlockElementAdd && isset($arElementOps[$f_ID])
				&& isset($arElementOps[$f_ID]["element_edit"]))
			{
				$arActions[] = array(
					"ICON" => "copy",
					"TEXT" => GetMessage("IBEL_A_COPY_ELEMENT"),
					"ACTION" => $actionCopy
				);
			}

			if (
				isset($arElementOps[$f_ID])
				&& isset($arElementOps[$f_ID]["element_delete"])
			)
			{
				$arActions[] = array("SEPARATOR"=>true);
				$arActions[] = array(
					"ICON" => "delete",
					"TEXT" => GetMessage('MAIN_DELETE'),
					"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
					"ACTION" => "if (confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($row->arRes['orig']['ID'], "delete", $sThisSectionUrl)
				);
			}
		}

		if (!empty($arActions))
			$row->AddActions($arActions);
	}

	if($bCatalog && !empty($listItemId))
	{
		$subscriptions = Catalog\SubscribeTable::getList(array(
			'select' => array('ITEM_ID', 'CNT'),
			'filter' => array('@ITEM_ID' => $listItemId, 'DATE_TO' => null),
			'runtime' => array(new Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		));
		while($subscribe = $subscriptions->fetch())
		{
			if(isset($arRows[$subscribe['ITEM_ID']]))
			{
				$arRows[$subscribe['ITEM_ID']]->addField('SUBSCRIPTIONS', $subscribe['CNT']);
			}
		}
	}

	$lAdmin->AddFooter(
		array(
			array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
			array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
		)
	);

	$arGroupActions = array();
	foreach ($arElementOps as $id => $arOps)
	{
		if (isset($arOps["element_delete"]))
		{
			$arGroupActions["delete"] = GetMessage("MAIN_ADMIN_LIST_DELETE");
			break;
		}
	}
	foreach ($arElementOps as $id => $arOps)
	{
		if (isset($arOps["element_edit"]))
		{
			$arGroupActions["activate"] = GetMessage("MAIN_ADMIN_LIST_ACTIVATE");
			$arGroupActions["deactivate"] = GetMessage("MAIN_ADMIN_LIST_DEACTIVATE");
			$arGroupActions['clear_counter'] = strtolower(GetMessage('IBSEL_A_CLEAR_COUNTER'));
			break;
		}
	}

	$arParams = array('disable_action_sub_target' => true);
	if ($bWorkFlow)
	{
		$arGroupActions["unlock"] = GetMessage("IBEL_A_UNLOCK_ACTION");
		$arGroupActions["lock"] = GetMessage("IBEL_A_LOCK_ACTION");

		$statuses = '<div id="wf_status_id" style="display:none">'.SelectBox("wf_status_id", CWorkflowStatus::GetDropDownList("N", "desc")).'</div>';
		$arGroupActions["wf_status"] = GetMessage("IBEL_A_WF_STATUS_CHANGE");
		$arGroupActions["wf_status_chooser"] = array("type" => "html", "value" => $statuses);

		$arParams["select_onchange"] .= "BX('wf_status_id').style.display = (this.value == 'wf_status'? 'block':'none');";
	}
	elseif ($bBizproc)
	{
		$arGroupActions["unlock"] = GetMessage("IBEL_A_UNLOCK_ACTION");
	}

	$lAdmin->AddGroupActionTable($arGroupActions, $arParams);

?><script type="text/javascript">
function CheckProductName(id)
{
	if (!id)
		return false;
	var obj = BX(id),
		obFormElement;
	if (!obj)
		return false;
	obj.blur();
	obFormElement = BX.findParent(obj,{tag: 'form'});
	if (!obFormElement)
		return false;
	if ((obFormElement.elements['NAME']) && (0 < obFormElement.elements['NAME'].value.length))
		return obFormElement.elements['NAME'].value;
	else
		return false;

}
function ShowNewOffer(id)
{
	var mxProductName = CheckProductName(id),
		PostParams;
	if (!mxProductName)
		alert('<? echo CUtil::JSEscape(GetMessage('IB_SE_L_ENTER_PRODUCT_NAME')); ?>');
	else
	{
		PostParams = {};
		PostParams.bxpublic = 'Y';
		<? if (!(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))
		{
			?>PostParams.bxsku = 'Y';<?
		}
		?>
		PostParams.PRODUCT_NAME = mxProductName;
		PostParams.sessid = BX.bitrix_sessid();
		(new BX.CAdminDialog({
			'content_url': '<? echo CIBlock::GetAdminSubElementEditLink($intSubIBlockID, $intSubPropValue, 0, array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID), $sThisSectionUrl, defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1); ?>',
			'content_post': PostParams,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
	}
}

function ShowNewOfferExt(id)
{
	var mxProductName = CheckProductName(id),
		PostParams;
	if (!mxProductName)
		alert('<? echo CUtil::JSEscape(GetMessage('IB_SE_L_ENTER_PRODUCT_NAME')); ?>');
	else
	{
		PostParams = {};
		PostParams.bxpublic = 'Y';
		<? if (!(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))
		{
			?>PostParams.bxsku = 'Y';<?
		}
		?>
		PostParams.PRODUCT_NAME = mxProductName;
		PostParams.sessid = BX.bitrix_sessid();
		(new BX.CAdminDialog({
			'content_url': '<? echo CIBlock::GetAdminSubElementEditLink($intSubIBlockID, $intSubPropValue, 0, array('WF' => 'Y', 'TMP_ID' => $strSubTMP_ID, 'SUBPRODUCT_TYPE' => CCatalogAdminTools::TAB_GROUP), $sThisSectionUrl, defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1); ?>',
			'content_post': PostParams,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
	}
}

function ShowSkuGenerator(id)
{
	var mxProductName = CheckProductName(id),
		requriedFields = '',
		PostParams = {};
	<?
	$arIBlock = CIBlock::GetArrayByID($intSubIBlockID);
	if (isset($arIBlock['FIELDS']) && is_array($arIBlock['FIELDS']))
	{
		foreach($arIBlock['FIELDS'] as $fieldName => $arFieldValue)
		{
			switch($fieldName)
			{
				case "IBLOCK_SECTION":
				case "ACTIVE_FROM":
				case "ACTIVE_TO":
				case "SORT":
				case "PREVIEW_TEXT":
				case "DETAIL_TEXT":
				case "CODE":
				case "TAGS":
					if($arFieldValue["IS_REQUIRED"] === 'Y')
					{
					?>
						requriedFields += '- <?=$arFieldValue["NAME"]?>\n';
					<?
					}
					break;
			}
		}
	}
?>
	if(requriedFields != '')
	{
		requriedFields = '<? echo CUtil::JSEscape(GetMessage('IB_SE_L_REQUIRED_FIELDS_FIND')); ?>:\n' + requriedFields;
		alert(requriedFields);
	}
	else if (!mxProductName)
		alert('<? echo CUtil::JSEscape(GetMessage('IB_SE_L_ENTER_PRODUCT_NAME')); ?>');
	else
	{
	PostParams.bxpublic = 'Y';
	PostParams.PRODUCT_NAME = mxProductName;
	<? if (!(defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1))
	{
		?>PostParams.bxsku = 'Y';<?
	}
	?>
	PostParams.sessid = BX.bitrix_sessid();

	(new BX.CAdminDialog({
		'content_url': '/bitrix/tools/catalog/iblock_subelement_generator.php?subIBlockId=<? echo $intSubIBlockID; ?>&subPropValue=<? echo $intSubPropValue; ?>&subTmpId=<? echo $strSubTMP_ID; ?>&iBlockId=<? echo $arSubCatalog['PRODUCT_IBLOCK_ID']; ?>',
		'content_post': PostParams,
		'draggable': true,
		'resizable': true,
		'height': screen.height / 2,
		'width': screen.width - 200,
		'buttons': [
			{
				title: '<? echo CUtil::JSEscape(GetMessage('IB_SE_L_GENERATE')); ?>',
				id: 'savebtn',
				name: 'savebtn',
				className: 'adm-btn-save',
				action: function () {
					this.disableUntilError();
					this.parentWindow.Submit();
				}
			},
			BX.CAdminDialog.btnCancel
		]
	})).Show();
	}
}
</script><?
	//We need javascript not in excel mode
	if (($_REQUEST["mode"]=='list' || $_REQUEST["mode"]=='frame') && $boolSubCatalog && $boolSubCurrency)
	{
		?><script type="text/javascript">
		top.arSubCatalogShowedGroups = [];
		top.arSubExtra = [];
		top.arSubCatalogGroups = [];
		top.SubBaseIndex = "";
		<?
		if (is_array($arCatGroup) && !empty($arCatGroup))
		{
			$i = 0;
			$j = 0;
			foreach ($arCatGroup as &$CatalogGroups)
			{
				if (in_array("CATALOG_GROUP_".$CatalogGroups["ID"], $arSelectedFields))
				{
					echo "top.arSubCatalogShowedGroups[".$i."]=".$CatalogGroups["ID"].";\n";
					$i++;
				}
				if ($CatalogGroups["BASE"] != "Y")
				{
					echo "top.arSubCatalogGroups[".$j."]=".$CatalogGroups["ID"].";\n";
					$j++;
				}
				else
				{
					echo "top.SubBaseIndex=".$CatalogGroups["ID"].";\n";
				}
			}
			unset($CatalogGroups);
		}
		if (is_array($arCatExtra) && !empty($arCatExtra))
		{
			$i = 0;
			foreach ($arCatExtra as &$CatExtra)
			{
				echo "top.arSubExtra[".$CatExtra["ID"]."]=".$CatExtra["PERCENTAGE"].";\n";
				$i++;
			}
			unset($CatExtra);
		}
		?>
		top.SubChangeBasePrice = function(id)
		{
			var i,
				cnt,
				pr,
				price,
				extraId,
				esum,
				eps;
			for (i = 0, cnt = top.arSubCatalogShowedGroups.length; i < cnt; i++)
			{
				pr = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.arSubCatalogShowedGroups[i]+"]");
				if (pr.disabled)
				{
					price = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.SubBaseIndex+"]").value;
					if (price > 0)
					{
						extraId = top.document.getElementById("CATALOG_EXTRA["+id+"]"+"["+top.arSubCatalogShowedGroups[i]+"]").value;
						esum = parseFloat(price) * (1 + top.arSubExtra[extraId] / 100);
						eps = 1.00/Math.pow(10, 6);
						esum = Math.round((esum+eps)*100)/100;
					}
					else
						esum = "";

					pr.value = esum;
				}
			}
		};

		top.SubChangeBaseCurrency = function(id)
		{
			var currency = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.SubBaseIndex+"]"),
				i,
				cnt,
				pr;
			for (i = 0, cnt = top.arSubCatalogShowedGroups.length; i < cnt; i++)
			{
				pr = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.arSubCatalogShowedGroups[i]+"]");
				if (pr.disabled)
				{
					pr.selectedIndex = currency.selectedIndex;
				}
			}
		};
	</script>
	<?
	}

	$aContext = array();
	if ($boolIBlockElementAdd)
	{
		$aContext[] = array(
			"ICON" => "btn_sub_new",
			"TEXT" => htmlspecialcharsEx('' != trim($arSubIBlock["ELEMENT_ADD"]) ? $arSubIBlock["ELEMENT_ADD"] : GetMessage('IB_SE_L_ADD_NEW_ELEMENT')),
			"LINK" => "javascript:ShowNewOffer('btn_sub_new')",
			"TITLE" => GetMessage("IB_SE_L_ADD_NEW_ELEMENT_DESCR")
		);
		if (CBXFeatures::IsFeatureEnabled('CatCompleteSet'))
		{
			$aContext[] = array(
				"ICON" => "btn",
				"TEXT" => GetMessage('IB_SE_L_ADD_NEW_ELEMENT_WITH_GROUP'),
				"LINK" => "javascript:ShowNewOfferExt('btn_sub_new')",
				"TITLE" => GetMessage("IB_SE_L_ADD_NEW_ELEMENT_WITH_GROUP_DESCR")
			);
		}
	}
	$aContext[] = array(
		"ICON"=>"btn_sub_refresh",
		"TEXT"=>htmlspecialcharsEx(GetMessage('IB_SE_L_REFRESH_ELEMENTS')),
		"LINK" => "javascript:".$lAdmin->ActionAjaxReload($lAdmin->GetListUrl(true)),
		"TITLE"=>GetMessage("IB_SE_L_REFRESH_ELEMENTS_DESCR"),
	);
	if ($boolIBlockElementAdd)
	{
		$aContext[] = array(
			"ICON"=>"btn_sub_gen",
			"TEXT"=>htmlspecialcharsEx(GetMessage('IB_SE_L_GENERATE_ELEMENTS')),
			"LINK" => "javascript:ShowSkuGenerator('btn_sub_gen');",
			"TITLE"=>GetMessage("IB_SE_L_GENERATE_ELEMENTS")
		);
	}
	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->CheckListMode();

	$lAdmin->DisplayList(B_ADMIN_SUBELEMENTS_LIST);
	if ($boolSubWorkFlow || $boolSubBizproc)
	{
		echo BeginNote();?>
		<span class="adm-lamp adm-lamp-green"></span> - <?echo GetMessage("IBLOCK_GREEN_ALT")?><br>
		<span class="adm-lamp adm-lamp-yellow"></span> - <?echo GetMessage("IBLOCK_YELLOW_ALT")?><br>
		<span class="adm-lamp adm-lamp-red"></span> - <?echo GetMessage("IBLOCK_RED_ALT")?><br>
		<?echo EndNote();
	}
}
else
{
	ShowMessage(GetMessage('IB_SE_L_SHOW_PRICES_AFTER_COPY'));
}