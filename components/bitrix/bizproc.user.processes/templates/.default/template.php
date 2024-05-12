<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
?>
<div id="bp-user-processes-errors-container"></div>
<?php

/**
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 */

\Bitrix\Main\UI\Extension::load([
	'sidepanel',
	'ui',
	'ui.alerts',
	'ui.buttons',
	'ui.buttons.icons',
	'ui.cnt',
	'ui.entity-selector',
	"ui.fonts.opensans",
	'ui.icons',
	'ui.viewer',
]);
\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/bizproc.workflow.faces/templates/.default/style.css');

if (IsModuleInstalled('crm'))
{
	CJSCore::Init('sidepanel');
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/common.js');
}

$viewData = is_array($arResult['viewData'] ?? null) ? $arResult['viewData'] : [];
$workflows = $viewData['workflows'] ?? [];
$currentUserId = $viewData['userId'] ?? 0;
$targetUserId = $viewData['targetUserId'] ?? $currentUserId;
$component = $this->getComponent();

$renderDocumentName = function (array $document): string
{
	$documentUrl = $document['url'] ?? null;
	$documentName = $document['name'] ?? '';

	if (!isset($documentUrl))
	{
		return htmlspecialcharsbx($documentName ?? '');
	}

	return sprintf(
		'<a href="%s">%s</a>',
		$documentUrl,
		htmlspecialcharsbx($documentName),
	);
};

$openTaskInPopup = function (array $task) use ($targetUserId): string
{
	$taskId = $task['id'] ?? 0;

	return 'return BX.Bizproc.showTaskPopup('
		. $taskId
		. ', () => BX.Bizproc.Component.UserProcesses.Instance.reloadGrid(), '
		. $targetUserId
		. ', this)'
	;
};

$renderTaskAnchorAttrs = function (array $task) use ($viewData, $openTaskInPopup): string
{
	$noPopup = !($task['canShowInPopup'] ?? false);

	if ($noPopup)
	{
		return 'href="' . ($task['url'] ?? '#') . '"';
	}
	else
	{
		return 'href="#" onclick="' . $openTaskInPopup($task) . '"';
	}
};

$renderTaskComments = function (array $task) use ($renderTaskAnchorAttrs): string
{
	return
		'<div class="bp-comments"><a '
		. $renderTaskAnchorAttrs($task)
		. '><span class="bp-comments-icon"></span>'
		. htmlspecialcharsbx($task['comments'] ?? 0)
		. '</a></div>'
	;
};

$renderWorkflowComments = function (string $workflowId, int $commentsCount): string
{
	return
		sprintf(
			'<div class="bp-comments">
				<a href="#" onclick="return BX.Bizproc.showWorkflowInfoPopup(\'%s\')">
					<span class="bp-comments-icon"></span>
					%d
				</a>
			</div>',
			CUtil::JSEscape(htmlspecialcharsbx($workflowId)),
			$commentsCount,
		);
};

$renderTaskProgress = function ($workflowId, $taskId) use ($component)
{
	ob_start();

	global $APPLICATION;
	$APPLICATION->IncludeComponent(
		'bitrix:bizproc.workflow.faces',
		'',
		['WORKFLOW_ID' => $workflowId],
		$component,
	);

	return ob_get_clean() ?: '';
};

$renderTaskNameContainer = function (array $task, string $inner): string
{
	return sprintf(
		'<div class="bp-task-container" data-task-id="%d">%s</div>',
		$task['id'] ?? 0,
		$inner,
	);
};

$renderTaskName = function (array $task) use ($renderTaskAnchorAttrs, $renderTaskNameContainer): string
{
	return $renderTaskNameContainer(
		$task,
		sprintf(
			'<span class="bp-task"><a %s>%s</a></span>',
			$renderTaskAnchorAttrs($task),
			htmlspecialcharsbx($task['name'] ?? ''),
		),
	);
};

