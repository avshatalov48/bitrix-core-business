<?
/** @global CMain $APPLICATION */
/** @global CDatabase $DB */
/** @global CUser $USER */

use Bitrix\Main\Loader,
	Bitrix\Main,
	Bitrix\Iblock,
	Bitrix\Currency,
	Bitrix\Catalog;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/admin_lib.php");

$bBizproc = Loader::includeModule("bizproc");
$bWorkflow = Loader::includeModule("workflow");
$bFileman = Loader::includeModule("fileman");
$bExcel = isset($_REQUEST["mode"]) && ($_REQUEST["mode"] == "excel");
$dsc_cookie_name = COption::GetOptionString('main', 'cookie_name', 'BITRIX_SM')."_DSC";

$bSearch = false;
$bCurrency = false;
$arCurrencyList = array();
$elementsList = array();

$listImageSize = Main\Config\Option::get('iblock', 'list_image_size');
$minImageSize = array("W" => 1, "H"=>1);
$maxImageSize = array(
	"W" => $listImageSize,
	"H" => $listImageSize,
);
unset($listImageSize);
$useCalendarTime = (string)Main\Config\Option::get('iblock', 'list_full_date_edit') == 'Y';

if (isset($_REQUEST['mode']) && ($_REQUEST['mode']=='list' || $_REQUEST['mode']=='frame'))
	CFile::DisableJSFunction(true);

$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
if($arIBTYPE===false)
	$APPLICATION->AuthForm(GetMessage("IBLIST_A_BAD_BLOCK_TYPE_ID"));

$IBLOCK_ID = 0;
if (isset($_REQUEST['IBLOCK_ID']))
	$IBLOCK_ID = (int)$_REQUEST["IBLOCK_ID"];
$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);

if($arIBlock)
	$bBadBlock = !CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_admin_display");
else
	$bBadBlock = true;

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBLIST_A_BAD_IBLOCK"));?>
	<a href="<?echo htmlspecialcharsbx("iblock_admin.php?lang=".LANGUAGE_ID."&type=".urlencode($type))?>"><?echo GetMessage("IBLOCK_BACK_TO_ADMIN")?></a>
	<?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

if(!$arIBlock["SECTIONS_NAME"])
	$arIBlock["SECTIONS_NAME"] = $arIBTYPE["SECTION_NAME"]? $arIBTYPE["SECTION_NAME"]: GetMessage("IBLIST_A_SECTIONS");
if(!$arIBlock["ELEMENTS_NAME"])
	$arIBlock["ELEMENTS_NAME"] = $arIBTYPE["ELEMENT_NAME"]? $arIBTYPE["ELEMENT_NAME"]: GetMessage("IBLIST_A_ELEMENTS");

$arIBlock["SITE_ID"] = array();
$rsSites = CIBlock::GetSite($IBLOCK_ID);
while($arSite = $rsSites->Fetch())
	$arIBlock["SITE_ID"][] = $arSite["LID"];

$bWorkFlow = $bWorkflow && (CIBlock::GetArrayByID($IBLOCK_ID, "WORKFLOW") != "N");
$bBizproc = $bBizproc && (CIBlock::GetArrayByID($IBLOCK_ID, "BIZPROC") != "N");

define("MODULE_ID", "iblock");
define("ENTITY", "CIBlockDocument");
define("DOCUMENT_TYPE", "iblock_".$IBLOCK_ID);

$bCatalog = Loader::includeModule("catalog");
$arCatalog = false;
$boolSKU = false;
$boolSKUFiltrable = false;
$strSKUName = '';
$uniq_id = 0;
$strUseStoreControl = '';
$strSaveWithoutPrice = '';
$boolCatalogRead = false;
$boolCatalogPrice = false;
$boolCatalogPurchasInfo = false;
$catalogPurchasInfoEdit = false;
$boolCatalogSet = false;
$showCatalogWithOffers = false;
$productTypeList = array();
if ($bCatalog)
{
	$strUseStoreControl = COption::GetOptionString('catalog', 'default_use_store_control');
	$strSaveWithoutPrice = COption::GetOptionString('catalog','save_product_without_price');
	$boolCatalogRead = $USER->CanDoOperation('catalog_read');
	$boolCatalogPrice = $USER->CanDoOperation('catalog_price');
	$boolCatalogPurchasInfo = $USER->CanDoOperation('catalog_purchas_info');
	$boolCatalogSet = CBXFeatures::IsFeatureEnabled('CatCompleteSet');
	$arCatalog = CCatalogSKU::GetInfoByIBlock($arIBlock["ID"]);
	if (empty($arCatalog))
	{
		$bCatalog = false;
	}
	else
	{
		if (CCatalogSKU::TYPE_PRODUCT == $arCatalog['CATALOG_TYPE'] || CCatalogSKU::TYPE_FULL == $arCatalog['CATALOG_TYPE'])
		{
			if (CIBlockRights::UserHasRightTo($arCatalog['IBLOCK_ID'], $arCatalog['IBLOCK_ID'], "iblock_admin_display"))
			{
				$boolSKU = true;
				$strSKUName = GetMessage('IBLIST_A_OFFERS');
			}
		}
		if(!$boolCatalogRead && !$boolCatalogPrice)
			$bCatalog = false;
	}
	$productTypeList = CCatalogAdminTools::getIblockProductTypeList($arIBlock['ID'], true);
	$showCatalogWithOffers = (COption::GetOptionString('catalog', 'show_catalog_tab_with_offers') == 'Y');
	if ($boolCatalogPurchasInfo)
		$catalogPurchasInfoEdit = $boolCatalogPrice && $strUseStoreControl != 'Y';
}

$dbrFProps = CIBlockProperty::GetList(
	array(
		"SORT" => "ASC",
		"NAME" => "ASC"
	),
	array(
		"IBLOCK_ID" => $IBLOCK_ID,
		"CHECK_PERMISSIONS" => "N",
	)
);

$arFileProps = array();
$arProps = array();
while ($arProp = $dbrFProps->GetNext())
{
	if ($arProp["ACTIVE"] == "Y")
	{
		$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
		$arProps[] = $arProp;
	}

	if ($arProp["PROPERTY_TYPE"] == "F")
	{
		$arFileProps[$arProp["ID"]] = $arProp;
	}
}

if ($boolSKU)
{
	$dbrFProps = CIBlockProperty::GetList(
		array(
			"SORT"=>"ASC",
			"NAME"=>"ASC"
		),
		array(
			"IBLOCK_ID"=>$arCatalog['IBLOCK_ID'],
			"ACTIVE"=>"Y",
			"CHECK_PERMISSIONS"=>"N",
		)
	);

	$arSKUProps = array();
	while($arProp = $dbrFProps->GetNext())
	{
		if ('Y' == $arProp['FILTRABLE'] && 'F' != $arProp['PROPERTY_TYPE'] && $arCatalog['SKU_PROPERTY_ID'] != $arProp['ID'])
		{
			$arProp["PROPERTY_USER_TYPE"] = ('' != $arProp["USER_TYPE"] ? CIBlockProperty::GetUserType($arProp["USER_TYPE"]) : array());
			$boolSKUFiltrable = true;
			$arSKUProps[] = $arProp;
		}
	}
}

$sTableID = (defined("CATALOG_PRODUCT")? "tbl_product_list_": "tbl_iblock_list_").md5($type.".".$IBLOCK_ID);
$oSort = new CAdminSorting($sTableID, "timestamp_x", "desc");
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
//$arOrder = (strtoupper($by) === "ID"? array($by => $order): array($by => $order, "ID" => "ASC"));
$lAdmin = new CAdminList($sTableID, $oSort);
$lAdmin->bMultipart = true;
$arFilterFields = Array(
	"find_name",
	"find_section_section",
	"find_id_1",		"find_id_2",
	"find_timestamp_1",	"find_timestamp_2",
	"find_code",
	"find_external_id",
	"find_modified_by",	"find_modified_user_id",
	"find_created_from",	"find_created_to",
	"find_created_by",	"find_created_user_id",
	"find_date_active_from_from",	"find_date_active_from_to",
	"find_date_active_to_from",	"find_date_active_to_to",
	"find_active",
	"find_intext",
	"find_status",		"find_status_id",
	"find_tags",
);
foreach ($arProps as $arProp)
{
	if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F")
		$arFilterFields[] = "find_el_property_".$arProp["ID"];
}

if ($boolSKU && $boolSKUFiltrable)
{
	foreach ($arSKUProps as $arProp)
	{
		$arFilterFields[] = "find_sub_el_property_".$arProp["ID"];
	}
}

if ($bCatalog)
{
	$arFilterFields[] = "find_el_catalog_type";
	$arFilterFields[] = "find_el_catalog_available";
	if ($boolCatalogSet)
		$arFilterFields[] = "find_el_catalog_bundle";
}

if(isset($_REQUEST["del_filter"]) && $_REQUEST["del_filter"] != "")
	$find_section_section = -1;
elseif(isset($_REQUEST["find_section_section"]))
	$find_section_section = $_REQUEST["find_section_section"];
else
	$find_section_section = -1;
//We have to handle current section in a special way
$section_id = intval($find_section_section);
$lAdmin->InitFilter($arFilterFields);
$find_section_section = $section_id;
//This is all parameters needed for proper navigation
$sThisSectionUrl = '&type='.urlencode($type).'&lang='.LANGUAGE_ID.'&IBLOCK_ID='.$IBLOCK_ID.'&find_section_section='.intval($find_section_section);

$arFilter = Array(
	"IBLOCK_ID"		=>$IBLOCK_ID,
	"NAME"			=>$find_name,
	"SECTION_ID"		=>$find_section_section,
	"ID_1"			=>$find_id_1,
	"ID_2"			=>$find_id_2,
	"TIMESTAMP_X_1"		=>$find_timestamp_1,
	"CODE"			=>$find_code,
	"EXTERNAL_ID"		=>$find_external_id,
	"MODIFIED_BY"		=>$find_modified_by,
	"MODIFIED_USER_ID"	=>$find_modified_user_id,
	"DATE_CREATE_1"		=>$find_created_from,
	"CREATED_BY"		=>$find_created_by,
	"CREATED_USER_ID"	=>$find_created_user_id,
	"DATE_ACTIVE_FROM_1"	=>$find_date_active_from_from,
	"DATE_ACTIVE_FROM_2"	=>$find_date_active_from_to,
	"DATE_ACTIVE_TO_1"	=>$find_date_active_to_from,
	"DATE_ACTIVE_TO_2"	=>$find_date_active_to_to,
	"ACTIVE"		=>$find_active,
	"DESCRIPTION"		=>$find_intext,
	"WF_STATUS"		=>$find_status==""?$find_status_id:$find_status,
	"?TAGS"			=>$find_tags,
	"CHECK_PERMISSIONS" => "Y",
	"MIN_PERMISSION" => "R",
);
if(!empty($find_timestamp_2))
	$arFilter["TIMESTAMP_X_2"] = CIBlock::isShortDate($find_timestamp_2)? ConvertTimeStamp(AddTime(MakeTimeStamp($find_timestamp_2), 1, "D"), "FULL"): $find_timestamp_2;
if(!empty($find_created_to))
	$arFilter["DATE_CREATE_2"] = CIBlock::isShortDate($find_created_to)? ConvertTimeStamp(AddTime(MakeTimeStamp($find_created_to), 1, "D"), "FULL"): $find_created_to;

if ($bBizproc && 'E' != $arIBlock['RIGHTS_MODE'])
{
	$strPerm = CIBlock::GetPermission($IBLOCK_ID);
	if ('W' > $strPerm)
	{
		unset($arFilter['CHECK_PERMISSIONS']);
		unset($arFilter['MIN_PERMISSION']);
		$arFilter['CHECK_BP_PERMISSIONS'] = 'read';
	}
}

