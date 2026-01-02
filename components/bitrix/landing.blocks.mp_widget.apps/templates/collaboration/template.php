<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load([
	'ui.qrauthorization',
	'ui.icon-set.main',
	'ui.icon-set.social',
	'ui.buttons',
]);

$imgSrc = 'https://cdn.bitrix24.site/bitrix/images/landing/vibe/collaboration/apps-en.png';
if (in_array(\CBitrix24::getPortalZone(), ['ru', 'kz', 'by', 'uz'], true))
{
	$imgSrc = 'https://cdn.bitrix24.site/bitrix/images/landing/vibe/collaboration/apps-ru.png';
}

$title = Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_TITLE');
$text1 = Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_TEXT_1');
$text2 = Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_TEXT_2');
$text3 = Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_TEXT_3');
$mobileButtonText = Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_BUTTON_TEXT');
$desktopButtonText = Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_DESKTOP_TEXT');
$buttonLink = isset($arResult['DESKTOP_APP_LINK']) && is_string($arResult['DESKTOP_APP_LINK'])
	? $arResult['DESKTOP_APP_LINK']
	: '';

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
?>

<div class="landing-widget-apps-collaboration row no-gutters g-cursor-default" id="<?= $id ?>">
	<div class="col">
		<div
			class="landing-block-node-title g-font-weight-600 g-font-size-35 g-line-height-1_1 g-color g-mb-40"
			style="--color: #000000;"
		>
			<?= $title ?>
		</div>
		<div class="g-mb-60 g-width-80x">
			<div
				class="d-flex g-mb-18 g-font-size-20 g-color g-pos-rel landing-collab-list-item align-items-start"
				style="--color: #333;"
			>
				<i
					class="landing-block-node-card-icon fa fa-check g-mr-12 g-font-size-18 g-pa-4 g-rounded-50 g-bg g-color"
					style="--bg: #0075FF33; --color: #0075FF;"
				></i>
				<div class="g-line-height-1_1">
					<?= $text1 ?>
				</div>
			</div>
			<div
				class="d-flex g-mb-18 g-font-size-20 g-color g-pos-rel landing-collab-list-item align-items-start"
				style="--color: #333;"
			>
				<i
					class="landing-block-node-card-icon fa fa-check g-mr-12 g-font-size-18 g-pa-4 g-rounded-50 g-bg g-color"
					style="--bg: #0075FF33; --color: #0075FF;"
				></i>
				<div class="g-line-height-1_1">
					<?= $text2 ?>
				</div>
			</div>
			<div
				class="d-flex g-mb-18 g-font-size-20 g-color g-pos-rel landing-collab-list-item align-items-start"
				style="--color: #333;"
			>
				<i
					class="landing-block-node-card-icon fa fa-check g-mr-12 g-font-size-18 g-pa-4 g-rounded-50 g-bg g-color"
					style="--bg: #0075FF33; --color: #0075FF;"
				></i>
				<div class="g-line-height-1_1">
					<?= $text3 ?>
				</div>
			</div>
		</div>
		<div class="d-flex align-items-center">
			<div
				style="width: 161px; height: 156px; background: rgba(38, 211, 235, .12) url('https://cdn.bitrix24.site/bitrix/images/landing/vibe/collaboration/apps-qr-bg.png') center no-repeat; background-size: cover;"
				class="g-mr-20 d-flex align-items-center justify-content-center g-rounded-20"
			>
				<div
					style="width: 87px; height: 87px; background: url('https://cdn.bitrix24.site/bitrix/images/landing/vibe/collaboration/apps-qr.png'); background-size: cover;"
					class="d-flex align-items-center justify-content-center g-rounded-50"
				>
				</div>
			</div>
			<div class="d-flex flex-column">
				<a
					href="<?= $buttonLink ?>"
					class="landing-widget-desktop-button landing-widget-desktop-button-collaboration text-center"
				>
					<?= $desktopButtonText ?>
				</a>

				<a class="landing-widget-qr-button d-flex align-items-center justify-content-center g-pl-18 g-pr-18 g-rounded-10 g-font-size-16 g-font-weight-500 text-center g-cursor-pointer g-btn-type-solid g-color g-bg g-bg--hover"
				   style=" --bg: #3273F6; --bg-hover: #678EF1; --color: #ffffff; height: 38px; transition: background 0.3s, color 0.3s;"
				>
					<?= $mobileButtonText ?>
				</a>
			</div>
		</div>
	</div>
	<div class="col">
		<div
			style="background: linear-gradient(141.9deg, #EDF1F7 0%, #E0EEFF 100%);"
			class=" d-flex align-items-center justify-content-center g-rounded-20 g-pt-60 g-pb-60"
		>
			<img style="display: block; max-width: 242px; height: auto; object-fit: cover;" src="<?= $imgSrc ?>" alt="">
		</div>
	</div>
</div>

<script>
	BX.ready(function() {
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const options = {
				title: '<?= Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_QR_POPUP_TITLE') ?>',
				content: '<?= Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_QR_POPUP_TEXT_1') ?>'
					+ '<br><br>'
					+ '<?= Loc::getMessage('BLOCK_MP_WIDGET_APPS_COLLABORATION_QR_POPUP_TEXT_2') ?>',
			};
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
				new BX.Landing.Widget.AppsCollaboration(widgetElement, options);
			}
		}
	});
</script>
