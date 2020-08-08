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
$favoriteStar = Toolbar::hasFavoriteStar()? '<span class="ui-toolbar-star" id="uiToolbarStar"></span>' : '';

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
</script>
