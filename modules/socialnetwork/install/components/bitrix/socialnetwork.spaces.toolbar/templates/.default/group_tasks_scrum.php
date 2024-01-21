<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION \CMain
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;
use Bitrix\Tasks\Helper\Filter;
use Bitrix\Tasks\Scrum\Service\TaskService;

Extension::load([
	'ui.buttons',
	'ui.entity-selector',
	'pull.client',
	'tasks.creation-menu',
]);

$messages = Loc::loadLanguageFile(__DIR__ . '/tasks.php');

$taskService = new TaskService($arResult['userId']);

$filterInstance = $taskService->getFilterInstance($arResult['groupId'], $arResult['viewMode']);

$filterId = $filterInstance->getId();
$filters = $filterInstance->getFilters();

$presets = Filter::getPresets($filterInstance);
if ($arResult['viewMode'] === 'complete')
{
	unset($presets['filter_tasks_scrum']);
}

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
					'FILTER_ID' => $filterId,
					'FILTER' => $filters,
					'FILTER_PRESETS' => $presets,
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

		<?php if ($arResult['viewMode'] === 'plan'): ?>
			<div
				id="sn-spaces-toolbar-tasks-short-view-btn"
				class="sn-spaces__toolbar-space_tasks-short-view-btn"
			></div>
		<?php endif; ?>

		<?php if ($arResult['viewMode'] === 'complete'): ?>
			<div id="sn-spaces-toolbar-tasks-sprint-selector"></div>
		<?php endif; ?>

		<?php if ($arResult['viewMode'] === 'active'): ?>
			<div
				id="sn-spaces-toolbar-tasks-robots-btn"
				class="sn-spaces__toolbar-tasks_robots-btn"
			></div>
		<?php endif; ?>

		<div id="sn-spaces-toolbar-tasks-settings-btn"></div>
	</div>

</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const viewMode = '<?= CUtil::JSescape($arResult['viewMode']) ?>';
		const tasksToolbar = new BX.Socialnetwork.Spaces.TasksToolbar({
			isUserSpace: false,
			isScrumSpace: true,
			groupId: '<?= (int) $arResult['groupId'] ?>',
			filterId: '<?= $filterId ?>',
			filterContainer: document.getElementById('sn-spaces__toolbar_filter-container'),
			filterRole: '<?= $arResult['filterRole'] ?>',
			counters: <?= Json::encode($arResult['counters']) ?>,
			viewList: <?= Json::encode($arResult['viewList']) ?>,
			pathToGroupTasks: '<?= $arResult['pathToGroupTasks'] ?>',
			pathToAddTask: '<?= $arResult['pathToAddTask'] ?>',
			pathToGroupTasksTask: '<?= $arResult['pathToGroupTasksTask'] ?>',
			pathToTemplateList: '<?= $arResult['pathToTemplateList'] ?>',
			pathToScrumBurnDown: '<?= $arResult['pathToScrumBurnDown'] ?>',
			displayPriority: '<?= CUtil::JSescape($arResult['displayPriority']) ?>',
			isShortView: '<?= CUtil::JSescape($arResult['isShortView']) ?>',
			viewMode,
			order: '<?= CUtil::JSescape($arResult['order'] ?? null) ?>',
			activeSprintId: '<?= $arResult['activeSprintId'] ?>',
			taskLimitExceeded: '<?= ($arResult['taskLimitExceeded'] ? 'Y' : 'N') ?>',
			canUseAutomation: '<?= ($arResult['canUseAutomation'] ? 'Y' : 'N') ?>',
			canEditSprint: '<?= ($arResult['canCompleteSprint'] ? 'Y' : 'N') ?>',
			currentCompletedSprint: <?= Json::encode($arResult['currentCompletedSprint']) ?>,
		});

		tasksToolbar.renderScrumAddBtnTo(document.getElementById('sn-spaces-toolbar-tasks-add-btn'));
		tasksToolbar.renderCountersTo(document.getElementById('sn-spaces-toolbar-tasks-counters'));
		if (viewMode === 'complete')
		{
			tasksToolbar.renderScrumSprintSelector(
				document.getElementById('sn-spaces-toolbar-tasks-sprint-selector')
			);
		}
		tasksToolbar.renderViewBtnTo(document.getElementById('sn-spaces-toolbar-tasks-view-btn'));
		if (viewMode === 'plan')
		{
			tasksToolbar.renderScrumShortView(document.getElementById('sn-spaces-toolbar-tasks-short-view-btn'));
		}
		if (viewMode === 'active')
		{
			tasksToolbar.renderScrumRobots(document.getElementById('sn-spaces-toolbar-tasks-robots-btn'));
		}
		tasksToolbar.renderSettingsBtnTo(document.getElementById('sn-spaces-toolbar-tasks-settings-btn'));
	});
</script>