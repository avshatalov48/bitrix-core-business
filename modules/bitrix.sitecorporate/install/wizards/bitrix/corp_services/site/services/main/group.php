<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

COption::SetOptionString('socialnetwork', 'allow_tooltip', 'N', false , $site_id);

$stickerTaskId = 0;
$stickerPerm = [];
if (CModule::IncludeModule('fileman'))
{
	$rsDB = \Bitrix\Main\TaskTable::getList([
		'select' => ['ID'],
		'filter' => ['=MODULE_ID' => 'fileman', '=NAME' => 'stickers_edit'],
	]);
	if ($arTask = $rsDB->fetch())
	{
		$stickerTaskId = (int)$arTask['ID'];
		$stickerPerm = CSticker::GetAccessPermissions();
	}
}

$userGroupID = "";
$dbGroup = CGroup::GetList("", "", Array("STRING_ID" => "content_editor"));

if($arGroup = $dbGroup -> Fetch())
{
	$userGroupID = (int)$arGroup["ID"];
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
	$userGroupID = (int)$group->Add($arFields);

	if ($stickerTaskId > 0)
	{
		$stickerPerm[$userGroupID] = $stickerTaskId;
	}
}
if ($userGroupID > 0)
{
	WizardServices::SetFilePermission(Array($siteID, "/bitrix/admin"), Array($userGroupID => "R"));

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
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/about/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/contacts/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/news/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/services/"), Array($userGroupID => "W"));
	WizardServices::SetFilePermission(Array($siteID, $SiteDir . "/search/"), Array($userGroupID => "W"));
}

if ($stickerTaskId > 0 && !empty($stickerPerm))
{
	CSticker::SaveAccessPermissions($stickerPerm);
}
