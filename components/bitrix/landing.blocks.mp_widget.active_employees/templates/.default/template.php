<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

const USER_CIRCLE_PARAM_DEFAULT = ['cx' => 82, 'cy' => 82, 'r' => 64];
const USER_CIRCLES_PARAM = [
	USER_CIRCLE_PARAM_DEFAULT,
	['cx' => 91, 'cy' => 91, 'r' => 78],
	['cx' => 114, 'cy' => 114, 'r' => 80],
	USER_CIRCLE_PARAM_DEFAULT,
	['cx' => 92, 'cy' => 92, 'r' => 80],
	['cx' => 53, 'cy' => 53, 'r' => 42],
];

$activity = $arResult['GENERAL_ACTIVITY'] . '<span>' . '%' . '</span>';
$circleDashOffset = 'stroke-dashoffset: calc(754px - (754px * ' . $arResult['GENERAL_ACTIVITY'] . ') / 100);';
$circleDashOffsetSidebar = 'stroke-dashoffset: calc(189px - (189px * ' . $arResult['GENERAL_ACTIVITY'] . ') / 100);';
$title = \htmlspecialcharsbx($arResult['TITLE']);
$isShowEmptyState = $arResult['SHOW_EMPTY_STATE'];
$emptyStateText = Loc::getMessage('BLOCK_MP_WIDGET_ACTIVE_EMPLOYEES_EMPTY_STATE_TEXT');
$diagramText = Loc::getMessage('BLOCK_MP_WIDGET_ACTIVE_EMPLOYEES_DIAGRAM_TEXT');
$indexActivityText = Loc::getMessage('BLOCK_MP_WIDGET_ACTIVE_EMPLOYEES_INDEX_TEXT');
$users = $arResult['USERS'];
$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
?>

<div class="landing-widget-active-employees" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="landing-widget-active-employees-title">
			<?= $title ?>
		</div>
		<?php
		if ($isShowEmptyState): ?>
			<div class="landing-widget-active-employees-empty-state">
				<div class="landing-widget-active-employees-empty-state-icon"></div>
				<div class="landing-widget-active-employees-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php
		else: ?>
			<div class="landing-widget-active-employees-content">
				<div class="landing-widget-active-employees-content-diagram">
					<svg class="landing-widget-active-employees-content-diagram-circle">
						<circle class="circle-1" cx="145" cy="145" r="120"></circle>
						<circle class="circle-2" cx="145" cy="145" r="120" style="<?= $circleDashOffset ?>"></circle>
					</svg>
					<div class="landing-widget-active-employees-content-diagram-text-container">
						<div class="landing-widget-active-employees-content-diagram-activity">
							<?= $activity ?>
						</div>
						<div class="landing-widget-active-employees-content-diagram-text">
							<?= $diagramText ?>
						</div>
					</div>
				</div>
				<div class="landing-widget-active-employees-content-item-box">
					<?php
					$count = 0;
					foreach ($users as $user)
					{
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$cx = USER_CIRCLES_PARAM[$count]['cx'] ?? USER_CIRCLE_PARAM_DEFAULT['cx'];
						$cy = USER_CIRCLES_PARAM[$count]['cy'] ?? USER_CIRCLE_PARAM_DEFAULT['cy'];
						$r = USER_CIRCLES_PARAM[$count]['r'] ?? USER_CIRCLE_PARAM_DEFAULT['r'];
						$count++;
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID'] . '/';
							$userLinkHrefAttr = 'href="' . \htmlspecialcharsbx($userLink) . '"';
						}
						?>
						<div class="landing-widget-active-employees-content-item">
							<svg class="landing-widget-active-employees-content-item-circle-box">
								<circle class="landing-widget-active-employees-content-item-circle" cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>"></circle>
							</svg>
							<?php if ($img !== ''): ?>
							<a
								<?= $userLinkHrefAttr ?>
								class="landing-widget-active-employees-content-item-img"
								style="background-image: url('<?= $img ?? '' ?>');"
							>
							</a>
							<?php else: ?>
								<div class="landing-widget-active-employees-content-item-img-default"></div>
							<?php endif; ?>
							<div class="landing-widget-active-employees-content-item-text-box">
								<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-content-item-text-name"><?= $name ?></a>
								<div class="landing-widget-active-employees-content-item-text-work-position"><?= $position ?></div>
							</div>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		<?php
		endif; ?>
	</div>

	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-active-employees-title">
			<?= $title ?>
		</div>
		<?php
		if ($isShowEmptyState): ?>
			<div class="landing-widget-active-employees-empty-state">
				<div class="landing-widget-active-employees-empty-state-icon"></div>
				<div class="landing-widget-active-employees-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php
		else: ?>
		<div class="landing-widget-active-employees-content">
			<div class="landing-widget-active-employees-content-diagram-wrap">
				<div class="landing-widget-active-employees-content-diagram">
					<svg class="landing-widget-active-employees-content-diagram-circle">
						<circle class="circle-1" cx="40" cy="40" r="30"></circle>
						<circle class="circle-2" cx="40" cy="40" r="30" style="<?= $circleDashOffsetSidebar ?>"></circle>
					</svg>
					<div class="landing-widget-active-employees-content-diagram-text-container">
						<div class="landing-widget-active-employees-content-diagram-activity">
							<?= $activity ?>
						</div>
					</div>
				</div>
				<div class="landing-widget-active-employees-content-diagram-text">
					<?= $diagramText ?>
				</div>
			</div>
			<div class="landing-widget-active-employees-content-item-box">
				<?php
				$count = 0;
				foreach ($users as $user)
				{
				$name = \htmlspecialcharsbx($user['NAME']);
				$position = \htmlspecialcharsbx($user['WORK_POSITION']);
				$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
				$userActivity = $user['ACTIVITY'] ?? '0';
				$userLinkHrefAttr = '';
				if ($user['ID'] > 0)
				{
					$userLink = '/company/personal/user/' . $user['ID'] . '/';
					$userLinkHrefAttr = 'href="' . \htmlspecialcharsbx($userLink) . '"';
				}
				?>
				<div class="landing-widget-active-employees-content-item">
					<div class="landing-widget-active-employees-content-item-inner">
						<svg class="landing-widget-active-employees-content-item-circle-box">
							<circle class="landing-widget-active-employees-content-item-circle" cx="27" cy="27" r="24"></circle>
						</svg>
						<?php if ($img !== ''): ?>
						<a
							<?= $userLinkHrefAttr ?>
							class="landing-widget-active-employees-content-item-img"
							style="background-image: url('<?= $img ?? '' ?>');"
						>
						</a>
						<?php else: ?>
						<div class="landing-widget-active-employees-content-item-img-default">
						</div>
							<?php endif; ?>
					</div>
					<div class="landing-widget-active-employees-content-item-text-box">
						<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-content-item-text-name"><?= $name ?></a>
						<div class="landing-widget-active-employees-content-item-text-work-position"><?= $position ?></div>
						<div class="landing-widget-active-employees-content-item-text-index">
							<div class="landing-widget-active-employees-content-item-text-index-name"><?= $indexActivityText ?></div>
							<div class="landing-widget-active-employees-content-item-text-index-value"><?= $userActivity ?></div>
						</div>
					</div>
					</div>
					<?php
					}
					?>
				</div>
			</div>
			<?php
			endif; ?>
	</div>
</div>

<script>
	BX.ready(function()
	{
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const widgetElement = document.querySelector('#<?= $id ?>');
			if (widgetElement)
			{
				new BX.Landing.Widget.About(widgetElement);
			}
		}
	});
</script>
