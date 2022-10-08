<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

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
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');

if(!empty($arResult['ITEMS'])):
	if ($arParams['NO_BACKGROUND'] == "Y")
	{
		$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
		$bodyClasses = 'no-hidden no-background no-all-paddings';
		$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
	}
	$containerId = 'rest-configuration-action';
?>
	<div class="rest-market-section">
		<div class="rest-market-grid-title rest-market-grid-title-border"><?= Loc::getMessage("REST_CONFIGURATION_ACTION_BLOCK_TITLE") ?></div>
		<div class="rest-market-grid" id="<?=$containerId?>"></div>
	</div>
	<script>
		var gridSite = new BX.TileGrid.Grid({
			id: 'grid_site',
			container: document.getElementById('<?=$containerId?>'),
			itemHeight: 160,
			itemMinWidth: 270,
			itemType: 'BX.Rest.MarketSite.TileGrid.Item',
			items: <?=Json::encode($arResult['ITEMS']);?>
		});
		gridSite.draw();
	</script>
<? endif;?>