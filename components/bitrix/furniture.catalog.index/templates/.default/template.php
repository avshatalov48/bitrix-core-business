<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
?>
<?
if (is_array($arResult['ITEMS']) && count($arResult['ITEMS']) > 0):
?>
<div class="product-list">
<?
	foreach ($arResult['ITEMS'] as $arItem):
?>
	<div class="product">
		<div class="product-overlay"></div>
		<div class="product-image"<?if($arItem['PICTURE']['SRC']):?> style="background-image: url(<?=$arItem['PICTURE']['SRC']?>)"<?endif;?>></div>
		<a class="product-desc" href="<?=$arItem['DETAIL_URL']?>">
			<b><?=$arItem['NAME']?></b>
			<p><?=$arItem['DESCRIPTION']?></p>
		</a>
	</div>
<?
	endforeach;
?>
</div>
<?
endif;
?>
