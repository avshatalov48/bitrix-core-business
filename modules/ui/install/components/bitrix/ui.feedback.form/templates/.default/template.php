<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Localization\Loc;

Extension::load(['sidepanel', 'ui.buttons', 'ui.feedback.form']);
$buttonId = $arParams['ID'] . '-button';

$title = $arParams['TITLE'] ?: Loc::getMessage('UI_FEEDBACK_FORM_BUTTON');
$portal = $arParams['PORTAL_URI'] ?: 'https://landing.bitrix24.ru';

if ($arParams['VIEW_TARGET'])
{
	$this->SetViewTarget($arParams['VIEW_TARGET']);
}
?>

<div id="<?=htmlspecialcharsbx($buttonId)?>" class="ui-btn ui-btn-light-border">
	<?=htmlspecialcharsbx($title);?>
</div>
<script>
	new BX.UI.Feedback.Form(<?=Json::encode([
		'id' => $arParams['ID'],
		'button' => $buttonId,
		'form' => $arResult['FORM'],
		'presets' => $arResult['PRESETS'],
		'title' => $title,
		'portal' => $portal,
	])?>);
</script>
<?
if ($arParams['VIEW_TARGET'])
{
	$this->EndViewTarget();
}