<?php
/**
 * @global \CMain $APPLICATION
 * @var \CatalogProductGridComponent $component
 * @var $this \CBitrixComponentTemplate
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\UI\Extension;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

Extension::load('catalog.external-catalog-stub');

?>

<script>
	BX.ready(() => {
		BX.Catalog.ExternalCatalogStub.showDocsStub();

		BX.addCustomEvent(
			BX.SidePanel.Instance.getTopSlider(),
			'SidePanel.Slider:onClose',
			() =>
			{
				window.location.href = <?= CUtil::PhpToJSObject($arResult['STUB_REDIRECT']) ?>;
			}
		);
	});
</script>
