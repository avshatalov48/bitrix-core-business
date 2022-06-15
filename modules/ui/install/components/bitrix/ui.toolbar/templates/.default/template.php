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

\Bitrix\Main\UI\Extension::load('ui.design-tokens');

$filter = Toolbar::getFilter();
$afterTitleButtons = Toolbar::renderAfterTitleButtons();
$rightButtons = Toolbar::renderRightButtons();
$filterButtons = Toolbar::renderAfterFilterButtons();
$beforeTitleHtml = Toolbar::getBeforeTitleHtml();
$afterTitleHtml = Toolbar::getAfterTitleHtml();
$rightCustomHtml = Toolbar::getRightCustomHtml();

$favoriteTitleTemplate = (!empty($arParams['~FAVORITES_TITLE_TEMPLATE']) ? $arParams['~FAVORITES_TITLE_TEMPLATE'] : '');
if (mb_strlen($favoriteTitleTemplate) <= 0)
{
	$favoriteTitleTemplate = $APPLICATION->getProperty('FavoriteTitleTemplate', '');
}

$favoriteUrl = (!empty($arParams['~FAVORITES_URL']) ? $arParams['~FAVORITES_URL'] : '');
if (mb_strlen($favoriteUrl) <= 0)
{
	$favoriteUrl = $APPLICATION->getProperty('FavoriteUrl', '');
}

$favoriteStar = Toolbar::hasFavoriteStar()? '<span class="ui-toolbar-star" id="uiToolbarStar" data-bx-title-template="' . htmlspecialcharsbx($favoriteTitleTemplate) . '" data-bx-url="' . htmlspecialcharsbx($favoriteUrl) . '"></span>' : '';

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

<div id="uiToolbarContainer" class="ui-toolbar"><?php

	?><div id="pagetitleContainer" class="ui-toolbar-title-box"<?=$titleStyles?>><?php
		?>
		<div class="ui-toolbar-title-inner">
			<div class="ui-toolbar-title-item-box">
				<?php
				if (!empty($beforeTitleHtml)):
					?><div class="ui-toolbar-before-title"><?=$beforeTitleHtml?></div><?
				endif;
				?>
				<span id="pagetitle" class="ui-toolbar-title-item"><?=$APPLICATION->getTitle(false, true)?></span>
				<?= $favoriteStar ?>
			</div><?php
			?>
			<div style="display: none" class="ui-toolbar-subtitle">
				<span class="ui-toolbar-subtitle-item"></span>
				<span class="ui-toolbar-subtitle-control"></span>
			</div>
		</div>
		<?php
	?></div>
	<?php

	if($afterTitleButtons <> ''):
		?>
		<div class="ui-toolbar-after-title-buttons"><?= $afterTitleButtons ?></div><?php
	endif;

	if (!empty($afterTitleHtml)):
		?><div class="ui-toolbar-after-title"><?=$afterTitleHtml?></div><?
	endif;

	if($filter <> ''):
		?>
		<div class="ui-toolbar-filter-box"><?= $filter ?><?php
		if($filterButtons <> ''): ?><?php
			?>
			<div class="ui-toolbar-filter-buttons"><?= $filterButtons ?></div><?php
		endif
		?></div><?php
	endif;

	if($rightButtons <> ''):
		?>
		<div class="ui-toolbar-right-buttons"><?= $rightButtons ?></div><?php
	endif;

	if (!empty($rightCustomHtml)):
		?><div class="ui-toolbar-after-title"><?=$rightCustomHtml?></div><?
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
