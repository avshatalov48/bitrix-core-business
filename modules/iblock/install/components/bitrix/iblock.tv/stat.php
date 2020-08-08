<?
define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

if(
	isset($_SESSION["player_files"])
	&& is_array($_SESSION["player_files"])
	&& isset($_REQUEST["id"])
	&& isset($_SESSION["player_files"][$_REQUEST["id"]])
)
{
	$arFile = $_SESSION["player_files"][$_REQUEST["id"]];
	if(
		$arFile["STAT_EVENT"]
		&& !$arFile["WAS_STAT_EVENT"] //not yet for this session
		&& $arFile["STAT_EVENT1"] <> '' //event1 defined
		&& CModule::IncludeModule('statistic')
	)
	{
		CStatEvent::AddCurrent($arFile["STAT_EVENT1"], $arFile["STAT_EVENT2"], $arFile["STAT_EVENT3"]);
		$_SESSION["player_files"][$_REQUEST["id"]]["WAS_STAT_EVENT"] = true;
	}

	if(
		$arFile["SHOW_COUNTER_EVENT"]
		&& !$arFile["WAS_SHOW_COUNTER_EVENT"] //not yet for this session
		&& CModule::IncludeModule('iblock')
	)
	{
		CIBlockElement::CounterInc($_REQUEST["id"]);
		$_SESSION["player_files"][$_REQUEST["id"]]["WAS_SHOW_COUNTER_EVENT"] = true;
	}
}
CMain::FinalActions();
