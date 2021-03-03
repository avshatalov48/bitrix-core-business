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
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\Marketplace\Url;
use Bitrix\Rest\Url\DevOps;

$portalZoneId = (Loader::includeModule("bitrix24")) ? (CBitrix24::getPortalZone()) : "ru";
\Bitrix\Main\Loader::includeModule("ui");
\Bitrix\Main\UI\Extension::load(array("ui.tilegrid", "ui.buttons"));
\CJSCore::init(["sidepanel"], "loader");

$arResult["SLIDER"] = \CRestUtil::isSlider();

if ($arParams["NO_BACKGROUND"] == "Y")
{
	$bodyClass = $APPLICATION->getPageProperty("BodyClass", false);
	$bodyClasses = "pagetitle-toolbar-field-view no-all-paddings no-background mp-slider-view";
	$APPLICATION->setPageProperty("BodyClass", trim(sprintf("%s %s", $bodyClass, $bodyClasses)));
}
//region Category Items
$this->setViewTarget("rest.marketplace.category.items", 10);
if (is_array($arResult["ITEMS"]))
{
?>
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

	<script>
		BX.ready(function () {
			window.gridTile = new BX.TileGrid.Grid(
				{
					id: "mp_category",
					container: document.getElementById("mp-elements-block"),
					items: <?=CUtil::PhpToJSObject($arResult["ITEMS"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTile.draw();
		});
	</script>
	<?
}
elseif (
	is_array($arResult["NEW_ITEMS_PAID"]) || is_array($arResult["NEW_ITEMS_FREE"])
	|| is_array($arResult["TOP_ITEMS_PAID"]) || is_array($arResult["TOP_ITEMS_FREE"])
	|| is_array($arResult["SALE_OUT_ITEMS"]) || is_array($arResult["NEW_ITEMS_SUBSCRIPTION"])
	|| is_array($arResult["TOP_ITEMS_SUBSCRIPTION"])
)
{
	?>
	<div class="mp<? if (isset($_REQUEST["IFRAME"]) && $_REQUEST["IFRAME"] === "Y"): ?> mp-slider<? endif; ?>">
		<?if (is_array($arResult["SALE_OUT_ITEMS"]) && !empty($arResult["SALE_OUT_ITEMS"])):?>
			<div class="mp-title">
				<? if (!empty($arResult["SALE_OUT_NAME"])):?>
					<?=GetMessage(
						"MARKETPLACE_TITLE_SALE_OUT_WITH_NAME",
						[
							"#ACTION_NAME#" => $arResult["SALE_OUT_NAME"]
						]
					)?>
				<? else:?>
					<?=GetMessage("MARKETPLACE_TITLE_SALE_OUT")?>
				<? endif;?>
				<span
					class="rest-marketplace-show-all-link"
					data-role="sale-out"
					onclick="BX.onCustomEvent('BX.Main.Filter:clickMPAllLink', [this])"
				>
					<?=GetMessage("MARKETPLACE_SHOW_ALL_LINK")?>
				</span>
			</div>
			<div class="mp-container">
				<div class="mp-container" id="mp-sale-out-block"></div>
			</div>
		<?endif?>

		<div class="mp-title"><?=GetMessage("MARKETPLACE_TITLE_NEW")?></div>

		<?if (is_array($arResult["NEW_ITEMS_SUBSCRIPTION"]) && !empty($arResult["NEW_ITEMS_SUBSCRIPTION"])):?>
			<div class="mp-container">
				<div class="mp-title"><?=GetMessage("MARKETPLACE_PRICE_SUBSCRIPTION")?></div>
				<div class="mp-container" id="mp-new-block-subscription"></div>
			</div>
		<?endif?>

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

		<?if (is_array($arResult["TOP_ITEMS_SUBSCRIPTION"]) && !empty($arResult["TOP_ITEMS_SUBSCRIPTION"])):?>
			<div class="mp-container">
				<div class="mp-title"><?=GetMessage("MARKETPLACE_PRICE_SUBSCRIPTION")?></div>
				<div class="mp-container" id="mp-top-block-subscription"></div>
			</div>
		<?endif?>

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

	<script>
		BX.ready(function () {

			<?if (is_array($arResult["SALE_OUT_ITEMS"]) && !empty($arResult["SALE_OUT_ITEMS"])):?>
			var gridTileNew = new BX.TileGrid.Grid(
				{
					id: "mp_category_sale_out",
					container: document.getElementById("mp-sale-out-block"),
					items: <?=CUtil::PhpToJSObject($arResult["SALE_OUT_ITEMS"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileNew.draw();
			<?endif?>

			<?if (is_array($arResult["NEW_ITEMS_SUBSCRIPTION"]) && !empty($arResult["NEW_ITEMS_SUBSCRIPTION"])):?>
			var gridTileNew = new BX.TileGrid.Grid(
				{
					id: "mp_category_new_subscription",
					container: document.getElementById("mp-new-block-subscription"),
					items: <?=CUtil::PhpToJSObject($arResult["NEW_ITEMS_SUBSCRIPTION"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileNew.draw();
			<?endif?>

			<?if (is_array($arResult["NEW_ITEMS_PAID"]) && !empty($arResult["NEW_ITEMS_PAID"])):?>
			var gridTileNew = new BX.TileGrid.Grid(
				{
					id: "mp_category_new_paid",
					container: document.getElementById("mp-new-block-paid"),
					items: <?=CUtil::PhpToJSObject($arResult["NEW_ITEMS_PAID"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileNew.draw();
			<?endif?>

			<?if (is_array($arResult["NEW_ITEMS_FREE"]) && !empty($arResult["NEW_ITEMS_FREE"])):?>
			var gridTileNew = new BX.TileGrid.Grid(
				{
					id: "mp_category_new_free",
					container: document.getElementById("mp-new-block-free"),
					items: <?=CUtil::PhpToJSObject($arResult["NEW_ITEMS_FREE"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileNew.draw();
			<?endif?>

			<?if (is_array($arResult["TOP_ITEMS_SUBSCRIPTION"]) && !empty($arResult["TOP_ITEMS_SUBSCRIPTION"])):?>
			var gridTileTop = new BX.TileGrid.Grid(
				{
					id: "mp_category_top_subscription",
					container: document.getElementById("mp-top-block-subscription"),
					items: <?=CUtil::PhpToJSObject($arResult["TOP_ITEMS_SUBSCRIPTION"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileTop.draw();
			<?endif?>

			<?if (is_array($arResult["TOP_ITEMS_PAID"]) && !empty($arResult["TOP_ITEMS_PAID"])):?>
			var gridTileTop = new BX.TileGrid.Grid(
				{
					id: "mp_category_top_paid",
					container: document.getElementById("mp-top-block-paid"),
					items: <?=CUtil::PhpToJSObject($arResult["TOP_ITEMS_PAID"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileTop.draw();
			<?endif?>

			<?if (is_array($arResult["TOP_ITEMS_FREE"]) && !empty($arResult["TOP_ITEMS_FREE"])):?>
			var gridTileTop = new BX.TileGrid.Grid(
				{
					id: "mp_category_top_free",
					container: document.getElementById("mp-top-block-free"),
					items: <?=CUtil::PhpToJSObject($arResult["TOP_ITEMS_FREE"])?>,
					itemHeight: 105,
					itemMinWidth: 300,
					itemType: "BX.Rest.Marketplace.TileGrid.Item"
				}
			);
			gridTileTop.draw();
			<?endif?>
		});
	</script>
	<?
}
else
{

	/*?><?GetMessage("MARKETPLACE_EMPTY_CATEGORY");*/
	?><iframe src="https://integrations.bitrix24.site/<?=$portalZoneId?>/"  class="app-frame" frameborder="0" style="width: 100%;height: -webkit-calc(100vh - 143px);height: calc(100vh - 143px);"><?=GetMessage("MARKETPLACE_EMPTY_CATEGORY");?></iframe><?
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
	echo $APPLICATION->GetViewContent("rest.marketplace.category.items");
	return;
}

//region Filter
\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter([
	//"GRID_ID" => "",
	"FILTER_ID" => $arResult["FILTER"]["FILTER_ID"],
	"FILTER" => $arResult["FILTER"]["FILTER"],
	"FILTER_PRESETS" => $arResult["FILTER"]["FILTER_PRESETS"],
	"ENABLE_LIVE_SEARCH" => true,
	"ENABLE_LABEL" => true,
	"RESET_TO_DEFAULT_MODE"	=> true,
	"VALUE_REQUIRED"		=> true
]);
if (\CRestUtil::isAdmin())
{
	\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
		"link" => DevOps::getInstance()->getIndexUrl(),
		"color" => \Bitrix\UI\Buttons\Color::PRIMARY,
		"icon" => "",
		"text" => Loc::getMessage("MENU_MARKETPLACE_ADD"),
	]);
	if (false && isset($arParams["TAG"])) // Just for the future functionality
	{
		\Bitrix\UI\Toolbar\Facade\Toolbar::addButton([
			"link" => Url::getWidgetAddUrl(),
			"color" => \Bitrix\UI\Buttons\Color::PRIMARY,
			"icon" => "",
			"text" => Loc::getMessage("MENU_MARKETPLACE_ADD_WIDGET")
		]);
	}
}

//endregion
//region TopMenu
$this->setViewTarget("above_pagetitle");
$APPLICATION->IncludeComponent(
	"bitrix:menu",
	"top_horizontal",
	array(
		"ROOT_MENU_TYPE" => "left",
		"MENU_CACHE_TYPE" => "N",
		"MENU_CACHE_TIME" => "604800",
		"MENU_CACHE_USE_GROUPS" => "N",
		"MENU_CACHE_USE_USERS" => "Y",
		"CACHE_SELECTED_ITEMS" => "N",
		"MENU_CACHE_GET_VARS" => array(),
		"MAX_LEVEL" => "1",
		"USE_EXT" => "Y",
		"DELAY" => "N",
		"ALLOW_MULTI_SELECT" => "N"
	),
	false
);
$this->endViewTarget();

//endregion
//region Left menu
$sum = 0;
$items = [];

if (isset($arParams["TAG"]))
{
	$cnt = is_array($arResult["ITEMS"]) ? count($arResult["ITEMS"]) : 0;
	$items[] = [
		"ATTRIBUTES" => [
			"bx-role" => "mp-left-menu-item",
			"bx-mp-left-menu-item" => "all",
			"bx-filter-mode" => isset($arParams["PLACEMENT"]) ? "placement" : "tag",
			"bx-filter-value" => preg_replace("/[^a-z0-9_-]/i", "_", isset($arParams["PLACEMENT"]) ? $arParams["PLACEMENT"] : $arParams["TAG"]),
			"onclick" => "BX.onCustomEvent('BX.Main.Filter:clickMPMenu', [this])"
		],
		"NAME_HTML" => "<span class=\"ui-sidepanel-menu-link-text-item\">".Loc::getMessage("MARKETPLACE_COLLECTION")."</span>".($cnt > 0 ? " <span class=\"ui-sidepanel-menu-link-text-counter\">{$cnt}</span>" : ""),
		"ACTIVE" => true
	];
}

if (!empty($arResult["CATEGORIES"]))
{
	$activeItems = is_array($arResult["FILTER"]["DATA"]) ? $arResult["FILTER"]["DATA"]["CATEGORY"] : [];
	$activeItems = is_array($activeItems) ? $activeItems : [$activeItems];
	foreach ($arResult["CATEGORIES"] as $category)
	{
		$sum += $category["CNT"];

		$name = '';
		if (!empty($category['ICON_PATH']))
		{
			$name = '<img class="ui-sidepanel-menu-link-text-icon" src="' . $category['ICON_PATH'] . '" />';
		}
		$name .= '<span class="ui-sidepanel-menu-link-text-item">' . $category['NAME'] . '</span>';
		if ($category['CNT'] > 0)
		{
			$name .= '<span class="ui-sidepanel-menu-link-text-counter">' . $category['CNT'] . '</span>';
		}

		$item = [
			"ATTRIBUTES" => ["bx-role" => "mp-left-menu-item", "bx-mp-left-menu-item" => $category["CODE"], "onclick" => "BX.onCustomEvent('BX.Main.Filter:clickMPMenu', [this])"],
			"NAME_HTML" => $name,
			"ACTIVE" => in_array($category["CODE"], $activeItems),
			"OPERATIVE" => true,
			"CHILDREN" => []
		];
		if (isset($category["CHILDREN"]))
		{
			foreach ($category["CHILDREN"] as $category)
			{
				$item["CHILDREN"][] = [
					"ATTRIBUTES" => ["bx-role" => "mp-left-menu-item", "bx-mp-left-menu-item" => $category["CODE"]],
					"NAME_HTML" => "<span class=\"ui-sidepanel-menu-link-text-item\">".$category["NAME"]."</span>".($category["CNT"] > 0 ? " <span class=\"ui-sidepanel-menu-link-text-counter\">{$category["CNT"]}</span>" : ""),
					"ACTIVE" => in_array($category["CODE"], $activeItems),
				];
			}
		}
		$items[] = $item;
	}
}

if ($arResult["CATEGORIES_COUNT"] > 0)
{
	$sum = round($arResult["CATEGORIES_COUNT"], -1);
}
elseif ($sum > 890)
{
	$sum = 890;
}
else
{
	$sum = round($sum, -1);
}

$titleMessage = Loc::getMessage("MENU_MARKETPLACE_TITLE");
$title = <<<HTML
	<div class="mp-head-status-box">
		<span class="mp-head-status-value">
			{$sum}<span class="mp-head-status-plus">&#43;</span>
		</span>
		<span class="mp-head-status-text">{$titleMessage}</span>
		<button class="mp-head-status-btn">&rarr;</button>
	</div>
HTML;

$APPLICATION->AddViewContent("left-panel-before", $title);

if(!empty($arResult["CATEGORIES"])):
	?><? $APPLICATION->IncludeComponent(
		"bitrix:ui.sidepanel.wrappermenu",
		'',
		[
			"ID" => "mp-left-menu",
			"ITEMS" => $items
		]
	);?>
	<?
endif;
//endregion
//region Items html block
$this->setViewTarget("rest.marketplace.category.block");
?>
<div id="mp-category-block">
	<?=$APPLICATION->GetViewContent("rest.marketplace.category.items");?>
<script>
	BX.message({
		"MARKETPLACE_SHOW_APP": "<?=GetMessageJS("MARKETPLACE_SHOW_APP")?>",
		"MARKETPLACE_INSTALLED": "<?=GetMessageJS("MARKETPLACE_INSTALLED")?>",
		"MARKETPLACE_SALE": "<?=GetMessageJS("MARKETPLACE_SALE")?>"
	});
	BX.ready(function () {
		BX.Rest.Markeplace.Category.init(<?=CUtil::PhpToJSObject([
			"filterId" => $arResult["FILTER"]["FILTER_ID"],
			"signedParameters" => $component->getSignedParameters()
		])?>);
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
if(empty($arResult["CATEGORIES"]) && empty($arResult["ITEMS"])):
	$bodyClass = $APPLICATION->getPageProperty("BodyClass", false);
	$bodyClass = str_replace(['no-background','no-all-paddings'],'', $bodyClass);
	$bodyClasses = "pagetitle-toolbar-field-view  mp-slider-view";
	$APPLICATION->setPageProperty("BodyClass", trim(sprintf("%s %s", $bodyClass, $bodyClasses)));

	?>
	<div class="rest-marketplace-error">
		<div class="rest-marketplace-error-wrapper">
			<div class="rest-marketplace-error-start-icon-main rest-marketplace-error-start-icon-main-zip">
				<div class="rest-marketplace-error-start-icon-main rest-marketplace-error-start-icon-main-error">
					<div class="rest-marketplace-error-start-icon-refresh"></div>
					<div class="rest-marketplace-error-start-icon"></div>
					<div class="rest-marketplace-error-start-icon-circle"></div>
				</div>
			</div>
			<div class="rest-marketplace-error-title"><?=Loc::getMessage("REST_MARKETPLACE_ERROR_404_TITLE")?></div>
			<div class="rest-marketplace-error-info"><?=Loc::getMessage("REST_MARKETPLACE_ERROR_404_DESCRIPTION")?></div>
		</div>
	</div>
<?else:
	$APPLICATION->ShowViewContent("rest.marketplace.category.block");
endif;
?>
