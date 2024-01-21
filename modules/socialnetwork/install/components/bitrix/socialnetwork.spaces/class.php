<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Item\Workgroup;
use Bitrix\Socialnetwork\Space\List\SpaceListMode;

class SpacesComponent extends CBitrixComponent
{
	private $application;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->init();
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
				\Bitrix\Disk\Driver::getInstance()->getUrlManager(),
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

		if ($componentPage === 'group')
		{
			$groupId = (int)($variables['group_id'] ?? 0);
			$group = Workgroup::getById($groupId);
			$isScrum = $group && $group->isScrumProject();
			$componentPage = $isScrum ? 'group_tasks' : 'group_discussions';
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

		$this->includeComponentTemplate($componentPage);
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
			'group_tasks_task' => $groupSefFolder . 'group/#group_id#/tasks/task/#action#/#task_id#/',
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
		$urls['PATH_TO_VIDEO_CALL'] = isModuleInstalled('video') ? $userSefFolder . 'video/#user_id#/' : '';
		$urls['PATH_TO_COMPANY_DEPARTMENT'] = (
			isModuleInstalled('intranet')
				? '/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#'
				: ''
		);

		$urls['PATH_TO_USER_TASKS_TASK'] = $userSefFolder . 'user/#user_id#/tasks/task/#action#/#task_id#/';
		$urls['PATH_TO_USER_TASKS_VIEW'] = $userSefFolder . 'user/#user_id#/tasks/view/#action#/#view_id#/';
		$urls['PATH_TO_USER_TASKS_REPORT'] = $userSefFolder . 'user/#user_id#/tasks/report/';
		$urls['PATH_TO_USER_TASKS_TEMPLATES'] = $userSefFolder . 'user/#user_id#/tasks/templates/';

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
	}
}