foreach($arProps as $arProp)
{
	if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F")
	{
		$value = ${"find_el_property_".$arProp["ID"]};

		if(array_key_exists("AddFilterFields", $arProp["PROPERTY_USER_TYPE"]))
		{
			call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
				$arProp,
				array("VALUE" => "find_el_property_".$arProp["ID"]),
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

$arSubQuery = array();
if ($boolSKU && $boolSKUFiltrable)
{
	$arSubQuery = array("IBLOCK_ID" => $arCatalog['IBLOCK_ID']);
	foreach ($arSKUProps as $arProp)
	{
		if (!empty($arProp["PROPERTY_USER_TYPE"]) && isset($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"]))
		{
			call_user_func_array($arProp["PROPERTY_USER_TYPE"]["AddFilterFields"], array(
				$arProp,
				array("VALUE" => "find_sub_el_property_".$arProp["ID"]),
				&$arSubQuery,
				&$filtered,
			));
		}
		else
		{
			$value = ${"find_sub_el_property_".$arProp["ID"]};
			if(is_array($value) || strlen($value))
			{
				if($value === "NOT_REF")
					$value = false;
				$arSubQuery["?PROPERTY_".$arProp["ID"]] = $value;
			}
		}
	}
}

if (!empty($find_el_catalog_type))
	$arFilter['CATALOG_TYPE'] = $find_el_catalog_type;
if (!empty($find_el_catalog_available))
	$arFilter['CATALOG_AVAILABLE'] = $find_el_catalog_available;
if (!empty($find_el_catalog_bundle) && $boolCatalogSet)
	$arFilter['CATALOG_BUNDLE'] = $find_el_catalog_bundle;

if ($boolSKU && 1 < sizeof($arSubQuery))
{
	$arFilter['ID'] = CIBlockElement::SubQuery('PROPERTY_'.$arCatalog['SKU_PROPERTY_ID'], $arSubQuery);
}

if(intval($find_section_section)<0 || strlen($find_section_section)<=0)
	unset($arFilter["SECTION_ID"]);

// Handle edit action (check for permission before save!)
if($lAdmin->EditAction())
{
	if(is_array($_FILES['FIELDS']))
		CAllFile::ConvertFilesToPost($_FILES['FIELDS'], $_POST['FIELDS']);

	foreach($_POST['FIELDS'] as $ID=>$arFields)
	{
		if(!$lAdmin->IsUpdated($ID))
			continue;
		$TYPE = substr($ID, 0, 1);
		$ID = (int)substr($ID,1);
		$arFields["IBLOCK_ID"] = $IBLOCK_ID;

		if($TYPE=="S")
		{
			if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit"))
			{
				$obS = new CIBlockSection;

				if(array_key_exists("PREVIEW_PICTURE", $arFields))
				{
					$arFields["PICTURE"] = CIBlock::makeFileArray(
						$arFields["PREVIEW_PICTURE"],
						$_REQUEST["FIELDS_del"][$TYPE.$ID]["PREVIEW_PICTURE"] === "Y"
					);
				}
				elseif (array_key_exists("PICTURE", $arFields))
				{
					$arFields["PICTURE"] = CIBlock::makeFileArray(
						$arFields["PICTURE"],
						$_REQUEST["FIELDS_del"][$TYPE.$ID]["PICTURE"] === "Y"
					);
				}

				if (array_key_exists("DETAIL_PICTURE", $arFields))
				{
					$arFields["DETAIL_PICTURE"] = CIBlock::makeFileArray(
						$arFields["DETAIL_PICTURE"],
						$_REQUEST["FIELDS_del"][$TYPE.$ID]["DETAIL_PICTURE"] === "Y",
						$_REQUEST["FIELDS_descr"][$TYPE.$ID]["DETAIL_PICTURE"]
					);
				}

				$DB->StartTransaction();
				if(!$obS->Update($ID, $arFields, true, true, true))
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => '<br>'.$obS->LAST_ERROR)), $TYPE.$ID);
					$DB->Rollback();
				}
				else
				{
					$ipropValues = new \Bitrix\Iblock\InheritedProperty\sectionValues($IBLOCK_ID, $ID);
					$ipropValues->clearValues();
					$DB->Commit();
				}
			}
		}

		if($TYPE=="E")
		{
			$arRes = CIBlockElement::GetByID($ID);
			$arRes = $arRes->Fetch();
			if(!$arRes)
				continue;

			$WF_ID = $ID;
			if($bWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($ID);
				if($WF_ID!=$ID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $ID;
				}

				if($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_LOCKED", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
			}
			elseif ($bBizproc)
			{
				if (call_user_func(array(ENTITY, "IsDocumentLocked"), $ID, ""))
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_LOCKED", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
			}

			if(
				$bWorkFlow
			)
			{
				if (!CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
				{
					$lAdmin->AddUpdateError(GetMessage("IBEL_A_UPDERR3")." (ID:".$ID.")", $ID);
					continue;
				}

				// handle workflow status access permissions
				if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_any_wf_status"))
					$STATUS_PERMISSION = true;
				elseif ($arFields["WF_STATUS_ID"] > 0)
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arFields["WF_STATUS_ID"]) >= 1;
				else
					$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]) >= 2;

				if (!$STATUS_PERMISSION)
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
			}
			elseif ($bBizproc)
			{
				$bCanWrite = call_user_func(array(ENTITY, "CanUserOperateDocument"),
					CBPCanUserOperateOperation::WriteDocument,
					$USER->GetID(),
					$ID,
					array(
						"IBlockId" => $IBLOCK_ID,
						'IBlockRightsMode' => $arIBlock['RIGHTS_MODE'],
						'UserGroups' => $USER->GetUserGroupArray(),
					)
				);

				if(!$bCanWrite)
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
			}
			elseif(!CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
			{
				$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
				continue;
			}

			if (array_key_exists("PREVIEW_PICTURE", $arFields))
			{
				$arFields["PREVIEW_PICTURE"] = CIBlock::makeFileArray(
					$arFields["PREVIEW_PICTURE"],
					$_REQUEST["FIELDS_del"][$TYPE.$ID]["PREVIEW_PICTURE"] === "Y",
					$_REQUEST["FIELDS_descr"][$TYPE.$ID]["PREVIEW_PICTURE"]
				);
			}

			if (array_key_exists("DETAIL_PICTURE", $arFields))
			{
				$arFields["DETAIL_PICTURE"] = CIBlock::makeFileArray(
					$arFields["DETAIL_PICTURE"],
					$_REQUEST["FIELDS_del"][$TYPE.$ID]["DETAIL_PICTURE"] === "Y",
					$_REQUEST["FIELDS_descr"][$TYPE.$ID]["DETAIL_PICTURE"]
				);
			}

			if(!is_array($arFields["PROPERTY_VALUES"]))
				$arFields["PROPERTY_VALUES"] = array();
			$bFieldProps = array();
			foreach($arFields as $k=>$v)
			{
				if(
					$k != "PROPERTY_VALUES"
					&& strncmp($k, "PROPERTY_", 9) == 0
				)
				{
					$prop_id = substr($k, 9);

					if (isset($arFileProps[$prop_id]))
					{
						foreach ($v as $prop_value_id => $file)
						{
							$v[$prop_value_id] = CIBlock::makeFilePropArray(
								$v[$prop_value_id],
								$_REQUEST["FIELDS_del"][$TYPE.$ID][$k][$prop_value_id]["VALUE"] === "Y",
								$_REQUEST["FIELDS_descr"][$TYPE.$ID][$k][$prop_value_id]["VALUE"]
							);
						}
					}

					if(isset($_REQUEST["FIELDS_descr"][$TYPE.$ID][$k]) && is_array($_REQUEST["FIELDS_descr"][$TYPE.$ID][$k]))
					{
						foreach($_REQUEST["FIELDS_descr"][$TYPE.$ID][$k] as $PROPERTY_VALUE_ID => $ar)
						{
							if(
								is_array($ar)
								&& isset($ar["VALUE"])
								&& isset($v[$PROPERTY_VALUE_ID]["VALUE"])
								&& is_array($v[$PROPERTY_VALUE_ID]["VALUE"])
							)
								$v[$PROPERTY_VALUE_ID]["DESCRIPTION"] = $ar["VALUE"];
						}
					}

					$arFields["PROPERTY_VALUES"][$prop_id] = $v;
					unset($arFields[$k]);
					$bFieldProps[$prop_id] = true;
				}
			}
			if(count($bFieldProps) > 0)
			{
				//We have to read properties from database in order not to delete its values
				if(!$bWorkFlow)
				{
					$dbPropV = CIBlockElement::GetProperty($IBLOCK_ID, $ID, "sort", "asc", Array("ACTIVE"=>"Y"));
					while($arPropV = $dbPropV->Fetch())
					{
						if(!array_key_exists($arPropV["ID"], $bFieldProps) && $arPropV["PROPERTY_TYPE"] != "F")
						{
							if(!array_key_exists($arPropV["ID"], $arFields["PROPERTY_VALUES"]))
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
			foreach($arIBlock["FIELDS"] as $FIELD_ID => $field)
			{
				if(
					$field["IS_REQUIRED"] === "Y"
					&& !array_key_exists($FIELD_ID, $arFields)
					&& $FIELD_ID !== "DETAIL_PICTURE"
					&& $FIELD_ID !== "PREVIEW_PICTURE"
				)
					$arFields[$FIELD_ID] = $arRes[$FIELD_ID];
			}
			if($arRes["IN_SECTIONS"] == "Y")
			{
				$arFields["IBLOCK_SECTION"] = array();
				$rsSections = CIBlockElement::GetElementGroups($arRes["ID"], true, array('ID', 'IBLOCK_ELEMENT_ID'));
				while($arSection = $rsSections->Fetch())
					$arFields["IBLOCK_SECTION"][] = $arSection["ID"];
			}

			$arFields["MODIFIED_BY"]=$USER->GetID();
			$ib = new CIBlockElement;
			$DB->StartTransaction();
			if(!$ib->Update($ID, $arFields, true, true, true))
			{
				$lAdmin->AddUpdateError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $ib->LAST_ERROR)), $TYPE.$ID);
				$DB->Rollback();
			}
			else
			{
				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $ID);
				$ipropValues->clearValues();
				$DB->Commit();
			}

			if($bCatalog)
			{
				if ($boolCatalogPrice && CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_price"))
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
						'filter' => array('=ID' => $ID)
					))->fetch();
					if (empty($product))
					{
						$arCatalogProduct['ID'] = $ID;
						CCatalogProduct::Add($arCatalogProduct, false);
					}
					else
					{
						if (!empty($arCatalogProduct))
						{
							if ($strUseStoreControl != 'Y')
								$arCatalogProduct['SUBSCRIBE'] = $product['SUBSCRIBE_ORIG'];
							CCatalogProduct::Update($ID, $arCatalogProduct);
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
								'filter' => array('=PRODUCT_ID' => $ID, '=IS_DEFAULT' => 'Y'),
							))->fetch();
							if (!empty($ratio))
								$intRatioID = (int)$ratio['ID'];
							if ($intRatioID > 0)
								$ratioResult = CCatalogMeasureRatio::update($intRatioID, array('RATIO' => $newValue));
							else
								$ratioResult = CCatalogMeasureRatio::add(array('PRODUCT_ID' => $ID, 'RATIO' => $newValue, 'IS_DEFAULT' => 'Y'));
						}
						unset($newValue);
					}
				}
			}
		}
	}

	if($bCatalog)
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
			$db_extras = CExtra::GetList(array("ID" => "ASC"));

			$arCatExtraUp = array();
			while ($extras = $db_extras->Fetch())
				$arCatExtraUp[$extras["ID"]] = $extras["PERCENTAGE"];

			$arBaseGroup = CCatalogGroup::GetBaseGroup();
			$arCatalogGroupList = CCatalogGroup::GetListArray();
			foreach($CATALOG_PRICE as $elID => $arPrice)
			{
				if (!(
					CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $elID, "element_edit")
					&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $elID, "element_edit_price"))
				)
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
							$lAdmin->AddUpdateError(GetMessage('IBLIST_A_NO_BASE_PRICE', array("#ID#" => $elID)), $elID);
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
							$lAdmin->AddUpdateError(GetMessage('IBLIST_A_NO_BASE_PRICE', array("#ID#" => $elID)), $elID);
						}
					}
				}

				if($bError)
					continue;

				$arCurrency = $CATALOG_CURRENCY[$elID];

				if (!empty($arCatalogGroupList))
				{
					foreach ($arCatalogGroupList as $arCatalogGroup)
					{
						if ($arPrice[$arCatalogGroup["ID"]] != $CATALOG_PRICE_old[$elID][$arCatalogGroup["ID"]]
							|| $arCurrency[$arCatalogGroup["ID"]] != $CATALOG_CURRENCY_old[$elID][$arCatalogGroup["ID"]])
						{
							if ($arCatalogGroup["BASE"] == 'Y') // if base price check extra for other prices
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
								if($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
									CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
								elseif((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
									CPrice::Update($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
								elseif($arFields["PRICE"] >= 0)
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
							elseif(!isset($CATALOG_EXTRA[$elID][$arCatalogGroup["ID"]]))
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
								if($arFields["PRICE"] < 0 || trim($arFields["PRICE"]) === '')
									CPrice::Delete($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]]);
								elseif((int)$CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]] > 0)
									CPrice::Update($CATALOG_PRICE_ID[$elID][$arCatalogGroup["ID"]], $arFields);
								elseif($arFields["PRICE"] >= 0)
									CPrice::Add($arFields);
							}
						}
					}
					unset($arCatalogGroup);
				}

				$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $elID);
				$ipropValues->clearValues();
				\Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($IBLOCK_ID, $elID);
			}
			unset($arCatalogGroupList);
		}
	}
}

// Handle actions here
if(($arID = $lAdmin->GroupAction()))
{
	if($_REQUEST['action_target']=='selected')
	{
		$rsData = CIBlockSection::GetMixedList($arOrder, $arFilter);
		while($arRes = $rsData->Fetch())
		{
			$arID[] = $arRes['TYPE'].$arRes['ID'];
		}
	}

	foreach($arID as $ID)
	{

		if(strlen($ID)<=1)
			continue;
		$TYPE = substr($ID, 0, 1);
		$ID = intval(substr($ID,1));

		if($TYPE == "E")
		{
			$arRes = CIBlockElement::GetByID($ID);
			$arRes = $arRes->Fetch();
			if(!$arRes)
				continue;
			$WF_ID = $ID;
			if($bWorkFlow)
			{
				$WF_ID = CIBlockElement::WF_GetLast($ID);
				if($WF_ID!=$ID)
				{
					$rsData2 = CIBlockElement::GetByID($WF_ID);
					if($arRes = $rsData2->Fetch())
						$WF_ID = $arRes["ID"];
					else
						$WF_ID = $ID;
				}

				if($arRes["LOCK_STATUS"]=='red' && !($_REQUEST['action']=='unlock' && CWorkflow::IsAdmin()))
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_LOCKED", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
			}
			elseif ($bBizproc)
			{
				if (call_user_func(array(ENTITY, "IsDocumentLocked"), $ID, "") && !($_REQUEST['action']=='unlock' && CBPDocument::IsAdmin()))
				{
					$lAdmin->AddUpdateError(GetMessage("IBLIST_A_UPDERR_LOCKED", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
			}
			$bPermissions = false;
			//delete and modify can:
			if($bWorkFlow)
			{
				//For delete action we have to check all statuses in element history
				$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"], $_REQUEST['action']=="delete"? $ID: false);
				if($STATUS_PERMISSION >= 2)
					$bPermissions = true;
			}
			elseif ($bBizproc)
			{
				$bCanWrite = CIBlockDocument::CanUserOperateDocument(
					CBPCanUserOperateOperation::WriteDocument,
					$USER->GetID(),
					$ID,
					array(
						"IBlockId" => $IBLOCK_ID,
						'IBlockRightsMode' => $arIBlock['RIGHTS_MODE'],
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
			if(!$bPermissions)
			{
				$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)));
				continue;
			}
		}

		switch($_REQUEST['action'])
		{
		case "delete":
			@set_time_limit(0);
			if($TYPE=="S")
			{
				if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_delete"))
				{
					$DB->StartTransaction();
					$APPLICATION->ResetException();
					if(!CIBlockSection::Delete($ID))
					{
						$DB->Rollback();
						if($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError(GetMessage("IBLIST_A_SECTION_DELETE_ERROR", array("#ID#" => $ID))." [".$ex->GetString()."]", $TYPE.$ID);
						else
							$lAdmin->AddGroupError(GetMessage("IBLIST_A_SECTION_DELETE_ERROR", array("#ID#" => $ID)), $TYPE.$ID);
					}
					else
					{
						$DB->Commit();
					}
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_SECTION_DELETE_ERROR", array("#ID#" => $ID)), $TYPE.$ID);
				}
			}
			elseif($TYPE=="E")
			{
				if(CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_delete"))
				{
					$DB->StartTransaction();
					$APPLICATION->ResetException();
					if(!CIBlockElement::Delete($ID))
					{
						$DB->Rollback();
						if($ex = $APPLICATION->GetException())
							$lAdmin->AddGroupError(GetMessage("IBLIST_A_ELEMENT_DELETE_ERROR", array("#ID#" => $ID))." [".$ex->GetString()."]", $TYPE.$ID);
						else
							$lAdmin->AddGroupError(GetMessage("IBLIST_A_ELEMENT_DELETE_ERROR", array("#ID#" => $ID)), $TYPE.$ID);
					}
					else
					{
						$DB->Commit();
					}
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_ELEMENT_DELETE_ERROR", array("#ID#" => $ID)), $TYPE.$ID);
				}
			}
			break;
		case "activate":
		case "deactivate":
			$arFields = Array("ACTIVE"=>($_REQUEST['action']=="activate"?"Y":"N"));
			if($TYPE=="S")
			{
				if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit"))
				{
					$obS = new CIBlockSection();
					if(!$obS->Update($ID, $arFields))
					{
						$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obS->LAST_ERROR)), $TYPE.$ID);
					}
					else
					{
						$ipropValues = new \Bitrix\Iblock\InheritedProperty\sectionValues($IBLOCK_ID, $ID);
						$ipropValues->clearValues();
					}
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
				}
			}
			elseif($TYPE=="E")
			{
				if(CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
				{
					$obE = new CIBlockElement();
					if(!$obE->Update($ID, $arFields, true))
					{
						$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obE->LAST_ERROR)), $TYPE.$ID);
					}
					else
					{
						$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $ID);
						$ipropValues->clearValues();
					}
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
				}
			}
			break;
		case "section":
		case "add_section":
			$new_section = intval($_REQUEST["section_to_move"]);
			if($new_section >= 0)
			{
				if ($TYPE=="S")
				{
					if (CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $new_section, "section_section_bind"))
					{
						$obS = new CIBlockSection();
						if(!$obS->Update($ID, array("IBLOCK_SECTION_ID" => $new_section)))
						{
							$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obS->LAST_ERROR)), $TYPE.$ID);
						}
						else
						{
							$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($IBLOCK_ID, $ID);
							$ipropValues->clearValues();
						}
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
					}
				}
				elseif($TYPE=="E")
				{
					if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit") && CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $new_section, "section_element_bind"))
					{
						$obE = new CIBlockElement();

						$arSections = array($new_section);
						if($_REQUEST['action'] == "add_section")
						{
							$rsSections = $obE->GetElementGroups($ID, true, array('ID', 'IBLOCK_ELEMENT_ID'));
							while($ar = $rsSections->Fetch())
								$arSections[] = $ar["ID"];
						}

						$arFields = array(
							"IBLOCK_SECTION" => $arSections,
						);
						if ($_REQUEST["action"] == "section")
						{
							$arFields["IBLOCK_SECTION_ID"] = $new_section;
						}

						if(!$obE->Update($ID, $arFields))
						{
							$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obE->LAST_ERROR)), $TYPE.$ID);
						}
						else
						{
							$ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($IBLOCK_ID, $ID);
							$ipropValues->clearValues();
						}
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
					}
				}
			}
			break;
		case "wf_status":
			if($TYPE=="E" && $bWorkFlow)
			{
				$new_status = intval($_REQUEST["wf_status_id"]);
				if(
					$new_status > 0
				)
				{
					if (
						CIBlockElement::WF_GetStatusPermission($new_status) > 0
						|| CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit_any_wf_status")
					)
					{
						if($arRes["WF_STATUS_ID"] != $new_status)
						{
							$obE = new CIBlockElement();
							$res = $obE->Update($ID, array(
								"WF_STATUS_ID" => $new_status,
								"MODIFIED_BY" => $USER->GetID(),
							), true);
							if(!$res)
								$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obE->LAST_ERROR)), $TYPE.$ID);
						}
					}
					else
					{
						$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => GetMessage("IBLIST_A_ACCESS_DENIED_STATUS")." [".$new_status."].<br>")), $TYPE.$ID);
					}
				}
			}
			break;
		case "lock":
			if ($TYPE=="E")
			{
				if ($bWorkflow && !CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
				else
				{
					CIBlockElement::WF_Lock($ID);
				}
			}
			break;
		case "unlock":
			if ($TYPE=="E")
			{
				if ($bWorkflow && !CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
					continue;
				}
				if ($bBizproc)
					call_user_func(array(ENTITY, "UnlockDocument"), $ID, "");
				else
					CIBlockElement::WF_UnLock($ID);
			}
			break;
		case 'clear_counter':
			if ($TYPE=="E")
			{
				if(CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
				{
					$obE = new CIBlockElement();
					$arFields = array('SHOW_COUNTER' => false, 'SHOW_COUNTER_START' => false);
					if(!$obE->Update($ID, $arFields, false, false))
						$lAdmin->AddGroupError(GetMessage("IBLIST_A_SAVE_ERROR", array("#ID#" => $ID, "#ERROR_MESSAGE#" => $obE->LAST_ERROR)), $TYPE.$ID);
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
				}
			}
			break;
		case 'change_price':
			if ($TYPE=="S")
			{
				$elementsList['SECTIONS'][] = $ID;
			}

			if ($TYPE=="E")
			{
				if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $ID, "element_edit"))
				{
					$elementsList['ELEMENTS'][] = $ID;
				}
				else
				{
					$lAdmin->AddGroupError(GetMessage("IBLIST_A_UPDERR_ACCESS", array("#ID#" => $ID)), $TYPE.$ID);
				}
			}
			break;
		}
	}

	if (($_REQUEST['action']) === 'change_price' && !empty($_REQUEST['chprice_value_changing_price']))
	{
		$changePriceParams['PRICE_TYPE'] = $_REQUEST['chprice_id_price_type'];
		$changePriceParams['UNITS'] = $_REQUEST['chprice_units'];
		$changePriceParams['FORMAT_RESULTS'] = $_REQUEST['chprice_format_result'];
		$changePriceParams['INITIAL_PRICE_TYPE'] = $_REQUEST['chprice_initial_price_type'];
		$changePriceParams['RESULT_MASK'] = $_REQUEST['chprice_result_mask'];
		$changePriceParams['DIFFERENCE_VALUE'] = $_REQUEST['chprice_difference_value'];
		$changePriceParams['VALUE_CHANGING'] = $_REQUEST['chprice_value_changing_price'];

		$changePrice = new Catalog\Helpers\Admin\IblockPriceChanger( $changePriceParams, $IBLOCK_ID );
		$resultChanging = $changePrice->updatePrices( $elementsList );

		if (!$resultChanging->isSuccess())
		{
			foreach ($resultChanging->getErrors() as $error)
			{
				$lAdmin->AddGroupError(GetMessage($error->getMessage(), $error->getCode()));
			}
		}
		unset($resultChanging, $changePrice);

		$_SESSION['CHANGE_PRICE_PARAMS']['PRICE_TYPE'] = $changePriceParams['PRICE_TYPE'];
		$_SESSION['CHANGE_PRICE_PARAMS']['UNITS'] = $changePriceParams['UNITS'];
		$_SESSION['CHANGE_PRICE_PARAMS']['FORMAT_RESULTS'] = $changePriceParams['FORMAT_RESULTS'];
		$_SESSION['CHANGE_PRICE_PARAMS']['INITIAL_PRICE_TYPE'] = $changePriceParams['INITIAL_PRICE_TYPE'];
	}

	if (isset($return_url) && strlen($return_url)>0)
	{
		LocalRedirect($return_url);
	}
}

