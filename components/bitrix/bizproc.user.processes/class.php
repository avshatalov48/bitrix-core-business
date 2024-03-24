<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateToGet;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Bizproc\Workflow\WorkflowState;
use Bitrix\Bizproc\Workflow\Task;

class BizprocUserProcesses
	extends CBitrixComponent
	implements \Bitrix\Main\Errorable, \Bitrix\Main\Engine\Contract\Controllerable
{
	const GRID_ID = 'bizproc_user_processes';
	const FILTER_ID = 'bizproc_user_processes_filter';

	private ErrorCollection $errorCollection;
	private \Bitrix\Main\UI\Filter\Options $filterOptions;

	public function configureActions()
	{
		return [];
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
		$this->filterOptions = new \Bitrix\Main\UI\Filter\Options(static::FILTER_ID);
	}

	public function addErrors(array $errors): static
	{
		$this->errorCollection->add($errors);

		return $this;
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function setError(Error $error): static
	{
		$this->errorCollection->setError($error);

		return $this;
	}

	public function hasErrors(): bool
	{
		return !$this->errorCollection->isEmpty();
	}

	public function delegateTasksAction(array $taskIds, int $toUserId): ?array
	{
		$this->init();

		if (!$this->hasErrors() && $toUserId > 0)
		{
			$preparedTaskIds = [];
			foreach ($taskIds as $id)
			{
				if (is_numeric($id))
				{
					$preparedTaskIds[] = (int)$id;
				}
			}

			$taskService = new \Bitrix\Bizproc\Api\Service\TaskService(
				new \Bitrix\Bizproc\Api\Service\TaskAccessService($this->getCurrentUserId()),
			);

			$delegateRequest = new \Bitrix\Bizproc\Api\Request\TaskService\DelegateTasksRequest(
				$preparedTaskIds,
				$this->getTargetUserId(),
				$toUserId,
				$this->getCurrentUserId(),
			);
			$delegationResult = $taskService->delegateTasks($delegateRequest);

			if ($delegationResult->isSuccess())
			{
				return [
					'message' => $delegationResult->getSuccessDelegateTaskMessage(),
				];
			}
			else
			{
				$this->addErrors($delegationResult->getErrors());
			}
		}

		return null;
	}

	public function executeComponent(): void
	{
		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage('BIZPROC_USER_PROCESSES_TITLE'));

		$this->init();

		$this->addToolbar();
		$this->fillGridInfo();
		if (!$this->hasErrors())
		{
			$this->fillGridData();
			$this->fillGridActions();
		}

		$this->includeComponentTemplate();
	}

	private function init(): void
	{
		$this->checkModules();
		$this->checkRights();

		$this->arResult['viewData'] = [];
	}

	private function checkModules(): void
	{
		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			$errorMessage = Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ERROR', ['#MODULE#' => 'BizProc']);
			$this->setError(new Error($errorMessage));
		}
	}

	private function checkRights(): void
	{
		$accessService = new \Bitrix\Bizproc\Api\Service\TaskAccessService($this->getCurrentUserId());

		$taskViewAccessResult = $accessService->checkViewTasks($this->getTargetUserId());
		if (!$taskViewAccessResult->isSuccess())
		{
			$this->addErrors($taskViewAccessResult->getErrors());
		}
	}

	private function fillGridInfo(): void
	{
		$this->arResult['gridId'] = static::GRID_ID;
		$this->arResult['gridColumns'] = $this->getGridColumns();
		$this->arResult['pageNavigation'] = $this->getPageNavigation();
		$this->arResult['pageSizes'] = $this->getPageSizes();
	}

	private function getGridColumns(): array
	{
		return [
			[
				'id' => 'ID',
				'name' => 'ID',
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'DOCUMENT_NAME',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_DOCUMENT_NAME'),
				'default' => false,
				'sort' => ''
			],
			[
				'id' => 'TASK_DESCRIPTION',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_DESCRIPTION'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'PROCESS',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_PROCESS'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'TASK_PROGRESS',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_TASK_PROGRESS'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'WORKFLOW_TEMPLATE_NAME',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_TEMPLATE_NAME'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'TASK',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_TASK'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'TASK_COMMENTS',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_COMMENTS'),
				'default' => true,
				'sort' => '',
				'hideName' => true,
				'iconCls' => 'bp-comments-icon',
			],
			[
				'id' => 'WORKFLOW_STATE',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_STATE'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'MODIFIED',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_MODIFIED'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'WORKFLOW_STARTED',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_STARTED'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'WORKFLOW_STARTED_BY',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_STARTED_BY'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'OVERDUE_DATE',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_OVERDUE_DATE'),
				'default' => false,
				'sort' => '',
			],
		];
	}

	private function getPageNavigation(): \Bitrix\Main\UI\PageNavigation
	{
		$options = new \Bitrix\Main\Grid\Options(static::GRID_ID);
		$navParams = $options->GetNavParams();

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation(static::GRID_ID);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		return $pageNavigation;
	}

	private function getPageSizes(): array
	{
		return [
			['NAME' => '5', 'VALUE' => '5'],
			['NAME' => '10', 'VALUE' => '10'],
			['NAME' => '20', 'VALUE' => '20'],
			['NAME' => '50', 'VALUE' => '50'],
			['NAME' => '100', 'VALUE' => '100'],
		];
	}

	private function fillGridData(): void
	{
		/** @var \Bitrix\Main\UI\PageNavigation $pageNav */
		$pageNav = $this->arResult['pageNavigation'];

		$workflowStateService = new WorkflowStateService();

		$workflowsRequest = (new WorkflowStateToGet())
			->setAdditionalSelectFields([
				'STARTED_BY',
				'STATE_TITLE',
				'STARTED',
				'STARTED_BY',
				'TEMPLATE.NAME',
			])
			->setTaskSelectFields([
				'NAME',
				'ACTIVITY',
				'DESCRIPTION',
				'MODIFIED',
				'STATUS',
				'IS_INLINE',
				'DELEGATION_TYPE',
				'OVERDUE_DATE',
				'PARAMETERS',
				'TASK_USERS.USER_ID',
				'TASK_USERS.STATUS',
			])
			->setLimit($pageNav->getLimit())
			->setOffset($pageNav->getOffset())
			->countTotal()
		;

		$this->setFilterToRequest($workflowsRequest);

		$workflowsResponse = $workflowStateService->getList($workflowsRequest);

		$workflowViews = [];
		if ($workflowsResponse->isSuccess())
		{
			$workflows = $workflowsResponse->getWorkflowStatesCollection();
			$pageNav->setRecordCount($workflowsResponse->getTotalCount());
			foreach ($workflows as $workflowState)
			{
				$tasks = $workflowsResponse->getWorkflowTasks($workflowState->getId());
				$preparedTasks = $this->prepareTasksForView($tasks?->getAll() ?? []);
				$complexDocumentId = $workflowState->getComplexDocumentId();

				$workflowViews[$workflowState->getId()] = [
					'workflowId' => $workflowState->getId(),
					'startedBy' => \Bitrix\Main\UserTable::getById($workflowState->getStartedBy())->fetchObject(),
					'workflowStateTitle' => $workflowState->getStateTitle(),
					'templateName' => $workflowState->getTemplate()?->fillName() ?? '',
					'workflowStarted' => FormatDateFromDB($workflowState->getStarted()),
					'document' => [
						'url' => $this->getDocumentUrl($complexDocumentId),
						'name' => $this->getDocumentName($complexDocumentId),
					],
					'tasks' => $this->getTasksViewData($workflowState, $preparedTasks),
				];
			}
		}
		else
		{
			$this->addErrors($workflowsResponse->getErrors());
		}

		$this->arResult['viewData']['userId'] = $this->getCurrentUserId();
		$this->arResult['viewData']['targetUserId'] = $this->getTargetUserId();
		$this->arResult['viewData']['workflows'] = $workflowViews;
	}

	private function setFilterToRequest(WorkflowStateToGet $workflowsRequest): void
	{
		$workflowsRequest->setFilterUserId($this->getTargetUserId());

		$userFilter = $this->filterOptions->getFilter();

		if (isset($userFilter['SYSTEM_PRESET']))
		{
			$workflowsRequest->setFilterPresetId($userFilter['SYSTEM_PRESET']);
		}
	}

	/**
	 * @param Task[] $tasks
	 * @return array
	 */
	private function prepareTasksForView(array $tasks): array
	{
		if (!$tasks)
		{
			return [];
		}

		$preparedTasks = [];
		$taskIdx = 0;
		$firstTaskIdx = null;
		$hasTargetUserTask = false;
		$hasCurrentUserTask = false;
		foreach ($tasks as $userTask)
		{
			$currentTaskUser = null;
			$targetTaskUser = null;
			foreach ($userTask->getTaskUsers() as $taskUser)
			{
				if ($taskUser->getUserId() === $this->getCurrentUserId())
				{
					$currentTaskUser = $taskUser;
				}
				elseif ($taskUser->getUserId() === $this->getTargetUserId())
				{
					$targetTaskUser = $taskUser;
				}
				elseif (isset($currentTaskUser, $targetTaskUser))
				{
					break;
				}
			}

			if (
				isset($targetTaskUser)
				&& $targetTaskUser->getStatus() === CBPTaskUserStatus::Waiting
				&& !$hasTargetUserTask
			)
			{
				$hasTargetUserTask = true;
				$firstTaskIdx = $taskIdx;
			}
			elseif (
				isset($currentTaskUser)
				&& $currentTaskUser->getStatus() === CBPTaskUserStatus::Waiting
				&& !$hasTargetUserTask
				&& !$hasCurrentUserTask
			)
			{
				$hasCurrentUserTask = true;
				$firstTaskIdx = $taskIdx;
			}
			if (isset($targetTaskUser) || isset($currentTaskUser))
			{
				$preparedTasks[] = $userTask;
				$taskIdx += 1;
			}
		}

		if (!$preparedTasks)
		{
			$preparedTasks[] = end($tasks);
		}
		elseif (is_int($firstTaskIdx) && $firstTaskIdx > 0)
		{
			[$preparedTasks[0], $preparedTasks[$firstTaskIdx]] = [$preparedTasks[$firstTaskIdx], $preparedTasks[0]];
		}

		return $preparedTasks;
	}

	/**
	 * @param WorkflowState $state
	 * @param Task[] $workflowTasks
	 * @return Task[]
	 */
	private function getTasksViewData(WorkflowState $state, array $workflowTasks): array
	{
		$complexDocumentId = $state->getComplexDocumentId();
		$taskViews = [];

		$isAdmin = $this->isCurrentUserAdmin();
		foreach ($workflowTasks as $task)
		{
			$isResponsibleForTask = $task->isResponsibleForTask($this->getCurrentUserId());
			// todo - move to service
			$canView =
				$isAdmin
				|| CBPHelper::checkUserSubordination($this->getCurrentUserId(), $this->getTargetUserId())
				|| $isResponsibleForTask
			;
			$canModify = $isResponsibleForTask;

			$viewData = [
				'id' => $task->getId(),
				'name' => $task->getName(),
				'status' => $task->getStatus(),
				'statusName' => $this->getTaskStatusName($task->getStatus()),
				'canView' => $canView,
				'canModify' => $canModify,
				'canShowInPopup' => (
					$task->getActivity() !== 'RequestInformationActivity'
					&& $task->getActivity() !== 'RequestInformationOptionalActivity'
					&& $complexDocumentId[0] !== 'rpa'
				),
				'controls' =>
					$task->isInline()
						? CBPDocument::getTaskControls($task->getValues())
						: null,
				'modified' => FormatDateFromDB($task->getModified()),
				'url' => $this->getTaskUrl($task),
				'users' => $this->getTaskUsersView($task),
			];
			if ($task->hasDescription() && ($canView || !$task->isRightsRestricted()))
			{
				$viewData['description'] = $task->get('DESCRIPTION');
			}
			if ($task->hasOverdueDate() && $task->get('OVERDUE_DATE'))
			{
				$viewData['overdueDate'] = FormatDateFromDB($task->get('OVERDUE_DATE'));
			}
			if (\Bitrix\Main\Loader::includeModule('forum'))
			{
				$viewData['comments'] = $this->getWorkflowCommentsViewData($state);
			}

			$taskViews[] = $viewData;
		}

		return $taskViews;
	}

	private function getTaskStatusName(int $status): ?string
	{
		return match ($status)
		{
			CBPTaskUserStatus::Yes => Loc::getMessage('BIZPROC_USER_PROCESSES_TASK_STATUS_YES'),
			CBPTaskUserStatus::No, CBPTaskUserStatus::Cancel => Loc::getMessage('BIZPROC_USER_PROCESSES_TASK_STATUS_NO'),
			default => Loc::getMessage('BIZPROC_USER_PROCESSES_TASK_STATUS_OK'),
		};
	}

	private function getWorkflowCommentsViewData(WorkflowState $state): int
	{
		$topic = CForumTopic::getList([], ['XML_ID' => 'WF_' . $state->getId()])->fetch() ?: [];

		return isset($topic['POSTS']) ? (int)$topic['POSTS'] : 0;
	}

	private function getTaskUrl(Task $task): string
	{
		$parameters = $task->getParameters();

		if (is_string($parameters['TASK_EDIT_URL'] ?? null) && $parameters['TASK_EDIT_URL'])
		{
			$taskUrl = $parameters['TASK_EDIT_URL'];
		}
		else
		{
			$taskUrl = '/company/personal/bizproc/#ID#/';
		}

		$rawUrl = CComponentEngine::makePathFromTemplate(
			$taskUrl,
			['ID' => $task->getId(), 'task_id' => $task->getId()],
		);

		if (!$task->isResponsibleForTask($this->getCurrentUserId()))
		{
			$url = new \Bitrix\Main\Web\Uri($rawUrl);
			$url->addParams([
				'USER_ID' => $this->getTargetUserId(),
			]);

			return $url->getUri();
		}

		return $rawUrl;
	}

	private function getTaskUsersView(Task $task): array
	{
		$usersView = [];

		foreach ($task->getTaskUsers() as $taskUser)
		{
			$usersView[] = [
				'id' => $taskUser->getUserId(),
				'status' => $taskUser->getStatus(),
			];
		}

		return $usersView;
	}

	private function fillGridActions(): void
	{
		$snippet = new \Bitrix\Main\Grid\Panel\Snippet();

		$delegateToContainerId = 'action-user-control-container';
		$this->arResult['viewData']['actionPanelUserWrapperId'] = $delegateToContainerId;

		$actions = [
			'set_status_' . CBPTaskUserStatus::Yes => Loc::getMessage('BIZPROC_USER_PROCESSES_ACTION_SET_STATUS_YES'),
			'set_status_' . CBPTaskUserStatus::No => Loc::getMessage('BIZPROC_USER_PROCESSES_ACTION_SET_STATUS_NO'),
			'set_status_' . CBPTaskUserStatus::Ok => Loc::getMessage('BIZPROC_USER_PROCESSES_ACTION_SET_STATUS_OK'),
		];
		if (\Bitrix\Main\Loader::includeModule('intranet'))
		{
			$actions['delegate_to'] = Loc::getMessage('BIZPROC_USER_PROCESSES_ACTION_DELEGATE');
		}

		$setStatusButtonItems = [];
		foreach ($actions as $actionName => $actionTitle)
		{
			$setStatusButtonItems[] = [
				'NAME' => $actionTitle,
				'VALUE' => $actionName,
				'ONCHANGE' => [
					[
						'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
						'DATA' => [
							[
								'JS' => 'BX.Bizproc.Component.UserProcesses.Instance.onActionPanelChanged()',
							],
						],
					],
				],
			];
		}

		$this->arResult['gridActions'] = [
			'GROUPS' => [
				[
					'ITEMS' => [
						// $snippet->getForAllCheckbox(),
						[
							'TYPE' => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
							'NAME' => static::GRID_ID . '_action_button',
							'MULTIPLE' => 'N',
							'ITEMS' => $setStatusButtonItems,
						],
						[
							'TYPE' => \Bitrix\Main\Grid\Panel\Types::CUSTOM,
							'NAME' => static::GRID_ID . '_delegate_to',
							'VALUE' => "<div id = \"{$delegateToContainerId}\"></div>",
						],
						$snippet->getApplyButton([
							'ONCHANGE' => [
								[
									'ACTION' => \Bitrix\Main\Grid\Panel\Actions::CALLBACK,
									'DATA' => [
										[
											'JS' => 'BX.Bizproc.Component.UserProcesses.Instance.applyActionPanelValues()',
										],
									],
								],
							],
						]),
					],
				],
			],
		];
	}

	// TODO - implement service
	private function getDocumentName(array $complexDocumentId): string
	{
		$documentService = CBPRuntime::getRuntime()->getDocumentService();

		return $documentService->getDocumentName($complexDocumentId) ?? '';
	}

	// TODO - implement service
	private function getDocumentUrl(array $complexDocumentId): ?string
	{
		return CBPDocument::getDocumentAdminPage($complexDocumentId);
	}

	private function getTargetUserId(): int
	{
		return (int)($this->filterOptions->getFilter()['TARGET_USER'] ?? $this->getCurrentUserId());
	}

	private function getCurrentUserId(): int
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->getId();
	}

	private function isCurrentUserAdmin(): bool
	{
		return
			\Bitrix\Main\Engine\CurrentUser::get()->isAdmin()
			|| (
				\Bitrix\Main\Loader::includeModule('bitrix24')
				&& CBitrix24::IsPortalAdmin($this->getCurrentUserId())
			)
		;
	}

	private function addToolbar(): void
	{
		$filterParams = [
			'FILTER_ID' => static::FILTER_ID,
			'GRID_ID' => static::GRID_ID,
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => $this->getFilterPresets(),
			'ENABLE_LABEL' => true,
			'DISABLE_SEARCH' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => \Bitrix\Main\UI\Filter\Theme::MUTED,
		];

		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFavoriteStar();

		$lists = $this->getLists();
		if (is_array($lists))
		{
			$addButton = new \Bitrix\UI\Buttons\AddButton([
				'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
				'text' => Loc::getMessage('BIZPROC_USER_PROCESSES_BUTTON_START_WORKFLOW'),
			]);

			$this->arResult['viewData']['startWorkflowButtonId'] = static::GRID_ID . '-filter-start-workflow-button';
			$addButton->addAttribute('id', $this->arResult['viewData']['startWorkflowButtonId']);
			$addButton->addAttribute('data-lists', \Bitrix\Main\Web\Json::encode($lists));

			\Bitrix\UI\Toolbar\Facade\Toolbar::addButton(
				$addButton,
				\Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE,
			);
		}
	}

	private function getLists(): ?array
	{
		if (!\Bitrix\Main\Loader::includeModule('lists'))
		{
			return null;
		}

		$iblockTypeId = \Bitrix\Main\Config\Option::get("lists", "livefeed_iblock_type_id");
		$hasPermissions = $this->checkListsPermission($iblockTypeId);
		if (!$hasPermissions)
		{
			return null;
		}

		$siteDir = SITE_DIR;
		$siteId = SITE_ID;

		$path = rtrim($siteDir, '/');

		$listData = [];
		$lists = CIBlock::getList(
			[
				'SORT' => 'ASC',
				'NAME' => 'ASC'
			],
			[
				'ACTIVE' => 'Y',
				'TYPE' => $iblockTypeId,
				'SITE_ID' => $siteId
			],
		);
		while($list = $lists->fetch())
		{
			if(CLists::getLiveFeed($list['ID']))
			{
				$listData[$list['ID']]['name'] = $list['NAME'];

				$url = new \Bitrix\Main\Web\Uri($path . \Bitrix\Main\Config\Option::get('lists', 'livefeed_url'));
				$url->addParams([
					'livefeed' => 'y',
					'list_id' => $list['ID'],
					'element_id' => 0,
					'back_url' => $this->request->getRequestUri(),
				]);
				$listData[$list['ID']]['url'] = $url;
				if($list['PICTURE'] > 0)
				{
					$imageFile = CFile::GetFileArray($list['PICTURE']);
					if($imageFile !== false)
					{
						$imageFile = CFile::ResizeImageGet(
							$imageFile,
							['width' => 36, 'height' => 30],
							BX_RESIZE_IMAGE_PROPORTIONAL,
							false
						);
						$listData[$list['ID']]['icon'] = $imageFile['src'];
					}
				}
				else
				{
					$listData[$list['ID']]['icon'] = '/bitrix/images/lists/default.png';
				}
			}
		}

		return $listData;
	}

	protected function checkListsPermission(?string $iBlockTypeId): bool
	{
		global $USER;
		$listPerm = CListPermissions::checkAccess(
			$USER,
			$iBlockTypeId
		);

		if($listPerm < 0)
		{
			return false;
		}
		elseif($listPerm <= CListPermissions::ACCESS_DENIED)
		{
			return false;
		}

		return true;
	}

	private function getFilterFields(): array
	{
		$systemPresets = \Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateFilter::getPresetList();

		return [
			[
				'id' => 'SYSTEM_PRESET',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_FILTER_FIELD_STATUS'),
				'type' => 'list',
				'items' => array_combine(
					array_column($systemPresets, 'id'),
					array_column($systemPresets, 'name')
				),
				'default' => true,
			],
			[
				'id' => 'TARGET_USER',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_FILTER_FIELD_TARGET_USER'),
				'type' => 'entity_selector',
				'default' => true,
				'params' => [
					'multiple' => 'N',
					'dialogOptions' => [
						'context' => 'filter',
						'entities' => [
							[
								'id' => 'user',
								'options' => [
									'intranetUsersOnly' => true,
									'inviteEmployeeLink' => false,
								],
							],
						],
					],
				],
			],
		];
	}

	private function getFilterPresets(): array
	{
		$systemPresets = \Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateFilter::getPresetList();
		$userPresets = [];

		foreach ($systemPresets as $preset)
		{
			$userPresets[$preset['id']] = [
				'name' => $preset['name'],
				'fields' => [
					'SYSTEM_PRESET' => $preset['id'],
				],
				'default' => $preset['default'] ?? false,
			];
		}

		return $userPresets;
	}
}
