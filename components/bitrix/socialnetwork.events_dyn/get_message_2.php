<?
define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("PUBLIC_AJAX_MODE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$cuid = intval($_REQUEST["cuid"]);
$site_id = (isset($_REQUEST["site"]) && is_string($_REQUEST["site"])) ? trim($_REQUEST["site"]) : "";

if (isset($_REQUEST["is"]))
	$ImageSize = intval($_REQUEST["is"]);
else
	$ImageSize = 0;

if ($ImageSize <= 0)
	$ImageSize = 42;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Localization\Loc;

if ($GLOBALS["USER"]->IsAuthorized())
	$log_cnt = CUserCounter::GetValueByUserID($GLOBALS["USER"]->GetID(), $site);

if(
	$CACHE_MANAGER->Read(86400*30, "socnet_cm_".$cuid)
	&& $CACHE_MANAGER->Read(86400*30, "socnet_cf_".$cuid)
	&& $CACHE_MANAGER->Read(86400*30, "socnet_cg_".$cuid)
)
{
	if (intval($log_cnt) > 0)
	{
		$arData = array(
			array("LOG_CNT" => $log_cnt)
		);
		echo CUtil::PhpToJSObject($arData);

		define('PUBLIC_AJAX_MODE', true);
	}

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
	die();
}

$rsSites = CSite::GetByID($site);
if ($arSite = $rsSites->Fetch())
{
	$DateTimeFormat = $arSite["FORMAT_DATETIME"];
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
	Loc::loadLanguageFile(__FILE__);
}
else
{
	die();
}

if(CModule::IncludeModule("socialnetwork"))
{
	$userID = intval($_REQUEST["user_id"]);
	$mptr = Trim($_REQUEST["mptr"]);

	if(isset($_REQUEST["log"]))
		$log = Trim($_REQUEST["log"]);
	else
		$log = "N";

	$arParams["PATH_TO_USER"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["up"]));
	$arParams["PATH_TO_GROUP"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["gp"]));
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["mpm"]));

	if (trim($_REQUEST["nt"]) <> '')
		$arParams["NAME_TEMPLATE"] = Trim($GLOBALS["APPLICATION"]->UnJSEscape($_REQUEST["nt"]));
	else
		$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();

	$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
		array("#NOBR#", "#/NOBR#"),
		array("", ""),
		$arParams["NAME_TEMPLATE"]
	);

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
		$site_datetime_format = CSite::GetDateFormat("FULL", $site);
		$date_pos = false;
		$date_pos = mb_strpos($site_datetime_format, "DD");
		if ($date_pos === false)
			$date_pos = mb_strpos($site_datetime_format, "MM");
		if ($date_pos === false)
			$date_pos = mb_strpos($site_datetime_format, "YY");

		$time_pos = false;
		$time_pos = mb_strpos($site_datetime_format, "HH");
		if ($time_pos === false)
			$time_pos = mb_strpos($site_datetime_format, "MI");
		if ($time_pos === false)
			$time_pos = mb_strpos($site_datetime_format, "SS");

		$last_message_ts = CUserOptions::GetOption('socialnetwork', 'SONET_EVENT_TIMESTAMP', 0);

		$parser = new CSocNetTextParser($arSite["LANGUAGE_ID"], "/bitrix/images/socialnetwork/smile/");
		$parser->MaxStringLen = 20;

		$bGet = true;
		if (intval($last_message_ts) > 0)
		{
			$last_message_ts += CTimeZone::GetOffset();

			$bGet = false;
			// get all new (from UserOption) messages of all types

			$arFilter = array(
				"SECOND_USER_ID" => $GLOBALS["USER"]->GetID(),
				"RELATION" => SONET_RELATIONS_REQUEST,
				">DATE_CREATE" => ConvertTimeStamp($last_message_ts, "FULL", $site)
			);

			$dbUserRequests = CSocNetUserRelations::GetList(
				array(),
				$arFilter,
				false,
				array("nTopCount" => 1),
				array("ID")
			);
			if ($arUserRequests = $dbUserRequests->Fetch())
				$bGet = true;

			if (!$bGet)
			{
				$arFilter = array(
						"USER_ID" => $GLOBALS["USER"]->GetID(),
						"ROLE" => SONET_ROLES_REQUEST,
						"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
						">DATE_CREATE" => ConvertTimeStamp($last_message_ts, "FULL", $site)
					);

				$dbUserRequests = CSocNetUserToGroup::GetList(
					array(),
					$arFilter,
					false,
					array("nTopCount" => 1),
					array("ID")
				);
				if ($arUserRequests = $dbUserRequests->Fetch())
					$bGet = true;
			}

			if (!$bGet)
			{
				$arFilter = array(
						"TO_USER_ID" => $GLOBALS["USER"]->GetID(),
						"DATE_VIEW" => "",
						"TO_DELETED" => "N",
						">DATE_CREATE" => ConvertTimeStamp($last_message_ts, "FULL", $site)
					);

				if ($log == "Y")
					$arFilter["IS_LOG_ALL"] = "Y";

				$dbUserRequests = CSocNetMessages::GetList(
					array(),
					$arFilter,
					false,
					array("nTopCount" => 1),
					array("ID")
				);
				if ($arUserRequests = $dbUserRequests->Fetch())
					$bGet = true;
			}
		}

		if ($bGet)
		{
			$arFilter = array(
				"SECOND_USER_ID" => $GLOBALS["USER"]->GetID(),
				"RELATION" => SONET_RELATIONS_REQUEST
			);

			$dbUserRequests = CSocNetUserRelations::GetList(
				array("DATE_UPDATE" => "ASC"),
				$arFilter,
				false,
				array("nTopCount" => 50),
				array("ID", "FIRST_USER_ID", "MESSAGE", "FIRST_USER_NAME", "DATE_CREATE", "DATE_UPDATE", "FIRST_USER_LAST_NAME", "FIRST_USER_SECOND_NAME", "FIRST_USER_LOGIN", "FIRST_USER_PERSONAL_PHOTO", "FIRST_USER_PERSONAL_GENDER", "FIRST_USER_IS_ONLINE")
			);
			while ($arUserRequests = $dbUserRequests->GetNext())
			{
				$arTmpData = array();
				$arTmpData["TYPE"] = "FR";

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["FIRST_USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FIRST_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin($site));

				$arTmpData["IMAGE_USER"] = "/bitrix/images/1.gif";
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

				if (intval($arUserRequests["FIRST_USER_PERSONAL_PHOTO"]) > 0)
				{
					$imageFile = CFile::GetFileArray($arUserRequests["FIRST_USER_PERSONAL_PHOTO"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $ImageSize, "height" => $ImageSize),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$arTmpData["IMAGE_USER"] = $arFileTmp["src"];
					}
				}

				$arTmpData["ID"] = $arUserRequests["ID"];
				$arTmpData["ID_USER"] = $arUserRequests["FIRST_USER_ID"];

				$arTmpUser = array(
					"NAME" => $arUserRequests["~FIRST_USER_NAME"],
					"LAST_NAME" => $arUserRequests["~FIRST_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequests["~FIRST_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequests["~FIRST_USER_LOGIN"]
				);
				$arTmpData["NAME_USER"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
				$arTmpData["NAME_USER_TITLE"] = CUser::FormatName($arParams["TITLE_NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
				$arTmpData["URL_USER"] = $pu;
				$arTmpData["CAN_VIEW_USER"] = ($canViewProfile ? "Y" : "N");
				$arTmpData["IS_ONLINE"] = $arUserRequests["FIRST_USER_IS_ONLINE"];
				$arTmpData["DATE"] = $arUserRequests["DATE_UPDATE"];
				$arTmpData["DATE_TIMESTAMP"] = MakeTimeStamp($arUserRequests["DATE_CREATE"], $DateTimeFormat);

				$arTmpData["DATE_DATE_FORMATTED"] = ConvertTimeStamp($arTmpData["DATE_TIMESTAMP"], "SHORT", $site);
				$arTmpData["DATE_DATETIME_FORMATTED"] = ConvertTimeStamp($arTmpData["DATE_TIMESTAMP"], "FULL", $site);

				if ($time_pos > $date_pos)
					$arTmpData["DATE_TIME_FORMATTED"] = trim(mb_substr($arTmpData["DATE_DATETIME_FORMATTED"], mb_strlen($arTmpData["DATE_DATE_FORMATTED"])), " ,.;:");
				elseif ($date_pos > $time_pos && $time_pos !== false)
					$arTmpData["DATE_TIME_FORMATTED"] = trim(mb_substr($arTmpData["DATE_DATETIME_FORMATTED"], 0, $date_pos), " ,.;:");
				else
					$arTmpData["DATE_TIME_FORMATTED"] = "";

				if (date("d", $arTmpData["DATE_TIMESTAMP"]) == date("d") && date("n", $arTmpData["DATE_TIMESTAMP"]) == date("n") && date("Y", $arTmpData["DATE_TIMESTAMP"]) == date("Y"))
					$arTmpData["DATE_DAY"] = "TODAY";
				elseif ((mktime(0, 0, 0, date("n"), date("d"), date("Y")) - $arTmpData["DATE_TIMESTAMP"]) < 60*60*24)
					$arTmpData["DATE_DAY"] = "YESTERDAY";
				else
					$arTmpData["DATE_DAY"] = "";

				$arTmpData["MESSAGE"] = $parser->convert(
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
					);

				$arTmpData["BUTTONS"] = array(
						array(
							"NAME" => GetMessage("SONET_C2_FR_ADD"),
							"ID" => "add",
							"URL" => "EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=add"
						),
						array(
							"NAME" => GetMessage("SONET_C2_REJECT"),
							"ID" => "reject",
							"URL" => "EventType=FriendRequest&eventID=".$arUserRequests["ID"]."&action=reject"
						)
					);

				$arData[] = $arTmpData;

			} // while

			$arFilter = array(
				"USER_ID" => $GLOBALS["USER"]->GetID(),
				"ROLE" => SONET_ROLES_REQUEST,
				"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
			);

			$dbUserRequests = CSocNetUserToGroup::GetList(
				array("DATE_CREATE" => "ASC"),
				$arFilter,
				false,
				array("nTopCount" => 50),
				array("ID", "INITIATED_BY_USER_ID", "MESSAGE", "INITIATED_BY_USER_NAME", "DATE_CREATE", "INITIATED_BY_USER_LAST_NAME", "INITIATED_BY_USER_SECOND_NAME", "INITIATED_BY_USER_LOGIN", "INITIATED_BY_USER_PHOTO", "INITIATED_BY_USER_GENDER", "GROUP_ID", "GROUP_NAME", "GROUP_IMAGE_ID", "GROUP_VISIBLE")
			);
			while ($arUserRequests = $dbUserRequests->GetNext())
			{
				if (!empty($arUserRequests['GROUP_NAME']))
				{
					$arUserRequests['GROUP_NAME'] = \Bitrix\Main\Text\Emoji::decode($arUserRequests['GROUP_NAME']);
				}

				$arTmpData = array();
				$arTmpData["TYPE"] = "GR";

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["INITIATED_BY_USER_ID"]));
				$canViewProfileU = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["INITIATED_BY_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin($site));

				$arTmpData["IMAGE_USER"] = "/bitrix/images/1.gif";

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

				if (intval($arUserRequests["INITIATED_BY_USER_PHOTO"]) > 0)
				{
					$imageFile = CFile::GetFileArray($arUserRequests["INITIATED_BY_USER_PHOTO"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $ImageSize, "height" => $ImageSize),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$arTmpData["IMAGE_USER"] = $arFileTmp["src"];
					}
				}

				$pg = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arUserRequests["GROUP_ID"]));
				$canViewProfileG = (CSocNetUser::IsCurrentUserModuleAdmin($site) || ($arUserRequests["GROUP_VISIBLE"] == "Y"));

				$arTmpData["IMAGE_GROUP"] = "/bitrix/images/1.gif";

				if (intval($arUserRequests["GROUP_IMAGE_ID"]) <= 0)
					$arUserRequests["GROUP_IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, $site);

				if (intval($arUserRequests["GROUP_IMAGE_ID"]) > 0)
				{
					$imageFile = CFile::GetFileArray($arUserRequests["GROUP_IMAGE_ID"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $ImageSize, "height" => $ImageSize),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$arTmpData["IMAGE_GROUP"] = $arFileTmp["src"];
					}
				}

				$arTmpData["ID"] = $arUserRequests["ID"];
				$arTmpData["ID_USER"] = $arUserRequests["INITIATED_BY_USER_ID"];

				$arTmpUser = array(
					"NAME" => $arUserRequests["~INITIATED_BY_USER_NAME"],
					"LAST_NAME" => $arUserRequests["~INITIATED_BY_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequests["~INITIATED_BY_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequests["~INITIATED_BY_USER_LOGIN"]
				);
				$arTmpData["NAME_USER"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
				$arTmpData["NAME_USER_TITLE"] = CUser::FormatName($arParams["TITLE_NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
				$arTmpData["URL_USER"] = $pu;
				$arTmpData["CAN_VIEW_USER"] = ($canViewProfileU ? "Y" : "N");
				$arTmpData["DATE"] = $arUserRequests["DATE_CREATE"];
				$arTmpData["DATE_TIMESTAMP"] = MakeTimeStamp($arUserRequests["DATE_CREATE"], $DateTimeFormat);
				$arTmpData["DATE_DATE_FORMATTED"] = ConvertTimeStamp($arTmpData["DATE_TIMESTAMP"], "SHORT", $site);
				$arTmpData["DATE_DATETIME_FORMATTED"] = ConvertTimeStamp($arTmpData["DATE_TIMESTAMP"], "FULL", $site);

				if ($time_pos > $date_pos)
					$arTmpData["DATE_TIME_FORMATTED"] = trim(mb_substr($arTmpData["DATE_DATETIME_FORMATTED"], mb_strlen($arTmpData["DATE_DATE_FORMATTED"])), " ,.;:");
				elseif ($date_pos > $time_pos && $time_pos !== false)
					$arTmpData["DATE_TIME_FORMATTED"] = trim(mb_substr($arTmpData["DATE_DATETIME_FORMATTED"], 0, $date_pos), " ,.;:");
				else
					$arTmpData["DATE_TIME_FORMATTED"] = "";

				if (date("d", $arTmpData["DATE_TIMESTAMP"]) == date("d") && date("n", $arTmpData["DATE_TIMESTAMP"]) == date("n") && date("Y", $arTmpData["DATE_TIMESTAMP"]) == date("Y"))
					$arTmpData["DATE_DAY"] = "TODAY";
				elseif ((mktime(0, 0, 0, date("n"), date("d"), date("Y")) - $arTmpData["DATE_TIMESTAMP"]) < 60*60*24)
					$arTmpData["DATE_DAY"] = "YESTERDAY";
				else
					$arTmpData["DATE_DAY"] = "";

				$arTmpData["MESSAGE"] = $parser->convert(
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
					);
				$arTmpData["BUTTONS"] = array(
						array(
							"NAME" => GetMessage("SONET_C2_GR_ADD"),
							"ID" => "add",
							"URL" => "EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=add"
						),
						array(
							"NAME" => GetMessage("SONET_C2_REJECT"),
							"ID" => "reject",
							"URL" => "EventType=GroupRequest&eventID=".$arUserRequests["ID"]."&action=reject"
						)
					);
				$arTmpData["CAN_VIEW_GROUP"] = ($canViewProfileG ? "Y" : "N");
				$arTmpData["NAME_GROUP"] = $arUserRequests["GROUP_NAME"];
				$arTmpData["URL_GROUP"] = $pg;

				$arData[] = $arTmpData;
			} // while


			$arFilter = array(
				"TO_USER_ID" => $GLOBALS["USER"]->GetID(),
				"DATE_VIEW" => "",
				"TO_DELETED" => "N"
			);

			if ($log == "Y")
				$arFilter["IS_LOG_ALL"] = "Y";

			$dbUserRequests = CSocNetMessages::GetList(
				array("DATE_CREATE" => "ASC"),
				$arFilter,
				false,
				array("nTopCount" => 50),
				array("ID", "FROM_USER_ID", "TITLE", "MESSAGE", "DATE_CREATE", "MESSAGE_TYPE", "FROM_USER_NAME", "FROM_USER_LAST_NAME", "FROM_USER_SECOND_NAME", "FROM_USER_LOGIN", "FROM_USER_PERSONAL_PHOTO", "FROM_USER_PERSONAL_GENDER", "FROM_USER_IS_ONLINE", "IS_LOG")
			);
			while ($arUserRequests = $dbUserRequests->GetNext())
			{
				$arTmpData = array();
				$arTmpData["TYPE"] = "M";
				$arTmpData["IS_LOG"] = $arUserRequests["IS_LOG"];
				$arTmpData["MESSAGE_TYPE"] = $arUserRequests["MESSAGE_TYPE"];

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserRequests["FROM_USER_ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FROM_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin($site));
				$canAnsver = (IsModuleInstalled("im") || CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUserRequests["FROM_USER_ID"], "message", CSocNetUser::IsCurrentUserModuleAdmin($site)));

				$arTmpData["IMAGE_USER"] = "/bitrix/images/1.gif";

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

				if (intval($arUserRequests["FROM_USER_PERSONAL_PHOTO"]) > 0)
				{
					$imageFile = CFile::GetFileArray($arUserRequests["FROM_USER_PERSONAL_PHOTO"]);
					if ($imageFile !== false)
					{
						$arFileTmp = CFile::ResizeImageGet(
							$imageFile,
							array("width" => $ImageSize, "height" => $ImageSize),
							BX_RESIZE_IMAGE_EXACT,
							false
						);
						$arTmpData["IMAGE_USER"] = $arFileTmp["src"];
					}
				}

				$arTmpData["ID"] = $arUserRequests["ID"];
				$arTmpData["ID_USER"] = $arUserRequests["FROM_USER_ID"];

				$arTmpUser = array(
					"NAME" => $arUserRequests["~FROM_USER_NAME"],
					"LAST_NAME" => $arUserRequests["~FROM_USER_LAST_NAME"],
					"SECOND_NAME" => $arUserRequests["~FROM_USER_SECOND_NAME"],
					"LOGIN" => $arUserRequests["~FROM_USER_LOGIN"]
				);
				$arTmpData["NAME_USER"] = CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
				$arTmpData["NAME_USER_TITLE"] = CUser::FormatName($arParams["TITLE_NAME_TEMPLATE"], $arTmpUser, $bUseLogin);
				$arTmpData["URL_USER"] = $pu;
				$arTmpData["CAN_VIEW_USER"] = ($canViewProfile ? "Y" : "N");
				$arTmpData["IS_ONLINE"] = $arUserRequests["FROM_USER_IS_ONLINE"];
				$arTmpData["DATE"] = $arUserRequests["DATE_CREATE"];
				$arTmpData["DATE_TIMESTAMP"] = MakeTimeStamp($arUserRequests["DATE_CREATE"], $DateTimeFormat);
				$arTmpData["DATE_DATE_FORMATTED"] = ConvertTimeStamp($arTmpData["DATE_TIMESTAMP"], "SHORT", $site);
				$arTmpData["DATE_DATETIME_FORMATTED"] = ConvertTimeStamp($arTmpData["DATE_TIMESTAMP"], "FULL", $site);

				if ($time_pos > $date_pos)
					$arTmpData["DATE_TIME_FORMATTED"] = trim(mb_substr($arTmpData["DATE_DATETIME_FORMATTED"], mb_strlen($arTmpData["DATE_DATE_FORMATTED"])), " ,.;:");
				elseif ($date_pos > $time_pos && $time_pos !== false)
					$arTmpData["DATE_TIME_FORMATTED"] = trim(mb_substr($arTmpData["DATE_DATETIME_FORMATTED"], 0, $date_pos), " ,.;:");
				else
					$arTmpData["DATE_TIME_FORMATTED"] = "";

				if (date("d", $arTmpData["DATE_TIMESTAMP"]) == date("d") && date("n", $arTmpData["DATE_TIMESTAMP"]) == date("n") && date("Y", $arTmpData["DATE_TIMESTAMP"]) == date("Y"))
					$arTmpData["DATE_DAY"] = "TODAY";
				elseif ((mktime(0, 0, 0, date("n"), date("d"), date("Y")) - $arTmpData["DATE_TIMESTAMP"]) < 60*60*24)
					$arTmpData["DATE_DAY"] = "YESTERDAY";
				else
					$arTmpData["DATE_DAY"] = "";

				$arTmpData["MESSAGE"] = $parser->convert(
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
					);

				$arTmpData["MESSAGE"] = str_replace("#BR#", "<br />", $arTmpData["MESSAGE"]);

				$arTmpData["URL_MESSAGE"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGE_FORM_MESS"], array("user_id" => $arUserRequests["FROM_USER_ID"], "message_id" => $arUserRequests["ID"]));
				$arTmpData["URL_BAN"] = "EventType=Message&userID=".$arUserRequests["FROM_USER_ID"]."&action=ban";
				$arTmpData["CAN_ANSWER"] = (($arUserRequests["MESSAGE_TYPE"] == SONET_MESSAGE_PRIVATE && $canAnsver) ? "Y" : "N");
				$arTmpData["CAN_BAN"] = ((!CSocNetUser::IsUserModuleAdmin($arUserRequests["FROM_USER_ID"], $site) && $arUserRequests["MESSAGE_TYPE"] == SONET_MESSAGE_PRIVATE) ? "Y" : "N");

				if ($arTmpData["CAN_ANSWER"] == "Y")
				{
					$arTmpData["BUTTONS"] = array(
							array(
								"NAME" => GetMessage("SONET_C2_ANSWER"),
								"ID" => "reply",
								"ONCLICK" => "function () 
									{
										window.open('".$arTmpData["URL_MESSAGE"]."', '', 'location=yes,status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5));
										_this.Next();
										_this.oData.splice(_this.oCurrentMessagePointer-1, 1);
										if (_this.oCurrentMessagePointer > 0)
											_this.oCurrentMessagePointer--;
										_this.adjustPaging();
										_this.ShowContent(_this.oCurrentMessagePointer);
										return;
									}"
							),
						);
				}

				$arData[] = $arTmpData;
			} // while
		} // if bGet
	}

	if (
		count($arData) <= 0
		|| $arData[0] != "*"
	)
	{
		CSocNetMessages::__SpeedFileCheckMessages($GLOBALS["USER"]->GetID());
		CSocNetUserToGroup::__SpeedFileCheckMessages($GLOBALS["USER"]->GetID());
		CSocNetUserRelations::__SpeedFileCheckMessages($GLOBALS["USER"]->GetID());
	}

	global	$tmpSite;
	$tmpSite = $site;

	function date_cmp($a, $b)
	{
		global $tmpSite, $DB;

		$ts_a = $DB->CharToDateFunction($a["DATE"], "FULL", $tmpSite);
		$ts_b = $DB->CharToDateFunction($b["DATE"], "FULL", $tmpSite);


		if ($ts_a == $ts_b)
		{
			return 0;
		}
		return ($ts_a < $ts_b) ? -1 : 1;
	}

	usort($arData, "date_cmp");
	usort($arData, "date_cmp");

	if (is_array($arData) && is_array($arData[0]))
	{
		$arDialogPos = CUserOptions::GetOption('socialnetwork', 'SONET_EVENT_POS', false);
		if ($arDialogPos)
		{
			$arData[0]["POS_LEFT"] = $arDialogPos["left"];
			$arData[0]["POS_TOP"] = $arDialogPos["top"];
		}

		if (intval($log_cnt) > 0)
			$arData[0]["LOG_CNT"] = $log_cnt;
	}
	else
	{
		$arData = array(
			array("LOG_CNT" => $log_cnt)
		);
	}

	echo CUtil::PhpToJSObject($arData);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>
