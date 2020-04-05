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
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

?>

<div class="mp_wrap">
	<div class="mp_section">
		<?
		$APPLICATION->IncludeComponent("bitrix:rest.marketplace.installed", "", array(
			"DETAIL_URL_TPL" => $arParams["DETAIL_URL_TPL"],
			"CATEGORY_URL_TPL" => $arParams["CATEGORY_URL_TPL"],
			//"SEF_MODE" => $arParams["SEF_MODE"],
		), $component);
		?>
	</div>
</div>