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

foreach ($arResult['ADDITIONAL_FIELDS'] as $field)
{
	call_user_func($arResult['SHOW_FIELD_CALLBACK'], $field, $arResult['VALUES']);
}

if (!empty($arResult['SETTINGS_HTML'])):
	?>
	<div class="iblock-property-details-input">
		<table class="iblock-property-details-settings-table">
			<?= $arResult['SETTINGS_HTML'] ?>
		</table>
	</div>
	<?php
endif;
