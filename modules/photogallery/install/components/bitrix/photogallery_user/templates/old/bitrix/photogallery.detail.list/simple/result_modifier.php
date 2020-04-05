<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	$arParams["SHOW_CONTROLS"] = ($arParams["SHOW_CONTROLS"] == "Y" ? "Y" : "N");
//	$arParams["SHOW_CONTROLS"] = (($arParams["SHOW_CONTROLS"] == "Y" && $arParams["PERMISSION"] >= "W") ? "Y" : "N");
	if ($arParams["SHOW_CONTROLS"] == "Y")
	{
		$DetailListViewMode = ($_REQUEST["view_mode"] == "edit" ? "edit" : "view");

		if ($GLOBALS["USER"]->IsAuthorized())
		{
			require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
			$DetailListViewMode = CUserOptions::GetOption("photogallery", "DetailListViewMode", "view");
			if (in_array($_REQUEST["view_mode"], array("view", "edit")) && $_REQUEST["view_mode"] != $DetailListViewMode)
			{
				$DetailListViewMode = $_REQUEST["view_mode"];
				CUserOptions::SetOption("photogallery", "DetailListViewMode", $DetailListViewMode);
			}
		}
	}
	$arParams["DetailListViewMode"] = $DetailListViewMode;

$arParams1 = array(
	"MAX_VOTE" => intval($arParams["MAX_VOTE"])<=0? 5: intval($arParams["MAX_VOTE"]),
	"VOTE_NAMES" => is_array($arParams["VOTE_NAMES"])? $arParams["VOTE_NAMES"]: array(),
	"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"]);
$arResult["VOTE_NAMES"] = array();
foreach($arParams1["VOTE_NAMES"] as $k=>$v)
{
	if(strlen($v)>0)
		$arResult["VOTE_NAMES"][]=htmlspecialcharsbx($v);
	if(count($arResult["VOTE_NAMES"])>=$arParams1["MAX_VOTE"])
		break;
}
?>