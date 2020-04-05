<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (IsModuleInstalled("im"))
	LocalRedirect('/?IM_DIALOG='.IntVal($arParams["USER_ID"]), false, "301 Moved permanently");

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arParams["USER_ID"] = IntVal($arParams["USER_ID"]);
$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);

if (strLen($arParams["USER_VAR"]) <= 0)
	$arParams["USER_VAR"] = "user_id";
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if(strLen($arParams["GROUP_VAR"])<=0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
if (strlen($arParams["PATH_TO_USER"]) <= 0)
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
if (strlen($arParams["PATH_TO_GROUP"]) <= 0)
	$arParams["PATH_TO_GROUP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group&".$arParams["GROUP_VAR"]."=#group_id#");

$arParams["PATH_TO_MESSAGES_CHAT"] = trim($arParams["PATH_TO_MESSAGES_CHAT"]);
if (strlen($arParams["PATH_TO_MESSAGES_CHAT"]) <= 0)
	$arParams["PATH_TO_MESSAGES_CHAT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#");

$arParams["PATH_TO_MESSAGE_FORM_MESS"] = trim($arParams["PATH_TO_MESSAGE_FORM_MESS"]);
if (strlen($arParams["PATH_TO_MESSAGE_FORM_MESS"]) <= 0)
	$arParams["PATH_TO_MESSAGE_FORM_MESS"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=messages_chat&".$arParams["USER_VAR"]."=#user_id#&".$arParams["MESSAGE_VAR"]."=#message_id#");
	
$arParams["PATH_TO_VIDEO_CALL"] = trim($arParams["PATH_TO_VIDEO_CALL"]);
if (strlen($arParams["PATH_TO_VIDEO_CALL"]) <= 0)
	$arParams["PATH_TO_VIDEO_CALL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=video_call&".$arParams["USER_VAR"]."=#user_id#");
	
$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);

$arParams["MESSAGES_COUNT"] = IntVal($arParams["MESSAGES_COUNT"]);
if ($arParams["MESSAGES_COUNT"] <= 0)
	$arParams["MESSAGES_COUNT"] = 20;

if (strlen($arParams["NAME_TEMPLATE"]) <= 0)		
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

if (!$GLOBALS["USER"]->IsAuthorized())
	$arResult["NEED_AUTH"] = "Y";
else
{
	$arResult["FatalError"] = "";
	$arResult["Users"] = false;

	$dbUser = CUser::GetByID($USER->GetID());
	$arResult["UserSelf"] = $dbUser->GetNext();

	$arTmpUser = array(
		'NAME' => $arResult["UserSelf"]["~NAME"],
		'LAST_NAME' => $arResult["UserSelf"]["~LAST_NAME"],
		'SECOND_NAME' => $arResult["UserSelf"]["~SECOND_NAME"],
		'LOGIN' => $arResult["UserSelf"]["~LOGIN"],
	);

	$arResult["UserSelf"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);	

	if($arParams["GROUP_ID"] > 0)
	{
		//group message part
		
		$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);

		if (
			!$arGroup 
			|| !is_array($arGroup) 
			|| $arGroup["ACTIVE"] != "Y" 
		)
			$arResult["FatalError"] = GetMessage("SONET_CHAT_GROUP_NOT_FOUND");
		else
		{
			$arGroupSites = array();
			$rsGroupSite = CSocNetGroup::GetSite($arGroup["ID"]);
			while ($arGroupSite = $rsGroupSite->Fetch())
				$arGroupSites[] = $arGroupSite["LID"];

			if (!in_array(SITE_ID, $arGroupSites))
				$arResult["FatalError"] = GetMessage("SONET_CHAT_GROUP_NOT_FOUND");
			else
			{
				$arResult["Group"] = $arGroup;

				$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());
				
				if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanViewGroup"] || !$arResult["CurrentUserPerms"]["UserCanSpamGroup"])
					$arResult["FatalError"] = GetMessage("SONET_CHAT_GROUP_ACESS");
				else
				{
					$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			
					if ($arParams["SET_TITLE"]=="Y")
						$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_CHAT_GROUP_TITLE"));

					if (intval($arResult["Group"]["IMAGE_ID"]) <= 0)
						$arResult["Group"]["IMAGE_ID"] = COption::GetOptionInt("socialnetwork", "default_group_picture", false, SITE_ID);

					$arImage = CSocNetTools::InitImage($arResult["Group"]["IMAGE_ID"], 50, "/bitrix/images/socialnetwork/nopic_group_50.gif", 50, $arResult["Urls"]["Group"], true, 'target="_blank"');
					$arResult["Group"]["IMAGE_ID_FILE"] = $arImage["FILE"];
					$arResult["Group"]["IMAGE_ID_IMG"] = $arImage["IMG"];

					$dbRequests = CSocNetUserToGroup::GetList(
						array("USER_LAST_NAME" => "ASC", "USER_NAME" => "ASC"),
						array(
							"GROUP_ID" => $arResult["Group"]["ID"],
							"<=ROLE" => SONET_ROLES_USER
						),
						false,
						false,
						array("ID", "USER_ID", "ROLE", "DATE_CREATE", "DATE_UPDATE", "USER_NAME", "USER_LAST_NAME", "USER_SECOND_NAME", "USER_LOGIN", "USER_PERSONAL_PHOTO", "USER_PERSONAL_GENDER", "USER_IS_ONLINE")
					);
					if ($dbRequests)
					{
						$arResult["Users"] = array();
						$arResult["Users"]["List"] = false;
						while ($arRequests = $dbRequests->GetNext())
						{
							if($arResult["UserSelf"]["ID"] == $arRequests["USER_ID"])
								continue;

							if ($arResult["Users"]["List"] == false)
								$arResult["Users"]["List"] = array();

							$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arRequests["USER_ID"]));
							$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arRequests["USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

							$arTmpUser = array(
								"NAME" => htmlspecialcharsback($arRequests["USER_NAME"]),
								"LAST_NAME" => htmlspecialcharsback($arRequests["USER_LAST_NAME"]),
								"SECOND_NAME" => htmlspecialcharsback($arRequests["USER_SECOND_NAME"]),
								"LOGIN" => htmlspecialcharsback($arRequests["USER_LOGIN"])
							);
							$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);	

							$arResult["Users"]["List"][] = array(
								"ID" => $arRequests["ID"],
								"USER_ID" => $arRequests["USER_ID"],
								"USER_NAME" => $arRequests["USER_NAME"],
								"USER_LAST_NAME" => $arRequests["USER_LAST_NAME"],
								"USER_SECOND_NAME" => $arRequests["USER_SECOND_NAME"],
								"USER_LOGIN" => $arRequests["USER_LOGIN"],
								"USER_NAME_FORMATTED" => $strNameFormatted,
								"USER_PROFILE_URL" => $pu,
								"SHOW_PROFILE_LINK" => $canViewProfile,
								"PATH_TO_MESSAGES_CHAT" => str_replace("#user_id#", $arRequests["USER_ID"], $arParams["PATH_TO_MESSAGES_CHAT"]),
								"IS_ONLINE" => ($arRequests["USER_IS_ONLINE"] == "Y"),
								"IS_MODERATOR" => ($arRequests["ROLE"] != SONET_ROLES_USER)
							);
						}
					}
				}
			}
		}
	}
	else
	{
		//user message part
		
		if  (CModule::IncludeModule('extranet') && !CExtranet::IsProfileViewableByID($arParams["USER_ID"]) && $arParams["USER_ID"] != $USER->GetID())
		{
			$dbMessages = CSocNetMessages::GetList(
					array(),
					array(
						"FROM_USER_ID" => $arParams["USER_ID"],
						"TO_USER_ID" => $USER->GetID(),                        
						"MESSAGE_TYPE" => SONET_MESSAGE_PRIVATE
					),
					false,
					array("nTopCount" => 1),
					array("ID")
				);
				if (!$dbMessages->Fetch())
					$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
		}

		if (StrLen($arResult["FatalError"]) <= 0)
		{
			$dbUser = CUser::GetByID($arParams["USER_ID"]);
			$arResult["User"] = $dbUser->GetNext();

			$arTmpUser = array(
					'NAME' => $arResult["User"]["~NAME"],
					'LAST_NAME' => $arResult["User"]["~LAST_NAME"],
					'SECOND_NAME' => $arResult["User"]["~SECOND_NAME"],
					'LOGIN' => $arResult["User"]["~LOGIN"],
				);
		
			if (!is_array($arResult["User"]))
				$arResult["FatalError"] = GetMessage("SONET_P_USER_NO_USER").". ";
		}

		if (StrLen($arResult["FatalError"]) <= 0)
		{
			if ($arParams["SET_TITLE"]=="Y")
			{
				$arParams["TITLE_NAME_TEMPLATE"] = str_replace(
					array("#NOBR#", "#/NOBR#"), 
					array("", ""), 
					$arParams["NAME_TEMPLATE"]
				);
				$strTitleFormatted = CUser::FormatName($arParams['TITLE_NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
				$APPLICATION->SetTitle($strTitleFormatted.": ".GetMessage("SONET_C50_PAGE_TITLE"));
			}

			$arResult["User"]["NAME_FORMATTED"] = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);
			$arResult["CurrentUserPerms"] = CSocNetUserPerms::InitUserPerms($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"], CSocNetUser::IsCurrentUserModuleAdmin());

			if ($arResult["CurrentUserPerms"]["IsCurrentUser"])
				$arResult["FatalError"] = GetMessage("SONET_C50_SELF").". ";

			if (!$arResult["CurrentUserPerms"]["Operations"]["message"])
				$arResult["FatalError"] = GetMessage("SONET_C50_NO_PERMS").". ";
		}

		if (StrLen($arResult["FatalError"]) <= 0)
		{
			$arResult["Urls"]["User"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arResult["User"]["ID"]));
			$arResult["Urls"]["UserMessages"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MESSAGES_USERS_MESSAGES"], array("user_id" => $arResult["User"]["ID"]));
			
			$arResult["Urls"]["VideoCall"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_VIDEO_CALL"], array("user_id" => $arResult["User"]["ID"]));
			
			if(!CModule::IncludeModule("video"))
				$arResult["CurrentUserPerms"]["Operations"]["videocall"] = false;

			$arResult["IS_ONLINE"] = ($arResult["User"]["IS_ONLINE"] == "Y");

			if ($arResult["User"]['PERSONAL_BIRTHDAY'] <> '')
			{
				$arBirthDate = ParseDateTime($arResult["User"]['PERSONAL_BIRTHDAY'], CSite::GetDateFormat('SHORT'));
				$arResult['IS_BIRTHDAY'] = (intval($arBirthDate['MM']) == date('n') && intval($arBirthDate['DD']) == date('j'));
			}

			if(CModule::IncludeModule('intranet'))
			{
				$arResult['IS_FEATURED'] = CIntranetUtils::IsUserHonoured($arResult["User"]['ID']);
				$arResult['IS_ABSENT'] = CIntranetUtils::IsUserAbsent($arResult["User"]['ID']);
			}

			if (intval($arResult["User"]["PERSONAL_PHOTO"]) <= 0)
			{
				switch ($arResult["User"]["PERSONAL_GENDER"])
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
				$arResult["User"]["PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
			}
			$arImage = CSocNetTools::InitImage($arResult["User"]["PERSONAL_PHOTO"], 50, "/bitrix/images/socialnetwork/nopic_user_50.gif", 50, $arResult["CurrentUserPerms"]["Operations"]["viewprofile"] ? $arResult["Urls"]["User"] : "", true, 'target="_blank"');

			$arResult["User"]["PersonalPhotoFile"] = $arImage["FILE"];
			$arResult["User"]["PersonalPhotoImg"] = $arImage["IMG"];

			$arResult["ChatLastDate"] = CSocNetMessages::GetChatLastDate($GLOBALS["USER"]->GetID(), $arResult["User"]["ID"]);
			$arResult["REPLY_MESSAGE_ID"] = intval($arParams["MESSAGE_ID"]);
		}
	}
	//common part
	if (StrLen($arResult["FatalError"]) <= 0)
	{
		//intranet structure
		$arResult["Structure"] = false;
		if(IsModuleInstalled('intranet') && CModule::IncludeModule('iblock') && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()))
		{
			if(($iblock_id = COption::GetOptionInt('intranet', 'iblock_structure', 0)) > 0)
			{
				$arResult["Structure"] = array();
				$sec = CIBlockSection::GetList(Array("left_margin"=>"asc","SORT"=>"ASC"), Array("ACTIVE"=>"Y","CNT_ACTIVE"=>"Y","IBLOCK_ID"=>$iblock_id), true);
				while($ar = $sec->GetNext())
					$arResult["Structure"][] = $ar;

				//get users in the structure
				$arResult["UsersInStructure"] = array();
				$arFilter = array('ACTIVE' => 'Y');
				$obUser = new CUser();
				$dbUsers = $obUser->GetList(($sort_by = 'last_name'), ($sort_dir = 'asc'), $arFilter, array('SELECT' => array('UF_*')));
				while ($arUser = $dbUsers->GetNext())
				{
					if($arResult["UserSelf"]["ID"] == $arUser["ID"])
						continue;

					$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));
					$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

					$arTmpUser = array(
						"NAME" => $arUser["~NAME"],
						"LAST_NAME" => $arUser["~LAST_NAME"],
						"SECOND_NAME" => $arUser["~SECOND_NAME"],
						"LOGIN" => $arUser["~LOGIN"],
					);

					$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);		

					$arStructureUser = array(
						"USER_ID" => $arUser["ID"],
						"USER_NAME" => $arUser["NAME"],
						"USER_LAST_NAME" => $arUser["LAST_NAME"],
						"USER_SECOND_NAME" => $arUser["SECOND_NAME"],
						"USER_LOGIN" => $arUser["LOGIN"],
						"USER_NAME_FORMATTED" => $strNameFormatted,
						"USER_PROFILE_URL" => $pu,
						"SHOW_PROFILE_LINK" => $canViewProfile,
						"PATH_TO_MESSAGES_CHAT" => str_replace("#user_id#", $arUser["ID"], $arParams["PATH_TO_MESSAGES_CHAT"]),
						"IS_ONLINE" => ($arUser["IS_ONLINE"] == "Y")
					);
					if(is_array($arUser["UF_DEPARTMENT"]) && !empty($arUser["UF_DEPARTMENT"]))
					{
						foreach($arUser["UF_DEPARTMENT"] as $dep_id)
							$arResult["UsersInStructure"][$dep_id][] = $arStructureUser;
					}
					else
						$arResult["UsersInStructure"]["others"][] = $arStructureUser;
				}
			}
		}

		//Friends
		$arResult["Friends"] = false;
		if (CSocNetUser::IsFriendsAllowed() && (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()))
		{
			$dbFriends = CSocNetUserRelations::GetRelatedUsers($arResult["UserSelf"]["ID"], SONET_RELATIONS_FRIEND);
			if ($dbFriends)
			{
				$arResult["Friends"] = array();
				while ($arFriends = $dbFriends->GetNext())
				{
					$pref = ((IntVal($arResult["UserSelf"]["ID"]) == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");
					$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arFriends[$pref."_USER_ID"]));
					$canViewProfile = CSocNetUserPerms::CanPerformOperation($arResult["UserSelf"]["ID"], $arFriends[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

					$arTmpUser = array(
						"NAME" => $arFriends["~".$pref."_USER_NAME"],
						"LAST_NAME" => $arFriends["~".$pref."_USER_LAST_NAME"],
						"SECOND_NAME" => $arFriends["~".$pref."_USER_SECOND_NAME"],
						"LOGIN" => $arFriends["~".$pref."_USER_LOGIN"]
					);
					$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);	
					
					$arResult["Friends"][] = array(
						"USER_ID" => $arFriends[$pref."_USER_ID"],
						"USER_NAME" => $arFriends[$pref."_USER_NAME"],
						"USER_LAST_NAME" => $arFriends[$pref."_USER_LAST_NAME"],
						"USER_SECOND_NAME" => $arFriends[$pref."_USER_SECOND_NAME"],
						"USER_LOGIN" => $arFriends[$pref."_USER_LOGIN"],
						"USER_NAME_FORMATTED" => $strNameFormatted,
						"USER_PROFILE_URL" => $pu,
						"SHOW_PROFILE_LINK" => $canViewProfile,
						"PATH_TO_MESSAGES_CHAT" => str_replace("#user_id#", $arFriends[$pref."_USER_ID"], $arParams["PATH_TO_MESSAGES_CHAT"]),
						"IS_ONLINE" => ($arFriends[$pref."_USER_IS_ONLINE"] == "Y")
					);
				}
			}
		}
		elseif (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		{
			$arResult["Friends"] = array();

			$arUsersInListID = array();

			$arMyGroupsUsers = CExtranet::GetMyGroupsUsersFull(SITE_ID, true);
			foreach ($arMyGroupsUsers as $arUser)
			{
				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($arResult["UserSelf"]["ID"], $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);

				$arResult["Friends"][] = array(
					"USER_ID" =>  $arUser["ID"],
					"USER_NAME" =>  $arUser["NAME"],
					"USER_LAST_NAME" =>  $arUser["LAST_NAME"],
					"USER_SECOND_NAME" =>  $arUser["SECOND_NAME"],
					"USER_LOGIN" =>  $arUser["LOGIN"],
					"USER_NAME_FORMATTED" =>  $strNameFormatted,
					"USER_PROFILE_URL" => $pu,
					"SHOW_PROFILE_LINK" => $canViewProfile,
					"PATH_TO_MESSAGES_CHAT" => str_replace("#user_id#",  $arUser["ID"], $arParams["PATH_TO_MESSAGES_CHAT"]),
					"IS_ONLINE" => ($arUser["IS_ONLINE"] == "Y")
				);
				$arUsersInListID[] = $arUser["ID"];
			}

			$arPublicUsers = CExtranet::GetPublicUsers(true);
			foreach ($arPublicUsers as $arUser)
			{
				if (in_array($arUser["ID"], $arUsersInListID))
					continue;

				$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"]));
				$canViewProfile = CSocNetUserPerms::CanPerformOperation($arResult["UserSelf"]["ID"], $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

				$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arUser, $bUseLogin);					

				$arResult["Friends"][] = array(
					"USER_ID" =>  $arUser["ID"],
					"USER_NAME" =>  $arUser["NAME"],
					"USER_LAST_NAME" =>  $arUser["LAST_NAME"],
					"USER_SECOND_NAME" =>  $arUser["SECOND_NAME"],
					"USER_LOGIN" =>  $arUser["LOGIN"],
					"USER_NAME_FORMATTED" =>  $strNameFormatted,
					"USER_PROFILE_URL" => $pu,
					"SHOW_PROFILE_LINK" => $canViewProfile,
					"PATH_TO_MESSAGES_CHAT" => str_replace("#user_id#",  $arUser["ID"], $arParams["PATH_TO_MESSAGES_CHAT"]),
					"IS_ONLINE" => ($arUser["IS_ONLINE"] == "Y")
				);
			}
		}

		//Recent users
		$arResult["RecentUsers"] = array();
		$arNavParams = array("nPageSize" => 20, "bDescPageNumbering" => false);
		$dbMessages = CSocNetMessages::GetMessagesUsers($GLOBALS["USER"]->GetID(), $arNavParams);
		while ($arMessages = $dbMessages->GetNext())
		{
			$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arMessages["ID"]));
			$canViewProfile = CSocNetUserPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arMessages["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

			$arTmpUser = array(
				"NAME" => $arMessages["~NAME"],
				"LAST_NAME" => $arMessages["~LAST_NAME"],
				"SECOND_NAME" => $arMessages["~SECOND_NAME"],
				"LOGIN" => $arMessages["~LOGIN"]
			);
			$strNameFormatted = CUser::FormatName($arParams['NAME_TEMPLATE'], $arTmpUser, $bUseLogin);

			$arResult["RecentUsers"][] = array(
				"USER_ID" => $arMessages["ID"],
				"USER_NAME" => $arMessages["NAME"],
				"USER_LAST_NAME" => $arMessages["LAST_NAME"],
				"USER_SECOND_NAME" => $arMessages["SECOND_NAME"],
				"USER_LOGIN" => $arMessages["LOGIN"],
				"USER_NAME_FORMATTED" => $strNameFormatted,
				"USER_PROFILE_URL" => $pu,
				"SHOW_PROFILE_LINK" => $canViewProfile,
				"PATH_TO_MESSAGES_CHAT" => str_replace("#user_id#", $arMessages["ID"], $arParams["PATH_TO_MESSAGES_CHAT"]),
				"IS_ONLINE" => ($arMessages["IS_ONLINE"] == "Y")
			);
		}

		$arResult["PrintSmilesList"] = CSocNetSmile::PrintSmilesList(0, LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

		$strNow = CSocNetMessages::Now();
		$strNow_ts = MakeTimeStamp($strNow, "YYYY-MM-DD HH:MI:SS") + CTimeZone::GetOffset();
		$arResult["Now"] = date("Y-m-d H:i:s", $strNow_ts);		

		//user options
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strtolower($GLOBALS['DB']->type)."/favorites.php");
		$arResult["USER_OPTIONS"] = CUserOptions::GetOption('socnet', 'chat', array());
		if($arResult["USER_OPTIONS"]["sound"] <> "N")
			$arResult["USER_OPTIONS"]["sound"] = "Y";
		if($arResult["USER_OPTIONS"]["contacts"] <> "Y")
			$arResult["USER_OPTIONS"]["contacts"] = "N";
		$arResult["USER_OPTIONS"]["contacts_width"] = intval($arResult["USER_OPTIONS"]["contacts_width"]);

		$dirPath = '/bitrix/components/bitrix/socialnetwork.messages_chat';
		$arResult["MsgAddPath"] = $dirPath."/add_message.php";
		$arResult["MsgGetPath"] = $dirPath."/get_message.php";
	}
}
$this->IncludeComponentTemplate();
?>