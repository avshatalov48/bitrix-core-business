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

Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.notification',
]);

$getTileTemplate = function () use ($arParams)
{
	$inputName = $arParams['INPUT_NAME'] ?: '';
	$inputName .= ($inputName && $arParams['MULTIPLE']) ? '[]' : '';
	ob_start();
	?>
	<span data-role="tile-item" data-bx-id="%id%" data-bx-data="%data%" class="sender-ui-tile-item" style="%style%">
		<span data-role="tile-item-name">%name%</span>
		<?if ($inputName):?>
			<input type="hidden" name="<?=$inputName?>" value="%id%">
		<?endif;?>
		<?if (!$arParams['READONLY']):?>
			<span data-role="remove" class="sender-ui-tile-item-remove"></span>
		<?endif;?>
	</span>
	<?
	return ob_get_clean();
};

$containerId = 'sender-ui-tile-selector-';
$containerId .= $arParams['ID'] ?: 'def';
?>
<script type="text/javascript">
	BX.ready(function () {
		new BX.Sender.UI.TileSelector(<?=Json::encode(array(
			'containerId' => $containerId,
			'id' => $arParams['ID'],
			'duplicates' => $arParams['DUPLICATES'],
			'readonly' => $arParams['READONLY'],
			'multiple' => $arParams['MULTIPLE'],
			'manualInputEnd' => $arParams['MANUAL_INPUT_END'],
			'checkOnStatic' => $arParams['CHECK_ON_STATIC'],
			'notifyContent' => Loc::getMessage('SENDER_UI_STATIC_SELECTOR_SELECTED', ['%INSTRUCTION%' => 'javascript:top.BX.Helper.show("redirect=detail&code=1488298")']),
		))?>);
	});
</script>
<span id="<?=htmlspecialcharsbx($containerId)?>" class="sender-ui-tile-selector-wrap <?=($arParams['READONLY'] ? 'sender-readonly' : '')?>">

	<script data-role="tile-template" type="text/html">
		<?=$getTileTemplate();?>
	</script>

	<script data-role="popup-category-template" type="text/html">
		<div class="sender-ui-tile-searcher-sidebar-item">%name%</div>
	</script>

	<script data-role="popup-item-template" type="text/html">
		<div class="sender-ui-tile-searcher-content-item" title="%name%">%name%</div>
	</script>

	<script data-role="popup-template" type="text/html">
		<div class="sender-ui-tile-searcher">
			<div class="sender-ui-tile-searcher-container">
				<div data-role="popup-title" class="sender-ui-tile-searcher-title"></div>
				<div class="sender-ui-tile-searcher-inner">
					<div class="sender-ui-tile-searcher-main sender-ui-tile-searcher-inner-shadow">
						<div data-role="popup-item-list" class="sender-ui-tile-searcher-content" style="display: none;"></div>
						<svg data-role="popup-loader" class="sender-ui-tile-searcher-circular" viewBox="25 25 50 50">
							<circle class="sender-ui-tile-searcher-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							<circle class="sender-ui-tile-searcher-inner-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
						</svg>
					</div>
					<div data-role="popup-category-list" class="sender-ui-tile-searcher-sidebar" style="display: none;"></div>
				</div>
			</div>
		</div>
	</script>

	<span data-role="tile-container" class="sender-ui-tile-selector">
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
				),
				array(
					htmlspecialcharsbx($tile['id']),
					htmlspecialcharsbx(Json::encode($tile['data'])),
					$style,
					htmlspecialcharsbx($tile['name'])
				),
				$getTileTemplate()
			);

		endforeach;
		?>
		<input data-role="tile-input" type="text" class="sender-ui-tile-input" autocomplete="off" style="display: none;">

		<?if ($arParams['SHOW_BUTTON_SELECT'] && !$arParams['READONLY']):?>
			<span data-role="tile-select" class="sender-ui-tile-select">
				<?if ($arParams['BUTTON_SELECT_CAPTION']):?>
					<?=htmlspecialcharsbx($arParams['BUTTON_SELECT_CAPTION'])?>
				<?else:?>
					<?=Loc::getMessage('SENDER_UI_TILE_SELECTOR_SELECT')?>
				<?endif;?>
			</span>
		<?endif;?>
	</span>
	<?if ($arParams['SHOW_BUTTON_ADD'] && !$arParams['READONLY']):?>
		<span data-role="tile-add" class="sender-ui-tile-add">
			<?if ($arParams['BUTTON_ADD_CAPTION'] ?? ''):?>
				<?=htmlspecialcharsbx($arParams['BUTTON_ADD_CAPTION' ?? ''])?>
			<?else:?>
				<?=Loc::getMessage('SENDER_UI_TILE_SELECTOR_ADD')?>
			<?endif;?>
		</span>
	<?endif;?>
</span>