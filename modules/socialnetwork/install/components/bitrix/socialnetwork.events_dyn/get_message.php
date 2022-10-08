<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$cuid = intval($_REQUEST["cuid"]);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(
	$CACHE_MANAGER->Read(86400*30, "socnet_cm_".$cuid)
	&& $CACHE_MANAGER->Read(86400*30, "socnet_cf_".$cuid)
	&& $CACHE_MANAGER->Read(86400*30, "socnet_cg_".$cuid)
)
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
}
/*
$abs_path = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/managed_flags/socnet/c/".IntVal($cuid / 1000)."/".$cuid;

if (
	!file_exists($abs_path."_m")
	&& !file_exists($abs_path."_f")
	&& !file_exists($abs_path."_g")
)
	die();
*/

$site = trim($_REQUEST["site"]);
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);

// require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(CModule::IncludeModule("socialnetwork"))
{
	$userID = intval($_REQUEST["user_id"]);
	$mptr = Trim($_REQUEST["mptr"]);

	$arParams["PATH_TO_USER"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["up"]));
	$arParams["PATH_TO_GROUP"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["gp"]));
	$arParams["PATH_TO_MESSAGE_FORM"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["mp"]));
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["mpm"]));
	$arParams["PATH_TO_MESSAGES_CHAT"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["cp"]));
	$arParams["POPUP"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["popup"]));
	if (array_key_exists("pt", $_REQUEST) && $_REQUEST["pt"] == "Y")
		$arParams["POPUP_TEMPLATE"] = true;
	else
		$arParams["POPUP_TEMPLATE"] = false;

	if (trim($_REQUEST["nt"]) <> '')
		$arParams['NAME_TEMPLATE'] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["nt"]));
	else
		$arParams['NAME_TEMPLATE'] = CSite::GetNameFormat();

	if (trim($_REQUEST["sl"]) == "N")
		$bUseLogin = false;
	else
		$bUseLogin = true;

	$arData = array();

	if (!$GLOBALS["USER"]->IsAuthorized())
	{
		$arData[0] = "*";
	}
	else
	{
		$parser = new CSocNetTextParser(LANGUAGE_ID, "/bitrix/images/socialnetwork/smile/");
		$parser->MaxStringLen = 20;

		$bFound = false;

		if (!$bFound)
		{
			$arFilter = array(
					"SECOND_USER_ID" => $GLOBALS["USER"]->GetID(),
					"RELATION" => SONET_RELATIONS_REQUEST
				);

			if ($arParams["POPUP"] == "Y")
			{
				$last_message_id = CUserOptions::GetOption('socialnetwork', 'SONET_EVENT_FR', 0);
				if (intval($last_message_id) > 0)
					$arFilter[">ID"] = $last_message_id;
			}

			$dbUserRequests = CSocNetUserRelations::GetList(
				array("DATE_UPDATE" => "ASC"),
				$arFilter,
				false,
				array("nTopCount" => 1),
				array("ID", "FIRST_USER_ID", "MESSAGE", "FIRST_USER_NAME", "DATE_UPDATE", "FIRST_USER_LAST_NAME", "FIRST_USER_SECOND_NAME", "FIRST_USER_LOGIN", "FIRST_USER_PERSONAL_PHOTO", "FIRST_USER_PERSONAL_GENDER")
			);
			if ($arUserRequests = $dbUserRequests->GetNext())
			{
				if ($arParams["POPUP"] == "Y")
					CUserOptions::SetOption('socialnetwork', 'SONET_EVENT_FR', $arUserRequests["ID"]);

				$bFound = true;
				$arData[0] = "FR";

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["FIRST_USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FIRST_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				if ($arParams["POPUP_TEMPLATE"])
					$ImageSize = 50;
				else
					$ImageSize = 150;

				if (intval($arUserRequests["FIRST_USER_PERSONAL_PHOTO"]) <= 0)
				{
					switch ($arUserRequests["FIRST_USER_PERSONAL_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
								break;
						default:
							$suffix = "unknown";
					}
					$arUserRequests["FIRST_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, $site);
				}
				$arImage = CSocNetTools::InitImage($arUserRequests["FIRST_USER_PERSONAL_PHOTO"], $ImageSize, "/bitrix/images/socialnetwork/nopic_user_".$ImageSize.".gif", $ImageSize, $pu, $canViewProfile);

				$arData[1] = $arUserRequests["ID"];
				$arData[2] = $arUserRequests["FIRST_USER_ID"]; //2

//				$arData[3] = $arUserRequests["FIRST_USER_NAME"]." ".$arUserRequests["FIRST_USER_LAST_NAME"]; //3

				$arTmpUser = array(
					"NAME" => $arUserRequests["~FIRST_USER_NAME"],
					"LAST_NAME" => $arUserRequests["~FIRST_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequests["~FIRST_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequests["~FIRST_USER_LOGIN"]
				);
				$arData[3] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

				$arData[4] = $arImage["IMG"]; //4
				$arData[5] = $pu; //5
				$arData[6] = ($canViewProfile ? "Y" : "N"); //6
				$arData[7] = (CSocNetUser::IsOnLine($arUserRequests["FIRST_USER_ID"]) ? "Y" : "N"); //7
				$arData[8] = $arUserRequests["DATE_UPDATE"]; //8
				$arData[9] = $parser->convert(
						$arUserRequests["~MESSAGE"],
						false,
						array(),
						array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"BIU" => "Y",
							"IMG" => "Y",
							"LIST" => "Y",
							"QUOTE" => "Y",
							"CODE" => "Y",
							"FONT" => "Y",
							"SMILES" => "Y",
							"UPLOAD" => "N",
							"NL2BR" => "N"
						)
					); //9

				$arData[10] = "EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=add"; //10
				$arData[11] = "EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=reject"; //11
			}
		}


		if (!$bFound)
		{
			$arFilter = array(
					"USER_ID" => $GLOBALS["USER"]->GetID(),
					"ROLE" => SONET_ROLES_REQUEST,
					"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
				);

			if ($arParams["POPUP"] == "Y")
			{
				$last_message_id = CUserOptions::GetOption('socialnetwork', 'SONET_EVENT_GR', 0);
				if (intval($last_message_id) > 0)
					$arFilter[">ID"] = $last_message_id;
			}

			$dbUserRequests = CSocNetUserToGroup::GetList(
				array("DATE_CREATE" => "ASC"),
				$arFilter,
				false,
				array("nTopCount" => 1),
				array("ID", "INITIATED_BY_USER_ID", "MESSAGE", "INITIATED_BY_USER_NAME", "DATE_CREATE", "INITIATED_BY_USER_LAST_NAME", "INITIATED_BY_USER_SECOND_NAME", "INITIATED_BY_USER_LOGIN", "INITIATED_BY_USER_PHOTO", "INITIATED_BY_USER_GENDER", "GROUP_ID", "GROUP_NAME", "GROUP_IMAGE_ID", "GROUP_VISIBLE")
			);
			if ($arUserRequests = $dbUserRequests->GetNext())
			{
				if ($arParams["POPUP"] == "Y")
					CUserOptions::SetOption('socialnetwork', 'SONET_EVENT_GR', $arUserRequests["ID"]);

				$bFound = true;
				$arData[0] = "GR"; //0

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["INITIATED_BY_USER_ID"]));
				$canViewProfileU = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["INITIATED_BY_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				if ($arParams["POPUP_TEMPLATE"])
					$ImageSize = 50;
				else
					$ImageSize = 150;

				if (intval($arUserRequests["INITIATED_BY_USER_PHOTO"]) <= 0)
				{
					switch ($arUserRequests["INITIATED_BY_USER_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
								break;
						default:
							$suffix = "unknown";
					}
					$arUserRequests["INITIATED_BY_USER_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, $site);
				}
				$arImage = CSocNetTools::InitImage($arUserRequests["INITIATED_BY_USER_PHOTO"], $ImageSize, "/bitrix/images/socialnetwork/nopic_user_".$ImageSize.".gif", $ImageSize, $pu, $canViewProfileU);

				$pg = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arUserRequests["GROUP_ID"]));
				$canViewProfileG = (CSocNetUser::IsCurrentUserModuleAdmin() || ($arUserRequests["GROUP_VISIBLE"] == "Y"));

				if ($arParams["POPUP_TEMPLATE"])
					$ImageSize = 50;
				else
					$ImageSize = 150;

				if (intval($arUserRequests["GROUP_IMAGE_ID"]) <= 0)
					$arUserRequests["GROUP_IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

				$arImageG = CSocNetTools::InitImage($arUserRequests["GROUP_IMAGE_ID"], $ImageSize, "/bitrix/images/socialnetwork/nopic_group_".$ImageSize.".gif", $ImageSize, $pg, $canViewProfileG);

				$arData[1] = $arUserRequests["ID"]; //1
				$arData[2] = $arUserRequests["INITIATED_BY_USER_ID"]; //2

//				$arData[3] = $arUserRequests["INITIATED_BY_USER_NAME"]." ".$arUserRequests["INITIATED_BY_USER_LAST_NAME"]; //3

				$arTmpUser = array(
					"NAME" => $arUserRequests["~INITIATED_BY_USER_NAME"],
					"LAST_NAME" => $arUserRequests["~INITIATED_BY_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequests["~INITIATED_BY_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequests["~INITIATED_BY_USER_LOGIN"]
				);
				$arData[3] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

				$arData[4] = $arImage["IMG"]; //4
				$arData[5] = $pu; //5
				$arData[6] = ($canViewProfileU ? "Y" : "N"); //6
				$arData[7] = $arUserRequests["DATE_CREATE"]; //7
				$arData[8] = $arUserRequests["GROUP_NAME"]; //8
				$arData[9] = $arImageG["IMG"]; //9
				$arData[10] = $pg; //10
				$arData[11] = ($canViewProfileG ? "Y" : "N"); //11
				$arData[12] = $parser->convert(
						$arUserRequests["~MESSAGE"],
						false,
						array(),
						array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"BIU" => "Y",
							"IMG" => "Y",
							"LIST" => "Y",
							"QUOTE" => "Y",
							"CODE" => "Y",
							"FONT" => "Y",
							"SMILES" => "Y",
							"UPLOAD" => "N",
							"NL2BR" => "N"
						)
					); //12

				$arData[13] = "EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=add"; //13
				$arData[14] = "EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=reject"; //14
			}
		}


		if (!$bFound)
		{
			$arFilter = array(
					"TO_USER_ID" => $GLOBALS["USER"]->GetID(),
					"DATE_VIEW" => "",
					"TO_DELETED" => "N"
				);
			if ($arParams["POPUP"] == "Y")
			{
				$last_message_id = CUserOptions::GetOption('socialnetwork', 'SONET_EVENT_M', 0);
				if (intval($last_message_id) > 0)
					$arFilter[">ID"] = $last_message_id;
			}

			$dbUserRequests = CSocNetMessages::GetList(
				array("DATE_CREATE" => "ASC"),
				$arFilter,
				false,
				array("nTopCount" => 1),
				array("ID", "FROM_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "MESSAGE_TYPE", "FROM_USER_NAME", "FROM_USER_LAST_NAME", "FROM_USER_SECOND_NAME", "FROM_USER_LOGIN", "FROM_USER_PERSONAL_PHOTO", "FROM_USER_PERSONAL_GENDER")
			);
			if ($arUserRequests = $dbUserRequests->GetNext())
			{
				if ($arParams["POPUP"] == "Y")
					CUserOptions::SetOption('socialnetwork', 'SONET_EVENT_M', $arUserRequests["ID"]);

				$bFound = true;
				$arData[0] = "M"; //0

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["FROM_USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FROM_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
				$canAnsver = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FROM_USER_ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin());

				if ($arParams["POPUP_TEMPLATE"])
					$ImageSize = 50;
				else
					$ImageSize = 150;

				if (intval($arUserRequests["FROM_USER_PERSONAL_PHOTO"]) <= 0)
				{
					switch ($arUserRequests["FROM_USER_PERSONAL_GENDER"])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
								break;
						default:
							$suffix = "unknown";
					}
					$arUserRequests["FROM_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, $site);
				}
				$arImage = CSocNetTools::InitImage($arUserRequests["FROM_USER_PERSONAL_PHOTO"], $ImageSize, "/bitrix/images/socialnetwork/nopic_user_".$ImageSize.".gif", $ImageSize, $pu, $canViewProfile);

				$arData[1] = $arUserRequests["ID"]; //1
				$arData[2] = $arUserRequests["FROM_USER_ID"]; //2

//				$arData[3] = $arUserRequests["FROM_USER_NAME"]." ".$arUserRequests["FROM_USER_LAST_NAME"];

				$arTmpUser = array(
					"NAME" => $arUserRequests["~FROM_USER_NAME"],
					"LAST_NAME" => $arUserRequests["~FROM_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequests["~FROM_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequests["~FROM_USER_LOGIN"]
				);
				$arData[3] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

				$arData[4] = $arImage["IMG"]; //4
				$arData[5] = $pu; //5
				$arData[6] = ($canViewProfile ? "Y" : "N"); //6
				$arData[7] = (CSocNetUser::IsOnLine($arUserRequests["FROM_USER_ID"]) ? "Y" : "N"); //7
				$arData[8] = $arUserRequests["DATE_CREATE"]; //8
				$arData[9] = $arUserRequests["MESSAGE_TYPE"]; //9
				//$arData[0] = $arUserRequests["TITLE"]; //10
				$arData[10] = $parser->convert(
						$arUserRequests["~MESSAGE"],
						false,
						array(),
						array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"BIU" => "Y",
							"IMG" => "Y",
							"LIST" => "Y",
							"QUOTE" => "Y",
							"CODE" => "Y",
							"FONT" => "Y",
							"SMILES" => "Y",
							"UPLOAD" => "N",
							"NL2BR" => "N"
						)
					); //10

				$arData[11] = (($arUserRequests["MESSAGE_TYPE"] == SONET_MESSAGE_PRIVATE && $canAnsver) ? "Y" : "N"); //11
				$arData[12] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM_MESS"], array("user_id" => $arUserRequests["FROM_USER_ID"], "message_id" => $arUserRequests["ID"])); //12
				$arData[13] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_CHAT"], array("user_id" => $arUserRequests["FROM_USER_ID"], "message_id" => $arUserRequests["ID"])); //13
				$arData[14] = "EventType=Message&eventID=".$arUserRequests["ID"]."&action=close"; //14
				$arData[15] = ((!CSocNetUser::IsUserModuleAdmin($arUserRequests["FROM_USER_ID"]) && $arUserRequests["MESSAGE_TYPE"] == SONET_MESSAGE_PRIVATE) ? "Y" : "N"); //15
				$arData[16] = "EventType=Message&userID=".$arUserRequests["FROM_USER_ID"]."&action=ban"; //16
			}
		}
	}

	echo CUtil::PhpToJSObject($arData);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>