<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

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

Extension::load([
	'date',
	'ui.vue3',
	'ui.forms',
	'ui.buttons',
	'ui.buttons.icons',
]);

$containerId = uniqid('iblockPropertyFieldDate');
$options = [
	'selector' => '#' . $containerId,
	'controlName' => $arParams['NAME'],
	'isShowTime' => $arResult['SHOW_TIME'],
	'isMultiple' => $arResult['MULTIPLE'],
	'values' => $arResult['VALUES'],
];

$controlStyles =
	$arResult['SHOW_TIME']
		? 'ui-ctl-datetime'
		: 'ui-ctl-date'
;
?>
<div id="<?= htmlspecialcharsbx($containerId) ?>" class="iblock-property-field-date">
	<div
		v-for="(item, key) in items"
		:key="key"
		class="ui-ctl ui-ctl-after-icon <?=$controlStyles; ?> iblock-property-field-date-control"
	>
		<div
			class="ui-ctl-ext-after ui-ctl-icon-calendar"
			@click="showCalendar(item, $event.target)"
		></div>
		<input class="ui-ctl-element" type="text" :name="item.name" :value="item.value" />
	</div>
	<?php
	if ($arResult['MULTIPLE']):
		?><a href="javascript:;" class="ui-link iblock-property-field-date-add-btn" @click.prevent="appendNew"><?= Loc::getMessage('IBLOCK_CMP_PROPERTY_FIELD_PUBLIC_EDIT_DATE_ADD_BUTTON') ?></a><?php
	endif;
	?>
</div>
<script>
	BX.ready(function() {
		new BX.Iblock.PropertyFieldDate(<?= CUtil::PhpToJSObject($options); ?>);
	});
</script>