$renderTaskStatus = function (array $task): string
{
	$status = $task['status'] ?? 0;
	$statusName = htmlspecialcharsbx($task['statusName'] ?? '');

	if (!($task['canModify'] ?? false))
	{
		$stateTitle = is_string($task['workflowStateTitle'] ?? null) ? $task['workflowStateTitle'] : '';
		if (!$stateTitle)
		{
			return '';
		}

		return sprintf(
			'<span class="bp-status">
				<span class="bp-status-inner">
					<span>
						%s
					</span>
				</span>
			</span>',
			htmlspecialcharsbx($stateTitle),
		);
	}

	return match ($status)
	{
		CBPTaskUserStatus::No, CBPTaskUserStatus::Cancel => '<span class="bp-status-cancel">' . $statusName . '</span>',
		default =>'<span class="bp-status-ready">' . $statusName . '</span>',
	};
};

$isTaskCompletedByUser = function (array $task, int $userId): bool
{
	if ($userId <= 0)
	{
		return false;
	}

	foreach ($task['users'] ?? [] as $taskUser)
	{
		$taskUserId = $taskUser['id'] ?? 0;
		if ($taskUserId === $userId)
		{
			return ($taskUser['status'] ?? 0) !== CBPTaskUserStatus::Waiting;
		}
	}

	return false;
};

$renderTaskNameColumn = function (array $task) use (
	$renderTaskNameContainer,
	$renderTaskName,
	$renderTaskAnchorAttrs,
	$renderTaskStatus,
	$isTaskCompletedByUser,
	$currentUserId,
	$targetUserId,
): string
{
	$taskName = $task['name'] ?? '';

	if ($task['canView'] ?? false)
	{
		$taskId = $task['id'] ?? 0;
		$taskStatus = $task['status'] ?? null;
		$taskControls = is_array($task['controls'] ?? null) ? $task['controls'] : null;
		$canModify = $task['canModify'] ?? false;

		if (!$canModify)
		{
			if ($targetUserId === $currentUserId)
			{
				$renderedTaskName = $renderTaskNameContainer(
					$task,
					'<span>' . htmlspecialcharsbx($taskName) . '</span>'
				);
			}
			else
			{
				$renderedTaskName = $renderTaskName($task);
			}

			return $renderedTaskName . $renderTaskStatus($task);
		}
		elseif ($isTaskCompletedByUser($task, $currentUserId) || $taskStatus > CBPTaskStatus::Running)
		{
			return $renderTaskName($task) . $renderTaskStatus($task);
		}
		elseif (is_array($taskControls) && $taskStatus === CBPTaskStatus::Running)
		{
			$result = $renderTaskName($task) . '<div class="bp-btn-panel">';
			foreach ($taskControls['BUTTONS'] ?? [] as $button)
			{
				$isDecline =
					$button['TARGET_USER_STATUS'] == CBPTaskUserStatus::No
					|| $button['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel
				;
				$class = $isDecline ? 'danger' : 'success';
				$icon = $isDecline ? 'cancel' : 'done';
				$props = CUtil::PhpToJSObject(array(
					'TASK_ID' => $taskId,
					$button['NAME'] => $button['VALUE'],
				));

				$result .= sprintf(
					'<a
						href="#"
						onclick = "return BX.Bizproc.doInlineTask(
							%s,
							() => BX.Bizproc.Component.UserProcesses.Instance.updateTaskData(%d),
							this
						)"
						class="ui-btn ui-btn-%s ui-btn-icon-%s"
					>%s</a>
					',
					$props,
					(int)$taskId,
					$class,
					$icon,
					htmlspecialcharsbx($button['TEXT'] ?? ''),
				);
			}
			$result .= '</div>';

			return $result;
		}
		else
		{
			$anchor = '<a '
				. $renderTaskAnchorAttrs($task)
				. ' class="ui-btn ui-btn-primary">'
				. \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_DEFAULT_TASK_BUTTON')
				. '</a>';

			return $renderTaskName($task) . '<div class="bp-btn-panel">' . $anchor . '</div>';
		}
	}

	return $renderTaskNameContainer($task, '<span>' . htmlspecialcharsbx($taskName) . '</span>');
};

