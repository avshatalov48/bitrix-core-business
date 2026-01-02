<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Buttons\AirButtonStyle;
use Bitrix\UI\Buttons\Button;
use Bitrix\UI\Buttons\Color;
use Bitrix\UI\Buttons\Icon;
use Bitrix\UI\Buttons\Size;
use Bitrix\UI\Toolbar;

/** @var CBitrixComponentTemplate $this */
/** @var string $templateFolder */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$this->setFrameMode(true);

$manager = Toolbar\Manager::getInstance();
$toolbar = $manager->getToolbarById($arResult["TOOLBAR_ID"]) ?: $manager->createToolbar($arResult["TOOLBAR_ID"], []);
if (!$toolbar->isEnabled())
{
	return;
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.icon-set.outline',
	'ui.icon-set.api.core',
	'ui.icon-set.main',
	'ui.design-tokens.air',
]);

$filter = $toolbar->getFilter();
$afterTitleButtons = $toolbar->renderAfterTitleButtons();
$rightButtons = $toolbar->renderRightButtons();
$filterButtons = $toolbar->renderAfterFilterButtons();
$beforeTitleBoxHtml = $toolbar->getBeforeTitleBoxHtml();
$beforeTitleHtml = $toolbar->getBeforeTitleHtml();
$afterTitleHtml = $toolbar->getAfterTitleHtml();
$rightCustomHtml = $toolbar->getRightCustomHtml();
$rightCustomHtmlOptions = $toolbar->getRightCustomHtmlOptions();
$underTitleHtml = $toolbar->getUnderTitleHtml();

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

$favoriteStar = $toolbar->hasFavoriteStar()? '<span class="ui-toolbar-star ui-icon-set__scope" id="uiToolbarStar" data-bx-title-template="' . htmlspecialcharsbx($favoriteTitleTemplate) . '" data-bx-url="' . htmlspecialcharsbx($favoriteUrl) . '"></span>' : '';

$title = $toolbar->getTitle(false, true);
$title = $toolbar->hasEditableTitle()
	? sprintf('
		<span id="ui-editable-title-wrapper" class="ui-toolbar-title-item">
			<span id="pagetitle" class="ui-wrap-title-name-item ui-wrap-title-name">%s</span>
			<input type="text" class="ui-wrap-title-input ui-toolbar-edit-title-input" style="display: none;">
		</span>',
		$title
	)
	: sprintf('<span id="pagetitle" class="ui-toolbar-title-item">%s</span>', $title)
;

$titleProps = "";
if ($toolbar->getTitleMinWidth() !== null)
{
	$titleProps .= 'min-width:'.$toolbar->getTitleMinWidth().'px'.';';
}

if ($toolbar->getTitleMaxWidth() !== null)
{
	$titleProps .= 'max-width:'.$toolbar->getTitleMaxWidth().'px';
}

$titleStyles = !empty($titleProps) ? ' style="'.$titleProps.'"' : "";

$airDesignClassnameModifier = $arResult['USE_AIR_DESIGN'] ? '--air' : '';

$copyLinkButtonHtml = '';

$copyLinkButton = $toolbar->getCopyLinkButton();

$iconHoverableModifier = $toolbar->hasAirDesign() ? '--hoverable-default' : '';

if (is_null($copyLinkButton) === false)
{
	$copyLinkButtonLink = htmlspecialcharsbx($copyLinkButton['link'] ?? '');
	$copyLinkButtonTitle = htmlspecialcharsbx($copyLinkButton['title'] ?? Loc::getMessage('UI_TOOLBAR_COPY_LINK_BUTTON_TITLE'));
	$copyLinkButtonCopyingMessage = htmlspecialcharsbx($copyLinkButton['successfulCopyMessage'] ?? Loc::getMessage('UI_TOOLBAR_COPY_LINK_BUTTON_SUCCESS'));

	$copyLinkButtonHtml = <<<HTML
<button id="ui-toolbar-copy-link-button" title="{$copyLinkButtonTitle}" data-link="{$copyLinkButtonLink}" data-message="{$copyLinkButtonCopyingMessage}" class="ui-toolbar-copy-link-button">
	<div class="ui-toolbar-copy-link-button-icon ui-icon-set $iconHoverableModifier"></div>
</button>
HTML;
}

$editTitleButton = '';
if ($toolbar->hasEditableTitle())
{
	$editTitleButton = '<button class="ui-toolbar-edit-title-button">
		<span class="ui-toolbar-edit-title-button-icon ui-icon-set ' . $iconHoverableModifier . '"></span>
	</button>';
}

