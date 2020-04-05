<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arComponentParameters = Array(
	"PARAMETERS" => Array(
		"PATH_TO_BASKET" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "basket.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PERSONAL" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_PERSONAL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "index.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_PAYMENT" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PATH_TO_AUTH" => Array(
			"NAME" => GetMessage("SOA_PATH_TO_AUTH"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "/auth/",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		),
		"PAY_FROM_ACCOUNT" => Array(
			"NAME"=>GetMessage("SOA_ALLOW_PAY_FROM_ACCOUNT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y",
			"PARENT" => "BASE",
		),
		"COUNT_DELIVERY_TAX" => Array(
			"NAME"=>GetMessage("SOA_COUNT_DELIVERY_TAX"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N",
			"PARENT" => "BASE",
		),
		"COUNT_DISCOUNT_4_ALL_QUANTITY" => Array(
			"NAME"=>GetMessage("SOA_COUNT_DISCOUNT_4_ALL_QUANTITY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N",
			"PARENT" => "BASE",
		),
		"ONLY_FULL_PAY_FROM_ACCOUNT" => Array(
			"NAME"=>GetMessage("SOA_ONLY_FULL_PAY_FROM_ACCOUNT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N",
			"PARENT" => "BASE",
		),
		"ALLOW_AUTO_REGISTER" => Array(
			"NAME"=>GetMessage("SOA_ALLOW_AUTO_REGISTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"N",
			"PARENT" => "BASE",
		),
		"SEND_NEW_USER_NOTIFY" => Array(
			"NAME"=>GetMessage("SOA_SEND_NEW_USER_NOTIFY"),
			"TYPE" => "CHECKBOX",
			"DEFAULT"=>"Y",
			"PARENT" => "BASE",
		),
		"DELIVERY_NO_AJAX" => Array(
			"NAME" => GetMessage("SOA_DELIVERY_NO_AJAX"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"PARENT" => "BASE",
		),
		"DELIVERY_NO_SESSION" => Array(
			"NAME" => GetMessage("SOA_DELIVERY_NO_SESSION"),
			"TYPE" => "CHECKBOX",
			"MULTIPLE" => "N",
			"DEFAULT" => "N",
			"PARENT" => "BASE",
		),
		"TEMPLATE_LOCATION" => Array(
			"NAME"=>GetMessage("SBB_TEMPLATE_LOCATION"),
			"TYPE"=>"LIST",
			"MULTIPLE"=>"N",
			"VALUES"=>array(
					".default" => GetMessage("SBB_TMP_DEFAULT"),
					"popup" => GetMessage("SBB_TMP_POPUP")
				),
			"DEFAULT"=>".default",
			"COLS"=>25,
			"ADDITIONAL_VALUES"=>"N",
			"PARENT" => "BASE",
		),
		"DELIVERY_TO_PAYSYSTEM" => Array(
			"NAME" => GetMessage("SBB_DELIVERY_PAYSYSTEM"),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES"=>array(
					"d2p" => GetMessage("SBB_TITLE_DP"),
					"p2d" => GetMessage("SBB_TITLE_PD")
				),
			"PARENT" => "BASE",
		),
		"SET_TITLE" => Array()
	)
);

if(CModule::IncludeModule("sale"))
{
	$dbPerson = CSalePersonType::GetList(Array("SORT" => "ASC", "NAME" => "ASC"));
	while($arPerson = $dbPerson->GetNext())
	{

		$arPers2Prop = Array("" => GetMessage("SOA_SHOW_ALL"));
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(Array("SORT" => "ASC", "NAME" => "ASC"), Array("PERSON_TYPE_ID" => $arPerson["ID"]));
		while($arProp = $dbProp -> Fetch())
		{

			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}

		if($bProp)
		{
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] =  Array(
							"NAME" => GetMessage("SOA_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")",
							"TYPE"=>"LIST", "MULTIPLE"=>"Y",
							"VALUES" => $arPers2Prop,
							"DEFAULT"=>"",
							"COLS"=>25,
							"ADDITIONAL_VALUES"=>"N",
							"PARENT" => "BASE",
				);
		}
	}
}
?>