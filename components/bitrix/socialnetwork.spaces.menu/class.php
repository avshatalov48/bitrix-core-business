<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Helper\AvatarManager;
use Bitrix\Socialnetwork\Internals\Space;
use Bitrix\Socialnetwork\Item;
use Bitrix\Socialnetwork\Space\MembersManager;
use Bitrix\Socialnetwork\Space\List\Dictionary;

class SpacesMenuComponent extends \CBitrixComponent
{
	private $application;

	public function __construct($component = null)
	{
		parent::__construct($component);

		global $APPLICATION;
		$this->application = $APPLICATION;
	}

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));

		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		$params['PAGE'] = $params['PAGE'] ?? 'user_discussions';
		$params['PAGE_TYPE'] = $params['PAGE_TYPE'] ?? 'user';
		$params['PAGE_ID'] = $params['PAGE_ID'] ?? 'discussions';

		$params['GROUP_ID'] = (is_numeric($params['GROUP_ID'] ?? null) ? (int) $params['GROUP_ID'] : 0);

		return $params;
	}

	public function executeComponent()
	{
		$this->arResult['page'] = $this->arParams['PAGE'];
		$this->arResult['pageType'] = $this->arParams['PAGE_TYPE'];
		$this->arResult['pageId'] = $this->arParams['PAGE_ID'];

		$this->arResult['userId'] = Helper\User::getCurrentUserId();
		$this->arResult['groupId'] = $this->arParams['GROUP_ID'];

		if ($this->arResult['pageType'] === 'user')
		{
			$this->prepareUserMenu();
		}
		else
		{
			$this->prepareGroupMenu($this->arResult['groupId']);
		}

		$this->includeComponentTemplate($this->arResult['pageType']);
	}

	private function prepareUserMenu()
	{
		$userId = Helper\User::getCurrentUserId();

		$this->arResult['menuId'] = 'spaces_user_menu_' . $userId;

		$availableFeatures = $this->getAvailableUserFeatures($userId);
		$urls = $this->getUserUrls(
			$userId,
			$availableFeatures
		);

		$this->arResult['menuItems'] = $this->prepareUserMenuItems(
			$availableFeatures,
			$urls,
			$this->arParams['PAGE_ID']
		);
	}

	private function getAvailableUserFeatures(int $userId): array
	{
		$availableFeatures = [
			Dictionary::FEATURE_DISCUSSIONS => [
				'active' => true,
				'customName' => '',
			],
			Dictionary::FEATURE_TASKS => [
				'active' => true,
				'customName' => '',
			],
			Dictionary::FEATURE_CALENDAR => [
				'active' => true,
				'customName' => '',
			],
			Dictionary::FEATURE_FILES => [
				'active' => true,
				'customName' => '',
			],
		];

		return array_filter($availableFeatures, function ($value) {
			return (bool) $value['active'];
		});
	}

	private function getUserUrls(int $userId, array $availableFeatures): array
	{
		$urls = [];

		foreach ($availableFeatures as $featureId => $featureName)
		{
			switch ($featureId)
			{
				case Dictionary::FEATURE_DISCUSSIONS:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_DISCUSSIONS']
					);
					break;
				case Dictionary::FEATURE_TASKS:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_TASKS']
					);
					break;
				case Dictionary::FEATURE_CALENDAR:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_CALENDAR']
					);
					break;
				case Dictionary::FEATURE_FILES:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_USER_FILES'],
						[
							'user_id' => $userId,
							'PATH' => '',
						]
					);
					break;
			}
		}

		return $urls;
	}

	private function prepareUserMenuItems(
		array $availableFeatures,
		array $urls,
		string $pageId
	): array
	{
		$items = [];

		foreach ($availableFeatures as $featureId => $featureName)
		{
			$items[] = $this->getItem($featureId, $featureName, $urls[$featureId], $pageId);
		}

		return $items;
	}

	private function prepareGroupMenu(int $groupId): void
	{
		$userId = Helper\User::getCurrentUserId();

		$group = Item\Workgroup::getById($groupId);

		$this->arResult['isScrum'] = $group && $group->isScrumProject();

		$avatarManager = new AvatarManager();
		$groupFields = $group->getFields();
		$imageId = (int) $groupFields['IMAGE_ID'];
		if ($imageId)
		{
			$this->arResult['logo'] = $avatarManager->getImageAvatar($imageId)->toArray();
		}
		else
		{
			$this->arResult['logo'] = $avatarManager
				->getIconAvatar($groupFields['AVATAR_TYPE'] ?? '')
				->toArray()
			;
		}

		$this->arResult['menuId'] = 'spaces_group_menu_' . $groupId;

		$availableFeatures = $this->getAvailableGroupFeatures(
			$groupId,
			$userId
		);
		$urls = $this->getGroupUrls(
			$groupId,
			$availableFeatures
		);

		$this->arResult['pathToDiscussions'] = $urls['discussions'];

		$this->arResult['menuItems'] = $this->prepareGroupMenuItems(
			$availableFeatures,
			$urls,
			$this->arParams['PAGE_ID'],
			$groupId
		);

		$this->arResult['pathToCommonSpace'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_USER_DISCUSSIONS']
		);
		$this->arResult['pathToGroupFeatures'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_FEATURES'],
			['group_id' => $this->arResult['groupId']]
		);
		$this->arResult['pathToGroupUsers'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_USERS'],
			['group_id' => $this->arResult['groupId']]
		);
		$this->arResult['pathToGroupInvite'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_INVITE'],
			['group_id' => $this->arResult['groupId']]
		);

		$this->arResult['pathToScrumTeamSpeed'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_SCRUM_TEAM_SPEED'],
			['group_id' => $this->arResult['groupId']]
		);
		$this->arResult['pathToScrumBurnDown'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_SCRUM_BURN_DOWN'],
			['group_id' => $this->arResult['groupId']]
		);
		$this->arResult['pathToGroupTasksTask'] = CComponentEngine::makePathFromTemplate(
			$this->arParams['PATH_TO_GROUP_TASKS_TASK'],
			['group_id' => $this->arResult['groupId']]
		);

		$membersManager = new MembersManager();
		$this->arResult['groupMembersList'] = $membersManager->getGroupMembersList($groupId);
		$this->arResult['isNew'] = Context::getCurrent()->getRequest()->get('empty-state') === 'enabled';
		$this->arResult['isMember'] = $membersManager->isUserMember($userId, $groupId);

		$this->arResult['canInvite'] = $membersManager->canInviteUsers($groupId);

		$this->arResult['availableFeatures'] = [
			'discussions' => array_key_exists('discussions', $availableFeatures),
			'tasks' => array_key_exists('tasks', $availableFeatures),
			'calendar' => array_key_exists('calendar', $availableFeatures),
			'files' => array_key_exists('files', $availableFeatures),
		];
	}

	private function getGroupUrls(int $groupId, array $availableFeatures): array
	{
		$urls = [
			'view' => CComponentEngine::makePathFromTemplate(
				$this->arParams['PATH_TO_GROUP_DISCUSSIONS'],
				['group_id' => $groupId]
			)
		];

		foreach ($availableFeatures as $featureId => $feature)
		{
			switch ($featureId)
			{
				case Dictionary::FEATURE_DISCUSSIONS:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_GROUP_DISCUSSIONS'],
						['group_id' => $groupId]
					);
					break;
				case Dictionary::FEATURE_TASKS:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_GROUP_TASKS'],
						['group_id' => $groupId]
					);
					break;
				case Dictionary::FEATURE_CALENDAR:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_GROUP_CALENDAR'],
						['group_id' => $groupId]
					);
					break;
				case Dictionary::FEATURE_FILES:
					$urls[$featureId] = CComponentEngine::makePathFromTemplate(
						$this->arParams['PATH_TO_GROUP_FILES'],
						[
							'group_id' => $groupId,
							'PATH' => '',
						]
					);
					break;
			}
		}

		return $urls;
	}

	private function getAvailableGroupFeatures(int $groupId, int $userId): array
	{
		$entityType = 'G';

		$availableFeatures = [
			Dictionary::FEATURE_DISCUSSIONS => [
				'active' => CSocNetFeatures::isActiveFeature($entityType, $groupId, 'blog'),
				'customName' => '',
			],
		];

		$activeFeatures = CSocNetFeatures::getFeaturesNames($entityType, $groupId);

		$tasksFeatureActive = (
			array_key_exists('tasks', $activeFeatures)
			&& CSocNetFeaturesPerms::canPerformOperation(
				$userId,
				$entityType,
				$groupId,
				'tasks',
				'view',
				CSocNetUser::isCurrentUserModuleAdmin()
			)
		);
		$tasksFeatureName = $tasksFeatureActive ? $activeFeatures['tasks'] : '';
		$availableFeatures['tasks'] = [
			'active' => $tasksFeatureActive,
			'customName' => $tasksFeatureName,
		];

		$calendarFeatureActive = (
			array_key_exists('calendar', $activeFeatures)
			&& CSocNetFeaturesPerms::canPerformOperation(
				$userId,
				$entityType,
				$groupId,
				'calendar',
				'view',
				CSocNetUser::isCurrentUserModuleAdmin()
			)
		);
		$calendarFeatureName = $calendarFeatureActive ? $activeFeatures['calendar'] : '';
		$availableFeatures['calendar'] = [
			'active' => $calendarFeatureActive,
			'customName' => $calendarFeatureName,
		];

		$diskEnabled = (
			Loader::includeModule('disk')
			&& \Bitrix\Disk\Driver::isSuccessfullyConverted()
		);
		if ($diskEnabled)
		{
			$filesFeatureActive = array_key_exists('files', $activeFeatures);
			$filesFeatureName = $filesFeatureActive ? $activeFeatures['files'] : '';
			$availableFeatures['files'] = [
				'active' => $filesFeatureActive,
				'customName' => $filesFeatureName,
			];
		}

		return array_filter($availableFeatures, function ($value) {
			return (bool) $value['active'];
		});
	}

	private function prepareGroupMenuItems(
		array $availableFeatures,
		array $urls,
		string $pageId,
		int $spaceId,
	): array
	{
		$items = [];

		foreach ($availableFeatures as $featureId => $feature)
		{
			$items[] = $this->getItem($featureId, $feature, $urls[$featureId], $pageId, $spaceId);
		}

		return $items;
	}

	private function getItem(
		string $featureId,
		array $feature,
		string $url,
		string $pageId,
		int $spaceId = 0
	): array
	{
		$userId = Helper\User::getCurrentUserId();

		return [
			'TEXT' => $feature['customName'] ?: $this->getItemText($featureId),
			'ID' => $featureId,
			'ON_CLICK' => 'top.BX.Socialnetwork.Spaces.space.reloadPageContent("'.$url.'");',
			'IS_ACTIVE' => $this->getItemActivity($featureId, $pageId),
			'COUNTER' => $this->getItemCounter($featureId, $spaceId),
			'COUNTER_ID' => 'spaces_top_menu_' . $userId . '_' . $spaceId . '_' . $featureId,
		];
	}

	private function getItemText(string $featureId): string
	{
		$text = '';

		switch ($featureId)
		{
			case Dictionary::FEATURE_GENERAL:
			case Dictionary::FEATURE_DISCUSSIONS:
				$text = Loc::getMessage('SN_SPACES_MENU_GENERAL');
				break;
			case Dictionary::FEATURE_TASKS:
				$text = Loc::getMessage('SN_SPACES_MENU_TASKS');
				break;
			case Dictionary::FEATURE_CALENDAR:
				$text = Loc::getMessage('SN_SPACES_MENU_CALENDAR');
				break;
			case Dictionary::FEATURE_FILES:
				$text = Loc::getMessage('SN_SPACES_MENU_FILES');
				break;
		}

		return $text;
	}

	private function getItemActivity(string $featureId, string $pageId): bool
	{
		$isActive = false;

		switch ($featureId)
		{
			case Dictionary::FEATURE_GENERAL:
			case Dictionary::FEATURE_DISCUSSIONS:
				$isActive = $pageId === Dictionary::FEATURE_DISCUSSIONS;
				break;
			case Dictionary::FEATURE_TASKS:
			case Dictionary::FEATURE_CALENDAR:
				$isActive = $pageId === $featureId;
				break;
			case Dictionary::FEATURE_FILES:
				$isActive = (
					$pageId === $featureId
					|| $pageId === 'files_file'
				);
				break;
		}

		return $isActive;
	}

	private function getItemCounter(string $featureId, int $spaceId = 0): int
	{
		$userId = Helper\User::getCurrentUserId();
		$counter = Space\Counter::getInstance($userId);

		switch ($featureId)
		{
			case Dictionary::FEATURE_GENERAL:
			case Dictionary::FEATURE_DISCUSSIONS:
				return $counter->getValue($spaceId, [Space\Counter\Dictionary::COUNTERS_LIVEFEED_TOTAL]);
			case Dictionary::FEATURE_TASKS:
				return $counter->getValue($spaceId, [Space\Counter\Dictionary::COUNTERS_TASKS_TOTAL]);
			case Dictionary::FEATURE_CALENDAR:
				return $counter->getValue($spaceId, [Space\Counter\Dictionary::COUNTERS_CALENDAR_TOTAL]);
			default:
				return 0;
		}
	}
}