CJSCore::Init(array('date'));

// List header
$arHeader = array();
if ($bCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_TYPE",
		"content" => GetMessage("IBLIST_A_CATALOG_TYPE"),
		"title" => GetMessage('IBLIST_A_CATALOG_TYPE_TITLE'),
		"align" => "right",
		"sort" => "CATALOG_TYPE",
		"default" => true,
	);
}

//Common
$arHeader[] = array(
		"id" => "NAME",
		"content" => GetMessage("IBLIST_A_NAME"),
		"sort" => "name",
		"default" => true,
	);
$arHeader[] = array(
		"id" => "ACTIVE",
		"content" => GetMessage("IBLIST_A_ACTIVE"),
		"sort" => "active",
		"default" => true,
		"align" => "center",
	);
$arHeader[] = array(
		"id" => "SORT",
		"content" => GetMessage("IBLIST_A_SORT"),
		"sort" => "sort",
		"default" => true,
		"align" => "right",
	);
$arHeader[] = array(
		"id"=>"CODE",
		"content"=>GetMessage("IBLIST_A_CODE"),
		"sort"=>"code",
	);
$arHeader[] = array(
		"id" => "EXTERNAL_ID",
		"content" => GetMessage("IBLIST_A_EXTCODE"),
		"sort" => "external_id",
	);
$arHeader[] = array(
		"id" => "TIMESTAMP_X",
		"content" => GetMessage("IBLIST_A_TIMESTAMP"),
		"sort" => "timestamp_x",
		"default" => true,
	);
$arHeader[] = array(
		"id" => "USER_NAME",
		"content" => GetMessage("IBLIST_A_MODIFIED_BY"),
		"sort" => "modified_by",
	);
$arHeader[] = array(
		"id" => "DATE_CREATE",
		"content" => GetMessage("IBLIST_A_DATE_CREATE"),
		"sort" => "created",
	);
$arHeader[] = array(
		"id" => "CREATED_USER_NAME",
		"content" => GetMessage("IBLIST_A_CREATED_USER_NAME"),
		"sort" => "created_by",
	);
$arHeader[] = array(
		"id" => "ID",
		"content" => GetMessage("IBLIST_A_ID"),
		"sort" => "id",
		"default" => true,
		"align" => "right",
	);
//Section specific
$arHeader[] = array(
		"id" => "ELEMENT_CNT",
		"content" => GetMessage("IBLIST_A_ELS"),
		"sort" => "element_cnt",
		"align" => "right",
	);
$arHeader[] = array(
		"id" => "SECTION_CNT",
		"content" => GetMessage("IBLIST_A_SECS"),
		"align" => "right",
	);
//Element specific
$arHeader[] = array(
		"id" => "DATE_ACTIVE_FROM",
		"content" => GetMessage("IBLIST_A_DATE_ACTIVE_FROM"),
		"sort" => "date_active_from",
	);
$arHeader[] = array(
		"id" => "DATE_ACTIVE_TO",
		"content" => GetMessage("IBLIST_A_DATE_ACTIVE_TO"),
		"sort" => "date_active_to",
	);
$arHeader[] = array(
		"id" => "SHOW_COUNTER",
		"content" => GetMessage("IBLIST_A_SHOW_COUNTER"),
		"sort" => "show_counter",
		"align" => "right",
	);
$arHeader[] = array(
		"id" => "SHOW_COUNTER_START",
		"content" => GetMessage("IBLIST_A_SHOW_COUNTER_START"),
		"sort" => "show_counter_start",
		"align" => "right",
	);
$arHeader[] = array(
		"id" => "PREVIEW_PICTURE",
		"content" => GetMessage("IBLIST_A_PREVIEW_PICTURE"),
		"align" => "right",
		"sort" => "has_preview_picture"
	);
$arHeader[] = array(
		"id" => "PREVIEW_TEXT",
		"content" => GetMessage("IBLIST_A_PREVIEW_TEXT"),
	);
$arHeader[] = array(
		"id" => "DETAIL_PICTURE",
		"content" => GetMessage("IBLIST_A_DETAIL_PICTURE"),
		"align" => "right",
		"sort" => "has_detail_picture"
	);
$arHeader[] = array(
		"id" => "DETAIL_TEXT",
		"content" => GetMessage("IBLIST_A_DETAIL_TEXT"),
	);
$arHeader[] = array(
		"id" => "TAGS",
		"content" => GetMessage("IBLIST_A_TAGS"),
		"sort" => "tags",
	);

$arWFStatusAll = array();
$arWFStatusPerm = array();
if($bWorkFlow)
{
	$arHeader[] = array(
		"id" => "WF_STATUS_ID",
		"content" => GetMessage("IBLIST_A_STATUS"),
		"sort" => "status",
		"default" => true,
	);
	$arHeader[] = array(
		"id" => "WF_NEW",
		"content" => GetMessage("IBLIST_A_WF_NEW"),
	);
	$arHeader[] = array(
		"id" => "LOCK_STATUS",
		"content" => GetMessage("IBLIST_A_LOCK_STATUS"),
		"default" => true,
		"align" => "center",
	);
	$arHeader[] = array(
		"id" => "LOCKED_USER_NAME",
		"content" => GetMessage("IBLIST_A_LOCKED_USER_NAME"),
	);
	$arHeader[] = array(
		"id" => "WF_DATE_LOCK",
		"content" => GetMessage("IBLIST_A_WF_DATE_LOCK"),
	);
	$arHeader[] = array(
		"id" => "WF_COMMENTS",
		"content" => GetMessage("IBLIST_A_WF_COMMENTS"),
	);
	$rsWF = CWorkflowStatus::GetDropDownList("Y");
	while($arWF = $rsWF->GetNext())
		$arWFStatusAll[$arWF["~REFERENCE_ID"]] = $arWF["~REFERENCE"];
	$rsWF = CWorkflowStatus::GetDropDownList("N", "desc");
	while($arWF = $rsWF->GetNext())
		$arWFStatusPerm[$arWF["~REFERENCE_ID"]] = $arWF["~REFERENCE"];
}

foreach($arProps as $arFProps)
{
	$arHeader[] = array(
		"id" => "PROPERTY_".$arFProps['ID'],
		"content" => $arFProps['NAME'],
		"align" => ($arFProps["PROPERTY_TYPE"]=='N'? "right": "left"),
		"sort" => ($arFProps["MULTIPLE"]!='Y'? "PROPERTY_".$arFProps['ID']: ""),
	);
}

