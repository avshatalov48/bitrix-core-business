<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(array("ui.tilegrid", "ui.buttons"));
\CJSCore::init("sidepanel", "loader");

$arResult['SLIDER'] = \CRestUtil::isSlider();

if ($arParams['NO_BACKGROUND'] == "Y")
{
	$bodyClasses = 'pagetitle-toolbar-field-view no-hidden no-all-paddings no-background';
	$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
}

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

if ($arParams['SHOW_FILTER'] == "Y")
{
	if (!$arResult['SLIDER'])
	{
		$this->setViewTarget("inside_pagetitle", 10);
	}
	?>

	<div class="pagetitle-container pagetitle-flexible-space">
		<?
		$APPLICATION->IncludeComponent(
			'bitrix:main.ui.filter',
			'',
			array(
				'FILTER_ID'				=> $arResult["FILTER"]["FILTER_ID"],
				'FILTER'				=> $arResult['FILTER']['FILTER'],
				'FILTER_PRESETS'		=> $arResult['FILTER']['FILTER_PRESETS'],
				'ENABLE_LIVE_SEARCH'	=> true,
				'ENABLE_LABEL'			=> true,
				'RESET_TO_DEFAULT_MODE'	=> true,
				"VALUE_REQUIRED"		=> true
			),
			$component
		);
		?>
	</div>

	<?
	if (!$arResult['SLIDER'])
	{
		$this->endViewTarget();
	}
}
?>

<script>
	BX.message({
		"MARKETPLACE_SHOW_APP": "<?=GetMessageJS("MARKETPLACE_SHOW_APP")?>",
		"MARKETPLACE_INSTALLED": "<?=GetMessageJS("MARKETPLACE_INSTALLED")?>",
		"MARKETPLACE_SALE": "<?=GetMessageJS("MARKETPLACE_SALE")?>"
	});
</script>

<div id="mp-category-block">
	<?
	if ($arResult["AJAX_MODE"])
	{
		$APPLICATION->RestartBuffer();
	}

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
	else
	{
		echo GetMessage("MARKETPLACE_EMPTY_CATEGORY");
	}

	$jsParams = array(
		"ajaxPath" => POST_FORM_ACTION_URI,
		"pageCount" => isset($arResult["PAGE_COUNT"]) ? $arResult["PAGE_COUNT"] : "",
		"currentPage" => isset($arResult["CURRENT_PAGE"]) ? $arResult["CURRENT_PAGE"] : "",
		"filterId" => isset($arResult["FILTER"]["FILTER_ID"]) ? $arResult["FILTER"]["FILTER_ID"] : ""
	);
	?>
	<script>
		BX.ready(function () {
			BX.Rest.Markeplace.Category.init(<?=CUtil::PhpToJSObject($jsParams)?>);
		});
	</script>
	<?
	if ($arResult["AJAX_MODE"])
	{
		CMain::FinalActions();
		die();
	}
	?>
</div>

<script>
	<?if ($arParams['SHOW_FILTER'] == "Y"):?>
		BX.ready(function () {
			BX.Rest.Markeplace.Category.initEvents();
		});
	<?endif?>

	(function(){
		BX.rest.Marketplace.bindPageAnchors({allowChangeHistory: <?=$arParams['IFRAME'] ? 'false' : 'true'?>});
		<?if($arParams['IFRAME']):?>
			var installCallback = function()
			{
				top.BX.removeCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', installCallback);
				location.reload();
			};
			top.BX.addCustomEvent(top, 'Rest:AppLayout:ApplicationInstall', installCallback);
		<?endif;?>
	})();
</script>
