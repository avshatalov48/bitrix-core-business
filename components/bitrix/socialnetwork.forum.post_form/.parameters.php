<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(!CModule::IncludeModule("forum"))
	return;
$arForum = array();
$db_res = CForumNew::GetList(array(), array());
if ($db_res && ($res = $db_res->GetNext()))
{
	do
	{
		$arForum[intval($res["ID"])] = $res["NAME"];
	}while ($res = $db_res->GetNext());
}

$arComponentParameters = array(
	"PARAMETERS" => array(
		"FID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_DEFAULT_FID"),
			"TYPE" => "LIST",
			"VALUES" => $arForum,
			"DEFAULT" => '0'),
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
		"bVarsFromForm" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("F_BVARSFROMFORM"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"),
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

		"URL_TEMPLATES_MESSAGE" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_MESSAGE_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "message.php?TID=#TID#&MID=#MID#"),
		"URL_TEMPLATES_TOPIC_LIST" => Array(
			"PARENT" => "URL_TEMPLATES",
			"NAME" => GetMessage("F_LIST_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "topic_list.php"),

		"AJAX_TYPE" => CForumParameters::GetAjaxType(),

		"CACHE_TIME" => Array(),
	)
);
?>
