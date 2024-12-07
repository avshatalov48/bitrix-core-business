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

Loc::loadMessages(__FILE__);

$mobileText = Loc::getMessage('BLOCK_MP_WIDGET_APPS_TEXT_1');
$mobileButtonText = Loc::getMessage('BLOCK_MP_WIDGET_APPS_BUTTON_TEXT');
$mobileId = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));

$desktopText = Loc::getMessage('BLOCK_MP_WIDGET_APP_DESKTOP_TEXT_1', [
	'#OS_NAME#' => $arResult['OS_NAME'],
]);
$desktopButtonText = Loc::getMessage('BLOCK_MP_WIDGET_APP_DESKTOP_BUTTON_TEXT');
$buttonLink = $arResult['DESKTOP_APP_LINK'];
$os = $arResult['OS'];
$osIconClass = '';
if ($os === 'MAC')
{
	$osIconClass = '--apple-and-ios';
}
if ($os === 'WIN')
{
	$osIconClass = '--windows';
}
if ($os === 'LIN')
{
	$osIconClass = '--linux';
}
$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
?>
<div class="landing-widget-apps" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="landing-widget-content">
			<div class="landing-widget-app-mobile" id="<?= $mobileId ?>">
				<div class="landing-widget-app-mobile-title">
					<?= \htmlspecialcharsbx($arResult['TITLE_MOBILE']) ?>
				</div>
				<div class="landing-widget-app-mobile-content">
					<?php
					echo '<div class="landing-widget-app-mobile-main">';
					echo '<div class="landing-widget-app-mobile-text-box">';
					echo '<div class="landing-widget-app-mobile-text">' . $mobileText . '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-mobile-inner">';
					echo '<div class="landing-widget-app-mobile-qr-wrap">';
					echo '<div class="landing-widget-app-mobile-qr-box">';
					echo '<div class="ui-icon-set --qr-code-1 landing-widget-app-mobile-qr-icon">' . '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-mobile-qr-control-box">';
					echo '<div class="landing-widget-qr-button">' . $mobileButtonText . '</div>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-mobile-logo">' . '</div>';
					?>
				</div>
			</div>
			<div class="landing-widget-app-desktop">
				<div class="landing-widget-app-desktop-title">
					<?= \htmlspecialcharsbx($arResult['TITLE_DESKTOP']) ?>
				</div>
				<div class="landing-widget-app-desktop-content">
					<?php
					echo '<div class="landing-widget-app-desktop-main">';
					echo '<div class="landing-widget-app-desktop-text-box">';
					echo '<div class="landing-widget-app-desktop-text">' . $desktopText . '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-desktop-inner">';
					echo '<div class="landing-widget-app-desktop-icon-wrap">';
					echo '<div class="landing-widget-app-desktop-icon-box">';
					?>
					<div class="ui-icon-set <?= $osIconClass ?> landing-widget-app-desktop-icon"></div>
					<?php
					echo '</div>';
					echo '</div>';
					?>
					<a
						href="<?= $buttonLink ?>"
						class="landing-widget-desktop-button"
					>
						<?= $desktopButtonText ?>
					</a>
					<?php
					echo '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-desktop-logo">' . '</div>';
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-content">
			<div class="landing-widget-app-mobile" id="<?= $mobileId ?>">
				<div class="landing-widget-app-mobile-title">
					<?= \htmlspecialcharsbx($arResult['TITLE_MOBILE']) ?>
				</div>
				<div class="landing-widget-app-mobile-content">
					<?php
					echo '<div class="landing-widget-app-mobile-main">';
					echo '<div class="landing-widget-app-mobile-text-box">';
					echo '<div class="landing-widget-app-mobile-text">' . $mobileText . '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-mobile-inner">';
					echo '<div class="landing-widget-app-mobile-qr-wrap">';
					echo '<div class="landing-widget-app-mobile-qr-box">';
					echo '<div class="ui-icon-set --qr-code-1 landing-widget-app-mobile-qr-icon">' . '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-mobile-qr-control-box">';
					echo '<div class="landing-widget-qr-button">' . $mobileButtonText . '</div>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					?>
				</div>
			</div>
			<div class="landing-widget-app-desktop">
				<div class="landing-widget-app-desktop-title">
					<?= \htmlspecialcharsbx($arResult['TITLE_DESKTOP']) ?>
				</div>
				<div class="landing-widget-app-desktop-content">
					<?php
					echo '<div class="landing-widget-app-desktop-main">';
					echo '<div class="landing-widget-app-desktop-text-box">';
					echo '<div class="landing-widget-app-desktop-text">' . $desktopText . '</div>';
					echo '</div>';
					echo '<div class="landing-widget-app-desktop-inner">';
					echo '<div class="landing-widget-app-desktop-icon-wrap">';
					echo '<div class="landing-widget-app-desktop-icon-box">';
					?>
					<div class="ui-icon-set <?= $osIconClass ?> landing-widget-app-desktop-icon"></div>
					<?php
					echo '</div>';
					echo '</div>';
					?>
					<a
						href="<?= $buttonLink ?>"
						class="landing-widget-desktop-button"
					>
						<?= $desktopButtonText ?>
					</a>
					<?php
					echo '</div>';
					echo '</div>';
					?>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	BX.ready(function() {
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const options = {
				title: '<?= Loc::getMessage('BLOCK_MP_WIDGET_APPS_QR_POPUP_TITLE') ?>',
				content: '<?= Loc::getMessage('BLOCK_MP_WIDGET_APPS_QR_POPUP_TEXT_1') ?>'
					+ '<br><br>'
					+ '<?= Loc::getMessage('BLOCK_MP_WIDGET_APPS_QR_POPUP_TEXT_2') ?>',
			};
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
				new BX.Landing.Widget.Apps(widgetElement, options);
			}
		}
	});
</script>
