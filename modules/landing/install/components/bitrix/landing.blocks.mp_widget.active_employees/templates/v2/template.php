<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */

/** @var array $arResult */

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

const USER_CIRCLE_PARAM_DEFAULT_V2 = ['cx' => 82, 'cy' => 82, 'r' => 76];
const USER_CIRCLE_PARAM_SIDEBAR_DEFAULT_V2 = ['cx' => 30, 'cy' => 30, 'r' => 28];

$users = array_slice($arResult['USERS'], 0, 4);
$activity = $arResult['GENERAL_ACTIVITY'] . '<span>' . '%' . '</span>';
$circleDashOffset = 'stroke-dashoffset: calc(754px - (754px * ' . $arResult['GENERAL_ACTIVITY'] . ') / 100);';
$userActivities = [
	$users[0]['ACTIVITY'] ?? 0,
	$users[1]['ACTIVITY'] ?? 0,
	$users[2]['ACTIVITY'] ?? 0,
	$users[3]['ACTIVITY'] ?? 0,
];
$maxNumberUserActivity = max(max($userActivities), 1);
$percentagesUserActivity = array_map(static function($number) use ($maxNumberUserActivity) {
	return round(($number / $maxNumberUserActivity) * 100);
}, $userActivities);
$circleDashOffsetUser1 = 'stroke-dashoffset: calc(477px - (477px * ' . $percentagesUserActivity[0] . ') / 100);';
$circleDashOffsetUser2 = 'stroke-dashoffset: calc(477px - (477px * ' . $percentagesUserActivity[1] . ') / 100);';
$circleDashOffsetUser3 = 'stroke-dashoffset: calc(477px - (477px * ' . $percentagesUserActivity[2] . ') / 100);';
$circleDashOffsetUser4 = 'stroke-dashoffset: calc(477px - (477px * ' . $percentagesUserActivity[3] . ') / 100);';
$circleDashOffsetSidebar = 'stroke-dashoffset: calc(502px - (502px * ' . $arResult['GENERAL_ACTIVITY'] . ') / 100);';
$title = \htmlspecialcharsbx($arResult['TITLE']);
$isShowEmptyState = $arResult['SHOW_EMPTY_STATE'];
$emptyStateText = Loc::getMessage('BLOCK_MP_WIDGET_ACTIVE_EMPLOYEES_V2_EMPTY_STATE_TEXT');
$diagramText = Loc::getMessage('BLOCK_MP_WIDGET_ACTIVE_EMPLOYEES_V2_DIAGRAM_TEXT');
$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
?>

