<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = array(
	"PARAMETERS" => array(
		"PATH_TO_PAYMENT" => array(
			"NAME" => GetMessage("SOPC_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order/payment/",
			"COLS" => 25,
		),
		"SET_TITLE" => array()
	)
);

$paySystemList = array(GetMessage("SOPC_SHOW_ALL"));

if (\Bitrix\Main\Loader::includeModule('sale'))
{
	$paySystemManagerResult = Bitrix\Sale\PaySystem\Manager::getList(array('select' => array('ID','NAME')));

	while ($paySystem = $paySystemManagerResult->fetch())
	{
		if (!empty($paySystem['NAME']))
		{
			$paySystemList[$paySystem['ID']] = $paySystem['NAME'].' ['.$paySystem['ID'].']';
		}
	}
}

if (isset($paySystemList))
{
	$arComponentParameters['PARAMETERS']['ELIMINATED_PAY_SYSTEMS'] = array(
		"NAME"=>GetMessage("SOPC_ELIMINATED_PAY_SYSTEMS"),
		"TYPE"=>"LIST",
		"MULTIPLE"=>"Y",
		"DEFAULT" => "0",
		"VALUES"=>$paySystemList,
		"SIZE" => 6,
		"COLS"=>25,
		"ADDITIONAL_VALUES"=>"N",
	);
}

$arComponentParameters['PARAMETERS']['REFRESH_PRICES'] = array(
	"NAME" => GetMessage("SPOC_REFRESH_PRICE"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "N",
	"PARENT" => "ORDER",
);

if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
{
	$arComponentParameters['PARAMETERS']['ALLOW_INNER'] = array(
		"NAME" => GetMessage("SPOC_ALLOW_INNER"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ORDER",
	);

	$arComponentParameters['PARAMETERS']['ONLY_INNER_FULL'] = array(
		"NAME" => GetMessage("SPOC_ONLY_INNER_FULL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ORDER",
	);
}

