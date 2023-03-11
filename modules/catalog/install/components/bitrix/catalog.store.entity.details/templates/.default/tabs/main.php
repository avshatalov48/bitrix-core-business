<?php

/**
 * @var \CatalogStoreEntityDetails $component
 * @var array $arResult
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $APPLICATION;
?>
<div class="catalog-store-card-wrapper">
	<?php
	$APPLICATION->IncludeComponent(
		'bitrix:ui.form',
		'.default',
		array_merge($arResult['FORM'], [
			'SERVICE_URL' => $component->getPath() . '/ajax.php?' . bitrix_sessid_get(),
			'COMPONENT_AJAX_DATA' => [
				'COMPONENT_NAME' => $component->getName(),
				'ACTION_NAME' => 'save',
				'SIGNED_PARAMETERS' => $component->getSignedParameters(),
			],
		]),
		$component
	);
	?>
</div>
