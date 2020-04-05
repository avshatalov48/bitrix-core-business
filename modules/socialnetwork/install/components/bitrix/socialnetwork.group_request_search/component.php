<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2014 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @param array $arParams
 * @param array $arResult
 * @param CBitrixComponent $this
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

/*  intranet.user.search */
$arParams["IUS_INPUT_NAME"] = "ius_ids";
$arParams["IUS_INPUT_NAME_SUSPICIOUS"] = "ius_susp";
$arParams["IUS_INPUT_NAME_STRING"] = "users_list_string_ius";
$arParams["IUS_INPUT_NAME_EXTRANET"] = "ius_ids_extranet";
$arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"] = "ius_susp_extranet";
$arParams["IUS_INPUT_NAME_STRING_EXTRANET"] = "users_list_string_ius_extranet";

$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

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

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if (strlen($arParams["PATH_TO_SEARCH"]) <= 0)
	$arParams["PATH_TO_SEARCH"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=search");

$arParams["ITEMS_COUNT"] = IntVal($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 30;

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]);
$arParams["USER_ID"] = IntVal($USER->GetID());

// for bitrix:main.user.link
if (IsModuleInstalled('intranet'))
{
	$arTooltipFieldsDefault	= serialize(array(
		"EMAIL",
		"PERSONAL_MOBILE",
		"WORK_PHONE",
		"PERSONAL_ICQ",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION",
	));
	$arTooltipPropertiesDefault = serialize(array(
		"UF_DEPARTMENT",
		"UF_PHONE_INNER",
	));
}
else
{
	$arTooltipFieldsDefault = serialize(array(
		"PERSONAL_ICQ",
		"PERSONAL_BIRTHDAY",
		"PERSONAL_PHOTO",
		"PERSONAL_CITY",
		"WORK_COMPANY",
		"WORK_POSITION"
	));
	$arTooltipPropertiesDefault = serialize(array());
}

if (!array_key_exists("SHOW_FIELDS_TOOLTIP", $arParams))
	$arParams["SHOW_FIELDS_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_fields", $arTooltipFieldsDefault));
if (!array_key_exists("USER_PROPERTY_TOOLTIP", $arParams))
	$arParams["USER_PROPERTY_TOOLTIP"] = unserialize(COption::GetOptionString("socialnetwork", "tooltip_properties", $arTooltipPropertiesDefault));

$arResult["ShowForm"] = "Input";

