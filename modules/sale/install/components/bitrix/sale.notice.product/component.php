<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
//CUtil::InitJSCore(array('popup'));

/**
 * @deprecated deprecated since sale 16.5.0
 * @see catalog.product.subscribe
 *
 * Attention! Strongly recommended use catalog.product.subscribe.
 */

global $USER;

$arParams["NOTIFY_ID"] = intval($arParams["NOTIFY_ID"]);
$arParams["NOTIFY_PRODUCT_ID"] = trim($arParams["NOTIFY_PRODUCT_ID"]);
$arParams["NOTIFY_ACTION"]=trim($arParams["NOTIFY_ACTION"]);
if($arParams["NOTIFY_ACTION"] == '')
	$arParams["NOTIFY_ACTION"] = "action";
$arParams["NOTIFY_USE_CAPTHA"] = $arParams["NOTIFY_USE_CAPTHA"] == "Y" ? "Y" : "N";

$arResult = array("STATUS" => "N", "NOTIFY_URL" => "", "ERRORS" => "");

$notifyOption = COption::GetOptionString("sale", "subscribe_prod", "");
$arNotify = Array();
if($notifyOption <> '')
	$arNotify = unserialize($notifyOption, ['allowed_classes' => false]);

if (CModule::IncludeModule('sale') && CModule::IncludeModule('catalog') && $arParams["NOTIFY_ID"] > 0 && !empty($arNotify) && $arNotify[SITE_ID]['use'] == 'Y')
{
	if ($USER->IsAuthorized() && !isset($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]))
	{
		$_SESSION["NOTIFY_PRODUCT"][$USER->GetID()] = array();
		if ($USER->IsAuthorized())
		{
			$dbNotifyList = CSaleBasket::GetList(
				array(),
				array(
					"FUSER_ID" => CSaleBasket::GetBasketUserID(),
					"ORDER_ID" => "NULL",
					"SUBSCRIBE" => "Y",
					"CAN_BUY" => "N"
					),
				false,
				false,
				array('PRODUCT_ID', 'TYPE', 'SET_PARENT_ID')
			);
			while ($arNotifyList = $dbNotifyList->Fetch())
			{
				if (CSaleBasketHelper::isSetItem($arNotifyList))
					continue;

				$_SESSION["NOTIFY_PRODUCT"][$USER->GetID()][] = $arNotifyList["PRODUCT_ID"];
			}
		}
	}

	if ($USER->IsAuthorized() && isset($_GET[$arParams["NOTIFY_ACTION"]]) && $_GET[$arParams["NOTIFY_ACTION"]] == "SUBSCRIBE_PRODUCT")
	{
		$_SESSION["NOTIFY_PRODUCT"][$USER->GetID()][$arParams["NOTIFY_ID"]] = $arParams["NOTIFY_ID"];
	}

	if ($USER->IsAuthorized())
	{
		if (is_array($_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]) && in_array($arParams["NOTIFY_ID"], $_SESSION["NOTIFY_PRODUCT"][$USER->GetID()]))
			$arResult["STATUS"] = "Y";
		else
		{
			$arResult["STATUS"] = "N";
			//$arResult["NOTIFY_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam($arParams["NOTIFY_ACTION"]."=SUBSCRIBE_PRODUCT&".$arParams["NOTIFY_PRODUCT_ID"]."=".$arParams["NOTIFY_ID"], array($arParams["NOTIFY_PRODUCT_ID"], $arParams["NOTIFY_ACTION"])));
			$arResult["NOTIFY_URL"] = htmlspecialcharsback($arParams['NOTIFY_URL']);
		}
	}
	else
	{
		$arResult["STATUS"] = "R";
		$arResult["NOTIFY_URL"] = htmlspecialcharsback($arParams['NOTIFY_URL']);

		if ($arParams["NOTIFY_USE_CAPTHA"] == "Y")
			$_SESSION["NOTIFY_PRODUCT"]["CAPTHA"] = "Y";
		else
			$_SESSION["NOTIFY_PRODUCT"]["CAPTHA"] = "N";
	}

	if($arParams["NOTIFY_USE_CAPTHA"] == "Y")
		$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
	else
		$arResult["CAPTCHA_CODE"] = false;

	$this->IncludeComponentTemplate();
}//end sale

?>