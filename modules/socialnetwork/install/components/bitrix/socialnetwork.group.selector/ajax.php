<?
define("STOP_STATISTICS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
require_once("functions.php");

CModule::IncludeModule('socialnetwork');

if (!$USER->IsAuthorized())
	die();

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;

if (isset($_REQUEST["mode"]) && $_REQUEST["mode"] == "search")
{
	$APPLICATION->RestartBuffer();

	CSocNetTools::InitGlobalExtranetArrays($SITE_ID);

	$arFilter = array("SITE_ID" => $SITE_ID, "%NAME" => $_GET["query"]);
	if(!CSocNetUser::IsCurrentUserModuleAdmin($SITE_ID))
	{
		$arFilter["CHECK_PERMISSIONS"] = $USER->GetID();
	}

	$rsGroups = CSocNetGroup::GetList(array("NAME" => "ASC"), $arFilter);
	$arGroups = array();
	while($arGroup = $rsGroups->Fetch())
	{
		if (
			isset($GLOBALS["arExtranetGroupID"])
			&& is_array($GLOBALS["arExtranetGroupID"])
			&& in_array($arGroup["ID"], $GLOBALS["arExtranetGroupID"])
		)
		{
			$arGroup["IS_EXTRANET"] = "Y";
		}

		$arGroups[] = group2JSItem($arGroup);
	}

	if (
		isset($_REQUEST["features_perms"])
		&& sizeof($_REQUEST["features_perms"]) == 2
	)
	{
		filterByFeaturePerms($arGroups, $_REQUEST["features_perms"]);
	}

	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject($arGroups);
	die();
}
?>