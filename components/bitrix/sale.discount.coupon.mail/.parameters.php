<?
use \Bitrix\Main\Loader as Loader;
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!Loader::includeModule("sale") || !Loader::includeModule("catalog"))
{
	ShowError(GetMessage("SBP_NEED_REQUIRED_MODULES"));
	die();
}

$arComponentParameters = array(
	"GROUPS" => array(
		"DISCOUNT" => array(
			"NAME" => GetMessage("SBP_GROUPS_DISCOUNT"),
		),
		"COUPON" => array(
			"NAME" => GetMessage("SBP_GROUPS_COUPON"),
		),
		"REPL_SETT" => array(
			"NAME" => GetMessage("SBP_GROUPS_REPL_SETT"),
		),
	),
	"PARAMETERS" => array(
		"USE_DISCOUNT_ID" => array(
			"PARENT" => "DISCOUNT",
			"NAME" => GetMessage("SBP_PARAMETERS_USE_DISCOUNT_ID"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"
		),
		'COUPON_TYPE' => array(
			'PARENT' => 'COUPON',
			'NAME' => GetMessage("SBP_PARAMETERS_COUPON_TYPE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				"Order" => GetMessage("SBP_PARAMETERS_COUPON_TYPE_ORDER"),
				"Basket" => GetMessage("SBP_PARAMETERS_COUPON_TYPE_BASKET"),
			),
			"DEFAULT" => "Order",
		),
		'COUPON_IS_LIMITED' => array(
			'PARENT' => 'COUPON',
			'NAME' => GetMessage("SBP_PARAMETERS_COUPON_IS_LIMITED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y"
		),
		"COUPON_DESCRIPTION" => array(
			"PARENT" => "REPL_SETT",
			"NAME" => GetMessage("SBP_PARAMETERS_REPL_SETT_COUPON_DESCRIPTION"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#EMAIL_TO#}",
		),
	),
);
if ($arCurrentValues["USE_DISCOUNT_ID"] !== "Y")
{
	$discountParams = [
		"DISCOUNT_VALUE" => array(
			"PARENT" => "DISCOUNT",
			"NAME" => GetMessage("SBP_PARAMETERS_DISCOUNT_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "10",
		),
		'DISCOUNT_UNIT' => array(
			'PARENT' => 'DISCOUNT',
			'NAME' => GetMessage("SBP_PARAMETERS_DISCOUNT_UNIT"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				"Perc" => '%',
				"CurEach" => GetMessage("SBP_PARAMETERS_DISCOUNT_UNIT_EACH"),
				"CurAll" => GetMessage("SBP_PARAMETERS_DISCOUNT_UNIT_ALL"),
			),
			"DEFAULT" => "Prsnt",
		),
		"DISCOUNT_XML_ID" => array(
			"PARENT" => "REPL_SETT",
			"NAME" => GetMessage("SBP_PARAMETERS_REPL_SETT_DISCOUNT_XML_ID"),
			"TYPE" => "STRING",
			"DEFAULT" => "{#SENDER_CHAIN_CODE#}",
		),
	];
	$arComponentParameters['PARAMETERS'] = array_merge($arComponentParameters['PARAMETERS'], $discountParams);
}
else
{
	$discounts = [];
	$discountData = \Bitrix\Sale\Internals\DiscountTable::getList([
		'select' => ['ID', 'NAME']
	]);
	while ($discount = $discountData->fetch())
	{
		$discounts[$discount['ID']] = htmlspecialcharsbx("{$discount['NAME']} [{$discount['ID']}]");
	}
	$arComponentParameters['PARAMETERS']['DISCOUNT_ID'] = [
		"PARENT" => "DISCOUNT",
		"NAME" => GetMessage("SBP_PARAMETERS_DISCOUNT_ID"),
		"TYPE"=>"LIST",
		"MULTIPLE"=>"N",
		"VALUES" => $discounts,
		"COLS"=>25,
		"ADDITIONAL_VALUES"=>"N",
	];
}

if ($arCurrentValues["COUPON_IS_LIMITED"] === "Y")
{
	CBitrixComponent::includeComponentClass("bitrix:sale.discount.coupon.mail");
	$discountParams = [
		"COUPON_LIMIT_VALUE" => array(
			"PARENT" => "COUPON",
			"NAME" => GetMessage("SBP_PARAMETERS_COUPON_LIMIT_VALUE"),
			"TYPE" => "STRING",
			"DEFAULT" => "0",
		),
		'COUPON_LIMIT_TYPE' => array(
			'PARENT' => 'COUPON',
			'NAME' => GetMessage("SBP_PARAMETERS_COUPON_LIMIT_TYPE"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => Array(
				\CSaleDiscountCouponMailComponent::DAY_LIMIT_TYPE => GetMessage("SBP_PARAMETERS_COUPON_DAY_LIMIT_TYPE"),
				\CSaleDiscountCouponMailComponent::WEEK_LIMIT_TYPE => GetMessage("SBP_PARAMETERS_COUPON_WEEK_LIMIT_TYPE"),
				\CSaleDiscountCouponMailComponent::MONTH_LIMIT_TYPE => GetMessage("SBP_PARAMETERS_COUPON_MONTH_LIMIT_TYPE"),
			),
			"DEFAULT" => \CSaleDiscountCouponMailComponent::DAY_LIMIT_TYPE,
		)
	];
	$arComponentParameters['PARAMETERS'] = array_merge($arComponentParameters['PARAMETERS'], $discountParams);
}
