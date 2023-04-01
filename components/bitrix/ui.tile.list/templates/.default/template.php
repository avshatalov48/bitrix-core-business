<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global \CAllMain $APPLICATION */
/** @global \CAllUser $USER */
/** @global \CAllDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(['ui.icons', 'ui.design-tokens', 'ui.fonts.opensans']);

$getTileLayout = function (array $tile = [])
{
	$id = empty($tile['id']) ? '' : htmlspecialcharsbx($tile['id']);
	$name = empty($tile['name']) ? '' : htmlspecialcharsbx($tile['name']);
	$iconClass = empty($tile['iconClass']) ? '' : htmlspecialcharsbx($tile['iconClass']);
	$iconColor = empty($tile['iconColor'])
		? ''
		: 'background-color: ' . htmlspecialcharsbx($tile['iconColor']) . ';';
	$bgcolor = empty($tile['bgcolor'])
		? ''
		: 'background: ' . htmlspecialcharsbx($tile['bgcolor']) . ';';
	$color = empty($tile['color'])
		? ''
		: 'color: ' . htmlspecialcharsbx($tile['color']) . ';';

	$comingSoon = $tile['comingSoon'] ?? false;
	$badgeNew = $tile['badgeNew'] ?? false;
	$button = $tile['button'] ?? false;
	$buttonName = $tile['data']['buttonName'] ?? '';

	ob_start();
	?>
	<div data-role="tile/item"
		data-id="<?=$id?>"
		class="ui-tile-list-item<? if ($comingSoon):?> ui-tile-list-item-disabled<?endif?>"
		style="<?=$bgcolor?>"
	>
		<div class="ui-tile-list-logo-container">
			<span data-role="tile/item/icon" class="ui-tile-list-logo <?=$iconClass?>">
				<i style="<?=$iconColor?>" data-role="tile/item/icon/color"></i>
			</span>
		</div>
		<div class="ui-tile-list-name">
			<span data-role="tile/item/name" class="ui-tile-list-name-text" title="<?=$name?>" style="<?=$color?>"><?=$name?></span>
		</div>
		<? if ($badgeNew): ?>
		<div class="ui-tile-badge ui-tile-badge--new"><?=Loc::getMessage('UI_TILE_LIST_NEW')?></div>
		<? endif ?>
		<? if ($comingSoon): ?>
		<div class="ui-tile-list-label">
			<span class="ui-tile-list-label-text"><?=Loc::getMessage('UI_TILE_LIST_COMMING_SOON')?></span>
		</div>
		<? endif ?>
		<? if ($button): ?>
		<div class="ui-tile-list-btn-block">
			<button class="ui-btn ui-btn-primary"><?=$buttonName?></button>
		</div>
		<? endif ?>
	</div>
	<?
	return ob_get_clean();
};

$containerId = 'ui-tile-list-';
$containerId .= $arParams['ID'] ?: 'def';

?>
<script type="text/javascript">
	BX.ready(function () {
		new BX.UI.TileList.Manager(<?=Json::encode(array(
			'containerId' => $containerId,
			'id' => $arParams['ID'],
			'tileOptionsList' => $arParams['LIST']
		))?>);
	});
</script>

<div id="<?=htmlspecialcharsbx($containerId)?>" class="ui-tile-list-block">
	<div class="ui-tile-list-wrap">
		<div data-role="tile/items" class="ui-tile-list-list">
			<?
			foreach ($arResult['LIST'] as $tile)
			{
				echo $getTileLayout($tile);
			}
			?>

			<?if ($arParams['SHOW_BUTTON_ADD']):?>
				<div data-role="tile/add" class="ui-tile-list-item ui-tile-list-item-add">
					<div class="ui-tile-list-logo-container">
						<span class="ui-tile-list-logo ui-tile-list-logo-add"></span>
					</div>
					<div class="ui-tile-list-name">
					<span class="ui-tile-list-name-text"><?=(
						$arParams['BUTTON_ADD_CAPTION'] ?: Loc::getMessage('UI_TILE_LIST_ADD')
						)?></span>
					</div>
				</div>
			<?endif;?>
		</div>
	</div>

	<script data-role="tile/template" type="text/html">
		<?=$getTileLayout();?>
	</script>
</div>
