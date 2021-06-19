<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = Array(
	"GROUPS" => array(
		"URL_TEMPLATES" => array(
			"NAME" => GetMessage("F_URL_TEMPLATES"),
		),
	),
	
	"PARAMETERS" => Array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
		"MESSAGE_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_MESSAGE_TYPE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MESSAGE_TYPE"]}'),
		
		"URL_TEMPLATES_INDEX" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_INDEX_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "index.php"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "read.php?FID=#FID#&TID=#TID#"),
		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_PROFILE_VIEW" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		
		"DATE_TIME_FORMAT" => CComponentUtil::GetDateTimeFormatField(GetMessage("F_DATE_TIME_FORMAT"), "ADDITIONAL_SETTINGS"),
		"NAME_TEMPLATE" => array(
			"TYPE" => "LIST",
			"NAME" => GetMessage("F_NAME_TEMPLATE"),
			"VALUES" => CComponentUtil::GetDefaultNameTemplates(),
			"MULTIPLE" => "N",
			"ADDITIONAL_VALUES" => "Y",
			"DEFAULT" => "",
			"PARENT" => "ADDITIONAL_SETTINGS",
			),
		"SET_NAVIGATION" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_SET_NAVIGATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
		"AJAX_TYPE" => CForumParameters::GetAjaxType(),
		// "DISPLAY_PANEL" => Array(
			// "PARENT" => "ADDITIONAL_SETTINGS",
			// "NAME" => GetMessage("F_DISPLAY_PANEL"),
			// "TYPE" => "CHECKBOX",
			// "DEFAULT" => "N"),
		
		"CACHE_TIME" => Array(),
		"SET_TITLE" => Array(),
	)
);
if (IsModuleInstalled("vote"))
{
	$right = $GLOBALS["APPLICATION"]->GetGroupRight("vote");
	if ($right >= "W")
	{
		$arComponentParameters["GROUPS"]["VOTE_SETTINGS"] = array("NAME" => GetMessage("F_VOTE_SETTINGS"));
		$arComponentParameters["PARAMETERS"]["SHOW_VOTE"] = array(
				"PARENT" => "VOTE_SETTINGS",
				"NAME" => GetMessage("F_SHOW_VOTE"),
				"TYPE" => "CHECKBOX",
				"DEFAULT" => "N", 
				"REFRESH" => "Y");
		if ($arCurrentValues["SHOW_VOTE"] == "Y")
		{
			$arVoteChannels = array();
			CModule::IncludeModule("vote");
			$db_res = CVoteChannel::GetList("", "", array("ACTIVE" => "Y"));
			if ($db_res && $res = $db_res->Fetch())
			{
				do 
				{
					$arVoteChannels[$res["ID"].""] = "[ ".$res["ID"]." ]".$res["TITLE"];
				} while ($res = $db_res->Fetch());
			}
			$arComponentParameters["PARAMETERS"]["VOTE_CHANNEL_ID"] = array(
					"PARENT" => "VOTE_SETTINGS",
					"NAME" => GetMessage("F_VOTE_CHANNEL_ID"),
					"TYPE" => "LIST",
					"VALUES" => $arVoteChannels,
					"DEFAULT" => "", 
					"REFRESH" => "Y");
			reset($arVoteChannels);
			if (intval($arCurrentValues["VOTE_CHANNEL_ID"]) > 0)
				$voteId = intval($arCurrentValues["VOTE_CHANNEL_ID"]);
			else
				$voteId = key($arVoteChannels);
			if (!empty($voteId))
			{
				$arPermissions = CVoteChannel::GetArrayGroupPermission($voteId);
				$arUGroupsEx = array();
				$db_res = CGroup::GetList();
				while($res = $db_res -> Fetch())
				{
					if ((isset($arPermissions[$res["ID"]]) && intval($arPermissions[$res["ID"]]) >= 2) || intval($res["ID"]) == 1):
						$arUGroupsEx[$res["ID"]] = $res["NAME"]."[".$res["ID"]."]";
					endif;
				}
				if (!empty($arUGroupsEx)):
					$arComponentParameters["PARAMETERS"]["VOTE_GROUP_ID"] = array(
						"PARENT" => "VOTE_SETTINGS",
						"NAME" => GetMessage("F_VOTE_GROUP_ID"),
						"TYPE" => "LIST",
						"VALUES" => $arUGroupsEx,
						"DEFAULT" => "",
						"MULTIPLE" => "Y");
				endif;
			}
		}
	}
}
?>
