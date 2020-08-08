<?
global $DB, $MESS, $DBType;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/filter_tools.php");
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/errors.php");


$GLOBALS["CACHE_ADVERTISING"] = Array(
	"BANNERS_ALL" => Array(),
	"BANNERS_CNT" => Array(),
	"CONTRACTS_ALL" => Array(),
	"CONTRACTS_CNT" => Array(),
);

/*
CModule::AddAutoloadClasses(
	"advertising",
	array(
		"CAdvBanner" => "classes/".$DBType."/advertising.php",
		"CAdvType" => "classes/".$DBType."/advertising.php",
		"CAdvContract" => "classes/".$DBType."/advertising.php",
		"CAdvertising" => "classes/".$DBType."/advertising.php",
	)
);
*/

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/classes/".mb_strtolower($DB->type)."/advertising.php");

\CJSCore::RegisterExt("adv_templates", Array(
	"js" =>    "/bitrix/js/advertising/template.js",
	"rel" =>   array()
));
?>
