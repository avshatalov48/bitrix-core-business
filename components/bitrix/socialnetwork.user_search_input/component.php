<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!function_exists("GetTagsIdTmp"))
{
	function GetTagsIdTmp($sName)
	{
		static $arPostfix = array();
		$sPostfix = rand();
		while (in_array($sPostfix, $arPostfix))
		{
			$sPostfix = rand();
		}
		array_push($arPostfix, $sPostfix);
		$sId = preg_replace("/\W/", "_", $sName);
		$sId = $sId.$sPostfix;
		return $sId;
	}
}

if (empty($arParams["NAME"]))
{
	// counter of this component inclusions on a page
	$GLOBALS["usi_counter"]++;
	$arParams["NAME"] = "TAGS";
	if ($GLOBALS["usi_counter"] > 1)
	{
		$arResult["NAME"] .= "_".$GLOBALS["usi_counter"];
		$arResult["~NAME"] .= "_".$GLOBALS["usi_counter"];
	}	
}

$arResult["ID"] = GetTagsIdTmp($arParams["NAME"]);
$arResult["NAME"] = htmlspecialcharsbx(CUtil::addslashes($arParams["NAME"]));
$arResult["~NAME"] = $arParams["NAME"];
$arResult["FUNCTION"] = htmlspecialcharsbx(CUtil::addslashes($arParams["FUNCTION"]));

preg_match('/^(\d+)$/', $arParams["VALUE"], $matches);
if (count($matches) <= 0)
{
	$arResult["VALUE"] = $arParams["VALUE"];
	$arResult["~VALUE"] = $arResult["VALUE"];
}
else
{
	// create username using Format from user_id

	$rsUser = CUser::GetByID($arParams["VALUE"]);
	if ($arUser = $rsUser->GetNext())
	{
		$arResult["VALUE"] = CUser::FormatName($arParams["NAME_TEMPLATE"]." [#ID#]", $arUser, ($arParams["SHOW_LOGIN"] != "N"));
		$arResult["~VALUE"] = $arResult["VALUE"];
	}
}


$arResult["GROUP_ID"] = intval($arParams["GROUP_ID"]);
$arResult["~GROUP_ID"] = $arParams["GROUP_ID"];

if ($arParams["NAME_TEMPLATE"] == '')
	$arParams["NAME_TEMPLATE"] = CSite::GetNameFormat();
						
if (trim($arParams["SHOW_LOGIN"]) != "N")
	$arParams['SHOW_LOGIN'] = "Y";

$this->IncludeComponentTemplate();
?>