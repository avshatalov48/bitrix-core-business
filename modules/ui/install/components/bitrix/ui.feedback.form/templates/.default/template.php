<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;

$this->setFrameMode(true);

Extension::load(['sidepanel', 'ui.buttons', 'ui.feedback.form']);
$buttonId = $arParams['ID'] . '-button';

$title = $arResult['TITLE'];
$jsParams = $arResult['JS_OBJECT_PARAMS'];
$jsParams['button'] = $buttonId;

if ($arParams['VIEW_TARGET'])
{
	$this->SetViewTarget($arParams['VIEW_TARGET']);
}
?>

<div id="<?=htmlspecialcharsbx($buttonId)?>" class="ui-btn ui-btn-themes ui-btn-light-border">
	<?=htmlspecialcharsbx($title);?>
</div>
<script>
	new BX.UI.Feedback.Form(<?=Json::encode($jsParams)?>);
</script>
<?
if ($arParams['VIEW_TARGET'])
{
	$this->EndViewTarget();
}