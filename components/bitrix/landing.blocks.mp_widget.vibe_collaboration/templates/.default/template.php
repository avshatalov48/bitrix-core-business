<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;

$extensions = ['ui.icon-set.outline'];
$hintNeeded = false;
if (!in_array(\CBitrix24::getPortalZone(), ['ru', 'kz', 'by', 'uz'], true))
{
	$extensions[] = 'ui.hint';
	$hintNeeded = true;
}

Extension::load($extensions);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));

$title = Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_TITLE');
$subtitle = Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_SUBTITLE');
$text = Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_TEXT');
$items = [
	1 => [
		1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_1_1'),
		2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_1_2_1'),
		3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_1_2_2'),
		4 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_1_2_3'),
	],
	2 => [
		1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_2_1'),
		2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_2_2'),
		3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_2_3'),
	],
	3 => [
		1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_3_1'),
		2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_3_2'),
		3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_3_3'),
	],
	4 => [
		1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_4_1'),
		2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_4_2'),
		3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_4_3'),
	],
];
if ($hintNeeded === true)
{
	$hints = [
		1 => [
			1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_1_1'),
			2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_1_2_1'),
			3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_1_2_2'),
			4 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_1_2_3'),
		],
		2 => [
			1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_2_1'),
			2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_2_2'),
			3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_2_3'),
		],
		3 => [
			1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_3_1'),
			2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_3_2'),
			3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_3_3'),
		],
		4 => [
			1 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_4_1'),
			2 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_4_2'),
			3 => Loc::getMessage('BLOCK_MP_WIDGET_VIBE_COLLABORATION_ITEM_HINT_4_3'),
		],
	];
}

$zone = $arResult['ZONE'];
if (in_array($zone, ['ru', 'kz', 'by', 'uz']))
{
	$linksZone = 'cis';
}
else
{
	$linksZone = 'west';
}
$linkTarget = [
	'cis' => '_self',
	'west' => '_blank',
];
$links = [
	'cis' => [
		1 => [
			1 => ['href' => 'help:#slider=info_vibe_work_messenger'],
			2 => ['href' => 'help:#slider=info_vibe_work_chats'],
			3 => ['href' => 'help:#slider=info_vibe_work_videocalls'],
			4 => ['href' => 'help:#slider=info_vibe_work_channels'],
		],
		2 => [
			1 => ['href' => 'help:#slider=info_vibe_work_tasks_and_projects'],
			2 => ['href' => 'help:#slider=info_vibe_work_calendar'],
			3 => ['href' => 'help:#slider=info_vibe_work_collab'],
		],
		3 => [
			1 => ['href' => 'help:#slider=info_vibe_work_files'],
			2 => ['href' => 'help:#slider=info_vibe_work_docsonline'],
			3 => ['href' => 'help:#slider=info_vibe_work_boards'],
		],
		4 => [
			1 => ['href' => 'help:#slider=info_vibe_work_feed'],
			2 => ['href' => 'help:#slider=info_vibe_work_groups'],
			3 => ['href' => 'help:#slider=info_vibe_work_mail'],
		],
	],
	'west' => [
		1 => [
			1 => ['href' => '/online/'],
			2 => ['href' => '/online/'],
			3 => ['href' => '/online/'],
			4 => ['href' => '/online/?IM_CHANNEL'],
		],
		2 => [
			1 => ['href' => '/company/personal/user/0/tasks/'],
			2 => ['href' => '/company/personal/user/0/calendar/'],
			3 => ['href' => '/online/?IM_COLLAB'],
		],
		3 => [
			1 => ['href' => '/company/personal/user/0/disk/path/'],
			2 => ['href' => '/company/personal/user/0/disk/documents/'],
			3 => ['href' => '/company/personal/user/0/disk/boards/'],
		],
		4 => [
			1 => ['href' => '/stream/'],
			2 => ['href' => '/workgroups/'],
			3 => ['href' => '/mail/'],
		],
	],
];

