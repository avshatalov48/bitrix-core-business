<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	return;

$this->setFramemode(false);

$arParams["REGISTER_PAGE"] = Trim($arParams["REGISTER_PAGE"]);
if ($arParams["REGISTER_PAGE"] == '')
	$arParams["REGISTER_PAGE"] = "register.php";

if (CModule::IncludeModule("sale"))
{
	if (empty($arParams["SET_TITLE"])) $arParams["SET_TITLE"] = "Y";
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("SPCA_AFFILIATE_ACCOUNT"));

	$arTransactTypes = array(
		"AFFILIATE_IN" => GetMessage("SPCA_AFFILIATE_PAY"),
		"AFFILIATE_ACCT" => GetMessage("SPCA_AFFILIATE_TRANSF"),
		"AFFILIATE_CLEAR" => GetMessage("SPCA_AFFILIATE_CLEAR"),
	);

	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			array("TRANSACT_DATE" => "ASC"),
			array(
				"USER_ID" => intval($GLOBALS["USER"]->GetID()),
				"SITE_ID" => SITE_ID,
			),
			false,
			false,
			array("ID", "PLAN_ID", "ACTIVE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "LAST_CALCULATE")
		);
		if ($arAffiliate = $dbAffiliate->GetNext())
		{
			$arResult = array();

			if ($arAffiliate["ACTIVE"] == "Y")
			{
				$arResult["FIELDS"] = $arAffiliate;

				$affiliateCurrency = CSaleLang::GetLangCurrency(SITE_ID);

				if ($_REQUEST["del_filter"] <> '')
					DelFilter(Array("filter_date_from", "filter_date_to"));
				else
					InitFilter(Array("filter_date_from", "filter_date_to"));

				$filter_date_from = $_REQUEST["filter_date_from"];
				$filter_date_to = $_REQUEST["filter_date_to"];
				if ($filter_date_from == '' && $filter_date_to == '')
				{
					$filter_date_from = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("m") - 3, 1, date("Y")));
					$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
				}

				$arResult["CURRENT_PAGE_PARAM"] = $APPLICATION->GetCurPageParam("", array("filter_date_from", "filter_date_to"));
				$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPage();

				$arResult["CURRENT_DATE"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), time());

				$arResult["PAID_SUM_INCOME"] = (($arAffiliate["PAID_SUM"] >= 0) ? SaleFormatCurrency($arAffiliate["PAID_SUM"], $affiliateCurrency) : "");
				$arResult["PAID_SUM_OUTCOME"] = (($arAffiliate["PAID_SUM"] < 0) ? SaleFormatCurrency(-$arAffiliate["PAID_SUM"], $affiliateCurrency) : "");

				$arResult["FILTER"] = array(
					"filter_date_from" => $filter_date_from,
					"filter_date_to" => $filter_date_to
				);

				$arFilter = array(
					"AFFILIATE_ID" => $arAffiliate["ID"]
				);
				if ($filter_date_from <> '')
					$arFilter[">=TRANSACT_DATE"] = Trim($filter_date_from);
				if ($filter_date_to <> '')
					$arFilter["<=TRANSACT_DATE"] = Trim($filter_date_to);

				$arResult["TRANSACT"] = array();

				$dbTransactList = CSaleAffiliateTransact::GetList(
					array("TRANSACT_DATE" => "ASC"),
					$arFilter,
					false,
					false,
					array("ID", "TRANSACT_DATE", "AMOUNT", "CURRENCY", "DEBIT", "DESCRIPTION")
				);
				while ($arTransactList = $dbTransactList->GetNext())
				{
					$arTransactList["AMOUNT_FORMAT"] = SaleFormatCurrency($arTransactList["AMOUNT"], $arTransactList["CURRENCY"]);
					$arTransactList["AMOUNT_INCOME"] = (($arTransactList["DEBIT"] == "Y") ? $arTransactList["AMOUNT_FORMAT"] : "");
					$arTransactList["AMOUNT_OUTCOME"] = (($arTransactList["DEBIT"] != "Y") ? $arTransactList["AMOUNT_FORMAT"] : "");
					$arTransactList["DESCRIPTION_NOTES"] = (array_key_exists($arTransactList["DESCRIPTION"], $arTransactTypes) ? $arTransactTypes[$arTransactList["DESCRIPTION"]] : "");

					$arResult["TRANSACT"][] = $arTransactList;
				}
				
				$arResult["FILTER_ID"] = rand(0, 10000);
			}
			else
			{
				$arResult = False;
			}

			$this->IncludeComponentTemplate();
		}
		else
		{
			LocalRedirect($arParams["REGISTER_PAGE"]."?REDIRECT_PAGE=".UrlEncode($APPLICATION->GetCurPage()));
			die();
		}
	}
	else
	{
		LocalRedirect($arParams["REGISTER_PAGE"]."?REDIRECT_PAGE=".UrlEncode($APPLICATION->GetCurPage()));
		die();
	}
}
else
{
	?>
	<b><?=ShowError(GetMessage("SPCA_NO_SHOP"))?></b>
	<?
}
?>