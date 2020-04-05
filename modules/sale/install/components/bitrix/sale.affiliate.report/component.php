<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

if(!CBXFeatures::IsFeatureEnabled('SaleAffiliate'))
	return;

$arParams["REGISTER_PAGE"] = trim($arParams["REGISTER_PAGE"]);
if (strlen($arParams["REGISTER_PAGE"]) <= 0)
	$arParams["REGISTER_PAGE"] = "register.php";

if (strlen($arParams["SET_TITLE"]) <= 0) $arParams["SET_TITLE"] = "Y";
	
if (CModule::IncludeModule("sale"))
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("SPCAS1_PROG_REPORT"));

	if ($GLOBALS["USER"]->IsAuthorized())
	{
		$dbAffiliate = CSaleAffiliate::GetList(
			array("TRANSACT_DATE" => "ASC"),
			array(
				"USER_ID" => IntVal($GLOBALS["USER"]->GetID()),
				"SITE_ID" => SITE_ID,
			),
			false,
			false,
			array("ID", "PLAN_ID", "ACTIVE", "PAID_SUM", "APPROVED_SUM", "PENDING_SUM", "LAST_CALCULATE")
		);
		
		if ($arAffiliate = $dbAffiliate->Fetch())
		{
			$arResult = array();

			if ($arAffiliate["ACTIVE"] == "Y")
			{
				$arResult["FIELDS"] = $arAffiliate;

				if (strlen($_REQUEST["del_filter"])>0)
					DelFilter(Array("filter_date_from", "filter_date_to"));
				else
					InitFilter(Array("filter_date_from", "filter_date_to"));

				$filter_date_from = $_REQUEST["filter_date_from"];
				$filter_date_to = $_REQUEST["filter_date_to"];
				if (StrLen($filter_date_from) <= 0 && StrLen($filter_date_to) <= 0)
				{
					$filter_date_from = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("m") - 3, 1, date("Y")));
					$filter_date_to = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), mktime(0, 0, 0, date("m"), date("d") + 1, date("Y")));
				}

				$arResult["FILTER"] = array(
					"filter_date_from" => $filter_date_from,
					"filter_date_to" => $filter_date_to
				);

				$arResult["CURRENT_PAGE_PARAM"] = $APPLICATION->GetCurPageParam("", array("filter_date_from", "filter_date_to"));
				$arResult["CURRENT_PAGE"] = $APPLICATION->GetCurPage();

				$arFilter = array(
					"=AFFILIATE_ID" => $arAffiliate["ID"],
					"=ALLOW_DELIVERY" => "Y",
					"=CANCELED" => "N",
					"=LID" => SITE_ID
				);
				if (StrLen($filter_date_from) > 0)
					$arFilter[">=DATE_ALLOW_DELIVERY"] = Trim($filter_date_from);
				if (StrLen($filter_date_to) > 0)
					$arFilter["<=DATE_ALLOW_DELIVERY"] = Trim($filter_date_to);

				$dbItemsList = \Bitrix\Sale\Internals\OrderTable::getList(
					array(
						'filter' => $arFilter,
						'select' => array(
							"BASKET_NAME" => 'BASKET.NAME',
							"BASKET_PRODUCT_ID" => 'BASKET.PRODUCT_ID',
							"BASKET_MODULE" => 'BASKET.MODULE',
							"BASKET_PRICE" => 'BASKET.PRICE',
							"BASKET_CURRENCY" => 'BASKET.CURRENCY',
							"BASKET_DISCOUNT_PRICE" => 'BASKET.DISCOUNT_PRICE',
							'BASKET_QUANTITY' => 'SUM_BASKET_QUANTITY'
						),
						'runtime' => array(
							new \Bitrix\Main\Entity\ExpressionField('SUM_BASKET_QUANTITY', 'SUM(%s)', array('BASKET.QUANTITY'))
						),
						'order' => array("BASKET.MODULE" => "ASC", "BASKET.NAME" => "ASC", "BASKET.PRODUCT_ID" => "ASC"),
						'group' => array("BASKET.MODULE", "BASKET.PRODUCT_ID", "BASKET.NAME", "BASKET.PRICE", "BASKET.CURRENCY", "BASKET.DISCOUNT_PRICE"),
					)
				);

				$arResult["ROWS"] = False;

				if ($arItemsList = $dbItemsList->Fetch())
				{
					$affiliateCurrency = CSaleLang::GetLangCurrency(SITE_ID);

					$currentBasketModule = $arItemsList["BASKET_MODULE"];
					$currentBasketProductID = $arItemsList["BASKET_PRODUCT_ID"];
					$currentBasketName = $arItemsList["BASKET_NAME"];
					$currentQuantity = 0;
					$currentSum = 0;

					$totalQuantity = 0;
					$totalSum = 0;

					$arResult["ROWS"] = array();

					do
					{
						if ($currentBasketModule != $arItemsList["BASKET_MODULE"]
							|| $currentBasketProductID != $arItemsList["BASKET_PRODUCT_ID"]
							|| $currentBasketName != $arItemsList["BASKET_NAME"])
						{
							$arResult["ROWS"][] = array(
								"NAME" => htmlspecialcharsex($currentBasketName),
								"QUANTITY" => $currentQuantity,
								"SUM" => $currentSum,
								"CURRENCY" => $affiliateCurrency,
								"SUM_FORMAT" => SaleFormatCurrency($currentSum, $affiliateCurrency)
							);

							$currentBasketModule = $arItemsList["BASKET_MODULE"];
							$currentBasketProductID = $arItemsList["BASKET_PRODUCT_ID"];
							$currentBasketName = $arItemsList["BASKET_NAME"];

							$totalQuantity += $currentQuantity;
							$totalSum += $currentSum;

							$currentQuantity = 0;
							$currentSum = 0;
						}

						$currentQuantity += $arItemsList["BASKET_QUANTITY"];
						if ($affiliateCurrency != $arItemsList["BASKET_CURRENCY"])
							//$currentSum += CCurrencyRates::ConvertCurrency(($arItemsList["BASKET_PRICE"] - $arItemsList["BASKET_DISCOUNT_PRICE"]) * $arItemsList["BASKET_QUANTITY"], $arItemsList["BASKET_CURRENCY"], $affiliateCurrency);
							$currentSum += CCurrencyRates::ConvertCurrency($arItemsList["BASKET_PRICE"] * $arItemsList["BASKET_QUANTITY"], $arItemsList["BASKET_CURRENCY"], $affiliateCurrency);
						else
							//$currentSum += ($arItemsList["BASKET_PRICE"] - $arItemsList["BASKET_DISCOUNT_PRICE"]) * $arItemsList["BASKET_QUANTITY"];
							$currentSum += $arItemsList["BASKET_PRICE"] * $arItemsList["BASKET_QUANTITY"];
					}
					while ($arItemsList = $dbItemsList->Fetch());

					$arResult["ROWS"][] = array(
						"NAME" => $currentBasketName,
						"QUANTITY" => $currentQuantity,
						"SUM" => $currentSum,
						"CURRENCY" => $affiliateCurrency,
						"SUM_FORMAT" => SaleFormatCurrency($currentSum, $affiliateCurrency)
					);

					$totalQuantity += $currentQuantity;
					$totalSum += $currentSum;

					$arResult["TOTAL"] = array(
						"QUANTITY" => $totalQuantity,
						"SUM" => $totalSum,
						"CURRENCY" => $affiliateCurrency,
						"SUM_FORMAT" => SaleFormatCurrency($totalSum, $affiliateCurrency)
					);
				}
				
				$arResult["FILTER_ID"] = rand(0, 10000);
			}
			else
			{
				$arResult = false;
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
	<b><?=ShowError(GetMessage("SPCAS1_NO_SHOP"))?></b>
	<?
}
?>