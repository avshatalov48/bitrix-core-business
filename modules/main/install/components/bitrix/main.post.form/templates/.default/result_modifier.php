<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var SocialnetworkBlogPostComment $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
/** @global CMain $APPLICATION */

__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php");

/********************************************************************
				Input params
 ********************************************************************/
/***************** BASE ********************************************/
$arParams["JS_OBJECT_NAME"] = "";
if (!empty($arParams["FORM_ID"]))
	$arParams["JS_OBJECT_NAME"] = "PlEditor".$arParams["FORM_ID"];
else
	$arParams["FORM_ID"] = "POST_FORM";

$arParams["LHE"] = (is_array($arParams['~LHE']) ? $arParams['~LHE'] : array());
$arParams["LHE"]["id"] = (empty($arParams["LHE"]["id"]) ? "idLHE_".$arParams["FORM_ID"] : $arParams["LHE"]["id"]);
$arParams["LHE"]["jsObjName"] = trim($arParams["LHE"]["jsObjName"]);
$arParams["divId"] = (empty($arParams["LHE"]["jsObjName"]) ? $arParams["LHE"]["id"] : $arParams["LHE"]["jsObjName"]);
$arParams["LHE"]["bInitByJS"] = (empty($arParams["TEXT"]["VALUE"]) && $arParams["LHE"]["bInitByJS"] === true ? true : false);
$arParams["LHE"]["lazyLoad"] = (empty($arParams["TEXT"]["VALUE"]) && ($arParams["LHE"]["bInitByJS"] === true || $arParams["LHE"]["lazyLoad"] === true) ? true : false);

$arParams["PARSER"] = array_unique(is_array($arParams["PARSER"]) ? $arParams["PARSER"] : array());
$arParams["BUTTONS"] = is_array($arParams["BUTTONS"]) ? $arParams["BUTTONS"] : array();
$arParams["BUTTONS"] = (in_array("MentionUser", $arParams["BUTTONS"]) && !IsModuleInstalled("socialnetwork") ?
	array_diff($arParams["BUTTONS"], array("MentionUser")) : $arParams["BUTTONS"]);
$arParams["BUTTONS"] = array_values($arParams["BUTTONS"]);
$arParams["BUTTONS_HTML"] = is_array($arParams["BUTTONS_HTML"]) ? $arParams["BUTTONS_HTML"] : array();

$arParams["TEXT"] = (is_array($arParams["~TEXT"]) ? $arParams["~TEXT"] : array());
$arParams["TEXT"]["ID"] = (!empty($arParams["TEXT"]["ID"]) ? $arParams["TEXT"]["ID"] : "POST_MESSAGE");
$arParams["TEXT"]["NAME"] = (!empty($arParams["TEXT"]["NAME"]) ? $arParams["TEXT"]["NAME"] : "POST_MESSAGE");
$arParams["TEXT"]["TABINDEX"] = intval($arParams["TEXT"]["TABINDEX"] <= 0 ? 10 : $arParams["TEXT"]["TABINDEX"]);
$arParams["TEXT"]["~SHOW"] = $arParams["TEXT"]["SHOW"];
$userOption = CUserOptions::GetOption("main.post.form", "postEdit");
if(isset($userOption["showBBCode"]) && $userOption["showBBCode"] == "Y")
	$arParams["TEXT"]["SHOW"] = "Y";

$arParams["PIN_EDITOR_PANEL"] = (isset($userOption["pinEditorPanel"]) && $userOption["pinEditorPanel"] == "Y") ? "Y" : "N";

$arParams["ADDITIONAL"] = (is_array($arParams["~ADDITIONAL"]) ? $arParams["~ADDITIONAL"] : array());

$arResult["SELECTOR_VERSION"] = (!empty($arParams["SELECTOR_VERSION"]) ? intval($arParams["SELECTOR_VERSION"]) : 1);

$addSpan = true;
if (!empty($arParams["ADDITIONAL"]))
{
	$res = reset($arParams["ADDITIONAL"]);
	$res = trim($res);
	$addSpan = (substr($res, 0, 1) == "<");
}
$arParams["ADDITIONAL_TYPE"] = ($addSpan ? "html" : "popup");
if ($arParams["TEXT"]["~SHOW"] != "Y")
{
	if ($addSpan)
	{
		array_unshift(
			$arParams["ADDITIONAL"],
			"<span ".
				"onclick=\"LHEPostForm.getHandler('".$arParams["LHE"]["id"]."').showPanelEditor();\" ".
				"class=\"feed-add-post-form-editor-btn".($arParams["TEXT"]["SHOW"] == "Y" ? " feed-add-post-form-btn-active" : "")."\" ".
				"id=\"lhe_button_editor_".$arParams["FORM_ID"]."\" ".
				"title=\"".GetMessage("MPF_EDITOR")."\"></span>");
	}
	else
	{
		$arParams["ADDITIONAL"][] =
			"{ text : '".GetMessage("MPF_EDITOR")."', onclick : function() {LHEPostForm.getHandler('".$arParams["LHE"]["id"]."').showPanelEditor(); this.popupWindow.close();}, className: 'blog-post-popup-menu', id: 'bx-html'}";
	}
}

