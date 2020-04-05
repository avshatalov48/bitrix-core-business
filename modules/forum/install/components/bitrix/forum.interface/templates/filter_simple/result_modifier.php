<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParams["SHOW_STRINGS"] = (intVal($arParams["SHOW_STRINGS"]) > 0 ? $arParams["SHOW_STRINGS"] : 2);
$arResult["SHOW_FILTER"] = array();
if ($GLOBALS["USER"]->IsAuthorized())
{
	require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
	$res = CUserOptions::GetOption("forum", "Filter", "");
	$res = (CheckSerializedData($res) ? @unserialize($res) : array());

	if (is_array($res))
		$arResult["SHOW_FILTER"] = $res;
}
else 
{
	if (is_array($_SESSION["FORUM"]["SHOW_FILTER"]))
		$arResult["SHOW_FILTER"] = $_SESSION["FORUM"]["SHOW_FILTER"];
}

	$arResult["HEADER"] = array(
		"TITLE"	=> htmlspecialcharsbx($arParams["HEADER"]["TITLE"]),
		"DESCRIPTION" => htmlspecialcharsbx($arParams["HEADER"]["DESCRIPTION"]));
	$arResult["FIELDS"] = array();
	$arResult["BUTTONS"] = array();
	
	if (!is_array($arParams))
		$arParams = array();
	
	if (!is_array($arParams["FIELDS"]))
		$arParams["FIELDS"] = array();
		
	foreach ($arParams["FIELDS"] as $res)
	{
		$result = array(
			"NAME" => htmlspecialcharsbx($res["NAME"].""),
			"NAME_TO" => htmlspecialcharsbx($res["NAME_TO"].""),
			"VALUE" => htmlspecialcharsbx($res["VALUE"].""),
			"VALUE_TO" => htmlspecialcharsbx($res["VALUE_TO"].""),
			"TYPE" => (in_array($res["TYPE"], array("TEXT", "HIDDEN", "DATE", "SELECT", "PERIOD", "CHECKBOX")) ? $res["TYPE"] : "TEXT"),
			"MULTIPLE" => ($res["MULTIPLE"] == "Y" ? "Y" : "N"), 
			"ACTIVE" => $res["ACTIVE"],
			"CLASS" => $res["CLASS"],
			"TITLE" => htmlspecialcharsbx($res["TITLE"]),
			"LABEL" => htmlspecialcharsbx($res["LABEL"]));

		if ($result["TYPE"] == "SELECT" )
		{
			$res1 = array();
			
			if (is_array($res["VALUE"]))
			{
				foreach ($res["VALUE"] as $key => $val)
				{
					$val = (is_array($val) ? $val : array("NAME" => $val));
					$val["TYPE"] = ($val["TYPE"] == "OPTGROUP" ? "OPTGROUP" : "OPTION");
					$val["NAME"] = htmlspecialcharsbx($val["NAME"]."");
					$val["CLASS"] = htmlspecialcharsbx($val["CLASS"]."");
					$res1[htmlspecialcharsbx($key."")] = $val;
				}
			}
			$result["VALUE"] = $res1;
		}
		$arResult["FIELDS"][] = $result;
	}
	
	if (is_array($arParams["BUTTONS"]))
	{
		foreach ($arParams["BUTTONS"] as $res)
		{
			$res = array(
				"NAME" => htmlspecialcharsbx($res["NAME"].""),
				"TITLE" => htmlspecialcharsbx($res["TITLE"].""),
				"VALUE" => htmlspecialcharsbx($res["VALUE"].""));
			$arResult["BUTTONS"][] = $res;
		}
	}
?>