<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;

$arForum = array();
$db_res = CForumNew::GetList(array(), array());
if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
		$arForum[intVal($res["ID"])] = $res["NAME"];
	}while ($res = $db_res->GetNext());
}

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
			"TYPE" => "LIST",
			"VALUES" => $arForum,
			"DEFAULT" => '0'),
		"SOCNET_GROUP_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SOCNET_GROUP_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["SOCNET_GROUP_ID"]}'),
		"USER_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_USER_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => '={$_REQUEST["USER_ID"]}'),
		"SORT_BY" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORTING_ORD"),
			"TYPE" => "LIST",
			"DEFAULT" => "LAST_POST_DATE",
			"VALUES" => array(
				"TITLE" => GetMessage("F_SHOW_TITLE"),
				"USER_START_NAME" => GetMessage("F_SHOW_USER_START_NAME"),
				"POSTS" => GetMessage("F_SHOW_POSTS"),
				"VIEWS" => GetMessage("F_SHOW_VIEWS"),
				"LAST_POST_DATE" => GetMessage("F_SHOW_LAST_POST_DATE"))),
		"SORT_ORDER" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_SORTING_BY"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" =>  Array("ASC"=>GetMessage("F_DESC_ASC"), "DESC"=>GetMessage("F_DESC_DESC"))),

		"URL_TEMPLATES_READ" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_READ_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic.php?TID=#TID#"),
		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_USER" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_PROFILE_VIEW_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "profile_view.php?UID=#UID#"),
		
		"TOPICS_COUNT" => Array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("F_TOPICS_PER_PAGE"),
			"TYPE" => "STRING",
			"DEFAULT" => intVal(COption::GetOptionString("forum", "TOPICS_PER_PAGE", "10")))
	)
);
?>
