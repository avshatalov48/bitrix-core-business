<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
	<ul id="top-menu">
<?foreach($arResult as $arItem):?>
	<?if ($arItem["PERMISSION"] > "D"):?>
		<li<?if ($arItem["SELECTED"]):?> class="selected"<?endif?>><a href="<?=$arItem["LINK"]?>"><b class="r1"></b><b class="r0"></b><span><?=$arItem["TEXT"]?></span></a></li>
	<?endif?>
<?endforeach?>

	</ul>
<?endif?>