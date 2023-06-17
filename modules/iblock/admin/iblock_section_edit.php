<?
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

/** @global CDatabase $DB */
global $DB;
/** @global CUser $USER */
global $USER;
/** @global CMain $APPLICATION */
global $APPLICATION;
/** @global CUserTypeManager $USER_FIELD_MANAGER */
global $USER_FIELD_MANAGER;

/** @global CAdminPage $adminPage */
global $adminPage;
/** @global CAdminSidePanelHelper $adminSidePanelHelper */
global $adminSidePanelHelper;

$request = Context::getCurrent()->getRequest();
$return_url = (string)($request->get('return_url') ?? '');

$selfFolderUrl = $adminPage->getSelfFolderUrl();

if (defined("BX_PUBLIC_MODE") && BX_PUBLIC_MODE == 1)
{
	$adminSidePanelHelper->setSkipResponse(true);
}

$io = CBXVirtualIo::GetInstance();
$strWarning = "";
$bVarsFromForm = false;
$message = false;
$ID = (isset($_REQUEST['ID']) ? (int)$_REQUEST['ID'] : 0);
$IBLOCK_SECTION_ID = (isset($_REQUEST['IBLOCK_SECTION_ID']) ? (int)$_REQUEST['IBLOCK_SECTION_ID'] : 0);
$IBLOCK_ID = (isset($_REQUEST['IBLOCK_ID']) ? (int)$_REQUEST['IBLOCK_ID'] : 0);
$find_section_section = (isset($_REQUEST['find_section_section']) ? (int)$_REQUEST['find_section_section'] : 0);
/* autocomplete */
$strLookup = '';
if (isset($_REQUEST['lookup']))
	$strLookup = preg_replace("/[^a-zA-Z0-9_:]/", "", htmlspecialcharsbx($_REQUEST["lookup"]));
if ('' != $strLookup)
{
	define('BT_UT_AUTOCOMPLETE', 1);
}
$bAutocomplete = defined('BT_UT_AUTOCOMPLETE') && (BT_UT_AUTOCOMPLETE == 1);

$arIBlock = CIBlock::GetArrayByID($IBLOCK_ID);
if($arIBlock)
{
	$bBadBlock = !(CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "iblock_admin_display")
		&& (
			CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_edit")
			|| CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_SECTION_ID, "section_section_bind")
		)
	);
}
else
{
	$bBadBlock = true;
}

if(!$bBadBlock)
{
	$arIBTYPE = CIBlockType::GetByIDLang($arIBlock['IBLOCK_TYPE_ID'], LANGUAGE_ID);
	if($arIBTYPE === false)
		$bBadBlock = true;
	else
		$type = $arIBlock['IBLOCK_TYPE_ID'];
}

