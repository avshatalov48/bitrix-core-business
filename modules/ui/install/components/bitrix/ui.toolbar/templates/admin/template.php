<?

use Bitrix\UI\Toolbar\Facade\Toolbar;

/** @var CBitrixComponentTemplate $this */
/** @var array $arResult */
/** @global CMain $APPLICATION */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$this->setFrameMode(true);

$filter = Toolbar::getFilter();
$afterTitleButtons = Toolbar::renderAfterTitleButtons();
$rightButtons = Toolbar::renderRightButtons();
$filterButtons = Toolbar::renderAfterFilterButtons();
$favoriteStar = Toolbar::hasFavoriteStar() ? $arResult['FAVORITE_STAR'] : '';

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

$title = Toolbar::getTitle(false, true);
$title = Toolbar::hasEditableTitle()
	? sprintf('
		<span id="ui-editable-title-wrapper" class="ui-toolbar-title-item">
			<span id="pagetitle" class="ui-wrap-title-name-item ui-wrap-title-name">%s</span>
			<span class="ui-wrap-title-edit-button" style="display: none;"></span>
			<input type="text" class="ui-wrap-title-input" style="display: none;">
		</span>',
		$title
	)
	: sprintf('<span id="pagetitle" class="ui-toolbar-title-item">%s</span>', $title)
;

\Bitrix\Main\UI\Extension::load(['ui.design-tokens', 'ui.fonts.opensans']);
?>

<div id="uiToolbarContainer" class="ui-toolbar">
	<?php if (Toolbar::hasTitle()): ?>
		<div id="pagetitleContainer" class="ui-toolbar-title-box"<?=$titleStyles?>>
			<?= $title ?>
			<?= $favoriteStar ?>
		</div>
	<?php endif; ?>

	<?php if (strlen($afterTitleButtons)):
		?><div class="ui-toolbar-after-title-buttons"><?=$afterTitleButtons?></div><?
	endif;

	if (strlen($filter)):
		?><div class="ui-toolbar-filter-box"><?=$filter?><?
			if (strlen($filterButtons)): ?><?
				?><div class="ui-toolbar-filter-buttons"><?=$filterButtons?></div><?
			endif
		?></div><?
	endif;

	if (strlen($rightButtons)):
		?><div class="ui-toolbar-right-buttons"><?=$rightButtons?></div><?
	endif;
?></div>

<script>
	BX.ready(function(){
		BX.UI.ToolbarManager.create(Object.assign(<?=\Bitrix\Main\Web\Json::encode([
				"id" => Toolbar::getId(),
				"currentFavoriteId" => (int) $arResult['CURRENT_FAVORITE_ID'],
				"titleMinWidth" => Toolbar::getTitleMinWidth(),
				"titleMaxWidth" => Toolbar::getTitleMaxWidth(),
				"buttonIds" => array_map(function(\Bitrix\UI\Buttons\BaseButton $button)
				{
					return $button->getUniqId();
				}, Toolbar::getButtons()),
			])?>,
			{
				target: document.getElementById('uiToolbarContainer')
			}
		));
	});
</script>
