<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
/** @global CUserTypeManager $USER_FIELD_MANAGER */
global $CACHE_MANAGER, $USER_FIELD_MANAGER;

use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\Item\UserToGroup;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Bitrix\Socialnetwork\Item\WorkgroupTemplate;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.group_create.ex/include.php");

if (!Loader::includeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["GROUP_ID"] = (
	intval($_REQUEST["SONET_GROUP_ID"]) > 0
		? intval($_REQUEST["SONET_GROUP_ID"])
		: intval($arParams["GROUP_ID"])
);

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
$bAutoSubscribe = (array_key_exists("USE_AUTOSUBSCRIBE", $arParams) && $arParams["USE_AUTOSUBSCRIBE"] == "N" ? false : true);

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$strSiteWorkgroupsPage = \Bitrix\Socialnetwork\ComponentHelper::getWorkgroupSEFUrl();
if (strlen($strSiteWorkgroupsPage) > 0)
{
	$arParams["PATH_TO_GROUP_GENERAL"] = $strSiteWorkgroupsPage."group/#group_id#/general/";
}

$arParams["PATH_TO_GROUP_EDIT"] = trim($arParams["PATH_TO_GROUP_EDIT"]);
if (strlen($arParams["PATH_TO_GROUP_EDIT"]) <= 0)
	$arParams["PATH_TO_GROUP_EDIT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_edit&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_GROUP_CREATE"] = trim($arParams["PATH_TO_GROUP_CREATE"]);
if (strlen($arParams["PATH_TO_GROUP_CREATE"]) <= 0)
	$arParams["PATH_TO_GROUP_CREATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_create&".$arParams["USER_VAR"]."=#user_id#");

$arParams["IUS_INPUT_NAME"] = "ius_ids";
$arParams["IUS_INPUT_NAME_SUSPICIOUS"] = "ius_susp";
$arParams["IUS_INPUT_NAME_STRING"] = "users_list_string_ius";
$arParams["IUS_INPUT_NAME_EXTRANET"] = "ius_ids_extranet";
$arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"] = "ius_susp_extranet";
$arParams["IUS_INPUT_NAME_STRING_EXTRANET"] = "users_list_string_ius_extranet";

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;

if ($arParams["USE_KEYWORDS"] != "N") $arParams["USE_KEYWORDS"] = "Y";

$arResult["GROUP_PROPERTIES"] = $USER_FIELD_MANAGER->GetUserFields("SONET_GROUP", 0, LANGUAGE_ID);

foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
{
	if (
		!empty($arUserField['EDIT_IN_LIST'])
		&& $arUserField['EDIT_IN_LIST'] == 'N'
		&& (
			empty($arUserField['MANDATORY'])
			|| $arUserField['MANDATORY'] != 'Y'
		)
	)
	{
		unset($arResult["GROUP_PROPERTIES"][$field]);
		continue;
	}

	$arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"] = StrLen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
	$arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"]);
	$arResult["GROUP_PROPERTIES"][$field]["~EDIT_FORM_LABEL"] = $arResult["GROUP_PROPERTIES"][$field]["EDIT_FORM_LABEL"];
}

$arResult["bVarsFromForm"] = false;
$arResult["templateEditMode"] = 'N';

$arResult["IS_IFRAME"] = $_GET["IFRAME"] == "Y";
$arResult["IS_POPUP"] = $_GET["POPUP"] == "Y";

if (in_array($_GET["CALLBACK"], array("REFRESH", "GROUP")))
{
	$arResult["CALLBACK"] = $_GET["CALLBACK"];
}

if (!empty($arParams["TAB"]))
{
	$arResult["TAB"] = strtolower($arParams["TAB"]);
}

if (strlen($_GET["tab"]) > 0)
{
	$arResult["TAB"] = $_GET["tab"];
}

$arResult['destinationContextOwner'] = 'GROUP_INVITE_OWNER';
$arResult['destinationContextModerators'] = 'GROUP_INVITE_MODERATORS';
$arResult['destinationContextUsers'] = 'GROUP_INVITE';

if (!$USER->IsAuthorized())
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$arResult["currentUserId"] = $USER->getId();
	$arResult["bIntranet"] = $arResult["intranetInstalled"] = ModuleManager::isModuleInstalled('intranet');

	$extranetSiteValue = Option::get("extranet", "extranet_site");

	$arResult["bExtranetInstalled"] =  (
		$arResult["intranetInstalled"]
		&& ModuleManager::isModuleInstalled('extranet')
		&& !empty($extranetSiteValue)
	);

	$arResult["bExtranet"] = (
		$arResult["bExtranetInstalled"]
		&& Loader::includeModule('extranet')
		&& CExtranet::IsExtranetSite()
	);
	$arResult["isCurrentUserIntranet"] = (
		!Loader::includeModule('extranet')
		|| CExtranet::IsIntranetUser()
	);

	$arResult["messageTextDisabled"] = (
		Loader::includeModule('bitrix24')
		&& (
			!CBitrix24::isLicensePaid()
			|| CBitrix24::isDemoLicense()
		)
		&& !CBitrix24::isNfrLicense()
	);

	$inviteMessageTextDefault = $arResult["inviteMessageText"] = Loc::getMessage("SONET_GCE_INVITE_MESSAGE_TEXT");

	if (
		!$arResult["messageTextDisabled"]
		&& (
			($userMessage = CUserOptions::getOption("socialnetwork", "invite_message_text"))
			|| ($userMessage = CUserOptions::getOption((IsModuleInstalled("bitrix24") ? "bitrix24" : "intranet"), "invite_message_text"))
		)
	)
	{
		$inviteMessageTextDefault = $arResult["inviteMessageText"] = $userMessage;
	}

	if (
		!$arResult["messageTextDisabled"]
		&& isset($_POST["MESSAGE_TEXT"])
	)
	{
		$arResult["inviteMessageText"] = htmlspecialcharsbx($_POST["MESSAGE_TEXT"]);
	}

	$arResult["POST"] = array(
		"FEATURES" => array(),
		"USER_IDS" => false,
		"MODERATOR_IDS" => false,
		"USERS_FOR_JS" => array(),
		"USERS_FOR_JS_I" => array(),
		"USERS_FOR_JS_E" => array(),
		"EMAILS" => ""
	);

	if ($arParams["GROUP_ID"] > 0)
	{
		__GCEGetGroup($arParams["GROUP_ID"], $arResult["GROUP_PROPERTIES"], $arResult["POST"], $arResult["TAB"]);
	}
	else
	{
		$arParams["GROUP_ID"] = 0;
		$arResult["POST"]["VISIBLE"] = "Y";
		if ($arResult["bExtranet"])
		{
			$arResult["POST"]["INITIATE_PERMS"] = "E";
		}
		else
		{
			$arResult["POST"]["INITIATE_PERMS"] = "K";
		}
		$arResult["POST"]["SPAM_PERMS"] = "K";
		$arResult["POST"]["IMAGE_ID_IMG"] = '<img src="/bitrix/images/1.gif" height="60" class="sonet-group-create-popup-image" id="sonet_group_create_popup_image" border="0">';
	}

	$arResult["USE_PRESETS"] = ($arResult["intranetInstalled"] ? 'Y' : 'N');

	$arResult['Types'] = (
		$arResult["USE_PRESETS"] == 'Y'
			? \Bitrix\Socialnetwork\Item\Workgroup::getTypes(array(
			'currentExtranetSite' => $arResult["bExtranet"]
		))
		: array()
	);

	$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["currentUserId"]));
	$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"]));

	if ($arResult["TAB"] != "invite")
	{
		if ($arParams["GROUP_ID"] <= 0)
		{
			if (
				!CSocNetUser::IsCurrentUserModuleAdmin() 
				&& $APPLICATION->GetGroupRight("socialnetwork", false, "Y", "Y", array(SITE_ID, false)) < "K"
			)
			{
				$arResult["FatalError"] = GetMessage("SONET_GCE_ERR_CANT_CREATE").". ";
			}
		}
		elseif (
			strlen($errorMessage) <= 0
			&& $arParams["GROUP_ID"] > 0
			&& $arResult["POST"]["OWNER_ID"] != $arResult["currentUserId"]
			&& !CSocNetUser::IsCurrentUserModuleAdmin()
		)
		{
			$arResult["FatalError"] = GetMessage("SONET_GCE_ERR_SECURITY").". ";
		}
	}

	if (StrLen($arResult["FatalError"]) <= 0)
	{
		if (
			!array_key_exists("TAB", $arResult)
			|| $arResult["TAB"] == "edit"
		)
		{
			__GCE_GetFeatures($arParams["GROUP_ID"], $arResult["POST"]["FEATURES"]);
		}

		$arResult["ShowForm"] = "Input";
		$arResult["ErrorFields"] = array();

		if (
			$_SERVER["REQUEST_METHOD"] == "POST"
			&& strlen($_POST["save"]) > 0
			&& check_bitrix_sessid()
		)
		{
			if ($_POST["ajax_request"] == "Y")
			{
				CUtil::JSPostUnescape();
			}

			$errorMessage = "";
			$warningMessage = "";

			if ($arResult["templateEditMode"] != 'Y')
			{

			}

			$moderatorIdList = array();
			$ownerId = (
				isset($arResult["POST"])
				&& isset($arResult["POST"]["OWNER_ID"])
				&& intval($arResult["POST"]["OWNER_ID"]) > 0
					? intval($arResult["POST"]["OWNER_ID"])
					: $arResult["currentUserId"]
			);

			if (
				!array_key_exists("TAB", $arResult)
				|| $arResult["TAB"] == "edit"
			)
			{
				if (
					intval($_POST["GROUP_IMAGE_ID"]) > 0
					&& (
						!isset($_POST["GROUP_IMAGE_ID_del"])
						|| (
							$_POST["GROUP_IMAGE_ID_del"] != "Y"
							&& intval($_POST["GROUP_IMAGE_ID_del"]) != intval($_POST["GROUP_IMAGE_ID_del"]) // main.file.input
						)
					)
				)
				{
					if (
						intval($arResult["POST"]["IMAGE_ID"]) != intval($_POST["GROUP_IMAGE_ID"])
						&& in_array($_POST['GROUP_IMAGE_ID'], \Bitrix\Main\UI\FileInputUtility::instance()->checkFiles('GROUP_IMAGE_ID', array($_POST['GROUP_IMAGE_ID'])))
					)
					{
						$arImageID = CFile::MakeFileArray($_POST["GROUP_IMAGE_ID"]);
						$arImageID["old_file"] = $arResult["POST"]["IMAGE_ID"];
						$arImageID["del"] = "N";
						CFile::ResizeImage($arImageID, array("width" => 300, "height" => 300), BX_RESIZE_IMAGE_PROPORTIONAL);
					}
				}
				else
				{
					$arImageID = array("del" => "Y", "old_file" => $arResult["POST"]["IMAGE_ID"]);
				}

				$arResult["POST"]["NAME"] = htmlspecialcharsbx($_POST["GROUP_NAME"]);
				$arResult["POST"]["DESCRIPTION"] = $_POST["GROUP_DESCRIPTION"];
				$arResult["POST"]["IMAGE_ID_DEL"] = (
					isset($_POST["GROUP_IMAGE_ID_del"])
					&& (
						$_POST["GROUP_IMAGE_ID_del"] == "Y"
						|| intval($_POST["GROUP_IMAGE_ID_del"]) == intval($_POST["GROUP_IMAGE_ID"])
					)
						? "Y"
						: "N"
				);
				$arResult["POST"]["SUBJECT_ID"] = $_POST["GROUP_SUBJECT_ID"];
				$arResult["POST"]["VISIBLE"] = ($_POST["GROUP_VISIBLE"] == "Y" ? "Y" : "N");
				$arResult["POST"]["OPENED"] = ($_POST["GROUP_OPENED"] == "Y" ? "Y" : "N");
				$arResult["POST"]["IS_EXTRANET_GROUP"] = ($_POST["IS_EXTRANET_GROUP"] == "Y" ? "Y" : "N");
				$arResult["POST"]["EXTRANET_INVITE_ACTION"] = (isset($_POST["EXTRANET_INVITE_ACTION"]) && $_POST["EXTRANET_INVITE_ACTION"] == "add" ? "add" : "invite");
				$arResult["POST"]["CLOSED"] = ($_POST["GROUP_CLOSED"] == "Y" ? "Y" : "N");
				$arResult["POST"]["KEYWORDS"] = $_POST["GROUP_KEYWORDS"];
				$arResult["POST"]["INITIATE_PERMS"] = $_POST["GROUP_INITIATE_PERMS"];
				$arResult["POST"]["SPAM_PERMS"] = $_POST["GROUP_SPAM_PERMS"];

				foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
				{
					if (array_key_exists($field, $_POST))
					{
						$arResult["POST"]["PROPERTIES"][$field] = $_POST[$field];
					}
				}

				if (strlen($_POST["GROUP_NAME"]) <= 0)
				{
					$errorMessage .= GetMessage(empty($_POST["GROUP_PROJECT"]) && $_POST["GROUP_PROJECT"] == 'Y' ? "SONET_GCE_ERR_NAME_PROJECT" : "SONET_GCE_ERR_NAME").".<br />";
					$arResult["ErrorFields"][] = "GROUP_NAME";
				}
				if (IntVal($_POST["GROUP_SUBJECT_ID"]) <= 0)
				{
					$errorMessage .= GetMessage(!empty($_POST["GROUP_PROJECT"]) && $_POST["GROUP_PROJECT"] == 'Y' ? "SONET_GCE_ERR_SUBJECT_PROJECT" : "SONET_GCE_ERR_SUBJECT").".<br />";
					$arResult["ErrorFields"][] = "GROUP_SUBJECT_ID";
				}

				if (strlen($_POST["GROUP_INITIATE_PERMS"]) <= 0)
				{
					$errorMessage .= GetMessage(!empty($_POST["GROUP_PROJECT"]) && $_POST["GROUP_PROJECT"] == 'Y' ? "SONET_GCE_ERR_PERMS_PROJECT" : "SONET_GCE_ERR_PERMS").".<br />";
					$arResult["ErrorFields"][] = "GROUP_INITIATE_PERMS";
				}
				if (strlen($_POST["GROUP_SPAM_PERMS"]) <= 0)
				{
					$errorMessage .= GetMessage("SONET_GCE_ERR_SPAM_PERMS").".<br />";
					$arResult["ErrorFields"][] = "GROUP_SPAM_PERMS";
				}

				foreach ($arResult["POST"]["FEATURES"] as $feature => $arFeature)
				{
					$arResult["POST"]["FEATURES"][$feature]["Active"] = ($_POST[$feature."_active"] == "Y");
					$arResult["POST"]["FEATURES"][$feature]["FeatureName"] = (strlen(trim($_POST[$feature."_name"])) > 0 ? trim($_POST[$feature."_name"]) : '');
				}

				// owner
				if (
					isset($_POST["OWNER_CODE"])
					&& preg_match('/^U(\d+)$/', $_POST["OWNER_CODE"], $match)
					&& intval($match[1]) > 0
				)
				{
					$ownerId = intval($match[1]);
					\Bitrix\Main\FinderDestTable::merge(array(
						"CONTEXT" => $arResult['destinationContextOwner'],
						"CODE" => array($_POST["OWNER_CODE"])
					));
				}

				// moderators
				$moderatorCodeList = (is_array($_POST["MODERATOR_CODES"]) ? $_POST["MODERATOR_CODES"] : array($_POST["MODERATOR_CODES"]));

				foreach ($moderatorCodeList as $destinationCode)
				{
					if(
						preg_match('/^U(\d+)$/', $destinationCode, $match)
						&& $match[1] != $ownerId
					)
					{
						$moderatorCodeList[] = $destinationCode;
						if (!in_array($match[1], $moderatorIdList))
						{
							$moderatorIdList[] = $match[1];
						}
					}
				}

				if (!empty($moderatorCodeList))
				{
					\Bitrix\Main\FinderDestTable::merge(array(
						"CONTEXT" => $arResult['destinationContextModerators'],
						"CODE" => $moderatorCodeList
					));
				}
			}

			if (
				!array_key_exists("TAB", $arResult) 
				|| $arResult["TAB"] == "invite"
			)
			{
				if (
					array_key_exists("NEW_INVITE_FORM", $_POST)
					&& $_POST["NEW_INVITE_FORM"] == "Y"
				)
				{
					// new form

					// members
					$arUserIDs = $arDepartmentIDs = $arUserCodes = array();

					$arUserCodesFromPost = (is_array($_POST["USER_CODES"]) ? $_POST["USER_CODES"] : array($_POST["USER_CODES"]));

					foreach ($arUserCodesFromPost as $destinationCode)
					{
						if(preg_match('/^U(\d+)$/', $destinationCode, $match))
						{
							if (
								in_array($match[1], $arUserIDs)
								|| in_array($match[1], $moderatorIdList)
								|| $match[1] == $ownerId
							)
							{
								continue;
							}

							$arUserIDs[] = $match[1];
							$arUserCodes['U'.$match[1]] = 'users';
						}
						elseif(preg_match('/^DR(\d+)$/', $destinationCode, $match))
						{
							if (!in_array($match[1], $arUserIDs))
							{
								$arDepartmentIDs[] = $match[1];
							}

							if (!array_key_exists('DR'.$match[1], $arUserCodes))
							{
								$arUserCodes['DR'.$match[1]] = 'users';
							}
						}
					}

					\Bitrix\Main\FinderDestTable::merge(array(
						"CONTEXT" => $arResult['destinationContextUsers'],
						"CODE" => array_keys($arUserCodes)
					));

					$arResult["POST"]["USER_IDS"] = $arUserIDs;
					$arResult["POST"]["USER_CODES"] = $arUserCodes;

					if (
						$arResult["bExtranetInstalled"]
						&& array_key_exists("EMAILS", $_POST)
					)
					{
						$arResult["POST"]["EMAILS"] = $_POST["EMAILS"];
					}

					if (
						array_key_exists("TAB", $arResult)
						&& $arResult["TAB"] == "invite"
						&& empty($arUserIDs)
						&& empty($arDepartmentIDs)
						&& !$arResult["intranetInstalled"])
					{
						$errorMessage .= GetMessage("SONET_GCE_NO_USERS").". ";
						$arResult["ErrorFields"][] = "USERS";
					}
				}
				else
				{
					// old form

					if ($arResult["intranetInstalled"]) // user.selector.new
					{
						if (
							is_array($_POST["USER_IDS"])
							&& count($_POST["USER_IDS"]) > 0
						)
						{
							$arResult["POST"]["USER_IDS"] = $_POST["USER_IDS"];
						}

						//adding e-mail from the input field to the list
						if (
							array_key_exists("EMAIL", $_POST) 
							&& strlen($_POST["EMAIL"]) > 0 
							&& check_email($_POST["EMAIL"])
						)
						{
							$_POST["EMAILS"] .= (empty($_POST["EMAILS"]) ? "" : ", ").trim($_POST["EMAIL"]);
						}

						if (array_key_exists("EMAILS", $_POST))
						{
							$arResult["POST"]["EMAILS"] = $_POST["EMAILS"];
						}
					}
					else // user_search_input
					{
						$arUserIDs = array();

						$arUsersList = array();
						$arUsersListTmp = Explode(",", $_POST["users_list"]);
						foreach ($arUsersListTmp as $userTmp)
						{
							$userTmp = Trim($userTmp);
							if (StrLen($userTmp) > 0)
							{
								$arUsersList[] = $userTmp;
							}
						}

						if (
							$arResult["TAB"] == "invite" 
							&& Count($arUsersList) <= 0
						)
						{
							$errorMessage .= GetMessage("SONET_GCE_NO_USERS").". ";
							$arResult["ErrorFields"][] = "USERS";
						}

						if (StrLen($errorMessage) <= 0)
						{
							foreach ($arUsersList as $user)
							{
								$arFoundUsers = CSocNetUser::SearchUser($user);
								if (
									$arFoundUsers
									&& is_array($arFoundUsers)
									&& count($arFoundUsers) > 0
								)
								{
									foreach ($arFoundUsers as $userID => $userName)
									{
										if (intval($userID) > 0)
										{
											$arUserIDs[] = $userID;
										}
									}
								}
							}
						}

						$arResult["POST"]["USER_IDS"] = $arUserIDs;
					}
				}
			}

			if (
				(
					!array_key_exists("TAB", $arResult) 
					|| $arResult["TAB"] == "edit"
				) 
				&& strlen($errorMessage) <= 0
			)
			{
				$arFields = array(
					"NAME" => $_POST["GROUP_NAME"],
					"DESCRIPTION" => $_POST["GROUP_DESCRIPTION"],
					"VISIBLE" => ($_POST["GROUP_VISIBLE"] == "Y" ? "Y" : "N"),
					"OPENED" => ($_POST["GROUP_OPENED"] == "Y" ? "Y" : "N"),
					"CLOSED" => ($_POST["GROUP_CLOSED"] == "Y" ? "Y" : "N"),
					"SUBJECT_ID" => $_POST["GROUP_SUBJECT_ID"],
					"KEYWORDS" => $_POST["GROUP_KEYWORDS"],
					"INITIATE_PERMS" => $_POST["GROUP_INITIATE_PERMS"],
					"SPAM_PERMS" => $_POST["GROUP_SPAM_PERMS"],
					"PROJECT" => ($_POST["GROUP_PROJECT"] == "Y" ? "Y" : "N"),
				);

				if (!empty($arImageID))
				{
					$arFields["IMAGE_ID"] = $arImageID;
				}

				if ($arFields["PROJECT"] == "Y")
				{
					if (isset($_POST["PROJECT_DATE_START"]))
					{
						$arFields["PROJECT_DATE_START"] = $_POST["PROJECT_DATE_START"];
					}

					if (isset($_POST["PROJECT_DATE_FINISH"]))
					{
						$arFields["PROJECT_DATE_FINISH"] = $_POST["PROJECT_DATE_FINISH"];
					}
				}

				if (
					!CModule::IncludeModule("extranet")
					|| !CExtranet::IsExtranetSite()
				)
				{
					$arFields["SITE_ID"] = array(SITE_ID);
					if (
						CModule::IncludeModule("extranet")
						&& !CExtranet::IsExtranetSite()
						&& $_POST["IS_EXTRANET_GROUP"] == "Y"
					)
					{
						$arFields["SITE_ID"][] = CExtranet::GetExtranetSiteID();
						$arFields["VISIBLE"] = "N";
						$arFields["OPENED"] = "N";
					}
				}
				elseif(
					CModule::IncludeModule("extranet") 
					&& CExtranet::IsExtranetSite()
				)
				{
					$arFields["SITE_ID"] = array(SITE_ID, CSite::GetDefSite());
				}

				foreach($arResult["GROUP_PROPERTIES"] as $field => $arUserField)
				{
					if (array_key_exists($field, $_POST))
					{
						$arFields[$field] = $_POST[$field];
					}
				}

				$USER_FIELD_MANAGER->EditFormAddFields("SONET_GROUP", $arFields);

				if ($arParams["GROUP_ID"] <= 0)
				{
					if (
						CModule::IncludeModule("extranet") 
						&& CExtranet::IsExtranetSite()
					)
					{
						$arFields["SITE_ID"][] = CSite::GetDefSite();
					}

					$arResult["GROUP_ID"] = CSocNetGroup::createGroup($ownerId, $arFields, $bAutoSubscribe);
					if (!$arResult["GROUP_ID"])
					{
						if ($e = $APPLICATION->GetException())
						{
							$errorMessage .= $e->GetString();
							$errorID = $e->GetID();
							if (strlen($errorID) > 0)
							{
								$arResult["ErrorFields"][] = $errorID;
							}
						}
					}
					else
					{
						$bFirstStepSuccess = true;
					}
				}
				else
				{
					$arFields["=DATE_UPDATE"] = $DB->CurrentTimeFunction();
					$arFields["=DATE_ACTIVITY"] = $DB->CurrentTimeFunction();

					$arResult["GROUP_ID"] = CSocNetGroup::update($arParams["GROUP_ID"], $arFields, $bAutoSubscribe);

					if (
						!$arResult["GROUP_ID"]
						&& ($e = $APPLICATION->getException())
					)
					{
						$errorMessage .= $e->getString();
						$errorID = $e->getId();
						if ($errorID == "ERROR_IMAGE_ID")
						{
							$arResult["ErrorFields"][] = "GROUP_IMAGE_ID";
						}
						elseif (
							isset($e->messages) 
							&& is_array($e->messages) 
							&& isset($e->messages[0]["id"])
						)
						{
							$arResult["ErrorFields"][] = $e->messages[0]["id"];
						}
					}
					else
					{
						if ($arResult["POST"]["OWNER_ID"] != $ownerId)
						{
							CSocNetUserToGroup::setOwner($ownerId, $arParams["GROUP_ID"], $arResult["POST"]);
						}

						$plusList = array_diff($moderatorIdList, $arResult["POST"]["MODERATOR_IDS"]);
						$minusList = array_diff($arResult["POST"]["MODERATOR_IDS"], $moderatorIdList);

						if (!empty($minusList))
						{
							$relationIdList = array();

							$resRelation = UserToGroupTable::getList(array(
								'filter' => array(
									'GROUP_ID' => $arParams["GROUP_ID"],
									'@USER_ID' => $minusList
								),
								'select' => array('ID')
							));
							while($relation = $resRelation->fetch())
							{
								$relationIdList[] = $relation['ID'];
							}

							if (!empty($relationIdList))
							{
								CSocNetUserToGroup::TransferModerator2Member($arResult["currentUserId"], $arParams["GROUP_ID"], $relationIdList, CSocNetUser::IsCurrentUserModuleAdmin());
							}
						}

						\Bitrix\Socialnetwork\Item\UserToGroup::addModerators(array(
							'group_id' => $arParams["GROUP_ID"],
							'user_id' => $plusList,
							'current_user_id' => $arResult["currentUserId"]
						));

						$rsSite = CSite::getList($by="sort", $order="desc", Array("ACTIVE" => "Y"));
						while($arSite = $rsSite->Fetch())
						{
							BXClearCache(true, "/".$arSite["ID"]."/bitrix/search.tags.cloud/");
						}
					}
				}
			}

			if (
				strlen($errorMessage) <= 0
				&& array_key_exists("TAB", $arResult)
				&& $arResult["TAB"] != "edit"
			)
			{
				$arResult["GROUP_ID"] = $arParams["GROUP_ID"];
			}

			if (
				!empty($arImageID)
				&& strlen($arImageID["tmp_name"]) > 0
			)
			{
				CFile::ResizeImageDeleteCache($arImageID);
			}

			if (strlen($errorMessage) > 0)
			{
				$arResult["ErrorMessage"] = $errorMessage;
				$arResult["bVarsFromForm"] = true;
			}
			elseif ($arResult["GROUP_ID"] > 0)
			{
				/* features */
				if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "edit")
				{
					foreach ($arResult["POST"]["FEATURES"] as $feature => $arFeature)
					{
						$idTmp = CSocNetFeatures::setFeature(
							SONET_ENTITY_GROUP,
							$arResult["GROUP_ID"],
							$feature,
							($_POST[$feature."_active"] == "Y"),
							(
								strlen($_REQUEST[$feature."_name"]) > 0
									? $_REQUEST[$feature."_name"]
									: (strlen($arFeature["FeatureName"]) > 0 ? $arFeature["FeatureName"] : false)
							)
						);

						if (
							$arParams["GROUP_ID"] <= 0
							&& $feature == 'chat'
						)
						{
							\CUserOptions::setOption("socialnetwork", "default_chat_create_default", ($_POST[$feature."_active"] == "Y" ? 'Y' : 'N'));
						}

						if (!$idTmp)
						{
							if ($e = $APPLICATION->GetException())
							{
								$errorMessage .= $e->GetString();
							}
						}
						else
						{
							$bSecondStepSuccess = true;
						}
					}
				}

				if (
					strlen($errorMessage) <= 0
					&& $_POST['SAVE_AS_TEMPLATE'] == 'Y'
				)
				{
					$typeFields = array(
						'VISIBLE' => (isset($arFields['VISIBLE']) && $arFields['VISIBLE'] == 'Y' ? 'Y' : 'N'),
						'OPENED' => (isset($arFields['OPENED']) && $arFields['OPENED'] == 'Y' ? 'Y' : 'N'),
						'PROJECT' => (isset($arFields['PROJECT']) && $arFields['PROJECT'] == 'Y' ? 'Y' : 'N'),
						'EXTERNAL' => (isset($_POST["IS_EXTRANET_GROUP"]) && $_POST["IS_EXTRANET_GROUP"] == 'Y' ? 'Y' : 'N')
					);
					$typeCode = \Bitrix\Socialnetwork\Item\Workgroup::getTypeCodeByParams(array(
						'typesList' => $arResult['Types'],
						'fields' => $typeFields
					));

					WorkgroupTemplate::create(array(
						'userId' => $arResult["currentUserId"],
						'name' => (!empty($arFields['NAME']) ? $arFields['NAME'] : $arResult['POST']['NAME']),
						'ownerId' => $ownerId,
						'type' => $typeCode,
						'params' => array(
							'description' => (isset($arFields['DESCRIPTION']) ? $arFields['DESCRIPTION'] : ''),
						)
					));
				}

				/* invite */
				if (
					strlen($errorMessage) <= 0 
					&& (
						!array_key_exists("TAB", $arResult) 
						|| $arResult["TAB"] == "invite"
					)
				)
				{
					if (
						CModule::IncludeModule('extranet')
						&& CModule::IncludeModule('intranet')
					)
					{
						$externalAuthIdList = \Bitrix\Socialnetwork\ComponentHelper::checkPredefinedAuthIdList(array('bot', 'imconnector', 'replica'));

						if (
							$_POST["EXTRANET_INVITE_ACTION"] == "invite"
							&& strlen($_POST["EMAILS"]) > 0
						)
						{
							if ($_POST["MESSAGE_TEXT"] != $inviteMessageTextDefault)
							{
								CUserOptions::setOption("socialnetwork", "invite_message_text", $_POST["MESSAGE_TEXT"]);
							}

							$arEmail = array();
							$arIntranetUsersEmails = array();
							$arInvitedExtranetUsers = array();
							$arEmailOriginal = preg_split("/[\n\r\t\\,;]+/", $_POST["EMAILS"]);

							$emailCnt = 0;
							foreach($arEmailOriginal as $addr)
							{
								if ($emailCnt >= 100)
								{
									break;
								}

								if(strlen($addr) > 0 && check_email($addr))
								{
									$addrX = "";
									$phraseX = "";
									$white_space = "(?:(?:\\r\\n)?[ \\t])";
									$spec = '()<>@,;:\\\\".\\[\\]';
									$cntl = '\\000-\\037\\177';
									$dtext = "[^\\[\\]\\r\\\\]";
									$domain_literal = "\\[(?:$dtext|\\\\.)*\\]$white_space*";
									$quoted_string = "\"(?:[^\\\"\\r\\\\]|\\\\.|$white_space)*\"$white_space*";
									$atom = "[^$spec $cntl]+(?:$white_space+|\\Z|(?=[\\[\"$spec]))";
									$word = "(?:$atom|$quoted_string)";
									$localpart = "$word(?:\\.$white_space*$word)*";
									$sub_domain = "(?:$atom|$domain_literal)";
									$domain = "$sub_domain(?:\\.$white_space*$sub_domain)*";
									$addr_spec = "$localpart\\@$white_space*$domain";
									$phrase = "$word*";

									if(preg_match("/$addr_spec/", $addr, $arMatches))
										$addrX = $arMatches[0];

									if(preg_match("/$localpart/", $addr, $arMatches))
										$phraseX = trim(trim($arMatches[0]), "\"");

									$arEmail[] = array("EMAIL" => $addrX, "NAME" => $phraseX);
									$emailCnt++;
								}
							}

							if (!empty($arEmail))
							{
								$userData = array(
									"GROUP_ID" => CIntranetInviteDialog::getUserGroups(SITE_ID, true)
								);

								foreach($arEmail as $email)
								{
									$arUser = array();
									$arFilter = array(
										"ACTIVE" => "Y",
										"=EMAIL" => $email["EMAIL"]
									);

									if (!empty($externalAuthIdList))
									{
										$arFilter['!=EXTERNAL_AUTH_ID'] = $externalAuthIdList;
									}

									$userID = 0;

									$rsUser = CUser::GetList(
										($by="id"),
										($order="asc"),
										$arFilter,
										array(
											"FIELDS" => array("ID", "EXTERNAL_AUTH_ID"),
											"SELECT" => array("UF_DEPARTMENT")
										)
									);
									if ($arUser = $rsUser->Fetch())
									{
										if (
											in_array($arUser['ID'], $moderatorIdList)
											|| $arUser['ID'] == $ownerId
										)
										{
											continue;
										}

										//if user with this e-mail is registered, but is external user
										if ($arUser["EXTERNAL_AUTH_ID"] == 'email')
										{
											$ID_TRANSFERRED = CIntranetInviteDialog::TransferEmailUser($arUser["ID"], array(
												"SITE_ID" => SITE_ID,
												"GROUP_ID" => $userData["GROUP_ID"]
											));

											if (!$ID_TRANSFERRED)
											{
												if($e = $APPLICATION->GetException())
												{
													$errorMessage .= $e->GetString();
												}
											}
											else
											{
												$arUserIDs[] = $ID_TRANSFERRED;
											}
										}
										elseif (
											empty($arUser["UF_DEPARTMENT"])
											|| (
												is_array($arUser["UF_DEPARTMENT"])
												&& intval($arUser["UF_DEPARTMENT"][0]) <= 0
											)
										)
										{
											$arUserIDs[] = $userID = $arUser["ID"];
											$checkword 	= $arUser["CONFIRM_CODE"];
										}
										else
										{
											$arIntranetUsersEmails[] = $email["EMAIL"];
											continue;
										}
									}
									else
									{
										$userData["EMAIL"] = $email["EMAIL"];
										$userData["LOGIN"] = $email["EMAIL"];
										$userData["CONFIRM_CODE"] = randString(8);
										
										$name = $last_name = "";
										if ($email["NAME"] <> '')
										{
											list($name, $last_name) = explode(" ", $email["NAME"]);
										}
										$userData["NAME"] = $name;
										$userData["LAST_NAME"] = $last_name;

										$ID = CIntranetInviteDialog::RegisterUser($userData, SITE_ID);

										if(is_array($ID))
										{
											foreach ($ID as $strErrorTmp)
											{
												$errorMessage .= $strErrorTmp;
											}
										}
										else
										{
											$arUserIDs[] = $ID;
											$userData['ID'] = $ID;
											CIntranetInviteDialog::InviteUser($userData, htmlspecialcharsbx($_POST["MESSAGE_TEXT"]));
										}
									}
								}
							}

							if (!empty($errorMessage))
							{
								$arResult["ErrorFields"][] = "EXTRANET_BLOCK";
							}
						}
						elseif (
							$_POST["EXTRANET_INVITE_ACTION"] == "add"
							&& CModule::IncludeModule("intranet")
						)
						{
							$userData = array(
								"ADD_EMAIL" => $_POST["ADD_EMAIL"],
								"ADD_NAME" => $_POST["ADD_NAME"],
								"ADD_LAST_NAME" => $_POST["ADD_LAST_NAME"],
								"ADD_SEND_PASSWORD" => $_POST["ADD_SEND_PASSWORD"]
							);

							$arFilter = array(
								"EMAIL" => $userData["ADD_EMAIL"]
							);
							if (!empty($externalAuthIdList))
							{
								$arFilter['!=EXTERNAL_AUTH_ID'] = $externalAuthIdList;
							}

							$rsUser = CUser::GetList(
								($by="id"),
								($order="asc"),
								$arFilter,
								array(
									"FIELDS" => array("ID", "EXTERNAL_AUTH_ID")
								)
							);
							if (
								($arUser = $rsUser->Fetch())
								&& ($arUser["EXTERNAL_AUTH_ID"] == 'email')
							)
							{
								$ID_ADDED = 0;
								$ID_TRANSFERRED = CIntranetInviteDialog::TransferEmailUser($arUser["ID"], array(
									"SITE_ID" => SITE_ID,
									"NAME" => $userData["ADD_NAME"],
									"LAST_NAME" => $userData["ADD_LAST_NAME"]
								));

								if (!$ID_TRANSFERRED)
								{
									if($e = $APPLICATION->GetException())
									{
										$errorMessage .= (strlen($errorMessage) > 0 ? "<br />" : "").$e->GetString();
									}
								}
							}
							else
							{
								$ID_ADDED = CIntranetInviteDialog::AddNewUser(SITE_ID, $userData, $strError);
							}

							if ($ID_ADDED <= 0)
							{
								$errorMessage .= (strlen($errorMessage) > 0 ? "<br />" : "").$strError;
								$arResult["ErrorFields"][] = "EXTRANET_BLOCK";
							}
							else
							{
								$arUserIDs[] = $ID_ADDED;
							}
						}
					}

					if (
						isset($arUserIDs)
						&& is_array($arUserIDs)
					)
					{
						foreach($arUserIDs as $key => $value)
						{
							if ($value == $arResult["currentUserId"])
							{
								unset($arUserIDs[$key]);
							}
						}
					}

					// send invitations and add users
					if (!empty($moderatorIdList))
					{
						foreach($moderatorIdList as $moderatorId)
						{
							CSocNetUserToGroup::add(array(
								"USER_ID" => $moderatorId,
								"GROUP_ID" => $arResult["GROUP_ID"],
								"ROLE" => UserToGroupTable::ROLE_MODERATOR,
								"=DATE_CREATE" => $DB->CurrentTimeFunction(),
								"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
								"MESSAGE" => "",
								"INITIATED_BY_TYPE" => UserToGroupTable::INITIATED_BY_GROUP,
								"INITIATED_BY_USER_ID" => $arResult["currentUserId"],
								"SEND_MAIL" => "N"
							));

							$chatNotificationResult = UserToGroup::addInfoToChat(array(
								'group_id' => $arResult["GROUP_ID"],
								'user_id' => $moderatorId,
								'action' => UserToGroup::CHAT_ACTION_IN,
								'sendMessage' => false
							));
						}
					}

					if (
						(
							!array_key_exists("TAB", $arResult)
							|| $arResult["TAB"] == "edit"
						)
						&& isset($ownerId)
						&& $ownerId != $arResult["currentUserId"]
						&& !in_array($arResult["currentUserId"], $moderatorIdList)
						&& !CSocNetUser::IsCurrentUserModuleAdmin() // not session admin
					)
					{
						CSocNetUserToGroup::add(array(
							"USER_ID" => $arResult["currentUserId"],
							"GROUP_ID" => $arResult["GROUP_ID"],
							"ROLE" => UserToGroupTable::ROLE_USER,
							"=DATE_CREATE" => $DB->CurrentTimeFunction(),
							"=DATE_UPDATE" => $DB->CurrentTimeFunction(),
							"MESSAGE" => "",
							"INITIATED_BY_TYPE" => UserToGroupTable::INITIATED_BY_GROUP,
							"INITIATED_BY_USER_ID" => $arResult["currentUserId"],
							"SEND_MAIL" => "N"
						));
					}

					if (
						!empty($arUserIDs)
						&& is_array($arUserIDs)
					)
					{
						foreach($arUserIDs as $user_id)
						{
							$canInviteGroup = CSocNetUserPerms::CanPerformOperation($arResult["currentUserId"], $user_id, "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin());
							$user2groupRelation = CSocNetUserToGroup::GetUserRole($user_id, $arResult["GROUP_ID"]);

							if (
								$canInviteGroup
								&& !$user2groupRelation
							)
							{
								$bMail = (
									!is_array($arInvitedExtranetUsers)
									|| !in_array($user_id, $arInvitedExtranetUsers)
								);

								if (!CSocNetUserToGroup::SendRequestToJoinGroup($arResult["currentUserId"], $user_id, $arResult["GROUP_ID"], $_POST["MESSAGE"], $bMail))
								{
									$rsUser = CUser::GetByID($user_id);
									if ($arUser = $rsUser->Fetch())
									{
										$arErrorUsers[] = array(
											CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, $bUseLogin),
											CSocNetUserPerms::CanPerformOperation($arResult["currentUserId"], $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin())
												? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]))
												: ""
										);
										if ($e = $APPLICATION->GetException())
										{
											$warningMessage .= $e->GetString();
										}
									}
								}
								elseif (
									is_array($arResult["POST"]["USER_IDS"])
									&& in_array($user_id, $arResult["POST"]["USER_IDS"])
								)
								{
									$bInvited = true;
									// delete from uninvited users list
									$arKeysFound = array_keys($arResult["POST"]["USER_IDS"], $user_id);
									foreach($arKeysFound as $key)
									{
										unset($arResult["POST"]["USER_IDS"][$key]);
									}
								}
							}
							//user already is related to group, don't invite him again
							else if (
								$canInviteGroup
								&& $user2groupRelation
							)
							{
								$rsUser = CUser::GetByID($user_id);
								if ($arRes = $rsUser->Fetch())
								{
									$email = $arRes["EMAIL"];
								}

								if (strlen($warningMessage) > 0)
								{
									$warningMessage .= "<br>";
								}

								switch ($user2groupRelation)
								{
									case SONET_ROLES_BAN:
										$warningMessage .= str_replace("#EMAIL#", $email, GetMessage("SONET_GCE_USER_BANNED_IN_GROUP"));
										break;
									case SONET_ROLES_REQUEST:
										$warningMessage .= str_replace("#EMAIL#", $email, GetMessage("SONET_GCE_USER_REQUEST_SENT"));
										break;
									default:
										$warningMessage .= str_replace("#EMAIL#", $email, GetMessage(empty($_POST["GROUP_PROJECT"]) && $_POST["GROUP_PROJECT"] == 'Y' ? "SONET_GCE_USER_IN_GROUP_PROJECT" : "SONET_GCE_USER_IN_GROUP"));
										break;
								}
							}
						}

						if (
							strlen($warningMessage) > 0
							&& !in_array("USERS", $arResult["ErrorFields"])
						)
						{
							$errorMessage .= $warningMessage.(!$bInvited ? "<br>".GetMessage("SONET_GCE_NO_USERS") : "").".";
							$warningMessage = "";
						}
					}

					if (
						isset($arDepartmentIDs)
						&& is_array($arDepartmentIDs)
						&& !empty($arDepartmentIDs)
					)
					{
						if (
							$arParams["GROUP_ID"] > 0
							&& !empty($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"])
							&& !empty($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"])
							&& !empty($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]["VALUE"])
							&& is_array($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]["VALUE"])
						)
						{
							$arDepartmentIDs = array_unique(array_map('intval', array_merge($arDepartmentIDs, $arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]["VALUE"])));
						}

						CSocNetGroup::Update($arResult["GROUP_ID"], array(
							'UF_SG_DEPT' => $arDepartmentIDs
						));
					}
				}

				//if some e-mails belong to internal users and can't be used for invitation
				if (count($arIntranetUsersEmails) == 1)
				{
					$warningMessage .= str_replace("#EMAIL#", HtmlSpecialCharsEx(implode("", $arIntranetUsersEmails)), GetMessage("SONET_GCE_CANNOT_EMAIL_ADD"));
				}
				elseif (count($arIntranetUsersEmails) > 1)
				{
					$warningMessage .= str_replace("#EMAIL#", HtmlSpecialCharsEx(implode(", ", $arIntranetUsersEmails)), GetMessage("SONET_GCE_CANNOT_EMAILS_ADD"));
				}

				//if no users were invited
				if (
					$arResult["TAB"] == "invite"
					&& empty($arUserIDs)
					&& empty($arDepartmentIDs)
				)
				{
					$errorMessage .= GetMessage("SONET_GCE_NO_USERS").". ";
					$arResult["ErrorFields"][] = "USERS";
				}
			}

			if (
				strlen($errorMessage) <= 0 
				&& strlen($warningMessage) <= 0
			)
			{
				if ($arResult["IS_IFRAME"])
				{
					if (
						$arResult["IS_POPUP"]
						|| $_GET["IFRAME_TYPE"] == "SIDE_SLIDER"
					)
					{
						if (!array_key_exists("TAB", $arResult))
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}
						else
						{
							$redirectPath = "";
						}
					}
					else
					{
						if (!array_key_exists("TAB", $arResult))
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}
						elseif ($arResult["TAB"] == "edit")
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}
						elseif ($arResult["TAB"] == "invite")
						{
							$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_EDIT"], array("group_id" => $arResult["GROUP_ID"], "user_id" => $arResult["currentUserId"]));
						}

						$redirectPath .= (strpos($redirectPath, "?") === false ? "?" :  "&")."POPUP=Y&SONET=Y";
						if ($arResult["TAB"] == "invite")
						{
							$redirectPath .= (strpos($redirectPath, "?") === false ? "?" :  "&")."tab=invite";
						}
						elseif ($arResult["TAB"] == "edit")
						{
							$redirectPath .= (strpos($redirectPath, "?") === false ? "?" :  "&")."tab=edit";
						}

						if ($bFirstStepSuccess)
						{
							$redirectPath .= "&CALLBACK=GROUP&GROUP_ID=".$arResult["GROUP_ID"];
						}
						else
						{
							$redirectPath .= "&CALLBACK=REFRESH";
						}
					}
				}
				else
				{
					$redirectPath = CComponentEngine::MakePathFromTemplate(
						(isset($arFields["PROJECT"]) && $arFields["PROJECT"] == "Y" ? $arParams["PATH_TO_GROUP_GENERAL"] : $arParams["PATH_TO_GROUP"]),
						array(
							"group_id" => $arResult["GROUP_ID"],
							"user_id" => $arResult["currentUserId"]
						)
					);
				}

				if ($_POST["ajax_request"] == "Y")
				{
					$groupFieldsList = array(
						'FIELDS' => array(),
						'UF' => array(),
					);
					__GCEGetGroup($arResult["GROUP_ID"], $groupFieldsList["UF"], $groupFieldsList["FIELDS"], "edit");

					$APPLICATION->RestartBuffer();
					echo CUtil::PhpToJsObject(array(
						'MESSAGE' => 'SUCCESS',
						'URL' => $redirectPath,
						'GROUP' => array_merge($groupFieldsList, array('ID' => $arResult["GROUP_ID"])),
						'ACTION' => (
							!array_key_exists("TAB", $arResult)
								? 'create'
								: $arResult["TAB"]
						)
					));
					require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
					die();
				}
				else
				{
					$APPLICATION->RestartBuffer();
					LocalRedirect($redirectPath);
				}
			}
			else
			{
				$arResult["WarningMessage"] = $warningMessage;
				$arResult["ErrorMessage"] = $errorMessage;

				if (!array_key_exists("TAB", $arResult))
				{
					if ($bFirstStepSuccess)
					{
						__GCEGetGroup($arResult["GROUP_ID"], $arResult["GROUP_PROPERTIES"], $arResult["POST"]);
						$arResult["CALLBACK"] = "EDIT";
					}

					if ($bSecondStepSuccess)
					{
						__GCE_GetFeatures($arResult["GROUP_ID"], $arResult["POST"]["FEATURES"]);
					}
				}

				if (
					is_array($arResult["POST"]["USER_IDS"])
					&& !empty($arResult["POST"]["USER_IDS"])
				)
				{
					$dbUsers = CUser::GetList(
						($sort_by = Array('last_name'=>'asc', 'IS_ONLINE'=>'desc')),
						($dummy=''),
						array(
							"ID" => implode("|", $arResult["POST"]["USER_IDS"]),
						),
						array(
							"FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION"),
							"SELECT" => array("UF_DEPARTMENT")
						)
					);

					while($arUser = $dbUsers->Fetch())
					{
						$arUserTmp = array(
							"id" => "U".$arUser["ID"],
							"entityId" => $arUser["ID"],
							"name" => trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser)),
							"avatar" => "",
							"desc" => $arUser["WORK_POSITION"] ? $arUser["WORK_POSITION"] : ($arUser["PERSONAL_PROFESSION"] ? $arUser["PERSONAL_PROFESSION"] : "&nbsp;")
						);
						$arResult["POST"]["USERS_FOR_JS"]["U".$arUser["ID"]] = $arUserTmp;

						if (
							$arResult["bExtranetInstalled"]
							&& $arResult["POST"]["IS_EXTRANET_GROUP"] == "Y"
						)
						{
							$arResult["POST"]["USERS_FOR_JS_".(empty($arUser["UF_DEPARTMENT"]) || (is_array($arUser["UF_DEPARTMENT"]) && intval($arUser["UF_DEPARTMENT"][0]) <= 0) ? "E" : "I")]["U".$arUser["ID"]] = $arUserTmp;
						}
					}
				}

				if ($_POST["ajax_request"] == "Y")
				{
					ob_end_clean();

					$arRes = array(
						'ERROR' => $errorMessage,
						'WARNING' => $warningMessage,
						'USERS_ID' => $arResult["POST"]["USER_IDS"]
					);

					echo CUtil::PhpToJsObject($arRes);
					die();
				}
			}
		}
		else
		{
			$arResult["GROUP_ID"] = $arParams["GROUP_ID"];
		}

		if ($arResult["ShowForm"] == "Input")
		{
			if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "edit")
			{
				$arResult["Subjects"] = array();
				$dbSubjects = CSocNetGroupSubject::GetList(
					array("SORT"=>"ASC", "NAME" => "ASC"),
					array("SITE_ID" => SITE_ID),
					false,
					false,
					array("ID", "NAME")
				);
				while ($arSubject = $dbSubjects->getNext())
				{
					$arResult["Subjects"][$arSubject["ID"]] = $arSubject["NAME"];
				}

				$arResult["InitiatePerms"] = \Bitrix\Socialnetwork\Item\Workgroup::getInitiatePermOptionsList(array(
					'project' => false
				));
				$arResult["InitiatePermsProject"] = \Bitrix\Socialnetwork\Item\Workgroup::getInitiatePermOptionsList(array(
					'project' => true
				));
				$arResult["SpamPerms"] = array(
					UserToGroupTable::ROLE_OWNER => GetMessage("SONET_GCE_IP_OWNER"),
					UserToGroupTable::ROLE_MODERATOR => GetMessage("SONET_GCE_IP_MOD"),
					UserToGroupTable::ROLE_USER => GetMessage("SONET_GCE_IP_USER"),
					SONET_ROLES_ALL => GetMessage("SONET_GCE_IP_ALL"),
				);
			}
		}

		if (
			!array_key_exists("TAB", $arResult)
			|| $arResult["TAB"] == "invite"
		)
		{
			$arResult["DEST_SORT"] = CSocNetLogDestination::GetDestinationSort(array(
				"DEST_CONTEXT" => $arResult['destinationContextUsers'],
				"CODE_TYPE" => 'U'
			));
			$arResult["DEST_USERS_LAST"] = array();
			$arResult["LAST_SORT"] = CSocNetLogDestination::fillLastDestination($arResult["DEST_SORT"], $arResult["DEST_USERS_LAST"]);

			if (isset($arResult["DEST_USERS_LAST"]['USERS']))
			{
				$arResult["DEST_USERS_LAST"] = $arResult["DEST_USERS_LAST"]['USERS'];
			}

			$arResult["siteDepartmentID"] = COption::GetOptionString("main", "wizard_departament", false, SITE_ID, true);

			if (
				is_array($arResult["DEST_USERS_LAST"])
				&& !empty($arResult["DEST_USERS_LAST"])
			)
			{
				foreach($arResult["DEST_USERS_LAST"] as $key => $user_code)
				{
					if ($user_code == 'U'.$arResult["currentUserId"])
					{
						unset($arResult["DEST_USERS_LAST"][$key]);
						break;
					}
				}
			}

			if (
				is_array($arResult["DEST_USERS_LAST"])
				&& !empty($arResult["DEST_USERS_LAST"])
			)
			{
				$arLastUserID = array();

				foreach($arResult["DEST_USERS_LAST"] as $key => $user_code)
				{
					if ($user_code == 'U'.$arResult["currentUserId"])
					{
						unset($arResult["DEST_USERS_LAST"][$key]);
						continue;
					}

					if(preg_match('/^U(\d+)$/', $key, $match))
					{
						$arLastUserID[] = $match[1];
					}
				}

				if (!empty($arLastUserID))
				{
					$dbUsers = CUser::GetList(
						($sort_by = Array('last_name'=>'asc', 'IS_ONLINE'=>'desc')),
						($dummy=''),
						array(
							"ACTIVE" => "Y",
							"ID" => implode("|", $arLastUserID),
						),
						array(
							"FIELDS" => array("ID", "LAST_NAME", "NAME", "SECOND_NAME", "LOGIN", "PERSONAL_PHOTO", "WORK_POSITION", "PERSONAL_PROFESSION"),
							"SELECT" => array("UF_DEPARTMENT")
						)
					);

					while($arUser = $dbUsers->Fetch())
					{
						if (intval($arResult["siteDepartmentID"]) > 0)
						{
							$arUserGroupCode = CAccess::GetUserCodesArray($arUser["ID"]);

							if (!in_array("DR".intval($arResult["siteDepartmentID"]), $arUserGroupCode))
							{
								continue;
							}
						}

						$arFileTmp = CFile::ResizeImageGet(
							$arUser["PERSONAL_PHOTO"],
							array('width' => 32, 'height' => 32),
							BX_RESIZE_IMAGE_EXACT,
							false
						);

						$arUserTmp = array(
							"id" => "U".$arUser["ID"],
							"entityId" => $arUser["ID"],
							"name" => trim(CUser::FormatName(empty($arParams["NAME_TEMPLATE"]) ? CSite::GetNameFormat(false) : $arParams["NAME_TEMPLATE"], $arUser)),
							"avatar" => (empty($arFileTmp['src'])? '': $arFileTmp['src']),
							"desc" => $arUser["WORK_POSITION"] ? $arUser["WORK_POSITION"] : ($arUser["PERSONAL_PROFESSION"] ? $arUser["PERSONAL_PROFESSION"] : "&nbsp;")
						);

						$key = (
							!$arResult["bExtranetInstalled"]
								? "USERS_FOR_JS"
								: (
									empty($arUser["UF_DEPARTMENT"])
									|| (
										is_array($arUser["UF_DEPARTMENT"])
										&& intval($arUser["UF_DEPARTMENT"][0]) <= 0
									)
										? "USERS_FOR_JS_E"
										: "USERS_FOR_JS_I"
							)
						);
						if (!array_key_exists("U".$arUser["ID"], $arResult["POST"][$key]))
						{
							$arResult["POST"][$key]["U".$arUser["ID"]] = $arUserTmp;
						}
					}
				}
			}
		}

		$arResult["arSocNetFeaturesSettings"] = CSocNetAllowed::getAllowedFeatures();
	}

	$arResult["step1Display"] = (
		$arResult["USE_PRESETS"] == 'Y'
		&& $arParams["GROUP_ID"] <= 0
	);

	$arResult["hidePresetSettings"] = (
		$arResult["USE_PRESETS"] == 'Y'
		&& false // temporary
	);

	$arResult["URL_CANCEL"] = (
		$arParams["GROUP_ID"] > 0
			? $arResult["Urls"]["Group"]
			: $strSiteWorkgroupsPage
	);
}

CModule::IncludeModule('intranet');
$APPLICATION->SetTitle(Loc::getMessage(
	$arParams["GROUP_ID"] > 0
		? (
	$arResult["POST"]["PROJECT"] == "Y"
		? ($arResult["TAB"] == "invite" ? "SONET_GCE_TITLE_INVITE_PROJECT" : "SONET_GCE_TITLE_EDIT_PROJECT")
		: ($arResult["TAB"] == "invite" ? "SONET_GCE_TITLE_INVITE" : "SONET_GCE_TITLE_EDIT")
	)
		: "SONET_GCE_TITLE_CREATE"
));

$this->IncludeComponentTemplate();
