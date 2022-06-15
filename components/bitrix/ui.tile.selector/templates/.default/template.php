<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(["ui.buttons", "main.popup", "ui.design-tokens"]);

$getTileTemplate = function () use ($arParams)
{
	$inputName = $arParams['INPUT_NAME'] ?: '';
	$inputName .= ($inputName && $arParams['MULTIPLE']) ? '[]' : '';
	ob_start();
	?>
	<span data-role="tile-item" data-bx-id="%id%" data-bx-data="%data%" class="ui-tile-selector-item ui-tile-selector-item-%type% ui-tile-selector-item-readonly-%readonly%" style="%style%">
		<span data-role="tile-item-name">%name%</span>
		<?if ($inputName):?>
			<input type="hidden" name="<?=$inputName?>" value="%id%">
		<?endif;?>
		<?if (!$arParams['READONLY'] && $arParams['CAN_REMOVE_TILES']):?>
			<span data-role="remove" class="ui-tile-selector-item-remove"></span>
		<?endif;?>
	</span>
	<?
	return ob_get_clean();
};

$containerId = 'ui-tile-selector-';
$containerId .= $arParams['ID'] ?: 'def';
?>
<script type="text/javascript">
	BX.ready(function () {
		new BX.UI.TileSelector(<?=Json::encode(array(
			'containerId' => $containerId,
			'id' => $arParams['ID'],
			'duplicates' => $arParams['DUPLICATES'],
			'readonly' => $arParams['READONLY'],
			'multiple' => $arParams['MULTIPLE'],
			'manualInputEnd' => $arParams['MANUAL_INPUT_END'],
			'fireClickEvent' => $arParams['FIRE_CLICK_EVENT'],
			'caption' => CUtil::jSEscape(!empty($arParams['BUTTON_SELECT_CAPTION']) ? $arParams['BUTTON_SELECT_CAPTION'] : Loc::getMessage('UI_TILE_SELECTOR_SELECT')),
			'captionMore' => CUtil::jSEscape(!empty($arParams['BUTTON_SELECT_CAPTION_MORE']) ? $arParams['BUTTON_SELECT_CAPTION_MORE'] : Loc::getMessage('UI_TILE_SELECTOR_SELECT'))
		))?>);
	});

	BX.message({
		UI_TILE_SELECTOR_MORE: '<?=CUtil::JSEscape(Loc::getMessage("UI_TILE_SELECTOR_MORE"))?>'
	});

</script>
<span id="<?=htmlspecialcharsbx($containerId)?>" class="ui-tile-selector-selector-wrap<?=($arParams['READONLY'] ? ' readonly' : '')?>">
	<span id="<?=htmlspecialcharsbx($containerId)?>-mask" class="ui-tile-selector-selector-mask"></span>
	<script data-role="tile-template" type="text/html">
		<?=$getTileTemplate();?>
	</script>

	<script data-role="popup-category-template" type="text/html">
		<div class="ui-tile-selector-searcher-sidebar-item">%name%</div>
	</script>

	<script data-role="popup-item-template" type="text/html">
		<div class="ui-tile-selector-searcher-content-item" title="%name%">%name%</div>
	</script>

	<script data-role="popup-template" type="text/html">
		<div class="ui-tile-selector-searcher">
			<div class="ui-tile-selector-searcher-container">
				<div data-role="popup-title" class="ui-tile-selector-searcher-title"></div>
				<div class="ui-tile-selector-searcher-inner">
					<div class="ui-tile-selector-searcher-main ui-tile-selector-searcher-inner-shadow">
						<div data-role="popup-item-list" class="ui-tile-selector-searcher-content" style="display: none;"></div>
						<svg data-role="popup-loader" class="ui-tile-selector-searcher-circular" viewBox="25 25 50 50">
							<circle class="ui-tile-selector-searcher-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							<circle class="ui-tile-selector-searcher-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
						</svg>
					</div>
					<div data-role="popup-category-list" class="ui-tile-selector-searcher-sidebar" style="display: none;"></div>
				</div>
			</div>
		</div>
	</script>

	<span data-role="tile-container" class="ui-tile-selector-selector">
		<?
		foreach ($arResult['LIST'] as $tile):
			$style = '';
			if (isset($tile['bgcolor']) && $tile['bgcolor'])
			{
				$style .= 'background-color: ' . htmlspecialcharsbx($tile['bgcolor']) . '; ';
			}
			if (isset($tile['color']) && $tile['color'])
			{
				$style .= 'color: ' . htmlspecialcharsbx($tile['color']) . '; ';
			}
			echo str_replace(
				array(
					'%id%',
					'%data%',
					'%style%',
					'%name%',
					'%readonly%',
				),
				array(
					htmlspecialcharsbx($tile['id']),
					htmlspecialcharsbx(Json::encode($tile['data'])),
					$style,
					htmlspecialcharsbx($tile['name']),
					($tile['readonly'] ? 'yes' : 'no')
				),
				$getTileTemplate()
			);

		endforeach;
		?>
		<span data-role="tile-more" class="ui-tile-selector-more" style="display: none;">
			<span data-role="tile-item-name">...</span>
		</span>
		<input data-role="tile-input" type="text" class="ui-tile-selector-input" autocomplete="off" style="display: none;">

		<?if ($arParams['SHOW_BUTTON_SELECT'] && !$arParams['READONLY']):?>
			<span class="ui-tile-selector-select-container">
				<span data-role="tile-select" class="ui-tile-selector-select">
					<?if ($arParams['BUTTON_SELECT_CAPTION']):?>
						<?=htmlspecialcharsbx($arParams['BUTTON_SELECT_CAPTION'])?>
					<?else:?>
						<?=Loc::getMessage('UI_TILE_SELECTOR_SELECT')?>
					<?endif;?>
				</span>
			</span>
		<?endif;?>
		<?if ($arParams['LOCK']):?>
			<span class="ui-tile-selector-lock-icon"></span>
		<?endif;?>
	</span>
	<?if ($arParams['SHOW_BUTTON_ADD'] && !$arParams['READONLY']):?>
		<span data-role="tile-add" class="ui-tile-selector-add">
			<?if ($arParams['BUTTON_ADD_CAPTION']):?>
				<?=htmlspecialcharsbx($arParams['BUTTON_ADD_CAPTION'])?>
			<?else:?>
				<?=Loc::getMessage('UI_TILE_SELECTOR_ADD')?>
			<?endif;?>
		</span>
	<?endif;?>
</span>