<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateFilter;
use Bitrix\Lists\Api\Service\ServiceFactory\ProcessService;
use Bitrix\Lists\Api\Service\ServiceFactory\ServiceFactory;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use Bitrix\Bizproc\Api\Data\WorkflowStateService\WorkflowStateToGet;
use Bitrix\Bizproc\Api\Service\WorkflowStateService;
use Bitrix\Bizproc\Api\Response\WorkflowStateService\GetListResponse;
use Bitrix\Main\Web\Uri;

class BizprocUserProcesses
	extends CBitrixComponent
	implements \Bitrix\Main\Errorable, \Bitrix\Main\Engine\Contract\Controllerable
{
	protected const GRID_ID = 'bizproc_user_processes_v2';
	protected const NAVIGATION_ID = 'page';
	protected const FILTER_ID = self::GRID_ID . '_filter';

	private ErrorCollection $errorCollection;
	private \Bitrix\Main\UI\Filter\Options $filterOptions;

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
				'name' => Loc::getMessage('BIZPROC_USER_PROCESSES_GRID_COLUMN_MODIFIED_2'),
				'default' => true,
				'sort' => '',
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

		foreach ($workflows->getWorkflowStatesCollection() as $workflowState)
		{
			$workflowId = $workflowState->getId();
			$complexDocumentId = $workflowState->getComplexDocumentId();

			$workflowView = new \Bitrix\Bizproc\UI\WorkflowUserView($workflowState, $userId);

			$workflowViews[] = [
				'workflowId' => $workflowId,
				'userId' => $userId,
				'startedById' => (int)$workflowView->getStartedBy()?->getId(),
				'startedBy' => $this->formatName($workflowView->getStartedBy()),
				'taskProgress' => $workflowView->getFaces(),
				'name' => $workflowView->getName(),
				'description' => $workflowView->getDescription(),
				'typeName' => $workflowView->getTypeName(),
				'statusText' => $workflowView->getStatusText(),
				'modified' => $this->formatDate($workflows->getUserModified($workflowId)),
				'templateName' => $workflowState->getTemplate()?->fillName() ?? '',
				'workflowStarted' => $this->formatDate($workflowState->getStarted()),
				'document' => [
					'url' => $this->getDocumentUrl($complexDocumentId),
					'name' => $this->getDocumentName($complexDocumentId),
				],
				'task' => $workflowView->getTasks()[0] ?? null,
				'taskCnt' => count($workflowView->getTasks()),
				'overdueDate' => $this->formatDate($workflowView->getOverdueDate()),
				'commentCnt' => $workflowView->getCommentCounter(),
				'isCompleted' => $workflowView->getIsCompleted(),
				'workflowResult' => $workflowView->getIsCompleted()
					? ($workflowView->getWorkflowResult() ?? $this->getFakeResult($workflowView->getStartedBy()))
					: null
				,
			];
		}

		return $workflowViews;
	}

	private function formatDate(?DateTime $date): string
	{
		return \CBPViewHelper::formatDateTime($date);
	}

	private function formatName($user): string
	{
		if (is_null($user))
		{
			return '';
		}

		return CUser::FormatName(CSite::GetNameFormat(false), $user, bHTMLSpec: false);
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

		$lists = $this->getLists();
		if (is_array($lists))
		{
			$addButton = new \Bitrix\UI\Buttons\AddButton([
				'color' => \Bitrix\UI\Buttons\Color::SUCCESS,
				'text' => Loc::getMessage('BIZPROC_USER_PROCESSES_BUTTON_START_WORKFLOW'),
				'dataset' => [
					'toolbar-collapsed-icon' => \Bitrix\UI\Buttons\Icon::ADD,
				],
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

		$iBlockTypeId = ProcessService::getIBlockTypeId();
		$factory = ServiceFactory::getServiceByIBlockTypeId($iBlockTypeId, $this->getCurrentUserId());

		if (!$factory)
		{
			return null;
		}

		$showAllProcesses = method_exists($factory::class, 'getAddElementCatalog');

		$response = $showAllProcesses ? $factory->getAddElementCatalog() : $factory->getCatalog();
		if (!$response->isSuccess())
		{
			return null;
		}

		$siteDir = SITE_DIR;
		$path = rtrim($siteDir, '/');

		$requestIBlockId = (int)$this->request->get('iBlockId');

		$listData = [];
		foreach ($response->getCatalog() as $process)
		{
			$iBlockId = (int)$process['ID'];

			if (!$showAllProcesses && !CLists::getLiveFeed($iBlockId))
			{
				continue;
			}

			$data = [
				'name' => $process['NAME'],
				'iBlockTypeId' => $iBlockTypeId,
				'iBlockId' => $iBlockId,
				'icon' => '/bitrix/images/lists/default.png',
				'selected' => false,
			];

			if (!$showAllProcesses)
			{
				$url = new Uri($path . \Bitrix\Main\Config\Option::get('lists', 'livefeed_url'));
				$url->addParams([
					'livefeed' => 'y',
					'list_id' => $iBlockId,
					'element_id' => 0,
					'back_url' => $this->request->getRequestUri(),
				]);
				$data['url'] = $url;
			}

			if ($process['PICTURE'] > 0)
			{
				$imageFile = CFile::GetFileArray($process['PICTURE']);
				if($imageFile !== false)
				{
					$imageFile = CFile::ResizeImageGet(
						$imageFile,
						['width' => 36, 'height' => 30],
						BX_RESIZE_IMAGE_PROPORTIONAL,
						false
					);
					$data['icon'] = $imageFile['src'];
				}
			}

			if ($iBlockId === $requestIBlockId)
			{
				$data['selected'] = true;
			}

			$listData[] = $data;
		}

		return $listData;
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

	private function getFakeResult(?\Bitrix\Main\EO_User $startedBy): ?array
	{
		if (!$startedBy)
		{
			return null;
		}
		$userName = $this->formatName($startedBy);

		return [
			'text' => \CBPHelper::convertBBtoText(
				'[URL=/company/personal/user/' . $startedBy['ID'] . '/]' . $userName . '[/URL]',
			),
			'status' => \Bitrix\Bizproc\Result\RenderedResult::USER_RESULT,
		];
	}
}
