<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CBitrixComponentTemplate $this */
/** @var string $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$this->setFrameMode(true);

$filter = Toolbar::getFilter();
$afterTitleButtons = Toolbar::renderAfterTitleButtons();
$rightButtons = Toolbar::renderRightButtons();
$filterButtons = Toolbar::renderAfterFilterButtons();

$favoriteTitleTemplate = (!empty($arParams['FAVORITES_TITLE_TEMPLATE']) ? htmlspecialcharsbx($arParams['FAVORITES_TITLE_TEMPLATE']) : '');
if (mb_strlen($favoriteTitleTemplate) <= 0)
{
	$favoriteTitleTemplate = $APPLICATION->getProperty('FavoriteTitleTemplate', '');
}

$favoriteUrl = (!empty($arParams['FAVORITES_URL']) ? htmlspecialcharsbx($arParams['FAVORITES_URL']) : '');
if (mb_strlen($favoriteUrl) <= 0)
{
	$favoriteUrl = $APPLICATION->getProperty('FavoriteUrl', '');
}

$favoriteStar = Toolbar::hasFavoriteStar()? '<span class="ui-toolbar-star" id="uiToolbarStar" data-bx-title-template="' . $favoriteTitleTemplate . '" data-bx-url="' . $favoriteUrl . '"></span>' : '';

$titleProps = "";
if (Toolbar::getTitleMinWidth() !== null)
{
	$titleProps .= 'min-width:'.Toolbar::getTitleMinWidth().'px'.';';
}

if (Toolbar::getTitleMaxWidth() !== null)
{
	$titleProps .= 'max-width:'.Toolbar::getTitleMaxWidth().'px';
}

$titleStyles = !empty($titleProps) ? ' style="'.$titleProps.'"' : "";

?>

<div id="uiToolbarContainer" class="ui-toolbar"><?
	?><div id="pagetitleContainer" class="ui-toolbar-title-box"<?=$titleStyles?>><?
		?>
		<div class="ui-toolbar-title-inner">
			<div class="ui-toolbar-title-item-box">
				<span id="pagetitle" class="ui-toolbar-title-item"><?=$APPLICATION->getTitle(false, true)?></span>
				<?= $favoriteStar ?>
			</div><?
			?>
			<div style="display: none" class="ui-toolbar-subtitle">
				<span class="ui-toolbar-subtitle-item"></span>
				<span class="ui-toolbar-subtitle-control"></span>
			</div>
		</div>
		<?
	?></div>
	<?

	if($afterTitleButtons <> ''):
		?>
		<div class="ui-toolbar-after-title-buttons"><?= $afterTitleButtons ?></div><?
	endif;

	if($filter <> ''):
		?>
		<div class="ui-toolbar-filter-box"><?= $filter ?><?
		if($filterButtons <> ''): ?><?
			?>
			<div class="ui-toolbar-filter-buttons"><?= $filterButtons ?></div><?
		endif
		?></div><?
	endif;

	if($rightButtons <> ''):
		?>
		<div class="ui-toolbar-right-buttons"><?= $rightButtons ?></div><?
	endif;
?></div>

<script>
	BX.message({
		UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU: '<?= GetMessageJS('UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU') ?>',
		UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU: '<?= GetMessageJS('UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU') ?>',
		UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT: '<?= GetMessageJS('UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT') ?>',
		UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT: '<?= GetMessageJS('UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT') ?>',
		UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE: '<?= GetMessageJS('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE') ?>',
		UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR: '<?= GetMessageJS('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR') ?>',
	});

	BX.UI.ToolbarManager.create(Object.assign(<?=\Bitrix\Main\Web\Json::encode([
		"id" => Toolbar::getId(),
		"titleMinWidth" => Toolbar::getTitleMinWidth(),
		"titleMaxWidth" => Toolbar::getTitleMaxWidth(),
		"buttonIds" => array_map(function(\Bitrix\UI\Buttons\BaseButton $button){
			return $button->getUniqId();
		}, Toolbar::getButtons()),
	])?>,
		{
			target: document.getElementById('uiToolbarContainer')
		}
	));
	new BX.UI.Toolbar.Star();
</script>