if (!$USER->IsAuthorized())
{
	$arResult["NEED_AUTH"] = "Y";
}
else
{
	$bIntranet = IsModuleInstalled('intranet');

	if ($arParams["GROUP_ID"] <= 0)
	{
		$arResult["FatalError"] = GetMessage("SONET_C33_NO_GROUP_ID").". ";
	}
	else
	{
		$arGroup = CSocNetGroup::GetByID($arParams["GROUP_ID"]);
		if (!$arGroup || !is_array($arGroup))
			$arResult["FatalError"] = GetMessage("SONET_C33_NO_GROUP").". ";
		else
		{
			$arResult["Group"] = $arGroup;

			if (CModule::IncludeModule("extranet"))
			{
				$arSites = array();
				$rsGroupSite = CSocNetGroup::GetSite($arParams["GROUP_ID"]);
				while($arGroupSite = $rsGroupSite->Fetch())
					$arSites[] = $arGroupSite["LID"];

				$extranet_site_id = CExtranet::GetExtranetSiteID();
				if ($extranet_site_id && in_array(CExtranet::GetExtranetSiteID(), $arSites))
					$arResult["bExtranet"] = true;
			}

			$arResult["Urls"]["Group"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["Group"]["ID"]));
			$arResult["Urls"]["Search"] = $arParams["PATH_TO_SEARCH"];

			$arResult["CurrentUserPerms"] = CSocNetUserToGroup::InitUserPerms($USER->GetID(), $arResult["Group"], CSocNetUser::IsCurrentUserModuleAdmin());

			if ($arParams["SET_TITLE"] == "Y")
				$APPLICATION->SetTitle($arResult["Group"]["NAME"].": ".GetMessage("SONET_C33_PAGE_TITLE"));

			if ($arParams["SET_NAV_CHAIN"] != "N")
			{
				$APPLICATION->AddChainItem($arResult["Group"]["NAME"], $arResult["Urls"]["Group"]);
				$APPLICATION->AddChainItem(GetMessage("SONET_C33_PAGE_TITLE"));
			}

			if (!$arResult["CurrentUserPerms"] || !$arResult["CurrentUserPerms"]["UserCanInitiate"])
				$arResult["FatalError"] = GetMessage("SONET_C33_NO_PERMS").". ";
			else
			{
				$arSuccessUsers = array();
				$arErrorUsers = array();
				$arResult["SuccessUsers"] = false;
				$arResult["ErrorUsers"] = false;
				if ($_SERVER["REQUEST_METHOD"]=="POST" && strlen($_POST["save"]) > 0 && check_bitrix_sessid())
				{
					$errorMessage = "";
					$warningMessage = "";

					$bAnyUser = false;

					if ($bIntranet)
					{
						if (strlen($arParams["IUS_INPUT_NAME"]) > 0 && is_array($_POST[$arParams["IUS_INPUT_NAME"]]) && count($_POST[$arParams["IUS_INPUT_NAME"]]) > 0)
							$bAnyUser = true;

						if (!$bAnyUser && CModule::IncludeModule('extranet') && strlen($arParams["IUS_INPUT_NAME_EXTRANET"]) > 0 && is_array($_POST[$arParams["IUS_INPUT_NAME_EXTRANET"]]) && count($_POST[$arParams["IUS_INPUT_NAME_EXTRANET"]]) > 0)
							$bAnyUser = true;

						if (!$bAnyUser && strlen($arParams["IUS_INPUT_NAME_SUSPICIOUS"]) > 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS"]]) > 0)
							$bAnyUser = true;

						if (!$bAnyUser && $arResult["bExtranet"] && strlen($arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"]) > 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"]]) > 0)
							$bAnyUser = true;
					}

					/* if data from intranet.user.search not found or there is no intranet module */
					if (!$bAnyUser)
					{
						if ($arResult["bExtranet"])
						{
							if (strlen($_POST["EMAILS"]) > 0)
								$bAnyUser = true;
						}
						elseif (strlen($_POST["users_list"]) > 0)
							$bAnyUser = true;
					}

					if (!$bAnyUser)
						$errorMessage .= GetMessage("SONET_C33_NO_USERS").". ";

					$arUserIDs = array();
					$arUsersList = array();
					$arUsersFull = array();

					if (StrLen($errorMessage) <= 0)
					{
						/* new component */
						if ($bIntranet && strlen($arParams["IUS_INPUT_NAME"]) > 0 && is_array($_POST[$arParams["IUS_INPUT_NAME"]]) && count($_POST[$arParams["IUS_INPUT_NAME"]]) > 0)
							$arUserIDs = $_POST[$arParams["IUS_INPUT_NAME"]];

						if ($bIntranet && strlen($arParams["IUS_INPUT_NAME_EXTRANET"]) > 0 && is_array($_POST[$arParams["IUS_INPUT_NAME_EXTRANET"]]) && count($_POST[$arParams["IUS_INPUT_NAME_EXTRANET"]]) > 0)
						{
							$arUserIDsExtranet = $_POST[$arParams["IUS_INPUT_NAME_EXTRANET"]];
							$arUserIDs = array_merge($arUserIDs, $arUserIDsExtranet);
						}

						$arUserIDs = array_unique($arUserIDs);
						
						if (is_array($arUserIDs) && count($arUserIDs) > 0)
						{
							$strUserIDs = implode("|", $arUserIDs);
							$rsUser = CUser::GetList(($by="id"), ($order="asc"), array("ACTIVE"=>"Y", "ID"=>$strUserIDs));
							while($arUser = $rsUser->GetNext())
							{
								$arUsersFull[] = array("ID" => $arUser["ID"], "NAME_FORMATTED"=> CSocNetUser::FormatNameEx(
									$arUser["NAME"],
									$arUser["SECOND_NAME"],
									$arUser["LAST_NAME"],
									$arUser["LOGIN"],
									($bIntranet ? $arUser["EMAIL"] : ""),
									$arUser["ID"])
								);
							}
						}
						
						/* old component */
						if (!is_array($arUserIDs) || count($arUserIDs) <= 0)
						{
							$arUsersListTmp = Explode(",", $_POST["users_list"]);
							foreach ($arUsersListTmp as $userTmp)
							{
								$userTmp = Trim($userTmp);
								if (StrLen($userTmp) > 0)
									$arUsersList[] = $userTmp;
							}

							if (!$arResult["bExtranet"])
							{
								if (Count($arUsersList) <= 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS"]]) <= 0)
									$errorMessage .= GetMessage("SONET_C33_NO_USERS").". ";
							}
							elseif (Count($arUsersList) <= 0 && strlen($_POST["EMAILS"]) <= 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"]]) <= 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS"]]) <= 0)
								$errorMessage .= GetMessage("SONET_C33_NO_USERS").". ";

							if (StrLen($errorMessage) <= 0)
							{
								$arUsersIDByInput = array();
								foreach ($arUsersList as $user)
								{
									$arFoundUsers = CSocNetUser::SearchUser($user, $bIntranet);
									if ($arFoundUsers && is_array($arFoundUsers) && count($arFoundUsers) > 0)
									{
										foreach ($arFoundUsers as $userID => $userName)
										{
											$userID = IntVal($userID);
											if ($userID > 0)
											{
												if (in_array($userID, $arUsersIDByInput))
													continue;

												$arUsersIDByInput[] = $userID;
												$arUsersFull[] = array("ID" => $userID, "NAME_FORMATTED"=> $userName);
											}
										}
									}
									else
									{
										$arErrorUsers[] = array($user, "");
										$warningMessage .= Str_Replace("#NAME#", HtmlSpecialCharsEx($user), GetMessage("SONET_C33_NO_USER1").". ");
									}
								}
							}
						}
					}

					if (StrLen($errorMessage) <= 0)
					{
						foreach ($arUsersFull as $arUserToRequest)
						{
							$isCurrentUserTmp = ($USER->GetID() == $arUserToRequest["ID"]);
							$canInviteGroup = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUserToRequest["ID"], "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin());
							$user2groupRelation = CSocNetUserToGroup::GetUserRole($arUserToRequest["ID"], $arResult["Group"]["ID"]);

							if ($isCurrentUserTmp)
							{
								$arErrorUsers[] = array($arUserToRequest["NAME_FORMATTED"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUserToRequest["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserToRequest["ID"])) : "");
								$warningMessage .= Str_Replace("#NAME#", $arUserToRequest["NAME_FORMATTED"], GetMessage("SONET_C11_ERR_SELF")).". ";
							}
							elseif (!$canInviteGroup)
							{
								$arErrorUsers[] = array($arUserToRequest["NAME_FORMATTED"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUserToRequest["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserToRequest["ID"])) : "");
								$warningMessage .= Str_Replace("#NAME#", $arUserToRequest["NAME_FORMATTED"], GetMessage("SONET_C11_BAD_USER")).". ";
							}
							elseif ($user2groupRelation)
							{
								$arErrorUsers[] = array($arUserToRequest["NAME_FORMATTED"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUserToRequest["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserToRequest["ID"])) : "");
								$warningMessage .= Str_Replace("#NAME#", $arUserToRequest["NAME_FORMATTED"], GetMessage("SONET_C11_BAD_RELATION")).". ";
							}
							else
							{
								if (CModule::IncludeModule('extranet') && $arResult["bExtranet"] && CExtranet::GetExtranetUserGroupID() > 0)
								{
									$arUserGroups = CUser::GetUserGroup($arUserToRequest["ID"]);
									if (is_array($arUserGroups) && !in_array(CExtranet::GetExtranetUserGroupID(), $arUserGroups))
									{
										$arUserGroups[] = CExtranet::GetExtranetUserGroupID();
										CUser::SetUserGroup($arUserToRequest["ID"], $arUserGroups);
									}
								}
								if (CSocNetUserToGroup::SendRequestToJoinGroup($USER->GetID(), $arUserToRequest["ID"], $arResult["Group"]["ID"], $_POST["MESSAGE"]))
									$arSuccessUsers[] = array($arUserToRequest["NAME_FORMATTED"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUserToRequest["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserToRequest["ID"])) : "");
								else
								{
									$arErrorUsers[] = array($arUserToRequest["NAME_FORMATTED"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUserToRequest["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUserToRequest["ID"])) : "");
									if ($e = $APPLICATION->GetException())
										$warningMessage .= $e->GetString();
								}
							}
						}

						$arEmail = array();
						
						// get all suspicious intranet emails
						if (strlen($arParams["IUS_INPUT_NAME_SUSPICIOUS"]) > 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS"]]) > 0)
						{
							$arEmailOriginal = preg_split("/[\n\r\t\,;]+/", $_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS"]]);
	
							foreach($arEmailOriginal as $addr)
							{
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
									$addr_spec = "$localpart\@$white_space*$domain";
									$phrase = "$word*";
	
									if(preg_match("/$addr_spec/", $addr, $arMatches))
										$addrX = $arMatches[0];
	
									if(preg_match("/$localpart/", $addr, $arMatches))
										$phraseX = trim(trim($arMatches[0]), "\"");
	
									$arEmail[] = array("EMAIL"=>$addrX, "NAME"=>$phraseX);
								}
							}
						}						

						// get all suspicious extranet emails
						if ($arResult["bExtranet"])
						{
							if (strlen($arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"]) > 0 && strlen($_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"]]) > 0)
							{
								$arEmailOriginal = preg_split("/[\n\r\t\,;]+/", $_POST[$arParams["IUS_INPUT_NAME_SUSPICIOUS_EXTRANET"]]);

								foreach($arEmailOriginal as $addr)
								{
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
										$addr_spec = "$localpart\@$white_space*$domain";
										$phrase = "$word*";

										if(preg_match("/$addr_spec/", $addr, $arMatches))
											$addrX = $arMatches[0];

										if(preg_match("/$localpart/", $addr, $arMatches))
											$phraseX = trim(trim($arMatches[0]), "\"");

										$arEmail[] = array("EMAIL"=>$addrX, "NAME"=>$phraseX);
									}
								}
							}
						}
						
						// get all suspicious extranet emails from an old form
						if ($arResult["bExtranet"] && (!is_array($arEmail) || count($arEmail) <= 0) && strlen($_POST["EMAILS"]) > 0)
						{
							$arEmailOriginal = preg_split("/[\n\r\t\,;]+/", $_POST["EMAILS"]);
	
							foreach($arEmailOriginal as $addr)
							{
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
									$addr_spec = "$localpart\@$white_space*$domain";
									$phrase = "$word*";

									if(preg_match("/$addr_spec/", $addr, $arMatches))
										$addrX = $arMatches[0];

									if(preg_match("/$localpart/", $addr, $arMatches))
										$phraseX = trim(trim($arMatches[0]), "\"");

									$arEmail[] = array("EMAIL"=>$addrX, "NAME"=>$phraseX);
								}
							}
						}

						$arUserToRequest = array();
						$arEmailToRegister = array();

						if (Count($arUsersFull) <= 0 && count($arEmail) <= 0)
							$errorMessage .= GetMessage("SONET_C33_NO_USERS").". ";

						foreach($arEmail as $email)
						{
							$arFilter = array(
									"ACTIVE"=>"Y", 
									"=EMAIL"=>$email["EMAIL"]
								);
								
							if (CModule::IncludeModule('extranet') && !CExtranet::IsExtranetSite())
								$arFilter["!UF_DEPARTMENT"] = false;

							$rsUser = CUser::GetList(($by="id"), ($order="asc"), $arFilter);
							$bFound = false;
							while ($arUser = $rsUser->GetNext())
							{
								$bFound = true;

								if (is_array($arUsersIDByInput) && in_array($arUser["ID"], $arUsersIDByInput))
									continue;

								$arUsersFull[] = array("ID" => $arUser["ID"], "NAME_FORMATTED"=> CSocNetUser::FormatNameEx(
									$arUser["NAME"],
									$arUser["SECOND_NAME"],
									$arUser["LAST_NAME"],
									$arUser["LOGIN"],
									($bIntranet ? $arUser["EMAIL"] : ""),
									$arUser["ID"])
								);

								$name = "";

								if (strlen(trim($arUser["NAME"].$arUser["LAST_NAME"])) <= 0)
									$name = "&lt;".$arUser["EMAIL"]."&gt;";
								else
									$name = trim($arUser["NAME"]." ".$arUser["LAST_NAME"])." &lt;".$arUser["EMAIL"]."&gt;";

								$user2groupRelation = CSocNetUserToGroup::GetUserRole($arUser["ID"], $arResult["Group"]["ID"]);

								if ($user2groupRelation)
								{
									$arErrorUsers[] = array($name, CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"])) : "");
									$warningMessage .= Str_Replace("#NAME#", $name, GetMessage("SONET_C11_BAD_RELATION")).". ";
									continue;
								}

								$arUserToRequest[] = array("NAME"=>$name, "ID"=>$arUser["ID"]);

								if ($arResult["bExtranet"] && CExtranet::GetExtranetUserGroupID() > 0)
								{
									$arUserGroups = CUser::GetUserGroup($arUser["ID"]);
									if (is_array($arUserGroups) && !in_array(CExtranet::GetExtranetUserGroupID(), $arUserGroups))
									{
										$arUserGroups[] = CExtranet::GetExtranetUserGroupID();
										CUser::SetUserGroup($arUser["ID"], $arUserGroups);
									}
								}
							}

							if (!$bFound && $arResult["bExtranet"])
								$arEmailToRegister[] = $email;
							elseif(!$bFound && CModule::IncludeModule('extranet') && !CExtranet::IsExtranetSite())	
								$warningMessage .= Str_Replace("#EMAIL#", HtmlSpecialCharsEx($email["EMAIL"]), GetMessage("SONET_C33_NOT_EMPLOYEE"))."<br />";

						}

						// create new extranet users
						if ($arResult["bExtranet"])
						{
							foreach($arEmailToRegister as $email)
							{
								$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
								if($def_group!="")
								{
									$GROUP_ID = explode(",", $def_group);
									$arPolicy = $USER->GetGroupPolicy($GROUP_ID);
								}
								else
									$arPolicy = $USER->GetGroupPolicy(array());

								$password_min_length = intval($arPolicy["PASSWORD_LENGTH"]);
								if($password_min_length <= 0)
									$password_min_length = 6;
								$password_chars = array(
									"abcdefghijklnmopqrstuvwxyz",
									"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
									"0123456789",
								);

								if($arPolicy["PASSWORD_PUNCTUATION"] === "Y")
									$password_chars[] = ",.<>/?;:'\"[]{}\\|`~!@#\$%^&*()-_+=";

								$password = randString($password_min_length, $password_chars);
								$checkword = randString(8);

								$user = new CUser;

								$name = $last_name = "";
								if (strlen($email["NAME"]) > 0)
									list($name, $last_name) = explode(" ", $email["NAME"]);

								$arFields = array(
									"EMAIL"		=> $email["EMAIL"],
									"LOGIN"		=> $email["EMAIL"],
									"NAME"			=> $name,
									"LAST_NAME"		=> $last_name,
									"ACTIVE"		=> "Y",
									"GROUP_ID"		=> array(2),
									"PASSWORD"		=> $password,
									"CONFIRM_PASSWORD"	=> $password,
									"CONFIRM_CODE"		=> $checkword,
									"LID"			=> SITE_ID
								);

								if (CExtranet::GetExtranetUserGroupID() > 0)
									$arFields["GROUP_ID"] = array(2, CExtranet::GetExtranetUserGroupID());

								$NEW_USER_ID = $user->Add($arFields);

								if (intval($NEW_USER_ID) > 0)
								{
									$arUserToRequest[] = array("NAME"=>(strlen($email["NAME"]) > 0 ? $email["NAME"]." " : "")."&lt;".$email["EMAIL"]."&gt;", "ID"=>$NEW_USER_ID);
	
									$event = new CEvent;
									$arFields = Array(
										"USER_ID"	=>	$NEW_USER_ID,
										"CHECKWORD"	=>	$checkword,
										"EMAIL"	=>	$email["EMAIL"],
										"USER_TEXT" => ''
									);
									$event->Send("EXTRANET_INVITATION", SITE_ID, $arFields);
								}
								else
								{
									$strError = $user->LAST_ERROR;
									if ($APPLICATION->GetException())
									{
										$err = $APPLICATION->GetException();
										$strError .= $err->GetString();
										$APPLICATION->ResetException();
									}
									$warningMessage .= Str_Replace("#EMAIL#", HtmlSpecialCharsEx($email["EMAIL"]), GetMessage("SONET_C33_CANNOT_USER_ADD").$strError);
								}
							}
						}
						
						if ((!is_array($arUsersFull) || count($arUsersFull) <= 0) && (!is_array($arUserToRequest) || count($arUserToRequest) <= 0))
							$errorMessage .= GetMessage("SONET_C33_NO_USERS").". ";
						else
						{
							foreach($arUserToRequest as $arUser)
							{
								if (CSocNetUserToGroup::SendRequestToJoinGroup($USER->GetID(), $arUser["ID"], $arResult["Group"]["ID"], $_POST["MESSAGE"]))
									$arSuccessUsers[] = array($arUser["NAME"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"])) : "");
								else
								{
									$arErrorUsers[] = array($arUser["NAME"], CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUser["ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin()) ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arUser["ID"])) : "");
									if ($e = $APPLICATION->GetException())
										$warningMessage .= $e->GetString();
								}
							}
						}
					}

					$arResult["SuccessUsers"] = $arSuccessUsers;
					$arResult["ErrorUsers"] = $arErrorUsers;
					$arResult["WarningMessage"] = $warningMessage;
					if (strlen($errorMessage) > 0)
						$arResult["ErrorMessage"] = $errorMessage;
					else
						$arResult["ShowForm"] = "Confirm";
				}

				if ($arResult["ShowForm"] == "Input")
				{
					if (!CModule::IncludeModule('extranet') || CExtranet::IsIntranetUser())
						$arResult["isCurrentUserIntranet"] = true;
					$arResult["Friends"] = false;
					if (CSocNetUser::IsFriendsAllowed())
					{
						$dbFriends = CSocNetUserRelations::GetRelatedUsers($USER->GetID(), SONET_RELATIONS_FRIEND, false);
						if ($dbFriends)
						{
							$arResult["Friends"] = array();
							$arResult["Friends"]["List"] = false;
							while ($arFriends = $dbFriends->GetNext())
							{
								if ($arResult["Friends"]["List"] == false)
									$arResult["Friends"]["List"] = array();

								$pref = ((IntVal($USER->GetID()) == $arFriends["FIRST_USER_ID"]) ? "SECOND" : "FIRST");

								if (!CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arFriends[$pref."_USER_ID"], "invitegroup", CSocNetUser::IsCurrentUserModuleAdmin()))
									continue;

								$pu = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arFriends[$pref."_USER_ID"]));
								$canViewProfile = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arFriends[$pref."_USER_ID"], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());

								if (intval($arParams["THUMBNAIL_LIST_SIZE"]) > 0)
								{
									if (intval($arFriends[$pref."_USER_PERSONAL_PHOTO"]) <= 0)
									{
										switch ($arFriends[$pref."_USER_PERSONAL_GENDER"])
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
										$arFriends[$pref."_USER_PERSONAL_PHOTO"] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
									}	
									$arImage = CSocNetTools::InitImage($arFriends[$pref."_USER_PERSONAL_PHOTO"], $arParams["THUMBNAIL_LIST_SIZE"], "/bitrix/images/socialnetwork/nopic_30x30.gif", 30, $pu, $canViewProfile);
								}
								else // old 
									$arImage = CSocNetTools::InitImage($arFriends[$pref."_USER_PERSONAL_PHOTO"], 150, "/bitrix/images/socialnetwork/nopic_user_150.gif", 150, $pu, $canViewProfile);

								$arResult["Friends"]["List"][] = array(
									"ID" => $arFriends["ID"],
									"USER_ID" => $arFriends[$pref."_USER_ID"],
									"USER_NAME_FORMATED" => CSocNetUser::FormatNameEx(
										$arFriends[$pref."_USER_NAME"],
										$arFriends[$pref."_USER_SECOND_NAME"],
										$arFriends[$pref."_USER_LAST_NAME"],
										$arFriends[$pref."_USER_LOGIN"],
										($bIntranet ? $arFriends[$pref."_USER_EMAIL"] : ""),
										$arFriends[$pref."_USER_ID"]
									),
									"USER_NAME" => $arFriends[$pref."_USER_NAME"],
									"USER_LAST_NAME" => $arFriends[$pref."_USER_LAST_NAME"],
									"USER_SECOND_NAME" => $arFriends[$pref."_USER_SECOND_NAME"],
									"USER_LOGIN" => $arFriends[$pref."_USER_LOGIN"],
									"USER_PERSONAL_PHOTO" => $arFriends[$pref."_USER_PERSONAL_PHOTO"],
									"USER_PERSONAL_PHOTO_FILE" => $arImage["FILE"],
									"USER_PERSONAL_PHOTO_IMG" => $arImage["IMG"],
									"USER_PROFILE_URL" => $pu,
									"SHOW_PROFILE_LINK" => $canViewProfile,
									"IS_ONLINE" => ($arFriends[$pref."_USER_IS_ONLINE"] == "Y")
								);
							}
						}
					}
				}
			}
		}
	}
	$arResult["bIntranet"] = $bIntranet;
}
$this->IncludeComponentTemplate();
