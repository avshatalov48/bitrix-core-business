<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParams["SHOW_STRINGS"] = (isset($arParams["SHOW_STRINGS"]) && intval($arParams["SHOW_STRINGS"]) > 0 ? $arParams["SHOW_STRINGS"] : 2);
$arResult["SHOW_FILTER"] = array();
if ($GLOBALS["USER"]->IsAuthorized())
{
	$res = CUserOptions::GetOption("forum", "Filter", "");
	$res = (CheckSerializedData($res) ? @unserialize($res, ["allowed_classes" => false]) : array());

	if (is_array($res))
		$arResult["SHOW_FILTER"] = $res;
}
else
{
	if (isset($_SESSION["FORUM"]["SHOW_FILTER"]) && is_array($_SESSION["FORUM"]["SHOW_FILTER"]))
		$arResult["SHOW_FILTER"] = $_SESSION["FORUM"]["SHOW_FILTER"];
}

	$arResult["HEADER"] = array(
		"TITLE"	=> isset($arParams["HEADER"]) && isset($arParams["HEADER"]["TITLE"]) ? htmlspecialcharsbx($arParams["HEADER"]["TITLE"]) : null,
		"DESCRIPTION" => isset($arParams["HEADER"]) && isset($arParams["HEADER"]["DESCRIPTION"]) ? htmlspecialcharsbx($arParams["HEADER"]["DESCRIPTION"]) : null
	);
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
			"NAME_TO" => isset($res["NAME_TO"]) ? htmlspecialcharsbx($res["NAME_TO"]."") : null,
			"VALUE" => isset($res["VALUE"]) ? htmlspecialcharsbx(is_array($res["VALUE"]) ? (isset($res["VALUE"][0]) ? $res["VALUE"][0] : null) : $res["VALUE"]."") : null,
			"VALUE_TO" => isset($res["VALUE_TO"]) ? htmlspecialcharsbx($res["VALUE_TO"]."") : null,
			"TYPE" => (in_array($res["TYPE"], array("TEXT", "HIDDEN", "DATE", "SELECT", "PERIOD", "CHECKBOX")) ? $res["TYPE"] : "TEXT"),
			"MULTIPLE" => (isset($res["MULTIPLE"]) && $res["MULTIPLE"] == "Y" ? "Y" : "N"),
			"ACTIVE" => isset($res["ACTIVE"]) ? $res["ACTIVE"] : null,
			"CLASS" => isset($res["CLASS"]) ? $res["CLASS"] : null,
			"TITLE" => isset($res["TITLE"]) ? htmlspecialcharsbx($res["TITLE"]) : null,
			"LABEL" => isset($res["LABEL"]) ? htmlspecialcharsbx($res["LABEL"]) : null
		);

		if ($result["TYPE"] == "SELECT" )
		{
			$res1 = array();

			if (is_array($res["VALUE"]))
			{
				foreach ($res["VALUE"] as $key => $val)
				{
					$val = (is_array($val) ? $val : array("NAME" => $val));
					$val["TYPE"] = (isset($val["TYPE"]) && $val["TYPE"] == "OPTGROUP" ? "OPTGROUP" : "OPTION");
					$val["NAME"] = isset($val["NAME"]) ? htmlspecialcharsbx($val["NAME"]."") : null;
					$val["CLASS"] = isset($val["CLASS"]) ? htmlspecialcharsbx($val["CLASS"]."") : null;
					$res1[htmlspecialcharsbx($key."")] = $val;
				}
			}
			$result["VALUE"] = $res1;
		}
		$arResult["FIELDS"][] = $result;
	}

	if (isset($arParams["BUTTONS"]) && is_array($arParams["BUTTONS"]))
	{
		foreach ($arParams["BUTTONS"] as $res)
		{
			$res = array(
				"NAME" => isset($res["NAME"]) ? htmlspecialcharsbx($res["NAME"]."") : null,
				"TITLE" => isset($res["TITLE"]) ? htmlspecialcharsbx($res["TITLE"]."") : null,
				"VALUE" => isset($res["VALUE"]) ? htmlspecialcharsbx($res["VALUE"]."") : null);
			$arResult["BUTTONS"][] = $res;
		}
	}
?>
