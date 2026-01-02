<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Web\Json;
use Bitrix\Main\UI\Extension;
use Bitrix\UI\Buttons\AirButtonStyle;

/**
 * @var $arResult array
 * @var $arParams array
 */

$this->setFrameMode(true);
Extension::load(['sidepanel', 'ui.buttons', 'ui.feedback.form']);

$title = $arResult['TITLE'];
$jsParams = $arResult['JS_OBJECT_PARAMS'];
$buttonId = $arParams['ID'] . '-button';
$jsParams['button'] = $buttonId;

if ($arParams['VIEW_TARGET'])
{
	$this->SetViewTarget($arParams['VIEW_TARGET']);
}
?>

<?php
if (isset($arParams['air']) && $arParams['air'] === true):
	$airButton = new \Bitrix\UI\Buttons\Button([
		'air' => true,
		'text' => $title,
		'style' => AirButtonStyle::OUTLINE,
		'color' => \Bitrix\UI\Buttons\Color::LIGHT_BORDER,
	]);

	$airButton->addAttribute('id', $buttonId);

	if (isset($arParams['USE_UI_TOOLBAR']) && $arParams['USE_UI_TOOLBAR'] === 'Y')
	{
		\Bitrix\UI\Toolbar\Facade\Toolbar::addButton($airButton);
	}
	else
	{
		echo $airButton->render();
	}
?>
<?php else: ?>
	<div id="<?=htmlspecialcharsbx($buttonId)?>" class="ui-btn ui-btn-themes ui-btn-light-border">
		<?=htmlspecialcharsbx($title);?>
	</div>
<?php endif; ?>
<script>
	new BX.UI.Feedback.Form(<?=Json::encode($jsParams)?>);
</script>
<?
if ($arParams['VIEW_TARGET'])
{
	$this->EndViewTarget();
}
