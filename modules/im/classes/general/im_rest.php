<?php

use Bitrix\Disk\File;
use Bitrix\Im\Chat;
use Bitrix\Im\V2\Chat\ChatFactory;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Main\Type\DateTime;

if (!CModule::IncludeModule('rest'))
{
	return;
}

class CIMRestService extends IRestService
{
	public static function OnRestServiceBuildDescription()
	{
		return array(
			'im' => array(
				'im.user.get' => array(__CLASS__, 'userGet'),
				'im.user.list.get' => array(__CLASS__, 'userList'),
				'im.user.business.list' => array(__CLASS__, 'userBusinessList'),
				'im.user.business.get' =>  array('callback' => array(__CLASS__, 'userBusinessList'), 'options' => array('private' => true)),

				'im.user.status.get' => array(__CLASS__, 'userStatusGet'),
				'im.user.status.set' => array(__CLASS__, 'userStatusSet'),
				'im.user.status.idle.start' => array(__CLASS__, 'userStatusIdleStart'),
				'im.user.status.idle.end' => array(__CLASS__, 'userStatusIdleEnd'),

				'im.recent.get' => array(__CLASS__, 'recentGet'),
				'im.recent.list' => array(__CLASS__, 'recentList'),
				'im.recent.pin' => array(__CLASS__, 'recentPin'),
				'im.recent.hide' => array(__CLASS__, 'recentHide'),
				'im.recent.unread' => array(__CLASS__, 'recentUnread'),

				'im.department.get' => array(__CLASS__, 'departmentGet'),
				'im.department.colleagues.list' => array(__CLASS__, 'departmentColleaguesList'),
				'im.department.colleagues.get' =>  array('callback' => array(__CLASS__, 'departmentColleaguesList'), 'options' => array('private' => true)),
				'im.department.managers.get' => array(__CLASS__, 'departmentManagersGet'),
				'im.department.employees.get' => array(__CLASS__, 'departmentEmployeesGet'),

				'im.chat.add' => array(__CLASS__, 'chatCreate'),
				'im.chat.getEntityChat' => array(__CLASS__, 'chatGetEntityChat'),
				'im.chat.get' => array(__CLASS__, 'chatGet'),
				'im.chat.setOwner' => array(__CLASS__, 'chatSetOwner'),
				'im.chat.setManager' => array(__CLASS__, 'chatSetManager'),
				'im.chat.updateColor' => array(__CLASS__, 'chatUpdateColor'),
				'im.chat.updateTitle' => array(__CLASS__, 'chatUpdateTitle'),
				'im.chat.updateAvatar' => array(__CLASS__, 'chatUpdateAvatar'),
				'im.chat.leave' => array(__CLASS__, 'chatUserDelete'),
				'im.chat.user.add' => array(__CLASS__, 'chatUserAdd'),
				'im.chat.user.delete' => array(__CLASS__, 'chatUserDelete'),
				'im.chat.user.list' => array(__CLASS__, 'chatUserList'),
				'im.chat.sendTyping' => array('callback' => array(__CLASS__, 'dialogWriting'), 'options' => array('private' => true)),
				'im.chat.mute' => array(__CLASS__, 'chatMute'),
				'im.chat.parent.join' => array('callback' => array(__CLASS__, 'chatParentJoin'), 'options' => array('private' => true)),
				'im.chat.url.get' => array('callback' => array(__CLASS__, 'chatUrlGet'), 'options' => array('private' => true)),
				'im.chat.url.delete' => array('callback' => array(__CLASS__, 'chatUrlDelete'), 'options' => array('private' => true)),
				'im.chat.url.counter.get' => array('callback' => array(__CLASS__, 'chatUrlCounterGet'), 'options' => array('private' => true)),
				'im.chat.file.get' => array('callback' => array(__CLASS__, 'chatFileGet'), 'options' => array('private' => true)),
				'im.chat.file.collection.get' => array('callback' => array(__CLASS__, 'chatFileCollectionGet'), 'options' => array('private' => true)),
				'im.chat.favorite.get' => array('callback' => array(__CLASS__, 'chatFavoriteGet'), 'options' => array('private' => true)),
				'im.chat.favorite.add' => array('callback' => array(__CLASS__, 'chatFavoriteAdd'), 'options' => array('private' => true)),
				'im.chat.favorite.delete' => array('callback' => array(__CLASS__, 'chatFavoriteDelete'), 'options' => array('private' => true)),
				'im.chat.favorite.counter.get' => array('callback' => array(__CLASS__, 'chatFavoriteCounterGet'), 'options' => array('private' => true)),
				'im.chat.task.get' => array('callback' => array(__CLASS__, 'chatTaskGet'), 'options' => array('private' => true)),
				'im.chat.task.delete' => array('callback' => array(__CLASS__, 'chatTaskDelete'), 'options' => array('private' => true)),
				'im.chat.task.prepare' => array('callback' => array(__CLASS__, 'chatTaskPrepare'), 'options' => array('private' => true)),
				'im.chat.calendar.get' => array('callback' => array(__CLASS__, 'chatCalendarGet'), 'options' => array('private' => true)),
				'im.chat.calendar.add' => array('callback' => array(__CLASS__, 'chatCalendarAdd'), 'options' => array('private' => true)),
				'im.chat.calendar.delete' => array('callback' => array(__CLASS__, 'chatCalendarDelete'), 'options' => array('private' => true)),
				'im.chat.calendar.prepare' => array('callback' => array(__CLASS__, 'chatCalendarPrepare'), 'options' => array('private' => true)),
				'im.chat.sign.get' => array('callback' => array(__CLASS__, 'chatSignGet'), 'options' => array('private' => true)),
				'im.chat.pin.get' => array('callback' => array(__CLASS__, 'chatPinGet'), 'options' => array('private' => true)),
				'im.chat.pin.add' => array('callback' => array(__CLASS__, 'chatPinAdd'), 'options' => array('private' => true)),
				'im.chat.pin.delete' => array('callback' => array(__CLASS__, 'chatPinDelete'), 'options' => array('private' => true)),
				'im.chat.reminder.get' => array('callback' => array(__CLASS__, 'chatReminderGet'), 'options' => array('private' => true)),
				'im.chat.reminder.add' => array('callback' => array(__CLASS__, 'chatReminderAdd'), 'options' => array('private' => true)),
				'im.chat.reminder.delete' => array('callback' => array(__CLASS__, 'chatReminderDelete'), 'options' => array('private' => true)),

				'im.dialog.get' => array(__CLASS__, 'dialogGet'),
				'im.dialog.messages.get' => array(__CLASS__, 'dialogMessagesGet'),
				'im.dialog.messages.search' => array('callback' => array(__CLASS__, 'dialogMessagesSearch'), 'options' => array('private' => true)),
				'im.dialog.context.get' => array('callback' => array(__CLASS__, 'dialogContextGet'), 'options' => array('private' => true)),
				'im.dialog.users.get' => array('callback' => array(__CLASS__, 'dialogUsersGet'), 'options' => array('private' => true)),
				'im.dialog.users.list' => array(__CLASS__, 'dialogUsersList'),
				'im.dialog.read' => array(__CLASS__, 'dialogRead'),
				'im.dialog.readAll' => array('callback' => array(__CLASS__, 'dialogReadAll'), 'options' => array('private' => true)),
				'im.dialog.read.all' => array(__CLASS__, 'dialogReadAll'),
				'im.dialog.unread' => array(__CLASS__, 'dialogUnread'),
				'im.dialog.writing' => array(__CLASS__, 'dialogWriting'),

				'im.message.add' => array(__CLASS__, 'messageAdd'),
				'im.message.delete' => array(__CLASS__, 'messageDelete'),
				'im.message.update' => array(__CLASS__, 'messageUpdate'),
				'im.message.like' => array(__CLASS__, 'messageLike'),
				'im.message.command' =>  array('callback' => array(__CLASS__, 'messageCommand')),
				'im.message.share' =>  array('callback' => array(__CLASS__, 'messageShare')),
				'im.message.user.get' =>  array('callback' => array(__CLASS__, 'messageUserGet'), 'options' => array('private' => true)),

				'im.notify' => array('callback' => array(__CLASS__, 'notifyAdd'), 'options' => array('private' => false)),
				'im.notify.get' => array('callback' => array(__CLASS__, 'notifyGet'), 'options' => array('private' => true)),
				'im.notify.personal.add' => array(__CLASS__, 'notifyAdd'),
				'im.notify.system.add' => array(__CLASS__, 'notifyAdd'),
				'im.notify.delete' => array(__CLASS__, 'notifyDelete'),
				'im.notify.read' => array(__CLASS__, 'notifyRead'),
				'im.notify.read.list' => array(__CLASS__, 'notifyReadList'),
				'im.notify.read.all' => array(__CLASS__, 'notifyReadAll'),
				'im.notify.confirm' => array(__CLASS__, 'notifyConfirm'),
				'im.notify.answer' => array(__CLASS__, 'notifyAnswer'),
				'im.notify.history.search' => array('callback' => array(__CLASS__, 'notifyHistorySearch'), 'options' => array('private' => true)),
				'im.notify.schema.get' => array('callback' => array(__CLASS__, 'notifySchemaGet'), 'options' => array('private' => true)),

				'im.disk.folder.list.get' => array('callback' => array(__CLASS__, 'diskFolderListGet'), 'options' => array('private' => true)),
				'im.disk.folder.get' => array(__CLASS__, 'diskFolderGet'),
				'im.disk.file.commit' => array(__CLASS__, 'diskFileCommit'),
				'im.disk.file.delete' => array(__CLASS__, 'diskFileDelete'),
				'im.disk.file.save' => array(__CLASS__, 'diskFileSave'),
				'im.disk.record.share' => array(__CLASS__, 'diskRecordShare'),

				'im.counters.get' =>  array(__CLASS__, 'counterGet'),

				'im.search.user.list' => array(__CLASS__, 'searchUser'),
				'im.search.user' =>  array('callback' => array(__CLASS__, 'searchUser'), 'options' => array('private' => true)),
				'im.search.chat.list' => array(__CLASS__, 'searchChat'),
				'im.search.chat' =>  array('callback' => array(__CLASS__, 'searchChat'), 'options' => array('private' => true)),
				'im.search.department.list' => array(__CLASS__, 'searchDepartment'),
				'im.search.department' =>  array('callback' => array(__CLASS__, 'searchDepartment'), 'options' => array('private' => true)),

				'im.search.last.get' => array(__CLASS__, 'searchLastGet'),
				'im.search.last.add' => array(__CLASS__, 'searchLastAdd'),
				'im.search.last.delete' => array(__CLASS__, 'searchLastDelete'),

				'im.promotion.get' =>  array('callback' => array(__CLASS__, 'promotionGet'), 'options' => array('private' => true)),
				'im.promotion.read' =>  array('callback' => array(__CLASS__, 'promotionRead'), 'options' => array('private' => true)),

				'im.mobile.config.get' =>  array('callback' => array(__CLASS__, 'mobileConfigGet'), 'options' => array('private' => true)),

				'im.call.user.register' => array('callback' => array(__CLASS__, 'callUserRegister'), 'options' => array('private' => true)),
				'im.call.user.update' => array('callback' => array(__CLASS__, 'callUserUpdate'), 'options' => array('private' => true)),
				'im.call.user.force.rename' => array('callback' => array(__CLASS__, 'callUserForceRename'), 'options' => array('private' => true)),
				'im.call.channel.public.list' => array('callback' => array(__CLASS__, 'callChannelPublicList'), 'options' => array('private' => true)),

				'im.videoconf.share.change' => array('callback' => array(__CLASS__, 'videoconfShareChange'), 'options' => array('private' => true)),
				'im.videoconf.password.check' => array('callback' => array(__CLASS__, 'videoconfPasswordCheck'), 'options' => array('private' => true)),
				'im.videoconf.add' => array('callback' => array(__CLASS__, 'videoconfAdd'), 'options' => array('private' => true)),
				'im.videoconf.update' => array('callback' => array(__CLASS__, 'videoconfUpdate'), 'options' => array('private' => true)),

				'im.desktop.status.get' => array('callback' => array(__CLASS__, 'desktopStatusGet'), 'options' => array('private' => true)),
				'im.desktop.page.open' => array('callback' => array(__CLASS__, 'desktopPageOpen'), 'options' => array('private' => true)),

				'im.version.v2.enable' => array('callback' => array(__CLASS__, 'enableV2Version'), 'options' => array('private' => true)),
				'im.version.v2.disable' => array('callback' => array(__CLASS__, 'disableV2Version'), 'options' => array('private' => true)),
			),
			'imbot' => Array(
				'imbot.register' => array(__CLASS__, 'botRegister'),
				'imbot.unregister' => array(__CLASS__, 'botUnRegister'),
				'imbot.update' => array(__CLASS__, 'botUpdate'),

				'imbot.dialog.get' => array(__CLASS__, 'dialogGet'),

				'imbot.chat.add' => array(__CLASS__, 'chatCreate'),
				'imbot.chat.get' => array(__CLASS__, 'chatGet'),
				'imbot.chat.setOwner' => array(__CLASS__, 'chatSetOwner'),
				'imbot.chat.setManager' => array(__CLASS__, 'chatSetManager'),
				'imbot.chat.updateColor' => array(__CLASS__, 'chatUpdateColor'),
				'imbot.chat.updateTitle' => array(__CLASS__, 'chatUpdateTitle'),
				'imbot.chat.updateAvatar' => array(__CLASS__, 'chatUpdateAvatar'),
				'imbot.chat.leave' => array(__CLASS__, 'chatUserDelete'),
				'imbot.chat.user.add' => array(__CLASS__, 'chatUserAdd'),
				'imbot.chat.user.delete' => array(__CLASS__, 'chatUserDelete'),
				'imbot.chat.user.list' => array(__CLASS__, 'chatUserList'),
				'imbot.chat.sendTyping' => array(__CLASS__, 'botSendTyping'),

				'imbot.bot.list' => array(__CLASS__, 'botList'),

				'imbot.message.add' => array(__CLASS__, 'botMessageAdd'),
				'imbot.message.delete' => array(__CLASS__, 'botMessageDelete'),
				'imbot.message.update' => array(__CLASS__, 'botMessageUpdate'),
				'imbot.message.like' => array(__CLASS__, 'botMessageLike'),

				'imbot.sendTyping' => array('callback' => array(__CLASS__, 'botSendTyping'), 'options' => array('private' => true)),

				'imbot.command.register' => array(__CLASS__, 'commandRegister'),
				'imbot.command.unregister' => array(__CLASS__, 'commandUnRegister'),
				'imbot.command.update' => array(__CLASS__, 'commandUpdate'),
				'imbot.command.answer' => array(__CLASS__, 'commandAnswer'),

				'imbot.app.register' =>  array('callback' => array(__CLASS__, 'appRegister'), 'options' => array('private' => false)),
				'imbot.app.unregister' =>  array('callback' => array(__CLASS__, 'appUnRegister'), 'options' => array('private' => false)),
				'imbot.app.update' =>  array('callback' => array(__CLASS__, 'appUpdate'), 'options' => array('private' => false)),

				CRestUtil::EVENTS => array(
					'OnImBotMessageAdd' => array('im', 'onImBotMessageAdd', array(__CLASS__, 'onBotMessageAdd'), array("category" => \Bitrix\Rest\Sqs::CATEGORY_BOT, "sendRefreshToken" => true)),
					'OnImBotMessageUpdate' => array('im', 'onImBotMessageUpdate', array(__CLASS__, 'onBotMessageUpdate'), array("category" => \Bitrix\Rest\Sqs::CATEGORY_BOT, "sendRefreshToken" => true)),
					'OnImBotMessageDelete' => array('im', 'onImBotMessageDelete', array(__CLASS__, 'onBotMessageDelete'), array("category" => \Bitrix\Rest\Sqs::CATEGORY_BOT, "sendRefreshToken" => true)),
					'OnImBotJoinChat' => array('im', 'onImBotJoinChat', array(__CLASS__, 'onBotJoinChat'), array("category" => \Bitrix\Rest\Sqs::CATEGORY_BOT)),
					'OnImBotDelete' => array('im', 'onImBotDelete', array(__CLASS__, 'onBotDelete'), array("category" => \Bitrix\Rest\Sqs::CATEGORY_BOT)),
					'OnImCommandAdd' => array('im', 'onImCommandAdd', array(__CLASS__, 'onCommandAdd'), array("category" => \Bitrix\Rest\Sqs::CATEGORY_BOT, "sendRefreshToken" => true)),
				),
			)
		);
	}

