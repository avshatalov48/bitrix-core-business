<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<? if (!empty($arResult)): ?>
	<div id="submenu">
		<div id="submenu-items">
			<ul id="submenu-list">
				<? foreach($arResult as $arItem): ?>
					<? if ($arItem["PERMISSION"] > "D"): ?>
						<li <? if ($arItem["SELECTED"]) { ?>class="selected"<? } ?>><a href="<?= $arItem["LINK"]?>"><span><?= $arItem["TEXT"]?></span></a></li>
					<? endif; ?>
				<? endforeach; ?>
			</ul>
		</div>
	</div>
<? endif; ?>