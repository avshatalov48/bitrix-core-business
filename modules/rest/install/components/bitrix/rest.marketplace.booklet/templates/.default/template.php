<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CAllMain $APPLICATION */
/** @global CAllUser $USER */
/** @global CAllDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

global $APPLICATION;
if ($arResult['TITLE'])
{
	$APPLICATION->SetTitle($arResult['TITLE']);
}

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
$bodyClasses = 'no-hidden no-background no-all-paddings no-margin-toolbar';
$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));

Extension::load(
	[
		'ui.tilegrid',
		'ui.buttons',
		'ui.sidepanel-content',
		'rest.integration',
	]
);

if (!empty($arResult['ITEMS'])):
	?>
	<div class="rest-market-section">
		<? if ($arResult['ACTION_TITLE']): ?>
			<div class="rest-market-grid-title rest-market-grid-title-border"><?=$arResult['ACTION_TITLE']?></div>
		<? endif; ?>
		<div class="rest-market-grid" id="<?=$arResult['CONTAINER_ID']?>"></div>
	</div>
	<script>
		var gridSite = new BX.TileGrid.Grid({
			id: 'grid_site',
			container: document.getElementById('<?=$arResult['CONTAINER_ID']?>'),
			itemHeight: 160,
			itemMinWidth: 270,
			itemType: 'BX.Rest.MarketSite.TileGrid.Item',
			items: <?=Json::encode($arResult['ITEMS']);?>
		});
		gridSite.draw();
	</script>
<?php endif; ?>

<?php if ($arResult['DESCRIPTION']):?>
	<div class="ui-slider-section">
		<div class="ui-slider-content-box">
			<div class="ui-slider-heading-4"><?=$arResult['DESCRIPTION_TITLE']?></div>
			<p class="ui-slider-paragraph-2"><?=$arResult['DESCRIPTION']?></p>
		</div>
	</div>
<?php endif; ?>

<?php if (!empty($arResult['APP_TAG_BANNER'])): ?>
	<?php
	$reee = $APPLICATION->IncludeComponent(
		'bitrix:rest.marketplace.category',
		'banner',
		array(
			'TAG' => $arResult['APP_TAG_BANNER'],
			'FILTER_ID' => '_banner_' . $arResult['CONTAINER_ID'],
			'BLOCK_COUNT' => $arResult['APP_BANNER_COUNT'],
			'SET_TITLE' => 'N',
			'HOLD_BANNER_ITEMS' => 'Y',
			'DETAIL_URL_TPL' => $arResult['MP_DETAIL_URL_TPL'],
			'MP_TAG_PATH' => $arResult['MP_TAG_PATH']
		),
		$component
	)
	?>
<?php endif; ?>
<?php if (!empty($arResult['APP_TAG'])): ?>
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:rest.marketplace.category',
		'list',
		array(
			'TAG' => $arResult['APP_TAG'],
			'FILTER_ID' => '_list_' . $arResult['CONTAINER_ID'],
			'SHOW_LAST_BLOCK' => 'Y',
			'BLOCK_COUNT' => $arResult['APP_COUNT'],
			'SET_TITLE' => 'N',
			'DETAIL_URL_TPL' => $arResult['MP_DETAIL_URL_TPL'],
			'INDEX_URL_PATH' => $arResult['MP_INDEX_PATH'],
			'SECTION_URL_PATH' => $arResult['MP_TAG_PATH'],
			'SECTION_TITLE' => Loc::getMessage("REST_MARKETPLACE_BOOKLET_TITLE_NEW_APP"),
			'SECTION_SHOW_ALL_BTN_NAME' => Loc::getMessage("REST_MARKETPLACE_BOOKLET_BTN_SHOW_ALL"),
		),
		$component
	)
	?>
<?php endif;?>