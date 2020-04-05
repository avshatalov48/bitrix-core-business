<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<ul id="top-menu-list">
	<?foreach($arResult as $arItem):?>
		<?if ($arItem["PERMISSION"] > "D"):?>
			<li<?if ($arItem["SELECTED"]):?> class="selected"<?endif?>><span><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></span></li>
		<?endif?>	
	<?endforeach?>
</ul>