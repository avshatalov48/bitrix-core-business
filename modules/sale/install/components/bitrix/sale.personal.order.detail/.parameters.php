<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arYesNo = [
	"Y" => GetMessage("SPOD_DESC_YES"),
	"N" => GetMessage("SPOD_DESC_NO"),
];

$arComponentParameters = [
	"PARAMETERS" => [
		"PATH_TO_LIST" => [
			"NAME" => GetMessage("SPOD_PATH_TO_LIST"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],
		"PATH_TO_CANCEL" => [
			"NAME" => GetMessage("SPOD_PATH_TO_CANCEL"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],
		"PATH_TO_COPY" => [
			"NAME" => GetMessage("SPOD_PATH_TO_COPY"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],
		"PATH_TO_PAYMENT" => [
			"NAME" => GetMessage("SPOD_PATH_TO_PAYMENT"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "payment.php",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],
		"ID" => [
			"NAME" => GetMessage("SPOD_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "={\$ID}",
			"COLS" => 25,
			"PARENT" => "ADDITIONAL_SETTINGS",
		],

		"CACHE_TIME"  =>  ["DEFAULT"=>3600],
		"CACHE_GROUPS" => [
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("SPOD_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		],

		"SET_TITLE" => [],
	]
];

if(CModule::IncludeModule("iblock"))
{
	$arComponentParameters["PARAMETERS"]["ACTIVE_DATE_FORMAT"] = CIBlockParameters::GetDateFormat(GetMessage("SPOD_ACTIVE_DATE_FORMAT"), "VISUAL");

	$arComponentParameters["PARAMETERS"]["PICTURE_WIDTH"] = [
		"NAME" => GetMessage("SPOD_PARAM_PREVIEW_PICTURE_WIDTH"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "110",
		"PARENT" => "VISUAL",
	];
	$arComponentParameters["PARAMETERS"]["PICTURE_HEIGHT"] = [
		"NAME" => GetMessage("SPOD_PARAM_PREVIEW_PICTURE_HEIGHT"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "110",
		"PARENT" => "VISUAL",
	];
	$arComponentParameters["PARAMETERS"]["PICTURE_RESAMPLE_TYPE"] = [
		"NAME" => GetMessage("SPOD_PARAM_RESAMPLE_TYPE"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => [
			BX_RESIZE_IMAGE_EXACT => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_EXACT"),
			BX_RESIZE_IMAGE_PROPORTIONAL => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_PROPORTIONAL"),
			BX_RESIZE_IMAGE_PROPORTIONAL_ALT => GetMessage("SPOD_PARAM_RESAMPLE_TYPE_BX_RESIZE_IMAGE_PROPORTIONAL_ALT")
		],
		"DEFAULT" => BX_RESIZE_IMAGE_PROPORTIONAL,
		"PARENT" => "VISUAL",
	];

	$arComponentParameters["PARAMETERS"]["CUSTOM_SELECT_PROPS"] = [
		"NAME" => GetMessage("SPOD_PARAM_CUSTOM_SELECT_PROPS"),
		"TYPE" => "STRING",
		"MULTIPLE" => "Y",
		"VALUES" => [],
		"PARENT" => "ADDITIONAL_SETTINGS",
	];
}

if(CModule::IncludeModule("sale"))
{
	$dbPerson = CSalePersonType::GetList(["SORT" => "ASC", "NAME" => "ASC"]);
	while($arPerson = $dbPerson->GetNext())
	{

		$arPers2Prop = [
			"" => GetMessage("SPOD_SHOW_ALL")
		];
		$bProp = false;
		$dbProp = CSaleOrderProps::GetList(
			["SORT" => "ASC", "NAME" => "ASC"],
			["PERSON_TYPE_ID" => $arPerson["ID"]]
		);
		while($arProp = $dbProp -> GetNext())
		{

			$arPers2Prop[$arProp["ID"]] = $arProp["NAME"];
			$bProp = true;
		}

		if($bProp)
		{
			$arComponentParameters["PARAMETERS"]["PROP_".$arPerson["ID"]] = [
				"NAME" => GetMessage("SPOD_PROPS_NOT_SHOW")." \"".$arPerson["NAME"]."\" (".$arPerson["LID"].")",
				"TYPE"=>"LIST",
				"MULTIPLE"=>"Y",
				"VALUES" => $arPers2Prop,
				"DEFAULT"=>"",
				"COLS"=>25,
				"ADDITIONAL_VALUES"=>"N",
				"PARENT" => "BASE",
			];
		}
	}

	$statusList = [
		GetMessage("SPOD_NOT_CHOSEN")
	];
	$listStatusNames = Bitrix\Sale\OrderStatus::getAllStatusesNames(LANGUAGE_ID);
	foreach($listStatusNames as $key => $data)
	{
		$statusList[$key] = $data;
	}

	$arComponentParameters['PARAMETERS']['RESTRICT_CHANGE_PAYSYSTEM'] = [
		"NAME" => GetMessage("SPOD_RESTRICT_CHANGE_PAYSYSTEM"),
		"TYPE" => "LIST",
		"VALUES" => $statusList,
		"MULTIPLE" => "Y",
		"DEFAULT" => 0,
		"PARENT" => "ADDITIONAL_SETTINGS",
		"SIZE" => 5,
	];

	$arComponentParameters['PARAMETERS']['REFRESH_PRICES'] = [
		"NAME" => GetMessage("SPOD_REFRESH_PRICE_AFTER_PAYSYSTEM_CHANGE"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ADDITIONAL_SETTINGS",
	];

	$arComponentParameters['PARAMETERS']['DISALLOW_CANCEL'] = [
		"NAME" => GetMessage("SPOD_DISALLOW_CANCEL"),
		"TYPE" => "CHECKBOX",
		"DEFAULT" => "N",
		"PARENT" => "ADDITIONAL_SETTINGS",
	];

	if (CBXFeatures::IsFeatureEnabled('SaleAccounts'))
	{
		$arComponentParameters['PARAMETERS']['ALLOW_INNER'] = [
			"NAME" => GetMessage("SPOD_ALLOW_INNER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ORDER",
		];

		$arComponentParameters['PARAMETERS']['ONLY_INNER_FULL'] = [
			"NAME" => GetMessage("SPOD_ONLY_INNER_FULL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "ORDER",
		];
	}
}
