<?php

namespace Bitrix\Socialnetwork\Integration\UI\EntitySelector;

use Bitrix\Extranet\Service\ServiceContainer;
use Bitrix\HumanResources\Service\Container;
use Bitrix\HumanResources\Util\StructureHelper;
use Bitrix\Intranet\Integration\Mail\EmailUser;
use Bitrix\Intranet\Internals\InvitationTable;
use Bitrix\Intranet\Invitation;
use Bitrix\Intranet\UserAbsence;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Router;
use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Entity\Query;
use Bitrix\Main\EO_User;
use Bitrix\Main\EO_User_Collection;
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\Search\Content;
use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Collab\CollabFeature;
use Bitrix\Socialnetwork\Integration\HumanResources\EmployeeHelper;
use Bitrix\Socialnetwork\UserToGroupTable;
use Bitrix\Socialnetwork\WorkgroupSiteTable;
use Bitrix\UI\EntitySelector\BaseProvider;
use Bitrix\UI\EntitySelector\Dialog;
use Bitrix\UI\EntitySelector\Item;
use Bitrix\UI\EntitySelector\SearchQuery;

class UserProvider extends BaseProvider
{
	protected const EXTRANET_ROLES = [
		UserToGroupTable::ROLE_USER,
		UserToGroupTable::ROLE_OWNER,
		UserToGroupTable::ROLE_MODERATOR,
		UserToGroupTable::ROLE_REQUEST,
	];
	protected const MAX_USERS_IN_RECENT_TAB = 50;
	protected const SEARCH_LIMIT = 100;

	protected const ENTITY_ID = 'user';

	public function __construct(array $options = [])
	{
		parent::__construct();
		$this->prepareOptions($options);
	}

	protected function prepareOptions(array $options = []): void
	{
		if (isset($options['nameTemplate']) && is_string($options['nameTemplate']))
		{
			preg_match_all(
				'/#NAME#|#LAST_NAME#|#SECOND_NAME#|#NAME_SHORT#|#SECOND_NAME_SHORT#|\s|,/',
				urldecode($options['nameTemplate']),
				$matches,
			);

			$this->options['nameTemplate'] = implode('', $matches[0]);
		}
		else
		{
			$this->options['nameTemplate'] = \CSite::getNameFormat(false);
		}

		$this->options['analyticsSource'] = 'userProvider';
		if (isset($options['analyticsSource']))
		{
			$this->options['analyticsSource'] = $options['analyticsSource'];
		}

		if (isset($options['onlyWithEmail']) && is_bool($options['onlyWithEmail']))
		{
			$this->options['onlyWithEmail'] = $options['onlyWithEmail'];
		}

		if (isset($options['extranetUsersOnly']) && is_bool($options['extranetUsersOnly']))
		{
			$this->options['extranetUsersOnly'] = $options['extranetUsersOnly'];
		}

		if (isset($options['intranetUsersOnly']) && is_bool($options['intranetUsersOnly']))
		{
			$this->options['intranetUsersOnly'] = $options['intranetUsersOnly'];
		}

		if (isset($options['footerInviteIntranetOnly']) && is_bool($options['footerInviteIntranetOnly']))
		{
			$this->options['footerInviteIntranetOnly'] = $options['footerInviteIntranetOnly'];
		}

		$this->options['showInvitationFooter'] = true;
		if (isset($options['showInvitationFooter']) && is_bool($options['showInvitationFooter']))
		{
			$this->options['showInvitationFooter'] = $options['showInvitationFooter'];
		}

		$this->options['emailUsers'] = false;
		if (isset($options['emailUsers']) && is_bool($options['emailUsers']))
		{
			$this->options['emailUsers'] = $options['emailUsers'];
		}

		$this->options['myEmailUsers'] = true;
		if (isset($options['myEmailUsers']) && is_bool($options['myEmailUsers']))
		{
			$this->options['myEmailUsers'] = $options['myEmailUsers'];
		}

		if (isset($options['emailUsersOnly']) && is_bool($options['emailUsersOnly']))
		{
			$this->options['emailUsersOnly'] = $options['emailUsersOnly'];
		}

		$this->options['networkUsers'] = false;
		if (isset($options['networkUsers']) && is_bool($options['networkUsers']))
		{
			$this->options['networkUsers'] = $options['networkUsers'];
		}

		if (isset($options['networkUsersOnly']) && is_bool($options['networkUsersOnly']))
		{
			$this->options['networkUsersOnly'] = $options['networkUsersOnly'];
		}

		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		$this->options['showLogin'] = $intranetInstalled;
		$this->options['showEmail'] = $intranetInstalled;

		$this->options['inviteEmployeeLink'] = true;
		if (isset($options['inviteEmployeeLink']) && is_bool($options['inviteEmployeeLink']))
		{
			$this->options['inviteEmployeeLink'] = $options['inviteEmployeeLink'];
		}

		$this->options['inviteExtranetLink'] = false;
		if (isset($options['inviteExtranetLink']) && is_bool($options['inviteExtranetLink']))
		{
			$this->options['inviteExtranetLink'] = $options['inviteExtranetLink'];
		}

		$this->options['inviteGuestLink'] = false;
		if (isset($options['inviteGuestLink']) && is_bool($options['inviteGuestLink']))
		{
			$this->options['inviteGuestLink'] = $options['inviteGuestLink'];
		}

		$this->options['lockGuestLinkFeatureId'] = '';
		if (isset($options['lockGuestLinkFeatureId']) && is_string($options['lockGuestLinkFeatureId']))
		{
			$this->options['lockGuestLinkFeatureId'] = $options['lockGuestLinkFeatureId'];
		}

		$this->options['lockGuestLink'] = false;
		if (isset($options['lockGuestLink']) && is_bool($options['lockGuestLink']))
		{
			$this->options['lockGuestLink'] = $options['lockGuestLink'];
		}

		// User Whitelist
		if (isset($options['userId']))
		{
			$ids = static::prepareUserIds($options['userId']);
			if (!empty($ids))
			{
				$this->options['userId'] = $ids;
			}
		}

		// User Blacklist
		if (isset($options['!userId']))
		{
			$ids = static::prepareUserIds($options['!userId']);
			if (!empty($ids))
			{
				$this->options['!userId'] = $ids;
			}
		}

		if (isset($options['selectFields']) && is_array($options['selectFields']))
		{
			$selectFields = [];
			$allowedFields = static::getAllowedFields();
			foreach ($options['selectFields'] as $field)
			{
				if (is_string($field) && array_key_exists($field, $allowedFields))
				{
					$selectFields[] = $field;
				}
			}

			$this->options['selectFields'] = array_unique($selectFields);
		}

		$this->options['fillDialog'] = true;
		if (isset($options['fillDialog']) && is_bool($options['fillDialog']))
		{
			$this->options['fillDialog'] = $options['fillDialog'];
		}

		$this->options['maxUsersInRecentTab'] = static::MAX_USERS_IN_RECENT_TAB;
		if (isset($options['maxUsersInRecentTab']) && is_int($options['maxUsersInRecentTab']))
		{
			$this->options['maxUsersInRecentTab'] = max(
				1,
				min($options['maxUsersInRecentTab'], static::MAX_USERS_IN_RECENT_TAB),
			);
		}

		$this->options['searchLimit'] = static::SEARCH_LIMIT;
		if (isset($options['searchLimit']) && is_int($options['searchLimit']))
		{
			$this->options['searchLimit'] = max(1, min($options['searchLimit'], static::SEARCH_LIMIT));
		}
	}

