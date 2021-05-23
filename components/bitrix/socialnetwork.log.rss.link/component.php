<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

$arResult["EXTERNAL_HASH"] = "";

$arParams["ENTITY_TYPE"] = $arResult["ENTITY_TYPE"] = ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP ? SONET_ENTITY_GROUP : SONET_ENTITY_USER);

if (!array_key_exists("ENTITY_ID", $arParams) || intval($arParams["ENTITY_ID"]) <= 0)
{
	ShowError(GetMessage("SONET_LRL_ENTITY_ID_EMPTY"));
	return;
}
else
	$arResult["ENTITY_ID"] = $arParams["ENTITY_ID"];

if ($arParams["ENTITY_TYPE"] == SONET_ENTITY_GROUP)
{
	if (!CSocNetGroup::GetByID($arParams["ENTITY_ID"]))
	{
		ShowError(GetMessage("SONET_LRL_GROUP_ID_INCORRECT"));
		return;
	}
}	
elseif ($arParams["ENTITY_TYPE"] == SONET_ENTITY_USER)
{
	$rsUser = CUser::GetByID($arParams["ENTITY_ID"]);
	if (!$arUser = $rsUser->Fetch())
	{
		ShowError(GetMessage("SONET_LRL_USER_ID_INCORRECT"));
		return;
	}
}	

if (array_key_exists("EVENT_ID", $arParams))
{
	if (is_array($arParams["EVENT_ID"]))
		$arResult["EVENT_ID"] = implode("|", $arParams["EVENT_ID"]);
	else
		$arResult["EVENT_ID"] = $arParams["EVENT_ID"];
	
	if 	($arResult["EVENT_ID"] == "all")
		$arResult["EVENT_ID"] = false;
}

$arParams["PATH_TO_RSS"] = trim($arParams["PATH_TO_RSS"]);
						
if ($GLOBALS["USER"]->IsAuthorized())
{
	$arParams["PATH_TO_RSS_MASK"] = trim($arParams["PATH_TO_RSS_MASK"]);
	$arResult["PATH_TO_RSS_MASK"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS_MASK"], array(
		"group_id" => $arParams["ENTITY_ID"], 
		"user_id" => $arParams["ENTITY_ID"], 
	));
	$arResult["EXTERNAL_HASH"] = CSocNetLog::GetSign($arResult["PATH_TO_RSS_MASK"], $GLOBALS["USER"]->GetID(), SITE_ID);	
}

$arResult["PATH_TO_RSS"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RSS"], array(
							"group_id" => $arParams["ENTITY_ID"], 
							"user_id" => $arParams["ENTITY_ID"], 
							"sign" => $arResult["EXTERNAL_HASH"], 
							"events" => $arResult["EVENT_ID"]
						));

if($arParams["PATH_TO_USER"] == '')
	$arParams["PATH_TO_USER"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user&".$arParams["USER_VAR"]."=#user_id#");

$APPLICATION->AddHeadString('<link rel="alternate" type="application/rss+xml" title="RSS" href="'.$arResult["PATH_TO_RSS"].'" />');
		
$this->IncludeComponentTemplate();
?>