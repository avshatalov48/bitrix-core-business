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

$region = $arResult['REGION'];
$zone = $arResult['ZONE'];
$isTrialActive = $arResult['IS_TRIAL_ACTIVE'] ?? false;
$isTariffFree = $arResult['IS_TARIFF_FREE'] ?? false;
$isTrialable = $arResult['IS_TRIALABLE'] ?? false;

$textButtonTrial = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_BUTTON_TRIAL');
$textButtonTalk = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_BUTTON_TALK');
$textButtonPrice = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_BUTTON_PRICE');
$textCustomerSuccess = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_TEXT_CS');
$textTrialNotActive = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_TEXT_2');
$textTrialActive = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_TEXT');
$titleTariffNotFree = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_TITLE_TARIFF_NOT_FREE');
$textTariffNotFree = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_TEXT_TARIFF_NOT_FREE');
$titleTrialActive = Loc::getMessage('BLOCK_MP_WIDGET_ENT_COMP_SCHEDULE_WEST_TITLE');

$hrefButtonTalk = '';
if (!in_array($region, ['ru', 'kz', 'by', 'uz']))
{
	$hrefButtonTalk = 'https://global.bitrix24.com/';
	if (in_array($zone, ['en', 'eu', 'uk']))
	{
		$hrefButtonTalk .= '~TePdd';
	}
	if ($zone === 'br')
	{
		$hrefButtonTalk .= '~ercrn';
	}
}
?>

<div
	style="background: image-set(url('https://cdn.bitrix24.site/bitrix/images/landing/vibe/ent-west/demo.png') 1x, url('https://cdn.bitrix24.site/bitrix/images/landing/vibe/ent-west/demo-2x.png') 2x) no-repeat; background-size: contain;"
	class="landing-widget-ent-v2 g-height-300"
	id="<?= $id ?>"
>
	<div class="g-pl-25 g-pr-25 g-pt-55 g-pb-55">
		<div style="display: flex; flex-direction: column; justify-content: center; min-height: 170px;">
			<?php if (false): ?>
				<a href="<?= $hrefButtonTalk ?>" target="_blank">
					<button
						style="--color: var(--theme-color-strict-inverse); --bg: var(--primary); --color-hover: var(--theme-color-strict-inverse); --bg-hover: var(--primary-lighten-1);"
						class="g-btn-primary g-mb-13 g-height-45 g-px-25 g-py-0 g-font-weight-500 g-font-size-20 g-rounded-25 btn g-brd-none g-color g-bg g-color--hover g-bg--hover g-cursor-pointer">
						<?= $textButtonTalk ?>
					</button>
				</a>
				<div class="landing-block-node-text g-color-main g-font-size-16 g-max-width-700">
					<?= $textCustomerSuccess ?>
				</div>
			<?php else: ?>
				<?php if (!$isTrialActive): ?>
					<?php if ($isTariffFree): ?>
						<?php if ($isTrialable): ?>
							<div>
								<button
									style="--color: var(--theme-color-strict-inverse); --bg: var(--primary); --color-hover: var(--theme-color-strict-inverse); --bg-hover: var(--primary-lighten-1);"
									id="trialButton"
									class="g-btn-primary g-mb-13 g-height-45 g-px-25 g-py-0 g-font-weight-500 g-font-size-20 g-rounded-25 btn g-brd-none g-color g-bg g-color--hover g-bg--hover g-cursor-pointer">
									<?= $textButtonTrial ?>
								</button>
							</div>
							<div class="landing-block-node-text g-color-main g-font-size-16 g-max-width-700">
								<?= $textTrialNotActive ?>
							</div>
						<?php endif; ?>
						<?php if (!$isTrialable): ?>
							<div class="landing-block-node-content g-mb-13">
								<div class="landing-block-node-title g-color-main g-font-size-25 g-font-weight-600 g-mb-10">
									<?= $titleTariffNotFree ?>
								</div>
								<div class="landing-block-node-text g-color-main g-font-size-16 g-max-width-700">
									<?= $textTariffNotFree ?>
								</div>
							</div>
							<a href="/settings/license_all.php" target="_blank">
								<button
									style="--color: var(--theme-color-strict-inverse); --bg: var(--primary); --color-hover: var(--theme-color-strict-inverse); --bg-hover: var(--primary-lighten-1);"
									class="g-btn-primary g-height-45 g-px-25 g-py-0 g-font-weight-500 g-font-size-20 g-rounded-25 btn g-brd-none g-color g-bg g-color--hover g-bg--hover g-cursor-pointer landing-widget-ent-west-button">
									<?= $textButtonPrice ?>
								</button>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<?php if (!$isTariffFree): ?>
						<div class="landing-block-node-content g-mb-13">
							<div class="landing-block-node-title g-color-main g-font-size-25 g-font-weight-600 g-mb-10">
								<?= $titleTariffNotFree ?>
							</div>
							<div class="landing-block-node-text g-color-main g-font-size-16 g-max-width-700">
								<?= $textTariffNotFree ?>
							</div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ($isTrialActive): ?>
					<div class="landing-block-node-content g-mb-13">
						<div class="landing-block-node-title g-color-main g-font-size-25 g-font-weight-600 g-mb-10">
							<?= $titleTrialActive ?>
						</div>
						<div class="landing-block-node-text g-color-main g-font-size-16 g-max-width-700">
							<?= $textTrialActive ?>
						</div>
					</div>
					<a href="/settings/license_all.php" target="_blank">
						<button
							style="--color: var(--theme-color-strict-inverse); --bg: var(--primary); --color-hover: var(--theme-color-strict-inverse); --bg-hover: var(--primary-lighten-1);"
							class="g-btn-primary g-height-45 g-px-25 g-py-0 g-font-weight-500 g-font-size-20 g-rounded-25 btn g-brd-none g-color g-bg g-color--hover g-bg--hover g-cursor-pointer landing-widget-ent-west-button">
							<?= $textButtonPrice ?>
						</button>
					</a>
				<?php endif; ?>
			<?php endif; ?>
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
				new BX.Landing.Widget.EnterpriceWestV2(widgetElement);
			}
		}
	});
</script>
