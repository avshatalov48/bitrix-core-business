<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\Collection;
use Bitrix\Main\Type\Contract\Arrayable;
use Bitrix\Socialnetwork\Helper;
use Bitrix\Socialnetwork\Integration\Intranet\ThemePicker;
use Bitrix\Socialnetwork\Space\List\Dictionary;
use Bitrix\Socialnetwork\Space\List\FilterModeOption;
use Bitrix\Socialnetwork\Space\List\SpaceListMode;
use Bitrix\Socialnetwork\Space\List\Invitation\InvitationManager;
use Bitrix\Socialnetwork\Space\List\Provider;
use Bitrix\Socialnetwork\Space\List\Item\Space;
use Bitrix\Socialnetwork\Space\List\RecentSearch\RecentSearchManager;

class SpacesListComponent extends CBitrixComponent implements Controllerable, \Bitrix\Main\Errorable
{
	protected ErrorCollection $errors;
	protected int $userId;

	public function __construct($component = null)
	{
		parent::__construct($component);
		$this->init();
	}

	public function getErrors(): array
	{
		return $this->errors->toArray();
	}

	public function getErrorByCode($code)
	{
		return null;
	}

	public function configureActions(): array
	{
		return [];
	}

	public function loadSpacesAction(string $mode, int $loadedSpacesCount): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		if (!in_array($mode, Dictionary::FILTER_MODES))
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();
		$provider = $this->getProvider($userId, $mode);
		$provider->setOffset($loadedSpacesCount);

		$result = $provider->getSpaces();
		if ($result->getErrors())
		{
			return null;
		}

		$data = $result->getData();
		$data['spaces'] = $this->castItemsToArray($data['spaces'] ?? []);

