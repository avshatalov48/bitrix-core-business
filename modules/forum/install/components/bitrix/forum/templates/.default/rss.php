<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams["RSS_TYPE_RANGE"] = $arParams["RSS_TYPE_RANGE"] ?? [];
$arResult["TYPE"] = $arResult["TYPE"] ?? 'default';
if (count($arParams["RSS_TYPE_RANGE"]) === 1)
{
	$arResult["TYPE"] = reset($arParams["RSS_TYPE_RANGE"]);
}

if ($arResult["TYPE"] === "default" && count($arParams["RSS_TYPE_RANGE"]) > 1)
{
?>
	<div class="forum-header-box">
		<div class="forum-header-title"><span><?=GetMessage("F_TITLE_RSS")?></span></div>
	</div>
	<div class="forum-info-box forum-subscribes-rss">
		<div class="forum-info-box-inner"><span style="float:left;"><?=GetMessage("F_CHECK_RSS_TYPE")?>:&nbsp;&nbsp;</span>
	<?php
	$APPLICATION->IncludeComponent(
		"bitrix:forum.rss",
		"",
		array(
			"TYPE" => $arResult["TYPE"],
			"IID" => $arResult["IID"],
			"MODE" => "link",
			"MODE_DATA" => $arResult["MODE"],

			"USE_RSS" => $arParams["USE_RSS"],
			"RSS_CACHE" => $arParams["RSS_CACHE"],
			"TYPE_RANGE" => $arParams["RSS_TYPE_RANGE"],
			"FID_RANGE" => $arParams["RSS_FID_RANGE"],
			"YANDEX" => $arParams["RSS_YANDEX"],
			"TN_TITLE" => $arParams["RSS_TN_TITLE"],
			"TN_DESCRIPTION" => $arParams["RSS_TN_DESCRIPTION"],
			"TN_TEMPLATE" => $arParams["RSS_TN_TEMPLATE"],
			"COUNT" => $arParams["RSS_COUNT"],
			"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
			"USER_FIELDS" => $arParams["USER_FIELDS"],

			"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"],
			"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
			"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
			"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
			"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
			"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],

			"CACHE_TYPE" => $arResult["CACHE_TYPE"],
			"CACHE_TIME" => $arResult["CACHE_TIME"],

			"SEO_USER" => $arParams["SEO_USER"]
		),
		$component
	);
	?>
			<div class="forum-clear-float"></div>
		</div>
	</div>
	<?php

	if ($arParams["SET_TITLE"] == "Y")
	{
		$GLOBALS["APPLICATION"]->SetTitle(GetMessage("F_TITLE_RSS"));
	}
	if ($arParams["SET_NAVIGATION"] != "N")
	{
		$APPLICATION->AddChainItem(GetMessage("F_TITLE_RSS"));
	}
	return false;
}


?><?php $APPLICATION->IncludeComponent(
	"bitrix:forum.rss",
	"",
	array(
		"TYPE" => $arResult["TYPE"],
		"IID" => $arResult["IID"],
		"MODE" => $arResult["MODE"],
		
		"USE_RSS" => $arParams["USE_RSS"],
		"TYPE_RANGE" => $arParams["RSS_TYPE_RANGE"],
		"FID_RANGE" => $arParams["RSS_FID_RANGE"],
		"YANDEX" => $arParams["RSS_YANDEX"],
		"TN_TITLE" => $arParams["RSS_TN_TITLE"],
		"TN_DESCRIPTION" => $arParams["RSS_TN_DESCRIPTION"],
		"TN_TEMPLATE" => $arParams["RSS_TN_TEMPLATE"],
		"COUNT" => $arParams["RSS_COUNT"],
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"],
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
		
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"],
		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"],
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"],
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"],
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"],
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"],
		
		"CACHE_TYPE" => $arResult["CACHE_TYPE"],
		"CACHE_TIME" => $arParams["RSS_CACHE"],

		"SEO_USER" => $arParams["SEO_USER"]
	),
	$component
);
