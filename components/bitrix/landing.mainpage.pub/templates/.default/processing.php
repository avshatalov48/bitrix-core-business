<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */
/** @var array $arParams */

use Bitrix\Landing\Manager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

Extension::load(['ui.lottie']);

Manager::setPageView(
	'BodyClass',
	'no-all-paddings landing-tile no-background'
);
?>
<div class="landing-vibe-processing" id="landing-vibe-processing">
	<div class="landing-vibe-processing-wrapper">
		<div class="landing-vibe-processing-content">
			<div class="landing-vibe-processing-animation" id="landing-vibe-processing-animation"></div>
			<div class="landing-vibe-processing-text">
				<div class="landing-vibe-processing-title"><?= Loc::getMessage('LANDING_TPL_MAINPAGE_PROCESSING_TITLE'); ?></div>
				<div class="landing-vibe-processing-title-sub"><?= Loc::getMessage('LANDING_TPL_MAINPAGE_PROCESSING_TITLE_SUB'); ?></div>
			</div>
		</div>
	</div>
</div>

<script>
	BX.Landing.Pub.renderLottieAnimation(BX('landing-vibe-processing-animation'));

	BX.PULL.subscribe({
		type: 'server',
		moduleId: 'landing',
		callback: (eventData) => {
			if (eventData.command === 'Vibe:onCreate')
			{
				document.location.reload();
			}
		},
	});
</script>
