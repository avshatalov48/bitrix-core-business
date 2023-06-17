<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */
/** @var \CBitrixComponent $component */

$APPLICATION->setTitle(GetMessage('LANDING_TPL_AI_SETTING_TITLE'));
?>

<?php $APPLICATION->IncludeComponent(
	'bitrix:ai.settings',
	'.default',
	array(
	),
	$component
);?>

<style>
	.landing-slider-pagetitle-wrap {
		font: 24px/26px var(--ui-font-family-secondary, var(--ui-font-family-open-sans));
		font-weight: var(--ui-font-weight-light, 300);
		color: #333;
		padding: 0 20px;
	}
</style>
