<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arComponentParameters = array(
	"PARAMETERS" => array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["FID"]}'),
		"TID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_TID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["TID"]}'),
		"MID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MID"]}'),
		"PAGE_NAME" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_PAGE_NAME"),
			"TYPE" => "STRING",
			"DEFAULT" => "message"),
		"MESSAGE_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_MESSAGE_TYPE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["MESSAGE_TYPE"]}'),
		"EDITOR_CODE_DEFAULT" => Array(
			"NAME" => GetMessage("F_EDITOR_CODE_DEFAULT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "BASE",),

		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?FID=#FID#&TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "list.php?FID=#FID#"),
		"URL_TEMPLATES_HELP" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_HELP_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "help.php"),
		"URL_TEMPLATES_RULES" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_RULES_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "rules.php"),

		"AJAX_TYPE" => CForumParameters::GetAjaxType(),

		"CACHE_TIME" => Array(),
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
			$db_res = CVoteChannel::GetList($by = "", $order = "", array("ACTIVE" => "Y"), $is_filtered);
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
			if (intVal($arCurrentValues["VOTE_CHANNEL_ID"]) > 0)
				$voteId = intVal($arCurrentValues["VOTE_CHANNEL_ID"]);
			else
				$voteId = key($arVoteChannels);

			if (!empty($voteId))
			{
				$arPermissions = CVoteChannel::GetArrayGroupPermission($voteId);
				$arUGroupsEx = array();
				$db_res = CGroup::GetList($by = "c_sort", $order = "asc");
				while($res = $db_res -> Fetch())
				{
					if ((isset($arPermissions[$res["ID"]]) && intVal($arPermissions[$res["ID"]]) >= 2) || intVal($res["ID"]) == 1):
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