	public function isAvailable(): bool
	{
		if (!$GLOBALS['USER']->isAuthorized())
		{
			return false;
		}

		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		if ($intranetInstalled)
		{
			return self::isIntranetUser() || self::isExtranetUser();
		}

		return \Bitrix\Socialnetwork\ComponentHelper::getModuleUsed();
	}

	public function shouldFillDialog(): bool
	{
		return $this->getOption('fillDialog', true);
	}

	public function getItems(array $ids): array
	{
		if (!$this->shouldFillDialog())
		{
			return [];
		}

		return $this->getUserItems([
			'userId' => $ids,
		]);
	}

	public function getSelectedItems(array $ids): array
	{
		return $this->getUserItems([
			'userId' => $ids,
			'ignoreUserWhitelist' => true,
			'activeUsers' => null, // to see fired employees
		]);
	}

	public function fillDialog(Dialog $dialog): void
	{
		if (!$this->shouldFillDialog())
		{
			return;
		}

		// Preload first 50 users ('doSearch' method has to have the same filter).
		$preloadedUsers = $this->getPreloadedUsersCollection();

		if ($preloadedUsers->count() < $this->options['maxUsersInRecentTab'])
		{
			// Turn off the user search
			$entity = $dialog->getEntity(static::ENTITY_ID);
			if ($entity)
			{
				$entity->setDynamicSearch(false);
			}
		}

		$recentUsers = new EO_User_Collection();

		// Recent Items
		$recentItems = $dialog->getRecentItems()->getEntityItems(static::ENTITY_ID);
		$recentIds = array_map('intval', array_keys($recentItems));
		$this->fillRecentUsers($recentUsers, $recentIds, $preloadedUsers);

		// Global Recent Items
		if ($recentUsers->count() < $this->options['maxUsersInRecentTab'])
		{
			$recentGlobalItems = $dialog->getGlobalRecentItems()->getEntityItems(static::ENTITY_ID);
			$recentGlobalIds = [];

			if (!empty($recentGlobalItems))
			{
				$recentGlobalIds = array_map('intval', array_keys($recentGlobalItems));
				$recentGlobalIds = array_values(array_diff($recentGlobalIds, $recentUsers->getIdList()));
				$recentGlobalIds = array_slice($recentGlobalIds, 0, $this->options['maxUsersInRecentTab'] - $recentUsers->count());
			}

			$this->fillRecentUsers($recentUsers, $recentGlobalIds, $preloadedUsers);
		}

		// The rest of preloaded users
		foreach ($preloadedUsers as $preloadedUser)
		{
			$recentUsers->add($preloadedUser);
		}

		$dialog->addRecentItems($this->makeUserItems($recentUsers));

		// Footer
		if ($this->options['showInvitationFooter'] && Loader::includeModule('intranet'))
		{
			$inviteEmployeeLink = null;
			$employeeInvitationAvailable = Invitation::canCurrentUserInvite();
			$intranetUsersOnly = $this->options['intranetUsersOnly'] ?? false;
			$footerInviteIntranetOnly = $this->options['footerInviteIntranetOnly'] ?? CollabFeature::isOn();
			$extranetInvitationAvailable = (
				ModuleManager::isModuleInstalled('extranet')
				&& Option::get('extranet', 'extranet_site')
				&& !$intranetUsersOnly
				&& !$footerInviteIntranetOnly
			);

			if (
				$this->options['inviteEmployeeLink']
				&& (
					$employeeInvitationAvailable
					|| $extranetInvitationAvailable
				)
				&& self::isIntranetUser()
			)
			{

				$inviteEmployeeLink = UrlManager::getInstance()->create('getSliderContent', [
					'c' => 'bitrix:intranet.invitation',
					'mode' => Router::COMPONENT_MODE_AJAX,
					'analyticsLabel[source]' => $this->options['analyticsSource'],
					'analyticsLabel[tool]' => 'Invitation',
					'analyticsLabel[category]' => 'invitation',
					'analyticsLabel[event]' => 'drawer_open',
					'analyticsLabel[c_section]' => $this->options['analyticsSource'],
				]);
			}

			$inviteGuestLink = null;
			if ($this->options['inviteGuestLink'] && ModuleManager::isModuleInstalled('mail') && self::isIntranetUser())
			{
				$inviteGuestLink = UrlManager::getInstance()->create('getSliderContent', [
					'c' => 'bitrix:intranet.invitation.guest',
					'mode' => Router::COMPONENT_MODE_AJAX,
				]);
			}

			if ($inviteEmployeeLink || $inviteGuestLink)
			{
				$footerOptions = [];
				if ($dialog->getFooter() === 'BX.SocialNetwork.EntitySelector.Footer')
				{
					// Footer could be set from ProjectProvider
					$footerOptions = $dialog->getFooterOptions() ?? [];
				}

				$footerOptions['inviteEmployeeLink'] = $inviteEmployeeLink;
				$footerOptions['inviteGuestLink'] = $inviteGuestLink;
				if ($inviteEmployeeLink)
				{
					$footerOptions['inviteEmployeeScope'] = ($employeeInvitationAvailable ? 'I' : '').($extranetInvitationAvailable ? 'E' : '');
				}

				if ($inviteGuestLink && $this->options['lockGuestLink'])
				{
					$footerOptions['lockGuestLink'] = true;
					$footerOptions['lockGuestLinkFeatureId'] = $this->options['lockGuestLinkFeatureId'] ?? '';
				}

				$dialog->setFooter('BX.SocialNetwork.EntitySelector.Footer', $footerOptions);
			}
		}
	}

