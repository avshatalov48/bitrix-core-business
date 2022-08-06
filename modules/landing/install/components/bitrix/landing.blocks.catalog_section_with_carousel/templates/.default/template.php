<?php

/**
 * @global \CMain $APPLICATION
 */
// use Bitrix\Main\Text\HtmlFilter;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>

<div class="landing-component">
	<?php if ($arResult['EDIT_MODE'] || $arResult['SHOW_ELEMENT_SECTION']): ?>
		<div class="landing-component">
			<?php $APPLICATION->IncludeComponent(
				'bitrix:catalog.section',
				'store_v3',
				$arResult['FIRST_COMPONENT_PARAMS'],
				false
			); ?>
		</div>
	<?php endif; ?>
	<?php if (!$arResult['EDIT_MODE']): ?>
		<div class="landing-component">
			<?php $APPLICATION->IncludeComponent(
				'bitrix:catalog.section',
				'store_v3',
				$arResult['SECOND_COMPONENT_PARAMS'],
				false
			); ?>
		</div>
	<?php endif; ?>
</div>