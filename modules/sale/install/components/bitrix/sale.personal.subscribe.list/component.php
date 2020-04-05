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

$arParams["PATH_TO_CANCEL"] = Trim($arParams["PATH_TO_CANCEL"]);
if (strlen($arParams["PATH_TO_CANCEL"]) <= 0)
	$arParams["PATH_TO_CANCEL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?ID=#ID#");

$arParams["PER_PAGE"] = (intval($arParams["PER_PAGE"]) <= 0 ? 20 : intval($arParams["PER_PAGE"]));
	
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y" );
if($arParams["SET_TITLE"] == 'Y')
	$APPLICATION->SetTitle(GetMessage("SPSL_DEFAULT_TITLE"));

$by = (strlen($_REQUEST["by"])>0 ? $_REQUEST["by"]: "ID");
$order = (strlen($_REQUEST["order"])>0 ? $_REQUEST["order"]: "DESC");

$dbRecurring = CSaleRecurring::GetList(
		array($by => $order),
		array("USER_ID" => IntVal($USER->GetID())),
		false,
		false,
		array("ID", "USER_ID", "MODULE", "PRODUCT_ID", "PRODUCT_NAME", "PRODUCT_URL", "PRODUCT_PRICE_ID", "RECUR_SCHEME_TYPE", "RECUR_SCHEME_LENGTH", "WITHOUT_ORDER", "PRICE", "CURRENCY", "ORDER_ID", "CANCELED", "CALLBACK_FUNC", "DESCRIPTION", "TIMESTAMP_X", "PRIOR_DATE", "NEXT_DATE", "REMAINING_ATTEMPTS", "SUCCESS_PAYMENT")
	);
$dbRecurring->NavStart($arParams["PER_PAGE"]);
$arResult["NAV_STRING"] = $dbRecurring->GetPageNavString(GetMessage("SPSL_PAGES"));
$arResult["RECURRING"] = Array();
while($arRecurring = $dbRecurring->GetNext())
{
	$arResultTmp = Array();
	$arResultTmp = $arRecurring;
	if (array_key_exists($arRecurring["RECUR_SCHEME_TYPE"], $GLOBALS["SALE_TIME_PERIOD_TYPES"]))
		$arResultTmp["SALE_TIME_PERIOD_TYPES"] = $GLOBALS["SALE_TIME_PERIOD_TYPES"][$arRecurring["RECUR_SCHEME_TYPE"]];
	$arResultTmp["URL_TO_CANCEL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_CANCEL"], Array("ID" => $arRecurring["ID"]));
	$arResult["RECURRING"][] = $arResultTmp;
}

$this->IncludeComponentTemplate();
?>