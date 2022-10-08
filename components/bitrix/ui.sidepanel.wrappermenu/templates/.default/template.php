<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

/** @var $this \CBitrixComponentTemplate */
/** @var \CAllMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

\Bitrix\Main\UI\Extension::load('ui.fonts.opensans');
?>

<?php $this->SetViewTarget($arResult['VIEW_TARGET']) ?>

<div <?if($arResult['ID']):?>id="<?=$arResult['ID']?>"<?endif;?> class="ui-sidepanel-sidebar">
	<?if(!empty($arResult['TITLE'])):?>
		<div class="ui-sidepanel-head">
			<h2 class="ui-sidepanel-title">
				<?=$arResult['TITLE']?>
			</h2>
		</div>
	<?endif;?>
	<?= getWrapperMenu($arResult['ITEMS']);?>
</div>

<script type="text/javascript">
    BX.message({
        UI_SIDEPANEL_MENU_BUTTON_OPEN: '<?=GetMessageJS("UI_SIDEPANEL_MENU_BUTTON_OPEN")?>',
        UI_SIDEPANEL_MENU_BUTTON_CLOSE: '<?=GetMessageJS("UI_SIDEPANEL_MENU_BUTTON_CLOSE")?>',
		UI_SIDEPANEL_MENU_ADD_ITEM: '<?=GetMessageJS("UI_SIDEPANEL_MENU_ADD_ITEM")?>'
    });

    BX.ready(function () {
        var sidepanelMenu = new BX.UI.DropdownMenu({
            container: document.getElementById("sidepanelMenu")
        });

        sidepanelMenu.init();
    });
</script>

<?php $this->EndViewTarget() ?>