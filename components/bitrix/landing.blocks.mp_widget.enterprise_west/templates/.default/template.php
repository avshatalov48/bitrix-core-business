<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));

$isTrialActive = $arResult['IS_TRIAL_ACTIVE'] ?? false;
$isTrialable = $arResult['IS_TRIALABLE'] ?? false;

$trialButtonText = Loc::getMessage('BLOCK_MP_WIDGET_ENT_WEST_BUTTON_TRIAL');
$priceButtonText = Loc::getMessage('BLOCK_MP_WIDGET_ENT_WEST_BUTTON_PRICE');
$trialText = Loc::getMessage('BLOCK_MP_WIDGET_ENT_WEST_TEXT');
$priceText = Loc::getMessage('BLOCK_MP_WIDGET_ENT_WEST_TEXT_2');
$trialActivatedTitle = Loc::getMessage('BLOCK_MP_WIDGET_ENT_WEST_TITLE');
?>

<div class="landing-widget-ent" id="<?= $id ?>">
	<div class="g-py-30 g-rounded-10 landing-widget-view-main --enterprise-west">
		<?php if (!$isTrialActive && $isTrialable): ?>
			<button id="trialButton" class="landing-widget-ent-west-button g-btn-primary g-brd-none g-height-45 g-mb-12--sm g-font-weight-500 g-rounded-25 g-cursor-pointer">
				<?= $trialButtonText ?>
			</button>
			<div class="landing-widget-ent-west-text g-font-size-16 text-center g-width-700">
				<?= $trialText ?>
			</div>
		<?php endif; ?>

		<?php if ($isTrialActive || !$isTrialable): ?>
			<?php if ($isTrialActive): ?>
				<div class="g-font-size-25 g-font-weight-700 g-mb-12 landing-widget-ent-west-title">
					<?= $trialActivatedTitle ?>
				</div>
			<?php endif; ?>
			<div class="landing-widget-ent-west-text g-font-size-16 g-mb-12 text-center g-width-600">
				<?= $priceText ?>
			</div>
			<a href="/settings/license_all.php" target="_blank">
				<button class="g-btn-primary g-brd-none g-height-45 g-font-weight-500 g-rounded-25 g-cursor-pointer landing-widget-ent-west-button">
					<?= $priceButtonText ?>
				</button>
			</a>
		<?php endif; ?>
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
				new BX.Landing.Widget.EnterpriceWest(widgetElement);
			}
		}
	});
</script>
