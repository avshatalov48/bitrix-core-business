<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<?if (!empty($arResult)):?>
	<ul id="user-menu">
<?foreach($arResult as $arItem):?>
	<?if ($arItem["PERMISSION"] > "D"):?>
		<li<?if ($arItem["SELECTED"]):?> class="selected"<?endif?>>
			<b class="r2"></b>
			<b class="r1"></b>
			<b class="r0"></b>
			<a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a>
			<b class="r0"></b>
			<b class="r1"></b>
			<b class="r2"></b>
		</li>
	<?endif?>
<?endforeach?>

	</ul>
<?endif?>