$cardConfig = [
	1 => [
		['icon' => '--o-chats'],
		['icon' => '--o-message', 'wrap' => 'first'],
		['icon' => '--o-record-video', 'wrap' => 'middle'],
		['icon' => '--o-speaker', 'wrap' => 'last'],
	],
	2 => [
		['icon' => '--o-complete-task-list'],
		['icon' => '--o-calendar-with-slots'],
		['icon' => '--o-collab'],
	],
	3 => [
		['icon' => '--o-storage'],
		['icon' => '--o-file'],
		['icon' => '--o-board'],
	],
	4 => [
		['icon' => '--browser'],
		['icon' => '--o-three-persons'],
		['icon' => '--o-mail'],
	],
];

?>

<div class="landing-block-collaboration-sections row no-gutters" id="<?= $id ?>">
	<div class="col">
		<div class="d-flex justify-content-between g-mb-40 flex-column">
			<div class="landing-block-node-title g-font-weight-600 g-font-size-36 g-mb-15 g-line-height-1_1 g-mr-10 g-width-50x">
				<?= $title ?>
			</div>
			<div class="landing-block-node-subtitle g-font-size-20 g-line-height-1_1 g-width-50x">
				<?= htmlspecialcharsbx($subtitle) ?>
			</div>
		</div>

		<div class="landing-block-node-card-container d-grid justify-content-center g-mb-15">
			<?php foreach ($items as $colIdx => $colItems): ?>
				<div class="landing-block-node-card-column d-grid">
					<?php
					$wrapStarted = false;
					foreach ($colItems as $rowIdx => $item):
						$itermConfig = $cardConfig[$colIdx][$rowIdx - 1];
						$link = $links[$linksZone][$colIdx][$rowIdx]['href'] ?? '';
						$target = $linkTarget[$linksZone];
						$icon = $itermConfig['icon'];
						$wrap = $itermConfig['wrap'] ?? '';
						$itemHeight = 60;
						if ($wrap === '')
						{
							$itemHeight = 83;
						}
						if ($wrap === 'first' && !$wrapStarted)
						{
							echo '<div class="landing-block-node-card-icon-section g-ml-10 g-mr-10 g-rounded-10">';
							$wrapStarted = true;
						}
						$iconSize = $itemHeight === 83 ? 54 : 40;
						$hintText = $hints[$colIdx][$rowIdx] ?? '';
						?>
						<a
							class="landing-block-node-card-link g-flex-centered justify-content-start g-color g-px-12 g-py-12 g-font-size-16 g-font-weight-600 text-decoration-none g-transition-0_3 <?= htmlspecialcharsbx($wrap) ?>"
							href="<?= htmlspecialcharsbx($link) ?>"
							target="<?= htmlspecialcharsbx($target) ?>"
							style="height: <?= $itemHeight ?>px;"
							<?php if ($hintNeeded === true): ?>
								data-hint="<?= htmlspecialcharsbx($hintText) ?>"
								data-hint-no-icon
							<?php endif; ?>
						>
							<div
								class="landing-block-node-card-icon-container g-rounded-50x g-mr-20"
								style="min-width: <?= $iconSize ?>px; min-height: <?= $iconSize ?>px;"
							>
								<div
									class="landing-block-node-card-icon ui-icon-set <?= htmlspecialcharsbx($icon) ?>"
								></div>
							</div>
							<div class="landing-block-node-card-text">
								<?= $item ?>
							</div>
						</a>
						<?php
						if ($wrap === 'last' && $wrapStarted)
						{
							echo '</div>';
						}
					endforeach;
					?>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="landing-block-node-text g-color g-font-size-16 g-line-height-1_1">
			<?= htmlspecialcharsbx($text) ?>
		</div>
	</div>
</div>

<script>
	BX.ready(function() {
		BX.UI.Hint.init(BX('landing-block-node-card-container'));
	})

	BX.ready(function() {
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
				new BX.Landing.Widget.VibeCollaboration(widgetElement);
			}
		}
	});
</script>