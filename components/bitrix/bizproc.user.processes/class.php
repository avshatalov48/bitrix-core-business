<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateFilter;
use Bitrix\Bizproc\Api\Service\TaskService;
use Bitrix\Lists\Api\Service\ServiceFactory\ProcessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Errorable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateToGet;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetListResponse;

class BizprocUserProcesses extends CBitrixComponent implements Errorable, Controllerable
{
	protected const GRID_ID = 'bizproc_user_processes_v2';
	protected const NAVIGATION_ID = 'page';
	protected const FILTER_ID = self::GRID_ID . '_filter';

	private ErrorCollection $errorCollection;
	private \Bitrix\Main\UI\Filter\Options $filterOptions;
	private \Bitrix\Main\Grid\Options $gridOptions;

	private int $targetUserId;

	private const WORKFLOW_FIELDS_TO_LOAD = [
		'STARTED_BY',
		'STATE_TITLE',
		'MODIFIED',
		'STARTED',
		'STARTED_BY',
		'TEMPLATE.NAME',
	];

	public function configureActions()
	{
		return [];
	}

	public function __construct($component = null)
	{
		parent::__construct($component);

		$this->errorCollection = new ErrorCollection();
		$this->filterOptions = new \Bitrix\Main\UI\Filter\Options(static::FILTER_ID);
		$this->gridOptions = new \Bitrix\Main\Grid\Options(static::GRID_ID);
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

	public function loadWorkflowsAction(array $ids): ?array
	{
		$this->init();
		if (!$this->hasErrors())
		{
			$this->checkRights();
		}

		if ($this->hasErrors())
		{
			return null;
		}

		$workflowIds = [];
		foreach ($ids as $id)
		{
			if (is_string($id) && $id)
			{
				$workflowIds[] = $id;
			}
		}

		if (!$workflowIds)
		{
			return null;
		}

		$request = (new WorkflowStateToGet())
			->setAdditionalSelectFields(static::WORKFLOW_FIELDS_TO_LOAD)
			->setFilterWorkflowIds($workflowIds)
			->setLimit($this->getPageNavigation()->getLimit())
		;
		$this->setFilterToRequest($request);

		$service = new WorkflowStateService();

		$response = $service->getList($request);

		if (!$response->isSuccess())
		{
			$this->addErrors($response->getErrors());

			return null;
		}

		return [
			'workflows' => $this->getWorkflowsViewData($response),
		];
	}

	public function delegateTasksAction(array $taskIds, int $toUserId): ?array
	{
		$this->init();
		if (!$this->hasErrors())
		{
			$this->checkRights();
		}

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

			$taskService = new TaskService(
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
		$this->fillGridInfo();

		if (!$this->hasErrors())
		{
			$this->checkRights();

			$this->addToolbar();
			$this->fillCounters();
			$this->fillGridData();
			$this->fillGridActions();
			$this->subscribeToPushes();

			$this->includeComponentTemplate();

			return;
		}

		$this->includeComponentTemplate('error');
	}

	private function subscribeToPushes(): void
	{
		$pageNavigation = $this->getPageNavigation();

		if (Loader::includeModule('pull') && $pageNavigation->getOffset() === 0)
		{
			\Bitrix\Bizproc\Integration\Push\WorkflowPush::subscribeUser($this->getCurrentUserId());
			$this->arResult['mustSubscribeToPushes'] = true;
		}
	}

	private function init(): void
	{
		$this->checkModules();
		$this->arResult['viewData'] = [];
	}

	private function checkModules(): void
	{
		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			$errorMessage = Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ERROR', ['#MODULE#' => 'bizproc']);
			$this->setError(new Error($errorMessage));
		}
	}

	private function checkRights(): void
	{
		$accessService = new \Bitrix\Bizproc\Api\Service\TaskAccessService($this->getCurrentUserId());

		$taskViewAccessResult = $accessService->checkViewTasks($this->getTargetUserId());
		if (!$taskViewAccessResult->isSuccess())
		{
			$this->targetUserId = $this->getCurrentUserId();
			$this->addErrors($taskViewAccessResult->getErrors());
		}
	}

	private function fillGridInfo(): void
	{
		$this->arResult['gridId'] = static::GRID_ID;
		$this->arResult['filterId'] = static::FILTER_ID;
		$this->arResult['navigationId'] = static::NAVIGATION_ID;
		$this->arResult['gridColumns'] = $this->getGridColumns();
		$this->arResult['pageNavigation'] = $this->getPageNavigation();
		$this->arResult['pageSizes'] = $this->getPageSizes();
	}

	private function fillCounters(): void
	{
		$userId = $this->getCurrentUserId();
		// time to verify
		\Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable::verifyUserUnread($userId);

		$task = (int)(CBPTaskService::getCounters($userId)['*'] ?? 0);
		$comment = \Bitrix\Bizproc\Workflow\Entity\WorkflowUserCommentTable::getCountUserUnread($userId);

		$this->arResult['counters'] = [
			'task' => $task,
			'comment' => $comment,
		];

		$userCounters = new \Bitrix\Bizproc\Workflow\WorkflowUserCounters($userId);
		$userCounters->setTask($task);
		$userCounters->setComment($comment);
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
				'sort' => '',
			],
			[
				'id' => 'TASK_DESCRIPTION',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_DESCRIPTION'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'PROCESS',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_PROCESS_DESC'),
				'default' => true,
				'sort' => '',
			],
			[
				'id' => 'MODIFIED',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_MODIFIED_2_MSGVER_1'),
				'default' => true,
				'sort' => 'MODIFIED',
				'width' => 192,
				//'align' => 'center',
				'first_order' => 'desc',
				'color' => \Bitrix\Main\Grid\Column\Color::BLUE,
			],
			[
				'id' => 'TASK_PROGRESS',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_TASK_FACES'),
				'default' => true,
				'sort' => '',
				'width' => 330,
				'resizeable' => false,
			],
			[
				'id' => 'TASK',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_RESULT'),
				'default' => true,
				'sort' => '',
				'width' => 200,
				'resizeable' => false,
				'prevent_default' => false,
			],
			[
				'id' => 'SUMMARY',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_SUMMARY'),
				'default' => true,
				'sort' => '',
				'width' => 100,
				'resizeable' => false,
			],
			[
				'id' => 'WORKFLOW_TEMPLATE_NAME',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_TEMPLATE_NAME'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'WORKFLOW_STATE',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_STATE'),
				'default' => false,
				'sort' => '',
			],
			[
				'id' => 'WORKFLOW_STARTED',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_WORKFLOW_STARTED'),
				'default' => false,
				'sort' => 'WORKFLOW_STARTED',
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
		$navParams = $this->gridOptions->GetNavParams();

		$pageNavigation = new \Bitrix\Main\UI\PageNavigation(static::NAVIGATION_ID);
		$pageNavigation->setPageSize($navParams['nPageSize'])->initFromUri();

		$currentPage = $this->request->getQuery(static::NAVIGATION_ID);
		if (is_numeric($currentPage))
		{
			$pageNavigation->setCurrentPage((int)$currentPage);
		}

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

		$workflowsResponse = $this->fetchWorkflows($pageNav->getLimit(), $pageNav->getOffset());

		$workflowViews = [];
		if ($workflowsResponse->isSuccess())
		{
			$workflowViews = $this->getWorkflowsViewData($workflowsResponse);
			$pageNav->setRecordCount($workflowsResponse->getTotalCount());
		}
		else
		{
			$this->addErrors($workflowsResponse->getErrors());
		}

		$this->arResult['viewData']['userId'] = $this->getCurrentUserId();
		$this->arResult['viewData']['targetUserId'] = $this->getTargetUserId();
		$this->arResult['viewData']['workflows'] = $workflowViews;
	}

	private function setSortingToRequest(WorkflowStateToGet $workflowsRequest): void
	{
		$defaultSorting = ['MODIFIED' => 'DESC'];

		$sortingPayload = $this->gridOptions->getSorting(['sort' => $defaultSorting]);

		$workflowsRequest->setOrder($sortingPayload['sort'] ?? $defaultSorting);
	}

	private function fetchWorkflows(int $limit, int $offset, bool $shouldCountTotal = true): GetListResponse
	{
		$workflowStateService = new WorkflowStateService();

		$workflowsRequest = (new WorkflowStateToGet())
			->setAdditionalSelectFields(static::WORKFLOW_FIELDS_TO_LOAD)
			->setLimit($limit)
			->setOffset($offset)
		;
		if ($shouldCountTotal)
		{
			$workflowsRequest->countTotal();
		}

		$this->setFilterToRequest($workflowsRequest);

		$this->setSortingToRequest($workflowsRequest);

		return $workflowStateService->getList($workflowsRequest);
	}

	private function setFilterToRequest(WorkflowStateToGet $workflowsRequest): void
	{
		$workflowsRequest->setFilterUserId($this->getTargetUserId());
		$userFilter = $this->filterOptions->getFilter($this->getFilterFields());
		if (empty($userFilter) && $this->filterOptions->getCurrentFilterId() === 'default_filter')
		{
			$userFilter['SYSTEM_PRESET'] = WorkflowStateFilter::PRESET_DEFAULT;
		}

		if (isset($userFilter['SYSTEM_PRESET']))
		{
			$workflowsRequest->setFilterPresetId($userFilter['SYSTEM_PRESET']);
		}

		$additionalFilter = [];
		foreach ($this->getFilterMap() as $userKey => $filterKey)
		{
			if (isset($userFilter[$userKey]))
			{
				$additionalFilter[$filterKey] = $userFilter[$userKey];
			}
		}

		if ($additionalFilter)
		{
			$workflowsRequest->setFilter($additionalFilter);
		}

		if (isset($userFilter['FIND']) && is_string($userFilter['FIND']))
		{
			$workflowsRequest->setFilterSearchQuery($userFilter['FIND']);
		}
	}

	private function getFilterMap(): array
	{
		return [
			'MODIFIED_from' => '>=MODIFIED',
			'MODIFIED_to' => '<=MODIFIED',
			'STARTED_from' => '>=FILTER.STARTED',
			'STARTED_to' => '<=FILTER.STARTED',
			'MODULE_ID' => '=FILTER.MODULE_ID',
			'WORKFLOW_TEMPLATE_ID' => '=FILTER.TEMPLATE_ID',
		];
	}

	private function getWorkflowsViewData(GetListResponse $workflows): array
	{
		$workflowViews = [];
		$userId = $this->getTargetUserId();

		$allColumns = array_column($this->getGridColumns(), 'id');
		$defaultColumns = array_column(
			array_filter($this->getGridColumns(), static fn($column) => $column['default']),
			'id'
		);
		$usedColumns = $this->gridOptions->getUsedColumns($defaultColumns);

		foreach ($workflows->getWorkflowStatesCollection() as $workflowState)
		{
			$workflowId = $workflowState->getId();

			$workflowView = new \Bitrix\Bizproc\UI\WorkflowUserGridView($workflowState, $userId);
			$workflowView
				->setUsedColumns(array_intersect($usedColumns, $allColumns))
				->setModified($workflows->getUserModified($workflowId))
			;

			$workflowViews[] = $workflowView->toArray();
		}

		return $workflowViews;
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

	private function getTargetUserId(): int
	{
		return $this->targetUserId
			?? (int)($this->filterOptions->getFilter()['TARGET_USER']
			?? $this->getCurrentUserId())
		;
	}

	private function getCurrentUserId(): int
	{
		return \Bitrix\Main\Engine\CurrentUser::get()->getId();
	}

	private function addToolbar(): void
	{
		$filterParams = [
			'FILTER_ID' => static::FILTER_ID,
			'GRID_ID' => static::GRID_ID,
			'FILTER' => $this->getFilterFields(),
			'FILTER_PRESETS' => $this->getFilterPresets(),
			'ENABLE_LABEL' => true,
			'RESET_TO_DEFAULT_MODE' => true,
			'THEME' => \Bitrix\Main\UI\Filter\Theme::MUTED,
		];

		\Bitrix\UI\Toolbar\Facade\Toolbar::addFilter($filterParams);
		\Bitrix\UI\Toolbar\Facade\Toolbar::addFavoriteStar();

		$listInfo = $this->getListInfo();
		if (is_array($listInfo))
		{
			$addButton = new \Bitrix\UI\Buttons\AddButton([
				'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
				'text' => Loc::getMessage('BIZPROC_USER_PROCESSES_BUTTON_START_WORKFLOW'),
				'dataset' => [
					'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
				],
				'click' => new \Bitrix\UI\Buttons\JsCode(
					'BX.Bizproc.Component.UserProcesses.Instance.clickStartWorkflowButton();'
				),
			]);

			$this->arResult['viewData']['startWorkflowButtonId'] = static::GRID_ID . '-filter-start-workflow-button';
			$this->arResult['viewData']['listParams'] = $listInfo;
			$addButton->addAttribute('id', $this->arResult['viewData']['startWorkflowButtonId']);

			\Bitrix\UI\Toolbar\Facade\Toolbar::addButton(
				$addButton,
				\Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE,
			);
		}
	}

	private function getListInfo(): ?array
	{
		if (!\Bitrix\Main\Loader::includeModule('lists'))
		{
			return null;
		}

		$iBlockTypeId = ProcessService::getIBlockTypeId();
		$factory = ServiceFactory::getServiceByIBlockTypeId($iBlockTypeId, $this->getCurrentUserId());

		if (!$factory)
		{
			return null;
		}

		$listInfo = [];

		$requestIBlockId = (int)$this->request->get('iBlockId');

		if ($requestIBlockId > 0 )
		{
			$filter = new \Bitrix\Lists\Api\Data\IBlockService\IBlockListFilter();
			$filter->setField('ID', $requestIBlockId, '=');

			$response = $factory->getAddElementCatalog($filter);
			if (!$response->isSuccess())
			{
				return $listInfo;
			}

			[$process] = $response->getCatalog();
			if ($process)
			{
				$iBlockId = (int)$process['ID'];
				$listInfo = [
					'name' => $process['NAME'],
					'iBlockTypeId' => $iBlockTypeId,
					'iBlockId' => $iBlockId,
				];
			}
		}

		return $listInfo;
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
			[
				'id' => 'MODIFIED',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_FILTER_FIELD_MODIFIED'),
				'type' => 'date',
			],
			[
				'id' => 'STARTED',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_FILTER_FIELD_STARTED'),
				'type' => 'date',
			],
			[
				'id' => 'MODULE_ID',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_FILTER_FIELD_MODULE_ID'),
				'type' => 'list',
				'items' => [
					'lists' => Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ID_LISTS'),
					'crm' => Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ID_CRM'),
					'disk' => Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ID_DISK'),
					'rpa' => Loc::getMessage('BIZPROC_USER_PROCESSES_MODULE_ID_RPA'),
				],
			],
			[
				'id' => 'WORKFLOW_TEMPLATE_ID',
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_FILTER_FIELD_TEMPLATE_ID'),
				'type' => 'entity_selector',
				'params' => [
					'multiple' => 'N',
					'dialogOptions' => [
						'context' => 'bp-filter',
						'entities' => [
							['id' => 'bizproc-template'],
						],
						'multiple' => 'N',
						'dropdownMode' => true,
						'hideOnSelect' => true,
						'hideOnDeselect' => false,
						'clearSearchOnSelect' => true,
						'showAvatars' => false,
						'compactView' => true,
					],
				],
			],
		];
	}

	private function getFilterPresets(): array
	{
		$systemPresets = WorkflowStateFilter::getPresetList();
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
