<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));

$title = Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_TITLE');

$cardTitles = [
	1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_CARD_TITLE_1'),
	2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_CARD_TITLE_2'),
];
$cardTexts = [
	1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_CARD_TEXT_1'),
	2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_CARD_TEXT_2'),
];
$cardButtonTexts = [
	1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_CARD_BUTTON_TEXT_1'),
	2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_BOARDS_CARD_BUTTON_TEXT_2'),
];

$createBoardsLink = $arResult['CREATE_BOARDS_LINK'] ?? '#';
?>

<div class="landing-block-boards row no-gutters" id="<?= $id ?>">
	<div class="col">
		<div
			class="landing-block-node-title g-font-weight-600 g-font-size-35 g-line-height-1 g-color g-mb-30"
			style="--color: #fff;"
		>
			<?= $title ?>
		</div>

		<div class="d-flex justify-content-center" style="gap: 16px;">
			<div
				class="g-pt-30 g-pb-35 g-pr-40 g-pl-40 g-rounded-10 g-cursor-default"
				style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/vibe/boards/3-1.png); background-size: cover; flex: 1; display: flex;flex-direction: column;justify-content: space-between; min-height: 251px;"
			>
				<div
					class="d-flex g-mb-20 flex-column"
					style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 60px;"
				>
					<div
						class="g-font-size-20 g-font-weight-600 g-color g-line-height-1_1 g-mb-20"
						style="--color: #fff;"
					>
						<?= $cardTitles[1] ?>
					</div>
					<div
						class="g-font-size-20 g-font-weight-400 g-line-height-1_2 g-color g-mb-16"
						style="--color: #fff; max-width: 360px;"
					>
						<?= $cardTexts[1] ?>
					</div>
				</div>
				<div>
					<div>
						<a
							class="landing-block-node-card-btn button-1 align-items-center justify-content-center g-pl-18 g-pr-18 g-rounded-10 g-font-size-16 g-font-weight-500 text-center g-cursor-pointer g-color g-bg g-bg--hover"
							style="display: flex; min-height: 38px; width: fit-content; max-width: 250px; transition: background 0.3s, color 0.3s; --color: #ffffff; --bg: #479CFF; --bg-hover: #7CAEF9;"
						>
							<?= $cardButtonTexts[1] ?>
						</a>
					</div>
				</div>
			</div>
			<div
				class="g-pt-30 g-pb-35 g-pr-40 g-pl-40 g-rounded-10 g-cursor-default"
				style="background-image: url(https://cdn.bitrix24.site/bitrix/images/landing/vibe/boards/3-2.png); background-size: cover; flex: 1; display: flex;flex-direction: column;justify-content: space-between; min-height: 251px;"
			>
				<div
					class="d-flex g-mb-20 flex-column"
					style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 60px;"
				>
					<div
						class="g-font-size-20 g-font-weight-600 g-color g-line-height-1_1 g-mb-20"
						style="--color: #fff;"
					>
						<?= $cardTitles[2] ?>
					</div>
					<div
						class="g-font-size-20 g-font-weight-400 g-line-height-1_2 g-color g-mb-16"
						style="--color: #fff; max-width: 360px;"
					>
						<?= $cardTexts[2] ?>
					</div>
				</div>
				<div>
					<div>
						<a
							href="<?= $createBoardsLink ?>"
							target="_blank"
							class="landing-block-node-card-btn button-2 align-items-center justify-content-center g-pl-18 g-pr-18 g-rounded-10 g-font-size-16 g-font-weight-500 text-center g-cursor-pointer g-color g-bg g-bg--hover"
							style="display: flex; min-height: 38px; width: fit-content; max-width: 250px; transition: background 0.3s, color 0.3s; --color: #ffffff; --bg: #479CFF; --bg-hover: #7CAEF9;"
						>
							<?= $cardButtonTexts[2] ?>
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
				};
				new BX.Landing.Widget.VibeBoards(widgetElement, options);
			}
		}
	});
</script>
