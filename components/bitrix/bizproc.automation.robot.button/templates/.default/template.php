<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI\Extension;
use Bitrix\UI\Buttons;

Extension::load([
	'ui.counter',
	'ui.fonts.opensans',
	'ui.actions-bar',
	'bizproc.local-settings',
]);

/**
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 */

$button = Buttons\Button::create()
	->setTag($arResult['TAG'])
	->setIcon($arResult['ICON'])
	->addClass('ui-btn-themes')
	->setText($arResult['TEXT'])
	->setCounterStyle($arResult['COUNTER_STYLE'])
	->setStyle('--style-outline')
	->addClass('robot-button-container')
	->setCounter($arResult['COUNTER'])
	->setNoCaps()
	->setRound()
;

$button->setAirDesign();

if (is_array($arResult['CUSTOM_CLASSES']))
{
	foreach ($arResult['CUSTOM_CLASSES'] as $cssClass)
	{
		$button->addClass($cssClass);
	}
}

if (!empty($arResult['ON_CLICK']))
{
	$button->bindEvent('click', new Buttons\JsCode($arResult['ON_CLICK']));
}

if (!empty($arResult['URL']) && empty($arResult['ON_CLICK']))
{
	$button->setLink($arResult['URL']);
}

$arResult['ROBOT_BUTTON_HTML'] = $button->render(false);

$buttonUniqId = $button->getUniqId();

?>

<?= $arParams['RENDER_BUTTON_TO_RESULT'] ?? false ? '' : $arResult['ROBOT_BUTTON_HTML'] ?>

<script>
	BX.ready(() =>
	{
		const buttonUniqId = '<?= CUtil::JSEscape($buttonUniqId) ?>';
		const availableNewRobots = <?= \Bitrix\Main\Web\Json::encode($arResult['AVAILABLE_NEW_ROBOTS']) ?>;

		new BX.Bizproc.Automation.RobotButton({
			buttonId: buttonUniqId,
		});

		BX.Bizproc.Automation.RobotButton.updateButtonCounter(availableNewRobots)
	});
</script>