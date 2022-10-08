<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
]);
?>

<div class="calendar-wrap">
<div class="calendar-error-wrap">
	<div class="calendar-error-icon"></div>
	<h2 class="calendar-error-title"><?=$arResult['TITLE']?></h2>
	<p class="calendar-error-desc"><?=$arResult['CONTENT']?></p>
</div>
</div>