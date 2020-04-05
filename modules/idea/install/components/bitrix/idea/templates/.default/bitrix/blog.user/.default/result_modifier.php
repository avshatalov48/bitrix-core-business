<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
__IncludeLang(dirname(__FILE__)."/lang/".LANGUAGE_ID."/result_modifier.php");
$arResult["USER_IDEA_LINK"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_IDEAS"], array("user_id" => $arResult["arUser"]["ID"]));

//Prepare data
if($arResult["User"]["PERSONAL_COUNTRY"]>0)
	$arResult["User"]["PERSONAL_COUNTRY"] = GetCountryByID($arResult["User"]["PERSONAL_COUNTRY"]);
else
	$arResult["User"]["PERSONAL_COUNTRY"] = "";
if($arResult["User"]["WORK_COUNTRY"]>0)
	$arResult["User"]["WORK_COUNTRY"] = GetCountryByID($arResult["User"]["WORK_COUNTRY"]);
else
	$arResult["User"]["WORK_COUNTRY"] = "";
if(strlen($arResult["User"]["PERSONAL_GENDER"])>0)
	$arResult["User"]["PERSONAL_GENDER"] = $arResult["arSex"][$arResult["User"]["PERSONAL_GENDER"]];

if(strlen($arResult["User"]["LAST_ACTIVITY_DATE"])>0 && strlen($arParams["DATE_TIME_FORMAT"])>0)
	$arResult["User"]["LAST_ACTIVITY_DATE"] = date($arParams["DATE_TIME_FORMAT"], strtotime($arResult["User"]["LAST_ACTIVITY_DATE"]));


//prepate titles
$arResult["DISPLAY_FIELDS"] = array();
$arResult["DISPLAY_FIELDS"]['FIELDS_MAIN_DATA'] = array(
	"LAST_ACTIVITY_DATE" => GetMessage("IDEA_USER_INFO_LAST_ACTIVITY_DATE_TITLE"),
	"PERSONAL_CITY" => GetMessage("IDEA_USER_INFO_PERSONAL_CITY_TITLE"),
	"WORK_COMPANY" => GetMessage("IDEA_USER_INFO_WORK_COMPANY_TITLE"),
);

$arResult["DISPLAY_FIELDS"]['FIELDS_CONTACT_DATA'] = array(
	"PERSONAL_PHONE" => GetMessage("IDEA_USER_INFO_PERSONAL_PHONE_TITLE"),
	"PERSONAL_CITY" => GetMessage("IDEA_USER_INFO_PERSONAL_CITY_TITLE"),
	"PERSONAL_STATE" => GetMessage("IDEA_USER_INFO_PERSONAL_STATE_TITLE"),
	"PERSONAL_COUNTRY" => GetMessage("IDEA_USER_INFO_PERSONAL_COUNTRY_TITLE"),
	"WORK_COMPANY" => GetMessage("IDEA_USER_INFO_WORK_COMPANY_TITLE"),
	"WORK_POSITION" => GetMessage("IDEA_USER_INFO_WORK_POSITION_TITLE"),
	"WORK_WWW" => GetMessage("IDEA_USER_INFO_WORK_WWW_TITLE"),
	"WORK_PHONE" => GetMessage("IDEA_USER_INFO_WORK_PHONE_TITLE"),
	"WORK_CITY" => GetMessage("IDEA_USER_INFO_WORK_CITY_TITLE"),
	"WORK_STATE" => GetMessage("IDEA_USER_INFO_WORK_STATE_TITLE"),
	"WORK_COUNTRY" => GetMessage("IDEA_USER_INFO_WORK_COUNTRY_TITLE"),
	"WORK_PROFILE" => GetMessage("IDEA_USER_INFO_WORK_PROFILE_TITLE"),
);

$arResult["DISPLAY_FIELDS"]['FIELDS_PERSONAL_DATA'] = array(
	"DATE_REGISTER" => GetMessage("IDEA_USER_INFO_DATE_REGISTER_TITLE"),
	"PERSONAL_GENDER" => GetMessage("IDEA_USER_INFO_PERSONAL_GENDER_TITLE"),
	"PERSONAL_BIRTHDATE" => GetMessage("IDEA_USER_INFO_PERSONAL_BIRTHDATE_TITLE"),
	"SECOND_NAME" => GetMessage("IDEA_USER_INFO_SECOND_NAME_TITLE"),
);

?>