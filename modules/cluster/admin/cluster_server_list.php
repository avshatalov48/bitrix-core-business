<?
define("ADMIN_MODULE_NAME", "cluster");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/prolog.php");
IncludeModuleLangFile(__FILE__);

if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$sTableID = "tbl_cluster_server_list";
$lAdmin = new CAdminList($sTableID);

$arHeaders = array(
	array(
		"id" => "NN",
		"content" => GetMessage("CLU_SL_NN"),
		"align" => "right",
		"default" => true,
	),
	array(
		"id" => "HOST",
		"content" => GetMessage("CLU_SL_HOST"),
		"default" => true,
	),
	array(
		"id" => "SERVER_TYPE",
		"content" => GetMessage("CLU_SL_SERVER_TYPE"),
		"default" => true,
	),
);
$lAdmin->AddHeaders($arHeaders);
$i = 1;
$arData = array();
foreach (CCluster::getServerList() as $server)
{
	if ($server["DEDICATED"] == "Y")
		$host = $server["HOST"];
	else
		$host = "";
	if (isset($arData[$host]))
		$arData[$host]["SERVERS"][] = $server;
	else
		$arData[$host] = array(
			"NN" => $i++,
			"HOST" => $host,
			"SERVERS" => array($server),
		);
}
$rsData = new CDBResult;
$rsData->InitFromArray($arData);
$rsData = new CAdminResult($rsData, $sTableID);
$i = 1;
while ($arRes = $rsData->Fetch())
{
	$row = $lAdmin->AddRow($i++, $arRes);
	if ($arRes["HOST"] == "")
		$row->AddViewField("HOST", GetMessage("CLU_SL_COMMON_TYPE"));
	$servers = array();
	foreach ($arRes["SERVERS"] as $server)
	{
		if ($server["SERVER_TYPE"] == "database")
			$type = GetMessage("CLU_SL_SERVER_TYPE_DATABASE");
		elseif ($server["SERVER_TYPE"] == "memcache")
			$type = GetMessage("CLU_SL_SERVER_TYPE_MEMCACHE");
		elseif ($server["SERVER_TYPE"] == "web")
			$type = GetMessage("CLU_SL_SERVER_TYPE_WEB");
		else
			$type = GetMessage("CLU_SL_SERVER_TYPE_UNKNOWN");

		$extendedInfo = array();
		if ($server["GROUP_ID"] > 0)
			$extendedInfo[] = GetMessage("CLU_SL_GROUP").": ".$server["GROUP_ID"];

		if ($server["ROLE_ID"] == "MAIN")
			$role = GetMessage("CLU_SL_ROLE_MAIN");
		elseif ($server["ROLE_ID"] == "SLAVE")
			$role = GetMessage("CLU_SL_ROLE_SLAVE");
		elseif ($server["ROLE_ID"] == "MODULE")
			$role = GetMessage("CLU_SL_ROLE_MODULE");
		else
			$role = "";

		if ($role != "")
			$extendedInfo[] = GetMessage("CLU_SL_ROLE").": ".$role;

		if (!empty($extendedInfo))
			$type .= " (".implode(", ", $extendedInfo).")";

		$servers[] = '<a href="'.htmlspecialcharsBx($server["EDIT_URL"]).'">'.htmlspecialcharsEx($type).'</a>';
	}
	$row->AddViewField("SERVER_TYPE", implode("<br>", $servers));
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(GetMessage("CLU_SL_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if($message)
	echo $message->Show();
$lAdmin->DisplayList();
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>