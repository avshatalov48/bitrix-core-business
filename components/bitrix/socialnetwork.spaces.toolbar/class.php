<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Disk;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentTypeException;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Socialnetwork;
use Bitrix\Socialnetwork\Component\WorkgroupUserList;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Helper\AvatarManager;
use Bitrix\Socialnetwork\Helper\UI\Discussions\DiscussionsFilterSpaces;
use Bitrix\Socialnetwork\Space\Toolbar\Composition;
use Bitrix\Socialnetwork\Space\Toolbar\Switcher\Mode\SmartTracking;
use Bitrix\Tasks\Helper\Filter;
use Bitrix\Tasks\Helper\FilterRegistry;
use Bitrix\Tasks\Helper\Grid;
use Bitrix\Tasks\Integration\Bizproc\Automation\Factory;
use Bitrix\Tasks\Internals\Counter;
use Bitrix\Tasks\Internals\Counter\CounterDictionary;
use Bitrix\Tasks\Internals\Routes\RouteDictionary;
use Bitrix\Tasks\Scrum;
use Bitrix\Tasks\Scrum\Service\KanbanService;
use Bitrix\Tasks\Scrum\Service\TaskService;
use Bitrix\Tasks\Scrum\Service\SprintService;
use Bitrix\Tasks\Slider\Path\PathMaker;
use Bitrix\Tasks\Slider\Path\TaskPathMaker;
use Bitrix\Tasks\Slider\Path\TemplatePathMaker;
use Bitrix\Tasks\Ui;
use Bitrix\Tasks\Access\ActionDictionary;
use Bitrix\Tasks\Access\TaskAccessController;
use Bitrix\Tasks\Util\Restriction\Bitrix24Restriction\Limit\TaskLimit;

class SpacesToolbarComponent extends CBitrixComponent implements Controllerable, Errorable
{
	private const ORDER_OPTION = 'order_new_task_v2';

