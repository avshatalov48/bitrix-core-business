<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule('advertising'))
	return;

__IncludeLang(GetLangFileName(dirname(__FILE__)."/lang/", '/'.basename(__FILE__)));	

//Matrix
$arWeekday = Array(
	"SUNDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"MONDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"TUESDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"WEDNESDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"THURSDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"FRIDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23),
	"SATURDAY" => Array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23)
);


$contractId  = false; 

$rsADV = CAdvContract::GetList($v1="s_sort", $v2="desc", array("NAME" => 'Default', 'DESCRIPTION' => GetMessage("CONTRACT_DESC")." [".WIZARD_SITE_ID."]"), $is_filtered);
if ($arADV = $rsADV->Fetch())
{
	$contractId  = $arADV["ID"];
	if (WIZARD_INSTALL_DEMO_DATA)
	{
		CAdvContract::Delete($arADV["ID"]); 
		$contractId  = false; 
	}
}
if ($contractId == false){

	//$ac = new CAdvContract();
	$arFields = array(
		'ACTIVE' => 'Y',
		'NAME' => 'Default',
		'SORT' => 1000,
		'DESCRIPTION' => GetMessage("CONTRACT_DESC")." [".WIZARD_SITE_ID."]",
		'EMAIL_COUNT' => 1,
		'arrTYPE' => array('ALL'),
		'arrWEEKDAY' => $arWeekday,
		'arrSITE' => Array(WIZARD_SITE_ID),
	);
	$contractId = CAdvContract::Set($arFields, 0, 'N');
		
	//Types
	$arTypes = Array(
		Array(
			"SID" => "TOP",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"NAME" => GetMessage("DEMO_ADV_TOP_TYPE"),
			"DESCRIPTION" => ""
		),
		Array(
			"SID" => "LEFT1",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"NAME" => GetMessage("DEMO_ADV_LEFT1_TYPE"),
			"DESCRIPTION" => ""
		),
		Array(
			"SID" => "LEFT2",
			"ACTIVE" => "Y",
			"SORT" => 1,
			"NAME" => GetMessage("DEMO_ADV_LEFT2_TYPE"),
			"DESCRIPTION" => ""
		),
	);
	
	foreach ($arTypes as $arFields)
	{
		$dbResult = CAdvType::GetByID($arTypes["SID"], $CHECK_RIGHTS="N");
		if ($dbResult && $dbResult->Fetch())
			continue;
	
		CAdvType::Set($arFields, "", $CHECK_RIGHTS="N");
	}
	
	$pathToBanner = str_replace("\\", "/", dirname(__FILE__));
	$lang = (in_array(LANGUAGE_ID, array("ru", "en", "de"))) ? LANGUAGE_ID : \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);
	$pathToBanner = $pathToBanner."/lang/".$lang;
	
	$arBanners = Array(
		Array(
			"CONTRACT_ID" => $contractId,
			"TYPE_SID" => "TOP",
			"STATUS_SID"		=> "PUBLISHED",
			"NAME" => GetMessage("DEMO_ADV_980_1_NAME"),
			"ACTIVE" => "Y",
			"arrSITE" => Array(WIZARD_SITE_ID),
			"WEIGHT"=> 100,
			"FIX_SHOW" => "N",
			"FIX_CLICK" => "Y",
			"AD_TYPE" => "image",
			"arrIMAGE_ID" => Array(
				"name" => "banner980_1.jpg",
				"type" => "image/gif",
				"tmp_name" => $pathToBanner."/banner980_1.jpg",
				"error" => "0",
				"size" => @filesize($pathToBanner."/banner980_1.jpg"),
				"MODULE_ID" => "advertising"
			),
			"IMAGE_ALT" => GetMessage("DEMO_ADV_980_1_NAME"),
			"URL" => GetMessage("DEMO_ADV_BANNER_URL1"),
			"URL_TARGET" => "_blank",
			"STAT_EVENT_1" => "banner",
			"STAT_EVENT_2" => "click",
			"arrWEEKDAY" => $arWeekday,
			"COMMENTS" => "banner980_1.jpg for " . WIZARD_SITE_ID,
		),
		Array(
			"CONTRACT_ID" => $contractId,
			"TYPE_SID" => "LEFT1",
			"STATUS_SID"		=> "PUBLISHED",
			"NAME" => GetMessage("DEMO_ADV_LEFT_1_NAME"),
			"ACTIVE" => "Y",
			"FIX_SHOW" => "N",
			"FIX_CLICK" => "Y",
			"arrSITE" => Array(WIZARD_SITE_ID),
			"WEIGHT"=> 100,
			"AD_TYPE" => "image",
			"arrIMAGE_ID" => Array(
				"name" => "left1.jpg",
				"type" => "image/gif",
				"tmp_name" => $pathToBanner."/left1.jpg",
				"error" => "0",
				"size" => @filesize($pathToBanner."/left1.jpg"),
				"MODULE_ID" => "advertising"
			),
			"IMAGE_ALT" => GetMessage("DEMO_ADV_LEFT_1_NAME"),
			"URL" => GetMessage("DEMO_ADV_BANNER_URL2"),
			"URL_TARGET" => "_blank",
			"STAT_EVENT_1" => "banner",
			"STAT_EVENT_2" => "click",
			"arrWEEKDAY" => $arWeekday,
			"COMMENTS" => "left1.jpg for " . WIZARD_SITE_ID,
		),
	
		Array(
			"CONTRACT_ID" => $contractId,
			"TYPE_SID" => "LEFT2",
			"STATUS_SID"		=> "PUBLISHED",
			"NAME" => GetMessage("DEMO_ADV_LEFT_2_NAME"),
			"ACTIVE" => "Y",
			"FIX_SHOW" => "N",
			"FIX_CLICK" => "Y",
			"arrSITE" => Array(WIZARD_SITE_ID),
			"WEIGHT"=> 100,
			"AD_TYPE" => "image",
			"arrIMAGE_ID" => Array(
				"name" => "left2.jpg",
				"type" => "image/gif",
				"tmp_name" => $pathToBanner."/left2.jpg",
				"error" => "0",
				"size" => @filesize($pathToBanner."/left2.jpg"),
				"MODULE_ID" => "advertising"
			),
			"IMAGE_ALT" => GetMessage("DEMO_ADV_LEFT_2_NAME"),
			"URL" => GetMessage("DEMO_ADV_BANNER_URL3"),
			"URL_TARGET" => "_blank",
			"STAT_EVENT_1" => "banner",
			"STAT_EVENT_2" => "click",
			"arrWEEKDAY" => $arWeekday,
			"COMMENTS" => "left2.jpg for " . WIZARD_SITE_ID,
		),
	
	);
	
	foreach ($arBanners as $arFields)
	{
		$dbResult = CAdvBanner::GetList($by, $order, Array("COMMENTS" => $arFields["COMMENTS"], "COMMENTS_EXACT_MATCH" => "Y"), $is_filtered, "N");
		if ($dbResult && $dbResult->Fetch())
			continue;
	
		CAdvBanner::Set($arFields, "", $CHECK_RIGHTS="N");
	}
}
?>