/**
 * @var string $arParams["HTML_BEFORE_TEXTAREA"]
 * @var string $arParams["HTML_AFTER_TEXTAREA"]
 * @var array $arParams["UPLOAD_FILE"]
 * @var array $arParams["UPLOAD_WEBDAV_ELEMENT"]
 */
$arParams["UPLOADS_CID"] = array();
$arParams["UPLOADS_HTML"] = "";

$arParams["DESTINATION"] = (is_array($arParams["DESTINATION"]) && IsModuleInstalled("socialnetwork") ? $arParams["DESTINATION"] : array());
$arParams["DESTINATION_SHOW"] = (array_key_exists("SHOW", $arParams["DESTINATION"]) ? $arParams["DESTINATION"]["SHOW"] : $arParams["DESTINATION_SHOW"]);
$arParams["DESTINATION_SHOW"] = ($arParams["DESTINATION_SHOW"] == "Y" ? "Y" : "N");
$arParams["DESTINATION_USE_CLIENT_DATABASE"] = (
	array_key_exists("USE_CLIENT_DATABASE", $arParams["DESTINATION"])
		? $arParams["DESTINATION"]["USE_CLIENT_DATABASE"]
		: 'Y'
);
$arParams["DESTINATION"] = (array_key_exists("VALUE", $arParams["DESTINATION"]) ? $arParams["DESTINATION"]["VALUE"] : $arParams["DESTINATION"]);

$arResult["bExtranetUser"] = (
	\Bitrix\Main\Loader::includeModule("extranet")
	&& !CExtranet::IsIntranetUser()
);

if (!empty($arParams["DEST_SORT"]))
{
	$arResult["DEST_SORT"] = $arParams["DEST_SORT"];
}
elseif (
	$arResult["SELECTOR_VERSION"] < 2
	&& \Bitrix\Main\Loader::includeModule("socialnetwork")
	&& $USER->IsAuthorized()
)
{
	$arResult["DEST_SORT"] = CSocNetLogDestination::GetDestinationSort(array(
		"DEST_CONTEXT" => (isset($arParams["DEST_CONTEXT"]) ? $arParams["DEST_CONTEXT"] : false)
	));
}
else
{
	$arResult["DEST_SORT"] = array();
}

if (
	$arResult["SELECTOR_VERSION"] < 2
	&& empty($arParams["DESTINATION"])
	&& in_array("MentionUser", $arParams["BUTTONS"])
	&& CModule::IncludeModule("socialnetwork")
)
{
	$arStructure = CSocNetLogDestination::GetStucture(array("LAZY_LOAD" => true));
	$arParams["DESTINATION"] = array(
		'LAST' => array(
			'USERS' => array()
		),
		"DEPARTMENT" => $arStructure['department'],
		"DEPARTMENT_RELATION" => $arStructure['department_relation']
	);

	foreach($arResult["DEST_SORT"] as $code => $sortInfo)
	{
		if (preg_match('/^U(\d+)$/i', $code, $matches))
		{
			$arParams["DESTINATION"]['LAST']['USERS'][$code] = $code;
		}
	}

	if ($arResult["bExtranetUser"])
	{
		$arParams["DESTINATION"]['EXTRANET_USER'] = 'Y';
		$arParams["DESTINATION"]['USERS'] = CSocNetLogDestination::GetExtranetUser();
	}
	else
	{
		$arDestUser = Array();
		foreach ($arParams["DESTINATION"]['LAST']['USERS'] as $value)
		{
			$arDestUser[] = str_replace('U', '', $value);
		}

		$arParams["DESTINATION"]['EXTRANET_USER'] = 'N';
		$arParams["DESTINATION"]['USERS'] = CSocNetLogDestination::GetUsers(Array('id' => $arDestUser));
	}
}

