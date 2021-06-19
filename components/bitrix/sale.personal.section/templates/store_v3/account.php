<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
	
if ($arParams['SHOW_ACCOUNT_PAGE'] !== 'Y')
{
	LocalRedirect($arParams['SEF_FOLDER']);
}

use Bitrix\Main\Localization\Loc;

global $USER;
if ($arParams['USE_PRIVATE_PAGE_TO_AUTH'] === 'Y' && !$USER->IsAuthorized())
{
	LocalRedirect($arResult['PATH_TO_AUTH_PAGE']);
}

if ($arParams['SET_TITLE'] == 'Y')
{
	$APPLICATION->SetTitle(Loc::getMessage("SPS_TITLE_ACCOUNT"));
}

if ($arParams["MAIN_CHAIN_NAME"] <> '')
{
	$APPLICATION->AddChainItem(htmlspecialcharsbx($arParams["MAIN_CHAIN_NAME"]), $arResult['SEF_FOLDER']);
}
$APPLICATION->AddChainItem(Loc::getMessage("SPS_CHAIN_ACCOUNT"));

if ($arParams['SHOW_ACCOUNT_COMPONENT'] !== 'N')
{
	$APPLICATION->IncludeComponent(
		"bitrix:sale.personal.account",
		"bootstrap_v4",
		Array(
			"SET_TITLE" => "N",
			"AUTH_FORM_IN_TEMPLATE" => 'Y'
		),
		$component
	);
}
if ($arParams['SHOW_ACCOUNT_PAY_COMPONENT'] !== 'N' && $USER->IsAuthorized())
{
	?>
	<div class="row">
		<div class="col">
			<h2 class="sale-personal-section-account-sub-header"><?=Loc::getMessage("SPS_BUY_MONEY")?></h2>
		</div>
	</div>

	<div class="row">
		<div class="col">
			<?
				$APPLICATION->IncludeComponent(
					"bitrix:sale.account.pay",
					"bootstrap_v4",
					Array(
						"COMPONENT_TEMPLATE" => "bootstrap_v4",
						"REFRESHED_COMPONENT_MODE" => "Y",
						"ELIMINATED_PAY_SYSTEMS" => $arParams['ACCOUNT_PAYMENT_ELIMINATED_PAY_SYSTEMS'],
						"PATH_TO_BASKET" => $arParams['PATH_TO_BASKET'],
						"PATH_TO_PAYMENT" => $arParams['PATH_TO_PAYMENT'],
						"PERSON_TYPE" => $arParams['ACCOUNT_PAYMENT_PERSON_TYPE'],
						"REDIRECT_TO_CURRENT_PAGE" => "N",
						"SELL_AMOUNT" => $arParams['ACCOUNT_PAYMENT_SELL_TOTAL'],
						"SELL_CURRENCY" => $arParams['ACCOUNT_PAYMENT_SELL_CURRENCY'],
						"SELL_SHOW_FIXED_VALUES" => $arParams['ACCOUNT_PAYMENT_SELL_SHOW_FIXED_VALUES'],
						"SELL_SHOW_RESULT_SUM" =>  $arParams['ACCOUNT_PAYMENT_SELL_SHOW_RESULT_SUM'],
						"SELL_TOTAL" => $arParams['ACCOUNT_PAYMENT_SELL_TOTAL'],
						"SELL_USER_INPUT" => $arParams['ACCOUNT_PAYMENT_SELL_USER_INPUT'],
						"SELL_VALUES_FROM_VAR" => "N",
						"SELL_VAR_PRICE_VALUE" => "",
						"SET_TITLE" => "N",
						"CONTEXT_SITE_ID" => $arParams["CONTEXT_SITE_ID"],
						"AUTH_FORM_IN_TEMPLATE" => 'Y',
					),
					$component
				);
			}
			?>
		</div>
	</div>
