<?php
/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
use \Bitrix\Main\Loader;
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Rest\Marketplace\Url;
$portalZoneId = (Loader::includeModule('bitrix24')) ? (CBitrix24::getPortalZone()) : 'ru';
\Bitrix\Main\Loader::includeModule('ui');
\Bitrix\Main\UI\Extension::load(array("ui.tilegrid", "ui.buttons"));
\CJSCore::init(["sidepanel"], "loader");

$arResult['SLIDER'] = \CRestUtil::isSlider();

if ($arParams['NO_BACKGROUND'] == "Y")
{
	$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
	$bodyClasses = 'pagetitle-toolbar-field-view no-all-paddings no-background mp-slider-view';
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
}
//region Category Items
$this->setViewTarget("rest.marketplace.category.items", 10);
if (is_array($arResult["ITEMS"]))
{
?>
<div class="rest-market-section">
	<? if($arParams['SECTION_TITLE']): ?>
		<div class="rest-market-grid-title rest-market-grid-title-border"><?=$arParams['SECTION_TITLE']?>
			<? if($arParams['SECTION_URL_PATH'] && $arParams['SECTION_SHOW_ALL_BTN_NAME']):?>
				<a class="rest-market-grid-title-btn-all" href="<?=$arParams['SECTION_URL_PATH']?>"><?=$arParams['SECTION_SHOW_ALL_BTN_NAME']?></a>
			<? endif;?>
		</div>
	<? endif;?>
	<div class="rest-market-grid">
		<div class="mp<? if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y"): ?> mp-slider<? endif; ?>">
			<div class="mp-container">
				<div class="mp-container" id="mp-elements-block"></div>
				<?if ($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
					<div class="mp-container-more">
						<span class="ui-btn ui-btn-light-border mp-btn-more" id="mp-more-button"><?=GetMessage("MARKETPLACE_MORE_APPS")?></span>
					</div>
				<?endif?>
			</div>
		</div>
	</div>
</div>

	<script>
		BX.ready(function () {
			window.gridTile = new BX.TileGrid.Grid(
				{
					id: 'mp_category',
					container: document.getElementById('mp-elements-block'),
					items: <?=CUtil::PhpToJSObject($arResult["ITEMS"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: 'BX.Rest.Marketplace.TileGrid.Item'
				}
			);
			gridTile.draw();
		});
	</script>
	<?
}
elseif (
	is_array($arResult["NEW_ITEMS_PAID"]) || is_array($arResult["NEW_ITEMS_FREE"])
	||is_array($arResult["TOP_ITEMS_PAID"]) || is_array($arResult["TOP_ITEMS_FREE"])
)
{
	?>
<div class="rest-market-section">
	<? if($arParams['SECTION_TITLE']): ?>
		<div class="rest-market-grid-title rest-market-grid-title-border"><?=$arParams['SECTION_TITLE']?>
			<? if($arParams['SECTION_URL_PATH'] && $arParams['SECTION_SHOW_ALL_BTN_NAME']):?>
				<a class="rest-market-grid-title-btn-all" href="<?=$arParams['SECTION_URL_PATH']?>"><?=$arParams['SECTION_SHOW_ALL_BTN_NAME']?></a>
			<? endif;?>
		</div>
	<? endif;?>
	<div class="rest-market-grid">
		<div class="mp<? if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y"): ?> mp-slider<? endif; ?>">
			<div class="mp-title"><?=GetMessage("MARKETPLACE_TITLE_NEW")?></div>

			<?if (is_array($arResult["NEW_ITEMS_PAID"]) && !empty($arResult["NEW_ITEMS_PAID"])):?>
				<div class="mp-container">
					<div class="mp-title"><?=GetMessage("MARKETPLACE_PRICE_PAID")?></div>
					<div class="mp-container" id="mp-new-block-paid"></div>
				</div>
			<?endif?>

			<?if (is_array($arResult["NEW_ITEMS_FREE"]) && !empty($arResult["NEW_ITEMS_FREE"])):?>
				<div class="mp-container">
					<div class="mp-title"><?=GetMessage("MARKETPLACE_PRICE_FREE")?></div>
					<div class="mp-container" id="mp-new-block-free"></div>
				</div>
			<?endif?>

			<div class="mp-title"><?=GetMessage("MARKETPLACE_TITLE_BEST")?></div>

			<?if (is_array($arResult["TOP_ITEMS_PAID"]) && !empty($arResult["TOP_ITEMS_PAID"])):?>
				<div class="mp-container">
					<div class="mp-title"><?=GetMessage("MARKETPLACE_PRICE_PAID")?></div>
					<div class="mp-container" id="mp-top-block-paid"></div>
				</div>
			<?endif?>

			<?if (is_array($arResult["TOP_ITEMS_FREE"]) && !empty($arResult["TOP_ITEMS_FREE"])):?>
				<div class="mp-container">
					<div class="mp-title"><?=GetMessage("MARKETPLACE_PRICE_FREE")?></div>
					<div class="mp-container" id="mp-top-block-free"></div>
				</div>
			<?endif?>
		</div>
	</div>
</div>
	<script>
		BX.ready(function () {
			<?if (is_array($arResult["NEW_ITEMS_PAID"]) && !empty($arResult["NEW_ITEMS_PAID"])):?>
			var gridTileNew = new BX.TileGrid.Grid(
				{
					id: 'mp_category_new_paid',
					container: document.getElementById('mp-new-block-paid'),
					items: <?=CUtil::PhpToJSObject($arResult["NEW_ITEMS_PAID"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: 'BX.Rest.Marketplace.TileGrid.Item'
				}
			);
			gridTileNew.draw();
			<?endif?>

			<?if (is_array($arResult["NEW_ITEMS_FREE"]) && !empty($arResult["NEW_ITEMS_FREE"])):?>
			var gridTileNew = new BX.TileGrid.Grid(
				{
					id: 'mp_category_new_free',
					container: document.getElementById('mp-new-block-free'),
					items: <?=CUtil::PhpToJSObject($arResult["NEW_ITEMS_FREE"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: 'BX.Rest.Marketplace.TileGrid.Item'
				}
			);
			gridTileNew.draw();
			<?endif?>

			<?if (is_array($arResult["TOP_ITEMS_PAID"]) && !empty($arResult["TOP_ITEMS_PAID"])):?>
			var gridTileTop = new BX.TileGrid.Grid(
				{
					id: 'mp_category_top_paid',
					container: document.getElementById('mp-top-block-paid'),
					items: <?=CUtil::PhpToJSObject($arResult["TOP_ITEMS_PAID"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: 'BX.Rest.Marketplace.TileGrid.Item'
				}
			);
			gridTileTop.draw();
			<?endif?>

			<?if (is_array($arResult["TOP_ITEMS_FREE"]) && !empty($arResult["TOP_ITEMS_FREE"])):?>
			var gridTileTop = new BX.TileGrid.Grid(
				{
					id: 'mp_category_top_free',
					container: document.getElementById('mp-top-block-free'),
					items: <?=CUtil::PhpToJSObject($arResult["TOP_ITEMS_FREE"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: 'BX.Rest.Marketplace.TileGrid.Item'
				}
			);
			gridTileTop.draw();
			<?endif?>
		});
	</script>
	<?
}

$jsParams = array(
	"pageCount" => isset($arResult["PAGE_COUNT"]) ? $arResult["PAGE_COUNT"] : "",
	"currentPageNumber" => isset($arResult["CURRENT_PAGE"]) ? $arResult["CURRENT_PAGE"] : "",
	"filter" => (isset($arParams["PLACEMENT"]) ? [
		"filterMode" => "placement",
		"filterValue" => $arParams["PLACEMENT"]
	] : (isset($arParams["TAG"]) ? [
		"filterMode" => "tag",
		"filterValue" => $arParams["TAG"]
	] : [
		"filterMode" => "default",
		"filterValue" => ""
	]))
);
?>
	<script>
		BX.ready(function () {
			BX.Rest.Markeplace.Category.Items.init(<?=CUtil::PhpToJSObject($jsParams)?>);
		});
	</script>
<?
$this->endViewTarget();
//endregion

if ($arResult["AJAX_MODE"])
{
	$APPLICATION->RestartBuffer();
	$APPLICATION->ShowViewContent("rest.marketplace.category.items");
	CMain::FinalActions();
	die();
}

//region Items html block
$this->setViewTarget("rest.marketplace.category.block");
?>
<div id="mp-category-block-list">
	<?=$APPLICATION->GetViewContent("rest.marketplace.category.items");?>
<script>
	BX.message({
		"MARKETPLACE_SHOW_APP": "<?=GetMessageJS("MARKETPLACE_SHOW_APP")?>",
		"MARKETPLACE_INSTALLED": "<?=GetMessageJS("MARKETPLACE_INSTALLED")?>",
		"MARKETPLACE_SALE": "<?=GetMessageJS("MARKETPLACE_SALE")?>"
	});
	BX.ready(function () {
		BX.Rest.Markeplace.Category.init(<?=CUtil::PhpToJSObject(
			[
				"filterId" => $arResult["FILTER"]["FILTER_ID"],
				"signedParameters" => $component->getSignedParameters()
			]
		)?>);
	});
	(function(){
		var reg = new RegExp("\\/category\\/(\\w+)\\/", "i");
		if (reg.test(location.href))
		{
			window.history.replaceState({}, "", location.href.replace(reg, "/"));
		}
		BX.rest.Marketplace.bindPageAnchors({allowChangeHistory: <?=$arParams["IFRAME"] ? "false" : "true"?>});
		<?if($arParams["IFRAME"]):?>
		var installCallback = function()
		{
			top.BX.removeCustomEvent(top, "Rest:AppLayout:ApplicationInstall", installCallback);
			location.reload();
		};
		top.BX.addCustomEvent(top, "Rest:AppLayout:ApplicationInstall", installCallback);
		<?endif;?>
	})();
</script>
</div>
<?
$this->endViewTarget();
//endregion

$APPLICATION->ShowViewContent("rest.marketplace.category.block");
?>
