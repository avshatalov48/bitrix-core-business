<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
	"GROUPS" => array(
		"FB" => array(
			"NAME" => GetMessage("CATALOG_SB_FB_SECTION_TITLE"),
		),
		"TW" => array(
			"NAME" => GetMessage("CATALOG_SB_TW_SECTION_TITLE"),
		),
		"GP" => array(
			"NAME" => GetMessage("CATALOG_SB_GP_SECTION_TITLE"),
		),
		"VK" => array(
			"NAME" => GetMessage("CATALOG_SB_VK_SECTION_TITLE"),
		)
	),
	"PARAMETERS" => array(
/*Base*/
		"URL_TO_LIKE" => array(
			"NAME" => GetMessage("CATALOG_SB_BASE_URL_TO_LIKE"),
			"TYPE" => "STRING",
			"PARENT" => "BASE"
		),
		"TITLE" => array(
			"NAME" => GetMessage("CATALOG_SB_BASE_TITLE"),
			"TYPE" => "STRING",
			"PARENT" => "BASE",
		),
		"DESCRIPTION" => array(
			"NAME" => GetMessage("CATALOG_SB_BASE_DESCRIPTION"),
			"TYPE" => "STRING",
			"PARENT" => "BASE",
		),
		"IMAGE" => array(
			"NAME" => GetMessage("CATALOG_SB_BASE_IMAGE"),
			"TYPE" => "STRING",
			"PARENT" => "BASE",
		),

/*FB*/
		"FB_USE" => array(
			"NAME" => GetMessage("CATALOG_SB_FB_USE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "FB",
		),

/*TW*/
		"TW_USE" => array(
			"NAME" => GetMessage("CATALOG_SB_TW_USE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "TW",
			"REFRESH" => "Y",
		),

/*G+*/
		"GP_USE" => array(
			"NAME" => GetMessage("CATALOG_SB_GP_USE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "GP"
		),

/*VK*/
		"VK_USE" => array(
			"NAME" => GetMessage("CATALOG_SB_VK_USE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "VK"
		)
	)
);


if(!isset($arCurrentValues["TW_USE"]) || $arCurrentValues["TW_USE"] == "Y")
{
	$arComponentParameters["PARAMETERS"]["TW_VIA"] = array(
		"NAME" => GetMessage("CATALOG_SB_TW_VIA"),
		"TYPE" => "STRING",
		"PARENT" => "TW"
	);

	$arComponentParameters["PARAMETERS"]["TW_HASHTAGS"] = array(
		"NAME" => GetMessage("CATALOG_SB_TW_HASHTAGS"),
		"TYPE" => "STRING",
		"PARENT" => "TW"
	);

	$arComponentParameters["PARAMETERS"]["TW_RELATED"] = array(
		"NAME" => GetMessage("CATALOG_SB_TW_RELATED"),
		"TYPE" => "STRING",
		"PARENT" => "TW"
	);
}

?>