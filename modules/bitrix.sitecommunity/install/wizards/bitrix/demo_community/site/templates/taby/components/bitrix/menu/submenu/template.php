<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? if (!empty($arResult)): ?>
<ul id="submenu">
	<? foreach($arResult as $arItem): ?>
		<? if ($arItem["PERMISSION"] > "D"): ?>
			<li <? if ($arItem["SELECTED"]) { ?>class="selected"<? } ?>><a href="<?= $arItem["LINK"]?>"><span><?= $arItem["TEXT"]?></span></a></li>
		<? endif; ?>
	<? endforeach; ?>
</ul>					
<div id="submenu-border"></div>
<?endif; ?>