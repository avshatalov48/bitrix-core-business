<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
//$this->setFrameMode(true);
?>
<?foreach($arResult["ITEMS"] as $item):
	if($arParams["DISPLAY_EMPTY"] != "Y" && !$item['VALUE']) continue;
?>
<?if($arParams["DISPLAY_NAME"]!="N"):?><?=htmlspecialcharsbx($item['NAME'])?>: <?endif;?>
<?=htmlspecialcharsbx($item['VALUE'])?><br />

<?endforeach;?>
