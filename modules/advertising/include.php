<?php

global $DB, $MESS;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/errors.php");


$GLOBALS["CACHE_ADVERTISING"] = Array(
	"BANNERS_ALL" => Array(),
	"BANNERS_CNT" => Array(),
	"CONTRACTS_ALL" => Array(),
	"CONTRACTS_CNT" => Array(),
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/advertising/classes/mysql/advertising.php");

\CJSCore::RegisterExt("adv_templates", Array(
	"js" =>    "/bitrix/js/advertising/template.js",
	"rel" =>   array()
));
