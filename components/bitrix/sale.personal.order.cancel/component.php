<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponent $this */

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

global $APPLICATION, $USER;

if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"), false, false, 'N', false);
	return;
}

$id = urldecode(urldecode((string)($arParams["ID"] ?? '')));

$arParams["PATH_TO_LIST"] = trim((string)($arParams["PATH_TO_LIST"] ?? ''));
if ($arParams["PATH_TO_LIST"] === '')
{
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage());
}

$arParams["PATH_TO_DETAIL"] = trim((string)($arParams["PATH_TO_DETAIL"] ?? ''));
if ($arParams["PATH_TO_DETAIL"] === '')
{
	$arParams["PATH_TO_DETAIL"] = htmlspecialcharsbx($APPLICATION->GetCurPage() . "?" . "ID=#ID#");
}
$arParams['SET_TITLE'] = (string)($arParams['SET_TITLE'] ?? 'Y');

if ($id == '' && $arParams["PATH_TO_LIST"] != htmlspecialcharsbx($APPLICATION->GetCurPage()))
{
	LocalRedirect($arParams["PATH_TO_LIST"]);
}

$arResult['ERROR_MESSAGE'] = '';
if ($id == '')
{
	$arResult["URL_TO_LIST"] = $arParams['PATH_TO_LIST'];
	$arResult["ERROR_MESSAGE"] = GetMessage("SPOC_EMPTY_ORDER_ID");
	$this->IncludeComponentTemplate();
	return;
}

if ($arParams['SET_TITLE'] === 'Y')
{
	$APPLICATION->SetTitle(GetMessage(
		'SPOC_TITLE',
		[
			'#ID#' => $id,
		]
	));
}

$bUseAccountNumber = \Bitrix\Sale\Integration\Numerator\NumeratorOrder::isUsedNumeratorForOrder();

$errors = array();

$registry = \Bitrix\Sale\Registry::getInstance(\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER);
/** @var \Bitrix\Sale\Order $orderClass */
$orderClass = $registry->getOrderClassName();

$order = null;
if ($bUseAccountNumber)
{
	$order = $orderClass::loadByAccountNumber($id);
}

if (!$order)
{
	$order = $orderClass::load($id);
}

if (!$order || $order->getField('USER_ID') !== $USER->GetID())
{
	$arResult["ERROR_MESSAGE"] = str_replace("#ID#", $id, GetMessage("SPOC_NO_ORDER"));
}
elseif ($order->isCanceled())
{
	$arResult["ERROR_MESSAGE"] = GetMessage("SPOC_ORDER_CANCELED", ['#ACCOUNT_NUMBER#' => htmlspecialcharsbx($id)]);
}
else
{
	$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
	if ($request->get("CANCEL") === "Y" && $request->isPost() && (string)$request->get("action") !== '' && check_bitrix_sessid())
	{
		if ($order->isPaid() || $order->isShipped())
		{
			$arResult["ERROR_MESSAGE"] = GetMessage("SPOC_CANCEL_ORDER");
		}
		else
		{
			$oldOrderObject = new CSaleOrder();
			$oldOrderObject->CancelOrder($order->getId(), "Y", $_REQUEST["REASON_CANCELED"]);
			if ($ex = $APPLICATION->GetException())
			{
				$errors[] = $ex->GetString();
			}
			else
			{
				LocalRedirect($arParams["PATH_TO_LIST"]);
			}
		}
	}
	else
	{
		$arResult['ID'] = $id;
		$arResult['ACCOUNT_NUMBER'] = $order->getField('ACCOUNT_NUMBER');
		$arResult['URL_TO_DETAIL'] = CComponentEngine::MakePathFromTemplate(
			$arParams["PATH_TO_DETAIL"],
			[
				"ID" => urlencode(urlencode($order->getField('ACCOUNT_NUMBER'))),
			]
		);
		$arResult['URL_TO_LIST'] = $arParams["PATH_TO_LIST"];
	}
}

if (!empty($errors) && is_array($errors))
{
	foreach ($errors as $errorMessage)
	{
		$arResult["ERROR_MESSAGE"] .= $errorMessage.".";
	}
}

$this->IncludeComponentTemplate();
