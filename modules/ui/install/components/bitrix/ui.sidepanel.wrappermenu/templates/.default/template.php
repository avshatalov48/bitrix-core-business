<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

/** @var $this \CBitrixComponentTemplate */
/** @var \CAllMain $APPLICATION */
/** @var array $arResult*/
/** @var array $arParams*/

?>

<?php $this->SetViewTarget('left-panel') ?>

<div <?if($arResult['ID']):?>id="<?=$arResult['ID']?>"<?endif;?> class="ui-sidepanel-sidebar">
    <div class="ui-sidepanel-head">
        <div class="ui-sidepanel-title">
			<?=$arResult['TITLE']?>
		</div>
    </div>
	<?= getWrapperMenu($arResult['ITEMS']);?>
</div>

<script type="text/javascript">
    BX.message({
        UI_SIDEPANEL_MENU_BUTTON_OPEN: '<?=GetMessageJS("UI_SIDEPANEL_MENU_BUTTON_OPEN")?>',
        UI_SIDEPANEL_MENU_BUTTON_CLOSE: '<?=GetMessageJS("UI_SIDEPANEL_MENU_BUTTON_CLOSE")?>'
    });

    BX.ready(function () {
        var sidepanelMenu = new BX.UI.DropdownMenu({
            container: document.getElementById("sidepanelMenu")
        });

        sidepanelMenu.init();
    });
</script>

<?php $this->EndViewTarget() ?>