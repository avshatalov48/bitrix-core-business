<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!$arResult['IS_IFRAME'])
{
	return;
}

CJSCore::init('intranet_theme_picker');

$sliderMenuContainerId = 'sonet-card-slider-menu';

$APPLICATION->IncludeComponent('bitrix:ui.sidepanel.wrappermenu', '', [
	'ID' => $sliderMenuContainerId,
	'ITEMS' => $arResult['MENU_ITEMS'],
	'TITLE' => $arResult['GROUP_NAME'],
]);

$this->SetViewTarget('left-panel');

?><script>
	BX.ready(function() {
		(new BX.Socialnetwork.WorkgroupSliderMenu()).init({
			menuNodeId: '<?= CUtil::JSEscape($sliderMenuContainerId) ?>',
		});
	});
</script><?php

$this->EndViewTarget();
