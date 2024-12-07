<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Disk\Driver;
use Bitrix\Disk\Internals\Grid\FolderListOptions;
use Bitrix\Disk\Internals\Grid\TrashCanOptions;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Errorable;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Space\List\SpaceListMode;

class SpacesComponent extends CBitrixComponent implements Controllerable, Errorable
{
	private $application;
	private ErrorCollection $errorCollection;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->init();
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

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));

		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params['SEF_MODE'] = 'Y';
		$params['SEF_FOLDER'] = $params['SEF_FOLDER'] ?? '/spaces/';
		$params['USER_SEF_FOLDER'] = $this->getUserSefFolder();
		$params['GROUP_SEF_FOLDER'] = $this->getGroupSefFolder();

		if (
			!isset($params['VARIABLE_ALIASES'])
			|| !is_array($params['VARIABLE_ALIASES'])
		)
		{
			$params['VARIABLE_ALIASES'] = [];
		}

		return $params;
	}

	public function executeComponent()
	{
		if (!\Bitrix\Socialnetwork\Space\Service::isAvailable())
		{
			ShowError('Functionality not available.');

			return;
		}

		$this->application->setTitle(Loc::getMessage('SN_SPACES_TITLE'));

		$defaultUrlTemplates404 = $this->prepareDefaultUrlTemplates(
			$this->arParams['USER_SEF_FOLDER'],
			$this->arParams['GROUP_SEF_FOLDER'],
		);

		$variables = [];
		$defaultVariableAliases404 = [];
		$componentVariables = $this->getComponentVariables();

		$engine = new CComponentEngine($this);

		if ($this->isDiskEnabled())
		{
			$engine->addGreedyPart('#PATH#');
			$engine->addGreedyPart('#FILE_PATH#');
			$engine->addGreedyPart('#TRASH_PATH#');
			$engine->addGreedyPart('#TRASH_FILE_PATH#');
			$engine->setResolveCallback([
				Driver::getInstance()->getUrlManager(),
				'resolveSocNetPathComponentEngine',
			]);
		}

		$urlTemplates = CComponentEngine::makeComponentUrlTemplates(
			$defaultUrlTemplates404,
			[]
		);
		$variableAliases = CComponentEngine::makeComponentVariableAliases(
			$defaultVariableAliases404,
			$this->arParams['VARIABLE_ALIASES']
		);

		$componentPage = $engine->guessComponentPath(
			$this->arParams['SEF_FOLDER'],
			$urlTemplates,
			$variables,
		);

		$groupId = (int) ($variables['group_id'] ?? 0);
		$userId = (int) ($variables['user_id'] ?? 0);

		if ($componentPage === 'group')
		{
			$group = Workgroup::getById($groupId);
			$isScrum = $group && $group->isScrumProject();
			$componentPage = $isScrum ? 'group_discussions' : 'group_discussions';
		}

		if ($componentPage === 'index')
		{
			$componentPage = 'user_discussions';
		}

		CComponentEngine::initComponentVariables(
			$componentPage,
			$componentVariables,
			$variableAliases,
			$variables
		);

		$this->arResult = $this->prepareResult(
			$variables,
			$variableAliases,
			$urlTemplates
		);

		$pageType = $this->getPageType($componentPage);

		$this->arResult['pageView'] = $this->getPageView(
			$pageType,
			$userId,
			$groupId,
			[
				'isTrashMode' => str_contains($componentPage, 'trashcan'),
				'viewMode' => Context::getCurrent()->getRequest()->get('viewMode'),
				'viewSize' => Context::getCurrent()->getRequest()->get('viewSize'),
			],
		);

		$isFrame = (Context::getCurrent()->getRequest()->get('IFRAME') === 'Y');
		if ($isFrame)
		{
			$this->application->restartBuffer();
		}

		$siteTemplateId = (defined('SITE_TEMPLATE_ID') ? SITE_TEMPLATE_ID  : 'def');
		if (
			Loader::includeModule('intranet')
			&& $siteTemplateId === 'bitrix24'
		)
		{
			$this->arResult['SHOW_BITRIX24_THEME'] = 'Y';
		}
		else
		{
			$this->arResult['SHOW_BITRIX24_THEME'] = 'N';
		}

		$this->checkFeaturesAccess($groupId, $componentPage);

		$this->includeComponentTemplate($componentPage);

		if ($isFrame)
		{
			\Bitrix\Socialnetwork\Internals\Space\LiveWatch\LiveWatchService::getInstance()->setUserAsWatchingNow(
				\Bitrix\Socialnetwork\Helper\User::getCurrentUserId()
			);

			require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');

			exit;
		}
	}

	public function getPageViewAction(
		string $pageType,
		int $userId,
		int $groupId,
		array $params,
	): string
	{
		return $this->getPageView(
			$pageType,
			$userId,
			$groupId,
			[
				'isTrashMode' => ($params['isTrashMode'] ?? null) === true,
				'viewMode' => ($params['viewMode'] ?? null) ? $params['viewMode'] : '',
				'viewSize' => ($params['viewSize'] ?? null) ? $params['viewSize'] : '',
			],
		);
	}

	private function prepareDefaultUrlTemplates(
		string $userSefFolder,
		string $groupSefFolder
	): array
	{
		$urls = [
			'index' => 'index.php',

			'user_discussions' => 'discussions/',

			'user_tasks' => 'tasks/',

			'user_calendar' => 'calendar/',

			'group' => 'group/#group_id#/',
			'group_discussions' => 'group/#group_id#/general/',

			'group_tasks' => 'group/#group_id#/tasks/',
			'group_tasks_task' => 'group/#group_id#/tasks/task/#action#/#task_id#/',
			'group_tasks_view' => 'group/#group_id#/tasks/view/#action#/#view_id#/',
			'group_tasks_report' => 'group/#group_id#/tasks/report/',

			'group_calendar' => 'group/#group_id#/calendar/',

			'group_photo_section' => 'group/#group_id#/photo/album/#section_id#/',

			'scrum_team_speed' => $groupSefFolder . 'group/#group_id#/scrum/team_speed/',
			'scrum_burn_down' => $groupSefFolder . 'group/#group_id#/scrum/burn_down/#sprint_id#/',

			'group_features' => 'group/#group_id#/features/',
			'group_users' => 'group/#group_id#/users/',
			'group_invite' => 'group/#group_id#/invite/',
		];

		if ($this->isDiskEnabled())
		{
			$urls['spaces_user_files'] = 'files/user/#user_id#/';
			$urls['user_files'] = 'files/user/#user_id#/disk/path/#PATH#';
			$urls['user_files_file'] = 'files/user/#user_id#/disk/file/#FILE_PATH#';
			$urls['user_files_file_history'] = 'files/user/#user_id#/disk/file-history/#FILE_ID#';
			$urls['user_files_trashcan_list'] = 'files/user/#user_id#/disk/trashcan/#TRASH_PATH#';
			$urls['user_files_trashcan_file_view'] = 'files/user/#user_id#/disk/trash/file/#TRASH_FILE_PATH#';
			$urls["user_files_external_link_list"] = 'user/#user_id#/disk/external';
			$urls["user_files_volume"] = $userSefFolder . 'user/#user_id#/disk/volume/#ACTION#';

			$urls['group_files'] = 'group/#group_id#/disk/path/#PATH#';
			$urls['group_files_file'] = 'group/#group_id#/disk/file/#FILE_PATH#';
			$urls['group_files_file_history'] = 'group/#group_id#/disk/file-history/#FILE_ID#';
			$urls['group_files_trashcan_list'] = 'group/#group_id#/disk/trashcan/#TRASH_PATH#';
			$urls['group_files_trashcan_file_view'] = 'group/#group_id#/disk/trash/file/#TRASH_FILE_PATH#';
			$urls['group_files_bizproc_workflow_admin'] = $groupSefFolder . 'group/#group_id#/disk/bp/';
			$urls['group_disk_bizproc_workflow_edit'] = $groupSefFolder . 'group/#group_id#/disk/bp_edit/#ID#/';
			$urls['group_files_start_bizproc'] = $groupSefFolder . 'group/#group_id#/disk/bp_start/#ELEMENT_ID#/';
			$urls['group_files_task'] = 'group/#group_id#/disk/bp_task/#ID#/';
			$urls['group_files_task_list'] = 'group/#group_id#/disk/bp_task_list/';
		}

		return $urls;
	}

	private function prepareResult(
		array $variables,
		array $variableAliases,
		array $urlTemplates
	): array
	{
		$urls = $this->prepareUrls(
			$this->arParams['SEF_FOLDER'],
			$this->arParams['USER_SEF_FOLDER'],
			$urlTemplates
		);

		$result = [
			'VARIABLES' => $variables,
			'ALIASES' => $variableAliases,
		];

		$result = array_merge($result, $urls);

		$result['NAME_TEMPLATE'] = CSite::getNameFormat();
		$result['DATE_TIME_FORMAT'] = CIntranetUtils::getCurrentDateTimeFormat();
		$result['DATE_TIME_FORMAT_WITHOUT_YEAR'] = CIntranetUtils::getCurrentDateTimeFormat(
			['woYear' => true]
		);

		$result['IS_LIST_DEPLOYED'] = $this->getListMode();

		return $result;
	}

	private function prepareUrls(
		string $sefFolder,
		string $userSefFolder,
		array $urlTemplates
	): array
	{
		$urls = [];

		foreach ($urlTemplates as $url => $value)
		{
			$urls['PATH_TO_' . mb_strtoupper($url)] = (
				(mb_substr($value, 0, 1) === '/') ? $value : $sefFolder . $value
			);
		}

		$urls['PATH_TO_USER'] = $userSefFolder . 'user/#user_id#/';

		$urls['PATH_TO_MESSAGES_CHAT'] = $userSefFolder . 'messages/chat/#user_id#/';
		$urls['PATH_TO_USER_BLOG_POST_IMPORTANT'] = $userSefFolder . 'user/#user_id#/blog/important/';
		$urls['PATH_TO_SEARCH_TAG'] = isModuleInstalled('search') ? SITE_DIR . 'search/?tags=#tag#' : '';
		$urls['PATH_TO_VIDEO_CALL'] = '';
		$urls['PATH_TO_COMPANY_DEPARTMENT'] = (
			isModuleInstalled('intranet')
				? '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#'
				: ''
		);

		$urls['PATH_TO_USER_TASKS_TASK'] = $userSefFolder . 'user/#user_id#/tasks/task/#action#/#task_id#/';
		$urls['PATH_TO_USER_TASKS_VIEW'] = $userSefFolder . 'user/#user_id#/tasks/view/#action#/#view_id#/';
		$urls['PATH_TO_USER_TASKS_REPORT'] = $userSefFolder . 'user/#user_id#/tasks/report/';
		$urls['PATH_TO_USER_TASKS_TEMPLATES'] = $userSefFolder . 'user/#user_id#/tasks/templates/';
		$urls['PATH_TO_USER_TEMPLATES_TEMPLATE'] = $userSefFolder
			. 'user/#user_id#/tasks/templates/template/#action#/#template_id#/'
		;

		return $urls;
	}

	private function getComponentVariables(): array
	{
		return [
			'user_id',
			'group_id',
			'page',
			'message_id',
			'subject_id',
			'path',
			'section_id',
			'element_id',
			'action',
			'post_id',
			'category',
			'topic_id',
			'task_id',
			'view_id',
			'type',
			'report_id',
			'placement_id',
			'sprint_id',
		];
	}

	private function getUserSefFolder(): string
	{
		return Option::get(
			'socialnetwork',
			'user_page',
			(
				IsModuleInstalled('extranet')
				&& $this->getSiteId() == Option::get('extranet', 'extranet_site')
					? '/extranet/contacts/personal/'
					: '/company/personal/'
			),
			$this->getSiteId()
		);
	}

	private function getGroupSefFolder(): string
	{
		return Option::get(
			'socialnetwork',
			'workgroups_page',
			(
				IsModuleInstalled('extranet')
				&& $this->getSiteId() == Option::get('extranet', 'extranet_site')
					? '/extranet/workgroups/'
					: 'workgroups/'
			),
			$this->getSiteId()
		);
	}

	private function isDiskEnabled(): bool
	{
		return (
			Option::get('disk', 'successfully_converted', false)
			&& Loader::includeModule('disk')
		);
	}

	private function getListMode(): string
	{
		return SpaceListMode::getOption();
	}

	private function init(): void
	{
		Loader::includeModule('socialnetwork');

		global $APPLICATION;
		$this->application = $APPLICATION;

		$this->errorCollection = new ErrorCollection();
	}

	private function checkFeaturesAccess(int $groupId, string $componentPage)
	{
		$this->arResult['spaceNotFoundOrCantSee'] = false;

		$feature = null;

		$tasksPages = [
			'group_tasks',
			'group_tasks_task',
		];
		if (in_array($componentPage, $tasksPages, true))
		{
			$feature = 'tasks';
		}

		$calendarPages = [
			'group_calendar',
		];
		if (in_array($componentPage, $calendarPages, true))
		{
			$feature = 'calendar';
		}

		$filePages = [
			'group_files',
			'group_files_file',
			'group_files_file_history',
			'group_files_trashcan_list',
			'group_files_trashcan_file_view',
		];
		if (in_array($componentPage, $filePages, true))
		{
			$feature = 'files';
		}

		if ($feature)
		{
			$this->arResult['spaceNotFoundOrCantSee'] = (
				!CSocNetFeatures::isActiveFeature(SONET_ENTITY_GROUP, $groupId, $feature)
			);
		}
	}

	private function getPageView(
		string $pageType,
		int $userId,
		int $groupId,
		array $params,
	): string
	{
		$isTrashMode = ($params['isTrashMode'] ?? null) === true;
		$viewMode = ($params['viewMode'] ?? null) ? $params['viewMode'] : '';
		$viewSize = ($params['viewSize'] ?? null) ? $params['viewSize'] : '';

		return match ($pageType)
		{
			'discussions' => 'discussions',
			'tasks' => $this->getTasksPageView($userId, $groupId, $viewMode),
			'calendar' => $this->getCalendarPageView(),
			'files' => $this->getFilesPageView($userId, $groupId, $isTrashMode, $viewMode, $viewSize),
			default => '',
		};
	}

	private function getPageType(string $componentPage): string
	{
		if (str_contains($componentPage, 'discussions'))
		{
			return 'discussions';
		}
		else if (str_contains($componentPage, 'tasks'))
		{
			return 'tasks';
		}
		else if (str_contains($componentPage, 'calendar'))
		{
			return 'calendar';
		}
		else if (str_contains($componentPage, 'files'))
		{
			return 'files';
		}
		else
		{
			return '';
		}
	}

	private function getTasksPageView(int $userId, int $groupId, string $viewMode = ''): string
	{
		if (!Loader::includeModule('tasks'))
		{
			return '';
		}

		if ($groupId)
		{
			\Bitrix\Tasks\Ui\Filter\Task::setGroupId($groupId);

			$group = \Bitrix\Socialnetwork\Item\Workgroup::getById($groupId);
			$isScrum = $group && $group->isScrumProject();
			if ($isScrum)
			{
				if (!$viewMode)
				{
					$viewHelper = new \Bitrix\Tasks\Scrum\Utility\ViewHelper($this->getSiteId());

					$viewMode = $viewHelper->getActiveView($groupId);
				}

				$scrumView = match ($viewMode)
				{
					'active_sprint' => 'active',
					'completed_sprint' => 'complete',
					default => 'plan',
				};

				$pageView = 'tasks-scrum-' . $scrumView;
				if ($scrumView === 'plan')
				{
					$displayPriority = \CUserOptions::getOption(
						'tasks.scrum.' . $groupId,
						'display_priority',
						'sprint'
					);

					$pageView .= '-' . $displayPriority;
				}

				return $pageView;
			}
		}
		else if($userId)
		{
			\Bitrix\Tasks\Ui\Filter\Task::setUserId($userId);
		}

		$state = \Bitrix\Tasks\Ui\Filter\Task::listStateInit()->getState();

		switch ($state['VIEW_SELECTED']['CODENAME'])
		{
			case 'VIEW_MODE_GANTT':
				return 'tasks-gantt';
			case 'VIEW_MODE_PLAN':
				return 'tasks-plan';
			case 'VIEW_MODE_KANBAN':
				return 'tasks-kanban';
			case 'VIEW_MODE_TIMELINE':
				return 'tasks-timeline';
			case 'VIEW_MODE_CALENDAR':
				return 'tasks-calendar';
			default:
				return 'tasks-list';
		}
	}

	private function getCalendarPageView(): string
	{
		if (!Loader::includeModule('calendar'))
		{
			return '';
		}

		$view = \Bitrix\Calendar\UserSettings::get()['view'];

		if ($view === 'day' || $view === 'list')
		{
			return 'calendar-schedule';
		}
		else
		{
			return 'calendar-base';
		}
	}

	private function getFilesPageView(
		int $userId,
		int $groupId,
		bool $isTrashMode,
		string $viewMode = '',
		string $viewSize = '',
	): string
	{
		if (!Loader::includeModule('disk'))
		{
			return '';
		}

		if ($viewMode)
		{
			if ($viewMode === FolderListOptions::VIEW_MODE_TILE)
			{
				$availableSizes = [
					FolderListOptions::VIEW_TILE_SIZE_M,
					FolderListOptions::VIEW_TILE_SIZE_XL,
				];
				$viewSize = in_array($viewSize, $availableSizes) ? $viewSize : FolderListOptions::VIEW_TILE_SIZE_M;

				return 'files-tile-' . $viewSize;
			}
			else
			{
				return 'files-list';
			}
		}

		if ($groupId)
		{
			$storage = Driver::getInstance()->getStorageByGroupId($groupId);
		}
		else
		{
			$storage = Driver::getInstance()->getStorageByUserId($userId);
		}

		if ($isTrashMode)
		{
			$options = new TrashCanOptions($storage);
		}
		else
		{
			$options = new FolderListOptions($storage);
		}

		if ($options->getViewMode() === FolderListOptions::VIEW_MODE_TILE)
		{
			return 'files-tile-' . $options->getViewSize();
		}
		else
		{
			return 'files-list';
		}
	}
}