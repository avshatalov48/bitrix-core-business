<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Buttons\AirButtonStyle;

/**
 * @var $arResult array
 * @var $arParams array
 */

$this->setFrameMode(true);
Extension::load(['ui.feedback.form']);

$title = $arResult['TITLE'];
$jsParams = $arResult['JS_OBJECT_PARAMS'];

$nodeId = $arParams['ID'] . '-node';
$jsParams['node'] = $nodeId;
$jsParams['showTitle'] = ($arParams['SHOW_TITLE'] ?? 'Y') === 'Y';

?>

<div id="<?= htmlspecialcharsbx($nodeId) ?>"></div>

<script>
	new BX.UI.Feedback.Form(<?=Json::encode($jsParams)?>);
</script>

<?php