if($bCatalog)
{
	$arHeader[] = array(
		"id" => "CATALOG_AVAILABLE",
		"content" => GetMessage("IBLIST_A_CATALOG_AVAILABLE"),
		"title" => GetMessage("IBLIST_A_CATALOG_AVAILABLE_TITLE_EXT"),
		"align" => "center",
		"sort" => "CATALOG_AVAILABLE",
		"default" => true,
	);
	if ($arCatalog['CATALOG_TYPE'] != CCatalogSKU::TYPE_PRODUCT)
	{
		$arHeader[] = array(
			"id" => "CATALOG_QUANTITY",
			"content" => GetMessage("IBLIST_A_CATALOG_QUANTITY_EXT"),
			"align" => "right",
			"sort" => "CATALOG_QUANTITY",
		);
		$arHeader[] = array(
			"id" => "CATALOG_QUANTITY_RESERVED",
			"content" => GetMessage("IBLIST_A_CATALOG_QUANTITY_RESERVED"),
			"align" => "right"
		);
		$arHeader[] = array(
			"id" => "CATALOG_MEASURE_RATIO",
			"content" => GetMessage("IBLIST_A_CATALOG_MEASURE_RATIO"),
			"title" => GetMessage('IBLIST_A_CATALOG_MEASURE_RATIO_TITLE'),
			"align" => "right",
			"default" => false,
		);
		$arHeader[] = array(
			"id" => "CATALOG_MEASURE",
			"content" => GetMessage("IBLIST_A_CATALOG_MEASURE"),
			"title" => GetMessage('IBLIST_A_CATALOG_MEASURE_TITLE'),
			"align" => "right",
			"default" => false,
		);
		$arHeader[] = array(
			"id" => "CATALOG_QUANTITY_TRACE",
			"content" => GetMessage("IBLIST_A_CATALOG_QUANTITY_TRACE"),
			"align" => "right",
		);
		$arHeader[] = array(
			"id" => "CATALOG_WEIGHT",
			"content" => GetMessage("IBLIST_A_CATALOG_WEIGHT"),
			"align" => "right",
			"sort" => "CATALOG_WEIGHT",
			"default" => false,
		);
		$arHeader[] = array(
			"id" => "CATALOG_WIDTH",
			"content" => GetMessage("IBLIST_A_CATALOG_WIDTH"),
			"title" => "",
			"align" => "right",
			"default" => false,
		);
		$arHeader[] = array(
			"id" => "CATALOG_LENGTH",
			"content" => GetMessage("IBLIST_A_CATALOG_LENGTH"),
			"title" => "",
			"align" => "right",
			"default" => false,
		);
		$arHeader[] = array(
			"id" => "CATALOG_HEIGHT",
			"content" => GetMessage("IBLIST_A_CATALOG_HEIGHT"),
			"title" => "",
			"align" => "right",
			"default" => false,
		);
		$arHeader[] = array(
			"id" => "CATALOG_VAT_INCLUDED",
			"content" => GetMessage("IBLIST_A_CATALOG_VAT_INCLUDED"),
			"title" => "",
			"align" => "right",
			"default" => false,
		);
		if ($boolCatalogPurchasInfo)
		{
			$arHeader[] = array(
				"id" => "CATALOG_PURCHASING_PRICE",
				"content" => GetMessage("IBLIST_A_CATALOG_PURCHASING_PRICE"),
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
				"content" => GetMessage("IBLIST_A_CATALOG_BAR_CODE"),
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
		$db_extras = CExtra::GetList(array("ID" => "ASC"));
		while ($extras = $db_extras->Fetch())
			$arCatExtra[$extras['ID']] = $extras;
		unset($extras, $db_extras);
	}

	$arHeader[] = array(
		"id" => "SUBSCRIPTIONS",
		"content" => GetMessage("IBLOCK_FIELD_SUBSCRIPTIONS"),
		"default" => false,
	);
}

if ($bBizproc)
{
	$arWorkflowTemplates = CBPDocument::GetWorkflowTemplatesForDocumentType(array(MODULE_ID, ENTITY, DOCUMENT_TYPE));
	foreach ($arWorkflowTemplates as $arTemplate)
	{
		$arHeader[] = array(
			"id" => "WF_".$arTemplate["ID"],
			"content" => $arTemplate["NAME"],
		);
	}
	$arHeader[] = array(
		"id" => "BIZPROC",
		"content" => GetMessage("IBLIST_A_BP_H"),
	);
	$arHeader[] = array(
		"id" => "LOCK_STATUS",
		"content" => GetMessage("IBLIST_A_LOCK_STATUS"),
		"default" => true,
	);
	$arHeader[] = array(
		"id" => "BP_PUBLISHED",
		"content" => GetMessage("IBLOCK_FIELD_BP_PUBLISHED"),
		"sort" => "status",
		"default" => true,
	);
}

$lAdmin->AddHeaders($arHeader);
$lAdmin->AddVisibleHeaderColumn('ID');

$arSelectedFields = $lAdmin->GetVisibleHeaderColumns();
$arSelectedProps = array();
$selectedPropertyIds = array();
$arSelect = array();
foreach($arProps as $i => $arProperty)
{
	$k = array_search("PROPERTY_".$arProperty['ID'], $arSelectedFields);
	if($k!==false)
	{
		$arSelectedProps[] = $arProperty;
		$selectedPropertyIds[] = $arProperty['ID'];
		if($arProperty["PROPERTY_TYPE"] == "L")
		{
			$arSelect[$arProperty['ID']] = array();
			$rs = CIBlockProperty::GetPropertyEnum($arProperty['ID']);
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = $ar["VALUE"];
		}
		elseif($arProperty["PROPERTY_TYPE"] == "G")
		{
			$arSelect[$arProperty['ID']] = array();
			$rs = CIBlockSection::GetTreeList(array("IBLOCK_ID"=>$arProperty["LINK_IBLOCK_ID"]), array("ID", "NAME", "DEPTH_LEVEL"));
			while($ar = $rs->GetNext())
				$arSelect[$arProperty['ID']][$ar["ID"]] = str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"];
		}
		unset($arSelectedFields[$k]);
	}
}

$arSelectedFields[] = "ID";
$arSelectedFields[] = "CREATED_BY";
$arSelectedFields[] = "LANG_DIR";
$arSelectedFields[] = "LID";
$arSelectedFields[] = "WF_PARENT_ELEMENT_ID";
$arSelectedFields[] = "ACTIVE";

if(in_array("LOCKED_USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "WF_LOCKED_BY";
if(in_array("USER_NAME", $arSelectedFields))
	$arSelectedFields[] = "MODIFIED_BY";
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

$measureList = array(0 => ' ');
if ($bCatalog)
{
	if (in_array("CATALOG_QUANTITY_TRACE", $arSelectedFields))
		$arSelectedFields[] = "CATALOG_QUANTITY_TRACE_ORIG";
	if (in_array('CATALOG_QUANTITY_RESERVED', $arSelectedFields) || in_array('CATALOG_MEASURE', $arSelectedFields))
	{
		if (!in_array('CATALOG_TYPE', $arSelectedFields))
			$arSelectedFields[] = 'CATALOG_TYPE';
	}
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
		foreach($arCatGroup as &$CatalogGroups)
		{
			if(in_array("CATALOG_GROUP_".$CatalogGroups["ID"], $arSelectedFields))
			{
				$arFilter["CATALOG_SHOP_QUANTITY_".$CatalogGroups["ID"]] = 1;
				$boolPriceInc = true;
			}
		}
	}
	if ($boolPriceInc)
	{
		$bCurrency = Loader::includeModule('currency');
		if ($bCurrency)
			$arCurrencyList = array_keys(Currency\CurrencyManager::getCurrencyList());
	}
	unset($boolPriceInc);

	if (in_array('CATALOG_MEASURE', $arSelectedFields))
	{
		$measureIterator = CCatalogMeasure::getList(array(), array(), false, false, array('ID', 'MEASURE_TITLE', 'SYMBOL_RUS'));
		while($measure = $measureIterator->Fetch())
			$measureList[$measure['ID']] = ($measure['SYMBOL_RUS'] != '' ? $measure['SYMBOL_RUS'] : $measure['MEASURE_TITLE']);
		unset($measure, $measureIterator);
	}
}

$arVisibleColumnsMap = array();
foreach($arSelectedFields as $value)
	$arVisibleColumnsMap[$value] = true;

// Getting list data
if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
{
	$arFilter["CNT_ALL"] = "Y";
	$arFilter["ELEMENT_SUBSECTIONS"] = "N";
	$rsData = CIBlockSection::GetMixedList($arOrder, $arFilter, true, $arSelectedFields);
}
else
{
	$rsData = CIBlockSection::GetMixedList($arOrder, $arFilter, false, $arSelectedFields);
}

$rsData = new CAdminResult($rsData, $sTableID);
$rsData->NavStart();

// Navigation setup
$lAdmin->NavText($rsData->GetNavPrint(htmlspecialcharsbx($arIBlock["SECTIONS_NAME"])));

$bSearch = Loader::includeModule('search');

function GetElementName($ID)
{
	$ID = (int)$ID;
	if ($ID <= 0)
		return '';
	static $cache = array();
	if(!isset($cache[$ID]))
	{
		$rsElement = CIBlockElement::GetList(array(), array("ID"=>$ID, "SHOW_HISTORY"=>"Y"), false, false, array("ID","IBLOCK_ID","NAME"));
		$cache[$ID] = $rsElement->GetNext();
	}
	return $cache[$ID];
}
function GetIBlockTypeID($IBLOCK_ID)
{
	$IBLOCK_ID = IntVal($IBLOCK_ID);
	if ($IBLOCK_ID <= 0)
		return '';
	static $cache = array();
	if(!isset($cache[$IBLOCK_ID]))
	{
		$rsIBlock = CIBlock::GetByID($IBLOCK_ID);
		if(!($cache[$IBLOCK_ID] = $rsIBlock->GetNext()))
			$cache[$IBLOCK_ID] = array("IBLOCK_TYPE_ID"=>"");
	}
	return $cache[$IBLOCK_ID]["IBLOCK_TYPE_ID"];
}

$arUsersCache = array();

$boolIBlockElementAdd = CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_element_bind");

$availQuantityTrace = COption::GetOptionString("catalog", "default_quantity_trace", 'N');
$arQuantityTrace = array(
	"D" => GetMessage("IBLIST_DEFAULT_VALUE")." (".($availQuantityTrace=='Y' ? GetMessage("IBLIST_YES_VALUE") : GetMessage("IBLIST_NO_VALUE")).")",
	"Y" => GetMessage("IBLIST_YES_VALUE"),
	"N" => GetMessage("IBLIST_NO_VALUE"),
);

$arRows = array();
$arElemID = array();

$arProductIDs = array();
$arCatalogRights = array();

$mainEntityEdit = false;
$mainEntityEditPrice = false;

// List build
while($arRes = $rsData->NavNext(true, "f_"))
{
	$sec_list_url = htmlspecialcharsbx(CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$f_ID)));
	$el_edit_url = htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $f_ID, array('find_section_section'=>intval($find_section_section), "WF"=>"Y")));;
	$sec_edit_url =  htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($IBLOCK_ID, $f_ID, array('find_section_section'=>intval($find_section_section))));

	$arRes_orig = $arRes;
	if($f_TYPE=="E")
	{
		if($bWorkFlow)
		{
			$LAST_ID = CIBlockElement::WF_GetLast($arRes['ID']);
			if($LAST_ID!=$arRes['ID'])
			{
				$rsData2 = CIBlockElement::GetList(
						Array(),
						Array(
							"ID"=>$LAST_ID,
							"SHOW_HISTORY"=>"Y"
							),
						false,
						Array("nTopCount"=>1),
						$arSelectedFields
					);
				if(isset($arCatGroup))
				{
					$arRes_tmp = Array();
					foreach($arRes as $vv => $vval)
					{
						if(substr($vv, 0, 8) == "CATALOG_")
							$arRes_tmp[$vv] = $arRes[$vv];
					}
				}

				$arRes = $rsData2->NavNext(true, "f_");
				$arRes["WF_NEW"] = $arRes_orig["WF_NEW"];
				if(isset($arCatGroup))
					$arRes = array_merge($arRes, $arRes_tmp);
				$f_ID = $arRes_orig["ID"];
			}
			$lockStatus = $arRes_orig['LOCK_STATUS'];
		}
		elseif($bBizproc)
		{
			$lockStatus = call_user_func(array(ENTITY, "IsDocumentLocked"), $f_ID, "") ? "red" : "green";
		}
		else
		{
			$lockStatus = "";
		}
	}

	$boolEditPrice = false;
	if($f_TYPE=="S")
	{
		$bReadOnly = !CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $f_ID, "section_edit");
		$mainEntityEditPrice = true;
	}
	else
	{
		$bReadOnly = !CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit");
		$boolEditPrice = CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit_price");
		if ($boolEditPrice)
			$mainEntityEditPrice = true;
	}
	if (!$bReadOnly)
		$mainEntityEdit = true;

	if ($bCatalog && 'E' == $f_TYPE)
	{
		if(isset($arVisibleColumnsMap["CATALOG_QUANTITY_TRACE"]))
		{
			$arRes['CATALOG_QUANTITY_TRACE'] = $arRes['CATALOG_QUANTITY_TRACE_ORIG'];
			$f_CATALOG_QUANTITY_TRACE = $f_CATALOG_QUANTITY_TRACE_ORIG;
		}
		if (isset($arVisibleColumnsMap['CATALOG_TYPE']))
		{
			$arRes['CATALOG_TYPE'] = (int)$arRes['CATALOG_TYPE'];

			if (
				$arRes['CATALOG_TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SKU
				|| $arRes['CATALOG_TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SET
			)
			{
				$arRes['CATALOG_QUANTITY_RESERVED'] = '';
			}
			if (
				$arRes['CATALOG_TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SKU
				&& !$showCatalogWithOffers
			)
			{
				$arRes['CATALOG_QUANTITY'] = '';
				$arRes['CATALOG_QUANTITY_TRACE'] = '';
				$arRes['CATALOG_QUANTITY_TRACE_ORIG'] = '';
				$arRes['CATALOG_CAN_BUY_ZERO'] = '';
				$arRes['CATALOG_CAN_BUY_ZERO_ORIG'] = '';
				$arRes['CATALOG_NEGATIVE_AMOUNT_TRACE'] = '';
				$arRes['CATALOG_NEGATIVE_AMOUNT_TRACE_ORIG'] = '';
				$arRes['CATALOG_PURCHASING_PRICE'] = '';
				$arRes['CATALOG_PURCHASING_CURRENCY'] = '';
			}
		}
		if (isset($arVisibleColumnsMap['CATALOG_MEASURE']))
		{
			$arRes['CATALOG_MEASURE'] = (int)$arRes['CATALOG_MEASURE'];
			if ($arRes['CATALOG_MEASURE'] < 0)
				$arRes['CATALOG_MEASURE'] = 0;
		}

	}

	if($f_TYPE=="S") // double click moves deeper
	{
		$arRes["PREVIEW_PICTURE"] = $arRes["PICTURE"];
		$row = $lAdmin->AddRow($f_TYPE.$f_ID, $arRes, $sec_list_url, GetMessage("IBLIST_A_LIST"));
	}
	else // in case of element take his action
	{
		$row = $lAdmin->AddRow($f_TYPE.$f_ID, $arRes);
		$arElemID[] = $f_ID;
	}
	$arRows[$f_TYPE.$f_ID] = $row;

	if($f_TYPE=="S")
		$row->AddViewField("NAME", '<a href="'.$sec_list_url.'" class="adm-list-table-icon-link" title="'.GetMessage("IBLIST_A_LIST").'"><span class="adm-submenu-item-link-icon adm-list-table-icon iblock-section-icon"></span><span class="adm-list-table-link">'.$f_NAME.'</span></a>');
	else
		$row->AddViewField("NAME", '<a href="'.$el_edit_url.'" title="'.GetMessage("IBLIST_A_EDIT").'">'.$f_NAME.'</a>');
	if($bReadOnly)
	{
		$row->AddInputField("NAME", false);
		$row->AddCheckField("ACTIVE", false);
		$row->AddInputField("SORT", false);
		$row->AddInputField("CODE", false);
		$row->AddInputField("EXTERNAL_ID", false);
	}
	else
	{
		$row->AddInputField("NAME", Array('size'=>'35'));
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("SORT", Array('size'=>'3'));
		$row->AddInputField("CODE");
		$row->AddInputField("EXTERNAL_ID");
	}

	if($bBizproc && $f_TYPE=="E")
		$row->AddCheckField("BP_PUBLISHED", false);

	if(array_key_exists("MODIFIED_BY", $arVisibleColumnsMap) && intval($f_MODIFIED_BY) > 0)
	{
		if(!array_key_exists($f_MODIFIED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_MODIFIED_BY);
			$arUsersCache[$f_MODIFIED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_MODIFIED_BY])
			$row->AddViewField("USER_NAME", '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_MODIFIED_BY.'" title="'.GetMessage("IBLIST_A_USERINFO").'">'.$f_MODIFIED_BY."</a>]&nbsp;(".htmlspecialcharsEx($arUser["LOGIN"]).") ".htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]));
	}

	if(array_key_exists("CREATED_BY", $arVisibleColumnsMap) && intval($f_CREATED_BY) > 0)
	{
		if(!array_key_exists($f_CREATED_BY, $arUsersCache))
		{
			$rsUser = CUser::GetByID($f_CREATED_BY);
			$arUsersCache[$f_CREATED_BY] = $rsUser->Fetch();
		}
		if($arUser = $arUsersCache[$f_CREATED_BY])
			$row->AddViewField("CREATED_USER_NAME", '[<a href="user_edit.php?lang='.LANGUAGE_ID.'&ID='.$f_CREATED_BY.'" title="'.GetMessage("IBLIST_A_USERINFO").'">'.$f_CREATED_BY."</a>]&nbsp;(".htmlspecialcharsEx($arUser["LOGIN"]).") ".htmlspecialcharsEx($arUser["NAME"]." ".$arUser["LAST_NAME"]));
	}

	if (array_key_exists("PREVIEW_PICTURE", $arVisibleColumnsMap))
	{
		if ($bReadOnly)
			$row->AddViewFileField("PREVIEW_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				)
			);
		else
			$row->AddFileField("PREVIEW_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => true,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => true,
					'del' => true,
					'description' => $f_TYPE=="E",
				)
			);
	}

	if (array_key_exists("DETAIL_PICTURE", $arVisibleColumnsMap))
	{
		if ($bReadOnly)
			$row->AddViewFileField("DETAIL_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				)
			);
		else
			$row->AddFileField("DETAIL_PICTURE", array(
				"IMAGE" => "Y",
				"PATH" => "Y",
				"FILE_SIZE" => "Y",
				"DIMENSIONS" => "Y",
				"IMAGE_POPUP" => "Y",
				"MAX_SIZE" => $maxImageSize,
				"MIN_SIZE" => $minImageSize,
				), array(
					'upload' => true,
					'medialib' => false,
					'file_dialog' => false,
					'cloud' => true,
					'del' => true,
					'description' => $f_TYPE=="E",
				)
			);
	}

	if($f_TYPE=="S")
	{
		if(array_key_exists("ELEMENT_CNT", $arVisibleColumnsMap))
		{
			$row->AddViewField("ELEMENT_CNT", $f_ELEMENT_CNT.'('.(int)CIBlockSection::GetSectionElementsCount($f_ID, array("CNT_ALL"=>"Y")).')');
		}

		if(array_key_exists("SECTION_CNT", $arVisibleColumnsMap))
		{
			$row->AddViewField("SECTION_CNT", " ".(int)(CIBlockSection::GetCount(array("IBLOCK_ID"=>$IBLOCK_ID, "SECTION_ID"=>$f_ID))));
		}
	}

	if($f_TYPE=="E")
	{
		if (array_key_exists("PREVIEW_TEXT", $arVisibleColumnsMap))
			$row->AddViewField("PREVIEW_TEXT", ($arRes["PREVIEW_TEXT_TYPE"]=="text" ? htmlspecialcharsex($arRes["PREVIEW_TEXT"]) : HTMLToTxt($arRes["PREVIEW_TEXT"])));
		if (array_key_exists("DETAIL_TEXT", $arVisibleColumnsMap))
			$row->AddViewField("DETAIL_TEXT", ($arRes["DETAIL_TEXT_TYPE"]=="text" ? htmlspecialcharsex($arRes["DETAIL_TEXT"]) : HTMLToTxt($arRes["DETAIL_TEXT"])));
		if($bWorkFlow || $bBizproc)
		{
			$lamp = '<span class="adm-lamp adm-lamp-in-list adm-lamp-'.$lockStatus.'"></span>';
			if($lockStatus=='red' && $arRes_orig['LOCKED_USER_NAME']!='')
				$row->AddViewField("LOCK_STATUS", $lamp.$arRes_orig['LOCKED_USER_NAME']);
			else
				$row->AddViewField("LOCK_STATUS", $lamp);
		}

		$row->AddCheckField("WF_NEW", false);
		if (!$bReadOnly)
		{
			$row->AddCalendarField("DATE_ACTIVE_FROM", array(), $useCalendarTime);
			$row->AddCalendarField("DATE_ACTIVE_TO", array(), $useCalendarTime);
			if (array_key_exists("PREVIEW_TEXT", $arVisibleColumnsMap))
			{
				$sHTML = '<input type="radio" name="FIELDS['.$f_TYPE.$f_ID.'][PREVIEW_TEXT_TYPE]" value="text" id="'.$f_TYPE.$f_ID.'PREVIEWtext"';
				if($arRes["PREVIEW_TEXT_TYPE"]!="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_TYPE.$f_ID.'PREVIEWtext">text</label> /';
				$sHTML .= '<input type="radio" name="FIELDS['.$f_TYPE.$f_ID.'][PREVIEW_TEXT_TYPE]" value="html" id="'.$f_TYPE.$f_ID.'PREVIEWhtml"';
				if($arRes["PREVIEW_TEXT_TYPE"]=="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_TYPE.$f_ID.'PREVIEWhtml">html</label><br>';
				$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$f_TYPE.$f_ID.'][PREVIEW_TEXT]">'.htmlspecialcharsex($arRes["PREVIEW_TEXT"]).'</textarea>';
				$row->AddEditField("PREVIEW_TEXT", $sHTML);
			}
			if (array_key_exists("DETAIL_TEXT", $arVisibleColumnsMap))
			{
				$sHTML = '<input type="radio" name="FIELDS['.$f_TYPE.$f_ID.'][DETAIL_TEXT_TYPE]" value="text" id="'.$f_TYPE.$f_ID.'DETAILtext"';
				if($arRes["DETAIL_TEXT_TYPE"]!="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_TYPE.$f_ID.'DETAILtext">text</label> /';
				$sHTML .= '<input type="radio" name="FIELDS['.$f_TYPE.$f_ID.'][DETAIL_TEXT_TYPE]" value="html" id="'.$f_TYPE.$f_ID.'DETAILhtml"';
				if($arRes["DETAIL_TEXT_TYPE"]=="html")
					$sHTML .= ' checked';
				$sHTML .= '><label for="'.$f_TYPE.$f_ID.'DETAILhtml">html</label><br>';
				$sHTML .= '<textarea rows="10" cols="50" name="FIELDS['.$f_TYPE.$f_ID.'][DETAIL_TEXT]">'.htmlspecialcharsex($arRes["DETAIL_TEXT"]).'</textarea>';
				$row->AddEditField("DETAIL_TEXT", $sHTML);
			}

			if (array_key_exists("TAGS", $arVisibleColumnsMap))
			{
				if ($bSearch)
				{
					$row->AddViewField("TAGS", $f_TAGS);
					$row->AddEditField("TAGS", InputTags("FIELDS[".$f_TYPE.$f_ID."][TAGS]", $arRes["TAGS"], $arIBlock["SITE_ID"]));
				}
				else
				{
					$row->AddInputField("TAGS");
				}
			}

			if(!empty($arWFStatusPerm))
				$row->AddSelectField("WF_STATUS_ID", $arWFStatusPerm);
			if($arRes_orig['WF_NEW']=='Y' || $arRes['WF_STATUS_ID']=='1')
				$row->AddViewField("WF_STATUS_ID", htmlspecialcharsex($arWFStatusAll[$arRes['WF_STATUS_ID']]));
			else
				$row->AddViewField("WF_STATUS_ID", '<a href="'.$el_edit_url.'" title="'.GetMessage("IBLIST_A_ED_TITLE").'">'.htmlspecialcharsex($arWFStatusAll[$arRes['WF_STATUS_ID']]).'</a> / <a href="'.'iblock_element_edit.php?ID='.$arRes_orig['ID'].$sThisSectionUrl.'" title="'.GetMessage("IBLIST_A_ED2_TITLE").'">'.htmlspecialcharsex($arWFStatusAll[$arRes_orig['WF_STATUS_ID']]).'</a>');
		}
		else
		{
			$row->AddCalendarField("DATE_ACTIVE_FROM", false);
			$row->AddCalendarField("DATE_ACTIVE_TO", false);
			$row->AddViewField("WF_STATUS_ID", htmlspecialcharsex($arWFStatusAll[$arRes['WF_STATUS_ID']]));
			if (array_key_exists("TAGS", $arVisibleColumnsMap))
				$row->AddViewField("TAGS", $f_TAGS);
		}
	}

	$row->AddViewField("ID", '<a href="'.($f_TYPE=="S"?$sec_edit_url:$el_edit_url).'" title="'.GetMessage("IBLIST_A_EDIT").'">'.$f_ID.'</a>');

	$arProperties = array();
	if($f_TYPE=="E" && !empty($arSelectedProps))
	{
		$rsProperties = CIBlockElement::GetProperty($IBLOCK_ID, $arRes['ID'], 'id', 'asc', array('ID' => $selectedPropertyIds));
		while($ar = $rsProperties->GetNext())
		{
			if(!array_key_exists($ar["ID"], $arProperties))
				$arProperties[$ar["ID"]] = array();
			$arProperties[$ar["ID"]][$ar["PROPERTY_VALUE_ID"]] = $ar;
		}
		unset($ar);
		unset($rsProperties);

		foreach($arSelectedProps as $aProp)
		{
			$arViewHTML = array();
			$arEditHTML = array();
			if(strlen($aProp["USER_TYPE"])>0)
				$arUserType = CIBlockProperty::GetUserType($aProp["USER_TYPE"]);
			else
				$arUserType = array();
			$max_file_size_show=100000;

			$last_property_id = false;
			foreach($arProperties[$aProp["ID"]] as $prop_id => $prop)
			{
				$prop['PROPERTY_VALUE_ID'] = intval($prop['PROPERTY_VALUE_ID']);
				$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].'][VALUE]';
				$DESCR_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].'][DESCRIPTION]';
				//View part
				if(array_key_exists("GetAdminListViewHTML", $arUserType))
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
				elseif($prop['PROPERTY_TYPE']=='N')
					$arViewHTML[] = $bExcel && isset($_COOKIE[$dsc_cookie_name])? number_format($prop["VALUE"], 4, chr($_COOKIE[$dsc_cookie_name]), ''): $prop["VALUE"];
				elseif($prop['PROPERTY_TYPE']=='S')
					$arViewHTML[] = $prop["VALUE"];
				elseif($prop['PROPERTY_TYPE']=='L')
					$arViewHTML[] = $prop["VALUE_ENUM"];
				elseif($prop['PROPERTY_TYPE']=='F')
				{
					if ($bExcel)
					{
						$arFile = CFile::GetFileArray($prop["VALUE"]);
						if (is_array($arFile))
							$arViewHTML[] = CHTTP::URN2URI($arFile["SRC"]);
						else
							$arViewHTML[] = "";
					}
					else
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
				}
				elseif($prop['PROPERTY_TYPE']=='G')
				{
					if(intval($prop["VALUE"])>0)
					{
						$rsSection = CIBlockSection::GetList(
							array(),
							array("ID" => $prop["VALUE"]),
							false,
							array('ID', 'NAME', 'IBLOCK_ID')
						);
						if($arSection = $rsSection->GetNext())
						{
							$arViewHTML[] = $arSection['NAME'].
							' [<a href="'.
							htmlspecialcharsbx(CIBlock::GetAdminSectionEditLink($arSection['IBLOCK_ID'], $arSection['ID'])).
							'" title="'.GetMessage("IBEL_A_SEC_EDIT").'">'.$arSection['ID'].'</a>]';
						}
					}
				}
				elseif($prop['PROPERTY_TYPE']=='E')
				{
					if($t = GetElementName($prop["VALUE"]))
					{
						$arViewHTML[] = $t['NAME'].
						' [<a href="'.htmlspecialcharsbx(CIBlock::GetAdminElementEditLink($t['IBLOCK_ID'], $t['ID'], array(
							"find_section_section" => $find_section_section,
							'WF' => 'Y',
						))).'" title="'.GetMessage("IBEL_A_EL_EDIT").'">'.$t['ID'].'</a>]';
					}
				}
				//Edit Part
				$bUserMultiple = $prop["MULTIPLE"] == "Y" &&  array_key_exists("GetPropertyFieldHtmlMulty", $arUserType);
				if($bUserMultiple)
				{
					if($last_property_id != $prop["ID"])
					{
						$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].']';
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
				elseif(array_key_exists("GetPropertyFieldHtml", $arUserType))
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
				elseif($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
				{
					if($prop["ROW_COUNT"] > 1)
						$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'">'.$prop["VALUE"].'</textarea>';
					else
						$html = '<input type="text" name="'.$VALUE_NAME.'" value="'.$prop["VALUE"].'" size="'.$prop["COL_COUNT"].'">';
					if($prop["WITH_DESCRIPTION"] == "Y")
						$html .= ' <span title="'.GetMessage("IBLIST_A_PROP_DESC_TITLE").'">'.GetMessage("IBLIST_A_PROP_DESC").
							'<input type="text" name="'.$DESCR_NAME.'" value="'.$prop["DESCRIPTION"].'" size="18"></span>';
					$arEditHTML[] = $html;
				}
				elseif($prop['PROPERTY_TYPE']=='L' && ($last_property_id!=$prop["ID"]))
				{
					$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][]';
					$arValues = array();
					foreach($arProperties[$prop["ID"]] as $g_prop)
					{
						$g_prop = intval($g_prop["VALUE"]);
						if($g_prop > 0)
							$arValues[$g_prop] = $g_prop;
					}
					if($prop['LIST_TYPE']=='C')
					{
						if($prop['MULTIPLE'] == "Y" || count($arSelect[$prop['ID']]) == 1)
						{
							$html = '<input type="hidden" name="'.$VALUE_NAME.'" value="">';
							foreach($arSelect[$prop['ID']] as $value => $display)
							{
								$html .= '<input type="checkbox" name="'.$VALUE_NAME.'" id="id'.$uniq_id.'" value="'.$value.'"';
								if(array_key_exists($value, $arValues))
									$html .= ' checked';
								$html .= '>&nbsp;<label for="id'.$uniq_id.'">'.$display.'</label><br>';
								$uniq_id++;
							}
						}
						else
						{
							$html = '<input type="radio" name="'.$VALUE_NAME.'" id="id'.$uniq_id.'" value=""';
							if(count($arValues) < 1)
								$html .= ' checked';
							$html .= '>&nbsp;<label for="id'.$uniq_id.'">'.GetMessage("IBLIST_A_PROP_NOT_SET").'</label><br>';
							$uniq_id++;
							foreach($arSelect[$prop['ID']] as $value => $display)
							{
								$html .= '<input type="radio" name="'.$VALUE_NAME.'" id="id'.$uniq_id.'" value="'.$value.'"';
								if(array_key_exists($value, $arValues))
									$html .= ' checked';
								$html .= '>&nbsp;<label for="id'.$uniq_id.'">'.$display.'</label><br>';
								$uniq_id++;
							}
						}
					}
					else
					{
						$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
						$html .= '<option value=""'.(count($arValues) < 1? ' selected': '').'>'.GetMessage("IBLIST_A_PROP_NOT_SET").'</option>';
						foreach($arSelect[$prop['ID']] as $value => $display)
						{
							$html .= '<option value="'.$value.'"';
							if(array_key_exists($value, $arValues))
								$html .= ' selected';
							$html .= '>'.$display.'</option>'."\n";
						}
						$html .= "</select>\n";
					}
					$arEditHTML[] = $html;
				}
				elseif($prop['PROPERTY_TYPE']=='F' && ($last_property_id != $prop["ID"]))
				{
					if($prop['MULTIPLE'] == "Y")
					{
						$inputName = array();
						foreach($arProperties[$prop["ID"]] as $g_prop)
						{
							$inputName['FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$g_prop['PROPERTY_VALUE_ID'].'][VALUE]'] = $g_prop["VALUE"];
						}
						if (class_exists('\Bitrix\Main\UI\FileInput', true))
						{
							$arEditHTML[] = \Bitrix\Main\UI\FileInput::createInstance(array(
									"name" => 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][n#IND#]',
									"description" => $prop["WITH_DESCRIPTION"]=="Y",
									"upload" => true,
									"medialib" => false,
									"fileDialog" => false,
									"cloud" => false,
									"delete" => true,
								))->show($inputName);
						}
						else
						{
							$arEditHTML[] = CFileInput::ShowMultiple($inputName, 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][n#IND#]', array(
								"IMAGE" => "Y",
								"PATH" => "Y",
								"FILE_SIZE" => "Y",
								"DIMENSIONS" => "Y",
								"IMAGE_POPUP" => "Y",
								"MAX_SIZE" => $maxImageSize,
								"MIN_SIZE" => $minImageSize,
								), false, array(
									'upload' => true,
									'medialib' => false,
									'file_dialog' => false,
									'cloud' => false,
									'del' => true,
									'description' => $prop["WITH_DESCRIPTION"]=="Y",
								)
							);
						}
					}
					else
					{
						$arEditHTML[] = CFileInput::Show($VALUE_NAME, $prop["VALUE"], array(
							"IMAGE" => "Y",
							"PATH" => "Y",
							"FILE_SIZE" => "Y",
							"DIMENSIONS" => "Y",
							"IMAGE_POPUP" => "Y",
							"MAX_SIZE" => $maxImageSize,
							"MIN_SIZE" => $minImageSize,
							), array(
								'upload' => true,
								'medialib' => false,
								'file_dialog' => false,
								'cloud' => false,
								'del' => true,
								'description' => $prop["WITH_DESCRIPTION"]=="Y",
							)
						);
					}
				}
				elseif(($prop['PROPERTY_TYPE']=='G') && ($last_property_id!=$prop["ID"]))
				{
					$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][]';
					$arValues = array();
					foreach($arProperties[$prop["ID"]] as $g_prop)
					{
						$g_prop = intval($g_prop["VALUE"]);
						if($g_prop > 0)
							$arValues[$g_prop] = $g_prop;
					}
					$html = '<select name="'.$VALUE_NAME.'" size="'.$prop["MULTIPLE_CNT"].'" '.($prop["MULTIPLE"]=="Y"?"multiple":"").'>';
					$html .= '<option value=""'.(count($arValues) < 1? ' selected': '').'>'.GetMessage("IBLIST_A_PROP_NOT_SET").'</option>';
					foreach($arSelect[$prop['ID']] as $value => $display)
					{
						$html .= '<option value="'.$value.'"';
						if(array_key_exists($value, $arValues))
							$html .= ' selected';
						$html .= '>'.$display.'</option>'."\n";
					}
					$html .= "</select>\n";
					$arEditHTML[] = $html;
				}
				elseif($prop['PROPERTY_TYPE']=='E')
				{
					$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].']['.$prop['PROPERTY_VALUE_ID'].']';
					$fixIBlock = $prop["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID'];
					if($t = GetElementName($prop["VALUE"]))
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
			$table_id = md5($f_TYPE.$f_ID.':'.$aProp['ID']);
			if($aProp["MULTIPLE"] == "Y")
			{
				$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0][VALUE]';
				$DESCR_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0][DESCRIPTION]';
				if(array_key_exists("GetPropertyFieldHtmlMulty", $arUserType))
				{
				}
				elseif(array_key_exists("GetPropertyFieldHtml", $arUserType))
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
				elseif($prop['PROPERTY_TYPE']=='N' || $prop['PROPERTY_TYPE']=='S')
				{
					if($prop["ROW_COUNT"] > 1)
						$html = '<textarea name="'.$VALUE_NAME.'" cols="'.$prop["COL_COUNT"].'" rows="'.$prop["ROW_COUNT"].'"></textarea>';
					else
						$html = '<input type="text" name="'.$VALUE_NAME.'" value="" size="'.$prop["COL_COUNT"].'">';
					if($prop["WITH_DESCRIPTION"] == "Y")
						$html .= ' <span title="'.GetMessage("IBLIST_A_PROP_DESC_TITLE").'">'.GetMessage("IBLIST_A_PROP_DESC").'<input type="text" name="'.$DESCR_NAME.'" value="" size="18"></span>';
					$arEditHTML[] = $html;
				}
				elseif($prop['PROPERTY_TYPE']=='F')
				{
				}
				elseif($prop['PROPERTY_TYPE']=='E')
				{
					$VALUE_NAME = 'FIELDS['.$f_TYPE.$f_ID.'][PROPERTY_'.$prop['ID'].'][n0]';
					$fixIBlock = $prop["LINK_IBLOCK_ID"] > 0;
					$windowTableId = 'iblockprop-'.Iblock\PropertyTable::TYPE_ELEMENT.'-'.$prop['ID'].'-'.$prop['LINK_IBLOCK_ID'];
					$arEditHTML[] = '<input type="text" name="'.$VALUE_NAME.'" id="'.$VALUE_NAME.'" value="" size="5">'.
						'<input type="button" value="..." onClick="jsUtils.OpenWindow(\'iblock_element_search.php?lang='.LANGUAGE_ID.'&amp;IBLOCK_ID='.$prop["LINK_IBLOCK_ID"].'&amp;n='.urlencode($VALUE_NAME).($fixIBlock ? '&amp;iblockfix=y' : '').'&amp;tableId='.$windowTableId.'\', 900, 700);">'.
						'&nbsp;<span id="sp_'.$VALUE_NAME.'" ></span>';
					unset($windowTableId);
					unset($fixIBlock);
				}

				if(
					$prop["PROPERTY_TYPE"] !== "G"
					&& $prop["PROPERTY_TYPE"] !== "L"
					&& $prop["PROPERTY_TYPE"] !== "F"
					&& !$bUserMultiple
				)
					$arEditHTML[] = '<input type="button" value="'.GetMessage("IBLIST_A_PROP_ADD").'" onClick="addNewRow(\'tb'.$table_id.'\')">';
			}
			if(count($arViewHTML) > 0)
			{
				if($prop["PROPERTY_TYPE"] == "F")
					$row->AddViewField("PROPERTY_".$aProp['ID'], implode("", $arViewHTML));
				else
					$row->AddViewField("PROPERTY_".$aProp['ID'], implode(" / ", $arViewHTML));
			}
			if(!$bReadOnly && count($arEditHTML) > 0)
				$row->AddEditField("PROPERTY_".$aProp['ID'], '<table id="tb'.$table_id.'" border=0 cellpadding=0 cellspacing=0><tr><td nowrap>'.implode("</td></tr><tr><td nowrap>", $arEditHTML).'</td></tr></table>');
		}
	}
	if($f_TYPE == "E")
	{
		$arCatalogRights[$row->arRes['ID']] = (!$bReadOnly && $boolEditPrice && $boolCatalogPrice);
		if (!$bReadOnly)
		{
			if ($boolEditPrice && $boolCatalogPrice)
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
					if ((float)$row->arRes["CATALOG_PURCHASING_PRICE"] > 0)
					{
						if ($bCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
					if ($catalogPurchasInfoEdit && $bCurrency)
					{
						$editFieldCode = '<input type="hidden" name="FIELDS_OLD[E'.$f_ID.'][CATALOG_PURCHASING_PRICE]" value="'.$row->arRes['CATALOG_PURCHASING_PRICE'].'">';
						$editFieldCode .= '<input type="hidden" name="FIELDS_OLD[E'.$f_ID.'][CATALOG_PURCHASING_CURRENCY]" value="'.$row->arRes['CATALOG_PURCHASING_CURRENCY'].'">';
						$editFieldCode .= '<input type="text" size="5" name="FIELDS[E'.$f_ID.'][CATALOG_PURCHASING_PRICE]" value="'.$row->arRes['CATALOG_PURCHASING_PRICE'].'">';
						$editFieldCode .= '<select name="FIELDS[E'.$f_ID.'][CATALOG_PURCHASING_CURRENCY]">';
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
						if ($bCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
				}
			}
		}
		else
		{
			if ($bCatalog)
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
						if ($bCurrency)
							$price = CCurrencyLang::CurrencyFormat($row->arRes["CATALOG_PURCHASING_PRICE"], $row->arRes["CATALOG_PURCHASING_CURRENCY"], true);
						else
							$price = $row->arRes["CATALOG_PURCHASING_PRICE"]." ".$row->arRes["CATALOG_PURCHASING_CURRENCY"];
					}
					$row->AddViewField("CATALOG_PURCHASING_PRICE", htmlspecialcharsEx($price));
				}
			}
		}
	}
	if($f_TYPE == "E")
	{
		if ($bCatalog)
		{
			if (isset($arCatGroup) && !empty($arCatGroup))
			{
				foreach($arCatGroup as $CatGroup)
				{
					if (array_key_exists("CATALOG_GROUP_".$CatGroup["ID"], $arVisibleColumnsMap))
					{
						$price = "";
						$sHTML = "";
						$selectCur = "";
						$extraId = (isset($arRes['CATALOG_EXTRA_ID_'.$CatGroup['ID']]) ? (int)$arRes['CATALOG_EXTRA_ID_'.$CatGroup['ID']] : 0);
						if (!isset($arCatExtra[$extraId]))
							$extraId = 0;
						if ($bCurrency)
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
										'IBLIST_A_CATALOG_EXTRA_DESCRIPTION',
										array('#VALUE#' => $arCatExtra[$extraId]['NAME'])
									)).
									'">(+'.$arCatExtra[$extraId]['PERCENTAGE'].'%)</span>';
							}
							if ($boolCatalogPrice && $boolEditPrice)
							{
								$selectCur = '<select name="CATALOG_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']" id="CATALOG_CURRENCY['.$f_ID.']['.$CatGroup["ID"].']"';
								if ($CatGroup["BASE"]=="Y")
									$selectCur .= ' onchange="top.ChangeBaseCurrency('.$f_ID.')"';
								elseif ($extraId > 0)
									$selectCur .= ' disabled readonly';
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
								$sHTML .= ' onchange="top.ChangeBasePrice('.$f_ID.')"';
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

							$row->AddEditField("CATALOG_GROUP_".$CatGroup["ID"], $sHTML);
						}
						unset($extraId);
					}
				}
			}
		}
	}

	if ($bBizproc)
	{
		if ($f_TYPE == "E")
		{
			$arDocumentStates = CBPDocument::GetDocumentStates(
				array(MODULE_ID, ENTITY, DOCUMENT_TYPE),
				array(MODULE_ID, ENTITY, $f_ID)
			);

			$arRes["CURENT_USER_GROUPS"] = $USER->GetUserGroupArray();
			if ($arRes["CREATED_BY"] == $USER->GetID())
				$arRes["CURENT_USER_GROUPS"][] = "Author";

			$arStr = array();
			$arStr1 = array();
			foreach ($arDocumentStates as $kk => $vv)
			{
				$canViewWorkflow = call_user_func(array(ENTITY, "CanUserOperateDocument"),
					CBPCanUserOperateOperation::ViewWorkflow,
					$USER->GetID(),
					$f_ID,
					array("AllUserGroups" => $arRes["CURENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates, "WorkflowId" => $kk)
				);
				if (!$canViewWorkflow)
					continue;

				$arStr1[$vv["TEMPLATE_ID"]] = $vv["TEMPLATE_NAME"];
				$arStr[$vv["TEMPLATE_ID"]] .= "<a href=\"/bitrix/admin/bizproc_log.php?ID=".$kk.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array("mode", "table_id")))."\">".(strlen($vv["STATE_TITLE"]) > 0 ? $vv["STATE_TITLE"] : $vv["STATE_NAME"])."</a><br />";

				if (strlen($vv["ID"]) > 0)
				{
					$arTasks = CBPDocument::GetUserTasksForWorkflow($USER->GetID(), $vv["ID"]);
					foreach ($arTasks as $arTask)
					{
						$arStr[$vv["TEMPLATE_ID"]] .= GetMessage("IBLIST_A_BP_TASK").":<br /><a href=\"bizproc_task.php?id=".$arTask["ID"]."\" title=\"".$arTask["DESCRIPTION"]."\">".$arTask["NAME"]."</a><br /><br />";
					}
				}
			}

			$str = "";
			foreach ($arStr as $k => $v)
			{
				$row->AddViewField("WF_".$k, $v);
				$str .= "<b>".(strlen($arStr1[$k]) > 0 ? $arStr1[$k] : GetMessage("IBLIST_BP"))."</b>:<br />".$v."<br />";
			}

			$row->AddViewField("BIZPROC", $str);
		}
	}

	$arActions = array();

	if($f_ACTIVE == "Y")
	{
		$arActive = array(
			"TEXT" => GetMessage("IBLIST_A_DEACTIVATE"),
			"ACTION" => $lAdmin->ActionDoGroup($f_TYPE.$f_ID, "deactivate", $sThisSectionUrl),
			"ONCLICK" => "",
		);
	}
	else
	{
		$arActive = array(
			"TEXT" => GetMessage("IBLIST_A_ACTIVATE"),
			"ACTION" => $lAdmin->ActionDoGroup($f_TYPE.$f_ID, "activate", $sThisSectionUrl),
			"ONCLICK" => "",
		);
	}

	$clearCounter = array(
		"TEXT" => GetMessage('IBLIST_A_CLEAR_COUNTER'),
		"TITLE" => GetMessage('IBLIST_A_CLEAR_COUNTER_TITLE'),
		"ACTION" => "if(confirm('".GetMessageJS("IBLIST_A_CLEAR_COUNTER_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_TYPE.$f_ID, "clear_counter", $sThisSectionUrl),
		"ONCLICK" => ""
	);

	if($f_TYPE=="S")
	{
		if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $f_ID, "section_edit"))
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => GetMessage("IBLOCK_CHANGE"),
				"ACTION" => $lAdmin->ActionRedirect($sec_edit_url),
				"DEFAULT" => true,
			);

		if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $f_ID, "section_delete"))
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage("MAIN_DELETE"),
				"ACTION" => "if(confirm('".GetMessageJS("IBLOCK_CONFIRM_DEL_MESSAGE")."')) ".$lAdmin->ActionDoGroup($f_TYPE.$f_ID, "delete", $sThisSectionUrl),
			);
	}
	elseif($bWorkFlow)
	{
		if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit_any_wf_status"))
			$STATUS_PERMISSION = 2;
		else
			$STATUS_PERMISSION = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"]);

		$intMinPerm = 2;

		$arUnLock = Array(
			"ICON" => "unlock",
			"TEXT" => GetMessage("IBLIST_A_UNLOCK"),
			"TITLE" => GetMessage("IBLIST_A_UNLOCK_ALT"),
			"ACTION" => "if(confirm('".GetMessageJS("IBLIST_A_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_TYPE.$arRes_orig['ID'], "unlock", $sThisSectionUrl),
		);

		if ($arRes_orig['LOCK_STATUS']=="red")
		{
			if (CWorkflow::IsAdmin())
				$arActions[] = $arUnLock;
		}
		else
		{
			/*
			 * yellow unlock
			 * edit
			 * copy
			 * history
			 * view (?)
			 * edit_orig (?)
			 * delete
			 */
		if (
				CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit")
				&& (2 <= $STATUS_PERMISSION)
			)
			{
				if ($arRes_orig['LOCK_STATUS']=="yellow")
				{
					$arActions[] = $arUnLock;
					$arActions[] = array("SEPARATOR"=>true);
				}

				$arActions[] = array(
					"ICON" => "edit",
					"TEXT" => GetMessage("IBLOCK_CHANGE"),
					"DEFAULT" => true,
					"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $arRes_orig['ID'], array(
						'WF' => 'Y',
						'find_section_section' => intval($find_section_section)
					)))
				);
				$arActions[] = $arActive;
				$arActions[] = array('SEPARATOR' => 'Y');
				$arActions[] = $clearCounter;
				$arActions[] = array('SEPARATOR' => 'Y');
			}

			if (
				$boolIBlockElementAdd
				&& (2 <= $STATUS_PERMISSION)
			)
			{
				$arActions[] = array(
					"ICON" => "copy",
					"TEXT" => GetMessage("IBLIST_A_COPY_ELEMENT"),
					"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $arRes_orig['ID'], array(
						'WF' => 'Y',
						'find_section_section' => intval($find_section_section),
						'action' => 'copy'
					)))
				);
			}

			if(!defined("CATALOG_PRODUCT"))
			{
				$arActions[] = array(
					"ICON" => "history",
					"TEXT" => GetMessage("IBLIST_A_HIST"),
					"TITLE" => GetMessage("IBLIST_A_HISTORY_ALT"),
					"ACTION" => $lAdmin->ActionRedirect('iblock_history_list.php?ELEMENT_ID='.$arRes_orig['ID'].$sThisSectionUrl),
				);
			}

			if(strlen($f_DETAIL_PAGE_URL)>0)
			{
				$tmpVar = CIBlock::ReplaceDetailUrl($arRes_orig["DETAIL_PAGE_URL"], $arRes_orig, true, "E");
				if (
					$arRes_orig['WF_NEW']=="Y"
					&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit")
					&& (2 <= $STATUS_PERMISSION)
				) // not yet published element under workflow
				{
					$arActions[] = array("SEPARATOR"=>true);
					$arActions[] = array(
						"ICON" => "view",
						"TEXT" => GetMessage("IBLIST_A_VIEW_WF"),
						"TITLE" => GetMessage("IBLIST_A_VIEW_WF_ALT"),
						"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar).((strpos($tmpVar, "?") !== false) ? "&" : "?")."show_workflow=Y"),
					);
				}
				elseif (
					$arRes["WF_STATUS_ID"] > 1
					&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit")
					&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit_any_wf_status")
				)
				{
					$arActions[] = array("SEPARATOR"=>true);
					$arActions[] = array(
						"ICON" => "view",
						"TEXT" => GetMessage("IBLIST_A_ADMIN_VIEW"),
						"TITLE" => GetMessage("IBLIST_A_VIEW_WF_ALT"),
						"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar)),
					);

					$arActions[] = array(
						"ICON" => "view",
						"TEXT" => GetMessage("IBLIST_A_VIEW_WF"),
						"TITLE" => GetMessage("IBLIST_A_VIEW_WF_ALT"),
						"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar).((strpos($tmpVar, "?") !== false) ? "&" : "?")."show_workflow=Y"),
					);
				}
				else
				{
					if (
						CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit")
						&& (2 <= $STATUS_PERMISSION)
					)
					$arActions[] = array("SEPARATOR"=>true);
					$arActions[] = array(
						"ICON" => "view",
						"TEXT" => GetMessage("IBLIST_A_ADMIN_VIEW"),
						"TITLE" => GetMessage("IBLIST_A_VIEW_WF_ALT"),
						"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar)),
					);
				}
			}

			if (
				$arRes["WF_STATUS_ID"] > 1
				&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit")
				&& CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit_any_wf_status")
			)
			{
				$arActions[] = array(
					"ICON" => "edit_orig",
					"TEXT" => GetMessage("IBLIST_A_ORIG_ED"),
					"TITLE" => GetMessage("IBLIST_A_ORIG_ED_TITLE"),
					"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $arRes_orig['ID'], array(
						'WF' => 'Y',
						'find_section_section' => intval($find_section_section)
					)))
				);
			}

			if (
				CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_delete")
				&& (2 <= $STATUS_PERMISSION)
			)
			{
				if (!CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit_any_wf_status"))
					$intMinPerm = CIBlockElement::WF_GetStatusPermission($arRes["WF_STATUS_ID"], $f_ID);
				if (2 <= $intMinPerm)
				{
					$arActions[] = array("SEPARATOR"=>true);
					$arActions[] = array(
						"ICON" => "delete",
						"TEXT" => GetMessage('MAIN_DELETE'),
						"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
						"ACTION" => "if(confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_TYPE.$arRes_orig['ID'], "delete", $sThisSectionUrl),
					);
				}
			}
		}
	}
	elseif($bBizproc)
	{
		$bWritePermission = call_user_func(array(ENTITY, "CanUserOperateDocument"),
			CBPCanUserOperateOperation::WriteDocument,
			$USER->GetID(),
			$f_ID,
			array("IBlockId" => $IBLOCK_ID, "UserGroups" => $USER->GetUserGroupArray(), "AllUserGroups" => $arRes["CURENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates)
		);
		$bStartWorkflowPermission = call_user_func(array(ENTITY, "CanUserOperateDocument"),
			CBPCanUserOperateOperation::StartWorkflow,
			$USER->GetID(),
			$f_ID,
			array("IBlockId" => $IBLOCK_ID, "UserGroups" => $USER->GetUserGroupArray(), "AllUserGroups" => $arRes["CURENT_USER_GROUPS"], "DocumentStates" => $arDocumentStates)
		);

		if ($bStartWorkflowPermission)
		{
			$arActions[] = array(
				"ICON" => "",
				"TEXT" => GetMessage("IBLIST_BP_START"),
				"ACTION" => $lAdmin->ActionRedirect('iblock_start_bizproc.php?document_id='.$f_ID.'&document_type=iblock_'.$IBLOCK_ID.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array("mode", "table_id"))).''),
			);
		}

		if ($lockStatus == "red")
		{
			if (CBPDocument::IsAdmin())
			{
				$arActions[] = Array(
					"ICON" => "unlock",
					"TEXT" => GetMessage("IBLIST_A_UNLOCK"),
					"TITLE" => GetMessage("IBLIST_A_UNLOCK_ALT"),
					"ACTION" => "if(confirm('".GetMessageJS("IBLIST_A_UNLOCK_CONFIRM")."')) ".$lAdmin->ActionDoGroup($f_TYPE.$f_ID, "unlock", $sThisSectionUrl),
				);
			}
		}
		elseif ($bWritePermission)
		{
			$arActions[] = array(
				"ICON" => "edit",
				"TEXT" => GetMessage("IBLOCK_CHANGE"),
				"DEFAULT" => true,
				"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $f_ID, array(
					'WF' => 'Y',
					'find_section_section' => intval($find_section_section)
				)))
			);
			$arActions[] = $arActive;
			$arActions[] = array('SEPARATOR' => 'Y');
			$arActions[] = $clearCounter;
			$arActions[] = array('SEPARATOR' => 'Y');

			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("IBLIST_A_COPY_ELEMENT"),
				"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $f_ID, array(
					'WF' => 'Y',
					'find_section_section' => intval($find_section_section),
					'action' => 'copy'
				)))
			);

			if(!defined("CATALOG_PRODUCT"))
			{
				$arActions[] = array(
					"ICON" => "history",
					"TEXT" => GetMessage("IBLIST_A_HIST"),
					"TITLE" => GetMessage("IBLIST_A_HISTORY_ALT"),
					"ACTION" => $lAdmin->ActionRedirect('iblock_bizproc_history.php?document_id='.$f_ID.'&back_url='.urlencode($APPLICATION->GetCurPageParam("", array())).''),
				);
			}

			$arActions[] = array("SEPARATOR"=>true);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage('MAIN_DELETE'),
				"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
				"ACTION" => "if(confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_TYPE.$f_ID, "delete", $sThisSectionUrl),
			);
		}
	}
	else
	{
		if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit"))
		{
			$arActions[] = array(
				"ICON" => "edit",
				"DEFAULT" => true,
				"TEXT" => GetMessage("IBLOCK_CHANGE"),
				"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $arRes_orig['ID'], array(
					'WF' => 'Y',
					'find_section_section' => intval($find_section_section)
				)))
			);
			$arActions[] = $arActive;
			$arActions[] = array('SEPARATOR' => 'Y');
			$arActions[] = $clearCounter;
			$arActions[] = array('SEPARATOR' => 'Y');
		}

		if ($boolIBlockElementAdd && CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_edit"))
		{
			$arActions[] = array(
				"ICON" => "copy",
				"TEXT" => GetMessage("IBLIST_A_COPY_ELEMENT"),
				"ACTION" => $lAdmin->ActionRedirect(CIBlock::GetAdminElementEditLink($IBLOCK_ID, $arRes_orig['ID'], array(
					'WF' => 'Y',
					'find_section_section' => intval($find_section_section),
					'action' => 'copy'
				)))
			);
		}

		if(strlen($f_DETAIL_PAGE_URL) > 0)
		{
			$tmpVar = CIBlock::ReplaceDetailUrl($arRes["DETAIL_PAGE_URL"], $arRes_orig, true, "E");
			$arActions[] = array(
				"ICON" => "view",
				"TEXT" => GetMessage("IBLIST_A_ADMIN_VIEW"),
				"TITLE" => GetMessage("IBLIST_A_VIEW_WF_ALT"),
				"ACTION" => $lAdmin->ActionRedirect(htmlspecialcharsbx($tmpVar)),
			);
		}

		if (CIBlockElementRights::UserHasRightTo($IBLOCK_ID, $f_ID, "element_delete"))
		{
			if (!empty($arActions))
				$arActions[] = array("SEPARATOR"=>true);
			$arActions[] = array(
				"ICON" => "delete",
				"TEXT" => GetMessage('MAIN_DELETE'),
				"TITLE" => GetMessage("IBLOCK_DELETE_ALT"),
				"ACTION" => "if(confirm('".GetMessageJS('IBLOCK_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_TYPE.$arRes_orig['ID'], "delete", $sThisSectionUrl),
			);
		}
	}
	$row->AddActions($arActions);
}
if ($bCatalog)
{
	if ($strUseStoreControl == "Y" && in_array("CATALOG_BAR_CODE", $arSelectedFields) && !empty($arElemID))
	{
		$productsWithBarCode = array();
		$rsProducts = Catalog\ProductTable::getList(array(
			'select' => array('ID', 'BARCODE_MULTI'),
			'filter' => array('@ID' => $arElemID)
		));
		while ($product = $rsProducts->fetch())
		{
			if (isset($arRows['E'.$product["ID"]]))
			{
				if ($product["BARCODE_MULTI"] == "Y")
					$arRows['E'.$product["ID"]]->arRes["CATALOG_BAR_CODE"] = GetMessage("IBLIST_A_CATALOG_BAR_CODE_MULTI");
				else
					$productsWithBarCode[] = $product["ID"];
			}
		}
		if (!empty($productsWithBarCode))
		{
			$arBarCodes = array();
			$rsProducts = CCatalogStoreBarCode::getList(array(), array(
				"PRODUCT_ID" => $productsWithBarCode,
			));
			while ($product = $rsProducts->Fetch())
			{
				$arBarCodes[$product["PRODUCT_ID"]][] = htmlspecialcharsEx($product["BARCODE"]);
			}
			foreach($arBarCodes as $productId => $barcode)
			{
				if (isset($arRows['E'.$productId]))
				{
					$arRows['E'.$productId]->arRes["CATALOG_BAR_CODE"] = implode(', ', $barcode);
				}
			}
		}
	}

	if (!empty($arProductIDs))
	{
		$arProductKeys = array_keys($arProductIDs);
		$existOffers = CCatalogSKU::getExistOffers($arProductKeys, $IBLOCK_ID);
		foreach ($arProductKeys as &$intProductID)
		{
			if (!empty($existOffers[$intProductID]))
			{
				if (!$showCatalogWithOffers)
				{
					$arRows['E'.$intProductID]->AddViewField('CATALOG_QUANTITY', ' ');
					$arRows['E'.$intProductID]->AddViewField('CATALOG_QUANTITY_TRACE', ' ');
					$arRows['E'.$intProductID]->AddViewField('CATALOG_WEIGHT', ' ');
					$arRows['E'.$intProductID]->AddViewField('CATALOG_VAT_INCLUDED', ' ');
					$arRows['E'.$intProductID]->AddViewField('CATALOG_PURCHASING_PRICE', ' ');
					$arRows['E'.$intProductID]->AddViewField('CATALOG_MEASURE_RATIO', ' ');
					$arRows['E'.$intProductID]->AddViewField('CATALOG_MEASURE', ' ');
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_QUANTITY']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_QUANTITY']['edit']);
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_QUANTITY_TRACE']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_QUANTITY_TRACE']['edit']);
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_WEIGHT']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_WEIGHT']['edit']);
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_VAT_INCLUDED']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_VAT_INCLUDED']['edit']);
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_PURCHASING_PRICE']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_PURCHASING_PRICE']['edit']);
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_MEASURE_RATIO']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_MEASURE_RATIO']['edit']);
					if (isset($arRows['E'.$intProductID]->aFields['CATALOG_MEASURE']['edit']))
						unset($arRows['E'.$intProductID]->aFields['CATALOG_MEASURE']['edit']);
					if (isset($arCatGroup) && !empty($arCatGroup))
					{
						foreach($arCatGroup as $CatGroup)
						{
							if (isset($arVisibleColumnsMap["CATALOG_GROUP_".$CatGroup["ID"]]))
							{
								if (isset($arRows['E'.$intProductID]->aFields["CATALOG_GROUP_".$CatGroup["ID"]]['edit']))
									unset($arRows['E'.$intProductID]->aFields["CATALOG_GROUP_".$CatGroup["ID"]]['edit']);
								$arRows['E'.$intProductID]->AddViewField("CATALOG_GROUP_".$CatGroup["ID"], ' ');
							}
						}
					}
					$arRows['E'.$intProductID]->arRes["CATALOG_BAR_CODE"] = ' ';
				}
			}
		}
		unset($intProductID, $existOffers);
	}
	foreach ($arElemID as &$intOneElemID)
	{
		$strProductType = '';
		if (isset($productTypeList[$arRows['E'.$intOneElemID]->arRes['CATALOG_TYPE']]))
			$strProductType = $productTypeList[$arRows['E'.$intOneElemID]->arRes['CATALOG_TYPE']];
		if ($arRows['E'.$intOneElemID]->arRes['CATALOG_BUNDLE'] == 'Y' && $boolCatalogSet)
			$strProductType .= ('' != $strProductType ? ', ' : '').GetMessage('IBLIST_A_CATALOG_TYPE_MESS_GROUP');
		$arRows['E'.$intOneElemID]->AddViewField('CATALOG_TYPE', $strProductType);
	}
	if (isset($intOneElemID))
		unset($intOneElemID);

	if (isset($arVisibleColumnsMap['CATALOG_MEASURE']) && !empty($arElemID))
	{
		foreach ($arElemID as &$intOneElemID)
		{
			if ($showCatalogWithOffers || $arRows['E'.$intOneElemID]->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SKU)
			{
				if (isset($arCatalogRights[$intOneElemID]) && $arCatalogRights[$intOneElemID] && $arRows['E'.$intOneElemID]->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SET)
				{
					$arRows['E'.$intOneElemID]->AddSelectField('CATALOG_MEASURE', $measureList);
				}
				else
				{
					$measureTitle = (isset($measureList[$arRows['E'.$intOneElemID]->arRes['CATALOG_MEASURE']])
						? $measureList[$arRows['E'.$intOneElemID]->arRes['CATALOG_MEASURE']]
						: $measureList[0]
					);
					$arRows['E'.$intOneElemID]->AddViewField('CATALOG_MEASURE', $measureTitle);
					unset($measureTitle);
				}
			}
			else
			{
				$arRows['E'.$intOneElemID]->AddViewField('CATALOG_MEASURE', ' ');
			}
		}
		unset($intOneElemID);
	}
	if (isset($arVisibleColumnsMap['CATALOG_MEASURE_RATIO']) && !empty($arElemID))
	{
		$arRatioList = array();
		$iterator = Catalog\MeasureRatioTable::getList(array(
			'select' => array('ID', 'PRODUCT_ID', 'RATIO'),
			'filter' => array('@PRODUCT_ID' => $arElemID, '=IS_DEFAULT' => 'Y')
		));
		while ($row = $iterator->fetch())
		{
			$id = (int)$row['PRODUCT_ID'];
			$arRatioList[$id] = $row['RATIO'];
			unset($id);
		}
		unset($row, $iterator);
		if (!empty($arRatioList))
		{
			foreach ($arElemID as &$intOneElemID)
			{
				$arRows['E'.$intOneElemID]->arRes['CATALOG_MEASURE_RATIO'] = (isset($arRatioList[$intOneElemID]) ? $arRatioList[$intOneElemID] : ' ');
				if ($showCatalogWithOffers || $arRows['E'.$intOneElemID]->arRes['CATALOG_TYPE'] != Catalog\ProductTable::TYPE_SKU)
				{
					if (isset($arCatalogRights[$intOneElemID]) && $arCatalogRights[$intOneElemID])
						$arRows['E'.$intOneElemID]->AddInputField('CATALOG_MEASURE_RATIO');
					else
						$arRows['E'.$intOneElemID]->AddInputField('CATALOG_MEASURE_RATIO', false);
				}
				else
				{
					$arRows['E'.$intOneElemID]->AddViewField('CATALOG_MEASURE_RATIO', ' ');
				}
			}
			unset($intOneElemID);
		}
	}

	if(!empty($arElemID))
	{
		$subscriptions = Catalog\SubscribeTable::getList(array(
			'select' => array('ITEM_ID', 'CNT'),
			'filter' => array('@ITEM_ID' => $arElemID, 'DATE_TO' => null),
			'runtime' => array(new Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)'))
		));
		while($subscribe = $subscriptions->fetch())
		{
			if(isset($arRows['E'.$subscribe['ITEM_ID']]))
			{
				$arRows['E'.$subscribe['ITEM_ID']]->addField('SUBSCRIPTIONS', $subscribe['CNT']);
			}
		}
	}
}

