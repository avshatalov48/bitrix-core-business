<?

use Bitrix\UI\Toolbar\Facade\Toolbar;

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

?>

<div id="uiToolbarContainer" class="ui-toolbar"><?
	?><div id="pagetitleContainer" class="ui-toolbar-title-box"<?=$titleStyles?>><?
		?><span id="pagetitle" class="ui-toolbar-title-item"><?=$APPLICATION->getTitle(false)?></span><?
		?><?= $favoriteStar ?><?
	?></div><?

	if (strlen($afterTitleButtons)):
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
