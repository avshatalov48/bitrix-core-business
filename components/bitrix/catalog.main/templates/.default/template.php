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
$this->setFrameMode(true);
?>
<div class="catalog-main">
<?foreach($arResult["ITEMS"] as $arItem):?>
	<p>
	<?if(is_array($arItem["PICTURE"])):?>
		<img style="vertical-align:middle;" border="0" src="<?=$arItem["PICTURE"]["SRC"]?>" width="<?=$arItem["PICTURE"]["WIDTH"]?>" height="<?=$arItem["PICTURE"]["HEIGHT"]?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>" />
	<?endif?>
	<?if($arItem["LIST_PAGE_URL"]):?>
		<a style="vertical-align:middle;" href="<?=$arItem["LIST_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
	<?else:?>
		<span style="vertical-align:middle;"><?=$arItem["NAME"]?></span>
	<?endif?>
	</p>
<?endforeach?>
</div>