$renderTaskDescription = function (array $task): string
{
	if (!($task['canView'] ?? false))
	{
		return '';
	}
	$taskDescription = $task['description'] ?? '';

	return sprintf(
		'<span class="bp-task-description">%s</span>',
		CBPViewHelper::prepareTaskDescription(
			CBPHelper::convertBBtoText((string)$taskDescription)
		),
	);
};

$renderUserName = function (\Bitrix\Main\EO_User $user): string
{
	return CUser::FormatName(CSite::GetNameFormat(false), $user);
};

$getRowActions = function (array $document, ?array $task) use ($openTaskInPopup): array
{
	$actions = [
		[
			'text' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_ROW_ACTION_DOCUMENT'),
			'href' => $document['url'] ?? '#',
		],
	];

	if (is_array($task))
	{
		$openTaskAction = [
			'text' => \Bitrix\Main\Localization\Loc::getMessage('BIZPROC_USER_PROCESSES_TEMPLATE_ROW_ACTION_TASK'),
		];

		if ($task['canModify'] ?? false)
		{
			if ($task['canShowInPopup'] ?? false)
			{
				$openTaskAction['onclick'] = $openTaskInPopup($task);
			}
			else
			{
				$openTaskAction['href'] = $task['url'] ?? '#';
			}

			$actions[] = $openTaskAction;
		}
	}

	return $actions;
};

$prepareWorkflowTasks = function (array $workflow, array $tasks) use (
	$renderTaskNameColumn,
	$isTaskCompletedByUser,
	$currentUserId
): array
{
	$preparedTasks = [];

	foreach ($tasks as $task)
	{
		$canModify = $task['canModify'] ?? false;
		$taskStatus = $task['status'] ?? -1;
		$task['workflowStateTitle'] = $workflow['workflowStateTitle'] ?? '';

		$preparedTasks[] = [
			'id' => $task['id'] ?? 0,
			'name' => $task['name'] ?? '',
			'canComplete' => (
				$canModify
				&& $taskStatus === CBPTaskStatus::Running
				&& !$isTaskCompletedByUser($task, $currentUserId)
			),
			'renderedName' => $renderTaskNameColumn($task),
		];
	}

	return $preparedTasks;
};

$wrapTask = function (string $workflowId, string $taskName, array $tasks): string
{
	return sprintf(
		'<div data-role="workflow-tasks-data" data-workflow-id="%s" data-tasks="%s">%s</div>',
		htmlspecialcharsbx($workflowId),
		htmlspecialcharsbx(\Bitrix\Main\Web\Json::encode($tasks)),
		$taskName,
	);
};

$gridRows = [];
foreach ($workflows as $row)
{
	$workflowId = $row['workflowId'] ?? '';
	$document = $row['document'] ?? [];
	$tasks = $row['tasks'] ?? [];

	$taskToShow = null;
	$overdueDate = '';
	$taskName = '';
	$taskProgress = '';
	$taskComments = '';
	$taskDescription = '';
	$taskModified = '';
	if ($tasks)
	{
		$taskToShow = $tasks[0];
		$taskToShow['workflowStateTitle'] = $row['workflowStateTitle'] ?? '';
		$canModify = $taskToShow['canModify'] ?? false;

		$overdueDate = $taskToShow['overdueDate'] ?? '';
		$taskName = $wrapTask($workflowId, $renderTaskNameColumn($taskToShow), $prepareWorkflowTasks($row, $tasks));
		$taskProgress = $renderTaskProgress($workflowId, $taskToShow['id'] ?? 0);
		$taskComments =
			$canModify
				? $renderTaskComments($taskToShow)
				: $renderWorkflowComments($workflowId, $taskToShow['comments'] ?? 0)
		;
		$taskDescription = $renderTaskDescription($taskToShow);
		$taskModified = $taskToShow['modified'] ?? '';
	}
	$process = sprintf(
		'%s<br><br>%s',
		$renderDocumentName($document),
		$taskDescription,
	);

	$gridRows[] = [
		'id' => $workflowId,
		'columns' => [
			'ID' => $workflowId,
			'PROCESS' => $process,
			'TASK_PROGRESS' => $taskProgress,
			'TASK' => $taskName,
			'TASK_COMMENTS' => $taskComments,
			'WORKFLOW_STATE' => htmlspecialcharsbx($row['workflowStateTitle'] ?? ''),
			'DOCUMENT_NAME' => $renderDocumentName($document),
			'WORKFLOW_TEMPLATE_NAME' => htmlspecialcharsbx($row['templateName'] ?? ''),
			'TASK_DESCRIPTION' => $taskDescription,
			'MODIFIED' => htmlspecialcharsbx($taskModified),
			'WORKFLOW_STARTED' => htmlspecialcharsbx($row['workflowStarted'] ?? ''),
			'WORKFLOW_STARTED_BY' => isset($row['startedBy']) ? $renderUserName($row['startedBy']) : '',
			'OVERDUE_DATE' => htmlspecialcharsbx($overdueDate),
		],
		'actions' => $getRowActions($document, $taskToShow),
	];
}