	protected function getPreloadedUsersCollection(): EO_User_Collection
	{
		return $this->getUserCollection([
			'order' => ['ID' => 'asc'],
			'limit' => $this->options['maxUsersInRecentTab'],
		]);
	}

	private function fillRecentUsers(
		EO_User_Collection $recentUsers,
		array $recentIds,
		EO_User_Collection $preloadedUsers,
	): void
	{
		if (count($recentIds) < 1)
		{
			return;
		}

		$ids = array_values(array_diff($recentIds, $preloadedUsers->getIdList()));
		if (!empty($ids))
		{
			$users = $this->getUserCollection(['userId' => $ids]);
			foreach ($users as $user)
			{
				$preloadedUsers->add($user);
			}
		}

		foreach ($recentIds as $recentId)
		{
			$user = $preloadedUsers->getByPrimary($recentId);
			if ($user)
			{
				$recentUsers->add($user);
			}
		}
	}

	public function doSearch(SearchQuery $searchQuery, Dialog $dialog): void
	{
		$atom = '=_0-9a-z+~\'!\$&*^`|\\#%/?{}-';
		$isEmailLike = (bool)preg_match('#^['.$atom.']+(\\.['.$atom.']+)*@#i', $searchQuery->getQuery());

		if ($isEmailLike)
		{
			$items = $this->getUserItems([
				'searchByEmail' => $searchQuery->getQuery(),
				'myEmailUsers' => false,
				'limit' => $this->options['searchLimit'],
			]);
		}
		else
		{
			$items = $this->getUserItems([
				'searchQuery' => $searchQuery->getQuery(),
				'limit' => $this->options['searchLimit'],
			]);
		}

		$limitExceeded = ($this->options['searchLimit'] <= count($items));
		if ($limitExceeded)
		{
			$searchQuery->setCacheable(false);
		}

		$dialog->addItems($items);
	}