// List footer
$lAdmin->AddFooter(
	array(
		array("title"=>GetMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()),
		array("counter"=>true, "title"=>GetMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"),
	)
);


// Action bar
if(true)
{
	$arActions = array();
	$arParams = array();
	if ($mainEntityEdit)
	{
		$arActions = array(
			"delete" => GetMessage("MAIN_ADMIN_LIST_DELETE"),
			"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
			"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
			'clear_counter' => strtolower(GetMessage('IBLIST_A_CLEAR_COUNTER'))
		);

		if ($arIBTYPE["SECTIONS"] == "Y")
		{
			$sections = '<div id="section_to_move" style="display:none"><select name="section_to_move">';
			$sections .= '<option value="">'.GetMessage("MAIN_NO").'</option>';
			$sections .= '<option value="0">'.GetMessage("IBLOCK_UPPER_LEVEL").'</option>';
			$rsSections = CIBlockSection::GetTreeList(Array("IBLOCK_ID" => $IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
			while ($ar = $rsSections->GetNext())
			{
				$sections .= '<option value="'.$ar["ID"].'">'.str_repeat(" . ", $ar["DEPTH_LEVEL"]).$ar["NAME"].'</option>';
			}
			$sections .= '</select></div>';

			$arActions["section"] = GetMessage("IBLIST_A_MOVE_TO_SECTION");
			$arActions["add_section"] = GetMessage("IBLIST_A_ADD_TO_SECTION");
			$arActions["section_chooser"] = array("type" => "html", "value" => $sections);
			$arParams["select_onchange"] = "BX('section_to_move').style.display = (this.value == 'section' || this.value == 'add_section'? 'block':'none');";
		}

		if ($bCatalog && $USER->CanDoOperation('catalog_price') && $mainEntityEditPrice)
		{
			$arActions["change_price"] = array(
				"action" => "CreateDialogChPrice()",
				"value" => "change_price",
				"name" => GetMessage("IBLOCK_CHANGE_PRICE")
			);
		}
	}

	if($bWorkFlow)
	{
		$arActions["unlock"] = GetMessage("IBLIST_A_UNLOCK_ACTION");
		$arActions["lock"] = GetMessage("IBLIST_A_LOCK_ACTION");

		$statuses = '<div id="wf_status_id" style="display:none">'.SelectBox("wf_status_id", CWorkflowStatus::GetDropDownList("N", "desc")).'</div>';
		$arActions["wf_status"] = GetMessage("IBLIST_A_WF_STATUS_CHANGE");
		$arActions["wf_status_chooser"] = array("type" => "html", "value" => $statuses);

		$arParams["select_onchange"] .= "BX('wf_status_id').style.display = (this.value == 'wf_status'? 'block':'none');";

	}
	elseif($bBizproc)
	{
		$arActions["unlock"] = GetMessage("IBLIST_A_UNLOCK_ACTION");
	}

	$lAdmin->AddGroupActionTable($arActions, $arParams);
}

if($bCatalog && $USER->CanDoOperation('catalog_price'))
{
	$lAdmin->BeginEpilogContent();
	?>
	<div>
		<input type="hidden" name="chprice_value_changing_price">
		<input type="hidden" name="chprice_units">
		<input type="hidden" name="chprice_id_price_type">
		<input type="hidden" name="chprice_format_result">
		<input type="hidden" name="chprice_result_mask">
		<input type="hidden" name="chprice_initial_price_type">
		<input type="hidden" name="chprice_difference_value">
	</div>

	<?

	/** Creation window of common price changer */
	CJSCore::Init(array('window'));
	?>

	<script>
		/**
		 * @func CreateDialogChPrice - creation of common changing price dialog
		 */
		function CreateDialogChPrice()
		{
			var paramsWindowChanger =
			{
				title: "<?=GetMessage("IBLOCK_CHANGING_PRICE")?>",
				content_url: "/bitrix/tools/catalog/iblock_catalog_change_price.php?lang=" + "<?=LANGUAGE_ID?>" + "&bxpublic=Y",
				content_post: "<?=bitrix_sessid_get()?>" + "&sTableID=<?=$sTableID?>",
				width: 800,
				height: 415,
				resizable: false,
				buttons: [
					{
						title: top.BX.message('JS_CORE_WINDOW_SAVE'),
						id: 'savebtn',
						name: 'savebtn',
						className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
					},
					top.BX.CAdminDialog.btnCancel
				]
			};
			var priceChanger = (new top.BX.CAdminDialog(paramsWindowChanger));
			priceChanger.Show();
		}
	</script>

	<?
	$lAdmin->EndEpilogContent();
}


$sLastFolder = '';
$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>0));
$chain = $lAdmin->CreateChain();

if(!defined("CATALOG_PRODUCT"))
{
	$chain->AddItem(array(
		"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
		"LINK" => htmlspecialcharsbx($sSectionUrl),
		"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
	));
}

if($find_section_section > 0)
{
	$sLastFolder = $sSectionUrl;

	$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'));
	while($ar_nav = $nav->GetNext())
	{
		$sSectionUrl = CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>$ar_nav["ID"]));
		$chain->AddItem(array(
			"TEXT" => $ar_nav["NAME"],
			"LINK" => htmlspecialcharsbx($sSectionUrl),
			"ONCLICK" => $lAdmin->ActionAjaxReload($sSectionUrl).';return false;',
		));

		if($ar_nav["ID"] != $find_section_section)
			$sLastFolder = $sSectionUrl;
	}
}

