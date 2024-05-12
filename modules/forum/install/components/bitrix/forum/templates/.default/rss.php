<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) { die(); }

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
			"TYPE" => $arResult["TYPE"] ?? null,
			"IID" => $arResult["IID"] ?? null,
			"MODE" => "link",
			"MODE_DATA" => $arResult["MODE"] ?? null,

			"USE_RSS" => $arParams["USE_RSS"] ?? null,
			"RSS_CACHE" => $arParams["RSS_CACHE"] ?? null,
			"TYPE_RANGE" => $arParams["RSS_TYPE_RANGE"] ?? null,
			"FID_RANGE" => $arParams["RSS_FID_RANGE"] ?? null,
			"YANDEX" => $arParams["RSS_YANDEX"] ?? null,
			"TN_TITLE" => $arParams["RSS_TN_TITLE"] ?? null,
			"TN_DESCRIPTION" => $arParams["RSS_TN_DESCRIPTION"] ?? null,
			"TN_TEMPLATE" => $arParams["RSS_TN_TEMPLATE"] ?? null,
			"COUNT" => $arParams["RSS_COUNT"] ?? null,
			"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"] ?? null,
			"USER_FIELDS" => $arParams["USER_FIELDS"] ?? null,

			"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"] ?? null,
			"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"] ?? null,
			"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
			"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
			"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
			"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

			"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
			"CACHE_TIME" => $arResult["CACHE_TIME"] ?? null,

			"SEO_USER" => $arParams["SEO_USER"] ?? null
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
		"TYPE" => $arResult["TYPE"] ?? null,
		"IID" => $arResult["IID"] ?? null,
		"MODE" => $arResult["MODE"] ?? null,

		"USE_RSS" => $arParams["USE_RSS"] ?? null,
		"TYPE_RANGE" => $arParams["RSS_TYPE_RANGE"] ?? null,
		"FID_RANGE" => $arParams["RSS_FID_RANGE"] ?? null,
		"YANDEX" => $arParams["RSS_YANDEX"] ?? null,
		"TN_TITLE" => $arParams["RSS_TN_TITLE"] ?? null,
		"TN_DESCRIPTION" => $arParams["RSS_TN_DESCRIPTION"] ?? null,
		"TN_TEMPLATE" => $arParams["RSS_TN_TEMPLATE"] ?? null,
		"COUNT" => $arParams["RSS_COUNT"] ?? null,
		"DATE_TIME_FORMAT" =>  $arResult["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,

		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"] ?? null,
		"URL_TEMPLATES_INDEX" =>  $arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_LIST" =>  $arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ" => $arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW" => $arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"CACHE_TYPE" => $arResult["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["RSS_CACHE"] ?? null,

		"SEO_USER" => $arParams["SEO_USER"] ?? null
	),
	$component
);
