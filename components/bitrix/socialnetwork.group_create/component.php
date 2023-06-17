<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
$bAutoSubscribe = (array_key_exists("USE_AUTOSUBSCRIBE", $arParams) && $arParams["USE_AUTOSUBSCRIBE"] == "N" ? false : true);

if ($arParams["USER_VAR"] == '')
	$arParams["USER_VAR"] = "user_id";
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["GROUP_VAR"] == '')
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if ($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if ($arParams["PATH_TO_GROUP"] == '')
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if ($arParams["PATH_TO_GROUP_EDIT"] == '')
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"]);
if ($arParams["PATH_TO_GROUP_CREATE"] == '')
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");

if ($arParams["NAME_TEMPLATE"] == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;

if ($arParams["USE_KEYWORDS"] != "N") $arParams["USE_KEYWORDS"] = "Y";
$arResult["GROUP_PROPERTIES"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("SONET_GROUP", 0, LANGUAGE_ID);

foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
{
	$arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
	$arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"]);
	$arResult["GROUP_PROPERTIES"][$field]["~EDIT_FORM_LABEL"] = $arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"];
}

$arResult["bVarsFromForm"] = false;


if (!$GLOBALS["USER"]->IsAuthorized())
	$arResult["NEED_AUTH"] = "Y";
else
{
	$arResult["bIntranet"] = IsModuleInstalled("intranet");
	$arResult["bExtranet"] = (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite());
	$arResult["POST"] = array();

	if ($arParams["GROUP_ID"] > 0)
	{
		$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);
		if ($arGroup && ($arGroup["OWNER_ID"] == $GLOBALS["USER"]->GetID() || CSocNetUser::IsCurrentUserModuleAdmin()))
		{
			$arResult["POST"]["NAME"] = $arGroup["NAME"];
			$arResult["POST"]["DESCRIPTION"] = $arGroup["DESCRIPTION"];
			$arResult["POST"]["IMAGE_ID_DEL"] = "N";
			$arResult["POST"]["SUBJECT_ID"] = $arGroup["SUBJECT_ID"];
			$arResult["POST"]["VISIBLE"] = $arGroup["VISIBLE"];
			$arResult["POST"]["OPENED"] = $arGroup["OPENED"];
			$arResult["POST"]["CLOSED"] = $arGroup["CLOSED"];
			$arResult["POST"]["KEYWORDS"] = $arGroup["KEYWORDS"];
			$arResult["POST"]["OWNER_ID"] = $arGroup["OWNER_ID"];
			$arResult["POST"]["INITIATE_PERMS"] = $arGroup["INITIATE_PERMS"];
			$arResult["POST"]["SPAM_PERMS"] = $arGroup["SPAM_PERMS"];

			$arResult["POST"]["IMAGE_ID"] = $arGroup["IMAGE_ID"];
			$arResult["POST"]["IMAGE_ID_FILE"] = CFile::GetFileArray($arGroup["IMAGE_ID"]);
			$arResult["POST"]["IMAGE_ID_IMG"] = (($arResult["POST"]["IMAGE_ID_FILE"] != false) ? CFile::ShowImage($arResult["POST"]["IMAGE_ID_FILE"]["ID"], 100, 100, "border=0", false, true) : false);
			
			foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
				if (array_key_exists($field, $arGroup))
				{
					$arResult["GROUP_PROPERTIES"][$field]["VALUE"] = $arGroup["~".$field];
					$arResult["GROUP_PROPERTIES"][$field]["ENTITY_VALUE_ID"] = $arGroup["ID"];
				}

			$rsGroupSite = CSocNetGroup::GetSite($arParams["GROUP_ID"]);
			while($arGroupSite = $rsGroupSite->Fetch())
				$arSites[] = $arGroupSite["LID"];
		}
		else
		{
			$arParams["GROUP_ID"] = 0;
			$arResult["POST"]["VISIBLE"] = "Y";
		}
	}
	else
	{
		$arParams["GROUP_ID"] = 0;
		$arResult["POST"]["VISIBLE"] = "Y";
		if ($arResult["bExtranet"])
			$arResult["POST"]["INITIATE_PERMS"] = "E";
		else
			$arResult["POST"]["INITIATE_PERMS"] = "K";
		$arResult["POST"]["SPAM_PERMS"] = "K";
	}

	$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $GLOBALS["USER"]->GetID()));
	$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"]));
	$arResult["Urls"]["GroupEdit"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arParams["GROUP_ID"]));
	$arResult["Urls"]["GroupCreate"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_CREATE"], array("user_id" => $GLOBALS["USER"]->GetID()));

	if ($arParams["GROUP_ID"] <= 0 && $arParams["SET_TITLE"] == "Y")
	{
		$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
			array("#NOBR#", "#/NOBR#"), 
			array("", ""), 
			$arParams["NAME_TEMPLATE"]
		);

		$arTmpUser = array(
			"NAME" => $GLOBALS["USER"]->GetFirstName(),
			"LAST_NAME" => $GLOBALS["USER"]->GetLastName(),
			"SECOND_NAME" => $GLOBALS["USER"]->GetParam("SECOND_NAME"),
			"LOGIN" => $GLOBALS["USER"]->GetLogin()
		);
		$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
	}

	if ($arParams["SET_TITLE"] == "Y")
	{
		if ($arParams["GROUP_ID"] > 0)
			$APPLICATION->SetTitle($arResult["POST"]["NAME"].": ".GetMessage("SONET_C8_GROUP_EDIT"));
		else
			$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C8_GROUP_CREATE"));
	}

	if ($arParams["SET_NAV_CHAIN"] != "N")
	{
		if ($arParams["GROUP_ID"] > 0)
		{
			$APPLICATION->AddChainItem($arResult["POST"]["NAME"], $arResult["Urls"]["Group"]);
			$APPLICATION->AddChainItem(GetMessage("SONET_C8_GROUP_EDIT"));
		}
		else
			$APPLICATION->AddChainItem(GetMessage("SONET_C8_GROUP_CREATE"));
	}

	if ($arParams["GROUP_ID"] <= 0)
	{
		if (!CSocNetUser::IsCurrentUserModuleAdmin() && $GLOBALS["APPLICATION"]->GetGroupRight("socialnetwork", false, "Y", "Y", array(SITE_ID, false)) < "K")
			$arResult["FatalError"] = GetMessage("SONET_C8_ERR_CANT_CREATE").". ";
	}
	elseif ($arResult["POST"]["OWNER_ID"] != $GLOBALS["USER"]->GetID() && !CSocNetUser::IsCurrentUserModuleAdmin())
		$arResult["FatalError"] = GetMessage("SONET_C8_ERR_SECURITY").". ";

	if ($arResult["FatalError"] == '')
	{
		$arResult["ShowForm"] = "Input";

		if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["save"] <> '' && check_bitrix_sessid())
		{
			$errorMessage = "";

			$arImageID = $GLOBALS["HTTP_POST_FILES"]["GROUP_IMAGE_ID"];

			if ($arImageID["tmp_name"] <> '')
				CFile::ResizeImage($arImageID, array("width" => 300, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL);
			$arImageID["old_file"] = $arResult["POST"]["IMAGE_ID"];
			$arImageID["del"] = ($_POST["GROUP_IMAGE_ID_DEL"] == "Y" ? "Y" : "N");

			$arResult["POST"]["NAME"] = $_POST["GROUP_NAME"];
			$arResult["POST"]["DESCRIPTION"] = $_POST["GROUP_DESCRIPTION"];
			$arResult["POST"]["IMAGE_ID_DEL"] = ($_POST["GROUP_IMAGE_ID_DEL"] == "Y" ? "Y" : "N");
			$arResult["POST"]["SUBJECT_ID"] = $_POST["GROUP_SUBJECT_ID"];
			$arResult["POST"]["VISIBLE"] = ($_POST["GROUP_VISIBLE"] == "Y" ? "Y" : "N");
			$arResult["POST"]["OPENED"] = ($_POST["GROUP_OPENED"] == "Y" ? "Y" : "N");
			$arResult["POST"]["CLOSED"] = ($_POST["GROUP_CLOSED"] == "Y" ? "Y" : "N");
			$arResult["POST"]["KEYWORDS"] = $_POST["GROUP_KEYWORDS"];
			$arResult["POST"]["INITIATE_PERMS"] = $_POST["GROUP_INITIATE_PERMS"];
			$arResult["POST"]["SPAM_PERMS"] = $_POST["GROUP_SPAM_PERMS"];

			foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
				if (array_key_exists($field, $_POST))
					$arResult["POST"]["PROPERTIES"][$field] = $_POST[$field];

			if ($_POST["GROUP_NAME"] == '')
				$errorMessage .= GetMessage("SONET_C8_ERR_NAME").".<br />";
			if ($_POST["GROUP_DESCRIPTION"] == '')
				$errorMessage .= GetMessage("SONET_C8_ERR_DESCR").".<br />";
			if (
				isset($_POST["GROUP_SUBJECT_ID"])
				&& intval($_POST["GROUP_SUBJECT_ID"]) <= 0
			)
			{
				$errorMessage .= GetMessage("SONET_C8_ERR_SUBJECT").".<br />";
			}
			if ($_POST["GROUP_INITIATE_PERMS"] == '')
				$errorMessage .= GetMessage("SONET_C8_ERR_PERMS").".<br />";
			if ($_POST["GROUP_SPAM_PERMS"] == '')
				$errorMessage .= GetMessage("SONET_C8_ERR_SPAM_PERMS").".<br />";

			if (
				$errorMessage == ''
				&& $arParams["GROUP_ID"] > 0
				&& $arResult["POST"]["OWNER_ID"] != $GLOBALS["USER"]->GetID() 
				&& !CSocNetUser::IsCurrentUserModuleAdmin()
			)
				$errorMessage .= GetMessage("SONET_C8_ERR_SECURITY").". ";

			$bCreate = false;

			if ($errorMessage == '')
			{
				$arFields = array(
					"NAME" => $_POST["GROUP_NAME"],
					"DESCRIPTION" => $_POST["GROUP_DESCRIPTION"],
					"VISIBLE" => ($_POST["GROUP_VISIBLE"] == "Y" ? "Y" : "N"),
					"OPENED" => ($_POST["GROUP_OPENED"] == "Y" ? "Y" : "N"),
					"CLOSED" => ($_POST["GROUP_CLOSED"] == "Y" ? "Y" : "N"),
					"SUBJECT_ID" => $_POST["GROUP_SUBJECT_ID"],
					"KEYWORDS" => $_POST["GROUP_KEYWORDS"],
					"IMAGE_ID" => $arImageID,
					"INITIATE_PERMS" => $_POST["GROUP_INITIATE_PERMS"],
					"SPAM_PERMS" => $_POST["GROUP_SPAM_PERMS"],
				);

				$arFields["SITE_ID"] = array(SITE_ID);

				if (CModule::IncludeModule("extranet") && !CExtranet::IsExtranetSite() && $_POST["IS_EXTRANET_GROUP"] == "Y")
				{
					$arFields["SITE_ID"][] = CExtranet::GetExtranetSiteID();
					$arFields["VISIBLE"] = "N";
					$arFields["OPENED"] = "N";
				}

				foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
					if (array_key_exists($field, $_POST))
						$arFields[$field] = $_POST[$field];

				$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("SONET_GROUP", $arFields);

				if ($arParams["GROUP_ID"] <= 0)
				{
					if (CModule::IncludeModule("extranet") && CExtranet::IsExtranetSite())
						$arFields["SITE_ID"][] = CSite::GetDefSite();

					$arResult["MEW_GROUP_ID"] = CSocNetGroup::CreateGroup($GLOBALS["USER"]->GetID(), $arFields, $bAutoSubscribe);
					if (!$arResult["MEW_GROUP_ID"])
					{
						if ($e = $APPLICATION->GetException())
							$errorMessage .= $e->GetString();
					}
					else
						$bCreate = true;

				}
				else
				{
					$arFields["=DATE_UPDATE"] = $GLOBALS["DB"]->CurrentTimeFunction();
					$arFields["=DATE_ACTIVITY"] = $GLOBALS["DB"]->CurrentTimeFunction();
					$arResult["MEW_GROUP_ID"] = CSocNetGroup::Update($arParams["GROUP_ID"], $arFields, $bAutoSubscribe);
					if (!$arResult["MEW_GROUP_ID"] && ($e = $APPLICATION->GetException()))
						$errorMessage .= $e->GetString();
					else
					{
						$rsSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
						while($arSite = $rsSite->Fetch())
							BXClearCache(true, "/".$arSite["ID"]."/bitrix/search.tags.cloud/");
					}
				}
			}

			if ($arImageID["tmp_name"] <> '')
				CFile::ResizeImageDeleteCache($arImageID);

			$arResult["POST"]["NAME"] = htmlspecialcharsex($arResult["POST"]["NAME"]);
			$arResult["POST"]["DESCRIPTION"] = htmlspecialcharsex($arResult["POST"]["DESCRIPTION"]);
			$arResult["POST"]["KEYWORDS"] = htmlspecialcharsex($arResult["POST"]["KEYWORDS"]);

			if ($errorMessage <> '')
			{
				$arResult["ErrorMessage"] = $errorMessage;
				$arResult["bVarsFromForm"] = true;
			}
			else
			{
				if ($bCreate && $arParams["ALLOW_REDIRECT_REQUEST"] == "Y" && $arParams["REDIRECT_REQUEST"] <> '')
				{
					$arResult["REDIRECT_REQUEST"] = CComponentEngine::MakePathFromTemplate($arParams["REDIRECT_REQUEST"], array("group_id" => $arResult["MEW_GROUP_ID"]));
					LocalRedirect($arResult["REDIRECT_REQUEST"]);
				}

				$arResult["ShowForm"] = "Confirm";
				$arResult["Urls"]["NewGroup"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["MEW_GROUP_ID"]));
				if ($arParams["REDIRECT_CONFIRM"] <> '')
				{
					$arResult["REDIRECT_CONFIRM"] = CComponentEngine::MakePathFromTemplate($arParams["REDIRECT_CONFIRM"], array("group_id" => $arResult["MEW_GROUP_ID"]));
					LocalRedirect($arResult["REDIRECT_CONFIRM"]);
				}

			}
		}

		if ($arResult["ShowForm"] == "Input")
		{
			$arResult["Subjects"] = array();
			$dbSubjects = CSocNetGroupSubject::GetList(
				array("SORT"=>"ASC", "NAME" => "ASC"),
				array("SITE_ID" => (is_array($arSites) ? $arSites : SITE_ID)),
				false,
				false,
				array("ID", "NAME")
			);
			while ($arSubject = $dbSubjects->GetNext())
				$arResult["Subjects"][$arSubject["ID"]] = $arSubject["NAME"];

			$arResult["InitiatePerms"] = array(
				SONET_ROLES_OWNER => GetMessage("SONET_C8_IP_OWNER"),
				SONET_ROLES_MODERATOR => GetMessage("SONET_C8_IP_MOD"),
				SONET_ROLES_USER => GetMessage("SONET_C8_IP_USER"),
			);

			$arResult["SpamPerms"] = array(
				SONET_ROLES_OWNER => GetMessage("SONET_C8_IP_OWNER"),
				SONET_ROLES_MODERATOR => GetMessage("SONET_C8_IP_MOD"),
				SONET_ROLES_USER => GetMessage("SONET_C8_IP_USER"),
				SONET_ROLES_ALL => GetMessage("SONET_C8_IP_ALL"),
			);
			$arResult["POST"]["IS_EXTRANET_GROUP"] = false;

			if (
				intval($arParams["GROUP_ID"]) > 0
				&& CModule::IncludeModule("extranet") 
				&& ($extranet_site_id = CExtranet::GetExtranetSiteID())
				&& in_array($extranet_site_id, $arSites)
			)
				$arResult["POST"]["IS_EXTRANET_GROUP"] = true;
		}
	}
}

$this->IncludeComponentTemplate();
?>