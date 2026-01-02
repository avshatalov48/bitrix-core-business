<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

/**
 * @var CMain $APPLICATION
 * @var CBitrixComponent $component
 * @var array $arResult
 */

\Bitrix\Main\UI\Extension::load(['ui.design-tokens', 'ui.fonts.opensans']);

global $APPLICATION;
$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings bx-ui-info-error');
?>
<div class='ui-info-error'>
	<div class='ui-info-error-inner'>
		<div class='ui-info-error-title'><?= $arResult['TITLE'] ?></div>
		<div class='ui-info-error-subtitle'><?= $arResult['DESCRIPTION'] ?></div>
		<div class='ui-info-error-img'>
			<div class='ui-info-error-img-inner'></div>
		</div>
	</div>
</div>
