<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
?>

<div class="mp_wrap">
<?php/*
$APPLICATION->IncludeComponent("bitrix:rest.marketplace.toolbar", '', array(
	"COMPONENT_PAGE" => $arParams["COMPONENT_PAGE"],
	"TOP_URL" => $arParams["TOP_URL"],
	"CATEGORY_URL" => $arParams["CATEGORY_URL"],
	"DETAIL_URL" => $arParams["DETAIL_URL"],
	"SEARCH_URL" => $arParams["SEARCH_URL"],
	"BUY_URL" => $arParams["BUY_URL"],
	"UPDATES_URL" => $arParams["UPDATES_URL"],
	"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
	"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
), $component);*/
?>
	<div class="mp_section">
<?php
$APPLICATION->IncludeComponent("bitrix:rest.marketplace.top", "", array(
	"ACTION" => "get_dev",
	"TITLE" => GetMessage("MARKETPLACE_DEV"),
	"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
	"BUY_URL" => $arParams["BUY_URL"],
), $component);

$APPLICATION->IncludeComponent("bitrix:rest.marketplace.top", "", array(
	"ACTION" => "get_last",
	"TITLE" => GetMessage("MARKETPLACE_NEW"),
	"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
	"BUY_URL" => $arParams["BUY_URL"],
), $component);

$APPLICATION->IncludeComponent("bitrix:rest.marketplace.top", "", array(
	"ACTION" => "get_best",
	"TITLE" => GetMessage("MARKETPLACE_BEST"),
	"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
	"BUY_URL" => $arParams["BUY_URL"],
), $component);
?>
	</div>
</div>
<script>
	BX.rest.Marketplace.bindPageAnchors({allowChangeHistory: true});
</script>