if($bBadBlock)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	if ($bAutocomplete)
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
	else
		require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBSEC_E_BAD_IBLOCK"));
	?><a href="iblock_admin.php?lang=<?=LANGUAGE_ID?>&amp;type=<?=urlencode($type)?>"><?echo GetMessage("IBSEC_E_BACK_TO_ADMIN")?></a><?
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$urlBuilder = Iblock\Url\AdminPage\BuilderManager::getInstance()->getBuilder();
if ($urlBuilder === null)
{
	$APPLICATION->SetTitle($arIBTYPE["NAME"]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(GetMessage("IBSEC_E_ERR_BUILDER_ADSENT"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
$urlBuilder->setIblockId($IBLOCK_ID);
$urlBuilder->setUrlParams(array());

$pageConfig = array(
	'IBLOCK_EDIT' => false,
	'PUBLIC_PAGE' => false,

	'SHOW_NAVCHAIN' => true,
	'NAVCHAIN_ROOT' => false,
	'SHOW_CONTEXT_MENU' => true
);
switch ($urlBuilder->getId())
{
	case 'CRM':
	case 'SHOP':
	case 'INVENTORY':
		$pageConfig['SHOW_NAVCHAIN'] = false;
		$pageConfig['SHOW_CONTEXT_MENU'] = false;
		$pageConfig['PUBLIC_PAGE'] = true;
		break;
	case 'CATALOG':
		break;
	case 'IBLOCK':
		$pageConfig['IBLOCK_EDIT'] = true;
		$pageConfig['NAVCHAIN_ROOT'] = true;
		break;
}
if ($bAutocomplete)
{
	$pageConfig['SHOW_NAVCHAIN'] = false;
	$pageConfig['SHOW_CONTEXT_MENU'] = false;
}

$APPLICATION->AddHeadScript('/bitrix/js/main/admin_tools.js');
$APPLICATION->AddHeadScript('/bitrix/js/iblock/iblock_edit.js');

if(!$arIBlock["SECTION_NAME"])
{
	$arIBlock["SECTION_NAME"] = $arIBTYPE["SECTION_NAME"] ?: GetMessage("IBLOCK_SECTION");
}

$bEditRights = $arIBlock["RIGHTS_MODE"] === "E" && CIBlockSectionRights::UserHasRightTo($IBLOCK_ID, $ID, "section_rights_edit");

$aTabs = array(
	array(
		"DIV" => "edit1",
		"TAB" => $arIBlock["SECTION_NAME"],
		"ICON" => "iblock_section",
		"TITLE" => htmlspecialcharsbx($ID > 0 ? $arIBlock["SECTION_EDIT"] : $arIBlock["SECTION_ADD"]),
	),
	array(
		"DIV" => "edit5",
		"TAB" => GetMessage("IBSEC_E_TAB5"),
		"ICON" => "iblock_iprops",
		"TITLE" => GetMessage("IBSEC_E_TAB5_TITLE"),
		"ONSELECT" => "InheritedPropertiesTemplates.onTabSelect();",
	),
	array(
		"DIV" => "edit2",
		"TAB" => GetMessage("IBSEC_E_TAB2"),
		"ICON" => "iblock_section",
		"TITLE" => GetMessage("IBSEC_E_TAB2_TITLE"),
	),
);
//Add user fields tab only when there is fields defined or user has rights for adding new field
if(
	(count($USER_FIELD_MANAGER->GetUserFields("IBLOCK_".$IBLOCK_ID."_SECTION")) > 0) ||
	($USER_FIELD_MANAGER->GetRights("IBLOCK_".$IBLOCK_ID."_SECTION") >= "W")
)
{
	$aTabs[] = $USER_FIELD_MANAGER->EditFormTab("IBLOCK_".$IBLOCK_ID."._SECTION");
}

if($bEditRights)
{
	$aTabs[] = array(
		"DIV" => "edit3",
		"TAB" => GetMessage("IBSEC_E_TAB_RIGHTS"),
		"ICON" => "iblock_section",
		"TITLE" => GetMessage("IBSEC_E_TAB_RIGHTS_TITLE"),
	);
}

if($arIBlock["SECTION_PROPERTY"] === "Y")
{
	$aTabs[] = array(
		"DIV" => "edit4",
		"TAB" => GetMessage("IBSEC_E_PROPERTY_TAB"),
		"ICON" => "iblock_section",
		"TITLE" => GetMessage("IBSEC_E_PROPERTY_TAB_TITLE"),
	);
}

$tabControl = new CAdminForm("form_section_".$IBLOCK_ID, $aTabs);
$customTabber = new CAdminTabEngine("OnAdminIBlockSectionEdit", array("ID" => $ID, "IBLOCK"=>$arIBlock, "IBLOCK_TYPE"=>$arIBTYPE));
$tabControl->AddTabs($customTabber);

if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& isset($_REQUEST["Update"]) && $_REQUEST["Update"] != ""
	&& check_bitrix_sessid()
)
{
	$adminSidePanelHelper->decodeUriComponent();

	$DB->StartTransaction();
	$bs = new CIBlockSection;

	$picture = $_REQUEST["PICTURE"] ?? null;
	$pictureDescription = $_REQUEST["PICTURE_descr"] ?? null;
	if (array_key_exists("PICTURE", $_FILES))
	{
		$picture = $_FILES["PICTURE"];
		$pictureDescription = null;
	}

	$arPICTURE = CIBlock::makeFileArray(
		$picture,
		isset(${"PICTURE_del"}) && ${"PICTURE_del"} === "Y",
		$pictureDescription
	);
	if ($arPICTURE["error"] == 0)
	{
		$arPICTURE["COPY_FILE"] = "Y";
	}

	$detailPicture = $_REQUEST["DETAIL_PICTURE"] ?? null;
	$detailPictureDescription = $_REQUEST["DETAIL_PICTURE_descr"] ?? null;
	if (array_key_exists("DETAIL_PICTURE", $_FILES))
	{
		$detailPicture = $_FILES["DETAIL_PICTURE"];
		$detailPictureDescription = null;
	}

	$arDETAIL_PICTURE = CIBlock::makeFileArray(
		$detailPicture,
		isset(${"DETAIL_PICTURE_del"}) && ${"DETAIL_PICTURE_del"} === "Y",
		$detailPictureDescription
	);
	if ($arDETAIL_PICTURE["error"] == 0)
	{
		$arDETAIL_PICTURE["COPY_FILE"] = "Y";
	}

	$arFields = [
		"IBLOCK_ID" => $IBLOCK_ID,
		"IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
		"PICTURE" => $arPICTURE,
		"DETAIL_PICTURE" => $arDETAIL_PICTURE,
	];
	$simpleFields = [
		'ACTIVE',
		'NAME',
		'SORT',
		'CODE',
		'DESCRIPTION',
		'DESCRIPTION_TYPE',
	];
	foreach ($simpleFields as $fieldId)
	{
		if (isset($_POST[$fieldId]) && is_string($_POST[$fieldId]))
		{
			$arFields[$fieldId] = $_POST[$fieldId];
		}
	}

	if (isset($arFields['DESCRIPTION']) && Loader::includeModule('bitrix24'))
	{
		$sanitizer = new \CBXSanitizer();
		$sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
		$sanitizer->ApplyDoubleEncode(false);
		$arFields['DESCRIPTION'] = $sanitizer->SanitizeHtml(htmlspecialchars_decode($arFields['DESCRIPTION']));
	}

	if(isset($_POST["SECTION_PROPERTY"]) && is_array($_POST["SECTION_PROPERTY"]))
	{
		$arFields["SECTION_PROPERTY"] = array();
		foreach($_POST["SECTION_PROPERTY"] as $PID => $arLink)
		{
			if($arLink["SHOW"] === "Y")
			{
				$arFields["SECTION_PROPERTY"][$PID] = array(
					"SMART_FILTER" => $arLink["SMART_FILTER"] ?? null,
					"DISPLAY_TYPE" => $arLink["DISPLAY_TYPE"] ?? null,
					"DISPLAY_EXPANDED" => $arLink["DISPLAY_EXPANDED"] ?? null,
					"FILTER_HINT" => $arLink["FILTER_HINT"] ?? null,
				);
			}
		}
	}

	if($bEditRights && $arIBlock["RIGHTS_MODE"] === "E")
	{
		if (isset($_POST["RIGHTS"]))
		{
			if (is_array($_POST["RIGHTS"]))
			{
				$arFields["RIGHTS"] = CIBlockRights::Post2Array($_POST["RIGHTS"]);
			}
			else
			{
				$arFields["RIGHTS"] = [];
			}
		}
	}

	if (is_array($_POST["IPROPERTY_TEMPLATES"]))
	{
		$SECTION_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_NAME"]);
		$SECTION_DETAIL_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_NAME"]);
		$ELEMENT_PREVIEW_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_NAME"]);
		$ELEMENT_DETAIL_PICTURE_FILE_NAME = \Bitrix\Iblock\Template\Helper::convertArrayToModifiers($_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_NAME"]);

		$arFields["IPROPERTY_TEMPLATES"] = array(
			"SECTION_META_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_META_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_META_TITLE"]["TEMPLATE"]:
					""
				),
			"SECTION_META_KEYWORDS" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_META_KEYWORDS"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_META_KEYWORDS"]["TEMPLATE"]:
					""
				),
			"SECTION_META_DESCRIPTION" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_META_DESCRIPTION"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_META_DESCRIPTION"]["TEMPLATE"]:
					""
				),
			"SECTION_PAGE_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_PAGE_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_PAGE_TITLE"]["TEMPLATE"]:
					""
				),
			"ELEMENT_META_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_TITLE"]["TEMPLATE"]:
					""
				),
			"ELEMENT_META_KEYWORDS" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_KEYWORDS"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_KEYWORDS"]["TEMPLATE"]:
					""
				),
			"ELEMENT_META_DESCRIPTION" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_DESCRIPTION"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_META_DESCRIPTION"]["TEMPLATE"]:
					""
				),
			"ELEMENT_PAGE_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PAGE_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PAGE_TITLE"]["TEMPLATE"]:
					""
				),
			"SECTION_PICTURE_FILE_ALT" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_ALT"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_ALT"]["TEMPLATE"]:
					""
				),
			"SECTION_PICTURE_FILE_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_TITLE"]["TEMPLATE"]:
					""
				),
			"SECTION_PICTURE_FILE_NAME" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_PICTURE_FILE_NAME"]["INHERITED"]==="N"?
					$SECTION_PICTURE_FILE_NAME:
					""
				),
			"SECTION_DETAIL_PICTURE_FILE_ALT" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_ALT"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_ALT"]["TEMPLATE"]:
					""
				),
			"SECTION_DETAIL_PICTURE_FILE_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_TITLE"]["TEMPLATE"]:
					""
				),
			"SECTION_DETAIL_PICTURE_FILE_NAME" => (
				$_POST["IPROPERTY_TEMPLATES"]["SECTION_DETAIL_PICTURE_FILE_NAME"]["INHERITED"]==="N"?
					$SECTION_DETAIL_PICTURE_FILE_NAME:
					""
				),
			"ELEMENT_PREVIEW_PICTURE_FILE_ALT" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_ALT"]["TEMPLATE"]:
					""
				),
			"ELEMENT_PREVIEW_PICTURE_FILE_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_TITLE"]["TEMPLATE"]:
					""
				),
			"ELEMENT_PREVIEW_PICTURE_FILE_NAME" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_PREVIEW_PICTURE_FILE_NAME"]["INHERITED"]==="N"?
					$ELEMENT_PREVIEW_PICTURE_FILE_NAME:
					""
				),
			"ELEMENT_DETAIL_PICTURE_FILE_ALT" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_ALT"]["TEMPLATE"]:
					""
				),
			"ELEMENT_DETAIL_PICTURE_FILE_TITLE" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]["INHERITED"]==="N"?
					$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_TITLE"]["TEMPLATE"]:
					""
				),
			"ELEMENT_DETAIL_PICTURE_FILE_NAME" => (
				$_POST["IPROPERTY_TEMPLATES"]["ELEMENT_DETAIL_PICTURE_FILE_NAME"]["INHERITED"]==="N"?
					$ELEMENT_DETAIL_PICTURE_FILE_NAME:
					""
				),
		);
	}

	$USER_FIELD_MANAGER->EditFormAddFields("IBLOCK_".$IBLOCK_ID."_SECTION", $arFields);

	if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y" && is_set($_POST, "XML_ID"))
		$arFields["XML_ID"] = $_POST["XML_ID"];

	if(!$customTabber->Check())
	{
		if($ex = $APPLICATION->GetException())
			$strWarning .= $ex->GetString();
		else
			$strWarning .= "Error. ";
	}

	if ($strWarning == "")
	{
		if ($ID > 0)
		{
			$res = $bs->Update($ID, $arFields, true, true, true);
		}
		else
		{
			$ID = $bs->Add($arFields, true, true, true);
			$res = ($ID > 0);
		}
	}
	else
	{
		$res = false;
	}

	if ($res && $strWarning == "")
	{
		$res = $customTabber->Action();
		if (!$res)
		{
			if ($ex = $APPLICATION->GetException())
				$strWarning .= $ex->GetString();
			else
				$strWarning .= "Error. ";
		}
	}

	if(!$res)
	{
		$strWarning .= $bs->LAST_ERROR;
		$bVarsFromForm = true;
		$DB->Rollback();
		$message = null;
		if($e = $APPLICATION->GetException())
			$message = new CAdminMessage(GetMessage("admin_lib_error"), $e);

		$errorMessage = '';
		if ($strWarning !== '')
		{
			$errorMessage = $strWarning;
		}
		elseif ($message instanceof \CAdminMessage)
		{
			$messageList = array();
			$rawMessageList = $message->GetMessages();
			if (is_array($rawMessageList))
			{
				foreach ($rawMessageList as $item)
				{
					$messageList[] = is_array($item)
						? $item["text"]
						: $item
					;
				}
			}
			$errorMessage = implode("; ", $messageList);
			unset($messageList);
		}
		$adminSidePanelHelper->sendJsonErrorResponse($errorMessage);
	}
	else
	{
		if (isset($_POST["IPROPERTY_CLEAR_VALUES"]) && $_POST["IPROPERTY_CLEAR_VALUES"] === "Y")
		{
			$ipropValues = new \Bitrix\Iblock\InheritedProperty\SectionValues($IBLOCK_ID, $ID);
			$ipropValues->clearValues();
		}

		$DB->Commit();

		if ($adminSidePanelHelper->isAjaxRequest())
		{
			$adminSidePanelHelper->sendSuccessResponse("base", array("ID" => $ID));
		}

		// fix open page without slider from public shop
		$urlBuilder->setSliderMode(false);
		$urlBuilder->setUrlParams([]);
		// fix end
		if ($request->getPost('apply') === null && $request->getPost('save_and_add') === null)
		{
			if ($bAutocomplete)
			{
				?><script type="text/javascript">
				window.opener.<? echo $strLookup; ?>.AddValue(<? echo $ID;?>);
				window.close();
				</script><?
				CMain::FinalActions();
			}
			elseif($return_url <> '')
			{
				if(mb_strpos($return_url, "#") !== false)
				{
					$rsSection = CIBlockSection::GetList(array(), array("ID" => $ID), false, array("SECTION_PAGE_URL"));
					$arSection = $rsSection->Fetch();
					if($arSection)
						$return_url = CIBlock::ReplaceDetailUrl($return_url, $arSection, true, "S");
				}
				$adminSidePanelHelper->localRedirect($return_url);
				LocalRedirect($return_url);
			}
			else
			{
				$saveUrl = $urlBuilder->getSectionListUrl($find_section_section);
				$adminSidePanelHelper->localRedirect($saveUrl);
				LocalRedirect($saveUrl);
			}
		}
		elseif ($request->getPost('save_and_add') !== null)
		{
			if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
			{
				while(ob_end_clean());
				$url = $urlBuilder->getSectionDetailUrl(
					0,
					array(
						"find_section_section" => $find_section_section,
						"return_url" => ($return_url <> ''? $return_url: null),
						"IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
						"from_module" => "iblock",
						"bxpublic" => "Y",
						"nobuttons" => "Y",
					)
				);
				?><script type="text/javascript">
						top.BX.ajax.post('<?=CUtil::JSEscape($url); ?>',
							{},
							function (result) {
								top.BX.closeWait();
								top.window.reloadAfterClose = true;
								top.BX.WindowManager.Get().SetContent(result);
							}
						);
					</script>';
				<?
				CMain::FinalActions();
			}
			else
			{
				$saveAndAddUrl = $urlBuilder->getSectionDetailUrl(
					0,
					array(
						"find_section_section" => $find_section_section,
						"IBLOCK_SECTION_ID" => $IBLOCK_SECTION_ID,
						"return_url" => ($return_url <> ''? $return_url: null),
					),
					"&".$tabControl->ActiveTabParam()
				);
				$adminSidePanelHelper->localRedirect($saveAndAddUrl);
				LocalRedirect($saveAndAddUrl);
			}
		}
		else
		{
			$applyUrl = $urlBuilder->getSectionDetailUrl(
				$ID,
				array(
					"find_section_section" => $find_section_section,
					"return_url" => $return_url <> '' ? $return_url: null
				),
				"&".$tabControl->ActiveTabParam()
			);
			$applyUrl = $adminSidePanelHelper->setDefaultQueryParams($applyUrl);
			LocalRedirect($applyUrl);
		}
	}
}

ClearVars("str_");
$str_ACTIVE="Y";
$str_NAME = htmlspecialcharsbx($arIBlock["FIELDS"]["SECTION_NAME"]["DEFAULT_VALUE"]);
$str_DESCRIPTION_TYPE = $arIBlock["FIELDS"]["SECTION_DESCRIPTION_TYPE"]["DEFAULT_VALUE"] !== "html"? "text": "html";
$str_DESCRIPTION = htmlspecialcharsbx($arIBlock["FIELDS"]["SECTION_DESCRIPTION"]["DEFAULT_VALUE"]);
$str_SORT="500";
$str_IBLOCK_SECTION_ID = $IBLOCK_SECTION_ID;

$result = CIBlockSection::GetByID($ID);
$arSection = $result->ExtractFields("str_");
if(!$arSection)
{
	$ID = 0;
	$ipropTemlates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($IBLOCK_ID, 0);
}
else
{
	$ipropTemlates = new \Bitrix\Iblock\InheritedProperty\SectionTemplates($IBLOCK_ID, $ID);
}

