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

foreach($arResult["ITEMS"] as $index => $item):
	if(isset($item['MENU'])):
?>
		<a href="javascript:void(0)" class="webform-small-button <?=$item["PARAMS"]["class"]?>" onclick="BX.PopupMenu.show('rest_hook_menu', this, <?=CUtil::PhpToJSObject($item['MENU'])?>)"><?=$item["TEXT"]?></a>
<?php
	else:
?>
	<a href="<?=\Bitrix\Main\Text\Converter::getHtmlConverter()->encode($item["LINK"])?>" class="webform-small-button <?=$item["PARAMS"]["class"]?>"><?=$item["TEXT"]?></a>
<?php
	endif;
endforeach;

$this->EndViewTarget();
?>