$lAdmin->ShowChain($chain);

// toolbar
$boolBtnNew = false;
$aContext = array();
if ($boolIBlockElementAdd)
{
	$boolBtnNew = true;
	if (!empty($arCatalog))
	{
		CCatalogAdminTools::setProductFormParams();
		$arCatalogBtns = CCatalogAdminTools::getIBlockElementMenu(
			$IBLOCK_ID,
			$arCatalog,
			array(
				'IBLOCK_SECTION_ID' => $find_section_section,
				'find_section_section' => $find_section_section,
				'from' => 'iblock_list_admin'
			)
		);
		if (!empty($arCatalogBtns))
			$aContext = $arCatalogBtns;
	}
	if (empty($aContext))
	{
		$aContext[] = array(
			"TEXT" => htmlspecialcharsbx($arIBlock["ELEMENT_ADD"]),
			"ICON" => "btn_new",
			"LINK" => CIBlock::GetAdminElementEditLink($IBLOCK_ID, 0, array(
				'IBLOCK_SECTION_ID'=>$find_section_section,
				'find_section_section'=>$find_section_section,
				'from' => 'iblock_list_admin'
			)),
		);
	}
}

if(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $find_section_section, "section_section_bind") && $arIBTYPE["SECTIONS"]!="N")
{
	$aContext[] = array(
		"TEXT" => htmlspecialcharsbx($arIBlock["SECTION_ADD"]),
		"ICON" => ($boolBtnNew ? "" : "btn_new"),
		"LINK" => CIBlock::GetAdminSectionEditLink($IBLOCK_ID, 0, array(
			'IBLOCK_SECTION_ID'=>$find_section_section,
			'find_section_section'=>$find_section_section,
			'from' => 'iblock_list_admin',
		)),
	);
}

