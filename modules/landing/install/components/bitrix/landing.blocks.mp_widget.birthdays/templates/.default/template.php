<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$title = \htmlspecialcharsbx($arResult['TITLE']);
$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
$isShowEmptyState = $arResult['SHOW_EMPTY_STATE'];
$emptyStateText = Loc::getMessage('BLOCK_MP_WIDGET_BIRTHDAY_EMPTY_STATE_TEXT');
$sidebarUsers = array_slice($arResult['USERS'], 0, 4);
?>

<div class="landing-widget-birthdays" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="w-color-h landing-widget-birthdays-title">
			<?= $title ?>
		</div>
		<?php if ($isShowEmptyState): ?>
			<div class="landing-widget-birthdays-empty-state">
				<div class="landing-widget-birthdays-empty-state-icon"></div>
				<div class="landing-widget-birthdays-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php else: ?>
			<div class="landing-widget-birthdays-content-box">
				<div class="landing-widget-birthdays-content">
					<?php
					foreach ($arResult['USERS'] as $user)
					{
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$birthdayDate = \htmlspecialcharsbx($user['PERSONAL_BIRTHDAY']);
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID']. '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
						echo '<div class="landing-widget-birthdays-content-item">';
						echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-birthdays-content-item-img">';
						if ($img !== '')
						{
							?>
							<div style="background-image: url('<?= \htmlspecialcharsbx($img) ?>');" class="landing-widget-birthdays-content-item-img-inner"></div>
							<?php
						}
						else
						{
							echo '<div class="landing-widget-birthdays-content-item-img-default-inner"></div>';
						}
						echo '</a>';
						echo '<div class="landing-widget-birthdays-content-item-text-box">';
						echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-birthdays-content-item-text-name">' . $name .'</a>';
						echo '<div class="landing-widget-birthdays-content-item-text-work-position">' . $position .'</div>';
						echo '<div class="landing-widget-birthdays-content-item-text-date">' . $birthdayDate .'</div>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div>
			</div>
		<?php endif; ?>
	</div>
	<?php if ($isShowEmptyState): ?>
	<div class="landing-widget-view-sidebar landing-widget-view-sidebar-empty-state">
	<?php else: ?>
	<div class="landing-widget-view-sidebar">
	<?php endif; ?>
		<div class="w-color-h landing-widget-birthdays-title">
			<?= $title ?>
		</div>
		<?php if ($isShowEmptyState): ?>
			<div class="landing-widget-birthdays-empty-state">
				<div class="landing-widget-birthdays-empty-state-icon"></div>
				<div class="landing-widget-birthdays-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php else: ?>
			<div class="landing-widget-birthdays-content-box">
				<div class="landing-widget-birthdays-content">
					<?php
					foreach ($sidebarUsers as $user)
					{
						$img = $user['PERSONAL_PHOTO_PATH'] ?? '';
						$name = \htmlspecialcharsbx($user['NAME']);
						$position = \htmlspecialcharsbx($user['WORK_POSITION']);
						$birthdayDate = \htmlspecialcharsbx($user['PERSONAL_BIRTHDAY']);
						$userLinkHrefAttr = '';
						if ($user['ID'] > 0)
						{
							$userLink = '/company/personal/user/' . $user['ID']. '/';
							$userLinkHrefAttr = 'href="' . $userLink . '"';
						}
						echo '<div class="landing-widget-birthdays-content-item">';
						echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-birthdays-content-item-img">';
						if ($img !== '')
						{
							?>
							<div style="background-image: url('<?= \htmlspecialcharsbx($img) ?>');" class="landing-widget-birthdays-content-item-img-inner"></div>
							<?php
						}
						else
						{
							echo '<div class="landing-widget-birthdays-content-item-img-default-inner"></div>';
						}
						echo '</a>';
						echo '<div class="landing-widget-birthdays-content-item-text-box">';
						echo '<a ' . $userLinkHrefAttr . ' class="landing-widget-birthdays-content-item-text-name">' . $name .'</a>';
						echo '<div class="landing-widget-birthdays-content-item-text-work-position">' . $position .'</div>';
						echo '<div class="landing-widget-birthdays-content-item-text-date">' . $birthdayDate .'</div>';
						echo '</div>';
						echo '</div>';
					}
					?>
				</div>
			</div>
		<?php endif; ?>
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
				new BX.Landing.Widget.Birthdays(widgetElement);
			}
		}
	});
</script>
