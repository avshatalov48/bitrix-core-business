<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var \CMain $APPLICATION */

/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Loc::loadMessages(__FILE__);

Extension::load(['ui.feedback.form']);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));

$isTrialActive = $arResult['IS_TRIAL_ACTIVE'] ?? false;
$isTrialAlreadyActivated = $arResult['IS_TRIAL_ALREADY_ACTIVATED'] ?? false;

$allowedTypes = ['1', '2', '3', '4'];
$type = isset($arResult['TYPE']) && in_array((string)$arResult['TYPE'], $allowedTypes, true)
	? (string)$arResult['TYPE']
	: '1';

$titles = [
	'1' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TITLE'),
	'2' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TITLE_2'),
	'3' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TITLE_3'),
	'4' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TITLE_4'),
];
$texts = [
	'1' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TEXT_MSGVER_1'),
	'2' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TEXT_2'),
	'3' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TEXT_3'),
	'4' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_TEXT_4'),
];
$buttonTexts = [
	'1' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_BUTTON'),
	'2' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_BUTTON_2'),
	'3' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_BUTTON_3'),
	'4' => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_AUTO_BUTTON_4'),
];
$links = [
	'1' => 'https://cdn.bitrix24.site/bitrix/images/landing/vibe/auto/vibe-auto-1.png',
	'2' => 'https://cdn.bitrix24.site/bitrix/images/landing/vibe/auto/vibe-auto-2.png',
	'3' => 'https://cdn.bitrix24.site/bitrix/images/landing/vibe/auto/vibe-auto-3.png',
	'4' => 'https://cdn.bitrix24.site/bitrix/images/landing/vibe/auto/vibe-auto-4.png',
];

$title = $titles[$type];
$text = $texts[$type];
$buttonText = $buttonTexts[$type];
$src = $links[$type];

?>

<div class="g-pl-30 g-pr-30 g-pt-30 g-pb-25 g-cursor-default" id="<?= $id ?>">
	<div class="row no-gutters">
		<div class="col-lg-8 col-md-6 col-sm-12 m-auto">
			<div class="col-10">
				<div
					class="landing-block-title g-font-weight-600 g-font-size-28 g-color g-mb-25 g-line-height-1_3"
					style="--color: #1f86ff;"
				>
					<?= $title ?>
				</div>
				<div
					class="landing-block-subtitle g-font-weight-400 g-font-size-15 g-color g-mb-30 g-max-width-400 g-line-height-1_3"
					style="--color: #000;"
				>
					<?= $text ?>
				</div>
				<div
					id="feedback-button"
					class="landing-block-button btn justify-content-center align-items-center g-pl-12 g-pr-12 g-rounded-10 g-font-size-16 text-center g-cursor-pointer col-5 g-color"
					style="--color: #fff; display: inline-flex; background-color: #1f86ff;"
				>
					<?= $buttonText ?>
				</div>
			</div>
		</div>
		<div class="col-lg-4 col-md-6 col-sm-12">
			<img style="width: 100%; height: auto; object-fit: cover;" alt="" src="<?= $src ?>" class="g-cursor-default">
		</div>
	</div>
</div>

<script>
	BX.ready(function() {
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
				const option = <?= Json::encode($arResult['FEEDBACK_FORM'] ?? []) ?>;
				new BX.Landing.Widget.VibeAuto(widgetElement, option);
			}
		}
	});
</script>
