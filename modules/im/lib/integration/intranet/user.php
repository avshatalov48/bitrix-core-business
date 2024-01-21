<?php
namespace Bitrix\Im\Integration\Intranet;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\UserTable;

class User
{
	static $isEmployee = [];

	const INVITE_MAX_USER_NOTIFY = 50;

	public static function canInvite(): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		return \Bitrix\Intranet\Invitation::canListDelete();
	}
	public static function onInviteLinkCopied(\Bitrix\Main\Event $event): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		$userId = (int)$event->getParameter('userId');

		return self::sendMessageToGeneralChat($userId, [
			'MESSAGE' => Loc::getMessage('IM_INT_USR_LINK_COPIED', [
				'#USER_NAME#' => self::getUserBlock($userId)
			]),
			'SYSTEM' => 'Y'
		]);
	}

	public static function onUserInvited(\Bitrix\Main\Event $event): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		$originatorId = (int)$event->getParameter('originatorId');
		$users = (array)$event->getParameter('userId');

		if (!self::isEmployee($originatorId))
		{
			return false;
		}

		$userForSend = [];
		$result = \Bitrix\Intranet\UserTable::getList([
			'filter' => [
				'=ID' => $users
			],
			'select' => ['ID', 'USER_TYPE', 'EMAIL']

		]);
		while ($row = $result->fetch())
		{
			if ($row['USER_TYPE'] === 'employee')
			{
				$userForSend[] = [
					'ID' => $row['ID'],
					'INVITED' => [
						'originator_id' => $originatorId,
						'can_resend' => !empty($row['EMAIL'])
					]
				];
			}
		}

		if (empty($userForSend))
		{
			return false;
		}

		self::sendInviteEvent($userForSend);

		$userForSend = array_map(function($user) {
			return self::getUserBlock($user['ID']);
		}, $userForSend);

		return self::sendMessageToGeneralChat($originatorId, [
			'MESSAGE' => Loc::getMessage('IM_INT_USR_INVITE_USERS', [
				'#USER_NAME#' => self::getUserBlock($originatorId),
				'#USERS#' => implode(', ', $userForSend)
			]),
			'SYSTEM' => 'Y',
			'PUSH' => 'N'
		]);
	}

	public static function onUserAdded(\Bitrix\Main\Event $event): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		$originatorId = (int)$event->getParameter('originatorId');
		$users = (array)$event->getParameter('userId');

		if (!self::isEmployee($originatorId))
		{
			return false;
		}

		$userForSend = [];
		$result = \Bitrix\Intranet\UserTable::getList([
			'filter' => [
				'=ID' => $users
			],
			'select' => ['ID', 'USER_TYPE', 'EMAIL']

		]);
		while ($row = $result->fetch())
		{
			if ($row['USER_TYPE'] === 'employee')
			{
				$userForSend[] = [
					'ID' => $row['ID'],
					'INVITED' => [
						'originator_id' => $originatorId,
						'can_resend' => !empty($row['EMAIL'])
					]
				];
			}
		}

		self::sendInviteEvent($userForSend);

		$users = array_map(function($userId) {
			return self::getUserBlock($userId);
		}, $users);

		return self::sendMessageToGeneralChat($originatorId, [
			'MESSAGE' => Loc::getMessage('IM_INT_USR_REGISTER_USERS', [
				'#USER_NAME#' => self::getUserBlock($originatorId),
				'#USERS#' => implode(', ', $users)
			]),
			'SYSTEM' => 'Y',
			'PUSH' => 'N'
		]);
	}

	public static function onUserAdminRights(\Bitrix\Main\Event $event): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		if (!\COption::GetOptionString("im", "general_chat_message_admin_rights", true))
		{
			return false;
		}

		$originatorId = (int)$event->getParameter('originatorId');
		$users = (array)$event->getParameter('userId');
		$type = (string)$event->getParameter('type');

		$users = array_map(function($userId) {
			return self::getUserBlock($userId);
		}, $users);

		$originatorGender = 'M';
		if ($originatorId > 0)
		{
			$dbUser = \CUser::GetList('', '', ['ID_EQUAL_EXACT' => $originatorId], array('FIELDS' => ['PERSONAL_GENDER']));
			if ($user = $dbUser->Fetch())
			{
				$originatorGender = $user["PERSONAL_GENDER"] == 'F'? 'F': 'M';
			}
		}

		$messId = (
			$type === 'setAdminRigths'
				? 'IM_INT_USR_SET_ADMIN_RIGTHS_'.$originatorGender
				: 'IM_INT_USR_REMOVE_ADMIN_RIGTHS_'.$originatorGender
		);

		return self::sendMessageToGeneralChat($originatorId, [
			'MESSAGE' => Loc::getMessage($messId, [
				'#USER_NAME#' => self::getUserBlock($originatorId),
				'#USERS#' => implode(', ', $users)
			]),
			'SYSTEM' => 'Y'
		]);

	}

	public static function onInviteSend(array $params): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		$userId = (int)$params['ID'];

		if (!self::isEmployee($userId))
		{
			return false;
		}

		\CIMContactList::SetRecent(['ENTITY_ID' => $userId]);

		return true;
	}

	public static function onInviteAccepted(array $params): bool
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return true;
		}

		$userData = $params['user_fields'];

		if (in_array($userData['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes()))
		{
			return true;
		}

		if ($userData['LAST_LOGIN'])
		{
			return true;
		}

		$userId = (int)$userData['ID'];
		if ($userData['LAST_ACTIVITY_DATE'])
		{
			return true;
		}

		if (!self::isEmployee($userId))
		{
			return false;
		}

		\CUser::SetLastActivityDate($userId);

		\CIMContactList::SetRecent(Array('ENTITY_ID' => $userId));

		if (self::isCountOfUsersExceededForPersonalNotify())
		{
			if (!\CIMChat::GetGeneralChatAutoMessageStatus(\CIMChat::GENERAL_MESSAGE_TYPE_JOIN))
			{
				return false;
			}

			return self::sendMessageToGeneralChat($userId, [
				"MESSAGE" => Loc::getMessage('IM_INT_USR_JOIN_GENERAL_2'),
				"PARAMS" => [
					"CODE" => 'USER_JOIN_GENERAL',
				]
			]);
		}

		self::sendInviteEvent([[
			'ID' => $userId,
			'INVITED' => false
		]]);

		$orm = \Bitrix\Main\UserTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=ACTIVE' => 'Y',
				'=IS_REAL_USER' => 'Y',
				'!=UF_DEPARTMENT' => false
			]
		]);
		while($row = $orm->fetch())
		{
			if ($row['ID'] == $userId)
			{
				continue;
			}

			$viewCommonUsers = (bool)\CIMSettings::GetSetting(\CIMSettings::SETTINGS, 'viewCommonUsers', $row['ID']);
			if (!$viewCommonUsers)
			{
				continue;
			}

			\CIMMessage::Add([
				"TO_USER_ID" => $row['ID'],
				"FROM_USER_ID" => $userId,
				"MESSAGE" => Loc::getMessage('IM_INT_USR_JOIN_2'),
				"SYSTEM" => 'Y',
				"RECENT_SKIP_AUTHOR" => 'Y',
				"MESSAGE_OUT" => IM_MAIL_SKIP,
				"PARAMS" => [
					"CODE" => 'USER_JOIN',
				],
			]);
		}

		return true;
	}

	private static function sendInviteEvent(array $users): bool
	{
		if (!\Bitrix\Main\Loader::includeModule('pull'))
		{
			return false;
		}

		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return false;
		}

		$onlineUsers = \Bitrix\Im\Helper::getOnlineIntranetUsers();
		foreach ($users as $user)
		{
			\Bitrix\Pull\Event::add($onlineUsers, [
				'module_id' => 'im',
				'command' => 'userInvite',
				'expiry' => 3600,
				'params' => [
					'userId' => $user['ID'],
					'invited' => $user['INVITED'],
					'user' => \Bitrix\Im\User::getInstance($user['ID'])->getFields(),
					'date' => new DateTime(),
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	private static function sendMessageToGeneralChat(int $fromUserId, array $params): bool
	{
		$chatId = \CIMChat::GetGeneralChatId();
		if (!$chatId)
			return false;

		$params = array_merge($params, [
			"TO_CHAT_ID" =>  $chatId,
			"FROM_USER_ID" => $fromUserId,
			"MESSAGE_OUT" => IM_MAIL_SKIP,
			"SKIP_USER_CHECK" => 'Y',
		]);

		$result = \CIMChat::AddMessage($params);

		return $result !== false;
	}

	private static function getUserBlock(int $userId): string
	{
		return '[USER='.$userId.'][/USER]';
	}

	private static function isEmployee(int $userId): bool
	{
		if (isset(self::$isEmployee[$userId]))
		{
			return self::$isEmployee[$userId];
		}

		if (!\Bitrix\Main\Loader::includeModule('intranet'))
		{
			return false;
		}

		$result = \Bitrix\Intranet\UserTable::getList([
			'filter' => [
				'=ID' => $userId
			],
			'select' => ['ID', 'USER_TYPE']

		])->fetch();

		self::$isEmployee[$userId] = $result['USER_TYPE'] === 'employee';

		return self::$isEmployee[$userId];
	}

	public static function getBirthdayForToday()
	{
		if (!\Bitrix\Main\ModuleManager::isModuleInstalled('intranet'))
		{
			return [];
		}

		$option = Option::get('im', 'contact_list_birthday');
		if ($option === 'none' || \Bitrix\Im\User::getInstance()->isExtranet())
		{
			return [];
		}

		global $USER;

		$today = (new DateTime())->format('m-d');
		if ($option === 'department')
		{
			$cacheId = 'birthday_'.$today.'_'.$USER->GetID();
		}
		else
		{
			$cacheId = 'birthday_'.$today;
		}

		$cache = \Bitrix\Main\Data\Cache::createInstance();
		if($cache->initCache(86400, $cacheId, '/bx/im/birthday/'))
		{
			return $cache->getVars();
		}

		$user = \CUser::getById($USER->GetId())->Fetch();

		$filter = [
			'=ACTIVE' => 'Y',
			'=BIRTHDAY_DATE' => $today,
			'=IS_REAL_USER' => true,
		];
		if ($option === 'department')
		{
			$filter['=UF_DEPARTMENT'] = $user['UF_DEPARTMENT'];
		}
		else
		{
			$filter['!=UF_DEPARTMENT'] = false;
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$result = [];
		$users = UserTable::getList([
			'filter' => $filter,
			'select' => ['ID'],
			'runtime' => [
				new ExpressionField('BIRTHDAY_DATE', str_replace('PERSONAL_BIRTHDAY', '%s', str_replace('%', '%%', $helper->formatDate('MM-DD', 'PERSONAL_BIRTHDAY'))), 'PERSONAL_BIRTHDAY')
			],
			'limit' => 100,
		])->fetchAll();

		foreach ($users as $user)
		{
			$result[] = \Bitrix\Im\User::getInstance($user['ID'])->getArray(['SKIP_ONLINE' => 'Y', 'JSON' => 'Y']);
		}

		$cache->forceRewriting(true);
		$cache->startDataCache();
		$cache->endDataCache($result);

		return $result;
	}

	private static function isCountOfUsersExceededForPersonalNotify(): bool
	{
		$count = UserTable::query()
			->setSelect(['ID'])
			->where('ACTIVE', true)
			->where('IS_REAL_USER', true)
			->whereNotNull('LAST_LOGIN')
			->setLimit(self::INVITE_MAX_USER_NOTIFY + 1)
			->fetchCollection()
			->count()
		;

		return $count > self::INVITE_MAX_USER_NOTIFY;
	}

	public static function registerEventHandler()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandlerCompatible('main', 'OnAfterUserAuthorize', 'im', self::class, 'onInviteAccepted');
		$eventManager->registerEventHandlerCompatible('intranet', 'OnRegisterUser', 'im', self::class, 'onInviteSend');
		$eventManager->registerEventHandler('intranet', 'OnCopyRegisterUrl', 'im', self::class, 'onInviteLinkCopied');
		$eventManager->registerEventHandler('intranet', 'onUserInvited', 'im', self::class, 'onUserInvited');
		$eventManager->registerEventHandler('intranet', 'onUserAdded', 'im', self::class, 'onUserAdded');
		$eventManager->registerEventHandler('intranet', 'onUserAdminRights', 'im', self::class, 'onUserAdminRights');
	}

	public static function unRegisterEventHandler()
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler('main', 'OnAfterUserAuthorize', 'im', self::class, 'onInviteAccepted');
		$eventManager->unRegisterEventHandler('intranet', 'OnRegisterUser', 'im', self::class, 'onInviteSend');
		$eventManager->unRegisterEventHandler('intranet', 'OnCopyRegisterUrl', 'im', self::class, 'onInviteLinkCopied');
		$eventManager->unRegisterEventHandler('intranet', 'onUserInvited', 'im', self::class, 'onUserInvited');
		$eventManager->unRegisterEventHandler('intranet', 'onUserAdded', 'im', self::class, 'onUserAdded');
		$eventManager->unRegisterEventHandler('intranet', 'onUserAdminRights', 'im', self::class, 'onUserAdminRights');
	}
}



