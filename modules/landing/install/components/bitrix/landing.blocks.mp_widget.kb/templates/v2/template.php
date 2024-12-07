<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @var \CMain $APPLICATION */

use \Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Uri;

\Bitrix\Main\UI\Extension::load([
	'ui.icon-set.main',
]);

Loc::loadMessages(__FILE__);

$id = 'widget-' . htmlspecialcharsbx(bin2hex(random_bytes(5)));
$useDemoData = $arResult['USE_DEMO_DATA'];
$sidebarKb = array_slice($arResult['KNOWLEDGE_BASES'], 0, 3);
?>

<div
	class="landing-widget-kb-v2"
	id="<?= $id ?>"
>
	<div class="landing-widget-view-main">
		<div class="landing-widget-kb-v2-title">
			<?= \htmlspecialcharsbx($arParams['TITLE']) ?>
			<?php if ($useDemoData): ?>
				<div class="landing-widget-kb-v2-badge --yellow">
					<?= Loc::getMessage('LANDING_WIDGET_KB_V2_DEMO_DATA') ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="landing-widget-kb-v2-content">
			<?php
			echo '<div class="landing-widget-kb-v2-content-row landing-widget-content-grid">';
			foreach ($arResult['KNOWLEDGE_BASES'] as $knowledgeBase)
			{
				echo '<div class="landing-widget-kb-v2-content-row-item">';
				echo '<a href="' . $knowledgeBase['PUBLIC_URL'] . '" target="_self" class="widget-kb-v2-item-link">';
				echo '<img class="widget-kb-v2-preview" src="' . $knowledgeBase['PREVIEW'] . '">';
				echo '<div class="widget-kb-v2-item-body">';
				echo '<div class="widget-kb-v2-item-body-head">';
				echo '<div class="widget-kb-v2-item-title">' . \htmlspecialcharsbx($knowledgeBase['TITLE']) . '</div>';
				echo '</div>';
				echo '</div>';
				echo '<div class="widget-kb-v2-item-views-box">';
				echo '<div class="ui-icon-set --opened-eye">' . '</div>';
				echo '<div class="widget-kb-v2-item-views">' . $knowledgeBase['VIEWS'] . '</div>';
				echo '</div>';
				echo '</a>';
				echo '</div>';
			}
			echo '</div>';
			?>
		</div>
		<div class="landing-widget-kb-v2-button-box">
			<button class="landing-widget-button --v2 extend-list-button">
				<?= $arResult['PHRASES']['NAVIGATOR_BUTTON']['EXTEND'] ?>
			</button>
			<button class="landing-widget-button --v2 view-all-button hide">
				<a href="/kb/" target="_blank">
					<?= $arResult['PHRASES']['NAVIGATOR_BUTTON']['VIEW_ALL'] ?>
				</a>
			</button>
		</div>
	</div>

	<div class="landing-widget-view-sidebar">
		<div class="landing-widget-kb-v2-title">
			<?= \htmlspecialcharsbx($arParams['TITLE']) ?>
			<?php if ($useDemoData): ?>
				<div class="landing-widget-kb-v2-badge --yellow">
					<?= Loc::getMessage('LANDING_WIDGET_KB_V2_DEMO_DATA') ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="landing-widget-kb-v2-content">
			<?php
			echo '<div class="landing-widget-kb-v2-content-row landing-widget-content-grid">';
			foreach ($sidebarKb as $knowledgeBase)
			{
				echo '<div class="landing-widget-kb-v2-content-row-item">';
				echo '<a href="' . $knowledgeBase['PUBLIC_URL'] . '" target="_self" class="widget-kb-v2-item-link">';
				echo '<img class="widget-kb-v2-preview" src="' . $knowledgeBase['PREVIEW'] . '">';
				echo '<div class="widget-kb-v2-item-body">';
				echo '<div class="widget-kb-v2-item-body-head">';
				echo '<div class="widget-kb-v2-item-title">' . \htmlspecialcharsbx($knowledgeBase['TITLE']) . '</div>';
				echo '</div>';
				echo '</div>';
				echo '<div class="widget-kb-v2-item-views-box">';
				echo '<div class="ui-icon-set --opened-eye">' . '</div>';
				echo '<div class="widget-kb-v2-item-views">' . $knowledgeBase['VIEWS'] . '</div>';
				echo '</div>';
				echo '</a>';
				echo '</div>';
			}
			echo '</div>';
			?>
		</div>
		<div class="landing-widget-kb-v2-button-box">
			<button class="landing-widget-button --v2 view-all-button">
				<a href="/kb/" target="_blank">
					<?= $arResult['PHRASES']['NAVIGATOR_BUTTON']['VIEW_ALL'] ?>
				</a>
			</button>
		</div>
	</div>
</div>

<script>
	BX.ready(function()
	{
		const editModeElement = document.querySelector('main.landing-edit-mode');
		if (!editModeElement)
		{
			const widgetElement = document.querySelector('#<?= $id ?>') ?? null;
			if (widgetElement)
			{
				const options = {
					isShowExtendButton: '<?= $arResult['IS_SHOW_EXTEND_BUTTON'] ?>',
				};
				new BX.Landing.Widget.KbV2(widgetElement, options);
			}
		}
	});
</script>