if(strlen($sLastFolder)>0)
{
	$aContext[] = Array(
		"TEXT" => GetMessage("IBLIST_A_UP"),
		"LINK" => $sLastFolder,
		"TITLE" => GetMessage("IBLIST_A_UP_TITLE"),
	);
}

if($bBizproc && IsModuleInstalled("bizprocdesigner"))
{
	$bCanDoIt = CBPDocument::CanUserOperateDocumentType(
		CBPCanUserOperateOperation::CreateWorkflow,
		$USER->GetID(),
		array(MODULE_ID, ENTITY, DOCUMENT_TYPE)
		);

	if($bCanDoIt)
	{
		$aContext[] = array(
			"TEXT" => GetMessage("IBLIST_BTN_BP"),
			"ICON" => "btn_bp",
			"LINK" => 'iblock_bizproc_workflow_admin.php?document_type=iblock_'.$IBLOCK_ID.'&lang='.LANGUAGE_ID.'&back_url_list='.urlencode($REQUEST_URI),
		);
	}
}

$lAdmin->AddAdminContextMenu($aContext);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle($arIBlock["NAME"]);
Main\Page\Asset::getInstance()->addJs('/bitrix/js/iblock/iblock_edit.js');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

//We need javascript not in excel mode
if((!$bExcel) && $bCatalog && $bCurrency)
{
	?><script type="text/javascript">
		top.arCatalogShowedGroups = new Array();
		top.arExtra = new Array();
		top.arCatalogGroups = new Array();
		top.BaseIndex = "";
	<?
	if (is_array($arCatGroup) && !empty($arCatGroup))
	{
		$i = 0;
		$j = 0;
		foreach($arCatGroup as &$CatalogGroups)
		{
			if (in_array("CATALOG_GROUP_".$CatalogGroups["ID"], $arSelectedFields))
			{
				echo "top.arCatalogShowedGroups[".$i."]=".$CatalogGroups["ID"].";\n";
				$i++;
			}
			if ($CatalogGroups["BASE"]!="Y")
			{
				echo "top.arCatalogGroups[".$j."]=".$CatalogGroups["ID"].";\n";
				$j++;
			}
			else
			{
				echo "top.BaseIndex=".$CatalogGroups["ID"].";\n";
			}
		}
	}
	if (is_array($arCatExtra) && !empty($arCatExtra))
	{
		$i = 0;
		foreach($arCatExtra as &$CatExtra)
		{
			echo "top.arExtra[".$CatExtra["ID"]."]=".$CatExtra["PERCENTAGE"].";\n";
			$i++;
		}
	}
		?>
		top.ChangeBasePrice = function(id)
		{
			for(var i = 0, cnt = top.arCatalogShowedGroups.length; i < cnt; i++)
			{
				var pr = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.arCatalogShowedGroups[i]+"]");
				if(pr.disabled)
				{
					var price = top.document.getElementById("CATALOG_PRICE["+id+"]"+"["+top.BaseIndex+"]").value;
					if(price > 0)
					{
						var extraId = top.document.getElementById("CATALOG_EXTRA["+id+"]"+"["+top.arCatalogShowedGroups[i]+"]").value;
						var esum = parseFloat(price) * (1 + top.arExtra[extraId] / 100);
						var eps = 1.00/Math.pow(10, 6);
						esum = Math.round((esum+eps)*100)/100;
					}
					else
						var esum = "";

					pr.value = esum;
				}
			}
		}

		top.ChangeBaseCurrency = function(id)
		{
			var currency = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.BaseIndex+"]");
			for(var i = 0, cnt = top.arCatalogShowedGroups.length; i < cnt; i++)
			{
				var pr = top.document.getElementById("CATALOG_CURRENCY["+id+"]["+top.arCatalogShowedGroups[i]+"]");
				if(pr.disabled)
				{
					pr.selectedIndex = currency.selectedIndex;
				}
			}
		}
	</script>
	<?
}
CJSCore::Init('file_input');
?>
<form method="GET" name="find_form" id="find_form" action="<?echo $APPLICATION->GetCurPage()?>">
<?
$arFindFields = Array();
$arFindFields["IBLIST_A_PARENT"] = GetMessage("IBLIST_A_PARENT");
$arFindFields["IBLIST_A_ID"] = GetMessage("IBLIST_A_ID");
$arFindFields["IBLIST_A_TS"] = GetMessage("IBLIST_A_TS");
$arFindFields["IBLIST_A_CODE"] = GetMessage("IBLIST_A_CODE");
$arFindFields["IBLIST_A_EXTCODE"] = GetMessage("IBLIST_A_EXTCODE");
$arFindFields["IBLIST_A_F_MODIFIED_BY"] = GetMessage("IBLIST_A_F_MODIFIED_BY");
$arFindFields["IBLIST_A_F_CREATED_WHEN"] = GetMessage("IBLIST_A_F_CREATED_WHEN");
$arFindFields["IBLIST_A_F_CREATED_BY"] = GetMessage("IBLIST_A_F_CREATED_BY");
if($bWorkFlow)
	$arFindFields["IBLIST_A_F_STATUS"] = GetMessage("IBLIST_A_F_STATUS");
