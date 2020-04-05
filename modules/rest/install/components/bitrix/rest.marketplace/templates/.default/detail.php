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
if(!$arResult['SLIDER'])
{
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
	), $component);
}*/
?>
	<div class="mp_section">
<?php
if($arResult['SLIDER'])
{
	$APPLICATION->IncludeComponent(
		'bitrix:rest.marketplace.iframe',
		'',
		array(
			'COMPONENT_NAME' => 'bitrix:rest.marketplace.detail',
			'COMPONENT_TEMPLATE_NAME' => '',
			'COMPONENT_PARAMS' => array(
				"APP" => $arResult["VARIABLES"]["app"],
				"APPLICATION_PAGE" => $arParams["APPLICATION_PAGE"],
			),
		),
		$component
	);
}
else
{
	$APPLICATION->IncludeComponent("bitrix:rest.marketplace.detail", "", array(
		"APP" => $arResult["VARIABLES"]["app"],
		"APPLICATION_PAGE" => $arParams["APPLICATION_PAGE"],
	));
}
?>
	</div>
</div>