<div class="landing-widget-active-employees-v2" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="landing-widget-active-employees-v2-title">
			<?= $title ?>
		</div>
		<?php
		if ($isShowEmptyState): ?>
			<div class="landing-widget-active-employees-v2-empty-state">
				<div class="landing-widget-active-employees-v2-empty-state-icon"></div>
				<div class="landing-widget-active-employees-v2-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php
		else: ?>
			<div class="landing-widget-active-employees-v2-content">
				<div class="landing-widget-active-employees-v2-content-items-col-1">
					<?php
					$user = $users[0] ?? null;
					if ($user)
					{
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$userActivity = $user['ACTIVITY'] ?? '0';
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID'] . '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
					}
					?>
					<?php if ($user): ?>
					<div class="landing-widget-active-employees-v2-content-item">
						<div class="landing-widget-active-employees-v2-content-item-inner">
							<svg class="landing-widget-active-employees-v2-content-item-circle-box">
								<circle class="landing-widget-active-employees-v2-content-item-circle" cx="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cx'] ?>" cy="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cy'] ?>" r="<?= USER_CIRCLE_PARAM_DEFAULT_V2['r'] ?>"  style="<?= $circleDashOffsetUser1 ?>"></circle>
							</svg>
							<?php if ($img !== ''): ?>
								<a
									<?= $userLinkHrefAttr ?>
									class="landing-widget-active-employees-v2-content-item-img"
									style="background-image: url('<?= $img ?? '' ?>');"
								>
								</a>
							<?php else: ?>
								<div class="landing-widget-active-employees-v2-content-item-img-default">
								</div>
							<?php endif; ?>
						</div>
						<div class="landing-widget-active-employees-v2-content-item-text-box">
							<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-v2-content-item-text-name"><?= $name ?></a>
							<div class="landing-widget-active-employees-v2-content-item-text-work-position"><?= $position ?></div>
						</div>
					</div>
					<?php endif; ?>
					<?php
					$user = $users[2] ?? null;
					if ($user)
					{
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$userActivity = $user['ACTIVITY'] ?? '0';
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID'] . '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
					}
					?>
					<?php if ($user): ?>
					<div class="landing-widget-active-employees-v2-content-item">
						<div class="landing-widget-active-employees-v2-content-item-inner">
							<svg class="landing-widget-active-employees-v2-content-item-circle-box">
								<circle class="landing-widget-active-employees-v2-content-item-circle" cx="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cx'] ?>" cy="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cy'] ?>" r="<?= USER_CIRCLE_PARAM_DEFAULT_V2['r'] ?>"   style="<?= $circleDashOffsetUser3 ?>"></circle>
							</svg>
							<?php if ($img !== ''): ?>
								<a
									<?= $userLinkHrefAttr ?>
									class="landing-widget-active-employees-v2-content-item-img"
									style="background-image: url('<?= $img ?? '' ?>');"
								>
								</a>
							<?php else: ?>
								<div class="landing-widget-active-employees-v2-content-item-img-default">
								</div>
							<?php endif; ?>
						</div>
						<div class="landing-widget-active-employees-v2-content-item-text-box">
							<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-v2-content-item-text-name"><?= $name ?></a>
							<div class="landing-widget-active-employees-v2-content-item-text-work-position"><?= $position ?></div>
						</div>
					</div>
					<?php endif; ?>
				</div>
				<div class="landing-widget-active-employees-v2-content-box">
					<div class="landing-widget-active-employees-v2-content-diagram">
						<svg class="landing-widget-active-employees-v2-content-diagram-circle">
							<circle class="circle-1" cx="145" cy="145" r="120"></circle>
							<circle class="circle-3" cx="145" cy="145" r="120"></circle>
							<circle class="circle-2" cx="145" cy="145" r="120" style="<?= $circleDashOffset ?>"></circle>
						</svg>
						<div class="landing-widget-active-employees-v2-content-diagram-text-container">
							<div class="landing-widget-active-employees-v2-content-diagram-activity">
								<?= $activity ?>
							</div>
						</div>
					</div>
					<div class="landing-widget-active-employees-v2-content-diagram-text">
						<?= $diagramText ?>
					</div>
				</div>
				<div class="landing-widget-active-employees-v2-content-items-col-2">
					<?php
					$user = $users[1] ?? null;
					if ($user)
					{
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$userActivity = $user['ACTIVITY'] ?? '0';
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID'] . '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
					}
					?>
					<?php if ($user): ?>
					<div class="landing-widget-active-employees-v2-content-item">
						<div class="landing-widget-active-employees-v2-content-item-inner">
							<svg class="landing-widget-active-employees-v2-content-item-circle-box">
								<circle class="landing-widget-active-employees-v2-content-item-circle" cx="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cx'] ?>" cy="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cy'] ?>" r="<?= USER_CIRCLE_PARAM_DEFAULT_V2['r'] ?>"  style="<?= $circleDashOffsetUser2 ?>"></circle>
							</svg>
							<?php if ($img !== ''): ?>
								<a
									<?= $userLinkHrefAttr ?>
									class="landing-widget-active-employees-v2-content-item-img"
									style="background-image: url('<?= $img ?? '' ?>');"
								>
								</a>
							<?php else: ?>
								<div class="landing-widget-active-employees-v2-content-item-img-default">
								</div>
							<?php endif; ?>
						</div>
						<div class="landing-widget-active-employees-v2-content-item-text-box">
							<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-v2-content-item-text-name"><?= $name ?></a>
							<div class="landing-widget-active-employees-v2-content-item-text-work-position"><?= $position ?></div>
						</div>
					</div>
					<?php endif; ?>
					<?php
					$user = $users[3] ?? null;
					if ($user)
					{
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$userActivity = $user['ACTIVITY'] ?? '0';
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID'] . '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
					}
					?>
					<?php if ($user): ?>
					<div class="landing-widget-active-employees-v2-content-item">
						<div class="landing-widget-active-employees-v2-content-item-inner">
							<svg class="landing-widget-active-employees-v2-content-item-circle-box">
								<circle class="landing-widget-active-employees-v2-content-item-circle" cx="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cx'] ?>" cy="<?= USER_CIRCLE_PARAM_DEFAULT_V2['cy'] ?>" r="<?= USER_CIRCLE_PARAM_DEFAULT_V2['r'] ?>" style="<?= $circleDashOffsetUser4 ?>"></circle>
							</svg>
							<?php if ($img !== ''): ?>
								<a
									<?= $userLinkHrefAttr ?>
									class="landing-widget-active-employees-v2-content-item-img"
									style="background-image: url('<?= $img ?? '' ?>');"
								>
								</a>
							<?php else: ?>
								<div class="landing-widget-active-employees-v2-content-item-img-default">
								</div>
							<?php endif; ?>
						</div>
						<div class="landing-widget-active-employees-v2-content-item-text-box">
							<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-v2-content-item-text-name"><?= $name ?></a>
							<div class="landing-widget-active-employees-v2-content-item-text-work-position"><?= $position ?></div>
						</div>
					</div>
					<?php endif; ?>
				</div>
			</div>
		<?php
		endif; ?>
	</div>

	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-active-employees-v2-title">
			<?= $title ?>
		</div>
		<div class="landing-widget-active-employees-v2-content-diagram-box">
			<div class="landing-widget-active-employees-v2-content-diagram">
				<svg class="landing-widget-active-employees-v2-content-diagram-circle">
					<circle class="circle-1" cx="120" cy="120" r="80"></circle>
					<circle class="circle-3" cx="120" cy="120" r="80"></circle>
					<circle class="circle-2" cx="120" cy="120" r="80" style="<?= $circleDashOffsetSidebar ?>"></circle>
				</svg>
				<div class="landing-widget-active-employees-v2-content-diagram-text-container">
					<div class="landing-widget-active-employees-v2-content-diagram-activity">
						<?= $activity ?>
					</div>
				</div>
			</div>
			<div class="landing-widget-active-employees-v2-content-diagram-text">
				<?= $diagramText ?>
			</div>
		</div>
		<div class="landing-widget-active-employees-v2-content">
			<div class="landing-widget-active-employees-v2-content-item-box">
				<?php
				$count = 0;
				foreach ($users as $user)
				{
					$name = \htmlspecialcharsbx($user['NAME']);
					$position = \htmlspecialcharsbx($user['WORK_POSITION']);
					$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
					$userLinkHrefAttr = '';
					$userActivity = $user['ACTIVITY'] ?? 0;
					$sidebarCircleDashOffsetUser = 'stroke-dashoffset: calc(188px - (188px * ' . $percentagesUserActivity[$count] . ') / 100);';
					$count++;
					if ($user['ID'] > 0)
					{
						$userLink = '/company/personal/user/' . $user['ID'] . '/';
						$userLinkHrefAttr = 'href="' . $userLink . '"';
					}
					?>
					<div class="landing-widget-active-employees-v2-content-item">
						<svg class="landing-widget-active-employees-v2-content-item-circle-box">
							<circle class="landing-widget-active-employees-v2-content-item-circle" cx="<?= USER_CIRCLE_PARAM_SIDEBAR_DEFAULT_V2['cx'] ?>" cy="<?= USER_CIRCLE_PARAM_SIDEBAR_DEFAULT_V2['cy'] ?>" r="<?= USER_CIRCLE_PARAM_SIDEBAR_DEFAULT_V2['r'] ?>" style="<?= $sidebarCircleDashOffsetUser ?>"></circle>
						</svg>
						<?php if ($img !== ''): ?>
							<a
								<?= $userLinkHrefAttr ?>
								class="landing-widget-active-employees-v2-content-item-img"
								style="background-image: url('<?= $img ?? '' ?>');"
							>
							</a>
						<?php else: ?>
							<div class="landing-widget-active-employees-v2-content-item-img-default"></div>
						<?php endif; ?>
						<div class="landing-widget-active-employees-v2-content-item-text-box">
							<a <?= $userLinkHrefAttr ?> class="landing-widget-active-employees-v2-content-item-text-name"><?= $name ?></a>
							<div class="landing-widget-active-employees-v2-content-item-text-work-position"><?= $position ?></div>
						</div>
					</div>
					<?php
				}
				?>
			</div>
		</div>
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
				new BX.Landing.Widget.ActiveEmployeesV2(widgetElement);
			}
		}
	});
</script>