/** @var array $arResult */
global $APPLICATION;

$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'GRID_ID' => $arResult['gridId'],
		'COLUMNS' => $arResult['gridColumns'],
		'ROWS' => $gridRows,
		'SHOW_ROW_CHECKBOXES' => true,
		'NAV_OBJECT' => $arResult['pageNavigation'],
		'AJAX_MODE' => 'Y',
		// 'AJAX_ID' => CAjax::getComponentID('bitrix:bizproc.user.processes'),
		'PAGE_SIZES' => $arResult['pageSizes'],
		'AJAX_OPTION_JUMP' => 'N',
		'SHOW_ROW_ACTIONS_MENU' => true,
		'SHOW_GRID_SETTINGS_MENU' => true,
		'SHOW_NAVIGATION_PANEL' => true,
		'SHOW_PAGINATION' => true,
		'SHOW_SELECTED_COUNTER' => false,
		'SHOW_TOTAL_COUNTER' => true,
		'TOTAL_ROWS_COUNT' => $arResult['pageNavigation']->getRecordCount(),
		'SHOW_PAGESIZE' => true,
		'SHOW_ACTION_PANEL' => true,
		'ACTION_PANEL' => $arResult['gridActions'] ?? null,
		'ALLOW_COLUMNS_SORT' => true,
		'ALLOW_COLUMNS_RESIZE' => true,
		'ALLOW_HORIZONTAL_SCROLL' => true,
		'ALLOW_INLINE_EDIT' => true,
		'ALLOW_SORT' => true,
		'ALLOW_PIN_HEADER' => true,
		'AJAX_OPTION_HISTORY' => 'N',
		'HANDLE_RESPONSE_ERROR' => true,
		'MESSAGES' => array_map(
			fn ($error) => [
				'TEXT' => $error->getMessage(),
				'TYPE' => 'error',
			],
			$this->getComponent()->getErrors(),
		),
	],
);

$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);
?>
<script>
	BX.ready(function ()
	{
		BX.message(<?= \Bitrix\Main\Web\Json::encode($messages) ?>);

		const gridId = '<?= CUtil::JSEscape($arResult['gridId']) ?>';

		BX.Bizproc.Component.UserProcesses.Instance = new BX.Bizproc.Component.UserProcesses({
			gridId: gridId,
			actionPanelUserWrapperId: '<?= CUtil::JSEscape($viewData['actionPanelUserWrapperId'] ?? null) ?>',
			errors: [],
			currentUserId: <?= (int)($viewData['userId'] ?? 0) ?>
		});
		BX.addCustomEvent('Grid::updated', () => BX.Bizproc.Component.UserProcesses.Instance.init());

		<?php if (isset($viewData['startWorkflowButtonId'])): ?>
			BX.Bizproc.Component.UserProcesses.Instance.initStartWorkflowButton(
				'<?= CUtil::JSEscape($viewData['startWorkflowButtonId']) ?>'
			);
		<?php endif; ?>
	})
</script>
