<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var CBitrixComponentTemplate $this
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.dialogs.messagebox',
]);

$APPLICATION->SetTitle(
	Loc::getMessage('IBLOCK_PROPERTY_LIST_TEMPLATE_TITLE', [
		'#IBLOCK_NAME#' => $arResult['IBLOCK_NAME'],
	])
);

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	$arResult['GRID']
);

?>
<script>
	(function() {
		BX.message(<?= CUtil::PhpToJSObject(Loc::loadLanguageFile(__FILE__)) ?>);

		BX.Iblock.PropertyListGrid.Instance = new BX.Iblock.PropertyListGrid(<?= CUtil::PhpToJSObject([
			'id' => $arResult['GRID']['GRID_ID'],
		]) ?>);
	})();
</script>
