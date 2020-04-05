<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = Array(
	"Y" => GetMessage("SPO_DESC_YES"),
	"N" => GetMessage("SPO_DESC_NO"),
);


$arComponentParameters = array(
	"PARAMETERS" => array(
		"SEF_MODE" => Array(
			"list" => Array(
				"NAME" => GetMessage("SPO_LIST_DESC"),
				"DEFAULT" => "index.php",
				"VARIABLES" => array()
			),
			"detail" => Array(
				"NAME" => GetMessage("SPO_DETAIL_DESC"),
				"DEFAULT" => "detail/#ID#",
				"VARIABLES" => array("ID")
			),
			"cancel" => Array(
				"NAME" => GetMessage("SPO_CANCEL_DESC"),
				"DEFAULT" => "cancel/#ID#",
				"VARIABLES" => array("ID")
			),

		),

		"ORDERS_PER_PAGE" => Array(
			"NAME" => GetMessage("SPO_ORDERS_PER_PAGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "20",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => Array(
			"NAME" => GetMessage("SPO_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/order/payment/",
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_BASKET" => Array(
			"NAME" => GetMessage("SPO_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/personal/cart",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_CATALOG" => array(
			"NAME" => GetMessage("SPO_PATH_TO_CATALOG"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/catalog/",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"SET_TITLE" => Array(),
		"SAVE_IN_SESSION" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SPO_SAVE_IN_SESSION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"NAV_TEMPLATE" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("SPOL_NAV_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"DISALLOW_CANCEL" => Array(
			"NAME" => GetMessage("SPO_DISALLOW_CANCEL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_TIME"  =>  Array("DEFAULT"=>3600),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("SPO_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),		
	)
);

if(CModule::IncludeModule("iblock"))
{
	$arComponentParameters["PARAMETERS"]["ACTIVE_DATE_FORMAT"] = CIBlockParameters::GetDateFormat(GetMessage("SPO_ACTIVE_DATE_FORMAT"), "VISUAL");

	$arComponentParameters["PARAMETERS"]["CUSTOM_SELECT_PROPS"] = array(
		"NAME" => GetMessage("SPO_PARAM_CUSTOM_SELECT_PROPS"),
		"TYPE" => "STRING",
		"MULTIPLE" => "Y",
		"VALUES" => array(),
		"PARENT" => "ADDITIONAL_SETTINGS",
	);
}

if(CModule::IncludeModule("sale"))
{
	$userInfo = array(
		"LOGIN" => GetMessage("SPO_USER_INFO_LOGIN"),
		"EMAIL" => GetMessage("SPO_USER_INFO_EMAIL"),
		"PERSON_TYPE_NAME" => GetMessage("SPO_USER_INFO_PERSON_TYPE_NAME"),
		0 => GetMessage("SPO_SHOW_ALL"),
	);

	$arComponentParameters['PARAMETERS']['DETAIL_HIDE_USER_INFO'] = array(
		"NAME" => GetMessage("SPO_ORDER_HIDE_USER_INFO"),
		"TYPE" => "LIST",
		"VALUES" => $userInfo,
		"MULTIPLE" => "Y",
		"DEFAULT" => 0,
		"PARENT" => "BASE"
	);

	$dbPerson = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"));
	while($arPerson = $dbPerson->GetNext())
	{

		$arPers2Prop = Array("" => GetMessage("SPO_SHOW_ALL"));
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PERSON_TYPE_ID" => $arPerson["ID"]));
		while($arProp = $dbProp -> GetNext())
		{

			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}

		if($bProp)
		{
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  Array(
							"NAME" => GetMessage("SPO_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")",
							"TYPE"=>"LIST", "MULTIPLE"=>"Y",
							"VALUES" => $arPers2Prop,
							"DEFAULT"=>"",
							"COLS"=>25,
							"ADDITIONAL_VALUES"=>"N",
							"PARENT" => "BASE",
				);
		}
	}

	// "historic" statuses
	$dbStat = CSaleStatus::GetList(array('sort' => 'asc'), array('LID' => LANGUAGE_ID), false, false, array('ID', 'NAME'));
	$statList = array();
	while ($item = $dbStat->Fetch())
		$statList[$item['ID']] = $item['NAME'];

	$arComponentParameters['PARAMETERS']['HISTORIC_STATUSES'] = array(
		"NAME" => GetMessage("SPO_HISTORIC_STATUSES"),
		"TYPE" => "LIST",
		"VALUES" => $statList,
		"MULTIPLE" => "Y",
		"DEFAULT" => "F",
		"PARENT" => "ADDITIONAL_SETTINGS",
	);

	array_unshift($statList, GetMessage("SPO_NOT_CHOSEN"));

	$arComponentParameters['PARAMETERS']['RESTRICT_CHANGE_PAYSYSTEM'] = array(
		"NAME" => GetMessage("SPO_RESTRICT_CHANGE_PAYSYSTEM"),
		"TYPE" => "LIST",
		"VALUES" => $statList,
		"MULTIPLE" => "Y",
		"DEFAULT" => 0,
		"PARENT" => "ORDER",
		"SIZE" => 5,
	);

	$arComponentParameters['PARAMETERS']['REFRESH_PRICES'] = array(
		"NAME" => GetMessage("SPO_REFRESH_PRICE_AFTER_PAYSYSTEM_CHANGE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ORDER",
	);

	$orderSortList = array(
		'STATUS' => GetMessage("SPO_ORDER_LIST_SORT_STATUS"),
		'ID' => GetMessage("SPO_ORDER_LIST_SORT_ID"),
		'ACCOUNT_NUMBER'=> GetMessage("SPO_ORDER_LIST_SORT_ACCOUNT_NUMBER"),
		'DATE_INSERT'=> GetMessage("SPO_ORDER_LIST_SORT_DATE_CREATE"),
		'PRICE'=> GetMessage("SPO_ORDER_LIST_SORT_PRICE")
	);

	$arComponentParameters['PARAMETERS']['ORDER_DEFAULT_SORT'] = array(
		"NAME" => GetMessage("SPO_ORDER_LIST_DEFAULT_SORT"),
		"TYPE" => "LIST",
		"VALUES" => $orderSortList,
		"MULTIPLE" => "N",
		"DEFAULT" => "STATUS",
		"PARENT" => "ORDER",
	);
	
	if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
	{
		$arComponentParameters['PARAMETERS']['ALLOW_INNER'] = array(
			"NAME" => GetMessage("SPO_ALLOW_INNER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ORDER",
		);

		$arComponentParameters['PARAMETERS']['ONLY_INNER_FULL'] = array(
			"NAME" => GetMessage("SPO_ONLY_INNER_FULL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ORDER",
		);
	}	
}
?>