	private Application $application;
	private ErrorCollection $errorCollection;
	private string $componentTemplate;
	private DiscussionsFilterSpaces $discussionsFilter;
	private int $userId;
	private int $spaceId;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->application = Application::getInstance();
		$this->errorCollection = new ErrorCollection();
	}

	public function configureActions(): array
	{
		return [];
	}

	public function getErrors(): array
	{
		return $this->errorCollection->toArray();
	}

	public function getErrorByCode($code): ?Error
	{
		return $this->errorCollection->getErrorByCode($code);
	}

	public function onIncludeComponentLang(): void
	{
		$this->includeComponentLang(basename(__FILE__));

		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($arParams): array
	{
		$arParams['GROUP_ID'] = (is_numeric($arParams['GROUP_ID'] ?? null) ? (int) $arParams['GROUP_ID'] : 0);
		$arParams['PAGE_ID'] = (is_string($arParams['PAGE_ID'] ?? null) ? $arParams['PAGE_ID'] : 'discussions');

		$arParams['STORAGE'] = (
			(($arParams['STORAGE'] ?? null) instanceof Disk\Storage)
				? $arParams['STORAGE']
				: null
		);

		$arParams['VIEW_MODE_LIST'] = (
			is_array($arParams['VIEW_MODE_LIST'] ?? null)
				? $arParams['VIEW_MODE_LIST']
				: []
		);

		$arParams['IS_TRASH_MODE'] = ($arParams['IS_TRASH_MODE'] ?? null) === true;

		$arParams['PATH_TO_USER_FILES_VOLUME'] = (
			is_string($arParams['PATH_TO_USER_FILES_VOLUME'] ?? null)
				? $arParams['PATH_TO_USER_FILES_VOLUME']
				: ''
		);
		$arParams['PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN'] = (
			is_string($arParams['PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN'] ?? null)
				? $arParams['PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN']
				: ''
		);

		return $arParams;
	}

	public function executeComponent(): void
	{
		$this
			->init()
			->preparePageData()
			->prepareOwnerData()
			->prepareComponentData()
			->prepareDiscussionsResult()
			->prepareTasksCountersResult()
			->prepareFilesResult()
			->prepareTasksResult()
			->resetFilter()
			->prepareDiscussionsFilterData()
			->prepareTasksGridData()
			->prepareTasksFilterData()
			->includeSelectedComponentTemplate();
	}

	public function getTasksCountersAction(int $groupId): ?array
	{
		if (
			!Loader::includeModule('socialnetwork')
			|| !Loader::includeModule('tasks')
		)
		{
			$this->errorCollection->setError(
				new Error(Loc::getMessage('SN_SPACES_SCRUM_ACTION_INCLUDE_MODULE_ERROR'))
			);

			return null;
		}

		return $this->getTasksCounters(
			Helper\User::getCurrentUserId(),
			$groupId,
		);
	}

	private function getScrumViewList(): array
	{
		$viewList = [];

		$viewList[] = [
			'id' => 1,
			'key' => 'scrum-plan',
			'title' => Loc::getMessage('SN_SPACES_SCRUM_VIEW_PLAN'),
			'urlParam' => 'tab',
			'urlValue' => 'plan',
		];
		$viewList[] = [
			'id' => 2,
			'key' => 'scrum-active',
			'title' => Loc::getMessage('SN_SPACES_SCRUM_VIEW_ACTIVE_SPRINT'),
			'urlParam' => 'tab',
			'urlValue' => 'active_sprint',
		];
		$viewList[] = [
			'id' => 3,
			'key' => 'scrum-completed',
			'title' => Loc::getMessage('SN_SPACES_SCRUM_VIEW_COMPLETED_SPRINT'),
			'urlParam' => 'tab',
			'urlValue' => 'completed_sprint',
		];

		return $viewList;
	}

	private function getTasksViewList(array $inputViewList): array
	{
		$listState = Ui\Filter\Task::getListStateInstance();
		if (!$listState)
		{
			return [];
		}

		$viewState = $listState->getState();

		$currentViewMode = $this->arResult['viewMode'];
		$viewList = [];

		if ($inputViewList)
		{
			foreach ($inputViewList as $mode)
			{
				if (array_key_exists($mode, $viewState['VIEWS']))
				{
					$view = $viewState['VIEWS'][$mode];

					$viewList[] = [
						'id' => $view['ID'],
						'title' => $view['SHORT_TITLE'],
						'selected' => $this->getTasksViewMode($mode) === $currentViewMode,
						'urlParam' => 'F_STATE',
						'urlValue' => 'sV' . CTaskListState::encodeState($view['ID']),
					];
				}
			}
		}
		else
		{
			foreach ($viewState['VIEWS'] as $viewKey => $view)
			{
				if ($this->arResult['pageType'] === 'user' && $viewKey === 'VIEW_MODE_KANBAN')
				{
					continue;
				}

				$viewList[] = [
					'id' => $view['ID'],
					'key' => $this->convertToKebabCase($viewKey),
					'title' => $view['SHORT_TITLE'],
					'selected' => $this->getTasksViewMode($viewKey) === $currentViewMode,
					'urlParam' => 'F_STATE',
					'urlValue' => 'sV' . CTaskListState::encodeState($view['ID']),
				];
			}
		}

		return $viewList;
	}

	private function getCurrentTasksViewState(int $userId): ?string
	{
		Ui\Filter\Task::setUserId($userId);

		$viewState = Ui\Filter\Task::listStateInit()?->getState()['VIEW_SELECTED']['CODENAME'];

		if ($viewState === 'VIEW_MODE_GANTT')
		{
			return FilterRegistry::FILTER_GANTT;
		}

		return FilterRegistry::FILTER_GRID;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws ArgumentTypeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function prepareFilesResult(): static
	{
		if ($this->arResult['pageId'] !== 'files')
		{
			return $this;
		}

		$storage = $this->arParams['STORAGE'];
		if (is_null($storage))
		{
			throw new ArgumentTypeException('No disk storage');
		}

		$proxyType = $storage->getProxyType();

		$isTrashMode = $this->arParams['IS_TRASH_MODE'];
		$pathToUserFilesVolume = $this->arParams['PATH_TO_USER_FILES_VOLUME'];
		$userId = Socialnetwork\Helper\User::getCurrentUserId();

		$this->arResult['pathToUserFilesVolume'] = $this->buildPathToFilesVolume(
			$storage,
			$pathToUserFilesVolume,
			$userId
		);
		$this->arResult['pathToFilesBizprocWorkflowAdmin'] = $this->arParams[
			'PATH_TO_GROUP_FILES_BIZPROC_WORKFLOW_ADMIN'
		];
		$this->arResult['networkDriveLink'] = Disk\Driver::getInstance()->getUrlManager()->getHostUrl()
			. $proxyType->getBaseUrlFolderList()
		;

		$this->arResult['storageId'] = $storage->getId();
		$this->arResult['documentHandlers'] = $this->getDocumentHandlersForCreatingFile();
		$this->arResult['permissions'] = $this->getFilesPermissions(
			$storage,
			$this->isShowFilesBizproc($storage, $isTrashMode),
			$this->arResult['pathToUserFilesVolume'] !== ''
		);
		$this->arResult['featureRestrictionMap'] = Disk\Integration\Bitrix24Manager::getFeatureRestrictionMap();
		$this->arResult['listAvailableFeatures'] = [
			'disk_folder_sharing' => Disk\Integration\Bitrix24Manager::isFeatureEnabled('disk_folder_sharing'),
		];

		return $this;
	}

	private function prepareTasksResult(): static
	{
		Loader::includeModule('tasks');

		if ($this->arResult['pageType'] === 'user')
		{
			if ($this->isTasksView())
			{
				$this->prepareUserTasksResult();
			}
		}
		else
		{
			if ($this->isTasksView())
			{
				$group = Socialnetwork\Item\Workgroup::getById($this->arResult['groupId']);
				$isScrum = $group && $group->isScrumProject();
				if ($isScrum)
				{
					$this->componentTemplate = $this->componentTemplate . '_scrum';
				}

				$this->prepareGroupTasksResult($isScrum);
			}

			if ($this->arResult['pageId'] === 'users')
			{
				$this->prepareGroupUsersResult();
			}
		}

		$this->arResult['pathToUserSpaceTasks'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_USER_TASKS']
		);

		$this->prepareTemplateListUrl();

		return $this;
	}

	private function prepareComponentData(): static
	{
		$this->arResult['viewList'] = [];
		$this->discussionsFilter = new DiscussionsFilterSpaces($this->arResult['groupId']);
		return $this;
	}

	private function resetFilter(): static
	{
		$filterId = $this->discussionsFilter->getContainerId();
		(new Options($filterId))->reset();
		return $this;
	}

	private function prepareDiscussionsFilterData(): static
	{
		if (!$this->isDiscussionsView())
		{
			return $this;
		}
		$this->arResult['FILTER'] = $this->discussionsFilter->getFilter();
		$this->arResult['FILTER_PRESETS'] = $this->discussionsFilter->getPresets(
			$this->discussionsFilter->getParamsForPresets()
		);
		$this->arResult['FILTER_ID'] = $this->discussionsFilter->getContainerId();

		return $this;
	}

	private function prepareTasksGridData(): static
	{
		if (!$this->isTasksView())
		{
			return $this;
		}

		$this->arResult['GRID_ID'] = Grid::getInstance($this->arResult['userId'], $this->arResult['groupId'])
			->setContext($this->arResult['taskCurrentViewMode'])
			->setScope(Socialnetwork\Livefeed\Context\Context::SPACES)
			->getId();

		return $this;
	}

	private function prepareTasksFilterData(): static
	{
		if (!$this->isTasksView())
		{
			return $this;
		}

		$filterInstance = Filter::getInstance($this->arResult['userId'], $this->arResult['groupId'])
			->setContext($this->arResult['taskCurrentViewMode']);
		$this->arResult['FILTER'] = $filterInstance->getFilters();
		$this->arResult['FILTER_PRESETS'] = $filterInstance->getAllPresets();
		$this->arResult['FILTER_ID'] = $filterInstance->getId();

		return $this;
	}

	private function includeSelectedComponentTemplate(): void
	{
		$membersManager = new Socialnetwork\Space\MembersManager();
		if (
			$this->arResult['pageType'] === 'group'
			&& !$membersManager->isUserMember($this->arResult['userId'], $this->arResult['groupId'])
		)
		{
			$this->componentTemplate = 'empty';
		}

		parent::includeComponentTemplate($this->componentTemplate);
	}

	private function getDocumentHandlersForCreatingFile(): array
	{
		$handlers = [];
		foreach ($this->listCloudHandlersForCreatingFile() as $handler)
		{
			$handlers[] = [
				'code' => $handler::getCode(),
				'name' => $handler::getName(),
			];
		}

		return array_merge($handlers, [
			[
				'code' => Disk\Document\LocalDocumentController::getCode(),
				'name' => Disk\Document\LocalDocumentController::getName(),
			]
		]);
	}

	/**
	 * @return Disk\Document\DocumentHandler[]
	 */
	private function listCloudHandlersForCreatingFile()
	{
		if (!Disk\Configuration::canCreateFileByCloud())
		{
			return [];
		}

		$list = [];
		$documentHandlersManager = Disk\Driver::getInstance()->getDocumentHandlersManager();
		foreach ($documentHandlersManager->getHandlers() as $handler)
		{
			if ($handler instanceof Disk\Document\Contract\FileCreatable)
			{
				$list[] = $handler;
			}
		}

		return $list;
	}

	private function getFilesPermissions(
		Disk\Storage $storage,
		bool $isShowBizproc,
		bool $canClean
	): array
	{
		$securityContext = $storage->getCurrentUserSecurityContext();
		$proxyType = $storage->getProxyType();

		$canChangeBizprocSettingsExceptUser = !($proxyType instanceof Disk\ProxyType\User);

		return [
			'canAdd' => $storage->canAdd($securityContext),
			'canChangeRights' => $storage->canChangeRights($securityContext),
			'canChangeBizprocSettings' => (
				$storage->canChangeSettings($securityContext)
				&& $canChangeBizprocSettingsExceptUser
				&& Disk\Integration\BizProcManager::isAvailable()
			),
			'canChangeBizproc' => (
				$storage->canCreateWorkflow($securityContext)
				&& $canChangeBizprocSettingsExceptUser
				&& $isShowBizproc
			),
			'canCleanFiles' => $canClean,
		];
	}

	private function isShowFilesBizproc(Disk\Storage $storage, bool $isTrashMode): bool
	{
		if ($isTrashMode)
		{
			return false;
		}

		return $storage->isEnabledBizProc() && Disk\Integration\BizProcManager::isAvailable();
	}

	private function buildPathToFilesVolume(Disk\Storage $storage, string $path, int $userId): string
	{
		$proxyType = $storage->getProxyType();
		$isUserStorage = $proxyType instanceof Disk\ProxyType\User;
		if ($isUserStorage)
		{
			return CComponentEngine::makePathFromTemplate(
				$path,
				[
					'ACTION' => '',
					'user_id' => $userId,
				]
			);
		}

		return '';
	}

	private function getTaskUrl(string $view, string $context): string
	{
		$path = new TaskPathMaker(
			0,
			$view,
			$context === PathMaker::GROUP_CONTEXT ? $this->spaceId : $this->userId,
			$context
		);

		$viewMode = Ui\Filter\Task::getListStateInstance()->getViewMode();
		$viewCode = CTaskListState::resolveConstantCodename($viewMode, CTaskListState::VIEW_MODE_LIST);
		$scope = $this->getTasksScope($viewCode);

		!empty($scope) && $path->addQueryParam('SCOPE', $scope);
		$this->isGroupSpace() && $path->addQueryParam('GROUP_ID', $this->spaceId);

		return $path->makeEntityPath();
	}

	private function prepareTemplateListUrl(): static
	{
		$this->arResult['pathToTemplateList'] =
			(new TemplatePathMaker())->setOwnerId($this->userId)->makeEntitiesListPath();

		return $this;
	}

	private function getTasksScope(string $viewCode): string
	{
		$scope = '';

		switch ($viewCode)
		{
			case 'VIEW_MODE_LIST':
				$scope = Ui\ScopeDictionary::SCOPE_TASKS_GRID;
				break;
			case 'VIEW_MODE_KANBAN':
				$scope = Ui\ScopeDictionary::SCOPE_TASKS_KANBAN;
				break;
			case 'VIEW_MODE_TIMELINE':
				$scope = Ui\ScopeDictionary::SCOPE_TASKS_KANBAN_TIMELINE;
				break;
			case 'VIEW_MODE_PLAN':
				$scope = Ui\ScopeDictionary::SCOPE_TASKS_KANBAN_PERSONAL;
				break;
			case 'VIEW_MODE_CALENDAR':
				$scope = Ui\ScopeDictionary::SCOPE_TASKS_CALENDAR;
				break;
			case 'VIEW_MODE_GANTT':
				$scope = Ui\ScopeDictionary::SCOPE_TASKS_GANTT;
				break;
		}

		return $scope;
	}

	private function prepareTasksCountersResult(): static
	{
		if ($this->arResult['pageId'] !== 'tasks')
		{
			return $this;
		}

		$this->arResult['counters'] = $this->getTasksCounters(
			$this->arResult['userId'],
			$this->arResult['groupId'],
		);

		return $this;
	}

	private function prepareUserTasksResult(): void
	{
		$userId = $this->arResult['userId'];

		$this->arResult['viewMode'] = $this->getUserTasksViewMode($userId);
		$this->arResult['viewList'] = $this->getTasksViewList($this->arParams['VIEW_MODE_LIST']);
		$this->arResult['taskCurrentViewMode'] = $this->getCurrentTasksViewState($userId);

		$this->arResult['pathToAddTask'] = $this->getTaskUrl(
			PathMaker::EDIT_ACTION,
			PathMaker::PERSONAL_CONTEXT,
		);
		$this->arResult['pathToViewTask'] = $this->getTaskUrl(
			PathMaker::DEFAULT_ACTION,
			PathMaker::PERSONAL_CONTEXT,
		);

		$pathToTasks = $this->getPathToTasks();
		$this->arResult['pathToTasks'] = $pathToTasks;

		$this->arResult['order'] = $this->getTasksOrder();
		$this->arResult['shouldSubtasksBeGrouped'] = $this->shouldSubtasksBeGrouped($userId);

		$this->arResult['sortFields'] = \Bitrix\Tasks\Ui\Controls\Column::getFieldsForSorting();

		$gridId = $this->getGridId($userId);
		$this->arResult['gridId'] = $gridId;
		$this->arResult['taskSort'] = $this->getTasksSort($userId, 0, $gridId);

		$this->arResult['syncScript'] = \CIntranetUtils::GetStsSyncURL(['LINK_URL' => $pathToTasks], 'tasks');

		$this->arResult['permissions'] = $this->getTasksPermissions($userId);
	}

	private function prepareGroupTasksResult(bool $isScrum): void
	{
		$userId = $this->arResult['userId'];
		$groupId = $this->arResult['groupId'];

		$this->arResult['pathToGroupTasks'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_TASKS'],
			['group_id' => $groupId]
		);

		if ($isScrum)
		{
			$request = Context::getCurrent()->getRequest();
			$this->saveScrumActiveView($request->get('tab'), $groupId);

			$this->arResult['viewMode'] = $this->getGroupTasksViewMode($groupId, true);

			$this->arResult['viewList'] = $this->getScrumViewList();
			$this->arResult['displayPriority'] = $this->getDisplayPriorityState($groupId);
			$this->arResult['isShortView'] = $this->getShortViewState($groupId);

			$sprintService = new SprintService($userId);

			$this->arResult['activeSprintId'] = 0;
			$this->arResult['currentCompletedSprint'] = [];
			$this->arResult['taskLimitExceeded'] = false;
			$this->arResult['canUseAutomation'] = false;
			$this->arResult['canCompleteSprint'] = false;
			if ($this->arResult['viewMode'] === 'active')
			{
				$sprint = $sprintService->getActiveSprintByGroupId($groupId);

				$this->arResult['activeSprintId'] = ($sprintService->getErrors() ? 0 : $sprint->getId());

				$this->arResult['taskLimitExceeded'] = TaskLimit::isLimitExceeded();
				$this->arResult['canUseAutomation'] = Factory::canUseAutomation();

				$this->arResult['canCompleteSprint'] = $sprintService->canCompleteSprint($userId, $groupId);
			}

			if ($this->arResult['viewMode'] === 'complete')
			{
				$completedSprint = $sprintService->getLastCompletedSprint($groupId);

				$dateStart = $completedSprint->getDateStart()->format(Bitrix\Main\Type\Date::getFormat());
				$dateEnd = $completedSprint->getDateEnd()->format(Bitrix\Main\Type\Date::getFormat());
				$this->arResult['currentCompletedSprint'] = [
					'id' => $completedSprint->getId(),
					'selectorLabel' => $dateStart . ' - ' . $dateEnd,
				];
			}
		}
		else
		{
			$this->arResult['viewMode'] = $this->getGroupTasksViewMode($groupId, false);

			$this->arResult['viewList'] = $this->getTasksViewList($this->arParams['VIEW_MODE_LIST']);
		}

		$this->arResult['taskCurrentViewMode'] = $this->getCurrentTasksViewState($userId);

		$this->arResult['pathToAddTask'] = $this->getTaskUrl(
			PathMaker::EDIT_ACTION,
			PathMaker::GROUP_CONTEXT,
		);

		$this->arResult['pathToGroupTasksTask'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_TASKS_TASK'],
			['group_id' => $groupId]
		);
		$this->arResult['pathToScrumBurnDown'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_SCRUM_BURN_DOWN'],
			['group_id' => $groupId]
		);

		$pathToTasks = $this->getPathToTasks();
		$this->arResult['pathToTasks'] = $pathToTasks;

		$this->arResult['order'] = $this->getGroupTasksOrder($groupId);
		$this->arResult['shouldSubtasksBeGrouped'] = $this->shouldSubtasksBeGrouped($userId);

		$this->arResult['sortFields'] = \Bitrix\Tasks\Ui\Controls\Column::getFieldsForSorting();

		$gridId = $this->getGridId($userId, $groupId);
		$this->arResult['gridId'] = $gridId;
		$this->arResult['taskSort'] = $this->getTasksSort($userId, $groupId, $gridId);

		$this->arResult['syncScript'] = \CIntranetUtils::GetStsSyncURL(['LINK_URL' => $pathToTasks], 'tasks');

		$this->arResult['permissions'] = $this->getTasksPermissions($userId);
	}

	private function getPathToTasks(): string
	{
		return str_replace('#user_id#', $this->userId, RouteDictionary::PATH_TO_USER_TASKS_LIST);
	}

	private function getGroupTasksOrder(int $groupId): string
	{
		if ($this->arResult['viewMode'] !== 'plan')
		{
			return (new KanbanService())->getKanbanSortValue($groupId);
		}

		return $this->getTasksOrder();
	}

	private function getTasksOrder(): string
	{
		return \CUserOptions::getOption('tasks', static::ORDER_OPTION, 'actual');
	}

	private function getTasksSort(int $userId, int $groupId = 0, ?string $gridId = null): array
	{
		$sort = Grid::getInstance($userId, $groupId, $gridId)->getOptions()->GetSorting($this->getDefaultSorting())['sort'];
		reset($sort);

		return [
			'field' => key($sort),
			'direction' => current($sort),
		];
	}

	private function getDefaultSorting(): array
	{
		return [
			'sort' => ['ACTIVITY_DATE' => 'desc'],
		];
	}

	private function getGridId(int $userId, int $groupId = 0): string
	{
		$context = $this->arResult['viewMode'] === 'gantt' ? 'gantt' : 'grid';
		$scope = Socialnetwork\Livefeed\Context\Context::SPACES;

		return Grid::getInstance($userId, $groupId)
			->setContext($context)
			->setScope($scope)
			->getId();
	}

	private function shouldSubtasksBeGrouped(int $userId): bool
	{
		$instance = CTaskListState::getInstance($userId);
		$state = $instance->getState();

		return $state['SUBMODES']['VIEW_SUBMODE_WITH_SUBTASKS']['SELECTED'] === 'Y';
	}

	private function getTasksPermissions(int $userId): array
	{
		return [
			'import' => TaskAccessController::can($userId, ActionDictionary::ACTION_TASK_IMPORT),
			'export' => TaskAccessController::can($userId, ActionDictionary::ACTION_TASK_EXPORT),
		];
	}

	private function prepareGroupUsersResult(): void
	{
		$groupPerms = Helper\Workgroup::getPermissions([
			'groupId' => $this->arResult['groupId'],
		]);

		$this->arResult = WorkgroupUserList::prepareFilterResult(
			$this->arResult,
			$groupPerms,
			'MEMBERS'
		);

		$this->arResult['pathToInvite'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_INVITE'],
			['group_id' => $this->arResult['groupId']]
		);
	}

	private function convertToKebabCase(string $string): string
	{
		$string = preg_replace('/[\s.]+/', '_', $string);

		$string = preg_replace('/[^0-9a-zA-Z_\-]/', '-', $string);

		$string = mb_strtolower(preg_replace('/[A-Z]+/', '-\0', $string));
		$string = trim($string, '-_');

		return preg_replace('/[_\-][_\-]+/', '-', $string);
	}

	private function prepareDiscussionsResult(): static
	{
		if ($this->arResult['pageId'] !== 'discussions')
		{
			return $this;
		}

		$this->arResult['spaceName'] = Loc::getMessage('SN_SPACES_DISCUSSIONS_COMPOSITION_GENERAL_SPACES_NAME');

		if ($this->arResult['pageType'] === 'group')
		{
			$group = Socialnetwork\Item\Workgroup::getById($this->arResult['groupId']);
			if ($group)
			{
				$groupFields = $group->getFields();

				$this->arResult['spaceName'] = $groupFields['NAME'];

				$avatarManager = new AvatarManager();

				$imageId = (int) $groupFields['IMAGE_ID'];
				if ($imageId)
				{
					$this->arResult['spaceLogo'] = $avatarManager->getImageAvatar($imageId)->toArray();
				}
				else
				{
					$this->arResult['spaceLogo'] = $avatarManager
						->getIconAvatar($groupFields['AVATAR_TYPE'] ?? '')
						->toArray()
					;
				}
			}
		}

		if ($this->arResult['pageType'] === 'user')
		{
			$this->arResult['pathToFilesPage'] = \CComponentEngine::makePathFromTemplate(
				$this->arParams['PATH_TO_USER_FILES'],
				[
					'user_id' => $this->userId,
					'PATH' => '',
				]
			);
		}
		if ($this->arResult['pageType'] === 'group')
		{
			$this->arResult['pathToFilesPage'] = \CComponentEngine::makePathFromTemplate(
				$this->arParams['PATH_TO_GROUP_FILES'],
				[
					'group_id' => $this->spaceId,
					'PATH' => '',
				]
			);
		}

		$this->arResult['storage'] = null;
		$this->arResult['folder'] = null;
		$storage = $this->getStorageByPageType($this->arResult['pageType']);
		if (!is_null($storage))
		{
			$folder = Disk\Folder::loadById($storage->getRootObjectId());

			$this->arResult['storage'] = $storage;
			$this->arResult['folder'] = $folder;
		}

		return $this
			->prepareSmartTrackingMode()
			->prepareCompositionData();
	}

	/**
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	private function getStorageByPageType(string $pageType): ?Disk\Storage
	{
		$diskDriver = Disk\Driver::getInstance();

		if ($pageType === 'user')
		{
			return $diskDriver->getStorageByUserId($this->arResult['userId']);
		}

		return $diskDriver->getStorageByGroupId($this->arResult['groupId']);
	}

	private function prepareSmartTrackingMode(): static
	{
		$this->arResult['isSmartTrackingMode'] = SmartTracking::get(
			$this->userId,
			0,
			SmartTracking::getDefaultCode()
		)->getValue();

		return $this;
	}

	private function preparePageData(): static
	{
		$this->arResult['page'] = $this->arParams['PAGE'];
		$this->arResult['pageType'] = $this->arParams['PAGE_TYPE'];
		$this->arResult['pageId'] = $this->arParams['PAGE_ID'];
		$this->componentTemplate = $this->arResult['page'];

		return $this;
	}

	private function prepareOwnerData(): static
	{
		$this->arResult['userId'] = Socialnetwork\Helper\User::getCurrentUserId();
		$this->arResult['groupId'] = $this->arParams['GROUP_ID'];

		return $this;
	}
	private function prepareCompositionData(): static
	{
		$composition = new Composition($this->arResult['userId'], $this->arResult['groupId']);
		$composition->setDefaultSettings();
		$this->arResult['compositionFilters'] = $composition->getDefaultSettings(false);
		$this->arResult['appliedFields'] = $composition->getSettings(false);

		return $this;
	}

	private function getTasksCounters(int $userId, int $groupId)
	{
		$counterInstance = Counter::getInstance($userId);

		$group = Socialnetwork\Item\Workgroup::getById($groupId);
		if ($group && $group->isScrumProject())
		{
			$taskService = new TaskService($userId);

			$filterInstance = $taskService->getFilterInstance(
				$groupId,
				$this->getGroupTasksViewMode($groupId, true)
			);
		}
		else
		{
			$filterInstance = Filter::getInstance($userId, $groupId);
		}

		$filterRole = $this->getFilterRole($filterInstance);
		$this->arResult['filterRole'] = $filterRole;

		$counters = $counterInstance->getCounters($filterRole, $groupId);

		$counters[CounterDictionary::COUNTER_EXPIRED]['filterField'] = 'PROBLEM';
		$counters[CounterDictionary::COUNTER_NEW_COMMENTS]['filterField'] = 'PROBLEM';

		return $counters;
	}

	private function getFilterRole($filterInstance): string
	{
		$filterOptions = $filterInstance->getOptions();
		$filter = $filterOptions->getFilter();

		$possibleRoles = Counter\Role::getRoles();
		$role = Counter\Role::ALL;

		if (
			array_key_exists('ROLEID', $filter)
			&& array_key_exists($filter['ROLEID'], $possibleRoles)
		)
		{
			$role = $filter['ROLEID'];
		}

		return $role;
	}

	private function getDisplayPriorityState(int $groupId)
	{
		return \CUserOptions::getOption('tasks.scrum.'.$groupId, 'display_priority', 'sprint');
	}

	private function getShortViewState(int $groupId)
	{
		return \CUserOptions::getOption('tasks.scrum.'.$groupId, 'short_view', 'Y');
	}

	private function getUserTasksViewMode(int $userId): string
	{
		Ui\Filter\Task::setUserId($userId);

		$viewCode = Ui\Filter\Task::listStateInit()?->getState()['VIEW_SELECTED']['CODENAME'];

		return $this->getTasksViewMode($viewCode);
	}

	private function getGroupTasksViewMode(int $groupId, bool $isScrum): string
	{
		if ($isScrum)
		{
			$viewHelper = new Scrum\Utility\ViewHelper($this->getSiteId());

			return match ($viewHelper->getActiveView($groupId))
			{
				'active_sprint' => 'active',
				'completed_sprint' => 'complete',
				default => 'plan',
			};
		}
		else
		{
			Ui\Filter\Task::setGroupId($groupId);

			$viewCode = Ui\Filter\Task::listStateInit()?->getState()['VIEW_SELECTED']['CODENAME'];

			return $this->getTasksViewMode($viewCode);
		}
	}

	private function getTasksViewMode(string $code): string
	{
		return match ($code)
		{
			'VIEW_MODE_GANTT' => 'gantt',
			'VIEW_MODE_PLAN' => 'plan',
			'VIEW_MODE_KANBAN' => 'kanban',
			'VIEW_MODE_TIMELINE' => 'timeline',
			'VIEW_MODE_CALENDAR' => 'calendar',
			default => 'list',
		};
	}

	private function saveScrumActiveView(?string $view, int $groupId)
	{
		$viewHelper = new Scrum\Utility\ViewHelper($this->getSiteId());

		$viewHelper->saveActiveView($view, $groupId);
	}

	private function isTasksView(): bool
	{
		return $this->arResult['pageId'] === 'tasks'; // todo: replace with const
	}

	private function isDiscussionsView(): bool
	{
		return $this->arResult['pageId'] === 'discussions'; // todo: replace with const
	}

	private function init(): static
	{
		$this->userId = $this->arParams['USER_ID'];
		$this->spaceId = $this->arParams['GROUP_ID'];
		return $this;
	}

	private function isGroupSpace(): bool
	{
		return $this->arResult['pageType'] === 'group';
	}
}