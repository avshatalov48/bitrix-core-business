<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)	die();
if (!CModule::IncludeModule("vote")): 
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
endif;
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
if (is_array($arParams["CHANNEL_SID"])):
	$arr = array();
	foreach ($arParams["CHANNEL_SID"] as $v):
		$v = trim(str_replace("-", "", $v));
		$v = (preg_match("~^[A-Za-z0-9_]+$~", $v) ? $v : "");
		if (strlen($v) > 0):
			$arr[] = $v;
		endif;
	endforeach;
	$arParams["CHANNEL_SID"] = "";
	if (!empty($arr)):
		$arParams["CHANNEL_SID"] = $arr;
	endif;
else:
	$arParams["CHANNEL_SID"] = trim(str_replace("-", "", $arParams["CHANNEL_SID"]));
	$arParams["CHANNEL_SID"] = (preg_match("~^[A-Za-z0-9_]+$~", $arParams["CHANNEL_SID"]) ? $arParams["CHANNEL_SID"] : "");
endif;
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
		"vote_form" => "PAGE_NAME=vote_new&VOTE_ID=#VOTE_ID#",
		"vote_result" => "PAGE_NAME=vote_result&VOTE_ID=#VOTE_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE):
		if (strlen(trim($arParams[strtoupper($URL)."_TEMPLATE"])) <= 0)
			$arParams[strtoupper($URL)."_TEMPLATE"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strtoupper($URL)."_TEMPLATE"] = $arParams[strtoupper($URL)."_TEMPLATE"];
		$arParams[strtoupper($URL)."_TEMPLATE"] = htmlspecialcharsbx($arParams["~".strtoupper($URL)."_TEMPLATE"]);
	endforeach;
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$arResult["VOTES"] = array();
$arResult["NAV_STRING"] = "";

$db_res = GetVoteList($arParams["CHANNEL_SID"],
	array(
		"bDescPageNumbering" => false,
		"nPageSize" => 10,
		"bShowAll" => false
	)
);
$channelID = false;
if ($db_res)
{
	$arResult["NAV_STRING"] = $db_res->GetPageNavString(GetMessage("VOTE_PAGES"));
	$votedUser = \Bitrix\Vote\User::getCurrent();
	while ($res = $db_res->Fetch())
	{
		$channelID = ($channelID ?: $res["CHANNEL_ID"]);
		$res["USER_ALREADY_VOTE"] = ($votedUser->isVotedFor($res["ID"]) ? "Y" : "N");
		$res["URL"] = array(
				"~VOTE_RESULT" => CComponentEngine::makePathFromTemplate($arParams["~VOTE_RESULT_TEMPLATE"], array("VOTE_ID" => $res["ID"])),
				"~VOTE_FORM" => CComponentEngine::makePathFromTemplate($arParams["~VOTE_FORM_TEMPLATE"], array("VOTE_ID" => $res["ID"])),
				"VOTE_RESULT" => CComponentEngine::makePathFromTemplate($arParams["VOTE_RESULT_TEMPLATE"], array("VOTE_ID" => $res["ID"])),
				"VOTE_FORM" => CComponentEngine::makePathFromTemplate($arParams["VOTE_FORM_TEMPLATE"], array("VOTE_ID" => $res["ID"])));
		$res["IMAGE"] = CFile::GetFileArray($res["IMAGE_ID"]);
		// For custom 
		foreach ($res["URL"] as $key => $val):
			$res[$key."_URL"] = $val;
		endforeach;
		$res["TITLE"] = htmlspecialcharsEx($res["TITLE"]);
		if ($res['DESCRIPTION_TYPE'] == 'text')
			$res['DESCRIPTION'] = htmlspecialcharsbx($res['DESCRIPTION']);
		$arResult["VOTES"][$res["ID"]] = $res;
	}
}
/********************************************************************
				/Data
********************************************************************/

if ($channelID && $GLOBALS["APPLICATION"]->GetGroupRight("vote") == "W" && CModule::IncludeModule("intranet") && is_object($GLOBALS['INTRANET_TOOLBAR']))
{
	$GLOBALS['INTRANET_TOOLBAR']->AddButton(array(
		'TEXT' => GetMessage("comp_voting_list_add"),
		'TITLE' => GetMessage("comp_voting_list_add_title"),
		'ICON' => 'add',
		'HREF' => '/bitrix/admin/vote_edit.php?lang='.LANGUAGE_ID."&CHANNEL_ID=".$channelID,
		'SORT' => '100',
	));
	$GLOBALS['INTRANET_TOOLBAR']->AddButton(array(
		'TEXT' => GetMessage("comp_voting_list_list"),
		'TITLE' => GetMessage("comp_voting_list_list_title"),
		'ICON' => 'settings',
		'HREF' => '/bitrix/admin/vote_list.php?lang='.LANGUAGE_ID."&find_channel_id=".$channelID,
		'SORT' => '200',
	));
}
	
$this->IncludeComponentTemplate();
?>