if($bVarsFromForm)
{
	$DB->InitTableVarsForEdit("b_iblock_section", "", "str_");
	$str_IPROPERTY_TEMPLATES = $_POST["IPROPERTY_TEMPLATES"];
}
else
{
	$str_IPROPERTY_TEMPLATES = $ipropTemlates->findTemplates();
	$str_IPROPERTY_TEMPLATES["SECTION_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["SECTION_PICTURE_FILE_NAME"] ?? null);
	$str_IPROPERTY_TEMPLATES["SECTION_DETAIL_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["SECTION_DETAIL_PICTURE_FILE_NAME"] ?? null);
	$str_IPROPERTY_TEMPLATES["ELEMENT_PREVIEW_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["ELEMENT_PREVIEW_PICTURE_FILE_NAME"] ?? null);
	$str_IPROPERTY_TEMPLATES["ELEMENT_DETAIL_PICTURE_FILE_NAME"] = \Bitrix\Iblock\Template\Helper::convertModifiersToArray($str_IPROPERTY_TEMPLATES["ELEMENT_DETAIL_PICTURE_FILE_NAME"] ?? null);

	// default values
	$columns = $DB->GetTableFieldsList("b_iblock_section");
	foreach ($columns as $column)
	{
		${"str_{$column}"} ??= null;
	}
}

if($ID>0)
	$APPLICATION->SetTitle(GetMessage("IBSEC_E_EDIT_TITLE", array("#IBLOCK_NAME#"=>$arIBlock["NAME"], "#SECTION_TITLE#"=>$arIBlock["SECTION_NAME"])));
else
	$APPLICATION->SetTitle(GetMessage("IBSEC_E_NEW_TITLE", array("#IBLOCK_NAME#"=>$arIBlock["NAME"], "#SECTION_TITLE#"=>$arIBlock["SECTION_NAME"])));

if ($pageConfig['SHOW_NAVCHAIN'])
{
	if ($pageConfig['NAVCHAIN_ROOT'])
	{
		$adminChain->AddItem(array(
			"TEXT" => htmlspecialcharsex($arIBlock["NAME"]),
			"LINK" => htmlspecialcharsbx($urlBuilder->getSectionListUrl(0))
		));
	}
	if ($find_section_section > 0)
	{
		$nav = CIBlockSection::GetNavChain($IBLOCK_ID, $find_section_section, array('ID', 'NAME'), true);
		foreach ($nav as $ar_nav)
		{
			$last_nav = $urlBuilder->getSectionListUrl(
				(int)$ar_nav['ID']
			);
			$adminChain->AddItem(array(
				"TEXT" => htmlspecialcharsEx($ar_nav["NAME"]),
				"LINK" => htmlspecialcharsbx($last_nav),
			));
		}
		unset($last_nav, $ar_nav, $nav);
	}
}
if ($bAutocomplete)
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
else
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if ($pageConfig['SHOW_CONTEXT_MENU'])
{
	$aMenu = array(
		array(
			"TEXT" => htmlspecialcharsbx($arIBlock["SECTIONS_NAME"]),
			"LINK" => $urlBuilder->getSectionListUrl($find_section_section),
			"ICON" => "btn_list",
		),
	);

	if ($ID > 0)
	{
		$newUrl = $urlBuilder->getSectionDetailUrl(
			null,
			array(
				"find_section_section" => $find_section_section,
				"IBLOCK_SECTION_ID" => ($IBLOCK_SECTION_ID > 0 ? $IBLOCK_SECTION_ID : $find_section_section)
			)
		);
		if (!$adminSidePanelHelper->isPublicFrame())
			$newUrl = $adminSidePanelHelper->setDefaultQueryParams($newUrl);
		$aMenu[] = array(
			"TEXT" => htmlspecialcharsbx($arIBlock["SECTION_ADD"]),
			"LINK" => $newUrl,
			"ICON" => "btn_new",
		);
		$deleteUrlParams = array('find_section_section'=> $find_section_section, 'action'=>'delete');
		if (!$adminSidePanelHelper->isPublicFrame())
			$deleteUrlParams['skip_public'] = true;
		$urlDelete = $selfFolderUrl.CIBlock::GetAdminSectionListLink($IBLOCK_ID, $deleteUrlParams);
		$urlDelete.= '&'.bitrix_sessid_get();
		$urlDelete.= '&ID[]='.(preg_match('/iblock_list_admin\.php/', $urlDelete) ? "S" : "").$ID;
		$buttonAction = $adminSidePanelHelper->isPublicFrame() ? "ONCLICK" : "LINK";
		$aMenu[] = array(
			"TEXT" => htmlspecialcharsbx($arIBlock["SECTION_DELETE"]),
			$buttonAction => "javascript:if(confirm('".GetMessageJS("IBSEC_E_CONFIRM_DEL_MESSAGE")."'))top.window.location.href='".CUtil::JSEscape($urlDelete)."'",
			"ICON" => "btn_delete",
		);
	}

	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}

if($strWarning)
	CAdminMessage::ShowOldStyleError($strWarning."<br>");
elseif($message)
	echo $message->Show();

$nameFormat = CSite::GetNameFormat();

//We have to explicitly call calendar and editor functions because
//first output may be discarded by form settings
$bFileman = Loader::includeModule("fileman");

$arTranslit = $arIBlock["FIELDS"]["SECTION_CODE"]["DEFAULT_VALUE"];
$bLinked = !$ID && (empty($_POST["linked_state"]) || $_POST["linked_state"] !== 'N');

$tabControl->BeginPrologContent();
?>
<style>
	#bx-admin-prefix table.internal tr:last-child td.internal-left,
	td.internal-left {
		border-left: 1px solid!important;
		border-color:#f1f1f1 #ccd5d7 #cfd8d9 #cfd8da!important;
		border-color:rgba(0,0,0,0.05) rgba(204,213,215,1) rgba(207,216,217,1) rgba(207,216,218,1);
	}
	#bx-admin-prefix table.internal tr:last-child td.internal-right,
	td.internal-right {
		border-right: 1px solid!important;
		border-color:#f1f1f1 #ccd5d7 #cfd8d9 #cfd8da!important;
		border-color:rgba(0,0,0,0.05) rgba(204,213,215,1) rgba(207,216,217,1) rgba(207,216,218,1);
	}
</style>
<?
if(method_exists($USER_FIELD_MANAGER, 'showscript'))
	echo $USER_FIELD_MANAGER->ShowScript();
CAdminCalendar::ShowScript();
if(COption::GetOptionString("iblock", "use_htmledit") === "Y" && $bFileman)
{
	//TODO:This dirty hack will be replaced by special method like calendar do
	echo '<div style="display:none">';
	CFileMan::AddHTMLEditorFrame(
		"SOME_TEXT",
		"",
		"SOME_TEXT_TYPE",
		"text",
		array(
			'height' => 450,
			'width' => '100%'
		),
		"N",
		0,
		"",
		"",
		$arIBlock["LID"]
	);
	echo '</div>';
}

if($arTranslit["TRANSLITERATION"] == "Y")
{
	CJSCore::Init(array('translit'));
	?>
	<script type="text/javascript">
		var linked=<?if($bLinked) echo 'true'; else echo 'false';?>;
		function set_linked()
		{
			linked=!linked;

			var name_link = document.getElementById('name_link');
			if(name_link)
			{
				if(linked)
					name_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
				else
					name_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
			}
			var code_link = document.getElementById('code_link');
			if(code_link)
			{
				if(linked)
					code_link.src='/bitrix/themes/.default/icons/iblock/link.gif';
				else
					code_link.src='/bitrix/themes/.default/icons/iblock/unlink.gif';
			}
			var linked_state = document.getElementById('linked_state');
			if(linked_state)
			{
				if(linked)
					linked_state.value='Y';
				else
					linked_state.value='N';
			}
		}
		var oldValue = '';
		function transliterate()
		{
			if(linked)
			{
				var from = document.getElementById('NAME');
				var to = document.getElementById('CODE');
				if(from && to && oldValue != from.value)
				{
					BX.translit(from.value, {
						'max_len' : <?echo intval($arTranslit['TRANS_LEN'])?>,
						'change_case' : '<?echo $arTranslit['TRANS_CASE']?>',
						'replace_space' : '<?echo $arTranslit['TRANS_SPACE']?>',
						'replace_other' : '<?echo $arTranslit['TRANS_OTHER']?>',
						'delete_repeat_replace' : <?echo $arTranslit['TRANS_EAT'] == 'Y'? 'true': 'false'?>,
						'use_google' : <?echo $arTranslit['USE_GOOGLE'] == 'Y'? 'true': 'false'?>,
						'callback' : function(result){to.value = result; setTimeout('transliterate()', 250);}
					});
					oldValue = from.value;
				}
				else
				{
					setTimeout('transliterate()', 250);
				}
			}
			else
			{
				setTimeout('transliterate()', 250);
			}
		}
		transliterate();
	</script>
	<?
}
?>
<script type="text/javascript">
	var InheritedPropertiesTemplates = new JCInheritedPropertiesTemplates(
		'<?echo $tabControl->GetName()?>_form',
		'<?=$selfFolderUrl?>iblock_templates.ajax.php?ENTITY_TYPE=S&IBLOCK_ID=<?echo $IBLOCK_ID;?>&ENTITY_ID=<?echo intval($ID)?>&bxpublic=y'
	);
	BX.ready(function(){
		setTimeout(function(){
			InheritedPropertiesTemplates.updateInheritedPropertiesTemplates(true);
		}, 1000);
	});
</script>
<?

$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
?>
<?=bitrix_sessid_post()?>
<?echo GetFilterHiddens("find_");?>
<input type="hidden" name="linked_state" id="linked_state" value="<?if($bLinked) echo 'Y'; else echo 'N';?>">
<input type="hidden" name="Update" value="Y">
<input type="hidden" name="ID" value="<?echo $ID?>">
<?if($return_url <> ''):?>
	<input type="hidden" name="return_url" value="<?=htmlspecialcharsbx($return_url)?>">
<?endif;

$tabControl->EndEpilogContent();
$customTabber->SetErrorState($bVarsFromForm);

$arEditLinkParams = array(
	"find_section_section" => intval($find_section_section)
);
if ($bAutocomplete)
{
	$arEditLinkParams['lookup'] = $strLookup;
}
if ($adminSidePanelHelper->isPublicFrame())
{
	$arEditLinkParams["IFRAME"] = "Y";
	$arEditLinkParams["IFRAME_TYPE"] = "PUBLIC_FRAME";
}
$tabControl->Begin(array(
	"FORM_ACTION" => $urlBuilder->getSectionSaveUrl(null, $arEditLinkParams)
));
$tabControl->BeginNextFormTab();
?>
	<?$tabControl->BeginCustomField("ID", "ID:");?>
<?if($ID>0):?>
	<tr>
		<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%"><?echo $str_ID?></td>
	</tr>
<?endif;?>
	<?$tabControl->EndCustomField("ID", '');?>

	<?$tabControl->BeginCustomField("DATE_CREATE", GetMessage("IBSEC_E_CREATED"));?>
<?if($ID>0):?>
		<?if($str_DATE_CREATE <> ''):?>
			<tr>
				<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
				<td width="60%"><?echo $str_DATE_CREATE?><?
				if(intval($str_CREATED_BY)>0):
					?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$str_CREATED_BY?>"><?echo $str_CREATED_BY?></a>]<?
					$rsUser = CUser::GetByID($str_CREATED_BY);
					$arUser = $rsUser->Fetch();
					if($arUser):
						echo '&nbsp;'.CUser::FormatName($nameFormat, $arUser, false, true);
					endif;
				endif;
				?></td>
			</tr>
		<?endif;?>
<?endif;?>
	<?$tabControl->EndCustomField("DATE_CREATE", '');?>

	<?$tabControl->BeginCustomField("TIMESTAMP_X", GetMessage("IBSEC_E_LAST_UPDATE"));?>
<?if($ID>0):?>
	<tr>
		<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td width="60%"><?echo $str_TIMESTAMP_X?><?
		if(intval($str_MODIFIED_BY)>0):
			?>&nbsp;&nbsp;&nbsp;[<a href="user_edit.php?lang=<?=LANGUAGE_ID?>&amp;ID=<?=$str_MODIFIED_BY?>"><?echo $str_MODIFIED_BY?></a>]<?
			if(intval($str_CREATED_BY) != intval($str_MODIFIED_BY))
			{
				$rsUser = CUser::GetByID($str_MODIFIED_BY);
				$arUser = $rsUser->Fetch();
			}
			if($arUser):
				echo '&nbsp;'.CUser::FormatName($nameFormat, $arUser, false, true);
			endif;
		endif?></td>
	</tr>
<?endif;?>
	<?$tabControl->EndCustomField("TIMESTAMP_X", '');?>

<?
$tabControl->AddCheckBoxField(
	'ACTIVE',
	GetMessage('IBSEC_E_ACTIVE'),
	false,
	[
		'Y',
		'N',
	],
	$str_ACTIVE === 'Y'
);

$tabControl->BeginCustomField("IBLOCK_SECTION_ID", GetMessage("IBSEC_E_PARENT_SECTION").":");?>
	<tr>
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td>
		<?$l = CIBlockSection::GetTreeList(Array("IBLOCK_ID"=>$IBLOCK_ID), array("ID", "NAME", "DEPTH_LEVEL"));?>
		<select name="IBLOCK_SECTION_ID" >
			<option value="0"><?echo GetMessage("IBLOCK_UPPER_LEVEL")?></option>
		<?
			while($a = $l->Fetch()):
				?><option value="<?echo intval($a["ID"])?>"<?if($str_IBLOCK_SECTION_ID==$a["ID"])echo " selected"?>><?echo str_repeat(".", $a["DEPTH_LEVEL"])?><?echo htmlspecialcharsbx($a["NAME"])?></option><?
			endwhile;
		?>
		</select>
		</td>
	</tr>
<?
$tabControl->EndCustomField("IBLOCK_SECTION_ID", '<input type="hidden" id="IBLOCK_SECTION_ID" name="IBLOCK_SECTION_ID" value="'.$str_IBLOCK_SECTION_ID.'">');

if($arTranslit["TRANSLITERATION"] == "Y")
{
	$tabControl->BeginCustomField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true);
	?>
		<tr id="tr_NAME">
			<td><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td nowrap>
				<input type="text" size="70" name="NAME" id="NAME" maxlength="255" value="<?echo $str_NAME?>"><img id="name_link" title="<?echo GetMessage("IBSEC_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("NAME",
		'<input type="hidden" name="NAME" id="NAME" value="'.$str_NAME.'">'
	);

	$tabControl->BeginCustomField("CODE", GetMessage("IBLOCK_FIELD_CODE").":", $arIBlock["FIELDS"]["SECTION_CODE"]["IS_REQUIRED"] === "Y");
	?>
		<tr id="tr_CODE">
			<td><?echo $tabControl->GetCustomLabelHTML()?></td>
			<td nowrap>

				<input type="text" size="70" name="CODE" id="CODE" maxlength="255" value="<?echo $str_CODE?>"><img id="code_link" title="<?echo GetMessage("IBSEC_E_LINK_TIP")?>" class="linked" src="/bitrix/themes/.default/icons/iblock/<?if($bLinked) echo 'link.gif'; else echo 'unlink.gif';?>" onclick="set_linked()" />
			</td>
		</tr>
	<?
	$tabControl->EndCustomField("CODE",
		'<input type="hidden" name="CODE" id="CODE" value="'.$str_CODE.'">'
	);
}
else
{
	$tabControl->AddEditField("NAME", GetMessage("IBLOCK_FIELD_NAME").":", true, array("size" => 70, "maxlength" => 255), $str_NAME);
}

$tabControl->BeginCustomField("PICTURE", GetMessage("IBSEC_E_PICTURE"), $arIBlock["FIELDS"]["SECTION_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("PICTURE", $_REQUEST) && $arSection)
	$str_PICTURE = intval($arSection["PICTURE"]);
?>
	<tr class="adm-detail-file-row">
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td><?
			echo \Bitrix\Main\UI\FileInput::createInstance(array(
				"name" => "PICTURE",
				"description" => true,
				"upload" => true,
				"allowUpload" => "I",
				"medialib" => true,
				"fileDialog" => true,
				"cloud" => true,
				"delete" => true,
				"maxCount" => 1
			))->show(($bVarsFromForm ? $_REQUEST["PICTURE"] : $str_PICTURE), $bVarsFromForm);
			?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("PICTURE", '');

$tabControl->BeginCustomField("DESCRIPTION", GetMessage("IBSEC_E_DESCRIPTION"), $arIBlock["FIELDS"]["SECTION_DESCRIPTION"]["IS_REQUIRED"] === "Y");
?>
	<tr class="heading">
		<td colspan="2"><?echo $tabControl->GetCustomLabelHTML()?></td>
	</tr>

	<?if(COption::GetOptionString("iblock", "use_htmledit") === "Y" && $bFileman):?>
	<tr id="tr_DESCRIPTION_EDITOR">
		<td colspan="2" align="center">
			<?CFileMan::AddHTMLEditorFrame(
				"DESCRIPTION",
				$str_DESCRIPTION,
				"DESCRIPTION_TYPE",
				$str_DESCRIPTION_TYPE,
				array(
					'height' => 450,
					'width' => '100%'
				),
				"N",
				0,
				"",
				"",
				$arIBlock["LID"],
				true,
				false,
				array(
					'hideTypeSelector' => $arIBlock["FIELDS"]["SECTION_DESCRIPTION_TYPE_ALLOW_CHANGE"]["DEFAULT_VALUE"] === "N",
				)
			);?>
		</td>
	</tr>
	<?else:?>
	<tr id="tr_DESCRIPTION_TYPE">
		<td><?echo GetMessage("IBSEC_E_DESC_TYPE")?></td>
		<td>
			<?php
			$isHtml = $str_DESCRIPTION_TYPE === 'html';
			if($arIBlock["FIELDS"]["SECTION_DESCRIPTION_TYPE_ALLOW_CHANGE"]["DEFAULT_VALUE"] === "N"):
				?>
				<input type="hidden" name="DESCRIPTION_TYPE" value="<?= $str_DESCRIPTION_TYPE;?>"><?= ($isHtml ? GetMessage("IBSEC_E_DESC_TYPE_HTML"): GetMessage("IBSEC_E_DESC_TYPE_TEXT")); ?>
				<?php
			else:
				?>
				<input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_text" value="text"<?= (!$isHtml ? ' checked' : ''); ?>> <label for="DESCRIPTION_TYPE_text"><?= GetMessage("IBLOCK_DESC_TYPE_TEXT")?></label> / <input type="radio" name="DESCRIPTION_TYPE" id="DESCRIPTION_TYPE_html" value="html"<?= ($isHtml ? ' checked' : ''); ?>> <label for="DESCRIPTION_TYPE_html"><?= GetMessage("IBLOCK_DESC_TYPE_HTML")?></label>
				<?php
			endif;
			?>
		</td>
	</tr>
	<tr id="tr_DESCRIPTION">
		<td colspan="2" align="center">
			<textarea cols="60" rows="15"  name="DESCRIPTION" style="width:100%"><?echo $str_DESCRIPTION?></textarea>
		</td>
	</tr>
	<?endif;
$tabControl->EndCustomField("DESCRIPTION",
	'<input type="hidden" name="DESCRIPTION" value="'.$str_DESCRIPTION.'">'.
	'<input type="hidden" name="DESCRIPTION_TYPE" value="'.$str_DESCRIPTION_TYPE.'">'
);

$tabControl->BeginNextFormTab();

$tabControl->AddSection("IPROPERTY_TEMPLATES_SECTION", GetMessage("IBSEC_E_SEO_FOR_SECTIONS"));
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_META_TITLE", GetMessage("IBSEC_E_SEO_META_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_META_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_META_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_META_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_META_KEYWORDS", GetMessage("IBSEC_E_SEO_META_KEYWORDS"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_META_KEYWORDS",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))
);
?>
<?
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_META_DESCRIPTION", GetMessage("IBSEC_E_SEO_META_DESCRIPTION"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_META_DESCRIPTION",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))
);
?>
<?
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_PAGE_TITLE", GetMessage("IBSEC_E_SEO_SECTION_PAGE_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_PAGE_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_SECTION_OVERWRITE"))
);
?>
<?
$tabControl->AddSection("IPROPERTY_TEMPLATES_ELEMENT", GetMessage("IBSEC_E_SEO_FOR_ELEMENTS"));
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_META_TITLE", GetMessage("IBSEC_E_SEO_META_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_META_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_META_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_META_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS", GetMessage("IBSEC_E_SEO_META_KEYWORDS"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_META_KEYWORDS",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_META_KEYWORDS", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION", GetMessage("IBSEC_E_SEO_META_DESCRIPTION"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_META_DESCRIPTION",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_META_DESCRIPTION", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE", GetMessage("IBSEC_E_SEO_PAGE_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_PAGE_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_PAGE_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->AddSection("IPROPERTY_TEMPLATES_SECTIONS_PICTURE", GetMessage("IBSEC_E_SEO_FOR_SECTIONS_PICTURE"));
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_ALT", GetMessage("IBSEC_E_SEO_FILE_ALT"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_ALT",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_TITLE", GetMessage("IBSEC_E_SEO_FILE_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_NAME", GetMessage("IBSEC_E_SEO_FILE_NAME"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_PICTURE_FILE_NAME",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->AddSection("IPROPERTY_TEMPLATES_SECTIONS_DETAIL_PICTURE", GetMessage("IBSEC_E_SEO_FOR_SECTIONS_DETAIL_PICTURE"));
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_ALT", GetMessage("IBSEC_E_SEO_FILE_ALT"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_ALT",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_TITLE", GetMessage("IBSEC_E_SEO_FILE_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_NAME", GetMessage("IBSEC_E_SEO_FILE_NAME"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "SECTION_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_SECTION_DETAIL_PICTURE_FILE_NAME",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "SECTION_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "S", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->AddSection("IPROPERTY_TEMPLATES_ELEMENTS_PREVIEW_PICTURE", GetMessage("IBSEC_E_SEO_FOR_ELEMENTS_PREVIEW_PICTURE"));
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT", GetMessage("IBSEC_E_SEO_FILE_ALT"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_PREVIEW_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_ALT",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_PREVIEW_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE", GetMessage("IBSEC_E_SEO_FILE_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_PREVIEW_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_PREVIEW_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME", GetMessage("IBSEC_E_SEO_FILE_NAME"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_PREVIEW_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_PREVIEW_PICTURE_FILE_NAME",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_PREVIEW_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->AddSection("IPROPERTY_TEMPLATES_ELEMENTS_DETAIL_PICTURE", GetMessage("IBSEC_E_SEO_FOR_ELEMENTS_DETAIL_PICTURE"));
$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT", GetMessage("IBSEC_E_SEO_FILE_ALT"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_ALT",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_DETAIL_PICTURE_FILE_ALT", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE", GetMessage("IBSEC_E_SEO_FILE_TITLE"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_TITLE",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_DETAIL_PICTURE_FILE_TITLE", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->BeginCustomField("IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME", GetMessage("IBSEC_E_SEO_FILE_NAME"));
?>
<tr class="adm-detail-valign-top">
	<td width="40%"><?echo $tabControl->GetCustomLabelHTML()?></td>
	<td width="60%"><?echo IBlockInheritedPropertyInput($IBLOCK_ID, "ELEMENT_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))?></td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_TEMPLATES_ELEMENT_DETAIL_PICTURE_FILE_NAME",
	IBlockInheritedPropertyHidden($IBLOCK_ID, "ELEMENT_DETAIL_PICTURE_FILE_NAME", $str_IPROPERTY_TEMPLATES, "E", GetMessage("IBSEC_E_SEO_ELEMENT_OVERWRITE"))
);

$tabControl->AddSection("IPROPERTY_TEMPLATES_MANAGEMENT", GetMessage("IBSEC_E_SEO_MANAGEMENT"));
$tabControl->BeginCustomField("IPROPERTY_CLEAR_VALUES", GetMessage("IBSEC_E_SEO_CLEAR_VALUES"));
?>
<tr>
	<td width="40%"><label for="IPROPERTY_CLEAR_VALUES"><?echo $tabControl->GetCustomLabelHTML()?></label></td>
	<td width="60%">
		<input type="checkbox" id="IPROPERTY_CLEAR_VALUES" name="IPROPERTY_CLEAR_VALUES" value="Y" />
	</td>
</tr>
<?
$tabControl->EndCustomField("IPROPERTY_CLEAR_VALUES",
	'<input type="hidden" name="IPROPERTY_CLEAR_VALUES" value="N">'
);

$tabControl->BeginNextFormTab();

$tabControl->AddEditField("SORT", GetMessage("IBLOCK_FIELD_SORT").":", false, array("size" => 7, "maxlength" => 10), $str_SORT);

if(COption::GetOptionString("iblock", "show_xml_id", "N")=="Y")
{
	$tabControl->AddEditField(
		"XML_ID",
		GetMessage("IBLOCK_FIELD_XML_ID").":",
		$arIBlock["FIELDS"]["SECTION_XML_ID"]["IS_REQUIRED"] === "Y",
		array("size" => 70, "maxlength" => 255),
		$str_XML_ID
	);
}

if($arTranslit["TRANSLITERATION"] != "Y")
{
	$tabControl->AddEditField("CODE", GetMessage("IBLOCK_FIELD_CODE").":", $arIBlock["FIELDS"]["SECTION_CODE"]["IS_REQUIRED"] === "Y", array("size" => 70, "maxlength" => 255), $str_CODE);
}

$tabControl->BeginCustomField("DETAIL_PICTURE", GetMessage("IBLOCK_FIELD_DETAIL_PICTURE").":", $arIBlock["FIELDS"]["SECTION_DETAIL_PICTURE"]["IS_REQUIRED"] === "Y");
if($bVarsFromForm && !array_key_exists("DETAIL_PICTURE", $_REQUEST) && $arSection)
	$str_DETAIL_PICTURE = intval($arSection["DETAIL_PICTURE"]);
?>
	<tr class="adm-detail-file-row">
		<td><?echo $tabControl->GetCustomLabelHTML()?></td>
		<td><?
			echo \Bitrix\Main\UI\FileInput::createInstance(array(
				"name" => "DETAIL_PICTURE",
				"description" => true,
				"upload" => true,
				"allowUpload" => "I",
				"medialib" => true,
				"fileDialog" => true,
				"cloud" => true,
				"delete" => true,
				"maxCount" => 1
			))->show(($bVarsFromForm ? $_REQUEST["DETAIL_PICTURE"] : $str_DETAIL_PICTURE), $bVarsFromForm);
			?>
		</td>
	</tr>
<?
$tabControl->EndCustomField("DETAIL_PICTURE", '');

//Add user fields tab only when there is fields defined or user has rights for adding new field
$entity_id = "IBLOCK_".$IBLOCK_ID."_SECTION";
if(
	(!empty($USER_FIELD_MANAGER->GetUserFields($entity_id))) ||
	($USER_FIELD_MANAGER->GetRights($entity_id) >= "W")
)
{
	$tabControl->BeginNextFormTab();

	if($USER_FIELD_MANAGER->GetRights($entity_id) >= "W")
	{
		$tabControl->BeginCustomField("USER_FIELDS_ADD", GetMessage("IBSEC_E_USER_FIELDS_ADD_HREF"));
		$userFieldUrl = $selfFolderUrl."userfield_edit.php?lang=".LANGUAGE_ID."&ENTITY_ID=".urlencode($entity_id);
		$userFieldUrl = $adminSidePanelHelper->editUrlToPublicPage($userFieldUrl);
		$userFieldUrl .= "&back_url=".urlencode($APPLICATION->GetCurPageParam('', array('bxpublic'))."&tabControl_active_tab=user_fields_tab");

		?>
			<tr>
				<td align="left" colspan="2">
					<a target="_top" href="<?=$userFieldUrl?>"><?= $tabControl->GetCustomLabelHTML()?></a>
				</td>
			</tr>
		<?
		$tabControl->EndCustomField("USER_FIELDS_ADD", '');
	}

	$arUserFields = $USER_FIELD_MANAGER->GetUserFields($entity_id, $ID, LANGUAGE_ID);
	foreach($arUserFields as $FIELD_NAME => $arUserField)
	{
		$arUserField["VALUE_ID"] = intval($ID);
		$strLabel = $arUserField["EDIT_FORM_LABEL"]?: $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = $strLabel;

		$tabControl->BeginCustomField($FIELD_NAME, $strLabel, $arUserField["MANDATORY"]=="Y");
			echo $USER_FIELD_MANAGER->GetEditFormHTML($bVarsFromForm, $GLOBALS[$FIELD_NAME] ?? '', $arUserField);


		$form_value = $GLOBALS[$FIELD_NAME] ?? '';
		if(!$bVarsFromForm)
			$form_value = $arUserField["VALUE"];
		elseif($arUserField["USER_TYPE"]["BASE_TYPE"]=="file")
			$form_value = $GLOBALS[$arUserField["FIELD_NAME"]."_old_id"];

		$hidden = IBlockGetHiddenHTML($FIELD_NAME, $form_value);

		$tabControl->EndCustomField($FIELD_NAME, $hidden);
	}
}

if($bEditRights)
{
	$tabControl->BeginNextFormTab();

	if($ID > 0)
	{
		$obSectionRights = new CIBlockSectionRights($IBLOCK_ID, $ID);
		$htmlHidden = '';
		foreach($obSectionRights->GetRights() as $RIGHT_ID => $arRight)
			$htmlHidden .= '
				<input type="hidden" name="RIGHTS[][RIGHT_ID]" value="'.htmlspecialcharsbx($RIGHT_ID).'">
				<input type="hidden" name="RIGHTS[][GROUP_CODE]" value="'.htmlspecialcharsbx($arRight["GROUP_CODE"]).'">
				<input type="hidden" name="RIGHTS[][TASK_ID]" value="'.htmlspecialcharsbx($arRight["TASK_ID"]).'">
			';
	}
	else
	{
		$obSectionRights = new CIBlockSectionRights($IBLOCK_ID, $str_IBLOCK_SECTION_ID);
		$htmlHidden = '';
	}

	$tabControl->BeginCustomField("RIGHTS", GetMessage("IBSEC_E_RIGHTS_FIELD"));
		IBlockShowRights(
			'section',
			$IBLOCK_ID,
			$ID,
			GetMessage("IBSEC_E_RIGHTS_SECTION_TITLE"),
			"RIGHTS",
			$obSectionRights->GetRightsList(),
			$obSectionRights->GetRights(array("count_overwrited" => true, "parent" => $str_IBLOCK_SECTION_ID)),
			true, /*$bForceInherited=*/($ID <= 0)
		);
	$tabControl->EndCustomField("RIGHTS", $htmlHidden);
}

if($arIBlock["SECTION_PROPERTY"] === "Y")
{
	$editor = new CEditorPopupControl();
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField("SECTION_PROPERTY", GetMessage("IBSEC_E_SECTION_PROPERTY_FIELD"));
	?>
		<tr><td align="right" colspan="2">
				<a id="modeChangeToTree" href="javascript:setMode(BX('table_SECTION_PROPERTY'), 'tree')"><?echo GetMessage("IBSEC_E_PROP_TREE_MODE");?></a>
				<a id="modeChangeToFlat" style="display: none;" href="javascript:setMode(BX('table_SECTION_PROPERTY'), 'flat')"><?echo GetMessage("IBSEC_E_PROP_FLAT_MODE");?></a>
		</td></tr>
		<tr><td align="center" colspan="2">
			<? echo $editor->getEditorHtml(); ?>
			<table class="internal" id="table_SECTION_PROPERTY" style="border-left: none !important; border-right: none !important;">
			<tr
				id="tr_HEADING"
				class="heading"
				mode="flat"
				prop_sort="-1"
				prop_id="0"
				left_margin="-1"
				>
				<td class="internal-left"><?echo GetMessage("IBSEC_E_PROP_TABLE_NAME");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_CODE");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_TYPE");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_SMART_FILTER");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_DISPLAY_TYPE");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_DISPLAY_EXPANDED");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_FILTER_HINT");?></td>
				<td class="internal-right"><?echo GetMessage("IBSEC_E_PROP_TABLE_ACTION");?></td>
			</tr>
			<?
			if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit"))
				$arShadow = $arHidden = array(
					-1 => GetMessage("IBSEC_E_PROP_SELECT_CHOOSE"),
					0 => GetMessage("IBSEC_E_PROP_SELECT_CREATE"),
				);
			else
				$arShadow = $arHidden = array(
					-1 => GetMessage("IBSEC_E_PROP_SELECT_CHOOSE"),
				);

			$arPropLinks = CIBlockSectionPropertyLink::GetArray($arIBlock["ID"], $ID, $ID <= 0);
			$arParents = array();
			foreach ($arPropLinks as $prop_id => $arLink)
			{
				if (!isset($arParents[$arLink["INHERITED_FROM"]]))
				{
					$arParents[$arLink["INHERITED_FROM"]] = array(
						"ID" => $arLink["INHERITED_FROM"],
						"TITLE" => $arLink["LINK_TITLE"],
						"LEFT_MARGIN" => $arLink["LEFT_MARGIN"],
					);

				}
			}

			\Bitrix\Main\Type\Collection::sortByColumn(
				$arParents,
				'LEFT_MARGIN',
				'',
				null,
				true
			);

			$maxMargin = 0;
			foreach ($arParents as $parent)
			{
				$maxMargin = $parent["LEFT_MARGIN"];
			?>
				<tr
					id="tr_PARENT_<?echo $parent["ID"]?>"
					mode="tree"
					prop_sort="0"
					prop_id="0"
					left_margin="<?echo $parent["LEFT_MARGIN"];?>"
					style="display:none; border:none;"
					>
					<td align="left" colspan="4" style="background-color:#f5f9f9; border: none;">
						<?if ($parent["ID"] > 0)
						{
							$localUrl = CIBlock::GetAdminSectionEditLink(
								$IBLOCK_ID,
								$parent["ID"],
								array(
									"form_section_".$IBLOCK_ID."_active_tab" => "edit4",
									"replace_script_name" => true
								)
							);
							if (!$adminSidePanelHelper->isPublicFrame())
								$localUrl = $adminSidePanelHelper->setDefaultQueryParams($localUrl);
							?>
							<br><br><a href="<?=$localUrl; ?>"><? echo htmlspecialcharsex($parent["TITLE"]) ?></a>
							<?
						}
						else
						{
							$localUrl = CIBlock::GetAdminIBlockEditLink(
								$IBLOCK_ID,
								array(
									"form_catalog_edit_".$IBLOCK_ID."_active_tab" => "edit3",
									"replace_script_name" => true
								)
							);
							if (!$adminSidePanelHelper->isPublicFrame())
								$localUrl = $adminSidePanelHelper->setDefaultQueryParams($localUrl);
							?>
							<br><br><a href="<?=$localUrl; ?>"><?echo htmlspecialcharsex($parent["TITLE"])?></a>
						<?
						}
						?>
					</td>
				</tr>
				<tr
					id="tr_PARENTHEADER_<?echo $parent["ID"]?>"
					class="heading"
					mode="tree"
					prop_sort="1"
					prop_id="-1"
					left_margin="<?echo $parent["LEFT_MARGIN"];?>"
					style="display:none"
					>
					<td class="internal-left"><?echo GetMessage("IBSEC_E_PROP_TABLE_NAME");?></td>
					<td><?echo GetMessage("IBSEC_E_PROP_TABLE_CODE");?></td>
					<td><?echo GetMessage("IBSEC_E_PROP_TABLE_TYPE");?></td>
					<td><?echo GetMessage("IBSEC_E_PROP_TABLE_SMART_FILTER");?></td>
					<td><?echo GetMessage("IBSEC_E_PROP_TABLE_DISPLAY_TYPE");?></td>
					<td><?echo GetMessage("IBSEC_E_PROP_TABLE_DISPLAY_EXPANDED");?></td>
					<td><?echo GetMessage("IBSEC_E_PROP_TABLE_FILTER_HINT");?></td>
					<td class="internal-right"><?echo GetMessage("IBSEC_E_PROP_TABLE_ACTION");?></td>
				</tr>
			<?
			}

			$rsProps =  CIBlockProperty::GetList(array("SORT"=>"ASC",'ID' => 'ASC'), array("IBLOCK_ID" => $arIBlock["ID"], "CHECK_PERMISSIONS" => "N", "ACTIVE"=>"Y"));
			$rows = 0;
			$maxSort = 0;
			while ($arProp = $rsProps->Fetch()):
				$arProp['CODE'] = (string)$arProp['CODE'];
				$propName = ($arProp['CODE'] != '' ? '['.$arProp['CODE'].'] ' : '').$arProp['NAME'];
				$maxSort = $arProp["SORT"];
				if(array_key_exists($arProp["ID"], $arPropLinks))
				{
					$rows++;
					$arLink = $arPropLinks[$arProp["ID"]];
					if($arLink["INHERITED"] == "N")
						$arShadow[$arProp["ID"]] = $propName;
				}
				else
				{
					$arLink = false;
					$arHidden[$arProp["ID"]] = $propName;
					$arShadow[$arProp["ID"]] = $propName;
				}
				unset($propName);
			?>
			<tr
				id="tr_SECTION_PROPERTY_<?echo $arProp["ID"]?>"
				mode="<?echo (is_array($arLink)? "both": "none");?>"
				prop_sort="<?echo $arProp["SORT"]?>"
				prop_id="<?echo $arProp["ID"]?>"
				left_margin="<?echo is_array($arLink)? $arLink["LEFT_MARGIN"]: $maxMargin;?>"
				<?if(!is_array($arLink)) echo 'style="display:none"';?>
				>
				<td align="left" class="internal-left">
					<?if(!is_array($arLink) || $arLink["INHERITED"] == "N"):?>
					<input type="hidden" name="SECTION_PROPERTY[<?echo $arProp["ID"]?>][SHOW]" id="hidden_SECTION_PROPERTY_<?echo $arProp["ID"]?>" value="<?echo is_array($arLink)? "Y": "N";?>">
					<?endif?>
					<?echo htmlspecialcharsex($arProp["NAME"])?>
				</td>
				<td align="left"><?echo htmlspecialcharsex($arProp["CODE"])?></td>
				<td align="left"><?
					if($arProp['PROPERTY_TYPE'] == "S" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_S");
					elseif($arProp['PROPERTY_TYPE'] == "N" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_N");
					elseif($arProp['PROPERTY_TYPE'] == "L" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_L");
					elseif($arProp['PROPERTY_TYPE'] == "F" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_F");
					elseif($arProp['PROPERTY_TYPE'] == "G" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_G");
					elseif($arProp['PROPERTY_TYPE'] == "E" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_E");
					elseif($arProp['USER_TYPE'] && is_array($ar = CIBlockProperty::GetUserType($arProp['USER_TYPE'])))
						echo htmlspecialcharsex($ar["DESCRIPTION"]);
					else
						echo GetMessage("IBSEC_E_PROP_TYPE_S");
				?></td>
				<td style="text-align:center"><?
					echo '<input type="checkbox" value="Y" '.((is_array($arLink) && $arLink["INHERITED"] === "Y")? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][SMART_FILTER]" '.(is_array($arLink) && $arLink["SMART_FILTER"] === "Y"? 'checked="checked"': '').'>';
				?></td>
				<td>
					<?
					$displayTypes = CIBlockSectionPropertyLink::getDisplayTypes($arProp["PROPERTY_TYPE"], $arProp["USER_TYPE"]);
					if ($displayTypes)
					{
						$displayType = null;
						$field1 = '';

						if (is_array($arLink))
						{
							$displayType = $arLink["DISPLAY_TYPE"];
							$field1 = $arLink["INHERITED"] === "Y" ? 'disabled="disabled"' : '';
						}

						echo SelectBoxFromArray(
							'SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_TYPE]',
							[
								"REFERENCE_ID" => array_keys($displayTypes),
								"REFERENCE" => array_values($displayTypes),
							],
							$displayType,
							'',
							$field1
						);
					}
					else
					{
						echo '&nbsp;';
					}
					?>
				</td>
				<td style="text-align:center"><?
					echo '<input type="checkbox" value="Y" '.((is_array($arLink) && $arLink["INHERITED"] == "Y")? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_EXPANDED]" '.(is_array($arLink) && $arLink["DISPLAY_EXPANDED"] == "Y"? 'checked="checked"': '').'>';
				?></td>
				<td>
				<?
					if (!is_array($arLink) || $arLink["INHERITED"] == "N")
					{
						$filterHint = (is_array($arLink) ? (string)$arLink['FILTER_HINT'] : '');
						echo $editor->getControlHtml('SECTION_PROPERTY['.$arProp['ID'].'][FILTER_HINT]', $filterHint, 255);
					}
					elseif ($arLink['FILTER_HINT'] <> '')
					{
						echo CTextParser::closeTags($arLink['FILTER_HINT']);
					}
					else
					{
						echo '&nbsp;';
					}
				?></td>
				<td align="left" class="internal-right"><?
					if(!is_array($arLink) || $arLink["INHERITED"] == "N")
						echo '<a class="bx-action-href" href="javascript:deleteSectionProperty('.$arProp['ID'].', \'select_SECTION_PROPERTY\', \'shadow_SECTION_PROPERTY\', \'table_SECTION_PROPERTY\')">'.GetMessage("IBSEC_E_PROP_TABLE_ACTION_HIDE").'</a>';
					else
						echo '&nbsp;';
				?></td>
			</tr>
			<?endwhile?>
			<tr
				id="tr_FOOTER"
				mode="<?echo ($rows == 0)? 'both': 'none';?>"
				prop_sort="<?echo $maxSort + 1?>"
				prop_id="0"
				left_margin="<?echo $maxMargin + 1?>"
				<?echo ($rows == 0)? '': 'style="display:none"';?>
				>
				<td align="center" colspan="8">
					<?echo GetMessage("IBSEC_E_PROP_TABLE_EMPTY")?>
				</td>
			</tr>
			</table><br>
			<select id="shadow_SECTION_PROPERTY" style="display:none">
			<?foreach($arShadow as $key => $value):?>
				<option value="<?echo htmlspecialcharsex($key)?>"><?echo htmlspecialcharsbx($value)?></option>
			<?endforeach?>
			</select>
			<select id="select_SECTION_PROPERTY">
			<?foreach($arHidden as $key => $value):?>
				<option value="<?echo htmlspecialcharsex($key)?>"><?echo htmlspecialcharsbx($value)?></option>
			<?endforeach?>
			</select>
			<input type="button" value="<?echo GetMessage("IBSEC_E_PROP_TABLE_ACTION_ADD")?>" onclick="javascript:addSectionProperty(<?echo $arIBlock["ID"];?>, 'select_SECTION_PROPERTY', 'shadow_SECTION_PROPERTY', 'table_SECTION_PROPERTY')">
			<script type="text/javascript">
			<?echo CIBlockSectionPropertyLink::getDisplayTypesJsFunction();?>
			var last_mode = 'flat';
			var target_id = '';
			var target_select_id = '';
			var target_shadow_id = '';
			function addSectionProperty(iblock_id, select_id, shadow_id, table_id)
			{
				var select = BX(select_id);
				if(select && select.value > 0)
				{
					var hidden = BX('hidden_SECTION_PROPERTY_' + select.value);
					var tr = BX('tr_SECTION_PROPERTY_' + select.value);
					if(hidden && tr)
					{
						jsSelectUtils.deleteOption(select_id, select.value);
						hidden.value = 'Y';
						tr.style.display = 'table-row';
						tr.setAttribute('mode', 'both');
						animateTR(tr);
					}
					adjustEmptyTR(table_id);
				}

				if(select && select.value == 0)
				{
					target_id = table_id;
					target_select_id = select_id;
					target_shadow_id = shadow_id;
					(new BX.CAdminDialog({
						'content_url' : '<?=$selfFolderUrl?>iblock_edit_property.php?lang=<?echo LANGUAGE_ID?>&IBLOCK_ID='+iblock_id+'&ID=n0&bxpublic=Y&from_module=iblock&return_url=section_edit',
						'width' : 700,
						'height' : 400,
						'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
					})).Show();
				}
			}
			function animateTR(tr)
			{
				var tds = BX.findChildren(tr, {tag:'td'}, true);
				BX.fx.colorAnimate.addRule('animationRule',"#F8F9FC","#faeeb4", "background-color", 50, 1, true);
				for(var i = 0; i < tds.length; i++)
					BX.fx.colorAnimate(tds[i], 'animationRule');
			}
			function deleteSectionProperty(id, select_id, shadow_id, table_id)
			{
				var hidden = BX('hidden_SECTION_PROPERTY_' + id);
				var tr = BX('tr_SECTION_PROPERTY_' + id);
				if(hidden && tr)
				{
					hidden.value = 'N';
					tr.style.display = 'none';
					tr.setAttribute('mode', 'none');
					var select = BX(select_id);
					var shadow = BX(shadow_id);
					if(select && shadow)
					{
						jsSelectUtils.deleteAllOptions(select);
						for(var i = 0; i < shadow.length; i++)
						{
							if(shadow[i].value <= 0)
								jsSelectUtils.addNewOption(select, shadow[i].value, shadow[i].text);
							else if (BX('hidden_SECTION_PROPERTY_' + shadow[i].value).value == 'N')
								jsSelectUtils.addNewOption(select, shadow[i].value, shadow[i].text);
						}
					}
					adjustEmptyTR(table_id);
				}
			}
			function createSectionProperty(id, name, type, sortnum, property_type, user_type, code)
			{
				var tbl = BX(target_id);
				if(tbl)
				{
					var cnt = tbl.rows.length;
					var row = tbl.insertRow(cnt-1);
					//row.vAlign = 'top';
					row.id = 'tr_SECTION_PROPERTY_' + id;
					row.setAttribute('mode', 'both');
					row.setAttribute('prop_sort', sortnum);
					row.setAttribute('prop_id', id);
					row.setAttribute('left_margin', <?echo intval($str_LEFT_MARGIN)?>);

					var cell, c = 0;

					row.insertCell(-1);
					cell = row.cells[c];
					cell.align = 'left';
					cell.innerHTML = '<input type="hidden" name="SECTION_PROPERTY['+id+'][SHOW]" id="hidden_SECTION_PROPERTY_'+id+'" value="Y">'+BX.util.htmlspecialchars(name);
					cell.className = 'internal-left';

					row.insertCell(-1);
					cell = row.cells[++c];
					cell.align = 'left';
					cell.innerHTML = code;

					row.insertCell(-1);
					cell = row.cells[++c];
					cell.align = 'left';
					cell.innerHTML = type;

					row.insertCell(-1);
					cell = row.cells[++c];
					cell.align = 'center';
					cell.style.textAlign = 'center';
					cell.innerHTML = '<input type="checkbox" value="Y" name="SECTION_PROPERTY['+id+'][SMART_FILTER]">';

					row.insertCell(-1);
					cell = row.cells[++c];
					var displayTypes = getDisplayTypes(property_type, user_type);
					if (!displayTypes)
					{
						cell.innerHTML = '&nbsp;';
					}
					else
					{
						var select = BX.create('select', {
							'props': {
								'name': 'SECTION_PROPERTY[' + id + '][DISPLAY_TYPE]'
							}
						});
						for (var x in displayTypes)
						{
							if (displayTypes.hasOwnProperty(x))
							{
								jsSelectUtils.addNewOption(select, x, displayTypes[x], false, false);
							}
						}
						cell.appendChild(select);
					}

					row.insertCell(-1);
					cell = row.cells[++c];
					cell.align = 'center';
					cell.style.textAlign = 'center';
					cell.innerHTML = '<input type="checkbox" value="Y" name="SECTION_PROPERTY['+id+'][DISPLAY_EXPANDED]">';

					row.insertCell(-1);
					cell = row.cells[++c];
					cell.innerHTML = '<?echo CUtil::JSEscape($editor->getControlHtml('SECTION_PROPERTY[#ID#][FILTER_HINT]', '', 255))?>'.replace('#ID#', id) || '&nbsp;';

					row.insertCell(-1);
					cell = row.cells[++c];
					cell.align = 'left';
					cell.className = 'internal-right';
					cell.innerHTML = '<a class="bx-action-href" href="javascript:deleteSectionProperty('+id+', \''+target_select_id+'\', \''+target_shadow_id+'\', \''+target_id+'\')"><?echo GetMessageJS("IBSEC_E_PROP_TABLE_ACTION_HIDE")?></a>';

					setMode(tbl, last_mode);
					animateTR(row);
					var shadow = BX(target_shadow_id);
					if(shadow)
						jsSelectUtils.addNewOption(shadow, id, name);
					//adjustEmptyTR(target_id);
					BX.adminPanel.modifyFormElements(row);
				}
			}
			function adjustEmptyTR(table_id)
			{
				var tbl = BX(table_id);
				if(tbl)
				{
					var cnt = tbl.rows.length;
					var tr = tbl.rows[cnt-1];

					var display = 'table-row';
					for(var i = 1; i < cnt-1; i++)
					{
						if(tbl.rows[i].style.display != 'none')
							display = 'none';
					}
					tr.style.display = display;
				}
			}
			function setMode(table, mode)
			{
				for (var i = 0; i < table.rows.length; i++)
				{
					var tr = table.rows[i];
					var trMode = tr.getAttribute('mode');
					if (!trMode)
					{
						return;
					}

					if (trMode == 'both' || trMode == mode)
						tr.style.display = 'table-row';
					else
						tr.style.display = 'none';
				}

				if (mode == 'tree')
				{
					BX('modeChangeToTree').style.display = 'none';
					BX('modeChangeToFlat').style.display = 'block';
					sortPropsTable(table, compareRowsByMargin);
				}
				else
				{
					BX('modeChangeToFlat').style.display = 'none';
					BX('modeChangeToTree').style.display = 'block';
					sortPropsTable(table, compareRowsBySort);
				}
				jsUserOptions.SaveOption('iblock', 'section_property', 'mode', mode);
				last_mode = mode;
			}
			function compareRowsByMargin(a, b)
			{
				var sortA = parseInt(a.getAttribute('left_margin'));
				var sortB = parseInt(b.getAttribute('left_margin'));
				if (sortA > sortB)
					return 1;
				else if (sortA < sortB)
					return -1;
				else
					return compareRowsBySort(a, b);
			}
			function compareRowsBySort(a, b)
			{
				var sortA = parseInt(a.getAttribute('prop_sort'));
				var sortB = parseInt(b.getAttribute('prop_sort'));
				if (sortA > sortB)
					return 1;
				else if (sortA < sortB)
					return -1;
				else
					return compareRowsByPropId(a, b);
			}
			function compareRowsByPropId(a, b)
			{
				var sortA = parseInt(a.getAttribute('prop_id'));
				var sortB = parseInt(b.getAttribute('prop_id'));
				if (sortA > sortB)
					return 1;
				else if (sortA < sortB)
					return -1;
				else
					return 0;
			}
			function sortPropsTable(table, compareFunction)
			{
				for (var j = 1; j <= table.rows.length - 1; j++)
				{
					for (var i = 1; i <= table.rows.length - j; i++)
					{
						if (compareFunction(table.rows[i-1], table.rows[i]) > 0)
						{
							table.tBodies[0].insertBefore(table.rows[i], table.rows[i-1]);
						}
					}
				}
			}
			<?
			$userSettings = CUserOptions::getOption('iblock', 'section_property');
			if (isset($userSettings["mode"]) && $userSettings["mode"] === "tree"):
			?>
			BX.ready(function(){
				setMode(BX('table_SECTION_PROPERTY'), 'tree');
			});
			<?endif;?>
			</script>
		</td></tr>

		<?
		$arCatalog = false;
		if (Loader::includeModule("catalog"))
			$arCatalog = CCatalogSku::GetInfoByProductIBlock($IBLOCK_ID);

		if (is_array($arCatalog))
		{
		?>
		<tr class="heading">
			<td align="center" colspan="3"><?echo GetMessage("IBSEC_E_PROP_SKU_SECTION");?></td>
		</tr>
		<tr><td align="center" colspan="2">
			<table class="internal" id="table_SKU_SECTION_PROPERTY">
			<tr class="heading">
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_NAME");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_CODE");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_TYPE");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_SMART_FILTER");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_DISPLAY_TYPE");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_DISPLAY_EXPANDED");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_FILTER_HINT");?></td>
				<td><?echo GetMessage("IBSEC_E_PROP_TABLE_ACTION");?></td></tr>
			<?
			if(CIBlockRights::UserHasRightTo($arCatalog["IBLOCK_ID"], $arCatalog["IBLOCK_ID"], "iblock_edit"))
				$arShadow = $arHidden = array(
					-1 => GetMessage("IBSEC_E_PROP_SELECT_CHOOSE"),
					0 => GetMessage("IBSEC_E_PROP_SELECT_CREATE"),
				);
			else
				$arShadow = $arHidden = array(
					-1 => GetMessage("IBSEC_E_PROP_SELECT_CHOOSE"),
				);

			$arPropLinks = CIBlockSectionPropertyLink::GetArray($arCatalog["IBLOCK_ID"], $ID);
			$rsProps =  CIBlockProperty::GetList(array(
					"SORT"=>"ASC",
					'ID' => 'ASC',
				), array(
					"IBLOCK_ID" => $arCatalog["IBLOCK_ID"],
					"CHECK_PERMISSIONS" => "N",
					"ACTIVE"=>"Y",
				));
			$rows = 0;
			while ($arProp = $rsProps->Fetch()):

				if($arProp["ID"] == $arCatalog["SKU_PROPERTY_ID"])
					continue;
				$arProp['CODE'] = (string)$arProp['CODE'];
				$propName = ($arProp['CODE'] != '' ? '['.$arProp['CODE'].'] ' : '').$arProp['NAME'];
				if(array_key_exists($arProp["ID"], $arPropLinks))
				{
					$rows++;
					$arLink = $arPropLinks[$arProp["ID"]];
					if($arLink["INHERITED"] == "N")
						$arShadow[$arProp["ID"]] = $propName;
				}
				else
				{
					$arLink = false;
					$arHidden[$arProp["ID"]] = $propName;
					$arShadow[$arProp["ID"]] = $propName;
				}
				unset($propName);
			?>
			<tr id="tr_SECTION_PROPERTY_<?echo $arProp["ID"]?>" <?if(!is_array($arLink)) echo 'style="display:none"';?>>
				<td align="left">
					<?if(!is_array($arLink) || $arLink["INHERITED"] == "N"):?>
					<input type="hidden" name="SECTION_PROPERTY[<?echo $arProp["ID"]?>][SHOW]" id="hidden_SECTION_PROPERTY_<?echo $arProp["ID"]?>" value="<?echo is_array($arLink)? "Y": "N";?>">
					<?endif?>
					<?echo htmlspecialcharsex($arProp["NAME"])?>
				</td>
				<td align="left"><?echo htmlspecialcharsex($arProp["CODE"])?></td>
				<td align="left"><?
					if($arProp['PROPERTY_TYPE'] == "S" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_S");
					elseif($arProp['PROPERTY_TYPE'] == "N" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_N");
					elseif($arProp['PROPERTY_TYPE'] == "L" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_L");
					elseif($arProp['PROPERTY_TYPE'] == "F" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_F");
					elseif($arProp['PROPERTY_TYPE'] == "G" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_G");
					elseif($arProp['PROPERTY_TYPE'] == "E" && !$arProp['USER_TYPE'])
						echo GetMessage("IBLOCK_PROP_E");
					elseif($arProp['USER_TYPE'] && is_array($ar = CIBlockProperty::GetUserType($arProp['USER_TYPE'])))
						echo htmlspecialcharsex($ar["DESCRIPTION"]);
					else
						echo GetMessage("IBSEC_E_PROP_TYPE_S");
				?></td>
				<td align="center"><?
					echo '<input type="checkbox" value="Y" '.((is_array($arLink) && $arLink["INHERITED"] === "Y")? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][SMART_FILTER]" '.(is_array($arLink) && $arLink["SMART_FILTER"] === "Y"? 'checked="checked"': '').'>';
				?></td>
				<td>
					<?
					$displayTypes = CIBlockSectionPropertyLink::getDisplayTypes($arProp["PROPERTY_TYPE"], $arProp["USER_TYPE"]);
					if ($displayTypes)
					{
						$displayType = null;
						$field1 = '';

						if (is_array($arLink))
						{
							$displayType = $arLink["DISPLAY_TYPE"];
							$field1 = $arLink["INHERITED"] === "Y" ? 'disabled="disabled"' : '';
						}

						echo SelectBoxFromArray(
							'SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_TYPE]',
							[
								"REFERENCE_ID" => array_keys($displayTypes),
								"REFERENCE" => array_values($displayTypes),
							],
							$displayType,
							'',
							$field1
						);
					}
					else
					{
						echo '&nbsp;';
					}
					?>
				</td>
				<td align="center"><?
					echo '<input type="checkbox" value="Y" '.((is_array($arLink) && $arLink["INHERITED"] === "Y")? 'disabled="disabled"': '').' name="SECTION_PROPERTY['.$arProp['ID'].'][DISPLAY_EXPANDED]" '.(is_array($arLink) && $arLink["DISPLAY_EXPANDED"] === "Y"? 'checked="checked"': '').'>';
				?></td>
				<td><?
					if (!is_array($arLink) || $arLink["INHERITED"] == "N")
					{
						$filterHint = (is_array($arLink) ? (string)$arLink['FILTER_HINT'] : '');
						echo $editor->getControlHtml('SECTION_PROPERTY['.$arProp['ID'].'][FILTER_HINT]', $filterHint, 255);
					}
					elseif ($arLink['FILTER_HINT'] <> '')
					{
						echo CTextParser::closeTags($arLink['FILTER_HINT']);
					}
					else
					{
						echo '&nbsp;';
					}
				?></td>
				<td align="left"><?
					if(!is_array($arLink) || $arLink["INHERITED"] == "N")
						echo '<a class="bx-action-href" href="javascript:deleteSectionProperty('.$arProp['ID'].', \'select_SKU_SECTION_PROPERTY\', \'shadow_SKU_SECTION_PROPERTY\', \'table_SKU_SECTION_PROPERTY\')">'.GetMessage("IBSEC_E_PROP_TABLE_ACTION_HIDE").'</a>';
					else
						echo '&nbsp;';
				?></td>
			</tr>
			<?endwhile?>
			<tr <?echo ($rows == 0)? '': 'style="display:none"';?>>
				<td align="center" colspan="8">
					<?echo GetMessage("IBSEC_E_PROP_TABLE_EMPTY")?>
				</td>
			</tr>
			</table>
			<select id="shadow_SKU_SECTION_PROPERTY" style="display:none">
			<?foreach($arShadow as $key => $value):?>
				<option value="<?echo htmlspecialcharsex($key)?>"><?echo htmlspecialcharsbx($value)?></option>
			<?endforeach?>
			</select>
			<select id="select_SKU_SECTION_PROPERTY">
			<?foreach($arHidden as $key => $value):?>
				<option value="<?echo htmlspecialcharsex($key)?>"><?echo htmlspecialcharsbx($value)?></option>
			<?endforeach?>
			</select>
			<input type="button" value="<?echo GetMessage("IBSEC_E_PROP_TABLE_ACTION_ADD")?>" onclick="javascript:addSectionProperty(<?echo $arCatalog["IBLOCK_ID"];?>, 'select_SKU_SECTION_PROPERTY', 'shadow_SKU_SECTION_PROPERTY', 'table_SKU_SECTION_PROPERTY')">
		</td></tr>
			<?
		}
		?>
	<?
	$tabControl->EndCustomField("SECTION_PROPERTY", '');
}

if($return_url <> '')
	$bu = $return_url;
else
	$bu = $selfFolderUrl.CIBlock::GetAdminSectionListLink($IBLOCK_ID, array('find_section_section'=>intval($find_section_section)));

if ($adminSidePanelHelper->isSidePanelFrame()):
	$tabControl->Buttons(array("disabled" => false));
elseif (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1):
	$tabControl->Buttons(array(
		"disabled" => false,
		"btnSaveAndAdd" => !$bAutocomplete,
		"btnApply" => !$bAutocomplete,
		"btnCancel" => !$bAutocomplete,
		"back_url" => $bu,
	));
elseif($nobuttons !== "Y"):
	$save_and_add = "{
		title: '".CUtil::JSEscape(GetMessage("IBSEC_E_SAVE_AND_ADD"))."',
		name: 'save_and_add',
		id: 'save_and_add',
		className: 'adm-btn-add',
		action: function () {
			var FORM = this.parentWindow.GetForm();
			FORM.appendChild(BX.create('INPUT', {
				props: {
					type: 'hidden',
					name: 'save_and_add',
					value: 'Y'
				}
			}));

			this.parentWindow.hideNotify();
			this.disableUntilError();
			this.parentWindow.Submit();
		}
	}";
	$cancel = "{
		title: '".CUtil::JSEscape(GetMessage("admin_lib_edit_cancel"))."',
		name: 'cancel',
		id: 'cancel',
		action: function () {
			BX.WindowManager.Get().Close();
			if(window.reloadAfterClose)
				top.BX.reload(true);
		}
	}";
	$tabControl->ButtonsPublic(array(
		'.btnSave',
		$cancel,
		$save_and_add,
	));
endif;

$tabControl->Show();

$tabControl->ShowWarnings($tabControl->GetName(), $message);

if(CIBlockRights::UserHasRightTo($IBLOCK_ID, $IBLOCK_ID, "iblock_edit") && (!defined('BX_PUBLIC_MODE') || BX_PUBLIC_MODE != 1) && !$adminSidePanelHelper->isSidePanel())
{
	echo
		BeginNote(),
		GetMessage("IBSEC_E_IBLOCK_MANAGE_HINT"),
		' <a href="iblock_edit.php?type='.htmlspecialcharsbx($type).'&amp;lang='.LANGUAGE_ID.'&amp;ID='.$IBLOCK_ID.'&amp;admin=Y&amp;return_url='.urlencode(CIBlock::GetAdminSectionEditLink($IBLOCK_ID, $ID, array("find_section_section" => intval($find_section_section), "IBLOCK_SECTION_ID" => intval($find_section_section), "return_url" => $return_url <> ''? $return_url: null))).'">',
		GetMessage("IBSEC_E_IBLOCK_MANAGE_HINT_HREF"),
		'</a>',
		EndNote()
	;
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
