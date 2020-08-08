<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}
if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"));
}

$ID = 0;
if (isset($arParams['ID']))
	$ID = (int)$arParams['ID'];
if ($ID < 0)
	$ID = 0;

$arParams['PATH_TO_LIST'] = (isset($arParams['PATH_TO_LIST']) ? trim($arParams['PATH_TO_LIST']) : '');
if ($ID == 0 && $arParams['PATH_TO_LIST'] == '')
	return;
if ($arParams['PATH_TO_LIST'] == '')
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage());

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if($arParams["SET_TITLE"] == 'Y')
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("SPSC_TITLE")));

if ($ID > 0 && $_REQUEST["CANCEL_SUBSCRIBE"] == "Y" && check_bitrix_sessid())
{
	$dbRecurring = CSaleRecurring::GetList(
			array("ID" => "DESC"),
			array(
					"ID" => $ID,
					"USER_ID" => intval($USER->GetID())
				),
			false,
			false,
			array("ID")
		);
	if ($arRecurring = $dbRecurring->Fetch())
	{
		CSaleRecurring::CancelRecurring($arRecurring["ID"], "Y", $_REQUEST["REASON_CANCELED"]);
		LocalRedirect($arParams["PATH_TO_LIST"]);
	}
}

if ($ID <= 0)
	LocalRedirect($arParams["PATH_TO_LIST"]);

$dbRecurring = CSaleRecurring::GetList(
		array("ID" => "DESC"),
		array(
				"ID" => $ID,
				"USER_ID" => intval($GLOBALS["USER"]->GetID())
			),
		false,
		false,
		array("ID", "CANCELED", "PRODUCT_NAME")
	);
if ($arRecurring = $dbRecurring->GetNext())
{
	if ($arRecurring["CANCELED"] != "Y")
	{
		$arResult = Array(
				"ID" => $ID,
				"URL_TO_LIST" => $arParams["PATH_TO_LIST"],
				"CONFIRM" => str_replace("#NAME#", $arRecurring["PRODUCT_NAME"], str_replace("#ID#", $ID, GetMessage("STPSC_CONFIRM"))),
				"RECURRING" => $arRecurring
			);
	}
	else
		$arResult["ERROR_MESSAGE"] = GetMessage("STPSC_CANT_CANCEL");
}
else
	$arResult["ERROR_MESSAGE"] = str_replace("#ID#", $ID, GetMessage("SPOC_NO_ORDER"));

$this->IncludeComponentTemplate();
?>