$arFindFields["IBLIST_A_F_ACTIVE_FROM"] = GetMessage("IBLIST_A_DATE_ACTIVE_FROM");
$arFindFields["IBLIST_A_F_ACTIVE_TO"] = GetMessage("IBLIST_A_DATE_ACTIVE_TO");
$arFindFields["IBLIST_A_ACT"] = GetMessage("IBLIST_A_ACTIVE");
$arFindFields["IBLIST_A_F_DESC"] = GetMessage("IBLIST_A_F_DESC");
$arFindFields["IBLIST_A_TAGS"] = GetMessage("IBLIST_A_TAGS");

if ($bCatalog)
{
	$arFindFields["CATALOG_TYPE"] = GetMessage("IBLIST_A_CATALOG_TYPE");
	$arFindFields["CATALOG_BUNDLE"] = GetMessage("IBLIST_A_CATALOG_BUNDLE");
	$arFindFields["CATALOG_AVAILABLE"] = GetMessage("IBLIST_A_CATALOG_AVAILABLE");
}

foreach($arProps as $arProp)
	if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F")
		$arFindFields["IBLIST_A_PROP_".$arProp["ID"]] = $arProp["NAME"];

if ($boolSKU && $boolSKUFiltrable)
{
	foreach($arSKUProps as $arProp)
	{
		$arFindFields["IBLIST_A_SUB_PROP_".$arProp["ID"]] = ('' != $strSKUName ? $strSKUName.' - ' : '').$arProp["NAME"];
	}
}

$filterUrl = $APPLICATION->GetCurPageParam(); //$APPLICATION->GetCurPage().'?type='.urlencode($type).'&IBLOCK_ID='.urlencode($IBLOCK_ID).'&lang='.urlencode(LANG);
$oFilter = new CAdminFilter($sTableID."_filter", $arFindFields, array("table_id" => $sTableID, "url" => $filterUrl));
?><script type="text/javascript">
var arClearHiddenFields = [];

function clearFilterFields()
{
	var index;

	for (index = 0; index < arClearHiddenFields.length; index++)
	{
		if (window[arClearHiddenFields[index]] !== undefined)
		{
			if ('ClearForm' in window[arClearHiddenFields[index]])
			{
				window[arClearHiddenFields[index]].ClearForm();
			}
		}
	}
}

BX.ready(function(){
	BX.addCustomEvent(window, 'onBeforeAdminFilterClear', clearFilterFields);
});

try {
	var DecimalSeparator = Number("1.2").toLocaleString().charCodeAt(1);
	document.cookie = '<?echo $dsc_cookie_name?>='+DecimalSeparator+'; path=/;';
}
catch (e)
{
}
</script><?
$oFilter->Begin();
?>
	<tr>
		<td><b><?echo GetMessage("IBLIST_A_NAME")?></b></td>
		<td><input type="text" name="find_name" value="<?echo htmlspecialcharsex($find_name)?>" size="47">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_F_SECTION")?></td>
		<td>
			<select name="find_section_section" >
				<option value="-1"<?if($find_section_section < 0) echo " selected"?>><?echo GetMessage("IBLOCK_ALL")?></option>
				<option value="0"<?if($find_section_section == 0) echo " selected"?>><?echo GetMessage("IBLOCK_UPPER_LEVEL")?></option>
				<?
				$bsections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));
				while($arSection = $bsections->GetNext()):
					?><option value="<?echo $arSection["ID"]?>"<?if($arSection["ID"]==$find_section_section)echo " selected"?>><?echo str_repeat("&nbsp;.&nbsp;", $arSection["DEPTH_LEVEL"])?><?echo $arSection["NAME"]?></option><?
				endwhile;
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_F_FROM_TO_ID")?></td>
		<td nowrap>
			<input type="text" name="find_id_1" size="10" value="<?echo htmlspecialcharsex($find_id_1)?>">
			...
			<input type="text" name="find_id_2" size="10" value="<?echo htmlspecialcharsex($find_id_2)?>">
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLOCK_FIELD_TIMESTAMP_X")?>:</td>
		<td><?echo CalendarPeriod("find_timestamp_1", htmlspecialcharsbx($find_timestamp_1), "find_timestamp_2", htmlspecialcharsbx($find_timestamp_2), "find_form", "Y")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_CODE")?>:</td>
		<td><input type="text" name="find_code" size="47" value="<?echo htmlspecialcharsbx($find_code)?>"></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_EXTCODE")?>:</td>
		<td><input type="text" name="find_external_id" size="47" value="<?echo htmlspecialcharsbx($find_external_id)?>"></td>
	</tr>
	<tr>
		<td><?=GetMessage("IBLIST_A_F_MODIFIED_BY")?>:</td>
		<td>
			<?echo FindUserID(
				"find_modified_user_id",
				$find_modified_user_id,
				"",
				"find_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLIST_A_DATE_CREATE")?>:</td>
		<td><?echo CalendarPeriod("find_created_from", htmlspecialcharsex($find_created_from), "find_created_to", htmlspecialcharsex($find_created_to), "find_element_form", "Y")?></td>
	</tr>

	<tr>
		<td><?echo GetMessage("IBLIST_A_F_CREATED_BY")?>:</td>
		<td>
			<?echo FindUserID(
				"find_created_user_id",
				$find_created_user_id,
				"",
				"find_form",
				"5",
				"",
				" ... ",
				"",
				""
			);?>
		</td>
	</tr>

	<?if($bWorkFlow):?>
	<tr>
		<td><?=GetMessage("IBLIST_A_STATUS")?>:</td>
		<td><input type="text" name="find_status_id" value="<?echo htmlspecialcharsex($find_status_id)?>" size="3">
		<select name="find_status">
		<option value=""><?=GetMessage("IBLOCK_VALUE_ANY")?></option>
		<?
		$rs = CWorkflowStatus::GetDropDownList("Y");
		while($arRs = $rs->GetNext())
		{
			?><option value="<?=$arRs["REFERENCE_ID"]?>"<?if($find_status == $arRs["~REFERENCE_ID"])echo " selected"?>><?=$arRs["REFERENCE"]?></option><?
		}
		?>
		</select></td>
	</tr>
	<?endif?>
	<tr>
		<td><?echo GetMessage("IBLIST_A_DATE_ACTIVE_FROM")?>:</td>
		<td><?echo CalendarPeriod("find_date_active_from_from", htmlspecialcharsex($find_date_active_from_from), "find_date_active_from_to", htmlspecialcharsex($find_date_active_from_to), "find_form")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_DATE_ACTIVE_TO")?>:</td>
		<td><?echo CalendarPeriod("find_date_active_to_from", htmlspecialcharsex($find_date_active_to_from), "find_date_active_to_to", htmlspecialcharsex($find_date_active_to_to), "find_form")?></td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_ACTIVE")?>:</td>
		<td>
			<select name="find_active">
				<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_VALUE_ANY'))?></option>
				<option value="Y"<?if($find_active=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_YES"))?></option>
				<option value="N"<?if($find_active=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_NO"))?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?echo GetMessage("IBLIST_A_F_DESC")?>:</td>
		<td><input type="text" name="find_intext" value="<?echo htmlspecialcharsex($find_intext)?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?></td>
	</tr>
	<tr>
		<td><?=GetMessage("IBLIST_A_TAGS")?>:</td>
		<td>
			<?
			if ($bSearch):
				echo InputTags("find_tags", $find_tags, $arIBlock["SITE_ID"]);
			else:
			?>
				<input type="text" name="find_tags" value="<?echo htmlspecialcharsex($find_tags)?>" size="30">
			<?endif?>
		</td>
	</tr>
	<?
	if ($bCatalog)
	{
		?><tr>
		<td><?=GetMessage("IBLIST_A_CATALOG_TYPE"); ?>:</td>
		<td>
			<select name="find_el_catalog_type[]" multiple>
				<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_VALUE_ANY'))?></option>
				<?
				$catalogTypes = (!empty($find_el_catalog_type) ? $find_el_catalog_type : array());
				foreach ($productTypeList as $productType => $productTypeName)
				{
					?><option value="<? echo $productType; ?>"<? echo (in_array($productType, $catalogTypes) ? ' selected' : ''); ?>><? echo htmlspecialcharsex($productTypeName); ?></option><?
				}
				unset($productType, $productTypeName, $catalogTypes);
				?>
			</select>
		</td>
		</tr>
		<tr>
			<td><?echo GetMessage("IBLIST_A_CATALOG_BUNDLE")?>:</td>
			<td>
				<select name="find_el_catalog_bundle">
					<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_VALUE_ANY'))?></option>
					<option value="Y"<?if($find_el_catalog_bundle=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_YES"))?></option>
					<option value="N"<?if($find_el_catalog_bundle=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_NO"))?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td><?echo GetMessage("IBLIST_A_CATALOG_AVAILABLE")?>:</td>
			<td>
				<select name="find_el_catalog_available">
					<option value=""><?=htmlspecialcharsex(GetMessage('IBLOCK_VALUE_ANY'))?></option>
					<option value="Y"<?if($find_el_catalog_available=="Y")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_YES"))?></option>
					<option value="N"<?if($find_el_catalog_available=="N")echo " selected"?>><?=htmlspecialcharsex(GetMessage("IBLOCK_NO"))?></option>
				</select>
			</td>
		</tr>
		<?
	}

function _ShowGroupPropertyFieldList($name, $property_fields, $values)
{
	if (!is_array($values))
		$values = array();

	$res = "";
	$result = "";
	$bWas = false;
	$sections = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$property_fields["LINK_IBLOCK_ID"]), array("ID", "NAME", "DEPTH_LEVEL"));
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
	$result .= '<select name="'.$name.'[]" size="5" multiple>';
	$result .= '<option value=""'.(!$bWas?' selected':'').'>'.GetMessage("IBLIST_A_PROP_NOT_SET").'</option>';
	$result .= $res;
	$result .= '</select>';
	return $result;
}

foreach($arProps as $arProp):
	if($arProp["FILTRABLE"]=="Y" && $arProp["PROPERTY_TYPE"]!="F"):
?>
<tr>
	<td><?=$arProp["NAME"]?>:</td>
	<td>
		<?if(array_key_exists("GetAdminFilterHTML", $arProp["PROPERTY_USER_TYPE"])):
			echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
				$arProp,
				array(
					"VALUE" => "find_el_property_".$arProp["ID"],
					"TABLE_ID" => $sTableID,
				),
			));
		elseif($arProp["PROPERTY_TYPE"]=='S'):?>
			<input type="text" name="find_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"find_el_property_".$arProp["ID"]})?>" size="30">&nbsp;<?=ShowFilterLogicHelp()?>
		<?elseif($arProp["PROPERTY_TYPE"]=='N' || $arProp["PROPERTY_TYPE"]=='E'):?>
			<input type="text" name="find_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"find_el_property_".$arProp["ID"]})?>" size="30">
		<?elseif($arProp["PROPERTY_TYPE"]=='L'):?>
			<select name="find_el_property_<?=$arProp["ID"]?>">
				<option value=""><?echo GetMessage("IBLOCK_VALUE_ANY")?></option>
				<option value="NOT_REF"<?if(${"find_el_property_".$arProp["ID"]} == "NOT_REF")echo " selected"?>><?echo GetMessage("IBLIST_A_PROP_NOT_SET")?></option><?
				$dbrPEnum = CIBlockPropertyEnum::GetList(Array("SORT"=>"ASC", "VALUE"=>"ASC"), Array("PROPERTY_ID"=>$arProp["ID"]));
				while($arPEnum = $dbrPEnum->GetNext()):
				?>
					<option value="<?=$arPEnum["ID"]?>"<?if(${"find_el_property_".$arProp["ID"]} == $arPEnum["ID"])echo " selected"?>><?=$arPEnum["VALUE"]?></option>
				<?
				endwhile;
		?></select>
		<?
		elseif($arProp["PROPERTY_TYPE"]=='G'):
			echo _ShowGroupPropertyFieldList('find_el_property_'.$arProp["ID"], $arProp, ${'find_el_property_'.$arProp["ID"]});
		endif;
		?>
	</td>
</tr>
<?
	endif;
endforeach;?>
<?

if ($boolSKU && $boolSKUFiltrable)
{
	foreach($arSKUProps as $arProp)
	{
?>
<tr>
	<td><? echo ('' != $strSKUName ? $strSKUName.' - ' : ''), $arProp["NAME"]; ?>:</td>
	<td>
		<?if(!empty($arProp["PROPERTY_USER_TYPE"]) && isset($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"]))
		{
			echo call_user_func_array($arProp["PROPERTY_USER_TYPE"]["GetAdminFilterHTML"], array(
				$arProp,
				array(
					"VALUE" => "find_sub_el_property_".$arProp["ID"],
					"TABLE_ID" => $sTableID,
				),
			));
		}
		elseif($arProp["PROPERTY_TYPE"]=='S')
		{
			?><input type="text" name="find_sub_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"find_sub_el_property_".$arProp["ID"]})?>" size="30">&nbsp;<?=ShowFilterLogicHelp(); ?><?
		}
		elseif($arProp["PROPERTY_TYPE"]=='N' || $arProp["PROPERTY_TYPE"]=='E')
		{
			?><input type="text" name="find_sub_el_property_<?=$arProp["ID"]?>" value="<?echo htmlspecialcharsex(${"find_sub_el_property_".$arProp["ID"]})?>" size="30"><?
		}
		elseif($arProp["PROPERTY_TYPE"]=='L')
		{
			?><select name="find_sub_el_property_<?=$arProp["ID"]?>">
				<option value=""><?echo GetMessage("IBLOCK_VALUE_ANY")?></option>
				<option value="NOT_REF"<?if(${"find_sub_el_property_".$arProp["ID"]} == "NOT_REF")echo " selected"?>><?echo GetMessage("IBLIST_NOT_SET")?></option><?
				$dbrPEnum = CIBlockPropertyEnum::GetList(array("SORT"=>"ASC", "VALUE"=>"ASC"), array("PROPERTY_ID"=>$arProp["ID"]));
				while($arPEnum = $dbrPEnum->GetNext())
				{
					?><option value="<?=$arPEnum["ID"]?>"<?if(${"find_sub_el_property_".$arProp["ID"]} == $arPEnum["ID"])echo " selected"?>><?=$arPEnum["VALUE"]?></option><?
				}
			?></select><?
		}
		elseif($arProp["PROPERTY_TYPE"]=='G')
		{
			echo _ShowGroupPropertyFieldList('find_sub_el_property_'.$arProp["ID"], $arProp, ${'find_sub_el_property_'.$arProp["ID"]});
		}
		?>
	</td>
</tr>
<?
	}
}

$oFilter->Buttons(array(
	"table_id" => $sTableID,
	"url" => $APPLICATION->GetCurPage().'?type='.$type.'&IBLOCK_ID='.$IBLOCK_ID,
	"form" => "find_form",
));
$oFilter->End();
?>
</form>
<?
$lAdmin->DisplayList();
if($bWorkFlow || $bBizproc):
	echo BeginNote();?>
	<span class="adm-lamp adm-lamp-green"></span> - <?echo GetMessage("IBLIST_A_GREEN_ALT")?><br>
	<span class="adm-lamp adm-lamp-yellow"></span> - <?echo GetMessage("IBLIST_A_YELLOW_ALT")?><br>
	<span class="adm-lamp adm-lamp-red"></span> - <?echo GetMessage("IBLIST_A_RED_ALT")?><br>
	<?echo EndNote();
endif;
if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit") && !defined("CATALOG_PRODUCT"))
{
	echo
		BeginNote(),
		GetMessage("IBLIST_A_IBLOCK_MANAGE_HINT"),
		' <a href="'.htmlspecialcharsbx('iblock_edit.php?type='.urlencode($type).'&lang='.LANGUAGE_ID.'&ID='.$IBLOCK_ID.'&admin=Y&return_url='.urlencode("iblock_list_admin.php?".$sThisSectionUrl)).'">',
		GetMessage("IBLIST_A_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");