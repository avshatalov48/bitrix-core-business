<?

use Bitrix\Main\ModuleManager;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

$this->setFrameMode(true);

$favoriteStar = \Bitrix\UI\Toolbar\Facade\Toolbar::hasFavoriteStar()? '<span class="ui-toolbar-star" id="uiToolbarStar"></span>' : '';
?>

<div class="pagetitle-wrap <?=$APPLICATION->getProperty("TitleClass")?>">
	<div class="pagetitle-inner-container">
		<div class="pagetitle-menu pagetitle-container pagetitle-last-item-in-a-row" id="pagetitle-menu"><?
			echo $GLOBALS["INTRANET_TOOLBAR"]->__display();
			echo $APPLICATION->getViewContent("pagetitle");
		?></div>
		<div class="pagetitle">
			<span id="pagetitle" class="pagetitle-item"><?=$APPLICATION->getTitle(false, true);?></span>
			<?= $APPLICATION->getViewContent("in_pagetitle"); ?>
			<?= $favoriteStar; ?>
		</div>
		<?=$APPLICATION->getViewContent("inside_pagetitle")?>
	</div>
</div>