	public static function OnRestAppDelete($arParams)
	{
		if(!\Bitrix\Main\Loader::includeModule('rest'))
		{
			return;
		}
		$result = \Bitrix\Rest\AppTable::getList(array('filter' =>array('=ID' => $arParams['APP_ID'])));
		if ($result = $result->fetch())
		{
			$bots = \Bitrix\Im\Bot::getListCache();
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $result['CLIENT_ID'])
				{
					\Bitrix\Im\Bot::unRegister(Array('BOT_ID' => $bot['BOT_ID']));
				}
			}
		}
	}

	/* User api */
	public static function userGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$userId = null;
		if (isset($arParams['ID']))
		{
			$userId = intval($arParams['ID']);
			if ($userId <= 0)
			{
				throw new Bitrix\Rest\RestException("User ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$user = \Bitrix\Im\User::getInstance($userId);
		if ($user->isExists())
		{
			$userId = $user->getId();
		}
		else
		{
			throw new Bitrix\Rest\RestException("User is not exists", "USER_NOT_EXISTS", CRestServer::STATUS_WRONG_REQUEST);
		}

		$currentUserId = \Bitrix\Im\User::getInstance()->getId();
		$isExtranet = \Bitrix\Im\User::getInstance()->isExtranet();

		if ($isExtranet && !\Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($userId, $currentUserId))
		{
			throw new Bitrix\Rest\RestException("You can request only users who consist of your extranet group", "ACCESS_DENIED", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = $user->getArray(Array('JSON' => 'Y', 'HR_PHOTO' => isset($arParams['AVATAR_HR']) && $arParams['AVATAR_HR'] == 'Y'));

		$result['desktop_last_date'] = \CIMMessenger::GetDesktopStatusOnline($userId);
		$result['desktop_last_date'] = $result['desktop_last_date']? date('c', $result['desktop_last_date']): false;

		return $result;
	}

	public static function userList($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$users = Array();
		if (is_string($arParams['ID']))
		{
			$arParams['ID'] = \CUtil::JsObjectToPhp($arParams['ID']);
		}
		if (is_array($arParams['ID']))
		{
			foreach ($arParams['ID'] as $userId)
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$users[$userId] = $userId;
				}
			}
		}

		if (empty($users))
		{
			throw new Bitrix\Rest\RestException("A wrong format for the ID field is passed", "INVALID_FORMAT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$currentUserId = \Bitrix\Im\User::getInstance()->getId();
		$isExtranet = \Bitrix\Im\User::getInstance()->isExtranet();

		$extranetUsers = Array($currentUserId);
		if ($isExtranet)
		{
			$groups = \Bitrix\Im\Integration\Socialnetwork\Extranet::getGroup(Array(), $currentUserId);
			if (is_array($groups))
			{
				foreach ($groups as $group)
				{
					foreach ($group['USERS'] as $userId)
					{
						$extranetUsers[$userId] = $userId;
					}
				}
			}
		}

		$result = Array();
		foreach ($users as $userId)
		{
			if ($isExtranet && !isset($extranetUsers[$userId]))
			{
				continue;
			}

			$result[$userId] = \Bitrix\Im\User::getInstance($userId)->getArray(Array('JSON' => 'Y', 'HR_PHOTO' => isset($arParams['AVATAR_HR']) && $arParams['AVATAR_HR'] == 'Y'));
		}

		$arParams['RESULT_TYPE'] ??= '';
		if (mb_strtolower(($arParams['RESULT_TYPE'] ?? '')) === 'array')
		{
			$result = array_values($result);
		}

		return $result;
	}

	public static function userBusinessList($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$withUserData = $arParams['USER_DATA'] == 'Y';

		$params['OFFSET'] = intval($offset) > 0? intval($offset): (isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): 0);
		$params['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 10;

		$result = \Bitrix\Im\User::getBusiness(null, array('JSON' => 'Y', 'USER_DATA' => $withUserData? 'Y': 'N', 'LIST' => $params));
		if ($result['available'])
		{
			$result = self::setNavData(
				$result['result'],
				array(
					"count" => $result['total'],
					"offset" => $params['OFFSET']
				)
			);
		}
		else
		{
			$result = false;
		}

		return $result;
	}
	/* Status api */

	public static function userStatusGet($params, $n, \CRestServer $server)
	{
		$userId = \Bitrix\Im\Common::getUserId();
		$result = \CIMStatus::GetStatus($userId);
		if (!$result)
		{
			return false;
		}

		return $result['STATUS'];
	}

	public static function userStatusSet($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if (!is_string($params['STATUS']))
		{
			$params['STATUS'] = mb_strtolower($params['STATUS']);
		}

		if (!in_array($params['STATUS'], \CIMStatus::$AVAILABLE_STATUSES))
		{
			throw new Bitrix\Rest\RestException("Status is not available", "STATUS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = \Bitrix\Im\Common::getUserId();

		CIMSettings::SetSetting(CIMSettings::SETTINGS, ['status' => $params['STATUS']], $userId);

		return true;
	}

	public static function userStatusIdleStart($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$userId = \Bitrix\Im\Common::getUserId();

		if (isset($params['AGO']))
		{
			$ago = intval($params['AGO']);
			if ($ago <= 1)
			{
				$ago = 1;
			}
		}
		else
		{
			$ago = 10;
		}

		\CIMStatus::SetIdle($userId, true, $ago);

		return true;
	}

	public static function userStatusIdleEnd($params, $n, \CRestServer $server)
	{
		$userId = \Bitrix\Im\Common::getUserId();

		\CIMStatus::SetIdle($userId, false);

		return true;
	}

	/* Dialog api */

	public static function dialogGet($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Dialog::hasAccess($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
		if (!$chatId)
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \Bitrix\Im\Chat::getById($chatId, ['LOAD_READED' => true, 'JSON' => true]);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result['dialog_id'] = $arParams['DIALOG_ID'];

		return $result;

	}

	public static function dialogMessagesGet($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['CHAT_ID']) && intval($arParams['CHAT_ID']) > 0)
		{
			$arParams['DIALOG_ID'] = 'chat'.$arParams['CHAT_ID'];
		}

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Dialog::hasAccess($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);

		if (isset($arParams['FIRST_ID']))
		{
			if (!preg_match('/^\d{1,}$/i', $arParams['FIRST_ID']))
			{
				throw new Bitrix\Rest\RestException("First ID can't be string", "FIRST_ID_STRING", CRestServer::STATUS_WRONG_REQUEST);
			}
			$options['FIRST_ID'] = intval($arParams['FIRST_ID']);
		}
		else if (isset($arParams['LAST_ID']))
		{
			if (!preg_match('/^\d{1,}$/i', $arParams['LAST_ID']))
			{
				throw new Bitrix\Rest\RestException("Last ID can't be string", "LAST_ID_STRING", CRestServer::STATUS_WRONG_REQUEST);
			}
			$options['LAST_ID'] = intval($arParams['LAST_ID']) > 0? intval($arParams['LAST_ID']): 0;
		}
		$options['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 20;
		$options['CONVERT_TEXT'] = isset($arParams['CONVERT_TEXT']) && $arParams['CONVERT_TEXT'] == 'Y';
		$options['JSON'] = 'Y';

		return \Bitrix\Im\Chat::getMessages($chatId, null, $options);
	}

	public static function dialogMessagesSearch($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
			'SEARCH_MESSAGE' => $arParams['SEARCH_MESSAGE'] ?? null,
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
			'DATE' => $arParams['DATE'] ? new DateTime($arParams['DATE'], DateTimeInterface::RFC3339) : null,
		];
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC',
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$startId = $chat->getStartId();

		if ($startId > 0)
		{
			$filter['START_ID'] = $startId;
		}

		$messages = \Bitrix\Im\V2\MessageCollection::find($filter, $order, $limit);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($messages))->toRestFormat();
	}

	public static function dialogContextGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['MESSAGE_ID']) || (int)$arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException('Message ID can`t be empty', 'MESSAGE_ID_EMPTY', CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageId = (int)$arParams['MESSAGE_ID'];

		$message = new \Bitrix\Im\V2\Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!$message->getChat()->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if (!isset($arParams['RANGE']))
		{
			throw new Bitrix\Rest\RestException('Range can`t be empty', 'RANGE_EMPTY', CRestServer::STATUS_WRONG_REQUEST);
		}

		$range = (int)$arParams['RANGE'];
		$range = ($range <= 50 && $range >= 0) ? $range : 50;

		$result = (new \Bitrix\Im\V2\Message\MessageService())->getMessageContext($message, $range);
		if (!$result->isSuccess())
		{
			$error = $result->getErrors()[0];
			if (isset($error))
			{
				throw new Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		/** @var \Bitrix\Im\V2\MessageCollection $messages */
		$messages = $result->getResult();

		return (new \Bitrix\Im\V2\Rest\RestAdapter($messages))->toRestFormat();
	}

	public static function dialogUsersGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['CHAT_ID']) && intval($arParams['CHAT_ID']) > 0)
		{
			$arParams['DIALOG_ID'] = 'chat'.$arParams['CHAT_ID'];
		}

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Dialog::hasAccess($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);

		return \Bitrix\Im\Chat::getUsers($chatId, ['JSON' => 'Y']);
	}

	public static function dialogUsersList($params, $offset, CRestServer $server): Array
	{
		$params = array_change_key_case($params, CASE_UPPER);
		if (!\Bitrix\Im\Common::isDialogId($params['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Dialog::hasAccess($params['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($params['DIALOG_ID']);
		if (!$chatId)
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$countFilter = [
			'=CHAT_ID' => $chatId,
			'=USER.ACTIVE' => 'Y',
		];
		if (
			isset($params['SKIP_EXTERNAL']) && $params['SKIP_EXTERNAL'] === 'Y'
			|| isset($params['SKIP_EXTERNAL_EXCEPT_TYPES']))
		{
			$options['SKIP_EXTERNAL'] = 'Y';
			$options['SKIP_EXTERNAL_EXCEPT_TYPES'] = array_map('trim', mb_split(',', $params['SKIP_EXTERNAL_EXCEPT_TYPES']));

			$exceptType = $options['SKIP_EXTERNAL_EXCEPT_TYPES'] ?? [];
			$countFilter['!=USER.EXTERNAL_AUTH_ID'] = \Bitrix\Im\Model\UserTable::filterExternalUserTypes($exceptType);
		}

		$options['LIMIT'] = self::getLimit($params);
		$options['JSON'] = true;

		if (isset($params['LAST_ID']) && (int)$params['LAST_ID'] >= 0)
		{
			$options['LAST_ID'] = (int)$params['LAST_ID'];

			return \Bitrix\Im\Chat::getUsers($chatId, $options);
		}

		$counter = \Bitrix\Im\Model\RelationTable::getList([
			'select' => ['CNT' => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')],
			'filter' => $countFilter
		])->fetch();
		$options['OFFSET'] = self::getOffset($offset, $params);

		$result = Array();
		if ($counter && $counter["CNT"] > 0)
		{
			$result = \Bitrix\Im\Chat::getUsers($chatId, $options);
		}

		return self::setNavData(
			$result,
			array(
				"count" => $counter['CNT'],
				"offset" => $options['OFFSET']
			)
		);
	}

	public static function dialogWriting($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else if (isset($arParams['CHAT_ID']))
		{
			$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

			if ($arParams['CHAT_ID'] <= 0)
			{
				throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			$arParams['DIALOG_ID'] = 'chat'.$arParams['CHAT_ID'];
		}

		$result = CIMMessenger::StartWriting($arParams['DIALOG_ID']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		return true;
	}

	public static function dialogRead($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (isset($arParams['LAST_ID']))
		{
			$arParams['MESSAGE_ID'] = $arParams['LAST_ID'];
		}
		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);

		if ($arParams['MESSAGE_ID'] <= 0)
		{
			$arParams['MESSAGE_ID'] = null;
		}

		if ($arParams['DIALOG_ID'] !== 'notify' && !\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \Bitrix\Im\Dialog::read($arParams['DIALOG_ID'], $arParams['MESSAGE_ID']);

		return self::objectEncode($result);
	}

	public static function dialogReadAll($arParams, $n, CRestServer $server)
	{
		return \Bitrix\Im\Dialog::readAll();
	}

	public static function dialogUnread($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);
		if ($arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("First unread message id can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		\Bitrix\Im\Dialog::unread($arParams['DIALOG_ID'], $arParams['MESSAGE_ID']);

		return true;
	}


	/* Search api */
	public static function searchUser($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['FIND']))
		{
			throw new Bitrix\Rest\RestException("Too short a search phrase.", "FIND_SHORT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$params = Array(
			'FILTER' => Array('SEARCH' => $arParams['FIND']),
			'JSON' => 'Y'
		);
		if (isset($arParams['BUSINESS']) && $arParams['BUSINESS'] == 'Y')
		{
			$params['FILTER']['BUSINESS'] = 'Y';
		}

		$ormParams = \Bitrix\Im\User::getListParams($params);
		if (is_null($ormParams))
		{
			throw new Bitrix\Rest\RestException("Too short a search phrase.", "FIND_SHORT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$ormParams['select'] = array("CNT" => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));

		$counter = \Bitrix\Main\UserTable::getList($ormParams)->fetch();

		$result = Array();
		if ($counter && $counter["CNT"] > 0)
		{
			$params['OFFSET'] = intval($offset) > 0? intval($offset): (isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): 0);
			$params['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 10;
			$params['HR_PHOTO'] = isset($arParams['AVATAR_HR']) && $arParams['AVATAR_HR'] == 'Y';
			$params['JSON'] = true;

			$result = \Bitrix\Im\User::getList($params);
		}

		return self::setNavData(
			$result,
			array(
				"count" => $counter['CNT'],
				"offset" => $params['OFFSET']
			)
		);
	}

	public static function searchDepartment($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['FIND']))
		{
			throw new Bitrix\Rest\RestException("Too short a search phrase.", "FIND_SHORT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$options = Array(
			'FILTER' => Array('SEARCH' => $arParams['FIND']),
			'LIST' => Array(
				'OFFSET' => intval($offset) > 0? $offset: (isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): 0),
				'LIMIT' => isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 10,
			),
			'USER_DATA' => $arParams['USER_DATA'] == 'Y'? 'Y': 'N',
			'JSON' => 'Y',
		);

		$result = \Bitrix\Im\Department::getStructure($options);

		return self::setNavData(
			$result['result'],
			array(
				"count" => $result['total'],
				"offset" => $options['OFFSET']
			)
		);
	}

	public static function searchChat($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['FIND']))
		{
			throw new Bitrix\Rest\RestException("Too short a search phrase.", "FIND_SHORT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$params = Array(
			'FILTER' => Array('SEARCH' => $arParams['FIND']),
			'JSON' => 'Y'
		);

		$ormParams = \Bitrix\Im\Chat::getListParams($params);
		if (is_null($ormParams))
		{
			throw new Bitrix\Rest\RestException("Too short a search phrase.", "FIND_SHORT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$ormParams['select'] = array("CNT" => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)'));

		$counter = \Bitrix\Im\Model\ChatTable::getList($ormParams)->fetch();

		$result = Array();
		if ($counter && $counter["CNT"] > 0)
		{
			$params['OFFSET'] = intval($offset) > 0? intval($offset): (isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): 0);
			$params['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 10;
			$params['ORDER'] = Array('ID' => 'DESC');
			$params['JSON'] = 'Y';

			$result = \Bitrix\Im\Chat::getList($params);

		}

		return self::setNavData(
			$result,
			array(
				"count" => $counter['CNT'],
				"offset" => $params['OFFSET']
			)
		);

		return $result;
	}

	public static function searchLastGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$config = Array('JSON' => 'Y');
		if ($arParams['SKIP_OPENLINES'] == 'Y')
		{
			$config['SKIP_OPENLINES'] = 'Y';
		}
		if ($arParams['SKIP_CHAT'] == 'Y')
		{
			$config['SKIP_CHAT'] = 'Y';
		}
		if ($arParams['SKIP_DIALOG'] == 'Y')
		{
			$config['SKIP_DIALOG'] = 'Y';
		}

		return \Bitrix\Im\LastSearch::get(null, $config);
	}

	public static function searchLastAdd($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\LastSearch::add($arParams['DIALOG_ID'])? true: false;
	}

	public static function searchLastDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\LastSearch::delete($arParams['DIALOG_ID']);
	}

	/* Recent api */
	public static function recentGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$config = Array('JSON' => 'Y');

		if ($arParams['ONLY_OPENLINES'] === 'Y')
		{
			$config['ONLY_OPENLINES'] = 'Y';
		}
		else
		{
			if ($arParams['SKIP_OPENLINES'] === 'Y')
			{
				$config['SKIP_OPENLINES'] = 'Y';
			}
			if ($arParams['SKIP_CHAT'] === 'Y')
			{
				$config['SKIP_CHAT'] = 'Y';
			}
			if ($arParams['SKIP_DIALOG'] === 'Y')
			{
				$config['SKIP_DIALOG'] = 'Y';
			}
		}

		if (isset($arParams['LAST_UPDATE'])) // TODO remove this later
		{
			$arParams['LAST_SYNC_DATE'] = $arParams['LAST_UPDATE'];
		}
		if (isset($arParams['LAST_SYNC_DATE']) && $arParams['LAST_SYNC_DATE'])
		{
			try
			{
				$config['LAST_SYNC_DATE'] = new \Bitrix\Main\Type\DateTime($arParams['LAST_SYNC_DATE'], \DateTimeInterface::RFC3339);
			}
			catch (Exception $e){}
		}

		return \Bitrix\Im\Recent::get(null, $config);
	}

	public static function recentList($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$skipChatParam = $arParams['SKIP_CHAT'] ?? null;
		$skipDialogParam = $arParams['SKIP_DIALOG'] ?? null;
		$unreadOnly = $arParams['UNREAD_ONLY'] ?? null;

		$config = Array('JSON' => 'Y');
		if ($arParams['SKIP_OPENLINES'] === 'Y')
		{
			$config['SKIP_OPENLINES'] = 'Y';
		}
		if ($skipChatParam === 'Y')
		{
			$config['SKIP_CHAT'] = 'Y';
		}
		if ($skipDialogParam === 'Y')
		{
			$config['SKIP_DIALOG'] = 'Y';
		}
		if ($unreadOnly === 'Y')
		{
			$config['UNREAD_ONLY'] = 'Y';
		}
		if (isset($arParams['GET_ORIGINAL_TEXT']) && $arParams['GET_ORIGINAL_TEXT'] === 'Y')
		{
			$config['GET_ORIGINAL_TEXT'] = 'Y';
		}
		else
		{
			$config['GET_ORIGINAL_TEXT'] = 'N';
		}

		if (isset($arParams['LAST_MESSAGE_DATE']) && $arParams['LAST_MESSAGE_DATE'])
		{
			try
			{
				$config['LAST_MESSAGE_DATE'] = new \Bitrix\Main\Type\DateTime($arParams['LAST_MESSAGE_DATE'], \DateTimeInterface::RFC3339);
			}
			catch (Exception $e){}
		}

		$config['OFFSET'] = self::getOffset($offset, $arParams);
		$config['LIMIT'] = self::getLimit($arParams);

		$result = \Bitrix\Im\Recent::getList(null, $config);

		if ($result['hasMore'])
		{
			$result['next'] = $config['OFFSET']+(count($result['items']));
		}

		$result['total'] = -1;

		return $result;

	}

	public static function recentPin($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if ($arParams['DIALOG_ID'] !== 'notify' && !\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['ACTION']))
		{
			$arParams['PIN'] = $arParams['ACTION'];
		}

		return \Bitrix\Im\Recent::pin($arParams['DIALOG_ID'], $arParams['PIN'] != 'N');
	}

	public static function recentHide($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if ($arParams['DIALOG_ID'] !== 'notify' && !\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\Recent::hide($arParams['DIALOG_ID']);
	}

	public static function recentUnread($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if ($arParams['DIALOG_ID'] !== 'notify' && !\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['ACTION']))
		{
			$arParams['UNREAD'] = $arParams['ACTION'];
		}

		return \Bitrix\Im\Recent::unread($arParams['DIALOG_ID'], $arParams['UNREAD'] !== 'N');
	}

	/* Promotion api */

	public static function promotionGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$promoType = \Bitrix\Im\Promotion::DEVICE_TYPE_ALL;
		if (in_array($arParams['DEVICE_TYPE'], \Bitrix\Im\Promotion::getDeviceTypes(), true))
		{
			$promoType = $arParams['DEVICE_TYPE'];
		}

		return \Bitrix\Im\Promotion::getActive($promoType);
	}

	public static function promotionRead($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		return \Bitrix\Im\Promotion::read($arParams['ID']);
	}

	/* Department api */

	public static function departmentGet($arParams, $offset, CRestServer $server)
	{
		if (
			\Bitrix\Im\User::getInstance()->isExtranet()
			|| \Bitrix\Im\User::getInstance()->isBot()
		)
		{
			throw new Bitrix\Rest\RestException("Only intranet users have access to this method.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$ids = Array();
		if (is_string($arParams['ID']))
		{
			$arParams['ID'] = \CUtil::JsObjectToPhp($arParams['ID']);
		}
		if (is_array($arParams['ID']))
		{
			foreach ($arParams['ID'] as $id)
			{
				$id = intval($id);
				if ($id > 0)
				{
					$ids[$id] = $id;
				}
			}
		}

		if (empty($ids))
		{
			throw new Bitrix\Rest\RestException("A wrong format for the ID field is passed", "INVALID_FORMAT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$options = Array(
			'FILTER' => Array('ID' => $ids),
			'USER_DATA' => $arParams['USER_DATA'] == 'Y'? 'Y': 'N',
			'JSON' => 'Y',
		);
		$result = \Bitrix\Im\Department::getStructure($options);

		return $result;
	}

	public static function departmentManagersGet($arParams, $n, CRestServer $server)
	{
		if (
			\Bitrix\Im\User::getInstance()->isExtranet()
			|| \Bitrix\Im\User::getInstance()->isBot()
		)
		{
			throw new Bitrix\Rest\RestException("Only intranet users have access to this method.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$withUserData = $arParams['USER_DATA'] == 'Y';

		$ids = Array();
		if (is_string($arParams['ID']))
		{
			$arParams['ID'] = \CUtil::JsObjectToPhp($arParams['ID']);
		}
		if (is_array($arParams['ID']))
		{
			foreach ($arParams['ID'] as $id)
			{
				$id = intval($id);
				if ($id > 0)
				{
					$ids[$id] = $id;
				}
			}
		}

		if (empty($ids))
		{
			throw new Bitrix\Rest\RestException("Department ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\Department::getManagers($ids, array('JSON' => 'Y', 'USER_DATA' => $withUserData? 'Y': 'N'));
	}

	public static function departmentEmployeesGet($arParams, $n, CRestServer $server)
	{
		if (
			\Bitrix\Im\User::getInstance()->isExtranet()
			|| \Bitrix\Im\User::getInstance()->isBot()
		)
		{
			throw new Bitrix\Rest\RestException("Only intranet users have access to this method.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$withUserData = $arParams['USER_DATA'] == 'Y';

		$ids = Array();
		if (is_string($arParams['ID']))
		{
			$arParams['ID'] = \CUtil::JsObjectToPhp($arParams['ID']);
		}
		if (is_array($arParams['ID']))
		{
			foreach ($arParams['ID'] as $id)
			{
				$id = intval($id);
				if ($id > 0)
				{
					$ids[$id] = $id;
				}
			}
		}

		if (empty($ids))
		{
			throw new Bitrix\Rest\RestException("Department ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\Department::getEmployees($ids, array('JSON' => 'Y', 'USER_DATA' => $withUserData? 'Y': 'N'));
	}

	public static function departmentColleaguesList($arParams, $offset, CRestServer $server)
	{
		if (
			\Bitrix\Im\User::getInstance()->isExtranet()
			|| \Bitrix\Im\User::getInstance()->isBot()
		)
		{
			throw new Bitrix\Rest\RestException("Only intranet users have access to this method.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$withUserData = $arParams['USER_DATA'] == 'Y';

		$params['OFFSET'] = intval($offset) > 0? intval($offset): (isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): 0);
		$params['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 10;

		$result = \Bitrix\Im\Department::getColleagues(null, array('JSON' => 'Y', 'USER_DATA' => $withUserData? 'Y': 'N', 'LIST' => $params));

		return self::setNavData(
			$result['result'],
			array(
				"count" => $result['total'],
				"offset" => $params['OFFSET']
			)
		);
	}


	/* ChatAPI */

	/**
	 * im.chat.add
	 *
	 * @param $arParams
	 * @param $n
	 * @param CRestServer $server
	 * @return int
	 * @throws \Bitrix\Rest\AccessException
	 * @throws \Bitrix\Rest\RestException
	 */
	public static function chatCreate($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['USERS']))
		{
			if (is_string($arParams['USERS']))
			{
				$arParams['USERS'] = \CUtil::JsObjectToPhp($arParams['USERS']);
			}
			if (!is_array($arParams['USERS']))
			{
				$arParams['USERS'] = [];
			}

			$arParams['USERS'] = array_filter(array_values($arParams['USERS']));
			foreach ($arParams['USERS'] as $uid)
			{
				if (!is_integer($uid) && !is_string($uid))
				{
					throw new Bitrix\Rest\RestException("Parameter USERS has wrong type", "INVALID_FORMAT", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}
		else
		{
			$arParams['USERS'] = [];
		}

		$add = [
			'TYPE' => $arParams['TYPE'] == 'OPEN' ? Chat::TYPE_OPEN : Chat::TYPE_GROUP,
			'USERS' => $arParams['USERS'],
		];

		if (isset($arParams['AVATAR']))
		{
			$add['AVATAR'] = $arParams['AVATAR'];
		}
		if (isset($arParams['COLOR']))
		{
			$add['COLOR'] = $arParams['COLOR'];
		}
		if (isset($arParams['MESSAGE']))
		{
			$add['MESSAGE'] = $arParams['MESSAGE'];
		}
		if (isset($arParams['TITLE']))
		{
			$add['TITLE'] = $arParams['TITLE'];
		}
		if (isset($arParams['DESCRIPTION']))
		{
			$add['DESCRIPTION'] = $arParams['DESCRIPTION'];
		}

		if (\Bitrix\Im\User::getInstance()->isExtranet())
		{
			$add['USERS'] = \Bitrix\Im\Integration\Socialnetwork\Extranet::filterUserList($add['USERS']);
		}
		else
		{
			if (isset($arParams['ENTITY_TYPE']))
			{
				$add['ENTITY_TYPE'] = $arParams['ENTITY_TYPE'];
			}
			if (isset($arParams['ENTITY_ID']))
			{
				$add['ENTITY_ID'] = $arParams['ENTITY_ID'];
			}
		}

		global $USER;
		$userId = $USER->GetId();
		if ($server->getMethod() == "imbot.chat.add")
		{
			$userId = self::getBotId($arParams, $server);
		}

		$CIMChat = new CIMChat($userId);
		$chatId = $CIMChat->Add($add);
		if (!$chatId)
		{
			throw new Bitrix\Rest\RestException("Chat can't be created", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $chatId;
	}

	public static function chatGet($arParams, $n, CRestServer $server)
	{
		global $USER;
		if (!$USER->IsAuthorized())
		{
			throw new \Bitrix\Rest\RestException("Method not available for guest session.", "AUTHORIZE_ERROR", \CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			if (!\Bitrix\Im\Dialog::hasAccess($arParams['DIALOG_ID']))
			{
				throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
			}

			$chatId = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			if (!$chatId)
			{
				throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}

			$result = \Bitrix\Im\Chat::getById($chatId, ['LOAD_READED' => true, 'JSON' => true]);
			if (!$result)
			{
				throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}

			$result['dialog_id'] = $arParams['DIALOG_ID'];

			return $result;
		}
		else if (
			isset($arParams['ENTITY_TYPE']) && isset($arParams['ENTITY_ID'])
			&& !empty($arParams['ENTITY_TYPE']) && !empty($arParams['ENTITY_ID'])
		)
		{
			$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
																'select' => ['ID'],
																'filter' => [
																	'=ENTITY_TYPE' => $arParams['ENTITY_TYPE'],
																	'=ENTITY_ID' => $arParams['ENTITY_ID'],
																]
															))->fetch();
			if ($chatData)
			{
				return Array(
					'ID' => (int)$chatData['ID']
				);
			}
		}

		return null;
	}

	public static function chatSetOwner($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['USER_ID'] = intval($arParams['USER_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if ($server->getMethod() == mb_strtolower("imbot.chat.setOwner"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chat = new CIMChat($userId);
		$result = $chat->SetOwner($arParams['CHAT_ID'], $arParams['USER_ID']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Change owner can only owner and user must be member in chat", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatSetManager($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['USER_ID'] = intval($arParams['USER_ID']);
		$arParams['IS_MANAGER'] = isset($arParams['IS_MANAGER']) && $arParams['IS_MANAGER'] == 'N'? false: true;

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if ($server->getMethod() == mb_strtolower("imbot.chat.setManager"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		$chat = new CIMChat($userId);
		$result = $chat->SetManager($arParams['CHAT_ID'], $arParams['USER_ID'], $arParams['IS_MANAGER']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Change manager can only owner and user must be member in chat", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUpdateColor($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if (!Bitrix\Im\Color::isSafeColor($arParams['COLOR']))
		{
			throw new Bitrix\Rest\RestException("This color currently unavailable", "WRONG_COLOR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if ($server->getMethod() == mb_strtolower("imbot.chat.updateColor"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		$chat = new CIMChat($userId);
		$result = $chat->SetColor($arParams['CHAT_ID'], $arParams['COLOR']);

		if (!$result)
		{
			throw new Bitrix\Rest\RestException("This color currently set or chat isn't exists", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUpdateTitle($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['TITLE'] = trim($arParams['TITLE']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}
		if (empty($arParams['TITLE']))
		{
			throw new Bitrix\Rest\RestException("Title can't be empty", "TITLE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if ($server->getMethod() == mb_strtolower("imbot.chat.updateTitle"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if (!Chat::isActionAllowed('chat' . $arParams['CHAT_ID'], 'RENAME'))
		{
			throw new Bitrix\Rest\RestException('This chat cannot be renamed', 'ACCESS_ERROR', CRestServer::STATUS_FORBIDDEN);
		}

		$chat = new CIMChat($userId);
		$chat->Rename($arParams['CHAT_ID'], $arParams['TITLE']);

		return true;
	}

	public static function chatUpdateAvatar($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if ($server->getMethod() == mb_strtolower("imbot.chat.updateAvatar"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if (!Chat::isActionAllowed('chat' . $arParams['CHAT_ID'], 'AVATAR'))
		{
			throw new Bitrix\Rest\RestException('The avatar of this chat cannot be changed', 'ACCESS_ERROR', CRestServer::STATUS_FORBIDDEN);
		}

		$arParams['AVATAR'] = CRestUtil::saveFile($arParams['AVATAR']);
		if (!$arParams['AVATAR'] || mb_strpos($arParams['AVATAR']['type'], "image/") !== 0)
		{
			throw new Bitrix\Rest\RestException("Avatar incorrect type", "AVATAR_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$imageCheck = (new \Bitrix\Main\File\Image($arParams['AVATAR']["tmp_name"]))->getInfo();
		if(
			!$imageCheck
			|| !$imageCheck->getWidth()
			|| $imageCheck->getWidth() > 5000
			|| !$imageCheck->getHeight()
			|| $imageCheck->getHeight() > 5000
		)
		{
			throw new Bitrix\Rest\RestException("Avatar incorrect size (max 5000x5000)", "AVATAR_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['AVATAR'] = CFile::saveFile($arParams['AVATAR'], 'im');

		$result = CIMDisk::UpdateAvatarId($arParams['CHAT_ID'], $arParams['AVATAR'], $userId);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Chat isn't exists", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserAdd($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if (!Chat::isActionAllowed('chat' . $arParams['CHAT_ID'], 'EXTEND'))
		{
			throw new Bitrix\Rest\RestException('It is forbidden to add users to this chat', 'ACCESS_ERROR', CRestServer::STATUS_FORBIDDEN);
		}

		$userId = $USER->GetID();
		if ($server->getMethod() == "imbot.chat.user.add")
		{
			$userId = self::getBotId($arParams, $server);
		}

		$hideHistory = null;
		if (isset($arParams['HIDE_HISTORY']))
		{
			if ($arParams['HIDE_HISTORY'] == 'N')
			{
				$hideHistory = false;
			}
			else
			{
				$hideHistory = (bool)$arParams['HIDE_HISTORY'];
			}
		}

		if (\Bitrix\Im\User::getInstance($userId)->isExtranet())
		{
			if (is_string($arParams['USERS']))
			{
				$arParams['USERS'] = \CUtil::JsObjectToPhp($arParams['USERS']);
			}
			if (!is_array($arParams['USERS']))
			{
				throw new Bitrix\Rest\RestException("User IDs must be passed in array format", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
			}
			$arParams['USERS'] = \Bitrix\Im\Integration\Socialnetwork\Extranet::filterUserList($arParams['USERS'], $userId);
		}

		$CIMChat = new CIMChat($userId);
		$result = $CIMChat->AddUser($arParams['CHAT_ID'], $arParams['USERS'], $hideHistory);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("You don't have access or user already member in chat", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserDelete($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		$arParams['USER_ID'] = intval($arParams['USER_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		if (!Chat::isActionAllowed('chat' . $arParams['CHAT_ID'], 'LEAVE'))
		{
			throw new Bitrix\Rest\RestException('It is forbidden to delete users of this chat', 'ACCESS_ERROR', CRestServer::STATUS_FORBIDDEN);
		}

		$userId = $USER->GetID();
		if (in_array($server->getMethod(), Array("imbot.chat.leave", "imbot.chat.user.delete")))
		{
			$userId = self::getBotId($arParams, $server);
		}

		if (in_array($server->getMethod(), Array("im.chat.user.delete", "imbot.chat.user.delete")) && $arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$CIMChat = new CIMChat($userId);
		$result = $CIMChat->DeleteUser($arParams['CHAT_ID'], $arParams['USER_ID'] > 0? $arParams['USER_ID']: $userId);
		if (!$result)
		{
			$error = $GLOBALS['APPLICATION']->GetException();
			if ($error->GetID() === 'LEAVE_OWNER_FORBIDDEN')
			{
				throw new Bitrix\Rest\RestException($error->GetString(), "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
			}

			throw new Bitrix\Rest\RestException("You don't have access or user isn't member in chat", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserList($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'])
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$userId = $USER->GetID();
		if ($server->getMethod() == "imbot.chat.user.list")
		{
			$userId = self::getBotId($arParams, $server);
		}

		$arChat = CIMChat::GetChatData(array(
										   'ID' => $arParams['CHAT_ID'],
										   'USE_CACHE' => 'Y',
										   'USER_ID' => $userId
									   ));

		return isset($arChat['userInChat'][$arParams['CHAT_ID']])? $arChat['userInChat'][$arParams['CHAT_ID']]: Array();
	}

	public static function chatMute($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!Chat::isActionAllowed('chat' . $arParams['CHAT_ID'], 'MUTE'))
		{
			throw new Bitrix\Rest\RestException('This chat cannot be muted', 'ACCESS_ERROR', CRestServer::STATUS_FORBIDDEN);
		}

		if (isset($arParams['ACTION']))
		{
			$arParams['MUTE'] = $arParams['ACTION'];
		}

		return \Bitrix\Im\Chat::mute($arParams['CHAT_ID'], $arParams['MUTE'] != 'N');
	}

	public static function chatParentJoin($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);
		if ($arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = new CIMChat();

		if (!$chat->JoinParent($arParams['CHAT_ID'], $arParams['MESSAGE_ID']))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUrlGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'SEARCH_URL' => $arParams['SEARCH_URL'] ?? null,
			'USER_ID' => $arParams['USER_ID'] ?? null,
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
		];
		$limit = self::getLimit($arParams);
		$offset = self::getOffset($n, $arParams);
		$order = [
			'MESSAGE_ID' => $arParams['ORDER']['MESSAGE_ID'] ?? 'DESC'
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$startId = $chat->getStartId();

		if ($startId > 0)
		{
			$filter['START_ID'] = $startId;
		}

		$urls = \Bitrix\Im\V2\Link\Url\UrlCollection::find($filter, $order, $limit, new \Bitrix\Im\V2\Service\Context(), $offset);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($urls))->toRestFormat();
	}

	public static function chatUrlDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['LINK_ID']) || (int)$arParams['LINK_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('LINK_ID can`t be empty', 'LINK_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$linkId = (int)$arParams['LINK_ID'];
		$url = new \Bitrix\Im\V2\Link\Url\UrlItem($linkId);
		if ($url->getId() === null)
		{
			throw new \Bitrix\Rest\RestException('Url not found', \Bitrix\Im\V2\Entity\Url\UrlError::NOT_FOUND, \CRestServer::STATUS_NOT_FOUND);
		}

		$chatId = $url->getChatId();
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		global $USER;
		$userId = (int)$USER->GetID();

		if ($userId !== $url->getAuthorId() && !$USER->IsAdmin())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to delete this url', \Bitrix\Im\V2\Entity\Url\UrlError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$urls = new \Bitrix\Im\V2\Link\Url\UrlCollection();
		$urls->add($url);

		$deleteResult = (new \Bitrix\Im\V2\Link\Url\UrlService())->deleteUrls($urls);

		if (!$deleteResult->isSuccess())
		{
			throw new \Bitrix\Rest\RestException('Failed to delete url', \Bitrix\Im\V2\Entity\Url\UrlError::DELETE_ERROR, \CRestServer::STATUS_INTERNAL);
		}

		return true;
	}

	public static function chatUrlCounterGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $arParams['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$startId = $chat->getStartId();

		return [
			'counter' => (new \Bitrix\Im\V2\Link\Url\UrlService())->getCount($chatId, $startId)
		];
	}

	public static function chatFileGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'SEARCH_FILE_NAME' => $arParams['SEARCH_FILE_NAME'] ?? null,
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
			'SUBTYPE' => $arParams['SUBTYPE'] ?? null,
			'USER_ID' => $arParams['USER_ID'] ?? null,
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
		];
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC',
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if (isset($filter['SUBTYPE']))
		{
			$filter['SUBTYPE'] = \Bitrix\Im\V2\Entity\File\FileItem::getSubtypeFromJsonFormat($filter['SUBTYPE']);
		}

		$startId = $chat->getStartId();

		if ($startId > 0)
		{
			$filter['START_ID'] = $startId;
		}

		$files = \Bitrix\Im\V2\Link\File\FileCollection::find($filter, $order, $limit, new \Bitrix\Im\V2\Service\Context());

		return (new \Bitrix\Im\V2\Rest\RestAdapter($files))->toRestFormat();
	}

	public static function chatFileCollectionGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
		];
		$limit = self::getLimit($arParams);
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$startId = $chat->getStartId();

		if ($startId > 0)
		{
			$filter['START_ID'] = $startId;
		}

		$files = new \Bitrix\Im\V2\Link\File\FileCollection();

		foreach (\Bitrix\Im\V2\Entity\File\FileItem::ALLOWED_SUBTYPE as $subtype)
		{
			$filter['SUBTYPE'] = $subtype;

			$filesBySubtype = \Bitrix\Im\V2\Link\File\FileCollection::find($filter, [], $limit);

			foreach ($filesBySubtype as $fileBySubtype)
			{
				$files[] = $fileBySubtype;
			}
		}

		return (new \Bitrix\Im\V2\Rest\RestAdapter($files))->toRestFormat();
	}

	public static function chatFavoriteGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
			'SEARCH_MESSAGE' => $arParams['SEARCH_MESSAGE'] ?? null,
			'USER_ID' => $arParams['USER_ID'] ?? null,
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
		];
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC',
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$favoriteMessage = \Bitrix\Im\V2\Link\Favorite\FavoriteCollection::find($filter, $order, $limit);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($favoriteMessage))->toRestFormat();
	}

	public static function chatFavoriteAdd($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['MESSAGE_ID']) || (int)$arParams['MESSAGE_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('MESSAGE_ID can`t be empty', 'MESSAGE_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageId = (int)$arParams['MESSAGE_ID'];

		$message = new \Bitrix\Im\V2\Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = $message->getChat();

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if ($chat->getStartId() > $messageId)
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this message', \Bitrix\Im\V2\Message\MessageError::MESSAGE_ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$markResult = $message->markAsFavorite();
		if (!$markResult->isSuccess())
		{
			$error = $markResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $markResult->isSuccess();
	}

	public static function chatFavoriteDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['MESSAGE_ID']) || (int)$arParams['MESSAGE_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('MESSAGE_ID can`t be empty', 'MESSAGE_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageId = (int)$arParams['MESSAGE_ID'];

		$message = new \Bitrix\Im\V2\Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$unmarkResult = $message->unmarkAsFavorite();
		if (!$unmarkResult->isSuccess())
		{
			$error = $unmarkResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $unmarkResult->isSuccess();
	}

	public static function chatFavoriteCounterGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $arParams['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		return [
			'counter' => (new \Bitrix\Im\V2\Link\Favorite\FavoriteService())->getCount($chatId)
		];
	}

	public static function chatTaskGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'USER_ID' => $arParams['USER_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
			'SEARCH_TASK_NAME' => $arParams['SEARCH_TASK_NAME'] ?? null,
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
		];
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC'
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$tasks = \Bitrix\Im\V2\Link\Task\TaskCollection::find($filter, $order, $limit);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($tasks))->toRestFormat();
	}

	public static function chatTaskDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['LINK_ID']) || (int)$arParams['LINK_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('LINK_ID can`t be empty', 'LINK_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$linkId = (int)$arParams['LINK_ID'];
		$task = new \Bitrix\Im\V2\Link\Task\TaskItem($linkId);
		if ($task->getId() === null)
		{
			throw new \Bitrix\Rest\RestException('Task not found', \Bitrix\Im\V2\Entity\Task\TaskError::NOT_FOUND, \CRestServer::STATUS_NOT_FOUND);
		}

		$chatId = $task->getChatId();
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		global $USER;
		$userId = (int)$USER->GetID();

		if ($userId !== $task->getAuthorId() && !$USER->IsAdmin())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to delete this task', \Bitrix\Im\V2\Entity\Task\TaskError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$deleteResult = (new \Bitrix\Im\V2\Link\Task\TaskService())->unregisterTask($task, false);

		if (!$deleteResult->isSuccess())
		{
			throw new \Bitrix\Rest\RestException('Failed to delete task', \Bitrix\Im\V2\Entity\Task\TaskError::DELETE_ERROR, \CRestServer::STATUS_INTERNAL);
		}

		return true;
	}

	public static function chatTaskPrepare($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if ($server->getAuthType() !== \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE && !self::isDebugEnabled())
		{
			throw new \Bitrix\Rest\RestException('This method is available only with session auth type', 'WRONG_AUTH_TYPE', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$message = null;
		$messageId = null;

		if (isset($arParams['MESSAGE_ID']) && (int)$arParams['MESSAGE_ID'] > 0)
		{
			$messageId = (int)$arParams['MESSAGE_ID'];

			$message = new \Bitrix\Im\V2\Message($messageId);

			if ($message->getMessageId() === null)
			{
				throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
			}

			$chat = $message->getChat();
		}
		elseif (isset($arParams['CHAT_ID']) && (int)$arParams['CHAT_ID'] > 0)
		{
			$chatId = (int)$arParams['CHAT_ID'];

			$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);
		}
		else
		{
			throw new Bitrix\Rest\RestException('Message ID and chat ID can`t be empty together', 'CHAT_ID_MESSAGE_ID_EMPTY', CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if (isset($messageId) && $messageId < $chat->getStartId())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this message', \Bitrix\Im\V2\Message\MessageError::MESSAGE_ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$taskService = new \Bitrix\Im\V2\Link\Task\TaskService();
		$result = $taskService->prepareDataForCreateSlider($chat, $message);
		if (!$result->isSuccess())
		{
			$error = $result->getErrors()[0];
			if (isset($error))
			{
				throw new Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$data = $result->getResult();

		return [
			'link' => $data['LINK'],
			'params' => $data['PARAMS']
		];
	}

	public static function chatCalendarGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'USER_ID' => $arParams['USER_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
			'CALENDAR_DATE_FROM' => $arParams['CALENDAR_DATE_FROM'] ? new DateTime($arParams['CALENDAR_DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'CALENDAR_DATE_TO' => $arParams['CALENDAR_DATE_TO'] ? new DateTime($arParams['CALENDAR_DATE_TO'], DateTimeInterface::RFC3339) : null,
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
			'SEARCH_TITLE' => $arParams['SEARCH_TITLE'] ?? null,
		];
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC'
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$startId = $chat->getStartId();

		if ($startId > 0)
		{
			$filter['START_ID'] = $startId;
		}

		$calendars = \Bitrix\Im\V2\Link\Calendar\CalendarCollection::find($filter, $order, $limit);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($calendars))->toRestFormat();
	}

	public static function chatCalendarAdd($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$messageId = null;
		$chatId = null;

		if (isset($arParams['MESSAGE_ID']) && (int)$arParams['MESSAGE_ID'] > 0)
		{
			$messageId = (int)$arParams['MESSAGE_ID'];

			$message = new \Bitrix\Im\V2\Message($messageId);

			if ($message->getMessageId() === null)
			{
				throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
			}

			$chat = $message->getChat();
		}
		elseif (isset($arParams['CHAT_ID']) && (int)$arParams['CHAT_ID'] > 0)
		{
			$chatId = (int)$arParams['CHAT_ID'];

			$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);
		}
		else
		{
			throw new Bitrix\Rest\RestException('Message ID and chat ID can`t be empty together', 'CHAT_ID_MESSAGE_ID_EMPTY', CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!isset($arParams['CALENDAR_ID']) || (int)$arParams['CALENDAR_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('CALENDAR_ID can`t be empty', 'CALENDAR_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if (isset($messageId))
		{
			$startId = $chat->getStartId();

			if ($messageId < $startId)
			{
				throw new \Bitrix\Rest\RestException('You do not have access to this message', \Bitrix\Im\V2\Message\MessageError::MESSAGE_ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
			}
		}

		$chatId = $chat->getChatId();

		$calendarId = $arParams['CALENDAR_ID'];

		$calendarService = new \Bitrix\Im\V2\Link\Calendar\CalendarService();
		$calendar = \Bitrix\Im\V2\Entity\Calendar\CalendarItem::initById($calendarId);

		global $USER;
		$userId = (int)$USER->GetID();
		if ($userId !== $calendar->getCreatedBy())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this calendar event', Bitrix\Im\V2\Entity\Calendar\CalendarError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$saveResult = $calendarService->registerCalendar($chatId, $messageId, $calendar);
		if (!$saveResult->isSuccess())
		{
			$error = $saveResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $saveResult->isSuccess();
	}

	public static function chatCalendarDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['LINK_ID']) || (int)$arParams['LINK_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('LINK_ID can`t be empty', 'LINK_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$linkId = (int)$arParams['LINK_ID'];
		$calendar = new \Bitrix\Im\V2\Link\Calendar\CalendarItem($linkId);
		if ($calendar->getId() === null)
		{
			throw new \Bitrix\Rest\RestException('Calendar event not found', \Bitrix\Im\V2\Entity\Calendar\CalendarError::NOT_FOUND, \CRestServer::STATUS_NOT_FOUND);
		}

		$chatId = $calendar->getChatId();
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		global $USER;
		$userId = (int)$USER->GetID();

		if ($userId !== $calendar->getAuthorId() && !$USER->IsAdmin())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to delete this calendar event', \Bitrix\Im\V2\Entity\Calendar\CalendarError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$deleteResult = (new \Bitrix\Im\V2\Link\Calendar\CalendarService())->unregisterCalendar($calendar);

		if (!$deleteResult->isSuccess())
		{
			throw new \Bitrix\Rest\RestException('Failed to delete calendar event', \Bitrix\Im\V2\Entity\Calendar\CalendarError::DELETE_ERROR, \CRestServer::STATUS_INTERNAL);
		}

		return true;
	}

	public static function chatCalendarPrepare($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if ($server->getAuthType() !== \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE && !self::isDebugEnabled())
		{
			throw new \Bitrix\Rest\RestException('This method is available only with session auth type', 'WRONG_AUTH_TYPE', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$message = null;
		$messageId = null;

		if (isset($arParams['MESSAGE_ID']) && (int)$arParams['MESSAGE_ID'] > 0)
		{
			$messageId = (int)$arParams['MESSAGE_ID'];

			$message = new \Bitrix\Im\V2\Message($messageId);

			if ($message->getMessageId() === null)
			{
				throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
			}

			$chat = $message->getChat();
		}
		elseif (isset($arParams['CHAT_ID']) && (int)$arParams['CHAT_ID'] > 0)
		{
			$chatId = (int)$arParams['CHAT_ID'];

			$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);
		}
		else
		{
			throw new Bitrix\Rest\RestException('Message ID and chat ID can`t be empty together', 'CHAT_ID_MESSAGE_ID_EMPTY', CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if (isset($messageId))
		{
			$startId = $chat->getStartId();

			if ($messageId < $startId)
			{
				throw new \Bitrix\Rest\RestException('You do not have access to this message', \Bitrix\Im\V2\Message\MessageError::MESSAGE_ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
			}
		}

		$calendarService = new \Bitrix\Im\V2\Link\Calendar\CalendarService();
		$result = $calendarService->prepareDataForCreateSlider($chat, $message);
		if (!$result->isSuccess())
		{
			$error = $result->getErrors()[0];
			if (isset($error))
			{
				throw new Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $result->getResult();
	}

	public static function chatSignGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		return [
			'list' => [
				[
					'id' => 1,
					'messageId' => 2345,
					'chatId' => 92,
					'authorId' => 1,
					'dateCreate' => '2022-08-22T12:37:10+02:00',
					'sign' => [
						'id' => 1,
						'title' => 'Important document #1',
						'status' => 'signed',
						'from' => [
							'company' => 'Bitrix',
							'userId' => 1
						],
						'to' => [
							'company' => null,
							'userId' => 1
						],
						'links' => [
							'detail' => 'https://...'
						]
					],
				],
				[
					'id' => 2,
					'messageId' => 2346,
					'chatId' => 92,
					'authorId' => 1,
					'dateCreate' => '2022-08-24T12:37:10+02:00',
					'sign' => [
						'id' => 1,
						'title' => 'Important document #2',
						'status' => 'signed',
						'from' => [
							'company' => 'Bitrix',
							'userId' => 1
						],
						'to' => [
							'company' => null,
							'userId' => 1
						],
						'links' => [
							'detail' => 'https://...'
						]
					],
				]
			],
			'users' => [\Bitrix\Im\User::getInstance(1)->getArray(['JSON' => 'Y'])]
		];
	}

	public static function chatPinGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
		];
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC',
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$startId = $chat->getStartId();

		if ($startId > 0)
		{
			$filter['START_ID'] = $startId;
		}

		$pins = \Bitrix\Im\V2\Link\Pin\PinCollection::find($filter, $order, $limit);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($pins))->toRestFormat();
	}

	public static function chatPinAdd($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['MESSAGE_ID']) || (int)$arParams['MESSAGE_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('MESSAGE_ID can`t be empty', 'MESSAGE_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageId = (int)$arParams['MESSAGE_ID'];

		$message = new \Bitrix\Im\V2\Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = $message->getChat();

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if ($chat->getStartId() > $messageId)
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this message', \Bitrix\Im\V2\Message\MessageError::MESSAGE_ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$pinResult = $message->pin();
		if (!$pinResult->isSuccess())
		{
			$error = $pinResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $pinResult->isSuccess();
	}

	public static function chatPinDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['MESSAGE_ID']) || (int)$arParams['MESSAGE_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('MESSAGE_ID can`t be empty', 'MESSAGE_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageId = (int)$arParams['MESSAGE_ID'];

		$message = new \Bitrix\Im\V2\Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = $message->getChat();

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$unpinResult = $message->unpin();
		if (!$unpinResult->isSuccess())
		{
			$error = $unpinResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $unpinResult->isSuccess();
	}

	public static function chatReminderGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$filter = [
			'LAST_ID' => $arParams['LAST_ID'] ?? null,
			'SEARCH_MESSAGE' => $arParams['SEARCH_MESSAGE'] ?? null,
			'USER_ID' => $arParams['USER_ID'] ?? null,
			'CHAT_ID' => $arParams['CHAT_ID'] ?? null,
			'DATE_FROM' => $arParams['DATE_FROM'] ? new DateTime($arParams['DATE_FROM'], DateTimeInterface::RFC3339) : null,
			'DATE_TO' => $arParams['DATE_TO'] ? new DateTime($arParams['DATE_TO'], DateTimeInterface::RFC3339) : null,
		];
		if ($arParams['IS_REMINDED'] === 'Y')
		{
			$filter['IS_REMINDED'] = true;
		}
		if ($arParams['IS_REMINDED'] === 'N')
		{
			$filter['IS_REMINDED'] = false;
		}
		$limit = self::getLimit($arParams);
		$order = [
			'ID' => $arParams['ORDER']['ID'] ?? 'DESC',
		];
		if (!isset($filter['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $filter['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$reminders = \Bitrix\Im\V2\Link\Reminder\ReminderCollection::find($filter, $order, $limit);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($reminders))->toRestFormat();
	}

	public static function chatReminderAdd($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['DATE_REMIND']))
		{
			throw new \Bitrix\Rest\RestException('DATE_REMIND can`t be empty', \Bitrix\Im\V2\Link\Reminder\ReminderError::DATE_REMIND_EMPTY, \CRestServer::STATUS_FORBIDDEN);
		}

		$dateRemind = new DateTime($arParams['DATE_REMIND'], DateTimeInterface::RFC3339);

		if (!isset($arParams['MESSAGE_ID']) || (int)$arParams['MESSAGE_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('MESSAGE_ID can`t be empty', 'MESSAGE_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageId = (int)$arParams['MESSAGE_ID'];

		$message = new \Bitrix\Im\V2\Message($messageId);

		if ($message->getMessageId() === null)
		{
			throw new \Bitrix\Rest\RestException('Message not found', \Bitrix\Im\V2\Message\MessageError::MESSAGE_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chat = $message->getChat();

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		if ($chat->getStartId() > $messageId)
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this message', \Bitrix\Im\V2\Message\MessageError::MESSAGE_ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$addResult = $message->addToReminder($dateRemind);
		if (!$addResult->isSuccess())
		{
			$error = $addResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $addResult->isSuccess();
	}

	public static function chatReminderDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		if (!isset($arParams['REMINDER_ID']) || (int)$arParams['REMINDER_ID'] <= 0)
		{
			throw new \Bitrix\Rest\RestException('REMINDER_ID can`t be empty', 'REMINDER_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$reminderId = (int)$arParams['REMINDER_ID'];

		$reminder = new \Bitrix\Im\V2\Link\Reminder\ReminderItem($reminderId);

		if ($reminder->getId() === null)
		{
			throw new \Bitrix\Rest\RestException('Reminder not found', \Bitrix\Im\V2\Link\Reminder\ReminderError::REMINDER_NOT_FOUND, \CRestServer::STATUS_WRONG_REQUEST);
		}

		$deleteResult = (new \Bitrix\Im\V2\Link\Reminder\ReminderService())->deleteReminder($reminder);
		if (!$deleteResult->isSuccess())
		{
			$error = $deleteResult->getErrors()[0];
			if (isset($error))
			{
				throw new \Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), \CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $deleteResult->isSuccess();
	}

	public static function botList($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$result = Array();
		$list = \Bitrix\Im\Bot::getListCache();
		foreach ($list as $botId => $botData)
		{
			if ($botData['TYPE'] == \Bitrix\Im\Bot::TYPE_NETWORK)
				continue;

			$result[$botId] = Array(
				'ID' => $botId,
				'NAME' => \Bitrix\Im\User::getInstance($botId)->getFullName(),
				'CODE' => $botData['CODE'],
				'OPENLINE' => $botData['OPENLINE'],
			);
		}

		return $result;
	}

	public static function messageAdd($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['MESSAGE']))
		{
			if (!is_string($arParams['MESSAGE']))
			{
				throw new Bitrix\Rest\RestException("Wrong message type", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			$arParams['MESSAGE'] = trim($arParams['MESSAGE']);

			if ($arParams['MESSAGE'] == '' && empty($arParams['ATTACH']))
			{
				throw new Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else if (!isset($arParams['ATTACH']))
		{
			throw new Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isChatId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				$arParams['USER_ID'] = intval($arParams['DIALOG_ID']);
			}
		}

		$arParams['FROM_USER_ID'] = $USER->GetId();
		if (isset($arParams['USER_ID']))
		{
			$arParams['USER_ID'] = intval($arParams['USER_ID']);
			if ($arParams['USER_ID'] <= 0)
			{
				throw new Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			if (!Bitrix\Im\User::getInstance($arParams['USER_ID'])->isExists())
			{
				throw new Bitrix\Rest\RestException("User not found", "USER_NOT_FOUND", CRestServer::STATUS_WRONG_REQUEST);
			}

			$arMessageFields = Array(
				"MESSAGE_TYPE" => IM_MESSAGE_PRIVATE,
				"FROM_USER_ID" => $arParams['FROM_USER_ID'],
				"DIALOG_ID" => $arParams['USER_ID'],
			);
		}
		else if (isset($arParams['CHAT_ID']))
		{
			$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
			if ($arParams['CHAT_ID'] <= 0)
			{
				throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			if (
				CIMChat::GetGeneralChatId() == $arParams['CHAT_ID']
				&& !CIMChat::CanSendMessageToGeneralChat($arParams['FROM_USER_ID'])
			)
			{
				throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
			}

			if (!Chat::isActionAllowed('chat' . $arParams['CHAT_ID'], 'SEND'))
			{
				throw new Bitrix\Rest\RestException('It is forbidden to send messages to this chat', 'ACCESS_ERROR', CRestServer::STATUS_FORBIDDEN);
			}

			if (isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y')
			{
				$clientId = $server->getClientId();
				if ($clientId)
				{
					$result = \Bitrix\Rest\AppTable::getList(
						array(
							'filter' => array(
								'=CLIENT_ID' => $clientId
							),
							'select' => array(
								'CODE',
								'APP_NAME',
								'APP_NAME_DEFAULT' => 'LANG_DEFAULT.MENU_NAME',
							)
						)
					);
					$result = $result->fetch();
					$moduleName = !empty($result['APP_NAME'])
						? $result['APP_NAME']
						: (!empty($result['APP_NAME_DEFAULT'])
							? $result['APP_NAME_DEFAULT']
							: $result['CODE']
						)
					;

					$arParams['MESSAGE'] = "[b]".$moduleName."[/b]\n".$arParams['MESSAGE'];
				}
			}

			$arMessageFields = Array(
				"MESSAGE_TYPE" => IM_MESSAGE_CHAT,
				"FROM_USER_ID" => $arParams['FROM_USER_ID'],
				"DIALOG_ID" => 'chat'.$arParams['CHAT_ID'],
			);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['MESSAGE']))
		{
			$arMessageFields["MESSAGE"]	= $arParams['MESSAGE'];
		}

		$attach = CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
		if ($attach)
		{
			if ($attach->IsAllowSize())
			{
				$arMessageFields['ATTACH'] = $attach;
			}
			else
			{
				throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else if ($arParams['ATTACH'])
		{
			throw new Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['KEYBOARD']) && !empty($arParams['KEYBOARD']))
		{
			$keyboard = Array();
			if (is_string($arParams['KEYBOARD']))
			{
				$arParams['KEYBOARD'] = \CUtil::JsObjectToPhp($arParams['KEYBOARD']);
			}
			if (!isset($arParams['KEYBOARD']['BUTTONS']))
			{
				$keyboard['BUTTONS'] = $arParams['KEYBOARD'];
			}
			else
			{
				$keyboard = $arParams['KEYBOARD'];
			}
			$keyboard['BOT_ID'] = $arParams['BOT_ID'];

			$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			if ($keyboard)
			{
				$arMessageFields['KEYBOARD'] = $keyboard;
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect keyboard params", "KEYBOARD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['MENU']) && !empty($arParams['MENU']))
		{
			$menu = Array();
			if (is_string($arParams['MENU']))
			{
				$arParams['MENU'] = \CUtil::JsObjectToPhp($arParams['MENU']);
			}
			if (!isset($arParams['MENU']['ITEMS']))
			{
				$menu['ITEMS'] = $arParams['MENU'];
			}
			else
			{
				$menu = $arParams['MENU'];
			}
			$menu['BOT_ID'] = $arParams['BOT_ID'];

			$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);
			if ($menu)
			{
				$arMessageFields['MENU'] = $menu;
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect menu params", "MENU_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (
			isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y'
			&& $server->getAuthType() !== \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE
			&& \Bitrix\Im\Dialog::hasAccess($arMessageFields['DIALOG_ID'])
		)
		{
			$arMessageFields['SYSTEM'] = 'Y';
		}

		if (isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == 'N')
		{
			$arMessageFields['URL_PREVIEW'] = 'N';
		}
		if (isset($arParams['SKIP_CONNECTOR']) && mb_strtoupper($arParams['SKIP_CONNECTOR']) == 'Y')
		{
			$arMessageFields['SKIP_CONNECTOR'] = 'Y';
			$arMessageFields['SILENT_CONNECTOR'] = 'Y';
		}
		if (isset($arParams['TEMPLATE_ID']) && !empty($arParams['TEMPLATE_ID']))
		{
			$arMessageFields['TEMPLATE_ID'] = mb_substr((string)$arParams['TEMPLATE_ID'], 0, 255);
		}

		$id = CIMMessenger::Add($arMessageFields);
		if (!$id)
		{
			throw new Bitrix\Rest\RestException("Message isn't added", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return (int)$id;

	}

	public static function messageDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['MESSAGE_ID']))
		{
			$arParams['ID'] = $arParams['MESSAGE_ID'];
		}

		$arParams['ID'] = intval($arParams['ID']);
		if ($arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$res = CIMMessenger::Delete($arParams['ID']);
		if (!$res)
		{
			throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
		}

		return true;
	}

	public static function messageUpdate($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['MESSAGE_ID']))
		{
			$arParams['ID'] = $arParams['MESSAGE_ID'];
		}

		$arParams['ID'] = intval($arParams['ID']);
		if ($arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		$arParams['IS_EDITED'] = $arParams['IS_EDITED'] == 'N'? 'N': 'Y';

		$message = null;
		if (isset($arParams['ATTACH']))
		{
			$message = CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $arParams['ID']);
			if (!$message)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}

			if (empty($arParams['ATTACH']) || $arParams['ATTACH'] == 'N')
			{
				CIMMessageParam::Set($arParams['ID'], Array('IS_EDITED' => $arParams['IS_EDITED'], 'ATTACH' => Array()));
			}
			else
			{
				$attach = CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
				if ($attach)
				{
					if ($attach->IsAllowSize())
					{
						CIMMessageParam::Set($arParams['ID'], Array('IS_EDITED' => $arParams['IS_EDITED'], 'ATTACH' => $attach));
					}
					else
					{
						throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
					}
				}
				else if ($arParams['ATTACH'])
				{
					throw new Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}

		if (isset($arParams['KEYBOARD']))
		{
			if (is_null($message))
			{
				$message = CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $arParams['ID']);
			}
			if (!$message)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}

			if (empty($arParams['KEYBOARD']) || $arParams['KEYBOARD'] == 'N')
			{
				CIMMessageParam::Set($arParams['ID'], Array('KEYBOARD' => 'N'));
			}
			else
			{
				$keyboard = Array();
				if (is_string($arParams['KEYBOARD']))
				{
					$arParams['KEYBOARD'] = \CUtil::JsObjectToPhp($arParams['KEYBOARD']);
				}
				if (!isset($arParams['KEYBOARD']['BUTTONS']))
				{
					$keyboard['BUTTONS'] = $arParams['KEYBOARD'];
				}
				else
				{
					$keyboard = $arParams['KEYBOARD'];
				}
				$keyboard['BOT_ID'] = $arParams['BOT_ID'];

				$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
				if ($keyboard)
				{
					if ($keyboard->isAllowSize())
					{
						CIMMessageParam::Set($arParams['ID'], Array('KEYBOARD' => $keyboard));
					}
					else
					{
						throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of keyboard", "KEYBOARD_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
					}
				}
				else if ($arParams['KEYBOARD'])
				{
					throw new Bitrix\Rest\RestException("Incorrect keyboard params", "KEYBOARD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}

		if (isset($arParams['MENU']))
		{
			if (is_null($message))
			{
				$message = CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $arParams['MESSAGE_ID'], $arParams['BOT_ID']);
			}
			if (!$message)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}

			if (empty($arParams['MENU']) || $arParams['MENU'] == 'N')
			{
				CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('MENU' => 'N'));
			}
			else
			{
				$menu = Array();
				if (is_string($arParams['MENU']))
				{
					$arParams['MENU'] = \CUtil::JsObjectToPhp($arParams['MENU']);
				}
				if (!isset($arParams['MENU']['ITEMS']))
				{
					$menu['ITEMS'] = $arParams['MENU'];
				}
				else
				{
					$menu = $arParams['MENU'];
				}
				$menu['BOT_ID'] = $arParams['BOT_ID'];

				$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);
				if ($menu)
				{
					if ($menu->isAllowSize())
					{
						CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('MENU' => $menu));
					}
					else
					{
						throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of menu", "MENU_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
					}
				}
				else if ($arParams['MENU'])
				{
					throw new Bitrix\Rest\RestException("Incorrect menu params", "menu_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}

		if (isset($arParams['MESSAGE']))
		{
			$urlPreview = isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == "N"? false: true;

			$res = CIMMessenger::Update($arParams['ID'], $arParams['MESSAGE'], $urlPreview);
			if (!$res)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}
		}
		CIMMessageParam::SendPull($arParams['ID'], Array('KEYBOARD', 'ATTACH', 'MENU'));

		return true;
	}

	public static function messageLike($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['MESSAGE_ID']))
		{
			$arParams['ID'] = $arParams['MESSAGE_ID'];
		}

		$arParams['ID'] = intval($arParams['ID']);
		if ($arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['ACTION'] = mb_strtolower($arParams['ACTION']);
		if (!in_array($arParams['ACTION'], Array('auto', 'plus', 'minus')))
		{
			$arParams['ACTION'] = 'auto';
		}

		$result = CIMMessenger::Like($arParams['ID'], $arParams['ACTION']);
		if ($result === false)
		{
			throw new Bitrix\Rest\RestException("Action completed without changes", "WITHOUT_CHANGES", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function messageCommand($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['MESSAGE_ID']))
		{
			$arParams['ID'] = $arParams['MESSAGE_ID'];
		}

		$arParams['ID'] = intval($arParams['ID']);
		if ($arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['BOT_ID'] = intval($arParams['BOT_ID']);
		if ($arParams['BOT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Bot ID can't be empty", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($arParams['COMMAND'] == '')
		{
			throw new Bitrix\Rest\RestException("Command can't be empty", "COMMAND_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = CIMMessenger::ExecCommand($arParams['ID'], $arParams['BOT_ID'], $arParams['COMMAND'], $arParams['COMMAND_PARAMS']);
		if ($result === false)
		{
			throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}


	public static function messageShare($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['MESSAGE_ID']))
		{
			$arParams['ID'] = $arParams['MESSAGE_ID'];
		}

		$arParams['ID'] = intval($arParams['ID']);
		if ($arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Dialog::hasAccess($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
		if (!$chatId)
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = CIMMessenger::Share($arParams['ID'], $arParams['TYPE']);
		if ($result === false)
		{
			throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function messageUserGet($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (
			$server->getAuthType() != \Bitrix\Rest\OAuth\Auth::AUTH_TYPE
			&& !self::isDebugEnabled()
		)
		{
			throw new \Bitrix\Rest\RestException("Access for this method allowed only by OAuth authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_WRONG_REQUEST);
		}

		global $USER;
		$userId = $USER->GetID();
		if ($userId <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['FIRST_ID']))
		{
			$options['FIRST_ID'] = intval($arParams['FIRST_ID']);
		}
		else
		{
			$options['LAST_ID'] = isset($arParams['LAST_ID']) && intval($arParams['LAST_ID']) > 0? intval($arParams['LAST_ID']): 0;
		}

		$options['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 500? 500: intval($arParams['LIMIT'])): 50;
		$options['JSON'] = 'Y';

		$forUser = $userId;
		if (isset($arParams['USER_ID']) && intval($arParams['USER_ID']) > 0 && $arParams['USER_ID'] != $userId)
		{
			if (
				(
					!\Bitrix\Im\User::getInstance($arParams['USER_ID'])->isActive() && !\Bitrix\Im\User::getInstance($arParams['USER_ID'])->isExtranet()
					|| \Bitrix\Im\User::getInstance($arParams['USER_ID'])->isConnector()
				)
				&&
				(
					$USER->IsAdmin()
					|| \Bitrix\Main\Loader::includeModule('bitrix24') && CBitrix24::IsPortalAdmin($userId)
				)
			)
			{
				$forUser = intval($arParams['USER_ID']);
				$options['SKIP_MESSAGE'] = 'Y';
			}
			else
			{
				throw new Bitrix\Rest\RestException("You don't have access to this user", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return \Bitrix\Im\User::getMessages($forUser, $options);
	}

	public static function notifyAdd($arParams, $n, CRestServer $server)
	{
		global $USER;

		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['TO']))
		{
			$arParams['USER_ID'] = $arParams['TO'];
		}
		$arParams['USER_ID'] = intval($arParams['USER_ID']);
		if ($arParams['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($server->getMethod() == "im.notify.personal.add")
		{
			$arParams['TYPE'] = 'USER';
		}
		else if ($server->getMethod() == "im.notify.system.add")
		{
			$arParams['TYPE'] = 'SYSTEM';
		}
		else if (!isset($arParams['TYPE']) || !in_array($arParams['TYPE'], Array('USER', 'SYSTEM')))
		{
			$arParams['TYPE'] = 'USER';
		}

		$arParams['MESSAGE'] = trim($arParams['MESSAGE']);
		if ($arParams['MESSAGE'] == '')
		{
			throw new Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$messageOut = "";
		$arParams['MESSAGE_OUT'] = trim($arParams['MESSAGE_OUT']);
		if ($arParams['TYPE'] == 'SYSTEM')
		{
			$fromUserId = 0;
			$notifyType = IM_NOTIFY_SYSTEM;

			$clientId = $server->getClientId();
			if ($clientId)
			{
				$result = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
				$result = $result->fetch();
				$moduleName = !empty($result['APP_NAME'])
					? $result['APP_NAME']
					: (!empty($result['APP_NAME_DEFAULT'])
						? $result['APP_NAME_DEFAULT']
						: $result['CODE']
					)
				;
				$message = $moduleName."#BR#".$arParams['MESSAGE'];

				if (!empty($arParams['MESSAGE_OUT']))
				{
					$messageOut = $moduleName."#BR#".$arParams['MESSAGE_OUT'];
				}
			}
			else
			{
				$message = $arParams['MESSAGE'];
			}
		}
		else
		{
			$fromUserId = $USER->GetID();
			$notifyType = IM_NOTIFY_FROM;
			$message = $arParams['MESSAGE'];
			if (!empty($arParams['MESSAGE_OUT']))
			{
				$messageOut = $arParams['MESSAGE_OUT'];
			}
		}

		$arMessageFields = array(
			"TO_USER_ID" => $arParams['USER_ID'],
			"FROM_USER_ID" => $fromUserId,
			"NOTIFY_TYPE" => $notifyType,
			"NOTIFY_MODULE" => "rest",
			"NOTIFY_EVENT" => "rest_notify",
			"NOTIFY_MESSAGE" => $message,
			"NOTIFY_MESSAGE_OUT" => $messageOut,
		);

		$clientId = $server->getClientId();
		if ($clientId)
		{
			if (!empty($arParams['TAG']))
			{
				$appKey = mb_substr(md5($server->getClientId()), 0, 5);
				$arMessageFields['NOTIFY_TAG'] = 'MP|'.$appKey.'|'.$arParams['TAG'];
			}
			if (!empty($arParams['SUB_TAG']))
			{
				$appKey = mb_substr(md5($server->getClientId()), 0, 5);
				$arMessageFields['NOTIFY_SUB_TAG'] = 'MP|'.$appKey.'|'.$arParams['SUB_TAG'];
			}
		}

		$attach = CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
		if ($attach)
		{
			if ($attach->IsAllowSize())
			{
				$arMessageFields['ATTACH'] = $attach;
			}
			else
			{
				throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else if ($arParams['ATTACH'])
		{
			throw new Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return CIMNotify::Add($arMessageFields);
	}

	public static function notifyGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (
			(isset($arParams['LAST_ID']) && !isset($arParams['LAST_TYPE'])) ||
			(!isset($arParams['LAST_ID']) && isset($arParams['LAST_TYPE']))
		)
		{
			throw new Bitrix\Rest\RestException(
				"Parameters LAST_ID and LAST_TYPE should be used together.",
				"LAST_ID_AND_LAST_TYPE",
				CRestServer::STATUS_WRONG_REQUEST
			);
		}

		if (isset($arParams['LAST_ID']) )
		{
			if (!preg_match('/^\d+$/', $arParams['LAST_ID']))
			{
				throw new Bitrix\Rest\RestException(
					"Last notification ID can't be string",
					"LAST_ID_STRING",
					CRestServer::STATUS_WRONG_REQUEST
				);
			}

			$options['LAST_ID'] = (int)$arParams['LAST_ID'];
			$options['LAST_TYPE'] = (int)$arParams['LAST_TYPE'];
		}

		if (isset($arParams['LIMIT']))
		{
			$options['LIMIT'] = (int)$arParams['LIMIT'] > 50 ? 50 : (int)$arParams['LIMIT'];
		}
		else
		{
			$options['LIMIT'] = 50;
		}

		$options['CONVERT_TEXT'] = isset($arParams['CONVERT_TEXT']) && $arParams['CONVERT_TEXT'] === 'Y';

		return (new \Bitrix\Im\Notify($options))->get();
	}

	public static function notifyDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['ID']))
		{
			$CIMNotify = new CIMNotify();
			return $CIMNotify->DeleteWithCheck($arParams['ID']);
		}

		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		if (!empty($arParams['TAG']))
		{
			$appKey = mb_substr(md5($clientId), 0, 5);
			$result = CIMNotify::DeleteByTag('MP|'.$appKey.'|'.$arParams['TAG']);
		}
		else if (!empty($arParams['SUB_TAG']))
		{
			$appKey = mb_substr(md5($clientId), 0, 5);
			$result = CIMNotify::DeleteBySubTag('MP|'.$appKey.'|'.$arParams['SUB_TAG']);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $result;
	}

	public static function notifyRead($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['ACTION']))
		{
			$arParams['ACTION'] = 'Y';
		}

		if (isset($arParams['ID']) && (int)$arParams['ID'] >= 0)
		{
			$CIMNotify = new CIMNotify();

			$onlyCurrent = $arParams['ONLY_CURRENT'] ?? null;
			$readAllFromId = $onlyCurrent !== 'Y';
			if ($arParams['ACTION'] === 'Y')
			{
				$CIMNotify->MarkNotifyRead($arParams['ID'], $readAllFromId);
			}
			else
			{
				$CIMNotify->MarkNotifyUnRead($arParams['ID'], $readAllFromId);
			}
		}

		return true;
	}

	public static function notifyReadList($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!isset($arParams['ACTION']))
		{
			$arParams['ACTION'] = 'Y';
		}

		if (!isset($arParams['IDS']) || !is_array($arParams['IDS']))
		{
			throw new Bitrix\Rest\RestException("No IDS param or it is not an array", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$CIMNotify = new CIMNotify();
		foreach ($arParams['IDS'] as $notificationId)
		{
			$notificationId = (int)$notificationId;
			if ($notificationId <= 0)
			{
				break;
			}

			if ($arParams['ACTION'] === 'Y')
			{
				$CIMNotify->MarkNotifyRead($notificationId);
			}
			else
			{
				$CIMNotify->MarkNotifyUnRead($notificationId);
			}
		}

		return true;
	}

	public static function notifyReadAll($arParams, $n, CRestServer $server)
	{
		$notify = new \CIMNotify();
		$notify->MarkNotifyRead(0, true);

		return true;
	}

	public static function notifyConfirm($arParams, $n, CRestServer $server): array
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['NOTIFY_ID']))
		{
			$arParams['NOTIFY_ID'] = (int)$arParams['NOTIFY_ID'];
		}

		if (isset($arParams['ID']))
		{
			$arParams['NOTIFY_ID'] = (int)$arParams['ID'];
		}

		if ($arParams['NOTIFY_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Notification ID can't be empty", "ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (empty($arParams['NOTIFY_VALUE']))
		{
			throw new Bitrix\Rest\RestException("Notification Value  can't be empty", "NOTIFY_VALUE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$CIMNotify = new CIMNotify();
		$result = $CIMNotify->Confirm($arParams['NOTIFY_ID'], $arParams['NOTIFY_VALUE']);

		return [
			'result_message' => $result
		];
	}

	public static function notifyAnswer($arParams, $n, CRestServer $server): array
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['NOTIFY_ID']))
		{
			$arParams['NOTIFY_ID'] = (int)$arParams['NOTIFY_ID'];
		}

		if (isset($arParams['ID']))
		{
			$arParams['NOTIFY_ID'] = (int)$arParams['ID'];
		}

		if ($arParams['NOTIFY_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Notification ID can't be empty", "ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (empty($arParams['ANSWER_TEXT']))
		{
			throw new Bitrix\Rest\RestException("ANSWER_TEXT can't be empty", "ANSWER_TEXT_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$CIMNotify = new CIMNotify();
		$result = $CIMNotify->Answer($arParams['NOTIFY_ID'], $arParams['ANSWER_TEXT']);

		return [
			'result_message' => $result
		];
	}

	public static function notifyHistorySearch($arParams, $n, CRestServer $server): array
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (
			!($arParams['SEARCH_TYPE'] ?? null)
			&& !($arParams['SEARCH_DATE'] ?? null)
			&& mb_strlen(trim($arParams['SEARCH_TEXT'])) < 3
		)
		{
			throw new Bitrix\Rest\RestException("SEARCH_TEXT can't be less then 3 symbols", "SEARCH_TEXT_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['SEARCH_TEXT']))
		{
			$options['SEARCH_TEXT'] = $arParams['SEARCH_TEXT'];
		}
		if (isset($arParams['SEARCH_TYPE']))
		{
			$options['SEARCH_TYPE'] = $arParams['SEARCH_TYPE'];
		}
		if (isset($arParams['SEARCH_DATE']))
		{
			$options['SEARCH_DATE'] = $arParams['SEARCH_DATE'];
		}
		if (isset($arParams['LAST_ID']))
		{
			if (!preg_match('/^\d+$/', $arParams['LAST_ID']))
			{
				throw new Bitrix\Rest\RestException(
					"Last notification ID can't be string",
					"LAST_ID_STRING",
					CRestServer::STATUS_WRONG_REQUEST
				);
			}

			$options['LAST_ID'] = (int)$arParams['LAST_ID'];
		}

		if (isset($arParams['LIMIT']))
		{
			$options['LIMIT'] = (int)$arParams['LIMIT'] > 50 ? 50 : (int)$arParams['LIMIT'];
		}
		else
		{
			$options['LIMIT'] = 50;
		}

		$options['CONVERT_TEXT'] = isset($arParams['CONVERT_TEXT']) && $arParams['CONVERT_TEXT'] === 'Y';

		$notify = new \Bitrix\Im\Notify($options);

		return $notify->search();
	}

	public static function notifySchemaGet($arParams, $n, CRestServer $server): array
	{
		$schemaResult = [];
		$schema = CIMNotifySchema::GetNotifySchema();
		foreach ($schema as $moduleId => $notifyTypes)
		{
			$list = [];
			foreach ($notifyTypes['NOTIFY'] as $notifyId => $notify)
			{
				$list[] = [
					'ID' => $moduleId.'|'.$notifyId,
					'NAME' => $notify['NAME']
				];
			}

			if ($notifyTypes['NAME'] === '')
			{
				$module = CModule::CreateModuleObject($moduleId);
				$moduleName = $module->MODULE_NAME;
			}
			else
			{
				$moduleName = $notifyTypes['NAME'];
			}

			$schemaResult[$moduleId] = [
				'NAME' => $moduleName,
				'MODULE_ID' => $moduleId,
				'LIST' => $list,
			];
		}

		return $schemaResult;
	}

	public static function diskFolderListGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);
		$lastId = $arParams['LAST_ID'] ?? null;
		$fileName = $arParams['SEARCH_FILE_NAME'] ?? null;
		$limit = self::getLimit($arParams);
		if (!isset($arParams['CHAT_ID']))
		{
			throw new \Bitrix\Rest\RestException('CHAT_ID can`t be empty', 'CHAT_ID_EMPTY', \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = $arParams['CHAT_ID'];
		$chat = \Bitrix\Im\V2\Chat::getInstance($chatId);

		if (!$chat->hasAccess())
		{
			throw new \Bitrix\Rest\RestException('You do not have access to this chat', Bitrix\Im\V2\Rest\RestError::ACCESS_ERROR, \CRestServer::STATUS_FORBIDDEN);
		}

		$files = (new \Bitrix\Im\V2\Link\File\FileService())->getFilesBeforeMigrationFinished($chatId, $limit, $lastId, $fileName);

		return (new \Bitrix\Im\V2\Rest\RestAdapter($files))->toRestFormat();
	}

	public static function diskFolderGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			if (!\Bitrix\Im\Dialog::hasAccess($arParams['DIALOG_ID']))
			{
				throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
			}

			$chatId = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			if (!$chatId)
			{
				throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$chatId = intval($arParams['CHAT_ID']);

			if ($chatId <= 0)
			{
				throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}

			if (!\Bitrix\Im\Dialog::hasAccess('chat'.$chatId))
			{
				throw new Bitrix\Rest\RestException("You do not have access to the specified dialog", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
			}
		}

		$folderModel = CIMDisk::GetFolderModel($chatId);
		if (!$folderModel)
		{
			throw new Bitrix\Rest\RestException("Internal server error.", "INTERNAL_ERROR", CRestServer::STATUS_INTERNAL);
		}

		return Array(
			'ID' => (int)$folderModel->getId()
		);
	}

	public static function diskFileCommit($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['DIALOG_ID']))
		{
			if (\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
			{
				$arParams['CHAT_ID'] = \Bitrix\Im\Dialog::getChatId($arParams['DIALOG_ID']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$chatId = intval($arParams['CHAT_ID']);
		if ($chatId <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['MESSAGE'] = trim($arParams['MESSAGE']);
		if ($arParams['MESSAGE'] == '')
		{
			unset($arParams['MESSAGE']);
		}

		$arParams['SILENT_MODE'] = $arParams['SILENT_MODE'] == 'Y';

		$chatRelation = CIMChat::GetRelationById($chatId, false, true, false);
		if (!$chatRelation[CIMDisk::GetUserId()])
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (
			CIMChat::GetGeneralChatId() == $chatId
			&& !CIMChat::CanSendMessageToGeneralChat()
		)
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$files = Array();
		if (isset($arParams['FILE_ID']))
		{
			if (!is_array($arParams['FILE_ID']))
			{
				$arParams['FILE_ID'] = Array($arParams['FILE_ID']);
			}
			foreach ($arParams['FILE_ID'] as $fileId)
			{
				$files[$fileId] = 'disk'.$fileId;
			}
		}
		else if (isset($arParams['DISK_ID']))
		{
			if (!is_array($arParams['DISK_ID']))
			{
				$arParams['DISK_ID'] = Array($arParams['DISK_ID']);
			}
			foreach ($arParams['DISK_ID'] as $fileId)
			{
				$files[$fileId] = 'disk'.$fileId;
			}

			if (isset($arParams['SYMLINK']))
			{
				$arParams['SYMLINK'] = $arParams['SYMLINK'] == 'Y';
			}
		}
		else if (isset($arParams['UPLOAD_ID']))
		{
			if (!is_array($arParams['UPLOAD_ID']))
			{
				$arParams['UPLOAD_ID'] = Array($arParams['UPLOAD_ID']);
			}
			foreach ($arParams['UPLOAD_ID'] as $fileId)
			{
				$files[$fileId] = 'upload'.$fileId;
			}
		}

		if (empty($files))
		{
			throw new Bitrix\Rest\RestException("List of files in not specified", "FILES_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['TEMPLATE_ID']) && !empty($arParams['TEMPLATE_ID']))
		{
			$arParams['TEMPLATE_ID'] = mb_substr((string)$arParams['TEMPLATE_ID'], 0, 255);
		}

		if (isset($arParams['FILE_TEMPLATE_ID']) && !empty($arParams['FILE_TEMPLATE_ID']))
		{
			$arParams['FILE_TEMPLATE_ID'] = mb_substr((string)$arParams['FILE_TEMPLATE_ID'], 0, 255);
		}

		$result = CIMDisk::UploadFileFromDisk($chatId, array_values($files), $arParams['MESSAGE'], [
			'LINES_SILENT_MODE' => $arParams['SILENT_MODE'],
			'TEMPLATE_ID' => $arParams['TEMPLATE_ID']?:'',
			'FILE_TEMPLATE_ID' => $arParams['FILE_TEMPLATE_ID']?:'',
			'SYMLINK' => $arParams['SYMLINK']?:false,
		]);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Error during saving file to chat", "SAVE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $result;
	}

	public static function diskRecordShare($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$dialogId = $arParams['DIALOG_ID'];
		if (!\Bitrix\Im\Common::isDialogId($dialogId))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!\Bitrix\Im\Dialog::hasAccess($dialogId))
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = \Bitrix\Im\Dialog::getChatId($dialogId);
		if ($chatId <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID isn't found", "CHAT_NOT_FOUND", CRestServer::STATUS_WRONG_REQUEST);
		}

		$diskId = (int)$arParams['DISK_ID'];
		if ($diskId <= 0)
		{
			throw new Bitrix\Rest\RestException("Disk ID can't be empty", "DISK_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \CIMDisk::RecordShare($chatId, $diskId);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Error during record share", "EXECUTE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}


	public static function diskFileDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$chatId = intval($arParams['CHAT_ID']);
		if ($chatId <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$fileId = isset($arParams['FILE_ID'])? intval($arParams['FILE_ID']): intval($arParams['DISK_ID']);
		if ($fileId <= 0)
		{
			throw new Bitrix\Rest\RestException("File ID can't be empty", "FILE_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return CIMDisk::DeleteFile($chatId, $fileId);
	}

	public static function diskFileSave($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$fileId = isset($arParams['FILE_ID'])? intval($arParams['FILE_ID']): intval($arParams['DISK_ID']);
		if ($fileId <= 0)
		{
			throw new Bitrix\Rest\RestException("File ID can't be empty", "FILE_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = CIMDisk::SaveToLocalDisk($fileId);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("File ID can't be saved", "FILE_SAVE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return [
			'folder' => [
				'id' => (int)$result['FOLDER']->getId(),
				'name' => $result['FOLDER']->getName()
			],
			'file' => [
				'id' => (int)$result['FILE']->getId(),
				'name' => $result['FILE']->getName()
			],
		];
	}

	public static function counterGet($arParams, $n, CRestServer $server)
	{
		$counters = \Bitrix\Im\Counter::get();

		$onlyCounterParam = $arParams['ONLY_COUNTER'] ?? null;
		$jsonParam = $arParams['JSON'] ?? null;

		if ($onlyCounterParam)
		{
			$counters = $counters['TYPE'];
		}

		if ($jsonParam === 'Y')
		{
			$counters = \Bitrix\Im\Common::toJson($counters);
		}

		return $counters;
	}

	public static function notImplemented($arParams, $n, CRestServer $server)
	{
		throw new Bitrix\Rest\RestException("Method isn't implemented yet", "NOT_IMPLEMENTED", CRestServer::STATUS_NOT_FOUND);
	}

	/* BotAPI */

	public static function botRegister($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$customClientId = false;
		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$customClientId = true;
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		if ($customClientId)
		{
			$arApp = ['ID' => '', 'CLIENT_ID' => $arParams['CLIENT_ID']];
		}
		else
		{
			$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
			$arApp = $dbRes->fetch();
		}

		if (isset($arParams['EVENT_HANDLER']) && !empty($arParams['EVENT_HANDLER']))
		{
			$arParams['EVENT_MESSAGE_ADD'] = $arParams['EVENT_MESSAGE_UPDATE'] = $arParams['EVENT_MESSAGE_DELETE'] = $arParams['EVENT_WELCOME_MESSAGE'] = $arParams['EVENT_BOT_DELETE']	= $arParams['EVENT_HANDLER'];
		}

		if (isset($arParams['EVENT_MESSAGE_ADD']) && !empty($arParams['EVENT_MESSAGE_ADD']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_MESSAGE_ADD'] = $arParams['EVENT_MESSAGE_ADD'].(mb_strpos($arParams['EVENT_MESSAGE_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['EVENT_MESSAGE_ADD'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_MESSAGE_ADD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Handler for \"Message add\" event isn't specified", "EVENT_MESSAGE_ADD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['EVENT_MESSAGE_UPDATE']) && !empty($arParams['EVENT_MESSAGE_UPDATE']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_MESSAGE_UPDATE'] = $arParams['EVENT_MESSAGE_UPDATE'].(mb_strpos($arParams['EVENT_MESSAGE_UPDATE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['EVENT_MESSAGE_UPDATE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_MESSAGE_UPDATE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['EVENT_MESSAGE_DELETE']) && !empty($arParams['EVENT_MESSAGE_DELETE']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_MESSAGE_DELETE'] = $arParams['EVENT_MESSAGE_DELETE'].(mb_strpos($arParams['EVENT_MESSAGE_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['EVENT_MESSAGE_DELETE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_MESSAGE_DELETE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['EVENT_WELCOME_MESSAGE']) && !empty($arParams['EVENT_WELCOME_MESSAGE']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_WELCOME_MESSAGE'] = $arParams['EVENT_WELCOME_MESSAGE'].(mb_strpos($arParams['EVENT_WELCOME_MESSAGE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['EVENT_WELCOME_MESSAGE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_WELCOME_MESSAGE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Handler for \"Welcome message\" event isn't specified", "EVENT_WELCOME_MESSAGE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['EVENT_BOT_DELETE']) && !empty($arParams['EVENT_BOT_DELETE']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_BOT_DELETE'] = $arParams['EVENT_BOT_DELETE'].(mb_strpos($arParams['EVENT_BOT_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['EVENT_BOT_DELETE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_BOT_DELETE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Handler for \"Bot delete\" event isn't specified", "EVENT_BOT_DELETE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!isset($arParams['CODE']) || empty($arParams['CODE']))
		{
			throw new Bitrix\Rest\RestException("Bot code isn't specified", "CODE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (CModule::IncludeModule('bitrix24'))
		{
			$counter = \Bitrix\Im\Model\BotTable::getCount(array('=APP_ID' => $clientId));
			$restRegisterLimit = \Bitrix\Bitrix24\Feature::getVariable('imbot_rest_register_limit')?: 5;

			if ($counter >= $restRegisterLimit)
			{
				throw new Bitrix\Rest\RestException("Has reached the maximum number of bots for application (max: $restRegisterLimit)", "MAX_COUNT_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['TYPE'] = in_array($arParams['TYPE'], Array('O', 'B', 'H', 'S'))? $arParams['TYPE']: 'B';
		$arParams['OPENLINE'] = $arParams['OPENLINE'] == 'Y'? 'Y': 'N';

		if (!(in_array($arParams['TYPE'], Array('S', 'O')) || $arParams['OPENLINE'] == 'Y'))
		{
			unset($arParams['EVENT_MESSAGE_UPDATE']);
			unset($arParams['EVENT_MESSAGE_DELETE']);
		}

		$properties = Array();
		if (isset($arParams['PROPERTIES']['NAME']))
		{
			$properties['NAME'] = $arParams['PROPERTIES']['NAME'];
		}
		if (isset($arParams['PROPERTIES']['LAST_NAME']))
		{
			$properties['LAST_NAME'] = $arParams['PROPERTIES']['LAST_NAME'];
		}
		if (!(isset($properties['NAME']) || isset($properties['LAST_NAME'])))
		{
			throw new Bitrix\Rest\RestException("Bot name isn't specified", "NAME_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['PROPERTIES']['COLOR']))
		{
			$properties['COLOR'] = $arParams['PROPERTIES']['COLOR'];
		}
		if (isset($arParams['PROPERTIES']['EMAIL']))
		{
			$properties['EMAIL'] = $arParams['PROPERTIES']['EMAIL'];
		}
		if (isset($arParams['PROPERTIES']['PERSONAL_BIRTHDAY']))
		{
			$birthday = new \Bitrix\Main\Type\DateTime($arParams['PROPERTIES']['PERSONAL_BIRTHDAY'].' 19:45:00', 'Y-m-d H:i:s');
			$birthday = $birthday->format(\Bitrix\Main\Type\Date::convertFormatToPhp(\CSite::GetDateFormat('SHORT')));

			$properties['PERSONAL_BIRTHDAY'] = $birthday;
		}
		if (isset($arParams['PROPERTIES']['WORK_POSITION']))
		{
			$properties['WORK_POSITION'] = $arParams['PROPERTIES']['WORK_POSITION'];
		}
		if (isset($arParams['PROPERTIES']['PERSONAL_WWW']))
		{
			$properties['PERSONAL_WWW'] = $arParams['PROPERTIES']['PERSONAL_WWW'];
		}
		if (isset($arParams['PROPERTIES']['PERSONAL_GENDER']))
		{
			$properties['PERSONAL_GENDER'] = $arParams['PROPERTIES']['PERSONAL_GENDER'];
		}
		if (isset($arParams['PROPERTIES']['PERSONAL_PHOTO']))
		{
			$avatar = \CRestUtil::saveFile($arParams['PROPERTIES']['PERSONAL_PHOTO'], $arParams['CODE'].'.png');
			$imageCheck = (new \Bitrix\Main\File\Image($avatar["tmp_name"]))->getInfo();
			if(
				!$imageCheck
				|| !$imageCheck->getWidth()
				|| $imageCheck->getWidth() > 5000
				|| !$imageCheck->getHeight()
				|| $imageCheck->getHeight() > 5000
			)
			{
				$avatar = null;
			}

			if (isset($avatar) && mb_strpos($avatar['type'], "image/") === 0)
			{
				$avatar['MODULE_ID'] = 'imbot';
				$properties['PERSONAL_PHOTO'] = $avatar;
			}
		}

		$botId = \Bitrix\Im\Bot::register(Array(
											  'APP_ID' => $clientId,
											  'CODE' => $arParams['CODE'],
											  'TYPE' => $arParams['TYPE'],
											  'OPENLINE' => $arParams['OPENLINE'],
											  'MODULE_ID' => 'rest',
											  'PROPERTIES' => $properties
										  ));
		if ($botId)
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', $arParams['EVENT_MESSAGE_ADD']);

			if ($arParams['EVENT_MESSAGE_UPDATE'])
			{
				self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', true);
				self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', $arParams['EVENT_MESSAGE_UPDATE']);
			}

			if ($arParams['EVENT_MESSAGE_DELETE'])
			{
				self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', true);
				self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', $arParams['EVENT_MESSAGE_DELETE']);
			}

			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', $arParams['EVENT_WELCOME_MESSAGE']);

			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', $arParams['EVENT_BOT_DELETE']);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Bot can't be created", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $botId;
	}

	public static function botUnRegister($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$customClientId = false;
		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$customClientId = true;
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (!isset($bots[$arParams['BOT_ID']]))
		{
			throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
		{
			throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \Bitrix\Im\Bot::unRegister(Array('BOT_ID' => $arParams['BOT_ID']));
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Bot can't be deleted", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($customClientId)
		{
			self::unbindEvent("", $arParams['CLIENT_ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', true);
			self::unbindEvent("", $arParams['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', true);
			self::unbindEvent("", $arParams['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', true);
			self::unbindEvent("", $arParams['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', true);
			self::unbindEvent("", $arParams['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', true);
		}

		return true;
	}

	public static function botUpdate($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		$customClientId = false;
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$customClientId = true;
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (!isset($bots[$arParams['BOT_ID']]))
		{
			throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
		{
			throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($customClientId)
		{
			$arApp = ['ID' => '', 'CLIENT_ID' => $arParams['CLIENT_ID']];
		}
		else
		{
			$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
			$arApp = $dbRes->fetch();
		}

		$updateEvents = Array();

		if (isset($arParams['FIELDS']['EVENT_HANDLER']) && !empty($arParams['FIELDS']['EVENT_HANDLER']))
		{
			$arParams['FIELDS']['EVENT_MESSAGE_ADD'] = $arParams['FIELDS']['EVENT_MESSAGE_UPDATE'] = $arParams['FIELDS']['EVENT_MESSAGE_DELETE'] = $arParams['FIELDS']['EVENT_WELCOME_MESSAGE'] = $arParams['FIELDS']['EVENT_BOT_DELETE'] = $arParams['FIELDS']['EVENT_HANDLER'];
		}

		if (isset($arParams['FIELDS']['EVENT_MESSAGE_ADD']) && !empty($arParams['FIELDS']['EVENT_MESSAGE_ADD']))
		{
			$updateEvents['EVENT_MESSAGE_ADD'] = $arParams['FIELDS']['EVENT_MESSAGE_ADD'];
			if ($customClientId)
			{
				$updateEvents['EVENT_MESSAGE_ADD'] = $updateEvents['EVENT_MESSAGE_ADD'].(mb_strpos($updateEvents['EVENT_MESSAGE_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['FIELDS']['EVENT_MESSAGE_ADD'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_MESSAGE_ADD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['FIELDS']['EVENT_MESSAGE_UPDATE']) && !empty($arParams['FIELDS']['EVENT_MESSAGE_UPDATE']))
		{
			$updateEvents['EVENT_MESSAGE_UPDATE'] = $arParams['FIELDS']['EVENT_MESSAGE_UPDATE'];
			if ($customClientId)
			{
				$updateEvents['EVENT_MESSAGE_UPDATE'] = $updateEvents['EVENT_MESSAGE_UPDATE'].(mb_strpos($updateEvents['EVENT_MESSAGE_UPDATE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['FIELDS']['EVENT_MESSAGE_UPDATE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_MESSAGE_UPDATE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['FIELDS']['EVENT_MESSAGE_DELETE']) && !empty($arParams['FIELDS']['EVENT_MESSAGE_DELETE']))
		{
			$updateEvents['EVENT_MESSAGE_DELETE'] = $arParams['FIELDS']['EVENT_MESSAGE_DELETE'];
			if ($customClientId)
			{
				$updateEvents['EVENT_MESSAGE_DELETE'] = $updateEvents['EVENT_MESSAGE_DELETE'].(mb_strpos($updateEvents['EVENT_MESSAGE_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['FIELDS']['EVENT_MESSAGE_DELETE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_MESSAGE_DELETE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['FIELDS']['EVENT_WELCOME_MESSAGE']) && !empty($arParams['FIELDS']['EVENT_WELCOME_MESSAGE']))
		{
			$updateEvents['EVENT_WELCOME_MESSAGE'] = $arParams['FIELDS']['EVENT_WELCOME_MESSAGE'];
			if ($customClientId)
			{
				$updateEvents['EVENT_WELCOME_MESSAGE'] = $updateEvents['EVENT_WELCOME_MESSAGE'].(mb_strpos($updateEvents['EVENT_WELCOME_MESSAGE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['FIELDS']['EVENT_WELCOME_MESSAGE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_WELCOME_MESSAGE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['FIELDS']['EVENT_BOT_DELETE']) && !empty($arParams['FIELDS']['EVENT_BOT_DELETE']))
		{
			$updateEvents['EVENT_BOT_DELETE'] = $arParams['FIELDS']['EVENT_BOT_DELETE'];
			if ($customClientId)
			{
				$updateEvents['EVENT_BOT_DELETE'] = $updateEvents['EVENT_BOT_DELETE'].(mb_strpos($updateEvents['EVENT_BOT_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['FIELDS']['EVENT_BOT_DELETE'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_BOT_DELETE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$updateFields = Array();

		if (isset($arParams['FIELDS']['CODE']) && !empty($arParams['FIELDS']['CODE']))
		{
			$updateFields['CODE'] = $arParams['FIELDS']['CODE'];
		}

		if (isset($arParams['FIELDS']['TYPE']) && !empty($arParams['FIELDS']['TYPE']) && in_array($arParams['TYPE'], Array('O', 'B', 'H')))
		{
			$updateFields['TYPE'] = $arParams['FIELDS']['TYPE'];
		}

		if (isset($arParams['FIELDS']['OPENLINE']) && !empty($arParams['FIELDS']['OPENLINE']))
		{
			$updateFields['OPENLINE'] = $arParams['FIELDS']['OPENLINE'];
		}

		$properties = Array();
		if (isset($arParams['FIELDS']['PROPERTIES']['NAME']))
		{
			$properties['NAME'] = $arParams['FIELDS']['PROPERTIES']['NAME'];
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['LAST_NAME']))
		{
			$properties['LAST_NAME'] = $arParams['FIELDS']['PROPERTIES']['LAST_NAME'];
		}

		if (isset($properties['NAME']) && empty($properties['NAME']) && isset($properties['LAST_NAME']) && empty($properties['LAST_NAME']))
		{
			throw new Bitrix\Rest\RestException("Bot name isn't specified", "NAME_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['FIELDS']['PROPERTIES']['COLOR']))
		{
			$properties['COLOR'] = $arParams['FIELDS']['PROPERTIES']['COLOR'];
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['EMAIL']))
		{
			$properties['EMAIL'] = $arParams['FIELDS']['PROPERTIES']['EMAIL'];
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['PERSONAL_BIRTHDAY']))
		{
			$birthday = new \Bitrix\Main\Type\DateTime($arParams['FIELDS']['PROPERTIES']['PERSONAL_BIRTHDAY'].' 19:45:00', 'Y-m-d H:i:s');
			$birthday = $birthday->format(\Bitrix\Main\Type\Date::convertFormatToPhp(\CSite::GetDateFormat('SHORT')));

			$properties['PERSONAL_BIRTHDAY'] = $birthday;
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['WORK_POSITION']))
		{
			$properties['WORK_POSITION'] = $arParams['FIELDS']['PROPERTIES']['WORK_POSITION'];
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['PERSONAL_WWW']))
		{
			$properties['PERSONAL_WWW'] = $arParams['FIELDS']['PROPERTIES']['PERSONAL_WWW'];
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['PERSONAL_GENDER']))
		{
			$properties['PERSONAL_GENDER'] = $arParams['FIELDS']['PROPERTIES']['PERSONAL_GENDER'];
		}
		if (isset($arParams['FIELDS']['PROPERTIES']['PERSONAL_PHOTO']))
		{
			$avatar = \CRestUtil::saveFile($arParams['FIELDS']['PROPERTIES']['PERSONAL_PHOTO'], $bots[$arParams['BOT_ID']]['CODE'].'.png');
			$imageCheck = (new \Bitrix\Main\File\Image($avatar["tmp_name"]))->getInfo();
			if(
				!$imageCheck
				|| !$imageCheck->getWidth()
				|| $imageCheck->getWidth() > 5000
				|| !$imageCheck->getHeight()
				|| $imageCheck->getHeight() > 5000
			)
			{
				$avatar = null;
			}

			if ($avatar && mb_strpos($avatar['type'], "image/") === 0)
			{
				$avatar['MODULE_ID'] = 'imbot';
				$properties['PERSONAL_PHOTO'] = $avatar;
			}
		}

		if (!empty($properties))
		{
			$updateFields['PROPERTIES'] = $properties;
		}

		if (empty($updateFields))
		{
			if (empty($updateEvents))
			{
				throw new Bitrix\Rest\RestException("Update fields can't be empty", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$result = \Bitrix\Im\Bot::update(Array('BOT_ID' => $arParams['BOT_ID']), $updateFields);
			if (!$result)
			{
				throw new Bitrix\Rest\RestException("Bot can't be updated", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($updateEvents['EVENT_MESSAGE_ADD']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', $updateEvents['EVENT_MESSAGE_ADD']);
		}
		if (isset($updateEvents['EVENT_MESSAGE_UPDATE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', $updateEvents['EVENT_MESSAGE_UPDATE']);
		}
		if (isset($updateEvents['EVENT_MESSAGE_DELETE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', $updateEvents['EVENT_MESSAGE_DELETE']);
		}
		if (isset($updateEvents['EVENT_WELCOME_MESSAGE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', $updateEvents['EVENT_WELCOME_MESSAGE']);
		}
		if (isset($updateEvents['EVENT_BOT_DELETE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', $updateEvents['EVENT_BOT_DELETE']);
		}

		return true;
	}

	public static function botMessageAdd($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (isset($arParams['BOT_ID']))
		{
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($clientId && $bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$botFound = false;
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $clientId)
				{
					$botFound = true;
					$arParams['BOT_ID'] = $bot['BOT_ID'];
					break;
				}
			}
			if (!$botFound)
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arMessageFields = Array();

		if (intval($arParams['FROM_USER_ID']) && intval($arParams['TO_USER_ID']))
		{
			$arParams['SYSTEM'] = 'Y';
			$arMessageFields['FROM_USER_ID'] = intval($arParams['FROM_USER_ID']);
			$arMessageFields['TO_USER_ID'] = intval($arParams['TO_USER_ID']);
		}
		else
		{
			$arMessageFields['DIALOG_ID'] = $arParams['DIALOG_ID'];
			if (!\Bitrix\Im\Common::isDialogId($arMessageFields['DIALOG_ID']))
			{
				throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arMessageFields['MESSAGE'] = trim($arParams['MESSAGE']);
		if ($arMessageFields['MESSAGE'] == '')
		{
			throw new Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['ATTACH']) && !empty($arParams['ATTACH']))
		{
			$attach = CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
			if ($attach)
			{
				if ($attach->IsAllowSize())
				{
					$arMessageFields['ATTACH'] = $attach;
				}
				else
				{
					throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
			else if ($arParams['ATTACH'])
			{
				throw new Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['KEYBOARD']) && !empty($arParams['KEYBOARD']))
		{
			$keyboard = Array();
			if (is_string($arParams['KEYBOARD']))
			{
				$arParams['KEYBOARD'] = \CUtil::JsObjectToPhp($arParams['KEYBOARD']);
			}
			if (!isset($arParams['KEYBOARD']['BUTTONS']))
			{
				$keyboard['BUTTONS'] = $arParams['KEYBOARD'];
			}
			else
			{
				$keyboard = $arParams['KEYBOARD'];
			}
			$keyboard['BOT_ID'] = $arParams['BOT_ID'];

			$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			if ($keyboard)
			{
				$arMessageFields['KEYBOARD'] = $keyboard;
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect keyboard params", "KEYBOARD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['MENU']) && !empty($arParams['MENU']))
		{
			$menu = Array();
			if (is_string($arParams['MENU']))
			{
				$arParams['MENU'] = \CUtil::JsObjectToPhp($arParams['MENU']);
			}
			if (!isset($arParams['MENU']['ITEMS']))
			{
				$menu['ITEMS'] = $arParams['MENU'];
			}
			else
			{
				$menu = $arParams['MENU'];
			}
			$menu['BOT_ID'] = $arParams['BOT_ID'];

			$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);
			if ($menu)
			{
				$arMessageFields['MENU'] = $menu;
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect menu params", "MENU_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y')
		{
			$arMessageFields['SYSTEM'] = 'Y';
		}

		if (isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == 'N')
		{
			$arMessageFields['URL_PREVIEW'] = 'N';
		}

		if (isset($arParams['SKIP_CONNECTOR']) && mb_strtoupper($arParams['SKIP_CONNECTOR']) == 'Y')
		{
			$arMessageFields['SKIP_CONNECTOR'] = 'Y';
			$arMessageFields['SILENT_CONNECTOR'] = 'Y';
		}

		$id = \Bitrix\Im\Bot::addMessage(array('BOT_ID' => $arParams['BOT_ID']), $arMessageFields);
		if (!$id)
		{
			throw new Bitrix\Rest\RestException("Message isn't added", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $id;

	}

	public static function botMessageUpdate($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (isset($arParams['BOT_ID']))
		{
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($clientId && $bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$botFound = false;
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $clientId)
				{
					$botFound = true;
					$arParams['BOT_ID'] = $bot['BOT_ID'];
					break;
				}
			}
			if (!$botFound)
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);
		if ($arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$message = null;
		if (isset($arParams['ATTACH']))
		{
			$message = CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $arParams['MESSAGE_ID'], $arParams['BOT_ID']);
			if (!$message)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}

			if (empty($arParams['ATTACH']) || $arParams['ATTACH'] == 'N')
			{
				CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('ATTACH' => Array()));
			}
			else
			{
				$attach = CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
				if ($attach)
				{
					if ($attach->IsAllowSize())
					{
						CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('ATTACH' => $attach));
					}
					else
					{
						throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
					}
				}
				else if ($arParams['ATTACH'])
				{
					throw new Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}


		if (isset($arParams['KEYBOARD']))
		{
			if (is_null($message))
			{
				$message = CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $arParams['MESSAGE_ID'], $arParams['BOT_ID']);
			}
			if (!$message)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}

			if (empty($arParams['KEYBOARD']) || $arParams['KEYBOARD'] == 'N')
			{
				CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('KEYBOARD' => 'N'));
			}
			else
			{
				$keyboard = Array();
				if (is_string($arParams['KEYBOARD']))
				{
					$arParams['KEYBOARD'] = \CUtil::JsObjectToPhp($arParams['KEYBOARD']);
				}
				if (!isset($arParams['KEYBOARD']['BUTTONS']))
				{
					$keyboard['BUTTONS'] = $arParams['KEYBOARD'];
				}
				else
				{
					$keyboard = $arParams['KEYBOARD'];
				}
				$keyboard['BOT_ID'] = $arParams['BOT_ID'];

				$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
				if ($keyboard)
				{
					if ($keyboard->isAllowSize())
					{
						CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('KEYBOARD' => $keyboard));
					}
					else
					{
						throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of keyboard", "KEYBOARD_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
					}
				}
				else if ($arParams['KEYBOARD'])
				{
					throw new Bitrix\Rest\RestException("Incorrect keyboard params", "KEYBOARD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}

		if (isset($arParams['MENU']))
		{
			if (is_null($message))
			{
				$message = CIMMessenger::CheckPossibilityUpdateMessage(IM_CHECK_UPDATE, $arParams['MESSAGE_ID'], $arParams['BOT_ID']);
			}
			if (!$message)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}

			if (empty($arParams['MENU']) || $arParams['MENU'] == 'N')
			{
				CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('MENU' => 'N'));
			}
			else
			{
				$menu = Array();
				if (is_string($arParams['MENU']))
				{
					$arParams['MENU'] = \CUtil::JsObjectToPhp($arParams['MENU']);
				}
				if (!isset($arParams['MENU']['ITEMS']))
				{
					$menu['ITEMS'] = $arParams['MENU'];
				}
				else
				{
					$menu = $arParams['MENU'];
				}
				$menu['BOT_ID'] = $arParams['BOT_ID'];

				$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);
				if ($menu)
				{
					if ($menu->isAllowSize())
					{
						CIMMessageParam::Set($arParams['MESSAGE_ID'], Array('MENU' => $menu));
					}
					else
					{
						throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of menu", "MENU_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
					}
				}
				else if ($arParams['MENU'])
				{
					throw new Bitrix\Rest\RestException("Incorrect menu params", "menu_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
		}

		if (isset($arParams['MESSAGE']))
		{
			$urlPreview = isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == "N"? false: true;
			$skipConnector = isset($arParams['SKIP_CONNECTOR']) && mb_strtoupper($arParams['SKIP_CONNECTOR']) == "Y"? true: false;

			$res = CIMMessenger::Update($arParams['MESSAGE_ID'], $arParams['MESSAGE'], $urlPreview, false, $arParams['BOT_ID'], $skipConnector);
			if (!$res)
			{
				throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
			}
		}

		CIMMessageParam::SendPull($arParams['MESSAGE_ID'], Array('KEYBOARD', 'ATTACH', 'MENU'));

		return true;
	}

	public static function botMessageDelete($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (isset($arParams['BOT_ID']))
		{
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($clientId && $bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$botFound = false;
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $clientId)
				{
					$botFound = true;
					$arParams['BOT_ID'] = $bot['BOT_ID'];
					break;
				}
			}
			if (!$botFound)
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);
		if ($arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$res = CIMMessenger::Delete($arParams['MESSAGE_ID'], $arParams['BOT_ID'], $arParams['COMPLETE'] == 'Y');
		if (!$res)
		{
			throw new Bitrix\Rest\RestException("Time has expired for modification or you don't have access", "CANT_EDIT_MESSAGE", CRestServer::STATUS_FORBIDDEN);
		}

		return true;
	}

	public static function botMessageLike($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (isset($arParams['BOT_ID']))
		{
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($clientId && $bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$botFound = false;
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $clientId)
				{
					$botFound = true;
					$arParams['BOT_ID'] = $bot['BOT_ID'];
					break;
				}
			}
			if (!$botFound)
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);
		if ($arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['ACTION'] = mb_strtolower($arParams['ACTION']);
		if (!in_array($arParams['ACTION'], Array('auto', 'plus', 'minus')))
		{
			$arParams['ACTION'] = 'auto';
		}

		$result = CIMMessenger::Like($arParams['MESSAGE_ID'], $arParams['ACTION'], $arParams['BOT_ID']);
		if ($result === false)
		{
			throw new Bitrix\Rest\RestException("Action completed without changes", "WITHOUT_CHANGES", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function botSendTyping($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (isset($arParams['BOT_ID']))
		{
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($clientId && $bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$botFound = false;
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $clientId)
				{
					$botFound = true;
					$arParams['BOT_ID'] = $bot['BOT_ID'];
					break;
				}
			}
			if (!$botFound)
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		\Bitrix\Im\Bot::startWriting(Array('BOT_ID' => $arParams['BOT_ID']), $arParams['DIALOG_ID']);

		return true;
	}

	public static function onCommandAdd($arParams, $arHandler)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!$arHandler['APP_CODE'])
		{
			$parts = parse_url($arHandler['EVENT_HANDLER']);
			parse_str($parts['query'], $query);
			$query = array_change_key_case($query, CASE_UPPER);

			if ($query['CLIENT_ID'])
			{
				$arHandler['APP_CODE'] = 'custom'.$query['CLIENT_ID'];
			}
		}

		if (!$arHandler['APP_CODE'])
		{
			throw new Exception('Event is intended for another application');
		}

		$bot = \Bitrix\Im\Bot::getListCache();

		$commandId = Array();
		foreach ($arParams[0] as $commandData)
		{
			if ($commandData['APP_ID'] == $arHandler['APP_CODE'] && $commandData['BOT_ID'] > 0)
			{
				$sendBotData = self::getAccessToken($arHandler['APP_ID'], $commandData['BOT_ID']);
				$sendBotData['AUTH'] = $sendBotData;
				$sendBotData['BOT_ID'] = $commandData['BOT_ID'];
				$sendBotData['BOT_CODE'] = $bot[$commandData['BOT_ID']]['CODE'];
				$sendBotData['COMMAND'] = $commandData['COMMAND'];
				$sendBotData['COMMAND_ID'] = $commandData['ID'];
				$sendBotData['COMMAND_PARAMS'] = $commandData['EXEC_PARAMS'];
				$sendBotData['COMMAND_CONTEXT'] = $commandData['CONTEXT'];
				$sendBotData['MESSAGE_ID'] = $arParams[1];
				$commandId[$sendBotData['COMMAND_ID']] = $sendBotData;
				if ($commandData['CONTEXT'] != 'KEYBOARD')
				{
					if (
						$arParams[2]['MESSAGE_TYPE'] != IM_MESSAGE_PRIVATE ||
						$arParams[2]['FROM_USER_ID'] == $commandData['BOT_ID'] ||
						$arParams[2]['TO_USER_ID'] == $commandData['BOT_ID']
					)
					{
						\Bitrix\Im\Bot::startWriting(Array('BOT_ID' => $commandData['BOT_ID']), $arParams[2]['DIALOG_ID']);
					}
				}
			}
		}
		if (empty($commandId))
		{
			throw new Exception('Event is intended for another application');
		}
		$arParams[2]['MESSAGE_ID'] = $arParams[1];
		$arParams[2]['CHAT_TYPE'] = $arParams[2]['MESSAGE_TYPE'];
		$arParams[2]['LANGUAGE'] = \Bitrix\Im\Bot::getDefaultLanguage();

		if ($arParams[2]['FROM_USER_ID'] > 0)
		{
			$fromUser = \Bitrix\Im\User::getInstance($arParams[2]['FROM_USER_ID'])->getFields();

			$user = Array(
				'ID' => $fromUser['id'],
				'NAME' => $fromUser['name'],
				'FIRST_NAME' => $fromUser['first_name'],
				'LAST_NAME' => $fromUser['last_name'],
				'WORK_POSITION' => $fromUser['work_position'],
				'GENDER' => $fromUser['gender'],
			);
		}
		else
		{
			$user = Array();
		}

		return Array(
			'COMMAND' => $commandId,
			'PARAMS' => $arParams[2],
			'USER' => $user
		);
	}

	public static function onBotMessageAdd($arParams, $arHandler)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!$arHandler['APP_CODE'])
		{
			$parts = parse_url($arHandler['EVENT_HANDLER']);
			parse_str($parts['query'], $query);
			$query = array_change_key_case($query, CASE_UPPER);

			if ($query['CLIENT_ID'])
			{
				$arHandler['APP_CODE'] = 'custom'.$query['CLIENT_ID'];
			}
		}

		if (!$arHandler['APP_CODE'])
		{
			throw new Exception('Event is intended for another application');
		}

		$bots = Array();
		foreach ($arParams[0] as $botData)
		{
			if ($botData['APP_ID'] == $arHandler['APP_CODE'])
			{
				$sendBotData = self::getAccessToken($arHandler['APP_ID'], $botData['BOT_ID']);
				$sendBotData['AUTH'] = $sendBotData;
				$sendBotData['BOT_ID'] = $botData['BOT_ID'];
				$sendBotData['BOT_CODE'] = $botData['CODE'];
				$bots[$botData['BOT_ID']] = $sendBotData;

				if ($arParams[2]['CHAT_ENTITY_TYPE'] != 'LINES' && $botData['TYPE'] != \Bitrix\Im\Bot::TYPE_SUPERVISOR)
				{
					\Bitrix\Im\Bot::startWriting(Array('BOT_ID' => $botData['BOT_ID']), $arParams[2]['DIALOG_ID']);
				}
			}
		}

		if (empty($bots))
		{
			throw new Exception('Event is intended for another application');
		}
		$arParams[2]['MESSAGE_ID'] = $arParams[1];
		$arParams[2]['CHAT_TYPE'] = $arParams[2]['MESSAGE_TYPE'];
		$arParams[2]['LANGUAGE'] = \Bitrix\Im\Bot::getDefaultLanguage();

		if ($arParams[2]['FROM_USER_ID'] > 0)
		{
			$fromUser = \Bitrix\Im\User::getInstance($arParams[2]['FROM_USER_ID'])->getFields();

			$user = Array(
				'ID' => $fromUser['id'],
				'NAME' => $fromUser['name'],
				'FIRST_NAME' => $fromUser['first_name'],
				'LAST_NAME' => $fromUser['last_name'],
				'WORK_POSITION' => $fromUser['work_position'],
				'GENDER' => $fromUser['gender'],
				'IS_BOT' => $fromUser['bot']? 'Y':'N',
				'IS_CONNECTOR' => $fromUser['connector']? 'Y':'N',
				'IS_NETWORK' => $fromUser['network']? 'Y':'N',
				'IS_EXTRANET' => $fromUser['extranet']? 'Y':'N',
			);
		}
		else
		{
			$user = Array();
		}

		return Array(
			'BOT' => $bots,
			'PARAMS' => $arParams[2],
			'USER' => $user
		);
	}

	public static function onBotMessageUpdate($arParams, $arHandler)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!$arHandler['APP_CODE'])
		{
			$parts = parse_url($arHandler['EVENT_HANDLER']);
			parse_str($parts['query'], $query);
			$query = array_change_key_case($query, CASE_UPPER);

			if ($query['CLIENT_ID'])
			{
				$arHandler['APP_CODE'] = 'custom'.$query['CLIENT_ID'];
			}
		}

		if (!$arHandler['APP_CODE'])
		{
			throw new Exception('Event is intended for another application');
		}

		$bots = Array();
		foreach ($arParams[0] as $botData)
		{
			if ($botData['APP_ID'] == $arHandler['APP_CODE'])
			{
				$sendBotData = self::getAccessToken($arHandler['APP_ID'], $botData['BOT_ID']);
				$sendBotData['AUTH'] = $sendBotData;
				$sendBotData['BOT_ID'] = $botData['BOT_ID'];
				$sendBotData['BOT_CODE'] = $botData['CODE'];
				$bots[$botData['BOT_ID']] = $sendBotData;
			}
		}

		if (empty($bots))
		{
			throw new Exception('Event is intended for another application');
		}

		$arParams[2]['MESSAGE_ID'] = $arParams[1];
		$arParams[2]['CHAT_TYPE'] = $arParams[2]['MESSAGE_TYPE'];
		$arParams[2]['LANGUAGE'] = \Bitrix\Im\Bot::getDefaultLanguage();

		if ($arParams[2]['FROM_USER_ID'] > 0)
		{
			$fromUser = \Bitrix\Im\User::getInstance($arParams[2]['FROM_USER_ID'])->getFields();

			$user = Array(
				'ID' => $fromUser['id'],
				'NAME' => $fromUser['name'],
				'FIRST_NAME' => $fromUser['first_name'],
				'LAST_NAME' => $fromUser['last_name'],
				'WORK_POSITION' => $fromUser['work_position'],
				'GENDER' => $fromUser['gender'],
				'IS_BOT' => $fromUser['bot']? 'Y':'N',
				'IS_CONNECTOR' => $fromUser['connector']? 'Y':'N',
				'IS_NETWORK' => $fromUser['network']? 'Y':'N',
				'IS_EXTRANET' => $fromUser['extranet']? 'Y':'N',
			);
		}
		else
		{
			$user = Array();
		}

		return Array(
			'BOT' => $bots,
			'PARAMS' => $arParams[2],
			'USER' => $user
		);
	}

	public static function onBotMessageDelete($arParams, $arHandler)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!$arHandler['APP_CODE'])
		{
			$parts = parse_url($arHandler['EVENT_HANDLER']);
			parse_str($parts['query'], $query);
			$query = array_change_key_case($query, CASE_UPPER);

			if ($query['CLIENT_ID'])
			{
				$arHandler['APP_CODE'] = 'custom'.$query['CLIENT_ID'];
			}
		}

		if (!$arHandler['APP_CODE'])
		{
			throw new Exception('Event is intended for another application');
		}

		$bots = Array();
		foreach ($arParams[0] as $botData)
		{
			if ($botData['APP_ID'] == $arHandler['APP_CODE'])
			{
				$sendBotData = self::getAccessToken($arHandler['APP_ID'], $botData['BOT_ID']);
				$sendBotData['AUTH'] = $sendBotData;
				$sendBotData['BOT_ID'] = $botData['BOT_ID'];
				$sendBotData['BOT_CODE'] = $botData['CODE'];
				$bots[$botData['BOT_ID']] = $sendBotData;
			}
		}

		if (empty($bots))
		{
			throw new Exception('Event is intended for another application');
		}

		$arParams[2]['MESSAGE_ID'] = $arParams[1];
		$arParams[2]['CHAT_TYPE'] = $arParams[2]['MESSAGE_TYPE'];
		$arParams[2]['LANGUAGE'] = \Bitrix\Im\Bot::getDefaultLanguage();

		if ($arParams[2]['FROM_USER_ID'] > 0)
		{
			$fromUser = \Bitrix\Im\User::getInstance($arParams[2]['FROM_USER_ID'])->getFields();

			$user = Array(
				'ID' => $fromUser['id'],
				'NAME' => $fromUser['name'],
				'FIRST_NAME' => $fromUser['first_name'],
				'LAST_NAME' => $fromUser['last_name'],
				'WORK_POSITION' => $fromUser['work_position'],
				'GENDER' => $fromUser['gender'],
				'IS_BOT' => $fromUser['bot']? 'Y':'N',
				'IS_CONNECTOR' => $fromUser['connector']? 'Y':'N',
				'IS_NETWORK' => $fromUser['network']? 'Y':'N',
				'IS_EXTRANET' => $fromUser['extranet']? 'Y':'N',
			);
		}
		else
		{
			$user = Array();
		}

		return Array(
			'BOT' => $bots,
			'PARAMS' => $arParams[2],
			'USER' => $user
		);
	}

	public static function onBotJoinChat($arParams, $arHandler)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!$arHandler['APP_CODE'])
		{
			$parts = parse_url($arHandler['EVENT_HANDLER']);
			parse_str($parts['query'], $query);
			$query = array_change_key_case($query, CASE_UPPER);
			if ($query['CLIENT_ID'])
			{
				$arHandler['APP_CODE'] = 'custom'.$query['CLIENT_ID'];
			}
		}

		if (!$arHandler['APP_CODE'])
		{
			throw new Exception('Event is intended for another application');
		}

		$bots = Array();
		if ($arParams[0]['APP_ID'] == $arHandler['APP_CODE'])
		{
			$sendBotData = self::getAccessToken($arHandler['APP_ID'], $arParams[0]['BOT_ID']);
			$sendBotData['AUTH'] = $sendBotData;
			$sendBotData['BOT_ID'] = $arParams[0]['BOT_ID'];
			$sendBotData['BOT_CODE'] = $arParams[0]['CODE'];
			$bots[$arParams[0]['BOT_ID']] = $sendBotData;
		}

		if (empty($bots))
		{
			throw new Exception('Event is intended for another application');
		}

		$params = $arParams[2];
		$params['DIALOG_ID'] = $arParams[1];
		$params['LANGUAGE'] = \Bitrix\Im\Bot::getDefaultLanguage();

		if ($params['USER_ID'] > 0)
		{
			$fromUser = \Bitrix\Im\User::getInstance($params['USER_ID'])->getFields();

			$user = Array(
				'ID' => $fromUser['id'],
				'NAME' => $fromUser['name'],
				'FIRST_NAME' => $fromUser['first_name'],
				'LAST_NAME' => $fromUser['last_name'],
				'WORK_POSITION' => $fromUser['work_position'],
				'GENDER' => $fromUser['gender'],
			);
		}
		else
		{
			$user = Array();
		}

		if ($arParams[2]['CHAT_TYPE'] != 'LINES' && $arParams[0]['TYPE'] != \Bitrix\Im\Bot::TYPE_SUPERVISOR)
		{
			\Bitrix\Im\Bot::startWriting(Array('BOT_ID' => $params['BOT_ID']), $params['DIALOG_ID']);
		}

		return Array(
			'BOT' => $bots,
			'PARAMS' => $params,
			'USER' => $user
		);
	}

	public static function onBotDelete($arParams, $arHandler)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!$arHandler['APP_CODE'])
		{
			$parts = parse_url($arHandler['EVENT_HANDLER']);
			parse_str($parts['query'], $query);
			$query = array_change_key_case($query, CASE_UPPER);
			if ($query['CLIENT_ID'])
			{
				$arHandler['APP_CODE'] = 'custom'.$query['CLIENT_ID'];
			}
		}

		if (!$arHandler['APP_CODE'])
		{
			throw new Exception('Event is intended for another application');
		}

		$botCode = "";
		if ($arParams[0]['APP_ID'] == $arHandler['APP_CODE'])
		{
			$botCode = $arParams[0]['CODE'];
		}

		if (!$botCode)
		{
			throw new Exception('Event is intended for another application');
		}

		$botId = $arParams[1];

		$result = self::unbindEvent($arHandler['APP_ID'], $arHandler['APP_CODE'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd');
		if ($result)
		{
			self::unbindEvent($arHandler['APP_ID'], $arHandler['APP_CODE'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat');
			self::unbindEvent($arHandler['APP_ID'], $arHandler['APP_CODE'], 'im', 'onImBotDelete', 'OnImBotDelete');
		}

		return Array(
			'BOT_ID' => $botId,
			'BOT_CODE' => $botCode
		);
	}



	public static function commandRegister($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$customClientId = false;
		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$customClientId = true;
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		if ($customClientId)
		{
			$arApp = ['ID' => '', 'CLIENT_ID' => $arParams['CLIENT_ID']];
		}
		else
		{
			$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
			$arApp = $dbRes->fetch();
		}

		if (isset($arParams['EVENT_COMMAND_ADD']) && !empty($arParams['EVENT_COMMAND_ADD']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_COMMAND_ADD'] = $arParams['EVENT_COMMAND_ADD'].(mb_strpos($arParams['EVENT_COMMAND_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['EVENT_COMMAND_ADD'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_COMMAND_ADD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Handler for \"Command add\" event isn't specified", "EVENT_COMMAND_ADD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!isset($arParams['COMMAND']) || empty($arParams['COMMAND']))
		{
			throw new Bitrix\Rest\RestException("Command isn't specified", "COMMAND_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arParams['BOT_ID'] = intval($arParams['BOT_ID']);
		if ($arParams['BOT_ID'] > 0)
		{
			$bots = \Bitrix\Im\Bot::getListCache();
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		$arParams['COMMON'] = isset($arParams['COMMON']) && $arParams['COMMON'] == 'Y'? 'Y': 'N';
		$arParams['HIDDEN'] = isset($arParams['HIDDEN']) && $arParams['HIDDEN'] == 'Y'? 'Y': 'N';
		$arParams['EXTRANET_SUPPORT'] = isset($arParams['EXTRANET_SUPPORT']) && $arParams['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';

		if (!isset($arParams['LANG']) || empty($arParams['LANG']))
		{
			throw new Bitrix\Rest\RestException("Lang set can't be empty", "LANG_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$commandId = \Bitrix\Im\Command::register(Array(
													  'APP_ID' => $clientId,
													  'BOT_ID' => $arParams['BOT_ID'],
													  'COMMAND' => $arParams['COMMAND'],
													  'COMMON' => $arParams['COMMON'],
													  'HIDDEN' => $arParams['HIDDEN'],
													  'SONET_SUPPORT' => $arParams['SONET_SUPPORT'],
													  'EXTRANET_SUPPORT' => $arParams['EXTRANET_SUPPORT'],
													  'MODULE_ID' => 'rest',
													  'LANG' => $arParams['LANG'],
												  ));
		if ($commandId)
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImCommandAdd', 'onImCommandAdd', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImCommandAdd', 'onImCommandAdd', $arParams['EVENT_COMMAND_ADD']);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Command can't be created", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $commandId;
	}

	public static function commandUnRegister($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$commands = \Bitrix\Im\Command::getListCache();
		if (!isset($commands[$arParams['COMMAND_ID']]))
		{
			throw new Bitrix\Rest\RestException("Command not found", "COMMAND_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($commands[$arParams['COMMAND_ID']]['APP_ID'] != $clientId)
		{
			throw new Bitrix\Rest\RestException("Command was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \Bitrix\Im\Command::unRegister(Array('COMMAND_ID' => $arParams['COMMAND_ID']));
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Command can't be deleted", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function commandUpdate($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		$customClientId = false;
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$customClientId = true;
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Command::getListCache();
		if (!isset($bots[$arParams['COMMAND_ID']]))
		{
			throw new Bitrix\Rest\RestException("Command not found", "COMMAND_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($bots[$arParams['COMMAND_ID']]['APP_ID'] != $clientId)
		{
			throw new Bitrix\Rest\RestException("Command was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if ($customClientId)
		{
			$arApp = ['ID' => '', 'CLIENT_ID' => $arParams['CLIENT_ID']];
		}
		else
		{
			$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
			$arApp = $dbRes->fetch();
		}

		$updateEvents = Array();
		if (isset($arParams['FIELDS']['EVENT_COMMAND_ADD']) && !empty($arParams['FIELDS']['EVENT_COMMAND_ADD']))
		{
			$updateEvents['EVENT_COMMAND_ADD'] = $arParams['FIELDS']['EVENT_COMMAND_ADD'];
			if ($customClientId)
			{
				$updateEvents['EVENT_COMMAND_ADD'] = $updateEvents['EVENT_COMMAND_ADD'].(mb_strpos($updateEvents['EVENT_COMMAND_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
			}
			try
			{
				\Bitrix\Rest\HandlerHelper::checkCallback($arParams['FIELDS']['EVENT_COMMAND_ADD'], $arApp);
			}
			catch(Exception $e)
			{
				throw new Bitrix\Rest\RestException($e->getMessage(), "EVENT_COMMAND_ADD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		$updateFields = Array();

		if (isset($arParams['FIELDS']['COMMAND']) && !empty($arParams['FIELDS']['COMMAND']))
		{
			$updateFields['COMMAND'] = $arParams['FIELDS']['COMMAND'];
		}

		if (isset($arParams['FIELDS']['HIDDEN']) && !empty($arParams['FIELDS']['HIDDEN']))
		{
			$updateFields['HIDDEN'] = $arParams['FIELDS']['HIDDEN'] == 'Y'? 'Y': 'N';
		}

		if (isset($arParams['FIELDS']['EXTRANET_SUPPORT']) && !empty($arParams['FIELDS']['EXTRANET_SUPPORT']))
		{
			$updateFields['EXTRANET_SUPPORT'] = $arParams['FIELDS']['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		}

		if (isset($arParams['FIELDS']['LANG']) && !empty($arParams['FIELDS']['LANG']))
		{
			$updateFields['LANG'] = $arParams['FIELDS']['LANG'];
		}

		if (empty($updateFields))
		{
			if (empty($updateEvents))
			{
				throw new Bitrix\Rest\RestException("Update fields can't be empty", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$result = \Bitrix\Im\Command::update(Array('COMMAND_ID' => $arParams['COMMAND_ID']), $updateFields);
			if (!$result)
			{
				throw new Bitrix\Rest\RestException("Command can't be updated", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($updateEvents['EVENT_COMMAND_ADD']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImCommandAdd', 'onImCommandAdd', true);
			self::bindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImCommandAdd', 'onImCommandAdd', $updateEvents['EVENT_COMMAND_ADD']);
		}

		return true;
	}

	public static function commandAnswer($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$commands = \Bitrix\Im\Command::getListCache();
		if (isset($arParams['COMMAND_ID']))
		{
			if (!isset($commands[$arParams['COMMAND_ID']]))
			{
				throw new Bitrix\Rest\RestException("Command not found", "COMMAND_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($commands[$arParams['COMMAND_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Command was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else if (isset($arParams['COMMAND']))
		{
			$commandFound = false;
			foreach ($commands as $command)
			{
				if ($command['APP_ID'] == $clientId && $command['COMMAND'] == $arParams['COMMAND'])
				{
					$commandFound = true;
					$arParams['COMMAND_ID'] = $command['COMMAND_ID'];
					break;
				}
			}
			if (!$commandFound)
			{
				throw new Bitrix\Rest\RestException("Command not found", "COMMAND_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		$botId = intval($commands[$arParams['COMMAND_ID']]['BOT_ID']);

		$arMessageFields = Array();

		$arParams['MESSAGE_ID'] = intval($arParams['MESSAGE_ID']);
		if ($arParams['MESSAGE_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Message ID can't be empty", "MESSAGE_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$arMessageFields['MESSAGE'] = trim($arParams['MESSAGE']);
		if ($arMessageFields['MESSAGE'] == '')
		{
			throw new Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$attach = CIMMessageParamAttach::GetAttachByJson($arParams['ATTACH']);
		if ($attach)
		{
			if ($attach->IsAllowSize())
			{
				$arMessageFields['ATTACH'] = $attach;
			}
			else
			{
				throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of attach", "ATTACH_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else if ($arParams['ATTACH'])
		{
			throw new Bitrix\Rest\RestException("Incorrect attach params", "ATTACH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['KEYBOARD']) && !empty($arParams['KEYBOARD']) && $botId > 0)
		{
			$keyboard = Array();
			if (is_string($arParams['KEYBOARD']))
			{
				$arParams['KEYBOARD'] = \CUtil::JsObjectToPhp($arParams['KEYBOARD']);
			}
			if (!isset($arParams['KEYBOARD']['BUTTONS']))
			{
				$keyboard['BUTTONS'] = $arParams['KEYBOARD'];
			}
			else
			{
				$keyboard = $arParams['KEYBOARD'];
			}
			$keyboard['BOT_ID'] = $botId;

			$keyboard = \Bitrix\Im\Bot\Keyboard::getKeyboardByJson($keyboard);
			if ($keyboard)
			{
				if ($keyboard->isAllowSize())
				{
					$arMessageFields['KEYBOARD'] = $keyboard;
				}
				else
				{
					throw new Bitrix\Rest\RestException("You have exceeded the maximum allowable size of keyboard", "KEYBOARD_OVERSIZE", CRestServer::STATUS_WRONG_REQUEST);
				}
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect keyboard params", "KEYBOARD_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['MENU']) && !empty($arParams['MENU']))
		{
			$menu = Array();
			if (is_string($arParams['MENU']))
			{
				$arParams['MENU'] = \CUtil::JsObjectToPhp($arParams['MENU']);
			}
			if (!isset($arParams['MENU']['ITEMS']))
			{
				$menu['ITEMS'] = $arParams['MENU'];
			}
			else
			{
				$menu = $arParams['MENU'];
			}
			$menu['BOT_ID'] = $arParams['BOT_ID'];

			$menu = \Bitrix\Im\Bot\ContextMenu::getByJson($menu);
			if ($menu)
			{
				$arMessageFields['MENU'] = $menu;
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect menu params", "MENU_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		if (isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y')
		{
			$arMessageFields['SYSTEM'] = 'Y';
		}

		if (isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == 'N')
		{
			$arMessageFields['URL_PREVIEW'] = 'N';
		}

		$id = \Bitrix\Im\Command::addMessage(Array(
												 'MESSAGE_ID' => $arParams['MESSAGE_ID'],
												 'COMMAND_ID' => $arParams['COMMAND_ID']
											 ), $arMessageFields);
		if (!$id)
		{
			throw new Bitrix\Rest\RestException("Message isn't added", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $id;
	}



	public static function appRegister($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		if (!isset($arParams['CODE']) || empty($arParams['CODE']))
		{
			throw new Bitrix\Rest\RestException("App code isn't specified", "CODE_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$iframe = '';
		$iframeWidth = 0;
		$iframeHeight = 0;
		$iframePopup = 'N';
		$hash = '';

		$js = '';
		if (
			isset($arParams['JS_METHOD']) && in_array($arParams['JS_METHOD'], Array('PUT', 'SEND', 'CALL', 'SUPPORT')) &&
			isset($arParams['JS_PARAM']) && !empty($arParams['JS_PARAM'])
		)
		{
			if ($arParams['JS_METHOD'] == 'SEND')
			{
				if (preg_match('/\/([a-zA-Z0-9\-\_\+]+)((\s)?([a-zA-Z0-9\-\_\+]+))+/im', $arParams['JS_PARAM'], $matches))
				{
					$js = "BXIM.sendMessage('".$matches[0]."');";
				}
			}
			else if ($arParams['JS_METHOD'] == 'PUT')
			{
				if (preg_match('/\/([a-zA-Z0-9\-\_\+]+)((\s)?([a-zA-Z0-9\-\_\+]+))+/im', $arParams['JS_PARAM'], $matches))
				{
					$js = "BXIM.putMessage('".$matches[0]."');";
				}
			}
			else if ($arParams['JS_METHOD'] == 'CALL')
			{
				if (preg_match('/\+?[ \-\d+\(\)\#]+$/im', $arParams['JS_PARAM'], $matches))
				{
					$js = "BXIM.phoneTo('".$matches[0]."');";
				}
			}
			else if ($arParams['JS_METHOD'] == 'SUPPORT')
			{
				if (preg_match('/[a-f0-9]{32}$/im', $arParams['JS_PARAM'], $matches))
				{
					$js = "BXIM.openMessenger('networkLines".$matches[0]."');";
				}
			}
		}
		else if (isset($arParams['IFRAME']) && !empty($arParams['IFRAME']))
		{
			$check = parse_url($arParams['IFRAME']);
			if (!isset($check['scheme']) && !isset($check['host']))
			{
				throw new Bitrix\Rest\RestException("Iframe params must be HTTPS site", "IFRAME_HTTPS", CRestServer::STATUS_WRONG_REQUEST);
			}
			else if ($check['scheme'] != 'https' || empty($check['host']))
			{
				throw new Bitrix\Rest\RestException("Iframe params must be HTTPS site", "IFRAME_HTTPS", CRestServer::STATUS_WRONG_REQUEST);
			}
			$iframe = $arParams['IFRAME'];
			$iframeWidth = 320;
			if (isset($arParams['IFRAME_WIDTH']))
			{
				$iframeWidth = intval($arParams['IFRAME_WIDTH']) > 250? $arParams['IFRAME_WIDTH']: 250;
			}
			$iframeHeight = 250;
			if (isset($arParams['IFRAME_HEIGHT']))
			{
				$iframeHeight = intval($arParams['IFRAME_HEIGHT']) > 50? $arParams['IFRAME_HEIGHT']: 50;
			}
			$iframePopup = isset($arParams['IFRAME_POPUP']) && $arParams['IFRAME_POPUP'] == 'Y'? 'Y': 'N';

			if (!isset($arParams['HASH']) || empty($arParams['HASH']))
			{
				throw new Bitrix\Rest\RestException("Hash can't be empty", "HASH_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			$hash = mb_substr($arParams['HASH'], 0, 32);
		}

		if (!$iframe && !$js)
		{
			throw new Bitrix\Rest\RestException("Iframe or JS method isn't specified", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$iconId = 0;
		if (isset($arParams['ICON_FILE']) && $arParams['ICON_FILE'])
		{
			$iconFile = \CRestUtil::saveFile($arParams['ICON_FILE']);
			$imageCheck = (new \Bitrix\Main\File\Image($iconFile["tmp_name"]))->getInfo();
			if(
				!$imageCheck
				|| !$imageCheck->getWidth()
				|| $imageCheck->getWidth() > 5000
				|| !$imageCheck->getHeight()
				|| $imageCheck->getHeight() > 5000
			)
			{
				$iconFile = null;
			}
			if ($iconFile && mb_strpos($iconFile['type'], "image/") === 0)
			{
				$iconFile['MODULE_ID'] = 'imbot';
				$iconId = \CFile::saveFile($iconFile, 'imbot');
			}
		}


		$context = isset($arParams['CONTEXT'])? $arParams['CONTEXT']: 'ALL';
		$hidden = isset($arParams['HIDDEN']) && $arParams['HIDDEN'] == 'Y'? 'Y': 'N';
		$extranetSupport = isset($arParams['EXTRANET_SUPPORT']) && $arParams['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		$livechatSupport = isset($arParams['LIVECHAT_SUPPORT']) && $arParams['LIVECHAT_SUPPORT'] == 'Y'? 'Y': 'N';

		$arParams['BOT_ID'] = intval($arParams['BOT_ID']);
		if ($arParams['BOT_ID'] > 0)
		{
			$bots = \Bitrix\Im\Bot::getListCache();
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!isset($arParams['LANG']) || empty($arParams['LANG']))
		{
			throw new Bitrix\Rest\RestException("Lang set can't be empty", "LANG_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$id = \Bitrix\Im\App::register(Array(
										   'APP_ID' => $clientId,
										   'BOT_ID' => $arParams['BOT_ID'],
										   'CODE' => $arParams['CODE'],
										   'ICON_ID' => $iconId,
										   'HASH' => $hash,
										   'CONTEXT' => $context,
										   'HIDDEN' => $hidden,
										   'REGISTERED' => 'Y',
										   'JS' => $js,
										   'IFRAME' => $iframe,
										   'IFRAME_HEIGHT' => $iframeHeight,
										   'IFRAME_WIDTH' => $iframeWidth,
										   'IFRAME_POPUP' => $iframePopup,
										   'EXTRANET_SUPPORT' => $extranetSupport,
										   'LIVECHAT_SUPPORT' => $livechatSupport,
										   'MODULE_ID' => 'rest',
										   'LANG' => $arParams['LANG'],
									   ));
		if (!$id)
		{
			throw new Bitrix\Rest\RestException("App can't be registered".\var_export(Array(
																						  'APP_ID' => $clientId,
																						  'BOT_ID' => $arParams['BOT_ID'],
																						  'CODE' => $arParams['CODE'],
																						  'ICON_ID' => $iconId,
																						  'HASH' => $hash,
																						  'CONTEXT' => $context,
																						  'HIDDEN' => $hidden,
																						  'JS' => $js,
																						  'IFRAME' => $iframe,
																						  'IFRAME_HEIGHT' => $iframeHeight,
																						  'IFRAME_WIDTH' => $iframeWidth,
																						  'IFRAME_POPUP' => $iframePopup,
																						  'EXTRANET_SUPPORT' => $extranetSupport,
																						  'LIVECHAT_SUPPORT' => $livechatSupport,
																						  'MODULE_ID' => 'rest',
																						  'LANG' => $arParams['LANG'],
																					  ),1), "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $id;
	}

	public static function appUnRegister($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$apps = \Bitrix\Im\App::getListCache();
		if (!isset($apps[$arParams['APP_ID']]))
		{
			throw new Bitrix\Rest\RestException("App not found", "CHAT_APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($apps[$arParams['APP_ID']]['APP_ID'] != $clientId)
		{
			throw new Bitrix\Rest\RestException("App was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = \Bitrix\Im\App::unRegister(Array('ID' => $arParams['APP_ID']));
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("App can't be deleted", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function appUpdate($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$apps = \Bitrix\Im\App::getListCache();
		if (!isset($apps[$arParams['APP_ID']]))
		{
			throw new Bitrix\Rest\RestException("App not found", "CHAT_APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($apps[$arParams['APP_ID']]['APP_ID'] != $clientId)
		{
			throw new Bitrix\Rest\RestException("App was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$updateFields = Array();

		if (isset($arParams['FIELDS']['CONTEXT']) && !empty($arParams['FIELDS']['CONTEXT']))
		{
			$updateFields['CONTEXT'] = $arParams['FIELDS']['CONTEXT'];
		}
		if (isset($arParams['FIELDS']['HASH']) && !empty($arParams['FIELDS']['HASH']))
		{
			$updateFields['HASH'] = $arParams['FIELDS']['HASH'];
		}

		if (
			isset($arParams['FIELDS']['JS_METHOD']) && in_array($arParams['FIELDS']['JS_METHOD'], Array('PUT', 'SEND', 'CALL', 'SUPPORT')) &&
			isset($arParams['FIELDS']['JS_PARAM']) && !empty($arParams['FIELDS']['JS_PARAM'])
		)
		{
			if ($arParams['FIELDS']['JS_METHOD'] == 'SEND')
			{
				if (preg_match('/\/([a-zA-Z0-9\-\_\+]+)((\s)?([a-zA-Z0-9\-\_\+]+))+/im', $arParams['FIELDS']['JS_PARAM'], $matches))
				{
					$updateFields['JS'] = "BXIM.sendMessage('".$matches[0]."');";
				}
			}
			else if ($arParams['FIELDS']['JS_METHOD'] == 'PUT')
			{
				if (preg_match('/\/([a-zA-Z0-9\-\_\+]+)((\s)?([a-zA-Z0-9\-\_\+]+))+/im', $arParams['FIELDS']['JS_PARAM'], $matches))
				{
					$updateFields['JS'] = "BXIM.putMessage('".$matches[0]."');";
				}
			}
			else if ($arParams['FIELDS']['JS_METHOD'] == 'CALL')
			{
				if (preg_match('/\+?[ \-\d+\(\)\#]+$/im', $arParams['FIELDS']['JS_PARAM'], $matches))
				{
					$updateFields['JS'] = "BXIM.phoneTo('".$matches[0]."');";
				}
			}
			else if ($arParams['FIELDS']['JS_METHOD'] == 'SUPPORT')
			{
				if (preg_match('/[a-f0-9]{32}$/im', $arParams['FIELDS']['JS_PARAM'], $matches))
				{
					$updateFields['JS'] = "BXIM.openMessenger('networkLines".$matches[0]."');";
				}
			}
			if (isset($updateFields['JS']))
			{
				$updateFields['IFRAME'] = '';
			}
		}
		else if (isset($arParams['FIELDS']['IFRAME']) && !empty($arParams['FIELDS']['IFRAME']))
		{
			$check = parse_url($arParams['FIELDS']['IFRAME']);
			if (!isset($check['scheme']) && !isset($check['host']))
			{
				throw new Bitrix\Rest\RestException("Iframe params must be HTTPS site", "IFRAME_HTTPS", CRestServer::STATUS_WRONG_REQUEST);
			}
			else if ($check['scheme'] != 'https' || empty($check['host']))
			{
				throw new Bitrix\Rest\RestException("Iframe params must be HTTPS site", "IFRAME_HTTPS", CRestServer::STATUS_WRONG_REQUEST);
			}
			$updateFields['IFRAME'] = $arParams['FIELDS']['IFRAME'];
			$updateFields['JS'] = '';
		}

		if (isset($arParams['FIELDS']['IFRAME_WIDTH']))
		{
			$updateFields['IFRAME_WIDTH'] = intval($arParams['FIELDS']['IFRAME_WIDTH']);
		}
		if (isset($arParams['FIELDS']['IFRAME_HEIGHT']))
		{
			$updateFields['IFRAME_HEIGHT'] = intval($arParams['FIELDS']['IFRAME_HEIGHT']);
		}
		if (isset($arParams['FIELDS']['IFRAME_POPUP']) && !empty($arParams['FIELDS']['IFRAME_POPUP']))
		{
			$updateFields['IFRAME_POPUP'] = $arParams['FIELDS']['IFRAME_POPUP'] == 'Y'? 'Y': 'N';
		}

		if (isset($arParams['FIELDS']['HIDDEN']) && !empty($arParams['FIELDS']['HIDDEN']))
		{
			$updateFields['HIDDEN'] = $arParams['FIELDS']['HIDDEN'] == 'Y'? 'Y': 'N';
		}

		if (isset($arParams['FIELDS']['EXTRANET_SUPPORT']) && !empty($arParams['FIELDS']['EXTRANET_SUPPORT']))
		{
			$updateFields['EXTRANET_SUPPORT'] = $arParams['FIELDS']['EXTRANET_SUPPORT'] == 'Y'? 'Y': 'N';
		}

		if (isset($arParams['FIELDS']['LIVECHAT_SUPPORT']) && !empty($arParams['FIELDS']['LIVECHAT_SUPPORT']))
		{
			$updateFields['LIVECHAT_SUPPORT'] = $arParams['FIELDS']['LIVECHAT_SUPPORT'] == 'Y'? 'Y': 'N';
		}

		if (isset($arParams['FIELDS']['LANG']) && !empty($arParams['FIELDS']['LANG']))
		{
			$updateFields['LANG'] = $arParams['FIELDS']['LANG'];
		}

		if (isset($arParams['FIELDS']['ICON_FILE']))
		{
			$iconFile = \CRestUtil::saveFile($arParams['FIELDS']['ICON_FILE']);
			$imageCheck = (new \Bitrix\Main\File\Image($iconFile["tmp_name"]))->getInfo();
			if(
				!$imageCheck
				|| !$imageCheck->getWidth()
				|| $imageCheck->getWidth() > 5000
				|| !$imageCheck->getHeight()
				|| $imageCheck->getHeight() > 5000
			)
			{
				$iconFile = null;
			}
			if ($iconFile && mb_strpos($iconFile['type'], "image/") === 0)
			{
				$iconFile['MODULE_ID'] = 'imbot';
				$updateFields['ICON_FILE_ID'] = \CFile::saveFile($iconFile, 'imbot');
			}
		}

		if (!empty($updateFields))
		{
			$result = \Bitrix\Im\App::update(Array('ID' => $arParams['APP_ID']), $updateFields);
			if (!$result)
			{
				throw new Bitrix\Rest\RestException("Command can't be updated", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return true;
	}



	private static function getAccessToken($appId, $userId)
	{
		$session = \Bitrix\Rest\Event\Session::get();
		if(!$session)
		{
			return Array();
		}
		$auth = \Bitrix\Rest\Event\Sender::getAuth(
			$appId,
			$userId,
			array('EVENT_SESSION' => $session),
			[
				'sendRefreshToken' => 1,
				'sendAuth' => 1
			]
		);
		return $auth? $auth: Array();
	}

	private static function bindEvent($appId, $appCode, $bitrixEventModule, $bitrixEventName, $restEventName, $restEventHandler)
	{
		$res = \Bitrix\Rest\EventTable::getList(array(
													'filter' => array(
														'=EVENT_NAME' => toUpper($restEventName),
														"=APPLICATION_TOKEN" => $appCode,
														'=APP_ID' => $appId,
													),
													'select' => array('ID')
												));
		if ($handler = $res->fetch())
		{
			return true;
		}

		$result = \Bitrix\Rest\EventTable::add(array(
												   "APP_ID" => $appId,
												   "EVENT_NAME" => toUpper($restEventName),
												   "EVENT_HANDLER" => $restEventHandler,
												   "APPLICATION_TOKEN" => $appCode,
												   "USER_ID" => 0,
											   ));
		if($result->isSuccess())
		{
			\Bitrix\Rest\Event\Sender::bind($bitrixEventModule, $bitrixEventName);
		}

		return true;
	}

	private static function unbindEvent($appId, $appCode, $bitrixEventModule, $bitrixEventName, $restEventName, $skipCheck = false)
	{
		if (!$skipCheck)
		{
			$res = \Bitrix\Im\Model\BotTable::getList(array(
														  'filter' => array(
															  '=APP_ID' => $appCode,
														  ),
														  'select' => array('BOT_ID')
													  ));
			if ($handler = $res->fetch())
			{
				return false;
			}
		}

		$res = \Bitrix\Rest\EventTable::getList(array(
													'filter' => array(
														'=EVENT_NAME' => toUpper($restEventName),
														'=APP_ID' => $appId,
														'=APPLICATION_TOKEN' => $appCode,
													),
													'select' => array('ID', 'EVENT_HANDLER')
												));
		while($handler = $res->fetch())
		{
			\Bitrix\Rest\EventTable::delete($handler['ID']);
		}

		return true;
	}

	public static function mobileConfigGet($params, $n, \CRestServer $server)
	{
		if (
			$server->getAuthType() != \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE
			&& !self::isDebugEnabled()
		)
		{
			throw new \Bitrix\Rest\RestException("Get access to browser const available only for session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);

		}

		$config = Array();

		$settings = CIMSettings::Get();
		$config['SETTINGS'] = $settings['settings'];

		$userId = $GLOBALS['USER']->GetID();
		if (!isset($config['CONTACT_LIST']['users'][$userId]))
		{
			$arUsers = CIMContactList::GetUserData(array(
													   'ID' => $userId,
													   'DEPARTMENT' => 'N',
													   'USE_CACHE' => 'Y',
													   'SHOW_ONLINE' => 'N'
												   ));
			$config['CONTACT_LIST']['users'][$userId] = $arUsers['users'][$userId];
		}

		$config["ACTION"] = 'DIALOG';
		$config["PATH_TO_USER_PROFILE"] = SITE_DIR.'mobile/users/?user_id='.$userId.'&FROM_DIALOG=Y';
		$config["PATH_TO_USER_PROFILE_TEMPLATE"] = SITE_DIR.'mobile/users/?user_id=#user_id#&FROM_DIALOG=Y';

		$config['WEBRTC_MOBILE_SUPPORT'] = \Bitrix\Main\Loader::includeModule('mobileapp')? \Bitrix\MobileApp\Mobile::getInstance()->isWebRtcSupported(): false;

		return \CIMMessenger::GetMobileDialogTemplateJS([], $config);
	}

	public static function callUserRegister($params, $n, \CRestServer $server)
	{
		global $APPLICATION;

		if ($server->getAuthType() !== 'call')
		{
			throw new \Bitrix\Rest\RestException("Access for this method allowed only by call authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$params = array_change_key_case($params, CASE_UPPER);

		//1. check session info $_SESSION['LIVECHAT']['REGISTER'] - already registered?
		if ($_SESSION['CALL']['REGISTER'] &&
			!(isset($params['USER_HASH']) &&
			  trim($params['USER_HASH']) &&
			  preg_match("/^[a-fA-F0-9]{32}$/i", $params['USER_HASH'])))
		{
			$params['USER_HASH'] = $_SESSION['CALL']['REGISTER']['hash'];
		}

		//2. register user
		$userData = \Bitrix\Im\Call\User::register([
			'NAME' => $params['NAME'],
			'LAST_NAME' => $params['LAST_NAME'],
			'AVATAR' => $params['AVATAR'],
			'EMAIL' => $params['EMAIL'],
			'PERSONAL_WWW' => $params['WWW'],
			'PERSONAL_GENDER' => $params['GENDER'],
			'WORK_POSITION' => $params['POSITION'],
			'USER_HASH' => $params['USER_HASH'],
		]);
		if (!$userData)
		{
			throw new \Bitrix\Rest\RestException(
				\Bitrix\Im\Call\User::getError()->msg,
				\Bitrix\Im\Call\User::getError()->code,
				\CRestServer::STATUS_WRONG_REQUEST
			);
		}

		$aliasData = \Bitrix\Im\Alias::get($params['ALIAS']);
		if (!$aliasData)
		{
			throw new \Bitrix\Rest\RestException("Wrong alias.", "WRONG_ALIAS", \CRestServer::STATUS_FORBIDDEN);
		}

		//3. authorize
		\Bitrix\Im\Call\Auth::authorizeById($userData['ID'], true, true);

		//4. add to dialog
		$chat = new CIMChat(0);
		$chat->AddUser($aliasData['ENTITY_ID'], $userData['ID']);
		if ($exception = $APPLICATION->GetException())
		{
			if ($exception->GetID() !== 'NOTHING_TO_ADD')
			{
				throw new Bitrix\Rest\RestException(
					"You don't have access",
					"WRONG_REQUEST",
					CRestServer::STATUS_WRONG_REQUEST
				);
			}
		}

		//5. return id and hash
		$result = [
			'id' => (int)$userData['ID'],
			'hash' => $userData['HASH'],
			'created' => $userData['CREATED']
		];

		$_SESSION['CALL']['REGISTER'] = $result;

		//6. send notification to chat owner
		$chatData = CIMChat::GetChatData(['ID' => $aliasData['ENTITY_ID']]);
		$chatTitle = $chatData['chat'][$aliasData['ENTITY_ID']]['name'];
		$chatOwnerId = $chatData['chat'][$aliasData['ENTITY_ID']]['owner'];
		$notificationText = GetMessage("IM_VIDEOCONF_NEW_GUEST", ['#CHAT_TITLE#' => $chatTitle]);

		$publicLink = $aliasData['LINK'];
		$conferenceLinkText = GetMessage("IM_VIDEOCONF_JOIN_LINK");
		$conferenceLink = "<a href='{$publicLink}'>{$conferenceLinkText}</a>";
		CIMNotify::Add(
			[
				'TO_USER_ID' => $chatOwnerId,
				'MESSAGE' => $notificationText . "[br]" . $conferenceLink
			]
		);

		return $result;
	}

	public static function callUserUpdate($params, $n, \CRestServer $server)
	{
		if ($server->getAuthType() !== 'call')
		{
			throw new \Bitrix\Rest\RestException(
				"Access for this method allowed only by call authorization.",
				"WRONG_AUTH_TYPE",
				\CRestServer::STATUS_FORBIDDEN
			);
		}

		$params = array_change_key_case($params, CASE_UPPER);

		global $USER;

		$userManager = new \CUser;
		$userManager->Update($USER->GetID(), [
			'NAME' => $params['NAME']
		]);

		$relations = \Bitrix\Im\Chat::getRelation($params['CHAT_ID'], ['WITHOUT_COUNTERS' => 'Y']);

		if (\CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add(array_keys($relations), [
				'module_id' => 'im',
				'command' => 'callUserNameUpdate',
				'params' => [
					'userId' => $USER->GetID(),
					'name' => $params['NAME']
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
	}

	public static function callUserForceRename($params, $n, \CRestServer $server)
	{
		global $USER;

		$params = array_change_key_case($params, CASE_UPPER);
		$params['CHAT_ID'] = (int)$params['CHAT_ID'];
		$params['USER_ID'] = (int)$params['USER_ID'];

		if ($params['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}
		if ($params['USER_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("User ID can't be empty", "USER_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		//check if current user if owner of chat
		$chat = \Bitrix\Im\Model\ChatTable::getRowById($params['CHAT_ID']);
		if (!$chat)
		{
			throw new Bitrix\Rest\RestException("Chat was not found", "CHAT_NOT_FOUND", CRestServer::STATUS_WRONG_REQUEST);
		}
		$owner = (int)$chat['AUTHOR_ID'];
		if ((int)$USER->GetID() !== $owner)
		{
			throw new Bitrix\Rest\RestException("You cannot perform this operation", "NO_ACCESS", CRestServer::STATUS_WRONG_REQUEST);
		}

		//check if renamed user is call auth
		$userToRename = \Bitrix\Im\User::getInstance($params['USER_ID']);
		if (!$userToRename)
		{
			throw new Bitrix\Rest\RestException("User was not found", "USER_NOT_FOUND", CRestServer::STATUS_WRONG_REQUEST);
		}
		$externalAuth = $userToRename->getExternalAuthId();
		if ($externalAuth !== 'call')
		{
			throw new Bitrix\Rest\RestException("You cannot rename this user", "WRONG_USER_AUTH_TYPE", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userManager = new \CUser;
		$userManager->Update($params['USER_ID'], [
			'NAME' => $params['NAME']
		]);

		$relations = \Bitrix\Im\Chat::getRelation($params['CHAT_ID'], ['WITHOUT_COUNTERS' => 'Y']);

		if (\CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add(array_keys($relations), [
				'module_id' => 'im',
				'command' => 'callUserNameUpdate',
				'params' => [
					'userId' => $params['USER_ID'],
					'name' => $params['NAME']
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
	}

	public static function callChannelPublicList($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		$type = \CPullChannel::TYPE_PRIVATE;
		if ($params['APPLICATION'] == 'Y')
		{
			$clientId = $server->getClientId();
			if (!$clientId)
			{
				throw new \Bitrix\Rest\RestException("Get application public channel available only for application authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_WRONG_REQUEST);
			}
			$type = $clientId;
		}

		$users = Array();
		if (is_string($params['USERS']))
		{
			$params['USERS'] = \CUtil::JsObjectToPhp($params['USERS']);
		}
		if (is_array($params['USERS']))
		{
			foreach ($params['USERS'] as $userId)
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$users[$userId] = $userId;
				}
			}
		}

		if (empty($users))
		{
			throw new \Bitrix\Rest\RestException("A wrong format for the USERS field is passed", "INVALID_FORMAT", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$chatId = (int)$params['CALL_CHAT_ID'];

		if (!$chatId)
		{
			throw new \Bitrix\Rest\RestException("No chat id", "INVALID_FORMAT", \CRestServer::STATUS_WRONG_REQUEST);
		}

		$configParams = Array();
		$configParams['TYPE'] = $type;
		$configParams['USERS'] = $users;
		$configParams['JSON'] = true;

		$config = \Bitrix\Pull\Channel::getPublicIds($configParams);
		if ($config === false)
		{
			throw new \Bitrix\Rest\RestException("Push & Pull server is not configured", "SERVER_ERROR", \CRestServer::STATUS_INTERNAL);
		}

		return $config;
	}

	public static function videoconfShareChange($params, $n, \CRestServer $server)
	{
		global $USER;
		$userName = $USER->GetFullName();
		$params = array_change_key_case($params, CASE_UPPER);

		if (!\Bitrix\Im\Common::isDialogId($params['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		//check owner
		$chatId = \Bitrix\Im\Dialog::getChatId($params['DIALOG_ID']);
		$chatData = \Bitrix\Im\Chat::getById($chatId);

		if ($USER->GetID() != $chatData['OWNER'])
		{
			throw new Bitrix\Rest\RestException("You don't have access to this chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		//get chat users and delete guests
		$chatUsers = \Bitrix\Im\Chat::getUsers($chatId);
		$externalTypes = \Bitrix\Main\UserTable::getExternalUserTypes();

		$chat = new CIMChat($USER->GetId());
		foreach($chatUsers as $user)
		{
			if (in_array($user['external_auth_id'], $externalTypes, true))
			{
				$chat->DeleteUser($chatId, $user['id']);
			}
		}

		//get alias
		$aliasData = \Bitrix\Im\Alias::getByEntity('VIDEOCONF', $chatId);

		//generate new alias and update
		$newCode = \Bitrix\Im\Alias::generateUnique();
		$updateResult = \Bitrix\Im\Alias::update($aliasData['ID'], [
			'ALIAS' => $newCode,
			'ENTITY_TYPE' => 'VIDEOCONF',
			'ENTITY_ID' => $chatId
		]);

		if (!$updateResult)
		{
			throw new Bitrix\Rest\RestException("Can't update alias", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$newLink = \Bitrix\Im\Alias::get($newCode)['LINK'];

		//add message to chat
		$attach = new CIMMessageParamAttach(null);
		$attach->AddLink(
			[
				"NAME" => $newLink,
				"DESC" => GetMessage("IM_VIDEOCONF_SHARE_UPDATED_LINK", ['#USER_NAME#' => htmlspecialcharsback($userName)]),
				"LINK" => $newLink
			]
		);

		CIMChat::AddMessage(
			[
				"TO_CHAT_ID" => $chatId,
				"SYSTEM" => 'Y',
				"FROM_USER_ID" => $USER->GetID(),
				"MESSAGE" => GetMessage("IM_VIDEOCONF_LINK_TITLE"),
				"ATTACH" => $attach
			]
		);

		//send pull with changed alias

		$relations = \Bitrix\Im\Chat::getRelation($chatId, ['WITHOUT_COUNTERS' => 'Y']);
		if (\CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add(array_keys($relations), [
				'module_id' => 'im',
				'command' => 'videoconfShareUpdate',
				'params' => [
					'newCode' => $newCode,
					'newLink' => $newLink,
					'dialogId' => $params['DIALOG_ID']
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	public static function videoconfPasswordCheck($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if (!$params['PASSWORD'])
		{
			throw new Bitrix\Rest\RestException("Password can't be empty", "PASSWORD_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!$params['ALIAS'])
		{
			throw new Bitrix\Rest\RestException("Alias can't be empty", "ALIAS_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$conference = \Bitrix\Im\Call\Conference::getByAlias($params['ALIAS']);
		if ($conference && $conference->getPassword() === $params['PASSWORD'])
		{
			//create cache for current confId and sessId
			$storage = \Bitrix\Main\Application::getInstance()->getLocalSession('conference_check_' . $conference->getId());
			$storage->set('checked', true);

			//add user to chat
			$currentUserId = \Bitrix\Main\Engine\CurrentUser::get()->getId();
			$isUserInChat = Chat::isUserInChat($conference->getChatId());
			if ($currentUserId && !$isUserInChat)
			{
				$chat = new \CIMChat(0);
				$addingResult = $chat->AddUser($conference->getChatId(), $currentUserId);
				if (!$addingResult)
				{
					throw new Bitrix\Rest\RestException("Error during adding user to chat", "ADDING_TO_CHAT_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}

			return true;
		}

		return false;
	}

	public static function videoconfAdd($arParams, $n, CRestServer $server)
	{
		throw new \Bitrix\Rest\RestException('This method is not available', 'METHOD_NOT_AVAILABLE', CRestServer::STATUS_WRONG_REQUEST);

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (
			\Bitrix\Im\User::getInstance()->isExtranet()
			|| \Bitrix\Im\User::getInstance()->isBot()
		)
		{
			throw new Bitrix\Rest\RestException("Only intranet users have access to this method.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams['BROADCAST_MODE'] = ($arParams['BROADCAST_MODE'] ?? 'N') === 'Y';

		$createResult = \Bitrix\Im\Call\Conference::add($arParams);

		if (!$createResult->isSuccess())
		{
			$error = $createResult->getErrors()[0];
			throw new Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), CRestServer::STATUS_WRONG_REQUEST);
		}

		return [
			'chatId' => $createResult->getData()['CHAT_ID'],
			'alias' => $createResult->getData()['ALIAS_DATA']['ALIAS'],
			'link' => $createResult->getData()['ALIAS_DATA']['LINK']
		];
	}

	public static function videoconfUpdate($arParams, $n, CRestServer $server)
	{
		throw new \Bitrix\Rest\RestException('This method is not available', 'METHOD_NOT_AVAILABLE', CRestServer::STATUS_WRONG_REQUEST);

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (
			\Bitrix\Im\User::getInstance()->isExtranet()
			|| \Bitrix\Im\User::getInstance()->isBot()
		)
		{
			throw new Bitrix\Rest\RestException("Only intranet users have access to this method.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams['BROADCAST_MODE'] = ($arParams['BROADCAST_MODE'] ?? 'N') === 'Y';

		if (!isset($arParams['ID']) || (int)$arParams['ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Conference ID can't be empty", "CONFERENCE_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$conference = \Bitrix\Im\Call\Conference::getById((int)$arParams['ID']);

		if (!$conference)
		{
			throw new Bitrix\Rest\RestException("Conference with such id not found.", "CONFERENCE_NOT_FOUND", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!$conference->canUserEdit(\Bitrix\Main\Engine\CurrentUser::get()->getId()))
		{
			throw new Bitrix\Rest\RestException("You can't edit the conference.", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$updateResult = $conference->update($arParams);

		if (!$updateResult->isSuccess())
		{
			$error = $updateResult->getErrors()[0];
			throw new Bitrix\Rest\RestException($error->getMessage(), $error->getCode(), CRestServer::STATUS_WRONG_REQUEST);
		}

		return $updateResult->isSuccess();
	}

	public static function desktopStatusGet($params, $n, \CRestServer $server)
	{
		return [
			'isOnline' => CIMMessenger::CheckDesktopStatusOnline(),
			'version' => CIMMessenger::GetDesktopVersion()
		];
	}

	public static function desktopPageOpen($params, $n, \CRestServer $server)
	{
		$params = array_change_key_case($params, CASE_UPPER);

		if (CIMMessenger::GetDesktopVersion() === 0)
		{
			throw new Bitrix\Rest\RestException("Desktop was never installed", "NO_DESKTOP", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (!CIMMessenger::CheckDesktopStatusOnline())
		{
			throw new Bitrix\Rest\RestException("Desktop is not online", "DESKTOP_CLOSED", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (\CModule::IncludeModule("pull"))
		{
			$userId = \Bitrix\Main\Engine\CurrentUser::get()->getId();
			if (!$userId)
			{
				return false;
			}

			\Bitrix\Pull\Event::add($userId, [
				'module_id' => 'im',
				'command' => 'desktopOpenPage',
				'params' => [
					'url' => $params['URL']
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	public static function enableV2Version($params, $n, \CRestServer $server)
	{
		CUserOptions::SetOption('im', 'v2_enabled', 'Y');
	}

	public static function disableV2Version($params, $n, \CRestServer $server)
	{
		CUserOptions::SetOption('im', 'v2_enabled', 'N');
	}

	private static function getBotId($arParams, \CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$clientId = $server->getClientId();
		if (!$clientId)
		{
			if (!empty($arParams['CLIENT_ID']))
			{
				$clientId = 'custom'.$arParams['CLIENT_ID'];
			}
			else
			{
				throw new \Bitrix\Rest\AccessException("Client ID not specified");
			}
		}

		$bots = \Bitrix\Im\Bot::getListCache();
		if (isset($arParams['BOT_ID']))
		{
			if (!isset($bots[$arParams['BOT_ID']]))
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
			if ($clientId && $bots[$arParams['BOT_ID']]['APP_ID'] != $clientId)
			{
				throw new Bitrix\Rest\RestException("Bot was installed by another rest application", "APP_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}
		else
		{
			$botFound = false;
			foreach ($bots as $bot)
			{
				if ($bot['APP_ID'] == $clientId)
				{
					$botFound = true;
					$arParams['BOT_ID'] = $bot['BOT_ID'];
					break;
				}
			}
			if (!$botFound)
			{
				throw new Bitrix\Rest\RestException("Bot not found", "BOT_ID_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $arParams['BOT_ID'];
	}

	private static function getLimit($options = [])
	{
		$max = 200;
		$default = 50;

		if (!isset($options['LIMIT']))
		{
			return $default;
		}

		$limit = (int)$options['LIMIT'];
		if ($limit <= 0)
		{
			return $default;
		}

		if ($limit >= $max)
		{
			return $max;
		}

		return $limit;
	}

	private static function getOffset(int $offset, $options = [])
	{
		if ($offset > 0)
		{
			return $offset;
		}

		if (!isset($options['OFFSET']) || (int)$options['OFFSET'] <= 0)
		{
			return 0;
		}

		return (int)$options['OFFSET'];
	}

	/* Utils */
	public static function objectEncode($data, $options = [])
	{
		if (!is_array($options['IMAGE_FIELD']))
		{
			$options['IMAGE_FIELD'] = ['AVATAR', 'AVATAR_HR'];
		}


		if (is_array($data))
		{
			$result = [];
			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$value = self::objectEncode($value, $options);
				}
				else if ($value instanceof \Bitrix\Main\Type\DateTime)
				{
					$value = date('c', $value->getTimestamp());
				}
				else if (is_string($key) && in_array($key, $options['IMAGE_FIELD']) && is_string($value) && $value && mb_strpos($value, 'http') !== 0)
				{
					$value = self::getServerAddress().$value;
				}

				$key = str_replace('_', '', lcfirst(ucwords(mb_strtolower($key), '_')));

				$result[$key] = $value;
			}
			$data = $result;
		}

		return $data;
	}

	public static function getServerAddress()
	{
		$publicUrl = \Bitrix\Main\Config\Option::get('main', 'last_site_url', '');

		if ($publicUrl)
		{
			return $publicUrl;
		}
		else
		{
			return (\Bitrix\Main\Context::getCurrent()->getRequest()->isHttps() ? "https" : "http")."://".$_SERVER['SERVER_NAME'].(in_array($_SERVER['SERVER_PORT'], Array(80, 443))?'':':'.$_SERVER['SERVER_PORT']);
		}
	}

	public static function isDebugEnabled()
	{
		$settings = \Bitrix\Main\Config\Configuration::getValue('im');
		return $settings['rest_debug'] === true;
	}
}
?>