		return $data;
	}

	public function reloadSpacesAction(string $mode): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		if (!in_array($mode, Dictionary::FILTER_MODES))
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();
		$result = $this->getInitialSpaces($userId, $mode);
		if ($result->getErrors())
		{
			return null;
		}

		$data = $result->getData();
		$data['spaces'] = $this->castItemsToArray($data['spaces'] ?? []);

		FilterModeOption::setOption($userId, $mode);

		return $data;
	}

	public function searchSpacesAction(string $searchString, int $loadedSpacesCount): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();
		$provider = $this->getProvider($userId, Dictionary::FILTER_MODES['all']);
		$provider->setOffset($loadedSpacesCount);

		$result = $provider->searchSpaces($searchString);
		if ($result->getErrors())
		{
			return null;
		}

		$data = $result->getData();
		$data['spaces'] = $this->castItemsToArray($data['spaces'] ?? []);

		return $data;
	}

	public function addSpaceToRecentSearchAction(int $spaceId)
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();
		$recentSearchManager = new RecentSearchManager($userId);
		$recentSearchManager->addSpaceToRecentSearch($spaceId);
	}

	public function loadRecentSearchSpacesAction(): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();

		$recentSearchManager = (new RecentSearchManager($userId));

		$recentlySearchedSpacesData = $recentSearchManager->getRecentlySearchedSpacesData();
		$recentlySearchedSpaceIds = array_map(static function ($spaceData){
			return $spaceData->getSpaceId();
		}, $recentlySearchedSpacesData->toArray());

		$spaces = $this->getSpacesByIds($recentlySearchedSpaceIds, $userId);

		foreach ($spaces as $space)
		{
			$spaceSearchData = $recentlySearchedSpacesData->getSpaceSearchDataBySpacesId($space->getId());
			if ($spaceSearchData)
			{
				$space->setLastSearchDate($spaceSearchData->getLastSearchDate());
			}
		}

		return $spaces;
	}

	public function loadSpaceDataAction(int $spaceId): ?array
	{
		if (!Loader::includeModule('socialnetwork') || $spaceId < 0)
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();

		$invitation = $spaceId > 0 ? (new InvitationManager($userId))->getInvitationBySpaceId($spaceId) : null;
		$space = (new Provider($userId))->getSpaceById($spaceId);

		return [
			'space' => $space,
			'invitation' => $invitation,
			'isInvitation' => !empty($invitation),
			'spaceId' => $spaceId,
		];
	}

	public function loadSpacesDataAction(array $spaceIds): ?array
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		$userId = Helper\User::getCurrentUserId();

		$provider = (new Provider($userId));

		$result = [];

		if (in_array(0, $spaceIds))
		{
			$result[] = $provider->getCommonSpace();
		}

		Collection::normalizeArrayValuesByInt($spaceIds);

		if (empty($spaceIds))
		{
			return $result;
		}

		return array_merge($result, $provider->getSpacesByIds($spaceIds));
	}

	public function loadSpaceThemeAction(int $spaceId): ?array
	{
		if ($spaceId < 0 || !Loader::includeModule('socialnetwork'))
		{
			return null;
		}

		return ThemePicker::getGroupTheme($spaceId);
	}

	public function onIncludeComponentLang()
	{
		$this->includeComponentLang(basename(__FILE__));

		Loc::loadMessages(__FILE__);
	}

	public function onPrepareComponentParams($params)
	{
		return $params;
	}

	public function executeComponent()
	{
		$this->arResult['PATH_TO_GROUP_SPACE'] = $this->arParams['PATH_TO_GROUP'];
		$this->arResult['PATH_TO_USER_SPACE'] = $this->arParams['PATH_TO_USER_DISCUSSIONS'];
		$this->arResult['SELECTED_SPACE_ID'] = $this->arParams['GROUP_ID'] ?? 0;

		$userId = Helper\User::getCurrentUserId();
		$this->arResult['FILTER_MODE'] = FilterModeOption::getOption($userId);
		$this->arResult['CAN_CREATE_GROUP'] = Helper\Workgroup\Access::canCreate();


		$this->arResult['SPACES_LIST_MODE'] = SpaceListMode::getOption();

		$getInitialSpacesResult = $this->getInitialSpaces(
			$userId,
			$this->arResult['FILTER_MODE'],
		);

		$recentSpaces = $getInitialSpacesResult->getData()['spaces'] ?? [];
		$recentSpaceIds = array_map(static function($recentSpace) {
			/** @var Space $recentSpace */
			return $recentSpace->getId();
		}, $recentSpaces);

		[$invitationOnlySpaces, $invitations] = $this->getInvitations($userId, $recentSpaces);
		$invitationSpaceIds = array_map(static function($invitation) {
			return $invitation->getSpaceId();
		}, $invitations);

		$spaces = array_merge($recentSpaces, $invitationOnlySpaces);

		$this->arResult['RECENT_SPACE_IDS'] = $recentSpaceIds;
		$this->arResult['SPACES'] = $this->castItemsToArray($spaces);
		$this->arResult['INVITATION_SPACE_IDS'] = $invitationSpaceIds;
		$this->arResult['INVITATIONS'] = $this->castItemsToArray($invitations);
		$this->arResult['AVATAR_COLORS'] = Helper\Workgroup::getAvatarColors();
		$this->arResult['doShowCollapseMenuAhaMoment'] = $this->doShowCollapseMenuAhaMoment();
		$this->arResult['USER_THEME'] = ThemePicker::getUserTheme();

		$this->includeComponentTemplate();
	}

	private function doShowCollapseMenuAhaMoment(): bool
	{
		$leftMenuCollapsed = \CUserOptions::GetOption('intranet', 'left_menu_collapsed') === 'Y';
		$dontShowAhaMoment = \CUserOptions::GetOption('socialnetwork', 'dontShowCollapseMenuAhaMoment') === 'Y';

		return !$leftMenuCollapsed && !$dontShowAhaMoment;
	}

	private function getInitialSpaces(int $userId, string $mode): \Bitrix\Main\Result
	{
		$provider = $this->getProvider($userId, $mode);
		$result = $provider->getSpaces();

		if ($result->isSuccess())
		{
			$data = $result->getData();
			$spaces = $data['spaces'] ?? [];
			$spaces[] = $provider->getCommonSpace();

			$data['spaces'] = $spaces;
			$result->setData($data);
		}

		return $result;
	}

	private function getInvitations(int $userId, array $loadedSpaces): array
	{
		$spacesToLoad = [];
		$invitationOnlySpaces = [];
		$invitations = (new InvitationManager($userId))->getInvitations()->toArray();
		foreach ($invitations as $invitation)
		{
			if (!$this->hasSpace($loadedSpaces, $invitation->getSpaceId()))
			{
				$spacesToLoad[] = $invitation->getSpaceId();
			}
		}
		if (!empty($spacesToLoad))
		{
			$invitationOnlySpaces = (new Provider($userId))->getSpacesByIds($spacesToLoad);
		}

		return [$invitationOnlySpaces, $invitations];
	}

	/** @return array<Space> */
	private function getSpacesByIds(array $spaceIds, int $userId): array
	{
		$provider = $this->getProvider($userId, Dictionary::FILTER_MODES['all']);

		return !empty($spaceIds) ? $provider->getSpacesByIds($spaceIds): [];
	}

	/** @var array<Arrayable> $items */
	private function castItemsToArray(array $items): array
	{
		$result = [];
		foreach ($items as $item)
		{
			$result[] = $item->toArray();
		}

		return $result;
	}

	/** @var array<Space> $spaces */
	private function hasSpace(array $spaces, int $spaceId): bool
	{
		$result = false;
		foreach ($spaces as $space)
		{
			if ($space->getId() === $spaceId)
			{
				$result = true;
				break;
			}
		}

		return $result;
	}

	private function getProvider(int $userId, string $filterMode): Provider
	{
		if (!in_array($filterMode, Dictionary::FILTER_MODES))
		{
			$filterMode = Dictionary::FILTER_MODES['all'];
		}

		return (new Provider($userId, $filterMode));
	}

	private function init(): void
	{
		$this->errors = new ErrorCollection();
		$this->userId = CurrentUser::get()->getId();
	}
}