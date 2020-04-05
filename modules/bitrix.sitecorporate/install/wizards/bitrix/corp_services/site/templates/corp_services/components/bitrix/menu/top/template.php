<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
<ul id="top-menu">
<?foreach($arResult as $arItem):?>
	<?if($arItem["SELECTED"]):?>
		<li class="selected"><b class="r1"></b><b class="r0"></b><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a><b class="r0"></b><b class="r1"></b></li>
	<?else:?>
		<li><b class="r1"></b><b class="r0"></b><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a><b class="r0"></b><b class="r1"></b></li>
	<?endif?>
	
<?endforeach?>
</ul>		
<?endif?>