$saveTitleEditButton = new Button([
	'text' => '',
	'icon' => Icon::DONE,
	'size' => Size::EXTRA_SMALL,
	'color' => Color::PRIMARY,
	'style' => AirButtonStyle::FILLED,
	'air' => true,
]);


$saveTitleEditButton->addAttribute('id', 'ui-toolbar-save-title-button');
$saveTitleEditButton->setCollapsed(true);

$cancelTitleEditButton = new Button([
	'text' => '',
	'icon' => Icon::CANCEL,
	'size' => Size::EXTRA_SMALL,
	'color' => Color::LIGHT_BORDER,
	'style' => AirButtonStyle::OUTLINE,
	'air' => true,
]);

$cancelTitleEditButton->addAttribute('id', 'ui-toolbar-cancel-title-edit-button');
$cancelTitleEditButton->setCollapsed(true);

$saveTitleEditButtonHtml = $saveTitleEditButton->render();
$cancelTitleEditButtonHtml = $cancelTitleEditButton->render();

$editResultButtons = '
<div id="ui-toolbar-title-edit-result-buttons" class="ui-toolbar-title-edit-result-buttons">
	' . $saveTitleEditButtonHtml . $cancelTitleEditButtonHtml . '
</div>
';

$multiLineTitleClassnameModifier = $toolbar->isMultiLineTitleEnabled() ? '--multiline-title' : '';
$noShrinkTitleModifier = $toolbar->isTitleNoShrink() ? '--no-shrink' : '';

?>
<div id="<?=$arResult["CONTAINER_ID"]?>" class="ui-toolbar <?= $airDesignClassnameModifier ?> <?= $multiLineTitleClassnameModifier ?>"><?php
	if ($toolbar->hasTitle()):
	?><div id="pagetitleContainer" class="ui-toolbar-title-box <?= $noShrinkTitleModifier ?>"<?=$titleStyles?>><?php
		if(!empty($beforeTitleBoxHtml)): ?>
			<div class="ui-toolbar-before-title-box"><?= $beforeTitleBoxHtml ?></div>
		<?php endif; ?>
		<div class="ui-toolbar-title-inner">
			<div class="ui-toolbar-title-item-box">
				<?php
				if (!empty($beforeTitleHtml)):
					?><div class="ui-toolbar-before-title"><?=$beforeTitleHtml?></div><?
				endif;

				echo $title;

				if (!empty($title))
				{
					echo $favoriteStar;
				}

				if($toolbar->hasEditableTitle()): ?>
					<div id="ui-toolbar-title-edit-result-buttons" class="ui-toolbar-title-edit-result-buttons">
						<?= $saveTitleEditButton->render() ?>
						<?= $cancelTitleEditButton->render() ?>
					</div>
				<?php endif; ?>
				<div class="ui-toolbar-after-title-box">
					<div id="ui-toolbar-title-item-box-buttons" class="ui-toolbar-title-item-box-buttons">
						<?= $editTitleButton ?>
						<?= $copyLinkButtonHtml ?>
					</div>
				</div>
				<div class="ui-toolbar-after-title"><?= $afterTitleHtml ?></div>
			</div><?php
			?>
			<?php
			if (!empty($underTitleHtml)):
				?><div class="ui-toolbar-subtitle"><?=$underTitleHtml?></div><?
			endif;
			?>
		</div>
		<?php
	?></div>
	<?php
	endif;

	if($afterTitleButtons <> ''):
		?>
		<div class="ui-toolbar-after-title-buttons"><?= $afterTitleButtons ?></div><?php
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
		$align = $rightCustomHtmlOptions['align'] ?? '';
		$alignClass = '';
		if ($align === 'right')
		{
			$alignClass = '--right';
		}
		?><div class="ui-toolbar-right-custom-html <?=$alignClass?>"><?=$rightCustomHtml?></div><?
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
		"id" => $toolbar->getId(),
		"titleMinWidth" => $toolbar->getTitleMinWidth(),
		"titleMaxWidth" => $toolbar->getTitleMaxWidth(),
		"buttonIds" => array_map(function(\Bitrix\UI\Buttons\BaseButton $button){
			return $button->getUniqId();
		}, $toolbar->getButtons()),
		"titleEditor" => [
//			'selector' => $toolbar->getEditableTitleSelector(),
			'defaultTitle' => $toolbar->getDefaultEditableTitle(),
			'active' => $toolbar->hasEditableTitle(),
		],
	])?>,
		{
			target: document.getElementById('<?=$arResult["CONTAINER_ID"]?>')
		}
	));
	<?php if ($toolbar->hasFavoriteStar()):?>
	new BX.UI.ToolbarStar();
	<?php endif;?>
</script>
