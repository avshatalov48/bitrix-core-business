<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	return;

if (CModule::IncludeModule("sale"))
{
	if (strlen($arParams["SET_TITLE"]) <= 0) $arParams["SET_TITLE"] = "Y";
	
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("SPCAT1_TARIF_PLANS"));

	$affiliatePlanType = COption::GetOptionString("sale", "affiliate_plan_type", "N");
	$affiliateCurrency = CSaleLang::GetLangCurrency(SITE_ID);

	$arResult = array();

	$dbPlan = CSaleAffiliatePlan::GetList(
		array("NAME" => "ASC"),
		array("SITE_ID" => SITE_ID, "ACTIVE" => "Y"),
		false,
		false,
		array("ID", "NAME", "DESCRIPTION", "BASE_RATE", "BASE_RATE_TYPE", "BASE_RATE_CURRENCY", "MIN_PLAN_VALUE")
	);
	while ($arPlan = $dbPlan->Fetch())
	{
		$arPlan["BASE_RATE_FORMAT"] = (($arPlan["BASE_RATE_TYPE"] == "P") ? round($arPlan["BASE_RATE"], SALE_VALUE_PRECISION)."%" : SaleFormatCurrency($arPlan["BASE_RATE"], $arPlan["BASE_RATE_CURRENCY"]));
		$arPlan["MIN_PLAN_VALUE_FORMAT"] = (($affiliatePlanType == "N") ? str_replace("#NUM#", IntVal($arPlan["MIN_PLAN_VALUE"]), GetMessage("SPCAT1_LIMIT1")) : str_replace("#SUM#", SaleFormatCurrency($arPlan["MIN_PLAN_VALUE"], $affiliateCurrency), GetMessage("SPCAT1_LIMIT2")));
		$arPlan["DESCRIPTION"] = htmlspecialcharsex($arPlan["DESCRIPTION"]);
		$arPlan["NAME"] = htmlspecialcharsex($arPlan["NAME"]);
		$arResult[] = $arPlan;
	}

	$this->IncludeComponentTemplate();
}
else
{
	?>
	<b><?=ShowError(GetMessage("SPCAT1_NO_SHOP"))?></b>
	<?
}
?>