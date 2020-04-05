<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (IsModuleInstalled('socialnetwork'))
	$bSocNet = true;


if ($bSocNet)
{

	$arRes = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", 0, LANGUAGE_ID);
	$userProp = array();
	if (!empty($arRes))
	{
		foreach ($arRes as $key => $val)
			$userProp[$val["FIELD_NAME"]] = (strLen($val["EDIT_FORM_LABEL"]) > 0 ? $val["EDIT_FORM_LABEL"] : $val["FIELD_NAME"]);
	}

	$userProp1 = array(
		"LOGIN" => GetMessage("MAIN_UL_P_LOGIN"),
		"NAME" => GetMessage("MAIN_UL_P_NAME"),
		"SECOND_NAME" => GetMessage("MAIN_UL_P_SECOND_NAME"),
		"LAST_NAME" => GetMessage("MAIN_UL_P_LAST_NAME"),
		"EMAIL" => GetMessage("MAIN_UL_P_EMAIL"),
		"LAST_LOGIN" => GetMessage("MAIN_UL_P_LAST_LOGIN"),
		"DATE_REGISTER" => GetMessage("MAIN_UL_P_DATE_REGISTER"),
		"PERSONAL_BIRTHDAY" => GetMessage("MAIN_UL_P_PERSONAL_BIRTHDAY"),
		"PERSONAL_PROFESSION" => GetMessage("MAIN_UL_P_PERSONAL_PROFESSION"),
		"PERSONAL_WWW" => GetMessage("MAIN_UL_P_PERSONAL_WWW"),
		"PERSONAL_ICQ" => GetMessage("MAIN_UL_P_PERSONAL_ICQ"),
		"PERSONAL_GENDER" => GetMessage("MAIN_UL_P_PERSONAL_GENDER"),
		"PERSONAL_PHOTO" => GetMessage("MAIN_UL_P_PERSONAL_PHOTO"),
		"PERSONAL_NOTES" => GetMessage("MAIN_UL_P_PERSONAL_NOTES"),
		"PERSONAL_PHONE" => GetMessage("MAIN_UL_P_PERSONAL_PHONE"),
		"PERSONAL_FAX" => GetMessage("MAIN_UL_P_PERSONAL_FAX"),
		"PERSONAL_MOBILE" => GetMessage("MAIN_UL_P_PERSONAL_MOBILE"),
		"PERSONAL_PAGER" => GetMessage("MAIN_UL_P_PERSONAL_PAGER"),
		"PERSONAL_COUNTRY" => GetMessage("MAIN_UL_P_PERSONAL_COUNTRY"),
		"PERSONAL_STATE" => GetMessage("MAIN_UL_P_PERSONAL_STATE"),
		"PERSONAL_CITY" => GetMessage("MAIN_UL_P_PERSONAL_CITY"),
		"PERSONAL_ZIP" => GetMessage("MAIN_UL_P_PERSONAL_ZIP"),
		"PERSONAL_STREET" => GetMessage("MAIN_UL_P_PERSONAL_STREET"),
		"PERSONAL_MAILBOX" => GetMessage("MAIN_UL_P_PERSONAL_MAILBOX"),
		"WORK_COMPANY" => GetMessage("MAIN_UL_P_WORK_COMPANY"),
		"WORK_DEPARTMENT" => GetMessage("MAIN_UL_P_WORK_DEPARTMENT"),
		"WORK_POSITION" => GetMessage("MAIN_UL_P_WORK_POSITION"),
		"WORK_WWW" => GetMessage("MAIN_UL_P_WORK_WWW"),
		"WORK_PROFILE" => GetMessage("MAIN_UL_P_WORK_PROFILE"),
		"WORK_LOGO" => GetMessage("MAIN_UL_P_WORK_LOGO"),
		"WORK_NOTES" => GetMessage("MAIN_UL_P_WORK_NOTES"),
		"WORK_PHONE" => GetMessage("MAIN_UL_P_WORK_PHONE"),
		"WORK_FAX" => GetMessage("MAIN_UL_P_WORK_FAX"),
		"WORK_PAGER" => GetMessage("MAIN_UL_P_WORK_PAGER"),
		"WORK_COUNTRY" => GetMessage("MAIN_UL_P_WORK_COUNTRY"),
		"WORK_STATE" => GetMessage("MAIN_UL_P_WORK_STATE"),
		"WORK_CITY" => GetMessage("MAIN_UL_P_WORK_CITY"),
		"WORK_ZIP" => GetMessage("MAIN_UL_P_WORK_ZIP"),
		"WORK_STREET" => GetMessage("MAIN_UL_P_WORK_STREET"),
		"WORK_MAILBOX" => GetMessage("MAIN_UL_P_WORK_MAILBOX"),
	);

}

