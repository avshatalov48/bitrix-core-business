<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

COption::SetOptionString('main', 'new_user_registration', 'Y');
COption::SetOptionString('main', 'captcha_registration', 'Y');
COption::SetOptionInt("search", "suggest_save_days", 250);

if(COption::GetOptionString('main', 'CAPTCHA_presets', '') == '')
{
	COption::SetOptionString('main', 'CAPTCHA_transparentTextPercent', '0');
	COption::SetOptionString('main', 'CAPTCHA_arBGColor_1', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_arBGColor_2', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_numEllipses', '0');
	COption::SetOptionString('main', 'CAPTCHA_arEllipseColor_1', '7F7F7F');
	COption::SetOptionString('main', 'CAPTCHA_arEllipseColor_2', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_bLinesOverText', 'Y');
	COption::SetOptionString('main', 'CAPTCHA_numLines', '0');
	COption::SetOptionString('main', 'CAPTCHA_arLineColor_1', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_arLineColor_2', 'FFFFFF');
	COption::SetOptionString('main', 'CAPTCHA_textStartX', '40');
	COption::SetOptionString('main', 'CAPTCHA_textFontSize', '26');
	COption::SetOptionString('main', 'CAPTCHA_arTextColor_1', '000000');
	COption::SetOptionString('main', 'CAPTCHA_arTextColor_2', '000000');
	COption::SetOptionString('main', 'CAPTCHA_textAngel_1', '-15');
	COption::SetOptionString('main', 'CAPTCHA_textAngel_2', '15');
	COption::SetOptionString('main', 'CAPTCHA_textDistance_1', '-2');
	COption::SetOptionString('main', 'CAPTCHA_textDistance_2', '-2');
	COption::SetOptionString('main', 'CAPTCHA_bWaveTransformation', 'Y');
	COption::SetOptionString('main', 'CAPTCHA_arBorderColor', '000000');
	COption::SetOptionString('main', 'CAPTCHA_arTTFFiles', 'bitrix_captcha.ttf');
	COption::SetOptionString('main', 'CAPTCHA_letters', 'ABCDEFGHJKLMNPQRSTWXYZ23456789');
	COption::SetOptionString('main', 'CAPTCHA_presets', '2');
}	

	COption::SetOptionString('socialnetwork', 'allow_tooltip', 'Y', false , WIZARD_SITE_ID);
	COption::SetOptionString('socialnetwork', "tooltip_rating_id", serialize(array(1)), false, WIZARD_SITE_ID);

if (WIZARD_INSTALL_DEMO_DATA)	
{
	$user = new CUser;
	
	$pwdUser =  uniqid('pwd_');
	$arFields = Array(
	  "NAME"              => GetMessage("TASK_WIZARD_USER_NAME_1"),
	  "LAST_NAME"         => GetMessage("TASK_WIZARD_USER_SURNAME_1"),
	  "EMAIL"             => GetMessage("TASK_WIZARD_USER_EMAIL_1"),
	  "LOGIN"             => GetMessage("TASK_WIZARD_USER_LOGIN_1"),
	  "LID"               => LANGUAGE_ID,
	  "ACTIVE"            => "Y",
	  "GROUP_ID"          => array(3, 2),
	  "PASSWORD"          => $pwdUser,
	  "CONFIRM_PASSWORD"  => $pwdUser,
	);
	
	$user->Add($arFields);
	
	$pwdUser =  uniqid('pwd_');
	
	$arFields = Array(
	  "NAME"              => GetMessage("TASK_WIZARD_USER_NAME_2"),
	  "LAST_NAME"         => GetMessage("TASK_WIZARD_USER_SURNAME_2"),
	  "EMAIL"             => GetMessage("TASK_WIZARD_USER_EMAIL_2"),
	  "LOGIN"             => GetMessage("TASK_WIZARD_USER_LOGIN_2"),
	  "LID"               => LANGUAGE_ID,
	  "ACTIVE"            => "Y",
	  "GROUP_ID"          => array(3, 2),
	  "PASSWORD"          => $pwdUser,
	  "CONFIRM_PASSWORD"  => $pwdUser,
	);
	
	$user->Add($arFields);
	
	$arIMAGE = CFile::MakeFileArray(WIZARD_ABSOLUTE_PATH."/site/services/blog/images/user_1.jpg");
	$arIMAGE["MODULE_ID"] = "main";
	
	$pwdUser =  uniqid('pwd_');
	$arFields = Array(
	  "NAME"              => GetMessage("TASK_WIZARD_USER_NAME_3"),
	  "LAST_NAME"         => GetMessage("TASK_WIZARD_USER_SURNAME_3"),
	  "EMAIL"             => GetMessage("TASK_WIZARD_USER_EMAIL_3"),
	  "LOGIN"             => GetMessage("TASK_WIZARD_USER_LOGIN_3"),
	  "LID"               => LANGUAGE_ID,
	  "ACTIVE"            => "Y",
	  "GROUP_ID"          => array(3, 2),
	  "PASSWORD"          => $pwdUser,
	  "CONFIRM_PASSWORD"  => $pwdUser,
	  "UF_MOYMIR"         => "bitrix",
	  "UF_MYYANDEX"       => "bitrix",
	  "UF_MOYMIR"         => "bitrix",
	  "UF_SKYPE"          => "bitrix",
	  "UF_TWITTER"        => "bitrix",
	  "PERSONAL_PHOTO"    => $arIMAGE
	);
	
	$user->Add($arFields);
}
	
$rsGroups = CGroup::GetList("c_sort", "desc", array("ACTIVE"=>"Y", "ADMIN"=>"N", "ANONYMOUS"=>"N"));
if(!($rsGroups->Fetch()))
{
	$group = new CGroup;
	$arFields = Array(
	  "ACTIVE"       => "Y",
	  "C_SORT"       => 100,
	  "NAME"         => GetMessage("REGISTERED_USERS"),
	  "DESCRIPTION"  => "",
	  );
	$NEW_GROUP_ID = $group->Add($arFields);
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"main", "SYS"=>"Y", "BINDIG"=>"module","LETTER"=>"P"));
	if($arTask = $rsTasks->Fetch())
	{
	  CGroup::SetModulePermission($NEW_GROUP_ID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
	
	COption::SetOptionString('main', 'new_user_registration_def_group', $NEW_GROUP_ID);
}

$userGroupID = "";
$dbGroup = CGroup::GetList("", "", Array("STRING_ID" => "community_administrator"));

if($arGroup = $dbGroup -> Fetch())
{
	$userGroupID = $arGroup["ID"];
}
else
{
	$group = new CGroup;
	$arFields = Array(
	  "ACTIVE"       => "Y",
	  "C_SORT"       => 300,
	  "NAME"         => GetMessage("COMMUNITY_WIZARD_ADMINISTRATOR"),
	  "DESCRIPTION"  => GetMessage("COMMUNITY_WIZARD_ADMINISTRATOR_DESCR"),
	  "USER_ID"      => array(),
	  "STRING_ID"      => "community_administrator",
	  );
	$userGroupID = $group->Add($arFields);
	$DB->Query("INSERT INTO b_sticker_group_task(GROUP_ID, TASK_ID)	SELECT ".intval($userGroupID).", ID FROM b_task WHERE NAME='stickers_edit' AND MODULE_ID='fileman'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
}

$userGroupID = "";
$dbGroup = CGroup::GetList("", "", Array("STRING_ID" => "content_editor"));

if($arGroup = $dbGroup -> Fetch())
{
	$userGroupID = $arGroup["ID"];
}
else
{
	$group = new CGroup;
	$arFields = Array(
	  "ACTIVE"       => "Y",
	  "C_SORT"       => 300,
	  "NAME"         => GetMessage("TASK_WIZARD_CONTENT_EDITOR"),
	  "DESCRIPTION"  => GetMessage("TASK_WIZARD_CONTENT_EDITOR_DESCR"),
	  "USER_ID"      => array(),
	  "STRING_ID"      => "content_editor",
	  );
	$userGroupID = $group->Add($arFields);
	$DB->Query("INSERT INTO b_sticker_group_task(GROUP_ID, TASK_ID)	SELECT ".intval($userGroupID).", ID FROM b_task WHERE NAME='stickers_edit' AND MODULE_ID='fileman'", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
}

if(intval($userGroupID) > 0)
{
	WizardServices::SetFilePermission(Array($siteID, "/bitrix/admin"), Array($userGroupID => "R"));
	CMain::SetGroupRight("blog", $userGroupID, "W");
	CMain::SetGroupRight("forum", $userGroupID, "W");
	CMain::SetGroupRight("socialnetwork", $userGroupID, "W");
	
	$new_task_id = CTask::Add(array(
	        "NAME" => GetMessage("TASK_WIZARD_CONTENT_EDITOR"),
	        "DESCRIPTION" => GetMessage("TASK_WIZARD_CONTENT_EDITOR_DESC"),
	        "LETTER" => "Q",
	        "BINDING" => "module",
	        "MODULE_ID" => "main",
	));
	if($new_task_id)
	{
	  $arOps = array();
	  $rsOp = COperation::GetList(array(), array("NAME"=>"cache_control|view_own_profile|edit_own_profile"));
	  while($arOp = $rsOp->Fetch())
	    $arOps[] = $arOp["ID"];
	  CTask::SetOperations($new_task_id, $arOps);
	}
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"main", "SYS"=>"N", "BINDIG"=>"module","LETTER"=>"Q"));
	if($arTask = $rsTasks->Fetch())
	{
	  CGroup::SetModulePermission($userGroupID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
	
	$rsTasks = CTask::GetList(array(), array("MODULE_ID"=>"fileman", "SYS"=>"Y", "BINDIG"=>"module","LETTER"=>"F"));
	if($arTask = $rsTasks->Fetch())
	{
	  CGroup::SetModulePermission($userGroupID, $arTask["MODULE_ID"], $arTask["ID"]);
	}
	
	$SiteDir = "";
	if(WIZARD_SITE_ID != "s1"){
		$SiteDir = "/site_" . WIZARD_SITE_ID;
	}
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/index.php"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/blogs/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/forum/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/groups/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/people/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/photo/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/search/"), Array($userGroupID => "W"));
}
?>