<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$enableAuthorize = (isset($arCurrentValues['SHOW_AUTHOR']) && $arCurrentValues['SHOW_AUTHOR'] == 'Y');
$enableRegistration = !isset($arCurrentValues['SHOW_REGISTRATION']) || $arCurrentValues['SHOW_REGISTRATION'] === 'Y';

if ($enableAuthorize && !isset($arCurrentValues['SHOW_REGISTRATION']))
{
	$arCurrentValues['SHOW_REGISTRATION'] = 'Y';
}

$arComponentParameters = array(
	"GROUPS" => array(
		"PERSONAL" => array(
			"NAME" => GetMessage("SBBL_PERSONAL"),
			"SORT" => 110,
		),
		"AUTHOR" => array(
			"NAME" => GetMessage("SBBL_AUTHOR"),
			"SORT" => 120,
		),
		"LIST" => array(
			"NAME" => GetMessage("SBBL_LIST"),
			"SORT" => 130,
		),
	),
	"PARAMETERS" => array(
		// BASE
		"PATH_TO_BASKET" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_BASKET"),
			"TYPE" => "STRING",
			"DEFAULT" => '={SITE_DIR."personal/cart/"}',
			"PARENT" => "BASE",
		),
		"PATH_TO_ORDER" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_ORDER"),
			"TYPE" => "STRING",
			"DEFAULT" => '={SITE_DIR."personal/order/make/"}',
			"PARENT" => "BASE",
		),
		"HIDE_ON_BASKET_PAGES" => array(
			"NAME" => GetMessage("SBBL_HIDE_ON_BASKET_PAGES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y"
		),
		"SHOW_NUM_PRODUCTS" => array(
			"NAME" => GetMessage("SBBL_SHOW_NUM_PRODUCTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_TOTAL_PRICE" => array(
			"NAME" => GetMessage("SBBL_SHOW_TOTAL_PRICE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		"SHOW_EMPTY_VALUES" => array(
			"NAME" => GetMessage("SBBL_SHOW_EMPTY_VALUES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "BASE",
		),
		// PERSONAL
		"SHOW_PERSONAL_LINK" => array(
			"NAME" => GetMessage("SBBL_SHOW_PERSONAL_LINK"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "PERSONAL",
			"REFRESH" => "Y"
		),
		"PATH_TO_PERSONAL" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_PERSONAL"),
			"TYPE" => "STRING",
			"DEFAULT" => '={SITE_DIR."personal/"}',
			"COLS" => 25,
			"PARENT" => "PERSONAL",
			"HIDDEN" => (isset($arCurrentValues['SHOW_PERSONAL_LINK']) && $arCurrentValues['SHOW_PERSONAL_LINK'] == 'N' ? 'Y' : 'N')
		),
		// AUTHOR
		"SHOW_AUTHOR" => array(
			"NAME" => GetMessage("SBBL_SHOW_AUTHOR"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "AUTHOR",
			"REFRESH" => "Y"
		),
		"PATH_TO_AUTHORIZE" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_AUTHORIZE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
			"PARENT" => "AUTHOR",
			"HIDDEN" => ($enableAuthorize ? 'N' : 'Y')
		),
		"SHOW_REGISTRATION" => array(
			"NAME" => GetMessage("SBBL_SHOW_REGISTRATION"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "AUTHOR",
			"REFRESH" => "Y",
			"HIDDEN" => ($enableAuthorize ? 'N' : 'Y')
		),
		"PATH_TO_REGISTER" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_REGISTER"),
			"TYPE" => "STRING",
			"DEFAULT" => '={SITE_DIR."login/"}',
			"PARENT" => "AUTHOR",
			"HIDDEN" => ($enableRegistration ? 'N' : 'Y')
		),
		"PATH_TO_PROFILE" => array(
			"NAME" => GetMessage("SBBL_PATH_TO_PROFILE"),
			"TYPE" => "STRING",
			"DEFAULT" => '={SITE_DIR."personal/"}',
			"PARENT" => "AUTHOR",
			"HIDDEN" => ($enableAuthorize ? 'N' : 'Y')
		),
		// LIST
		"SHOW_PRODUCTS" => array(
			"NAME" => GetMessage("SBBL_SHOW_PRODUCTS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
			"PARENT" => "LIST",
		),
		// VISUAL
		"POSITION_FIXED" => array(
			"NAME" => GetMessage("SBBL_POSITION_FIXED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "VISUAL",
			"REFRESH" => "Y",
		),
	)
);

// LIST
if($arCurrentValues["SHOW_PRODUCTS"] == "Y")
{
	$arComponentParameters["PARAMETERS"] += array(
		"SHOW_DELAY" => array(
			"NAME" => GetMessage('SBBL_SHOW_DELAY'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "LIST",
		),
		"SHOW_NOTAVAIL" => array(
			"NAME" => GetMessage('SBBL_SHOW_NOTAVAIL'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"PARENT" => "LIST",
		),
		"SHOW_IMAGE" => array(
			"NAME" => GetMessage('SBBL_SHOW_IMAGE'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "LIST",
		),
		"SHOW_PRICE" => array(
			"NAME" => GetMessage('SBBL_SHOW_PRICE'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "LIST",
		),
		"SHOW_SUMMARY" => array(
			"NAME" => GetMessage('SBBL_SHOW_SUMMARY'),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
			"PARENT" => "LIST",
		),
	);
}

// VISUAL
if($arCurrentValues["POSITION_FIXED"] == "Y")
{
	$arComponentParameters["PARAMETERS"] += array(
		"POSITION_HORIZONTAL" => array(
			"NAME"=>GetMessage("SBBL_POSITION_HORIZONTAL"),
			"TYPE"=>"LIST",
			"VALUES"=>array(
				"left" => GetMessage("SBBL_POSITION_HORIZONTAL_LEFT"),
				"hcenter" => GetMessage("SBBL_POSITION_CENTER"),
				"right" => GetMessage("SBBL_POSITION_HORIZONTAL_RIGHT")
			),
			"DEFAULT"=>"right",
			"PARENT" => "VISUAL",
		),
		"POSITION_VERTICAL" => array(
			"NAME"=>GetMessage("SBBL_POSITION_VERTICAL"),
			"TYPE"=>"LIST",
			"VALUES"=>array(
				"top" => GetMessage("SBBL_POSITION_VERTICAL_TOP"),
				"vcenter" => GetMessage("SBBL_POSITION_CENTER"),
				"bottom" => GetMessage("SBBL_POSITION_VERTICAL_BOTTOM")
			),
			"DEFAULT"=>"top",
			"PARENT" => "VISUAL",
		),
	);
}