$arNameTemplate = array(
	'#LAST_NAME# #NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_JOHN'),
	'#LAST_NAME# #NAME# #SECOND_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_JOHN_LLOYD'),
	'#LAST_NAME#, #NAME# #SECOND_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_COMMA_JOHN_LLOYD'),
	'#NAME# #SECOND_NAME# #LAST_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_JOHN_LLOYD_SMITH'),
	'#NAME_SHORT# #SECOND_NAME_SHORT# #LAST_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_J_L_SMITH'),
	'#NAME_SHORT# #LAST_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_J_SMITH'),
	'#LAST_NAME# #NAME_SHORT#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_J'),
	'#LAST_NAME# #NAME_SHORT# #SECOND_NAME_SHORT#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_J_L'),
	'#LAST_NAME#, #NAME_SHORT#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_COMMA_J'),
	'#LAST_NAME#, #NAME_SHORT# #SECOND_NAME_SHORT#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SMITH_COMMA_J_L'),
	'#NAME# #LAST_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_JOHN_SMITH'),
	'#NAME# #SECOND_NAME_SHORT# #LAST_NAME#' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_JOHN_L_SMITH'),
	'' => GetMessage('MAIN_UL_P_NAME_TEMPLATE_SITE')
);

$arComponentParameters = array(
	"PARAMETERS" => array(
		"CACHE_TIME" => array("DEFAULT"=>"7200"),
		"ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("MAIN_UL_P_USER_ID"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "",
		),
		'NAME_TEMPLATE' => array(
			'TYPE' => 'LIST',
			'NAME' => GetMessage('MAIN_UL_P_NAME_TEMPLATE'),
			'VALUES' => $arNameTemplate,
			'MULTIPLE' => 'N',
			'ADDITIONAL_VALUES' => 'Y',
			'DEFAULT' => "",
			'PARENT' => 'BASE',
		),
		'SHOW_LOGIN' => array(
			'TYPE' => 'CHECKBOX',
			'NAME' => GetMessage('MAIN_UL_P_SHOW_LOGIN'),
			'VALUE' => 'Y',
			'DEFAULT' => 'Y',
			'PARENT' => 'BASE',
		),
		'USE_THUMBNAIL_LIST' => array(
			'TYPE' => 'CHECKBOX',
			'NAME' => GetMessage('MAIN_UL_P_USE_THUMBNAIL_SMALL'),
			'DEFAULT' => 'Y',
			'PARENT' => 'BASE',
			"REFRESH" => 'Y'
		),
	)
);