	public function handleBeforeItemSave(Item $item): void
	{
		if ($item->getEntityType() === 'email')
		{
			$user = UserTable::getById($item->getId())->fetchObject();
			if ($user && $user->getExternalAuthId() === 'email' && Loader::includeModule('intranet'))
			{
				EmailUser::invite($user->getId());
			}
		}
	}

	public function getUserCollection(array $options = []): EO_User_Collection
	{
		$dialogOptions = $this->getOptions();
		$options = array_merge($dialogOptions, $options);

		$ignoreUserWhitelist = isset($options['ignoreUserWhitelist']) && $options['ignoreUserWhitelist'] === true;
		if (!empty($dialogOptions['userId']) && !$ignoreUserWhitelist)
		{
			$options['userId'] = $dialogOptions['userId'];
		}

		return static::getUsers($options);
	}

	public function getUserItems(array $options = []): array
	{
		return $this->makeUserItems($this->getUserCollection($options), $options);
	}

	public function makeUserItems(EO_User_Collection $users, array $options = []): array
	{
		return self::makeItems($users, array_merge($this->getOptions(), $options));
	}

	public static function isIntranetUser(int $userId = null): bool
	{
		return self::hasUserRole($userId, 'intranet');
	}

	public static function isExtranetUser(int $userId = null): bool
	{
		return self::hasUserRole($userId, 'extranet');
	}

	public static function getCurrentUserId(): int
	{
		return is_object($GLOBALS['USER']) ? (int)$GLOBALS['USER']->getId() : 0;
	}

