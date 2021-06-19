<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arSites = array(
	"" =>  GetMessage("GD_STAT_P_SITE_ID_ALL")
);
$dbSite = CSite::GetList("sort", "desc", Array("ACTIVE" => "Y"));
while($arSite = $dbSite->GetNext())
	$arSites[$arSite["LID"]] = "[".$arSite["LID"]."] ".$arSite["NAME"];

$arGraphParams = array(
	"HIT" => GetMessage("GD_STAT_P_HIT"),
	"HOST" => GetMessage("GD_STAT_P_HOST"),
	"SESSION" => GetMessage("GD_STAT_P_SESSION"),
	"EVENT" => GetMessage("GD_STAT_P_EVENT"),
	"GUEST" => GetMessage("GD_STAT_P_GUEST")
);

$arParameters = Array(
	"PARAMETERS"=> Array(),
	"USER_PARAMETERS"=> Array(
		"SITE_ID" => Array(
			"NAME" => GetMessage("GD_STAT_P_SITE_ID"),
			"TYPE" => "LIST",
			"VALUES" => $arSites,
			"MULTIPLE" => "N",
			"DEFAULT" => ""
		),
		"HIDE_GRAPH" => Array(
			"NAME" => GetMessage("GD_STAT_P_HIDE_GRAPH"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"
		),
	)
);

if (
	!is_array($arAllCurrentValues)
	|| !array_key_exists("HIDE_GRAPH", $arAllCurrentValues)
	|| $arAllCurrentValues["HIDE_GRAPH"]["VALUE"] != "Y"
)
{
	$arParameters["USER_PARAMETERS"]["GRAPH_PARAMS"]	= array(
		"NAME" => GetMessage("GD_STAT_P_GRAPH_PARAMS"),
		"TYPE" => "LIST",
		"VALUES" => $arGraphParams,
		"MULTIPLE" => "Y",
		"DEFAULT" => array("HOST", "SESSION", "EVENT", "GUEST")
	);

	$arParameters["USER_PARAMETERS"]["GRAPH_DAYS"]	= array(
		"NAME" => GetMessage("GD_STAT_P_GRAPH_DAYS"),
		"TYPE" => "STRING",
		"DEFAULT" => "30"
	);

	$arParameters["USER_PARAMETERS"]["GRAPH_WIDTH"]	= array(
		"NAME" => GetMessage("GD_STAT_P_GRAPH_WIDTH"),
		"TYPE" => "STRING",
		"DEFAULT" => "400"
	);

	$arParameters["USER_PARAMETERS"]["GRAPH_HEIGHT"]	= array(
		"NAME" => GetMessage("GD_STAT_P_GRAPH_HEIGHT"),
		"TYPE" => "STRING",
		"DEFAULT" => "300"
	);
}
?>