if ($arCurrentValues["USE_THUMBNAIL_LIST"] == "Y"):
	$arComponentParameters["PARAMETERS"]["THUMBNAIL_LIST_SIZE"] = array(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("MAIN_UL_P_THUMBNAIL_SIZE_SMALL"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "30",
	);
endif;

if ($bSocNet && CModule::IncludeModule('socialnetwork')):

	if (is_array($arCurrentValues['SHOW_FIELDS']) && in_array("PERSONAL_PHOTO", $arCurrentValues['SHOW_FIELDS'])):
		$arComponentParameters["PARAMETERS"]["THUMBNAIL_DETAIL_SIZE"] = array(
			"PARENT" => "VISUAL",
			"NAME" => GetMessage("MAIN_UL_P_THUMBNAIL_SIZE_LARGE"),
			"TYPE" => "STRING",
			"MULTIPLE" => "N",
			"DEFAULT" => "100",
		);
	endif;

	if (IsModuleInstalled('intranet'))
	{
		$arUserFieldsDef = array(
			"EMAIL",
			"PERSONAL_MOBILE",
			"WORK_PHONE",
			"PERSONAL_ICQ",
			"PERSONAL_PHOTO",
			"PERSONAL_CITY",
			"WORK_COMPANY",
			"WORK_POSITION",
		);
		$arUserPropsDef = array(
			"UF_DEPARTMENT",
			"UF_PHONE_INNER",
		);
	}
	else
	{
		$arUserFieldsDef = array(
			"PERSONAL_ICQ",
			"PERSONAL_BIRTHDAY",
			"PERSONAL_PHOTO",
			"PERSONAL_CITY",
			"WORK_COMPANY",
			"WORK_POSITION"
		);
		$arUserPropsDef = array();
	}

	$arComponentParameters["PARAMETERS"]["SHOW_FIELDS"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("MAIN_UL_P_SHOW_FIELDS"),
		"TYPE" => "LIST",
		"VALUES" => $userProp1,
		"MULTIPLE" => "Y",
		"DEFAULT" => $arUserFieldsDef,
		"REFRESH" => 'Y'
	);
	$arComponentParameters["PARAMETERS"]["USER_PROPERTY"] = array(
		"PARENT" => "BASE",
		"NAME" => GetMessage("MAIN_UL_P_USER_PROPERTY"),
		"TYPE" => "LIST",
		"VALUES" => $userProp,
		"MULTIPLE" => "Y",
		"DEFAULT" => $arUserPropsDef,
	);
	$arComponentParameters["PARAMETERS"]["PATH_TO_SONET_USER_PROFILE"] = array(
		"NAME" => GetMessage("MAIN_UL_P_PATH_TO_SONET_USER_PROFILE"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"COLS" => 25,
		"PARENT" => "URL_TEMPLATES",
	);
	$arComponentParameters["PARAMETERS"]["PROFILE_URL"] = array(
		"NAME" => GetMessage("MAIN_UL_P_PROFILE_URL"),
		"TYPE" => "STRING",
		"MULTIPLE" => "N",
		"DEFAULT" => "",
		"COLS" => 25,
		"PARENT" => "BASE",
	);
	$arComponentParameters["PARAMETERS"]["DATE_TIME_FORMAT"] = array(
		"PARENT" => "VISUAL",
		"NAME" => GetMessage("MAIN_UL_P_DATE_TIME_FORMAT"),
		"TYPE" => "LIST",
		"VALUES" => CSocNetTools::GetDateTimeFormat(),
		"MULTIPLE" => "N",
		"DEFAULT" => $GLOBALS["DB"]->DateFormatToPHP(CSite::GetDateFormat("FULL")),
		"ADDITIONAL_VALUES" => "Y",
	);
	$arComponentParameters["PARAMETERS"]["SHOW_YEAR"] = array(
		"PARENT" => "ADDITIONAL_SETTINGS",
		"NAME" => GetMessage("MAIN_UL_P_SHOW_YEAR"),
		"TYPE" => "LIST",
		"VALUES" => array(
			"Y" => GetMessage("MAIN_UL_P_SHOW_YEAR_VALUE_Y"),
			"M" => GetMessage("MAIN_UL_P_SHOW_YEAR_VALUE_M"),
			"N" => GetMessage("MAIN_UL_P_SHOW_YEAR_VALUE_N")
		),
		"MULTIPLE" => "N",
		"DEFAULT" => "Y"
	);

endif;
?>