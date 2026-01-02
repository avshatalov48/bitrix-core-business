<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));

$title = Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_TITLE');

$cardTitles = [
	1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_TITLE_1'),
	2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_TITLE_2'),
	3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_TITLE_3'),
];
$cardTexts = [
	1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_TEXT_1'),
	2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_TEXT_2'),
	3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_TEXT_3'),
];
$cardButtonTexts = [
	1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_BUTTON_TEXT_1'),
	2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_BUTTON_TEXT_2'),
	3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_V2_CARD_BUTTON_TEXT_3'),
];

?>

<div class="landing-block-collaboration-steps row no-gutters" id="<?= $id ?>">
	<div class="col">
		<div
			class="landing-block-node-title g-font-weight-600 g-font-size-35 g-line-height-1 g-color g-mb-60"
			style="--color: #fff;"
		>
			<?= $title ?>
		</div>

		<div class="d-flex justify-content-center" style="gap: 16px;">
			<div
				class="g-pt-20 g-pr-20 g-pb-20 g-pl-20 g-rounded-10 g-cursor-default"
				style="background: rgba(52, 42, 126, .7); flex: 1; display: flex;flex-direction: column;justify-content: space-between;"
			>
				<div
					class="d-flex g-mb-60 g-font-weight-600"
					style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 60px;"
				>
					<div
						class="g-font-size-20 g-color g-line-height-1_1"
						style="--color: #fff;"
					>
						<?= $cardTitles[1] ?>
					</div>
					<div
						class="g-font-size-25 g-color g-line-height-1_1"
						style="--color: #fff;"
					>
						01
					</div>
				</div>
				<div>
					<div
						class="g-mb-16 g-font-size-16 g-color g-line-height-1_2"
						style="--color: #fff;"
					>
						<?= $cardTexts[1] ?>
					</div>
					<div>
						<a
							class="landing-block-node-card-btn button-1 align-items-center justify-content-center g-pl-18 g-pr-18 g-rounded-10 g-font-size-16 g-font-weight-500 text-center g-cursor-pointer g-color g-bg g-bg--hover"
							style="display: flex; height: 38px; transition: background 0.3s, color 0.3s; --color: #ffffff; --bg: #479CFF; --bg-hover: #7CAEF9;"
						>
							<?= $cardButtonTexts[1] ?>
						</a>
					</div>
				</div>
			</div>
			<div
				class="g-pt-20 g-pr-20 g-pb-20 g-pl-20 g-rounded-10 g-cursor-default"
				style="background: rgba(52, 42, 126, .7); flex: 1; display: flex;flex-direction: column;justify-content: space-between;"
			>
				<div
					class="d-flex g-mb-60 g-font-weight-600"
					style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 60px;"
				>
					<div
						class="g-font-size-20 g-color g-line-height-1_1"
						style="--color: #fff;"
					>
						<?= $cardTitles[2] ?>
					</div>
					<div
						class="g-font-size-25 g-color g-line-height-1_1"
						style="--color: #fff;"
					>
						02
					</div>
				</div>
				<div>
					<div
						class="g-mb-16 g-font-size-16 g-color g-line-height-1_2"
						style="--color: #fff;"
					>
						<?= $cardTexts[2] ?>
					</div>
					<div>
						<a
							class="landing-block-node-card-btn button-2 align-items-center justify-content-center g-pl-18 g-pr-18 g-rounded-10 g-font-size-16 g-font-weight-500 text-center g-cursor-pointer g-color g-bg g-bg--hover"
							style="display: flex; height: 38px; transition: background 0.3s, color 0.3s; --color: #ffffff; --bg: #479CFF; --bg-hover: #7CAEF9;"
						>
							<?= $cardButtonTexts[2] ?>
						</a>
					</div>
				</div>
			</div>
			<div
				class="g-pt-20 g-pr-20 g-pb-20 g-pl-20 g-rounded-10 g-cursor-default"
				style="background: rgba(52, 42, 126, .7); flex: 1; display: flex;flex-direction: column;justify-content: space-between;"
			>
				<div
					class="d-flex g-mb-60 g-font-weight-600"
					style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 60px;"
				>
					<div
						class="g-font-size-20 g-color g-line-height-1_1"
						style="--color: #fff;"
					>
						<?= $cardTitles[3] ?>
					</div>
					<div
						class="g-font-size-25 g-color g-line-height-1_1"
						style="--color: #fff;"
					>
						03
					</div>
				</div>
				<div>
					<div
						class="g-mb-16 g-font-size-16 g-color g-line-height-1_2"
						style="--color: #fff;"
					>
						<?= $cardTexts[3] ?>
					</div>
					<div>
						<a
							class="landing-block-node-card-btn button-3 align-items-center justify-content-center g-pl-18 g-pr-18 g-rounded-10 g-font-size-16 g-font-weight-500 text-center g-cursor-pointer g-color g-bg g-bg--hover"
							style="display: flex; height: 38px; transition: background 0.3s, color 0.3s; --color: #ffffff; --bg: #479CFF; --bg-hover: #7CAEF9;"
						>
							<?= $cardButtonTexts[3] ?>
						</a>
					</div>
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
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
				const options = {
					inviteDialogLink: <?= isset($arResult['INVITE_DIALOG_LINK']) ? json_encode($arResult['INVITE_DIALOG_LINK']) : 'null' ?>,
					generalChatId: <?= isset($arResult['GENERAL_CHAT_ID']) ? json_encode($arResult['GENERAL_CHAT_ID']) : 'null' ?>,
				};
				new BX.Landing.Widget.VibeCollaborationV2(widgetElement, options);
			}
		}
	});
</script>
