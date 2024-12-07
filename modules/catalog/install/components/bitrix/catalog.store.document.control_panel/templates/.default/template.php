<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @global \CMain $APPLICATION */
/** @var array $arResult */
/** @var \CatalogStoreDocumentControlPanelComponent $component */
/** @var \CBitrixComponentTemplate $this */

global $APPLICATION;

\Bitrix\Main\UI\Extension::load([
	'catalog.store-enable-wizard',
	'ui.fonts.opensans',
]);
CBitrixComponent::includeComponentClass("bitrix:catalog.feedback");

if ($arResult['IS_IFRAME_MODE'])
{
	?>
	<div class="catalog-store-documents-top">
		<div class="catalog-store-documents-title-box">
			<span class="catalog-store-documents-title"><?= \Bitrix\Main\Localization\Loc::getMessage('STORE_DOCUMENTS_CONTROL_PANEL_TITLE') ?></span>
		</div>

		<div class="pagetitle-container pagetitle-align-right-container">
			<?(new CatalogFeedbackComponent())->renderIntegrationRequestButton()?>
		</div>

	</div>
	<?php
}

$APPLICATION->IncludeComponent(
	'bitrix:main.interface.buttons',
	'',
	[
		'ID' => 'store_documents',
		'ITEMS' => $arResult['ITEMS'],
	]
);