if (
	in_array("MentionUser", $arParams["BUTTONS"])
	&& $arResult["SELECTOR_VERSION"] < 2
)
{
	if (CModule::IncludeModule("socialnetwork"))
	{
		$arResult["MENTION_DEST_SORT"] = CSocNetLogDestination::GetDestinationSort(array(
			"DEST_CONTEXT" => "MENTION"
		));
	}
	else
	{
		$arResult["MENTION_DEST_SORT"] = array();
	}

	$arParams["DESTINATION"]['LAST']['MENTION_USERS'] = array();

	$limit = 20;
	$mentionUserCounter = 0;

	$arDestUser = Array();
	foreach($arResult["MENTION_DEST_SORT"] as $code => $sortInfo)
	{
		if ($mentionUserCounter >=  $limit)
		{
			break;
		}

		if (preg_match('/^U(\d+)$/i', $code, $matches))
		{
			$arParams["DESTINATION"]['LAST']['MENTION_USERS'][$code] = $code;
			$arDestUser[] = str_replace('U', '', $code);
			$mentionUserCounter++;
		}
	}

	$arParams["DESTINATION"]['MENTION_USERS'] = (
		$arResult["bExtranetUser"]
			? $arParams["DESTINATION"]['USERS']
			: (
				!empty($arDestUser)
					? CSocNetLogDestination::GetUsers(Array('id' => $arDestUser))
					: array()
			)
	);
}

$arParams["TAGS"] = (is_array($arParams["TAGS"]) ? $arParams["TAGS"] : array());
if (!empty($arParams["TAGS"]))
{
	$arParams["TAGS"]["VALUE"] = (is_array($arParams["TAGS"]["VALUE"]) ? $arParams["TAGS"]["VALUE"] : array());
}

$arResult["SMILES"] = array("VALUE" => array(), "SETS" => array());
if (array_key_exists("SMILES", $arParams))
{
	if (!in_array("SmileList", $arParams["PARSER"]))
	{
		$arParams["PARSER"][] = "SmileList";
	}

	if (
		is_array($arParams["SMILES"])
		&& array_key_exists("VALUE", $arParams["SMILES"])
		&& !empty($arParams["SMILES"]["VALUE"])
	) // compatibility
	{
		$arResult["SMILES"] = $arParams["SMILES"];
	}
	else if (
		$res = CSmileGallery::getSmilesWithSets($arParams["SMILES"])
	)
	{
		$arResult["SMILES"] = array(
			"VALUE" => array(),
			"SETS" => array()
		);
		foreach ($res["SMILE"] as $smile)
		{
			$arResult["SMILES"]["VALUE"][] = array(
				"set_id" => $smile["SET_ID"],
				"code" => $smile["TYPING"],
				"path" => $smile["IMAGE"],
				"name" => $smile["NAME"],
				"width" => $smile["WIDTH"],
				"height" => $smile["HEIGHT"]
			);
		}
		$arResult["SMILES"]["SETS"] = $res["SMILE_SET"];
	}
}

$arParams["CUSTOM_TEXT"] = (is_array($arParams["CUSTOM_TEXT"]) ? $arParams["CUSTOM_TEXT"] : array());
$arParams["CUSTOM_TEXT_HASH"] = (!empty($arParams["CUSTOM_TEXT"]) ? md5(implode("", $arParams["CUSTOM_TEXT"])) : "");

$arParams["IMAGE_THUMB"] = array("WIDTH" => 90, "HEIGHT" => 90);
$arParams["IMAGE"] = array("WIDTH" => 90, "HEIGHT" => 90);
/********************************************************************
				/Input params
 ********************************************************************/

if (
	IsModuleInstalled("extranet")
	&& strlen(COption::GetOptionString("extranet", "extranet_site")) > 0
)
{
	$arResult["EXTRANET_ROOT"] = array(
		"EX" => array (
		'id' => 'EX',
		'entityId' => 'EX',
		'name' => GetMessage("MPF_EXTRANET_ROOT"),
		'parent' => 'DR0',
		)
	);
}

$arResult["ALLOW_EMAIL_INVITATION"] = (isset($arParams["ALLOW_EMAIL_INVITATION"]) && $arParams["ALLOW_EMAIL_INVITATION"] == "Y");
$arResult["ALLOW_ADD_CRM_CONTACT"] = ($arResult["ALLOW_EMAIL_INVITATION"] && CModule::IncludeModule('crm') && CCrmContact::CheckCreatePermission());
$arResult["ALLOW_CRM_EMAILS"] = (isset($arParams["ALLOW_CRM_EMAILS"]) && $arParams["ALLOW_CRM_EMAILS"] == 'Y');
?>