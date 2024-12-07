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
$isShowEmptyState = $arResult['SHOW_EMPTY_STATE'];
$emptyStateText = Loc::getMessage('BLOCK_MP_WIDGET_NEW_EMPLOYEES_EMPTY_STATE_TEXT');
$sidebarUsers = array_slice($arResult['USERS'], 0, 5);
?>

<div
	class="landing-widget-new-employees"
	id="<?= $id ?>"
>
	<div class="landing-widget-view-main">
		<div class="landing-widget-new-employees-title">
			<?= \htmlspecialcharsbx($arResult['TITLE']) ?>
		</div>
		<?php
		if ($isShowEmptyState): ?>
			<div class="landing-widget-new-employees-empty-state">
				<div class="landing-widget-new-employees-empty-state-icon"></div>
				<div class="landing-widget-new-employees-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php
		else: ?>
			<div class="landing-widget-new-employees-content">
				<?php
				foreach ($arResult['USERS'] as $user)
				{
					$name = \htmlspecialcharsbx($user['NAME']) ?? '';
					$lastName = \htmlspecialcharsbx($user['LAST_NAME']) ?? '';
					$fullName = $name . ' ' . $lastName;
					$position = \htmlspecialcharsbx($user['WORK_POSITION']);
					$date = \htmlspecialcharsbx($user['DATE_REGISTER']);
					$img = $user['PERSONAL_PHOTO_PATH'] ?? null;
					$userLinkHrefAttr = '';
					if ($user['ID'] > 0)
					{
						$userLink = '/company/personal/user/' . $user['ID']. '/';
						$userLinkHrefAttr = 'href="' . $userLink . '"';
					}
					echo '<div class="landing-widget-new-employees-content-item">';
					if ($img !== null)
					{
						?>
						<a
							<?= $userLinkHrefAttr ?>
							class="landing-widget-new-employees-content-item-img"
							style="background-image: url('<?= $img ?>');"
						>
						</a>
						<?php
					}
					else
					{
						echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-new-employees-content-item-img-default"></a>';
					}
					echo '<div class="landing-widget-new-employees-content-item-text-box">';
					echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-new-employees-content-item-text-name">' . $fullName . '</a>';
					echo '<div class="landing-widget-new-employees-content-item-text-work-position">'
						. $position
						. '</div>';
					echo '<div class="landing-widget-new-employees-content-item-text-date">' . $date . '</div>';
					echo '</div>';
					echo '</div>';
				}
				?>
			</div>
		<?php
		endif; ?>
	</div>

	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-view-sidebar-content">
			<div class="landing-widget-new-employees-title">
				<?= \htmlspecialcharsbx($arResult['TITLE']) ?>
			</div>
			<?php
			if ($isShowEmptyState): ?>
				<div class="landing-widget-new-employees-empty-state">
					<div class="landing-widget-new-employees-empty-state-icon"></div>
					<div class="landing-widget-new-employees-empty-state-text">
						<?= $emptyStateText ?>
					</div>
				</div>
			<?php
			else: ?>
				<div class="landing-widget-new-employees-content">
					<?php
					foreach ($sidebarUsers as $user)
					{
						$name = \htmlspecialcharsbx($user['NAME']) ?? '';
						$lastName = \htmlspecialcharsbx($user['LAST_NAME']) ?? '';
						$fullName = $name . ' ' . $lastName;
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$date = \htmlspecialcharsbx($user['DATE_REGISTER']);
						$img = $user['PERSONAL_PHOTO_PATH'] ?? null;
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID']. '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
						echo '<div class="landing-widget-new-employees-content-item">';
						if ($img !== null)
						{
							?>
							<a
								<?= $userLinkHrefAttr ?>
								class="landing-widget-new-employees-content-item-img"
								style="background-image: url('<?= $img ?>');"
							>
							</a>
							<?php
						}
						else
						{
							echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-new-employees-content-item-img-default"></a>';
						}
						echo '<div class="landing-widget-new-employees-content-item-text-box">';
						echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-new-employees-content-item-text-name">' . $fullName . '</a>';
						echo '<div class="landing-widget-new-employees-content-item-text-work-position">'
							. $position
							. '</div>';
						echo '<div class="landing-widget-new-employees-content-item-text-date">' . $date . '</div>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div>
			<?php
			endif; ?>
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
				new BX.Landing.Widget.NewEmployees(widgetElement);
			}
		}
	});
</script>
