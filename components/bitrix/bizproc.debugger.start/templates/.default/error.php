<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(['ui.alerts']);

/** @var CMain $APPLICATION */
/** @var array $arResult */

$bodyClass = $APPLICATION->GetPageProperty('BodyClass');
$APPLICATION->SetPageProperty(
	'BodyClass',
	($bodyClass ? $bodyClass . ' ' : '') . 'no-all-paddings no-hidden no-background'
);
?>
<div class="ui-alert ui-alert-danger">
	<span class="ui-alert-message"><?= htmlspecialcharsbx($arResult['errorMessage']) ?></span>
</div>


