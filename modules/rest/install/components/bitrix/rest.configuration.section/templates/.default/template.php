<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

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
if(!empty($arResult['ITEMS_JS'])):
	if ($arParams['NO_BACKGROUND'] == "Y")
	{
		$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);
		$bodyClasses = 'no-hidden no-background no-all-paddings';
		$APPLICATION->setPageProperty('BodyClass', trim(sprintf('%s %s', $bodyClass, $bodyClasses)));
	}

	Loc::loadMessages(__FILE__);
	Extension::load(array('ui.tilegrid', 'ui.buttons'));

	$templateId = 'rest-'.md5($component->__name. $this->__name);
?>
	<div class="rest-market-section">
		<div class="rest-market-grid-title"><?= Loc::getMessage("REST_CONFIGURATION_SECTION_LABEL") ?></div>
		<div class="rest-market-grid" id="<?=$templateId?>"></div>
	</div>
	<script>
		BX.ready(function ()
		{
			BX.message(<?=Json::encode(
				[
					'REST_CONFIGURATION_SECTION_LINK_NAME' => Loc::getMessage("REST_CONFIGURATION_SECTION_LINK_NAME")
				]
			)?>);
			var gridDirections = new BX.TileGrid.Grid({
				id: 'grid_directions',
				container: document.getElementById('<?=$templateId?>'),
				itemHeight: 134,
				itemMinWidth: 300,
				itemType: 'BX.Rest.MarketDirections.TileGrid.Item',
				items: <?=Json::encode($arResult['ITEMS_JS']);?>
			});

			gridDirections.draw();
		});
	</script>
<? endif;?>
