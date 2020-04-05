<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);
/** @var CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

if(!CModule::IncludeModule('lists'))
{
	ShowError(GetMessage("CC_BLLE_MODULE_NOT_INSTALLED"));
	return;
}
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/admin_tools.php");
$APPLICATION->AddHeadScript('/bitrix/js/iblock/iblock_edit.js');

$lists_perm = CListPermissions::CheckAccess(
	$USER,
	$arParams["~IBLOCK_TYPE_ID"],
	$arParams["~IBLOCK_ID"] > 0? $arParams["~IBLOCK_ID"]: false,
	$arParams["~SOCNET_GROUP_ID"]
);
if($lists_perm < 0)
{
	switch($lists_perm)
	{
	case CListPermissions::WRONG_IBLOCK_TYPE:
		ShowError(GetMessage("CC_BLLE_WRONG_IBLOCK_TYPE"));
		return;
	case CListPermissions::WRONG_IBLOCK:
		ShowError(GetMessage("CC_BLLE_WRONG_IBLOCK"));
		return;
	case CListPermissions::LISTS_FOR_SONET_GROUP_DISABLED:
		ShowError(GetMessage("CC_BLLE_LISTS_FOR_SONET_GROUP_DISABLED"));
		return;
	default:
		ShowError(GetMessage("CC_BLLE_UNKNOWN_ERROR"));
		return;
	}
}
elseif(
	(
		$arParams["~IBLOCK_ID"] > 0
		&& $lists_perm < CListPermissions::IS_ADMIN
		&& !CIBlockRights::UserHasRightTo($arParams["~IBLOCK_ID"], $arParams["~IBLOCK_ID"], "iblock_edit")
	) || (
		$arParams["~IBLOCK_ID"] == 0
		&& $lists_perm < CListPermissions::IS_ADMIN
	)
)
{
	ShowError(GetMessage("CC_BLLE_ACCESS_DENIED"));
	return;
}

$arParams["CAN_EDIT"] =
	$lists_perm >= CListPermissions::IS_ADMIN
	|| (
		$arParams["~IBLOCK_ID"] > 0
		&& CIBlockRights::UserHasRightTo($arParams["~IBLOCK_ID"], $arParams["~IBLOCK_ID"], "iblock_edit")
	)
;

$bBizProc = CModule::IncludeModule('bizproc') && CBPRuntime::isFeatureEnabled();
$arIBlock = CIBlock::GetArrayByID(intval($arParams["~IBLOCK_ID"]));

if($arIBlock)
{
	$arResult["~IBLOCK"] = $arIBlock;
	$arResult["IBLOCK"] = htmlspecialcharsex($arIBlock);
	$arResult["IBLOCK_ID"] = $arIBlock["ID"];
}
else
{
	$arResult["IBLOCK"] = false;
	$arResult["IBLOCK_ID"] = false;
}

if(isset($arParams["SOCNET_GROUP_ID"]) && $arParams["SOCNET_GROUP_ID"] > 0)
	$arParams["SOCNET_GROUP_ID"] = intval($arParams["SOCNET_GROUP_ID"]);
else
	$arParams["SOCNET_GROUP_ID"] = "";

$arResult["GRID_ID"] = "lists_fields";
$arResult["FORM_ID"] = "lists_list_edit";

$arResult["~LISTS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array("0", $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LISTS_URL"]
);
$arResult["LISTS_URL"] = htmlspecialcharsbx($arResult["~LISTS_URL"]);

$arResult["~LIST_URL"] = CHTTP::urlAddParams(str_replace(
	array("#list_id#", "#section_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], "0", $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_URL"]
), array("list_section_id" => ""));
$arResult["LIST_URL"] = htmlspecialcharsbx($arResult["~LIST_URL"]);

$arResult["~LIST_EDIT_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_EDIT_URL"]
);
$arResult["LIST_EDIT_URL"] = htmlspecialcharsbx($arResult["~LIST_EDIT_URL"]);

$arResult["~LIST_FIELDS_URL"] = str_replace(
	array("#list_id#", "#group_id#"),
	array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
	$arParams["~LIST_FIELDS_URL"]
);
$arResult["LIST_FIELDS_URL"] = htmlspecialcharsbx($arResult["~LIST_FIELDS_URL"]);

//Assume there was no error
$bVarsFromForm = false;

//Form submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid())
{
	//When Save or Apply buttons was pressed
	if(isset($_POST["save"]) || isset($_POST["apply"]))
	{
		//Gather fields for update
		$arFields = array(
			"NAME" => $_POST["NAME"],
			"DESCRIPTION" => $_POST["DESCRIPTION"],
			"IBLOCK_TYPE_ID" => $arParams["~IBLOCK_TYPE_ID"],
			"SORT" => $_POST["SORT"],
			"WORKFLOW" => "N",
			"ELEMENTS_NAME" => $_POST["ELEMENTS_NAME"],
			"ELEMENT_NAME" => $_POST["ELEMENT_NAME"],
			"ELEMENT_ADD" => $_POST["ELEMENT_ADD"],
			"ELEMENT_EDIT" => $_POST["ELEMENT_EDIT"],
			"ELEMENT_DELETE" => $_POST["ELEMENT_DELETE"],
			"SECTIONS_NAME" => $_POST["SECTIONS_NAME"],
			"SECTION_NAME" => $_POST["SECTION_NAME"],
			"SECTION_ADD" => $_POST["SECTION_ADD"],
			"SECTION_EDIT" => $_POST["SECTION_EDIT"],
			"SECTION_DELETE" => $_POST["SECTION_DELETE"],
		);

		if($arParams["SOCNET_GROUP_ID"])
			$arFields["SOCNET_GROUP_ID"] = $arParams["SOCNET_GROUP_ID"];

		if($bBizProc)
			$arFields["BIZPROC"] = $_POST["BIZPROC"]=="Y"? "Y": "N";

		$arSites = array(SITE_ID);
		if($arIBlock)
		{
			$rsSite = CIBlock::GetSite($arIBlock["ID"]);
			while($ar = $rsSite->Fetch())
				$arSites[] = $ar["SITE_ID"];
		}
		$arFields["SITE_ID"] = $arSites;

		if(isset($_FILES["PICTURE"]))
			$arFields["PICTURE"] = $_FILES["PICTURE"];
		if(isset($_POST["PICTURE_del"]))
			$arFields["PICTURE"]["del"] = "Y";

		if(is_array($_POST["RIGHTS"]))
			$postRights = CIBlockRights::Post2Array($_POST["RIGHTS"]);
		else
			$postRights = array();

		$rights = array();
		if($arIBlock)
		{
			$RIGHTS_MODE = CIBlock::GetArrayByID($arIBlock["ID"], "RIGHTS_MODE");
			if($RIGHTS_MODE != 'E')
			{
				$arIBlockPerms = CIBlock::GetGroupPermissions($arIBlock["ID"]);
				$i = 0;
				foreach($arIBlockPerms as $group_id => $letter)
				{
					if($letter > "W")
						$rights['n_'.($i++)] = array(
							"GROUP_CODE" => "G".$group_id,
							"IS_INHERITED" => "N",
							"TASK_ID" => CIBlockRights::LetterToTask($letter),
						);
				}
			}
			else
			{
				$obIBlockRights = new CIBlockRights($arIBlock["ID"]);
				$rights = $obIBlockRights->GetRights();
			}
		}

		//For existing iblock add rights to rule
		$arFields["RIGHTS_MODE"] = "E";
		$arFields["RIGHTS"] = array();
		foreach($rights as $rightId => $right)
		{
			if(array_key_exists($rightId, $postRights))
				$arFields["RIGHTS"][$rightId] = $right;
		}
		foreach($postRights as $rightId => $right)
			$arFields["RIGHTS"][$rightId] = $right;

		//Update existing or add new
		$ob = new CIBlock;
		if($arIBlock)
		{
			$res = $ob->Update($arIBlock["ID"], $arFields);
			if($res)
				$res = $arIBlock["ID"];
		}
		else
		{
			$res = $ob->Add($arFields);
			if($res)
			{
				$obList = new CList($res);
				$obList->AddField(array(
					"SORT" => 10,
					"NAME" => GetMessage("CC_BLLE_NAME_FIELD"),
					"IS_REQUIRED" => "Y",
					"MULTIPLE" => "N",
					"TYPE" => "NAME",
					"DEFAULT_VALUE" => "",
				));
				$obList->Save();
			}
		}

		if($res)
		{
			if ((strlen($arFields["SOCNET_GROUP_ID"]) > 0) && CModule::IncludeModule('socialnetwork'))
			{
				CSocNetGroup::SetLastActivity($arFields["SOCNET_GROUP_ID"]);
			}

			$list = new CList($res);
			$list->ActualizeDocumentAdminPage(str_replace(
				array("#list_id#", "#group_id#"),
				array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
				$arParams["LIST_ELEMENT_URL"]
			));

			//Clear components cache
			$CACHE_MANAGER->ClearByTag("lists_list_".$res);
			$CACHE_MANAGER->ClearByTag("lists_list_any");
			$CACHE_MANAGER->CleanDir("menu");

			$tab_name = $arResult["FORM_ID"]."_active_tab";

			//And go to proper page
			if($arIBlock)
			{
				$url = $arResult["LIST_URL"];
				if(isset($_POST["apply"]))
					$url = $arResult["LIST_EDIT_URL"];
				LocalRedirect(CHTTP::urlAddParams(
					$url,
					array($tab_name => $_POST[$tab_name]),
					array("skip_empty" => true, "encode" => true)
				));
			}
			elseif(isset($_POST["save"]))
			{
				LocalRedirect($arResult["LISTS_URL"]);
			}
			else
			{
				LocalRedirect(CHTTP::urlAddParams(str_replace(
					array("#list_id#", "#group_id#"),
					array($res, $arParams["SOCNET_GROUP_ID"]),
					$arParams["LIST_EDIT_URL"]
				),
					array($tab_name => $_POST[$tab_name]),
					array("skip_empty" => true, "encode" => true)
				));
			}
		}
		else
		{
			ShowError($ob->LAST_ERROR);
			$bVarsFromForm = true;
		}
	}
	elseif(isset($_POST["action"]) && $_POST["action"]==="delete" && $arResult["IBLOCK_ID"])
	{
		if(CIBlock::Delete($arResult["IBLOCK_ID"]))
		{
			//Clear components cache
			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag("lists_list_".$arResult["IBLOCK_ID"]);
			$CACHE_MANAGER->ClearByTag("lists_list_any");
			$CACHE_MANAGER->CleanDir("menu");
		}
		LocalRedirect($arResult["~LISTS_URL"]);
	}
	elseif(isset($_POST["action"]) && $_POST["action"]==="migrate" && $arResult["IBLOCK_ID"])
	{
		if($arParams["IBLOCK_TYPE_ID"] != COption::GetOptionString("lists", "livefeed_iblock_type_id"))
		{
			if(CModule::includeModule('bizproc') && CBPRuntime::isFeatureEnabled())
			{
				\Bitrix\Lists\Importer::migrateList($arResult["IBLOCK_ID"]);

				CLists::deleteListsUrl($arResult["IBLOCK_ID"]);
				$processesUrl = COption::GetOptionString('lists', 'livefeed_url');
				$listElementUrl = str_replace($arParams["LISTS_URL"], $processesUrl, $arParams["LIST_ELEMENT_URL"]);
				$list = new CList($arResult["IBLOCK_ID"]);
				$list->ActualizeDocumentAdminPage(str_replace(
					array("#list_id#", "#group_id#"),
					array($arResult["IBLOCK_ID"], $arParams["SOCNET_GROUP_ID"]),
					$listElementUrl
				));

				$path = rtrim(SITE_DIR, '/');
				LocalRedirect($path.$processesUrl);
			}
		}
		LocalRedirect($arResult["~LISTS_URL"]);
	}
	else
	{
		//Go to lists page
		LocalRedirect($arResult["~LISTS_URL"]);
	}
}

$data = array();

if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
	$typeTranslation = '_PROCESS';
else
	$typeTranslation = '';

if($bVarsFromForm)
{//There was an error so display form values
	$data["ID"] = $arIBlock? $arIBlock["ID"]: "";
	$data["NAME"] = $_POST["NAME"];
	$data["DESCRIPTION"] = $_POST["DESCRIPTION"];
	$data["SORT"] = $_POST["SORT"];
	if($bBizProc)
		$data["BIZPROC"] = $_POST["BIZPROC"];
	$data["PICTURE"] = $arIBlock? $arIBlock["PICTURE"]: "";
	$data["ELEMENTS_NAME"] = $_POST["ELEMENTS_NAME"];
	$data["ELEMENT_NAME"] = $_POST["ELEMENT_NAME"];
	$data["ELEMENT_ADD"] = $_POST["ELEMENT_ADD"];
	$data["ELEMENT_EDIT"] = $_POST["ELEMENT_EDIT"];
	$data["ELEMENT_DELETE"] = $_POST["ELEMENT_DELETE"];
	$data["SECTIONS_NAME"] = $_POST["SECTIONS_NAME"];
	$data["SECTION_NAME"] = $_POST["SECTION_NAME"];
	$data["SECTION_ADD"] = $_POST["SECTION_ADD"];
	$data["SECTION_EDIT"] = $_POST["SECTION_EDIT"];
	$data["SECTION_DELETE"] = $_POST["SECTION_DELETE"];
}
elseif($arIBlock)
{//Edit existing iblock
	$data["ID"] = $arIBlock["ID"];
	$data["NAME"] = $arIBlock["NAME"];
	$data["DESCRIPTION"] = $arIBlock["DESCRIPTION"];
	$data["SORT"] = $arIBlock["SORT"];
	if($bBizProc)
		$data["BIZPROC"] = $arIBlock["BIZPROC"];
	$data["PICTURE"] = $arIBlock["PICTURE"];
	$data["ELEMENTS_NAME"] = $arIBlock["ELEMENTS_NAME"];
	$data["ELEMENT_NAME"] = $arIBlock["ELEMENT_NAME"];
	$data["ELEMENT_ADD"] = $arIBlock["ELEMENT_ADD"];
	$data["ELEMENT_EDIT"] = $arIBlock["ELEMENT_EDIT"];
	$data["ELEMENT_DELETE"] = $arIBlock["ELEMENT_DELETE"];
	$data["SECTIONS_NAME"] = $arIBlock["SECTIONS_NAME"];
	$data["SECTION_NAME"] = $arIBlock["SECTION_NAME"];
	$data["SECTION_ADD"] = $arIBlock["SECTION_ADD"];
	$data["SECTION_EDIT"] = $arIBlock["SECTION_EDIT"];
	$data["SECTION_DELETE"] = $arIBlock["SECTION_DELETE"];
}
else
{//New one
	$data["ID"] = "";
	$data["NAME"] = GetMessage("CC_BLLE_FIELD_NAME_DEFAULT".$typeTranslation);
	$data["SORT"] = 500;
	if($bBizProc)
		$data["BIZPROC"] = "Y";
	$data["PICTURE"] = "";
	$arMessages = CIBlock::GetMessages(0, $arParams["~IBLOCK_TYPE_ID"]);
	$data["ELEMENTS_NAME"] = $arMessages["ELEMENTS_NAME"];
	$data["ELEMENT_NAME"] = $arMessages["ELEMENT_NAME"];
	$data["ELEMENT_ADD"] = $arMessages["ELEMENT_ADD"];
	$data["ELEMENT_EDIT"] = $arMessages["ELEMENT_EDIT"];
	$data["ELEMENT_DELETE"] = $arMessages["ELEMENT_DELETE"];
	$data["SECTIONS_NAME"] = $arMessages["SECTIONS_NAME"];
	$data["SECTION_NAME"] = $arMessages["SECTION_NAME"];
	$data["SECTION_ADD"] = $arMessages["SECTION_ADD"];
	$data["SECTION_EDIT"] = $arMessages["SECTION_EDIT"];
	$data["SECTION_DELETE"] = $arMessages["SECTION_DELETE"];
}

$arResult["FORM_DATA"] = array();
foreach($data as $key => $value)
{
	$arResult["FORM_DATA"][$key] = htmlspecialcharsbx($value);
	$arResult["FORM_DATA"]["~".$key] = $value;
}

if($arResult["IBLOCK_ID"] > 0)
	$RIGHTS_MODE = CIBlock::GetArrayByID($arResult["IBLOCK_ID"], "RIGHTS_MODE");
elseif($arParams["SOCNET_GROUP_ID"])
	$RIGHTS_MODE = 'S';
else
	$RIGHTS_MODE = 'E';

$arResult["RIGHTS"] = array();
if($RIGHTS_MODE != 'E')
{
	$i = 0;
	if($arParams["SOCNET_GROUP_ID"])
	{
		$arSocnetPerm = CLists::GetSocnetPermission($arResult["IBLOCK_ID"]);
		foreach($arSocnetPerm as $role => $permission)
		{
			if($permission > "W")
				$permission = "W";
			switch($role)
			{
			case "A":
			case "E":
			case "K":
				$arResult["RIGHTS"]['n'.($i++)] = array(
					"GROUP_CODE" => "SG".$arParams["SOCNET_GROUP_ID"]."_".$role,
					"IS_INHERITED" => "N",
					"TASK_ID" => CIBlockRights::LetterToTask($permission),
				);
				break;
			case "L":
				$arResult["RIGHTS"]['n'.($i++)] = array(
					"GROUP_CODE" => "AU",
					"IS_INHERITED" => "N",
					"TASK_ID" => CIBlockRights::LetterToTask($permission),
				);
				break;
			case "N":
				$arResult["RIGHTS"]['n'.($i++)] = array(
					"GROUP_CODE" => "G2",
					"IS_INHERITED" => "N",
					"TASK_ID" => CIBlockRights::LetterToTask($permission),
				);
				break;
			}
		}
	}
	else
	{
		$arIBlockPerms = CIBlock::GetGroupPermissions($arResult["IBLOCK_ID"]);
		foreach($arIBlockPerms as $group_id => $letter)
			$arResult["RIGHTS"]['n'.($i++)] = array(
				"GROUP_CODE" => "G".$group_id,
				"IS_INHERITED" => "N",
				"TASK_ID" => CIBlockRights::LetterToTask($letter),
			);
	}
}
elseif($arResult["IBLOCK_ID"] > 0)
{
	$obIBlockRights = new CIBlockRights($arResult["IBLOCK_ID"]);
	$arResult["RIGHTS"] = $obIBlockRights->GetRights(array("count_overwrited" => true));
}

$arResult["TASKS"] = CIBlockRights::GetRightsList();
$arResult['RAND_STRING'] = $this->randString();

$this->IncludeComponentTemplate();

if($arIBlock)
	$APPLICATION->SetTitle(GetMessage("CC_BLLE_TITLE_EDIT".$typeTranslation, array("#NAME#" => $arResult["IBLOCK"]["NAME"])));
else
	$APPLICATION->SetTitle(GetMessage("CC_BLLE_TITLE_NEW".$typeTranslation));

if($arResult["IBLOCK_ID"])
	$APPLICATION->AddChainItem($arResult["IBLOCK"]["NAME"], $arResult["~LIST_URL"]);

$APPLICATION->AddChainItem(GetMessage("CC_BLLE_CHAIN_EDIT"), $arResult["~LIST_EDIT_URL"]);
?>