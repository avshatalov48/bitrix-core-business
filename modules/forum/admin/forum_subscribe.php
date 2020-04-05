<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
\Bitrix\Main\Loader::includeModule("forum");
if ($APPLICATION->GetGroupRight("forum") == "D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Type\DateTime;
use \Bitrix\Main\Localization\Loc;
IncludeModuleLangFile(__FILE__);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/forum/prolog.php");
\Bitrix\Main\Loader::includeModule("forum");
//region get Default data
$forums = [];
$dbRes = \Bitrix\Forum\ForumTable::getList([
	"select" => ["ID", "NAME"],
	"order" => ["SORT"=>"ASC", "NAME"=>"ASC"]
]);
while($res = $dbRes->fetch())
{
	$forums[$res["ID"]] = $res["NAME"];
}
asort($forums);
array_unshift($forums, GetMessage("FM_SPACE"));
/*@var $request HttpRequest*/
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
//endregion

$sTableID = "tbl_subscribe1";
$oSort = new CAdminUiSorting($sTableID, "CNT", "DESC");
$lAdmin = new CAdminUiList($sTableID, $oSort);
//region Making Filter
$filterFields = [
	array(
		"id" => "USER.LOGIN",
		"name" => GetMessage("FM_FLT_LOGIN"),
		"filterable" => "%",
		"default" => true
	),
	array(
		"id" => "USER.NAME",
		"name" => GetMessage("FM_FLT_FIO"),
		"filterable" => "%",
		"default" => true
	),
	array(
		"id" => "USER.EMAIL",
		"name" => GetMessage("FM_FLT_EMAIL"),
		"filterable" => "%",
		"default" => true
	),
	array(
		"id" => "START_DATE",
		"name" => GetMessage("FM_FLT_START_DATE"),
		"type" => "date",
	),
	array(
		"id" => "FORUM_ID",
		"name" => GetMessage("FM_FLT_FORUM"),
		"type" => "list",
		"items" => $forums,
		"params" => array("multiple" => "Y"),
		"filterable" => ""
	),
	array(
		"id" => "NEW_TOPIC_ONLY",
		"name" => GetMessage("FM_FLT_SUBSCR_TYPE"),
		"type" => "list",
		"items" => [
			"Y" => GetMessage("FM_NEW_TOPIC_ONLY"),
			"N" => GetMessage("FM_ALL_MESSAGE")
		],
		"filterable" => ""
	),
];
/*******************************************************************/
$arFilter = [];
$lAdmin->AddFilter($filterFields, $arFilter);

//endregion
//region Group Actions
/*******************************************************************/
if (
	check_bitrix_sessid() && 
	($ids = $lAdmin->GroupAction()) &&
	$request->get("action_button_".$sTableID) === "delete" &&
	CForumUser::IsAdmin())
{
	if ($request->get("action_all_rows_".$sTableID) === "Y")
	{
		$dbRes = \Bitrix\Forum\SubscribeTable::getList([
			"select" => ["USER_ID"],
			"filter" => $arFilter,
			"group" => ["USER_ID"]
		]);
		$ids = [];
		while ($id = $dbRes->fetch())
		{
			$ids[] = $id["USER_ID"];
		}
	}
	foreach($ids as $ID)
	{
		CForumSubscribe::DeleteUSERSubscribe($ID);
	}
}
//endregion
$nav = $lAdmin->getPageNavigation($sTableID);
$nav->getOffset();
$pageSize = $lAdmin->getNavSize();
$lAdmin->AddHeaders(array(
	array("id"=>"USER_ID", "content"=>GetMessage("FM_HEAD_USER_ID"), "sort"=>"USER_ID", "default"=>true),
	array("id"=>"EMAIL", "content"=>GetMessage("FM_HEAD_EMAIL"), "sort"=>"USER.EMAIL", "default"=>true),
	array("id"=>"LOGIN", "content"=>GetMessage("FM_HEAD_LOGIN"), "sort"=>"USER.LOGIN", "default"=>true),
	array("id"=>"NAME", "content"=>GetMessage("FM_HEAD_NAME"), "sort"=>"USER.NAME", "default"=>true),
	array("id"=>"LAST_NAME", "content"=>GetMessage("FM_HEAD_LAST_NAME"), "sort"=>"USER.LAST_NAME", "default"=>true),
	array("id"=>"CNT", "content"=>GetMessage("FM_HEAD_SUBSC"), "sort"=>"CNT", "default"=>true)
));
$dbRes = \Bitrix\Forum\SubscribeTable::getList([
	"select" => ["CNT", "USER_ID"],
	"runtime" => [
			new \Bitrix\Main\Entity\ExpressionField("CNT", "COUNT(*)")
	],
	"order" => [$oSort->getField() => $oSort->getOrder()],
	"filter" => $arFilter,
	"limit" => (($limit = $nav->getLimit()) > 0 ? $limit + 1 : 0),
	"offset" => $nav->getOffset(),
	"count_total" => $lAdmin->isTotalCountRequest(),
	"group" => ["USER_ID"]
]);
if ($lAdmin->isTotalCountRequest())
{
	$lAdmin->sendTotalCountResponse($dbRes->getCount());
}

$users = [];
$records = [];
while ($res = $dbRes->fetch())
{
	$records[] = $res + ["ID" => count($records)];
	$users[$res["USER_ID"]] = [];
}
$nav->setRecordCount($nav->getOffset() + count($records));
if ($limit > 0)
{
	$records = array_slice($records, 0, $limit);
}
$lAdmin->setNavigation($nav, GetMessage("FM_TITLE_PAGE"), false);

if (!empty($users))
{
	$userRes = \Bitrix\Main\UserTable::getList([
		"select" => ["*"],
		"filter" => ["@ID" => array_keys($users)]
	]);
	while ($res = $userRes->fetch())
	{
		$users[$res["ID"]] = $res;
	}
}

foreach ($records as $key => $res)
{
	$row =& $lAdmin->AddRow($res["USER_ID"], $res);
	$user = $users[$res["USER_ID"]];
	$row->AddViewField("USER_ID", "<a href=\"user_edit.php?lang={LANGUAGE_ID}&ID={$res["USER_ID"]}\" >{$res["USER_ID"]}</a>");
	$row->AddViewField("EMAIL", TxtToHtml($user["EMAIL"]));
	$row->AddViewField("LOGIN", TxtToHtml($user["LOGIN"]));
	$row->AddViewField("NAME", TxtToHtml($user["NAME"]));
	$row->AddViewField("LAST_NAME", TxtToHtml($user["LAST_NAME"]));
	$row->AddViewField("CNT", $res["CNT"] <= 0 ? GetMessage("FM_NO") : $res["CNT"]);
	$row->AddActions([
		[
			"ICON" => "edit",
			"TEXT" => GetMessage("FM_ACT_EDIT"),
			"ACTION" => $lAdmin->ActionRedirect("forum_subscribe_edit.php?lang=" . LANG . "&USER_ID={$res["USER_ID"]}"),
			"DEFAULT" => true
		],
		[
			"ICON" => "delete",
			"TEXT" => GetMessage("FM_ACT_DELETE"),
			"ACTION" => "if(confirm('" . GetMessage("FM_ACT_DEL_CONFIRM") . "')) " . $lAdmin->ActionDoGroup($res["USER_ID"], "delete", "lang=" . LANG)
		]
	]);
}
/*******************************************************************/
	$lAdmin->AddGroupActionTable(
		array(
			"delete" => GetMessage("FM_ACT_DELETE")
			)
	);

/*******************************************************************/
	$lAdmin->CheckListMode();
/*******************************************************************/
	$APPLICATION->SetTitle(GetMessage("FM_TITLE"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	$lAdmin->DisplayFilter($filterFields);
	$lAdmin->DisplayList(["SHOW_COUNT_HTML" => true]);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
