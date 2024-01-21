<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION CMain
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load([
	'ui.buttons',
	'pull.client',
	'ui.label',
	'tasks.creation-menu',
]);

$messages = Loc::loadLanguageFile(__DIR__ . '/tasks.php');

?>

<div class="sn-spaces__toolbar-space_basic">
	<div class="sn-spaces__toolbar-space_left-content">
		<div id="sn-spaces-toolbar-tasks-add-btn"></div>

		<div
			class="sn-spaces__toolbar_filter-container ui-ctl ui-ctl-textbox ui-ctl-wa ui-ctl-after-icon ui-ctl-round ui-ctl-transp-white-borderless"
			id="sn-spaces__toolbar_filter-container"
		>
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				[
					'THEME' => Bitrix\Main\UI\Filter\Theme::SPACES,
					'FILTER_ID' => $arResult['FILTER_ID'],
					'GRID_ID' => $arResult['GRID_ID'],
					'FILTER' => $arResult['FILTER'],
					'FILTER_PRESETS' => $arResult['FILTER_PRESETS'],
					'ENABLE_LABEL' => true,
					'ENABLE_LIVE_SEARCH' => true,
					'RESET_TO_DEFAULT_MODE' => true,
				],
				null,
				[
					'HIDE_ICONS' => true
				],
			);
			?>
		</div>
		<div class="sn-spaces__toolbar-space_separator"></div>
		<div id="sn-spaces-toolbar-tasks-counters"></div>
	</div>

	<div class="sn-spaces__toolbar-space_right-content">
		<div id="sn-spaces-toolbar-tasks-view-btn"></div>
		<div id="sn-spaces-toolbar-tasks-settings-btn"></div>
	</div>

</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const tasksToolbar = new BX.Socialnetwork.Spaces.TasksToolbar({
			isUserSpace: false,
			isScrumSpace: false,
			userId: '<?= (int) $arResult['userId'] ?>',
			groupId: '<?= (int) $arResult['groupId'] ?>',
			filterId: '<?= $arResult['FILTER_ID'] ?>',
			filterContainer: document.getElementById('sn-spaces__toolbar_filter-container'),
			filterRole: '<?= $arResult['filterRole'] ?>',
			counters: <?= Json::encode($arResult['counters']) ?>,
			viewList: <?= Json::encode($arResult['viewList']) ?>,
			pathToGroupTasks: '<?= $arResult['pathToGroupTasks'] ?>',
			pathToAddTask: '<?= $arResult['pathToAddTask'] ?>',
			pathToTasks: '<?= $arResult['pathToTasks'] ?>',
			pathToTemplateList: '<?= $arResult['pathToTemplateList'] ?>',
			viewMode: '<?= CUtil::JSescape($arResult['viewMode']) ?>',
			order: '<?= CUtil::JSescape($arResult['order']) ?>',
			shouldSubtasksBeGrouped: <?= $arResult['shouldSubtasksBeGrouped'] ? 'true' : 'false' ?>,
			gridId: '<?= CUtil::JSescape($arResult['gridId']) ?>',
			sortFields: <?= Json::encode($arResult['sortFields']) ?>,
			taskSort: <?= Json::encode($arResult['taskSort']) ?>,
			syncScript: '<?= CUtil::JSescape($arResult['syncScript']) ?>',
			permissions: <?= Json::encode($arResult['permissions']) ?>,
		});

		tasksToolbar.renderAddBtnTo(document.getElementById('sn-spaces-toolbar-tasks-add-btn'));
		tasksToolbar.renderCountersTo(document.getElementById('sn-spaces-toolbar-tasks-counters'));
		tasksToolbar.renderViewBtnTo(document.getElementById('sn-spaces-toolbar-tasks-view-btn'));
		tasksToolbar.renderSettingsBtnTo(document.getElementById('sn-spaces-toolbar-tasks-settings-btn'));
	});
</script>