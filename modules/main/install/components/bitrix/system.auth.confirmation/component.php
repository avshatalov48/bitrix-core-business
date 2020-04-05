<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/*
Authorization form (for prolog)
Params:
	REGISTER_URL => path to page with authorization script (component?)
	PROFILE_URL => path to page with profile component
*/

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * @var array $arResult
 */

$arParams["USER_ID"] = trim($arParams["USER_ID"]);
if(strlen($arParams["USER_ID"]) <= 0)
	$arParams["USER_ID"] = "confirm_user_id";

$arParams["CONFIRM_CODE"] = trim($arParams["CONFIRM_CODE"]);
if(strlen($arParams["CONFIRM_CODE"]) <= 0)
	$arParams["CONFIRM_CODE"] = "confirm_code";

$arParams["LOGIN"] = trim($arParams["LOGIN"]);
if(strlen($arParams["LOGIN"]) <= 0)
	$arParams["LOGIN"] = "login";

$arResult["~USER_ID"] = $_REQUEST[$arParams["USER_ID"]];
$arResult["USER_ID"] = intval($arResult["~USER_ID"]);

$arResult["~CONFIRM_CODE"] = trim($_REQUEST[$arParams["CONFIRM_CODE"]]);
$arResult["CONFIRM_CODE"] = htmlspecialcharsbx($arResult["~CONFIRM_CODE"]);

$arResult["~LOGIN"] = trim($_REQUEST[$arParams["LOGIN"]]);
$arResult["LOGIN"] = htmlspecialcharsbx($arResult["~LOGIN"]);

if($USER->IsAuthorized())
{
	$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E02");
	$arResult["MESSAGE_CODE"] = "E02";
	$arResult["SHOW_FORM"] = false;
}
else
{
	if($arResult["USER_ID"] <= 0 && strlen($arResult["~LOGIN"]) > 0)
	{
		$rsUser = CUser::GetByLogin($arResult["~LOGIN"]);
	}
	else
	{
		$rsUser = CUser::GetByID($arResult["USER_ID"]);
	}

	if($arResult["USER"] = $rsUser->GetNext())
	{
		if($arResult["USER"]["ACTIVE"] === "Y")
		{
			$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E03");
			$arResult["MESSAGE_CODE"] = "E03";
			$arResult["SHOW_FORM"] = false;
		}
		else
		{
			if(strlen($arResult["CONFIRM_CODE"]) <= 0)
			{
				$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E04");
				$arResult["MESSAGE_CODE"] = "E04";
				$arResult["SHOW_FORM"] = true;
			}
			elseif($arResult["~CONFIRM_CODE"] !== $arResult["USER"]["~CONFIRM_CODE"])
			{
				$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E05");
				$arResult["MESSAGE_CODE"] = "E05";
				$arResult["SHOW_FORM"] = true;
			}
			else
			{
				$obUser = new CUser;
				$obUser->Update($arResult["USER"]["ID"], array("ACTIVE" => "Y", "CONFIRM_CODE" => ""));
				$rsUser = CUser::GetByID($arResult["USER"]["ID"]);
				$arResult["USER_ACTIVE"] = $rsUser->GetNext();
				if($arResult["USER_ACTIVE"] && $arResult["USER_ACTIVE"]["ACTIVE"] === "Y")
				{
					$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E06");
					$arResult["MESSAGE_CODE"] = "E06";
					$arResult["SHOW_FORM"] = false;
				}
				else
				{
					$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E07");
					$arResult["MESSAGE_CODE"] = "E07";
					$arResult["SHOW_FORM"] = true;
				}
			}
		}
	}
	else
	{
		$arResult["MESSAGE_TEXT"] = GetMessage("CC_BSAC_MESSAGE_E01");
		$arResult["MESSAGE_CODE"] = "E01";
		$arResult["SHOW_FORM"] = true;
	}
}

$arResult["~FORM_ACTION"] = $APPLICATION->GetCurPageParam();
$arResult["FORM_ACTION"] = htmlspecialcharsbx($arResult["~FORM_ACTION"]);

//echo "<pre>",htmlspecialcharsbx(print_r($arResult, true)),"</pre>";
$this->IncludeComponentTemplate();
?>