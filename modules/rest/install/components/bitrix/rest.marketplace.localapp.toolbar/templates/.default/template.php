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
 */

$this->SetViewTarget('pagetitle', 100);

foreach($arResult["ITEMS"] as $index => $arItem):
?>
	<a href="<?=\Bitrix\Main\Text\Converter::getHtmlConverter()->encode($arItem["LINK"])?>" class="webform-small-button <?=$arItem["PARAMS"]["class"]?>"><?=$arItem["TEXT"]?></a>
<?php
endforeach;

$this->EndViewTarget();
?>
