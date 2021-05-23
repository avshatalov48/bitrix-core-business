<?

use Bitrix\UI\Toolbar;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$this->setFrameMode(true);

$toolbarManager = Toolbar\Manager::getInstance();
if($arResult["TOOLBAR_ID"] != "")
{
	$toolbar = $toolbarManager->getToolbarById($arResult["TOOLBAR_ID"]);
}
else
{
	$toolbar = $toolbarManager->getToolbarById(Toolbar\Facade\Toolbar::DEFAULT_ID);
}

$filter = $toolbar->getFilter();
$afterTitleButtons = $toolbar->renderAfterTitleButtons();
$rightButtons = $toolbar->renderRightButtons();
$filterButtons = $toolbar->renderAfterFilterButtons();
?>

<div id="<?=$arResult["CONTAINER_ID"]?>" class="ui-toolbar">
	<? if($afterTitleButtons <> ''): ?><?
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

	if($rightButtons <> ''):?><?
		?>
		<div class="ui-toolbar-right-buttons"><?= $rightButtons ?></div><?
	endif ?>
</div>

<script>
	BX.UI.ToolbarManager.create(Object.assign(<?=\Bitrix\Main\Web\Json::encode([
		"id" => $toolbar->getId(),
		"titleMinWidth" => $toolbar->getTitleMinWidth(),
		"titleMaxWidth" => $toolbar->getTitleMaxWidth(),
		"buttonIds" => array_map(function(\Bitrix\UI\Buttons\BaseButton $button){
			return $button->getUniqId();
		}, $toolbar->getButtons()),
    ])?>,
		{
			target: document.getElementById('<?=$arResult["CONTAINER_ID"]?>')
		}
	));
</script>