	private static function hasUserRole(?int $userId, string $role): bool
	{
		static $roles = [
			'intranet' => [],
			'extranet' => [],
		];

		if (!isset($roles[$role]) || !ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		if (is_null($userId))
		{
			$userId = self::getCurrentUserId();
			if ($userId <= 0)
			{
				return false;
			}
		}

		if (
			$userId === self::getCurrentUserId()
			&& \CSocNetUser::isCurrentUserModuleAdmin()
		)
		{
			return true;
		}

		if (isset($roles[$role][$userId]))
		{
			return $roles[$role][$userId];
		}

		$cacheId = 'UserRole:'.$role;
		$cachePath = '/external_user_info/'.substr(md5($userId),-2).'/'.$userId.'/';
		$cache = Application::getInstance()->getCache();
		$ttl = 2592000; // 1 month

		if ($cache->initCache($ttl, $cacheId, $cachePath))
		{
			$roles[$role][$userId] = (bool)$cache->getVars();
		}
		else
		{
			$cache->startDataCache();

			$taggedCache = Application::getInstance()->getTaggedCache();
			$taggedCache->startTagCache($cachePath);
			$taggedCache->registerTag('USER_NAME_'.$userId);
			$taggedCache->endTagCache();

			$filter = [
				'=ID' => $userId,
				'=IS_REAL_USER' => true,
			];

			if ($role === 'intranet')
			{
				$filter['!UF_DEPARTMENT'] = false;
			}
			else if ($role === 'extranet')
			{
				$filter['UF_DEPARTMENT'] = false;
			}

			$roles[$role][$userId] =
				UserTable::getList(['select' => ['ID'], 'filter' => $filter])
					->fetchCollection()->count() === 1
			;

			$cache->endDataCache($roles[$role][$userId]);
		}

		return $roles[$role][$userId];
	}

	public static function isIntegrator(int $userId = null): bool
	{
		static $integrators;

		if ($integrators === null)
		{
			$integrators = [];
			if (Loader::includeModule('bitrix24'))
			{
				$integrators = array_fill_keys(\Bitrix\Bitrix24\Integrator::getIntegratorsId(), true);
			}
		}

		if (is_null($userId))
		{
			$userId = self::getCurrentUserId();
			if ($userId <= 0)
			{
				return false;
			}
		}

		return isset($integrators[$userId]);
	}

	public static function getAllowedFields(): array
	{
		static $fields = null;

		if ($fields !== null)
		{
			return $fields;
		}

		$fields = [
			'lastName' => 'LAST_NAME',
			'name' => 'NAME',
			'secondName' => 'SECOND_NAME',
			'login' => 'LOGIN',
			'email' => 'EMAIL',
			'title' => 'TITLE',
			'position', 'WORK_POSITION',
			'lastLogin' => 'LAST_LOGIN',
			'dateRegister' => 'DATE_REGISTER',
			'lastActivityDate' => 'LAST_ACTIVITY_DATE',
			'online' => 'IS_ONLINE',
			'profession' => 'PERSONAL_PROFESSION',
			'www' => 'PERSONAL_WWW',
			'birthday' => 'PERSONAL_BIRTHDAY',
			'icq' => 'PERSONAL_ICQ',
			'phone' => 'PERSONAL_PHONE',
			'fax' => 'PERSONAL_FAX',
			'mobile' => 'PERSONAL_MOBILE',
			'pager' => 'PERSONAL_PAGER',
			'street' => 'PERSONAL_STREET',
			'city' => 'PERSONAL_CITY',
			'state' => 'PERSONAL_STATE',
			'zip' => 'PERSONAL_ZIP',
			'mailbox' => 'PERSONAL_MAILBOX',
			'country' => 'PERSONAL_COUNTRY',
			'timeZoneOffset' => 'TIME_ZONE_OFFSET',
			'company' => 'WORK_COMPANY',
			'workPhone' => 'WORK_PHONE',
			'workDepartment' => 'WORK_DEPARTMENT',
			'workPosition' => 'WORK_POSITION',
			'workCity' => 'WORK_CITY',
			'workCountry' => 'WORK_COUNTRY',
			'workStreet' => 'WORK_STREET',
			'workState' => 'WORK_STATE',
			'workZip' => 'WORK_ZIP',
			'workMailbox' => 'WORK_MAILBOX',
		];

		foreach ($fields as $id => $dbName)
		{
			if (mb_strpos($dbName, 'PERSONAL_') === 0)
			{
				$fields['personal' . ucfirst($id)] = $dbName;
			}

			$fields[$dbName] = $dbName;
		}

		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		if ($intranetInstalled)
		{
			$userFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('USER');
			$allowedUserFields = [
				'ufPhoneInner' => 'UF_PHONE_INNER',
				'ufDistrict' => 'UF_DISTRICT',
				'ufSkype' => 'UF_SKYPE',
				'ufSkypeLink' => 'UF_SKYPE_LINK',
				'ufZoom' => 'UF_ZOOM',
				'ufTwitter' => 'UF_TWITTER',
				'ufFacebook' => 'UF_FACEBOOK',
				'ufLinkedin' => 'UF_LINKEDIN',
				'ufXing' => 'UF_XING',
				'ufWebSites' => 'UF_WEB_SITES',
				'ufSkills' => 'UF_SKILLS',
				'ufInterests' => 'UF_INTERESTS',
				'ufEmploymentDate' => 'UF_EMPLOYMENT_DATE',
			];

			foreach ($allowedUserFields as $id => $dbName)
			{
				if (array_key_exists($dbName, $userFields))
				{
					$fields[$id] = $dbName;
					$fields[$dbName] = $dbName;
				}
			}
		}

		return $fields;
	}

	public static function getUsers(array $options = []): EO_User_Collection
	{
		$query = static::getQuery($options);
		//echo '<pre>'.$query->getQuery().'</pre>';

		$result = $query->exec();

		return $result->fetchCollection();
	}

	protected static function getQuery(array $options = []): Query
	{
		$selectFields = [
			'ID', 'ACTIVE', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'TITLE',
			'PERSONAL_GENDER', 'PERSONAL_PHOTO', 'WORK_POSITION',
			'CONFIRM_CODE', 'EXTERNAL_AUTH_ID',
		];

		if (isset($options['selectFields']) && is_array($options['selectFields']))
		{
			$allowedFields = static::getAllowedFields();
			foreach ($options['selectFields'] as $field)
			{
				if (is_string($field) && array_key_exists($field, $allowedFields))
				{
					$selectFields[] = $allowedFields[$field];
				}
			}
		}

		$query = UserTable::query();
		$query->setSelect(array_unique($selectFields));

		$intranetInstalled = ModuleManager::isModuleInstalled('intranet');
		if ($intranetInstalled)
		{
			$query->addSelect('UF_DEPARTMENT');
		}

		$activeUsers = array_key_exists('activeUsers', $options) ? $options['activeUsers'] : true;
		if (is_bool($activeUsers))
		{
			$query->where('ACTIVE', $activeUsers ? 'Y' : 'N');
		}

		if (isset($options['onlyWithEmail']) && is_bool(isset($options['onlyWithEmail'])))
		{
			$query->addFilter(($options['onlyWithEmail'] ? '!' : '').'EMAIL', false);
		}

		if (isset($options['invitedUsers']) && is_bool(isset($options['invitedUsers'])))
		{
			$query->addFilter(($options['invitedUsers'] ? '!' : '').'CONFIRM_CODE', false);
		}

		if (!empty($options['searchQuery']) && is_string($options['searchQuery']))
		{
			$query->registerRuntimeField(
				new Reference(
					'USER_INDEX',
					\Bitrix\Main\UserIndexTable::class,
					Join::on('this.ID', 'ref.USER_ID'),
					['join_type' => 'INNER'],
				),
			);

			$query->whereMatch(
				'USER_INDEX.SEARCH_USER_CONTENT',
				Filter\Helper::matchAgainstWildcard(
					Content::prepareStringToken($options['searchQuery']), '*', 1,
				),
			);
		}
		else if (!empty($options['searchByEmail']) && is_string($options['searchByEmail']))
		{
			$query->whereLike('EMAIL', $options['searchByEmail'].'%');
		}

		$currentUserId = (
		!empty($options['currentUserId']) && is_int($options['currentUserId'])
			? $options['currentUserId']
			: $GLOBALS['USER']->getId()
		);

		$isIntranetUser = $intranetInstalled && self::isIntranetUser($currentUserId);
		if ($intranetInstalled)
		{
			$emptyValue = serialize([]);
			$emptyValue2 = serialize([0]);

			$query->registerRuntimeField(new ExpressionField(
				'IS_INTRANET_USER',
				'CASE WHEN
					(%s IS NOT NULL AND %s != \'' . $emptyValue . '\' AND %s != \'' . $emptyValue2 . '\') AND
					(%s IS NULL OR %s NOT IN (\'' . implode('\', \'', UserTable::getExternalUserTypes()) . '\'))
					THEN \'Y\'
					ELSE \'N\'
				END',
				['UF_DEPARTMENT', 'UF_DEPARTMENT', 'UF_DEPARTMENT', 'EXTERNAL_AUTH_ID', 'EXTERNAL_AUTH_ID']),
			);

			$query->registerRuntimeField(new ExpressionField(
				'IS_EXTRANET_USER',
				'CASE WHEN
					(%s IS NULL OR %s = \'' . $emptyValue . '\' OR %s = \'' . $emptyValue2 . '\') AND
					(%s IS NULL OR %s NOT IN (\'' . implode('\', \'', UserTable::getExternalUserTypes()) . '\'))
					 THEN \'Y\'
					 ELSE \'N\'
				END',
				['UF_DEPARTMENT', 'UF_DEPARTMENT', 'UF_DEPARTMENT', 'EXTERNAL_AUTH_ID', 'EXTERNAL_AUTH_ID']),
			);

			$query->registerRuntimeField(
				new Reference(
					'INVITATION',
					InvitationTable::class,
					Join::on('this.ID', 'ref.USER_ID')->where('ref.ORIGINATOR_ID', $currentUserId),
					['join_type' => 'LEFT'],
				),
			);

			$extranetUsersQuery = (empty($options['searchByEmail']) ? self::getExtranetUsersQuery($currentUserId) : null);

			$intranetUsersOnly = isset($options['intranetUsersOnly']) && $options['intranetUsersOnly'] === true;
			$extranetUsersOnly = isset($options['extranetUsersOnly']) && $options['extranetUsersOnly'] === true;
			$emailUsersOnly = isset($options['emailUsersOnly']) && $options['emailUsersOnly'] === true;
			$networkUsersOnly = isset($options['networkUsersOnly']) && $options['networkUsersOnly'] === true;

			$emailUsers =
				isset($options['emailUsers']) && is_bool($options['emailUsers']) ? $options['emailUsers'] : true
			;

			$myEmailUsers =
				isset($options['myEmailUsers']) && is_bool($options['myEmailUsers']) && $options['myEmailUsers']
			;

			$networkUsers =
				!(isset($options['networkUsers']) && is_bool($options['networkUsers'])) || $options['networkUsers']
			;

			if ($isIntranetUser)
			{
				if (isset($options['departmentId']) && is_int($options['departmentId']))
				{
					$query->addFilter('UF_DEPARTMENT', $options['departmentId']);
				}

				if ($emailUsersOnly)
				{
					$query->where('EXTERNAL_AUTH_ID', 'email');
					if ($myEmailUsers)
					{
						$query->whereNotNull('INVITATION.ID');
					}
				}
				else if ($networkUsersOnly)
				{
					$query->where('EXTERNAL_AUTH_ID', 'replica');
				}
				else if ($intranetUsersOnly)
				{
					$query->where('IS_INTRANET_USER', 'Y');
				}
				else if ($extranetUsersOnly)
				{
					$query->where('IS_EXTRANET_USER', 'Y');
					if ($extranetUsersQuery)
					{
						$query->whereIn('ID', $extranetUsersQuery);
					}
				}
				else
				{
					$filter = Query::filter()->logic('or');

					if (
						empty($options['searchByEmail'])
						&& !\CSocNetUser::isCurrentUserModuleAdmin()
					)
					{
						$filter->where('IS_INTRANET_USER', 'Y');
					}
					else
					{
						$filter->addCondition(Query::filter()
							->logic('or')
							->whereNotIn('EXTERNAL_AUTH_ID', UserTable::getExternalUserTypes())
							->whereNull('EXTERNAL_AUTH_ID'),
						);
					}

					if ($emailUsers === true)
					{
						if ($myEmailUsers)
						{
							$filter->addCondition(Query::filter()
								->where('EXTERNAL_AUTH_ID', 'email')
								->whereNotNull('INVITATION.ID'),
							);
						}
						else
						{
							$filter->where('EXTERNAL_AUTH_ID', 'email');
						}
					}

					if ($networkUsers === true)
					{
						$filter->where('EXTERNAL_AUTH_ID', 'replica');
					}

					if ($extranetUsersQuery)
					{
						$filter->whereIn('ID', $extranetUsersQuery);
						$filter->addCondition(Query::filter()
							->where(Query::filter()
								->logic('or')
								->whereNull('EXTERNAL_AUTH_ID')
								->whereNot('EXTERNAL_AUTH_ID', 'email'),
							)
							->whereNotNull('INVITATION.ID'),
						);
					}

					$query->where($filter);
				}
			}
			else
			{
				if ($intranetUsersOnly)
				{
					$query->where('IS_INTRANET_USER', 'Y');
				}
				else if ($extranetUsersOnly)
				{
					$query->where('IS_EXTRANET_USER', 'Y');
				}
				else
				{
					$query->addFilter('!=EXTERNAL_AUTH_ID', UserTable::getExternalUserTypes());
				}

				if ($extranetUsersQuery)
				{
					$query->whereIn('ID', $extranetUsersQuery);
				}
				else
				{
					$query->where(new ExpressionField('EMPTY_LIST', '1'), '!=', 1);
				}
			}
		}
		else
		{
			$query->addFilter('!=EXTERNAL_AUTH_ID', UserTable::getExternalUserTypes());
		}

		$userIds = self::prepareUserIds($options['userId'] ?? []);
		$notUserIds = self::prepareUserIds($options['!userId'] ?? []);

		// User Whitelist
		if (!empty($userIds))
		{
			$query->whereIn('ID', $userIds);
		}

		// User Blacklist
		if (!empty($notUserIds))
		{
			$query->whereNotIn('ID', $notUserIds);
		}

		if (
			empty($options['order'])
			&&
			($usersCount = count($userIds)) > 1
		)
		{
			$helper = Application::getConnection()->getSqlHelper();
			$expression = $helper->getOrderByIntField('%s', $userIds, false);
			$field = new ExpressionField(
				'ID_SEQUENCE',
				$expression,
				array_fill(0, $usersCount, 'ID'),
			);
			$query
				->registerRuntimeField($field)
				->setOrder($field->getName());
		}
		elseif (!empty($options['order']) && is_array($options['order']))
		{
			$query->setOrder($options['order']);
		}
		else
		{
			$query->setOrder(['LAST_NAME' => 'asc']);
		}

		if (isset($options['limit']) && is_int($options['limit']))
		{
			$query->setLimit($options['limit']);
		}
		elseif (empty($userIds)) // no limit if we filter users by ids
		{
			$query->setLimit(100);
		}

		return $query;
	}

	private static function prepareUserIds($items): array
	{
		$ids = [];
		if (is_array($items) && !empty($items))
		{
			foreach ($items as $id)
			{
				if ((int)$id > 0)
				{
					$ids[] = (int)$id;
				}
			}

			$ids = array_unique($ids);
		}
		else if (!is_array($items) && (int)$items > 0)
		{
			$ids = [(int)$items];
		}

		return $ids;
	}

	protected static function getExtranetUsersQuery(int $currentUserId): ?Query
	{
		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$extranetSiteId = ($extranetSiteId && ModuleManager::isModuleInstalled('extranet') ? $extranetSiteId : false);

		if (
			!$extranetSiteId
			|| \CSocNetUser::isCurrentUserModuleAdmin()
		)
		{
			return null;
		}

		$query = UserToGroupTable::query();
		$query->addSelect(new ExpressionField('DISTINCT_USER_ID', 'DISTINCT %s', 'USER.ID'));
		// $query->where('ROLE', '<=', UserToGroupTable::ROLE_USER);
		$query->whereIn('ROLE', self::EXTRANET_ROLES);
		$query->registerRuntimeField(
			new Reference(
				'GS',
				WorkgroupSiteTable::class,
				Join::on('ref.GROUP_ID', 'this.GROUP_ID')->where('ref.SITE_ID', $extranetSiteId),
				['join_type' => 'INNER'],
			),
		);

		$query->registerRuntimeField(
			new Reference(
				'UG_MY',
				UserToGroupTable::class,
				Join::on('ref.GROUP_ID', 'this.GROUP_ID')
					->where('ref.USER_ID', $currentUserId)
					->whereIn('ref.ROLE', self::EXTRANET_ROLES),
				['join_type' => 'INNER'],
			),
		);

		return $query;
	}

	public static function getUser(int $userId, array $options = []): ?EO_User
	{
		$options['userId'] = $userId;
		$users = static::getUsers($options);

		return $users->count() ? $users->getAll()[0] : null;
	}

	public static function makeItems(EO_User_Collection $users, array $options = []): array
	{
		$result = [];
		$options['departmentMap'] = EmployeeHelper::employeesToDepartment($users);

		foreach ($users as $user)
		{
			$result[] = static::makeItem($user, $options);
		}

		return $result;
	}

	public static function makeItem(EO_User $user, array $options = []): Item
	{
		$customData = [];
		foreach (['name', 'lastName', 'secondName', 'email', 'login'] as $field)
		{
			if (!empty($user->{'get'.$field}()))
			{
				$customData[$field] = $user->{'get'.$field}();
			}
		}

		if (!empty($user->getPersonalGender()))
		{
			$customData['gender'] = $user->getPersonalGender();
		}

		if (!empty($user->getWorkPosition()))
		{
			$customData['position'] = $user->getWorkPosition();
		}

		$userType = self::getUserType($user);

		if ($user->getConfirmCode() && in_array($userType, ['employee', 'integrator']))
		{
			$customData['invited'] = true;
		}

		if (isset($options['selectFields']) && is_array($options['selectFields']))
		{
			$userData = $user->collectValues();
			$allowedFields = static::getAllowedFields();
			foreach ($options['selectFields'] as $field)
			{
				if (!is_string($field))
				{
					continue;
				}

				$dbName = $allowedFields[$field] ?? null;
				$value = $userData[$dbName] ?? null;
				if (!empty($value))
				{
					if ($field === 'country' || $field === 'workCountry')
					{
						$value = \Bitrix\Main\UserUtils::getCountryValue(['VALUE' => $value]);
					}

					$customData[$field] = $value;
				}
			}
		}

		if (isset($options['showLogin']) && $options['showLogin'] === false)
		{
			unset($customData['login']);
		}

		if (isset($options['showEmail']) && $options['showEmail'] === false)
		{
			unset($customData['email']);
		}

		if (!empty($options['departmentMap']) && isset($options['departmentMap'][$user->getId()]))
		{
			$customData['nodeId'] = $options['departmentMap'][$user->getId()];
		}

		$item = new Item([
			'id' => $user->getId(),
			'entityId' => static::ENTITY_ID,
			'entityType' => $userType,
			'title' => self::formatUserName($user, $options),
			'avatar' => self::makeUserAvatar($user),
			'customData' => $customData,
			'tabs' => static::getTabsNames(),
		]);

		if (($userType === 'employee' || $userType === 'integrator') && Loader::includeModule('intranet'))
		{
			$isOnVacation = UserAbsence::isAbsentOnVacation($user->getId());
			if ($isOnVacation)
			{
				$item->getCustomData()->set('isOnVacation', true);
			}
		}

		return $item;
	}

	protected static function getTabsNames(): array
	{
		return [static::ENTITY_ID];
	}

	public static function getUserType(EO_User $user): string
	{
		$type = null;
		if (!$user->getActive())
		{
			$type = 'inactive';
		}
		else if ($user->getExternalAuthId() === 'email')
		{
			$type = 'email';
		}
		else if ($user->getExternalAuthId() === 'replica')
		{
			$type = 'network';
		}
		else if (!in_array($user->getExternalAuthId(), UserTable::getExternalUserTypes()))
		{
			if (ModuleManager::isModuleInstalled('intranet') || ModuleManager::isModuleInstalled('extranet'))
			{
				if (self::isIntegrator($user->getId()))
				{
					$type = 'integrator';
				}
				else if (
					ModuleManager::isModuleInstalled('extranet')
					&& ServiceContainer::getInstance()->getCollaberService()->isCollaberById($user->getId())
				)
				{
					$type = 'collaber';
				}
				else if (ModuleManager::isModuleInstalled('intranet'))
				{
					$ufDepartment = $user->getUfDepartment();
					if (
						empty($ufDepartment)
						|| (is_array($ufDepartment) && count($ufDepartment) === 1 && (int)$ufDepartment[0] === 0)
					)
					{
						$type = 'extranet';
					}
					else
					{
						$type = 'employee';
					}
				}
			}
			else
			{
				$type = 'user';
			}
		}
		else
		{
			$type = 'unknown';
		}

		return $type;
	}

	public static function formatUserName(EO_User $user, array $options = []): string
	{
		return \CUser::formatName(
			!empty($options['nameTemplate']) ? $options['nameTemplate'] : \CSite::getNameFormat(false),
			[
				'NAME' => $user->getName(),
				'LAST_NAME' => $user->getLastName(),
				'SECOND_NAME' => $user->getSecondName(),
				'LOGIN' => $user->getLogin(),
				'EMAIL' => $user->getEmail(),
				'TITLE' => $user->getTitle(),
			],
			true,
			false,
		);
	}

	public static function makeUserAvatar(EO_User $user): ?string
	{
		if (empty($user->getPersonalPhoto()))
		{
			return null;
		}

		$avatar = \CFile::resizeImageGet(
			$user->getPersonalPhoto(),
			['width' => 100, 'height' => 100],
			BX_RESIZE_IMAGE_EXACT,
			false,
		);

		return !empty($avatar['src']) ? $avatar['src'] : null;
	}

	public static function getUserUrl(?int $userId = null): string
	{

		return
			self::isExtranetUser($userId)
				? self::getExtranetUserUrl($userId)
				: self::getIntranetUserUrl($userId)
		;
	}

	public static function getExtranetUserUrl(?int $userId = null): string
	{
		$extranetSiteId = Option::get('extranet', 'extranet_site');
		$userPage = Option::get('socialnetwork', 'user_page', false, $extranetSiteId);
		if (!$userPage)
		{
			$userPage = '/extranet/contacts/personal/';
		}

		return $userPage.'user/' . ($userId !== null ? $userId : '#id#') . '/';
	}

	public static function getIntranetUserUrl(?int $userId = null): string
	{
		$userPage = Option::get('socialnetwork', 'user_page', false, SITE_ID);
		if (!$userPage)
		{
			$userPage = SITE_DIR.'company/personal/';
		}

		return $userPage.'user/' . ($userId !== null ? $userId : '#id#') . '/';
	}
}
