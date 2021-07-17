<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CBitrixComponentTemplate $this */
/** @var string $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . $this->getFolder() . '/template.php');

$this->setFrameMode(true);

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

?><div class="pagetitle-wrap <?= $APPLICATION->getProperty('TitleClass') ?>">
	<div class="pagetitle-inner-container">
		<div class="pagetitle-menu pagetitle-container pagetitle-last-item-in-a-row" id="pagetitle-menu"><?php
			echo $GLOBALS["INTRANET_TOOLBAR"]->__display();
			echo $APPLICATION->getViewContent("pagetitle");
		?></div>
		<div class="pagetitle">
			<span id="pagetitle" class="pagetitle-item"><?= $APPLICATION->getTitle(false, true); ?></span>
			<?= $APPLICATION->getViewContent("in_pagetitle"); ?>
			<?= $favoriteStar; ?>
		</div>
		<?=$APPLICATION->getViewContent("inside_pagetitle")?>
	</div>
</div>
<script>
	BX.message({
		UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU: '<?= GetMessageJS('UI_TOOLBAR_ADD_PAGE_TO_LEFT_MENU') ?>',
		UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU: '<?= GetMessageJS('UI_TOOLBAR_DELETE_PAGE_FROM_LEFT_MENU') ?>',
		UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT: '<?= GetMessageJS('UI_TOOLBAR_ITEM_WAS_ADDED_TO_LEFT') ?>',
		UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT: '<?= GetMessageJS('UI_TOOLBAR_ITEM_WAS_DELETED_FROM_LEFT') ?>',
		UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE: '<?= GetMessageJS('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE') ?>',
		UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR: '<?= GetMessageJS('UI_TOOLBAR_STAR_TITLE_DEFAULT_PAGE_DELETE_ERROR') ?>',
	});

	new BX.UI.Toolbar.Star();
</script>