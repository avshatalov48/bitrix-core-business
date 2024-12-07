<?php

use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CMain $APPLICATION */
/** @var array $arResult */

Extension::load([
	'catalog.store-enable-wizard',
]);
?>

<div data-role="catalog-store-enable-wizard"></div>

<script>
	BX.ready(() => {
		new BX.Catalog.Store.EnableWizard(
			<?php echo \CUtil::PhpToJSObject($arResult['options']);?>,
			<?php echo \CUtil::PhpToJSObject($arResult['analytics']);?>
		).render(
			document.querySelector('div[data-role="catalog-store-enable-wizard"]')
		);
	});
</script>
