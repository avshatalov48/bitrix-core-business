<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

$arParams["AJAX_CALL"] = $arParams["AJAX_CALL"] == "Y" ? "Y" : "N";
$arParams["STEP"] = intval($arParams["STEP"]);

if(isset($arParams["DELIVERY"]) && isset($arParams["PROFILE"]) && !isset($arParams["DELIVERY_ID"]))
	$arParams["DELIVERY_ID"] = \CSaleDelivery::getIdByCode($arParams["DELIVERY"].":".$arParams["PROFILE"]);

$arParams['NO_AJAX'] = $arParams['NO_AJAX'] == 'Y' ? 'Y' : 'N';
if ($arParams['NO_AJAX'] == 'Y')
{
	$arParams['AJAX_CALL'] = 'Y';
	$arParams['STEP'] = 1;
}

if(!isset($arParams["ORDER_DATA"]))
	$arParams["ORDER_DATA"] = array();

if(!isset($arParams["EXTRA_PARAMS"]))
	$arParams["EXTRA_PARAMS"] = array();

$arParams["LOCATION_TO"] = intval($arParams["LOCATION_TO"]);
$arParams["LOCATION_FROM"] = intval($arParams["LOCATION_FROM"]);
if ($arParams["LOCATION_FROM"] <= 0)
{
	$arParams["LOCATION_FROM"] = COption::GetOptionString('sale', 'location');
}

$arParams["STEP"] = intval($arParams["STEP"]);
if ($arParams["STEP"] <= 0) $arParams["AJAX_CALL"] = 'N';

if (is_set($arParams["START_VALUE"])) $arParams["START_VALUE"] = doubleval($arParams["START_VALUE"]);

if ($arParams["AJAX_CALL"] == "Y")
{
	$shipment = CSaleDelivery::convertOrderOldToNew(array(
		"WEIGHT" => $arParams["ORDER_WEIGHT"],
		"PRICE" =>  $arParams["ORDER_PRICE"],
		"LOCATION_TO" => $arParams["LOCATION_TO"],
		"LOCATION_ZIP" => $arParams['LOCATION_ZIP'],
		"ITEMS" =>  $arParams["ITEMS"],
		"CURRENCY" => $arParams["CURRENCY"]
	));

	/** @var \Bitrix\Sale\Delivery\Services\Base  $deliveryObj */
	$deliveryObj = \Bitrix\Sale\Delivery\Services\Manager::getObjectById($arParams["DELIVERY_ID"]);

	if(!$deliveryObj)
	{
		ShowError(GetMessage("SALE_DELIVERY_HANDLER_NOT_INSTALL"));
		return;
	}

	$calcResult = $deliveryObj->calculate($shipment);

	$result = array(
		"VALUE" => $calcResult->getPrice(),
		"TRANSIT" => $calcResult->getPeriodDescription(),
		"RESULT" => $calcResult->isSuccess() ? "OK" : "ERROR",
	);

	if (!empty($arParams["ORDER_DATA"]) && is_array($arParams["ORDER_DATA"]))
	{
		$orderDeliveryPriceData = $arParams["ORDER_DATA"];
		$orderDeliveryPriceData['BASKET_ITEMS'] = (!empty($arParams['ITEMS']) && is_array($arParams['ITEMS'])? $arParams['ITEMS'] : array());
		$orderDeliveryPriceData['PRICE_DELIVERY'] = $orderDeliveryPriceData['DELIVERY_PRICE'] = $calcResult->getPrice();
		$orderDeliveryPriceData['DELIVERY_ID'] = $arParams["DELIVERY_ID"];

		CSaleDiscount::DoProcessOrder($orderDeliveryPriceData, array(), $arErrors);

		if (floatval($orderDeliveryPriceData['DELIVERY_PRICE']) >= 0 && $orderDeliveryPriceData['PRICE_DELIVERY'] != $calcResult->getPrice())
		{
			$result['DELIVERY_DISCOUNT_PRICE'] = $orderDeliveryPriceData['DELIVERY_PRICE'];
			$result["DELIVERY_DISCOUNT_PRICE_FORMATED"] = SaleFormatCurrency($orderDeliveryPriceData['DELIVERY_PRICE'], $arParams["CURRENCY"]);
		}

	}




	$result["TEXT"] = $calcResult->isSuccess() ? $calcResult->getDescription() : implode("<br>\n", $calcResult->getErrorMessages());

	if($calcResult->isNextStep())
		$result["RESULT"] = "NEXT_STEP";

	if($calcResult->isSuccess() && strlen($calcResult->getDescription()) > 0)
		$result["RESULT"] = "NOTE";

	if(intval($calcResult->getPacksCount()) > 0)
		$result["PACKS_COUNT"] = $calcResult->getPacksCount();

	if ($calcResult->isSuccess() && CModule::IncludeModule('currency'))
	{
		$result["VALUE_FORMATTED"] = CurrencyFormat($calcResult->getPrice(), $arParams["CURRENCY"]);
	}
	elseif ($calcResult->isNextStep()  && strlen($calcResult->getTmpData()) > 0)
	{
		$result["TEMP"] = CUtil::JSEscape($calcResult->getTmpData());
	}

	$arResult["RESULT"] = $result;
}
else
{
	$arParams["STEP"] = 0;

	$arResult["B_ADMIN"] = defined("ADMIN_SECTION") && ADMIN_SECTION===true ? "Y" : "N";

	if ($arResult["B_ADMIN"] != "Y")
	{
		$folderPath = "";
		IncludeAJAX();
		if($this->InitComponentTemplate())
		{
			$template = $this->GetTemplate();
			if($template)
			{
				$folderPath = $template->GetFolder();
			}
		}

		if(strlen($folderPath) <= 0)
		{
			$folderPath = $this->GetPath().'/templates/'.(strlen($componentTemplate) > 0 ? $componentTemplate : '.default');
		}

		$APPLICATION->AddHeadScript($folderPath.'/proceed.js');
	}
	elseif ($arParams["STEP"] == 0)
	{
		$arResult["PATH"] = $this->GetPath().'/templates/'.(strlen($componentTemplate) > 0 ? $componentTemplate : '.default').'/';
	}
}

$arTmpParams = array(
	"STEP" => intval($arParams["STEP"]) + 1,
	"DELIVERY_ID" => $arParams["DELIVERY_ID"],
	"DELIVERY" => $arParams["DELIVERY"],
	"PROFILE" => $arParams["PROFILE"],
	"WEIGHT" => doubleval($arParams["ORDER_WEIGHT"]),
	"PRICE" => doubleval($arParams["ORDER_PRICE"]),
	"LOCATION" => intval($arParams["LOCATION_TO"]),
	"LOCATION_ZIP" => $arParams['LOCATION_ZIP'],
	"CURRENCY" => $arParams["CURRENCY"],
	"INPUT_NAME" => $arParams["INPUT_NAME"],
	"TEMP" => $arParams["~TEMP"],
	"ITEMS" => $arParams["ITEMS"],
	"EXTRA_PARAMS_CALLBACK" => $arParams["EXTRA_PARAMS_CALLBACK"],
	"ORDER_DATA" => $arParams["ORDER_DATA"]
);

$arResult["JS_PARAMS"] = CUtil::PhpToJsObject($arTmpParams);

$this->IncludeComponentTemplate();
?>