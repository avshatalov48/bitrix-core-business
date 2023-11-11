<?php

use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Util;

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

$APPLICATION->IncludeComponent('bitrix:main.field.config.list', '', [
	'moduleId' => 'catalog',
	'entityId' => StoreTable::getUfId(),
	'title' => Loc::getMessage('CATALOG_COMPONENT_STORE_FIELD_CONFIG_LIST_TITLE_MSGVER_1'),
	'detailUrl' => '/settings/configs/userfield.php',
]);

$hint = Loc::getMessage('CATALOG_COMPONENT_STORE_FIELD_CONFIG_LIST_HINT_MSGVER_1', [
	'#LINK_START#' => '<a href="' . Util::getArticleUrlByCode('17415624') . '">',
	'#LINK_END#' => '</a>',
]);

?>
<script>
BX.ready(function() {

	const hint = '<?= CUtil::JSEscape($hint) ?>';

	document.querySelector('#pagetitle').appendChild(
		BX.create('span', {
			dataset: {
				hint,
				hintHtml: true,
				hintInteractivity: true,
			}
		})
	);
	BX.UI.Hint.init();
});
</script>
