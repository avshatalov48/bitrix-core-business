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

?>

<div class="mp_top_nav">
	<ul class="mp_top_nav_ul">
<?php
foreach($arResult["ITEMS"] as $index => $arItem):
?>
		<li class="mp_top_nav_ul_li <?=$arItem["PARAMS"]["class"]?> <?php if($arItem["SELECTED"] && !isset($_GET["app"]) && !isset($_GET["category"])): ?>active<?php endif; ?>">
			<a href="<?=($arItem["PARAMS"]["class"] == "category" ? "javascript:void(0)" : $arItem["LINK"])?>" <? if($arItem["PARAMS"]["class"] == "category"): ?>onclick="BX.addClass(this.parentNode, 'active');ShowCategoriesPopup(this);"<? endif; ?>>
				<span class="leftborder"></span><span class="icon"></span><?=$arItem["TEXT"]?>
<?php
	if($arItem["PARAMS"]["class"] == "category"):
?>
				<span class="arrow"></span>
<?php
	elseif($arItem["PARAMS"]["class"] == "updates"):
?>
				<span id="menu_num_updates">
<?php
		if($arResult['NUM_UPDATES'] > 0):
?>
					(<?=$arResult['NUM_UPDATES']?>)
<?php
		endif;
?>
				</span>
<?php
	elseif($arItem["PARAMS"]["class"] == "sale" && $arResult["UNINSTALLED_PAID_APPS_COUNT"] > 0):
?>
				<span>(<?=$arResult["UNINSTALLED_PAID_APPS_COUNT"]?>)</span>
<?
	endif;
?>
				<span class="rightborder"></span>
			</a>
		</li>
<?php
endforeach;
?>
	</ul>
<?php
$APPLICATION->IncludeComponent("bitrix:rest.marketplace.search", "",
	array(
		"SEARCH_URL" => $arParams["SEARCH_URL"],
	),
	$component
);
?>
</div>

<script type="text/javascript">
	function ShowCategoriesPopup(bindElement)
	{
<?php
if(is_array($arResult['CATEGORY_LIST']) && !empty($arResult['CATEGORY_LIST'])):
?>
		BX.PopupMenu.show("mp_categories", bindElement, [
<?php
	foreach($arResult['CATEGORY_LIST'] as $category):
?>
			{
				text: "<?=CUtil::JSEscape(htmlspecialcharsbx($category["NAME"]))?>",
				className: "menu-popup-no-icon",
				href: "<?=CUtil::JSEscape(htmlspecialcharsbx(str_replace("#category#", $category["CODE"], $arParams["CATEGORY_URL_TPL"])))?>"
			},
<?php
	endforeach;
?>
			{
				text: "<?=GetMessage("MARKETPLACE_CATEGORY_ALL")?>",
				className: "menu-popup-no-icon",
				href: "<?=CUtil::JSEscape(htmlspecialcharsbx(str_replace("#category#", "all", $arParams["CATEGORY_URL_TPL"])))?>"
			}
		],
		{
			offsetTop: 0,
			offsetLeft: 43,
			angle: true,
			events: {
				onPopupClose: function()
				{
					BX.removeClass(this.bindElement.parentNode, "active");
				}
			}
		});
<?
endif;
?>
	}
</script>

