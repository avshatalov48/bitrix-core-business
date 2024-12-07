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
$title = \htmlspecialcharsbx($arResult['TITLE']);
$buttonText = \htmlspecialcharsbx($arResult['BUTTON']);
$isShowEmptyState = $arResult['SHOW_EMPTY_STATE'];
$emptyStateText = Loc::getMessage('BLOCK_MP_WIDGET_BP_V2_EMPTY_STATE_TEXT');
$sidebarBusinessProcesses = array_slice($arResult['BUSINESS_PROCESSES'], 0, 4);
?>

<div class="landing-widget-bp-v2" id="<?= $id ?>">
	<div class="landing-widget-view-main">
		<div class="landing-widget-bp-v2-title">
			<?= $title ?>
		</div>
		<?php
		if ($isShowEmptyState): ?>
			<div class="landing-widget-bp-v2-empty-state">
				<div class="landing-widget-bp-v2-empty-state-icon"></div>
				<div class="landing-widget-bp-v2-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php
		else: ?>
			<div class="landing-widget-bp-v2-content landing-widget-content-grid">
				<?php
				foreach ($arResult['BUSINESS_PROCESSES'] as $businessProcess)
				{
					$averageText = $businessProcess['AVERAGE_TEXT'];
					$name = \htmlspecialcharsbx($businessProcess['NAME']);
					$url = $businessProcess['URL'] ?? '';
					$subtitle = $arResult['SUBTITLE'] ?? '';

					echo '<div class="landing-widget-bp-v2-content-item">';
					echo '<div class="landing-widget-bp-v2-content-item-head">';
					echo '<div class="landing-widget-bp-v2-content-item-title-container">';
					echo '<div class="landing-widget-bp-v2-content-item-title" title="' . $name . '">' . $name . '</div>';
					echo '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-line">' . '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-time-box">';
					echo '<div class="landing-widget-bp-v2-content-item-time">' . $subtitle . '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-time-value">' . $averageText . '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-button">';
					if ($url !== '')
					{
						$href = "BX.SidePanel.Instance.open('" . $url . "', { width: 900 });";
						echo '<a onclick="' . $href . '">' . $buttonText . '</a>';
					}
					else
					{
						echo '<div>' . $buttonText . '</div>';
					}
					echo '</div>';
					echo '</div>';
				}
				?>
			</div>
			<div class="landing-widget-bp-v2-button-box">
				<button class="landing-widget-button extend-list-button">
					<?= $arResult['PHRASES']['NAVIGATOR_BUTTON']['EXTEND'] ?>
				</button>
				<button class="landing-widget-button view-all-button hide">
					<a href="/bizproc/userprocesses/" target="_blank">
						<?= $arResult['PHRASES']['NAVIGATOR_BUTTON']['VIEW_ALL'] ?>
					</a>
				</button>
			</div>
		<?php
		endif; ?>
	</div>

	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-bp-v2-title">
			<?= $title ?>
		</div>
		<?php
		if ($isShowEmptyState): ?>
			<div class="landing-widget-bp-v2-empty-state">
				<div class="landing-widget-bp-v2-empty-state-icon"></div>
				<div class="landing-widget-bp-v2-empty-state-text">
					<?= $emptyStateText ?>
				</div>
			</div>
		<?php
		else: ?>
			<div class="landing-widget-bp-v2-content landing-widget-content-grid">
				<?php
				foreach ($sidebarBusinessProcesses as $businessProcess)
				{
					$averageText = $businessProcess['AVERAGE_TEXT'];
					$name = \htmlspecialcharsbx($businessProcess['NAME']);
					$url = $businessProcess['URL'] ?? '';
					$subtitle = $arResult['SUBTITLE'] ?? '';

					echo '<div class="landing-widget-bp-v2-content-item">';
					echo '<div class="landing-widget-bp-v2-content-item-head">';
					echo '<div class="landing-widget-bp-v2-content-item-title-container">';
					echo '<div class="landing-widget-bp-v2-content-item-title" title="' . $name . '">' . $name . '</div>';
					echo '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-line">' . '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-time-box">';
					echo '<div class="landing-widget-bp-v2-content-item-time">' . $subtitle . '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-time-value">' . $averageText . '</div>';
					echo '</div>';
					echo '</div>';
					echo '<div class="landing-widget-bp-v2-content-item-button">';
					if ($url !== '')
					{
						$href = "BX.SidePanel.Instance.open('" . $url . "', { width: 900 });";
						echo '<a onclick="' . $href . '">' . $buttonText . '</a>';
					}
					else
					{
						echo '<div>' . $buttonText . '</div>';
					}
					echo '</div>';
					echo '</div>';
				}
				?>
			</div>
			<div class="landing-widget-bp-v2-button-box">
				<button class="landing-widget-button view-all-button">
					<a href="/bizproc/userprocesses/" target="_blank">
						<?= $arResult['PHRASES']['NAVIGATOR_BUTTON']['VIEW_ALL'] ?>
					</a>
				</button>
			</div>
		<?php
		endif; ?>
	</div>
</div>

<script>
	BX.ready(function() {
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const widget = document.querySelector('#<?= $id ?>') ?? null;
			if (widget)
			{
				const options = {
					isShowExtendButton: '<?= $arResult['IS_SHOW_EXTEND_BUTTON'] ?>',
				};
				new BX.Landing.Widget.BpV2(widget, options);
			}
		}
	});
</script>
