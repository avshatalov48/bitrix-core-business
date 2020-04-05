<?
if(!CModule::IncludeModule('rest'))
	return;

class CIMRestService extends IRestService
{
	public static function OnRestServiceBuildDescription()
	{
		return array(
			'im' => array(
				'im.revision.get' => array(__CLASS__, 'revisionGet'),

				'im.user.get' => array(__CLASS__, 'userGet'),
				'im.user.list' => array(__CLASS__, 'userList'),

				'im.search.user' => array(__CLASS__, 'searchUser'),
				'im.search.last.get' => array(__CLASS__, 'searchLastGet'),
				'im.search.last.add' => array(__CLASS__, 'searchLastAdd'),
				'im.search.last.delete' => array(__CLASS__, 'searchLastDelete'),

				'im.recent.get' => array(__CLASS__, 'recentGet'),
				'im.recent.pin' => array(__CLASS__, 'recentPin'),
				'im.recent.hide' => array(__CLASS__, 'recentHide'),

				'im.department.list' => array(__CLASS__, 'departmentList'),
				'im.department.get' => array(__CLASS__, 'departmentGet'),
				'im.department.colleagues.get' => array(__CLASS__, 'departmentColleaguesGet'),
				'im.department.managers.get' => array(__CLASS__, 'departmentManagersGet'),
				'im.department.employees.get' => array(__CLASS__, 'departmentEmployeesGet'),

				'im.chat.add' => array(__CLASS__, 'chatCreate'),
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
				'im.chat.sendTyping' => array(__CLASS__, 'chatSendTyping'),
				'im.chat.mute' => array(__CLASS__, 'chatMute'),

				'im.dialog.messages.get' => array(__CLASS__, 'dialogMessagesGet'),

				'im.message.add' => array(__CLASS__, 'messageAdd'),
				'im.message.delete' => array(__CLASS__, 'messageDelete'),
				'im.message.update' => array(__CLASS__, 'messageUpdate'),
				'im.message.like' => array(__CLASS__, 'messageLike'),

				'im.notify' => array(__CLASS__, 'notifyAdd'),
				'im.notify.personal.add' => array(__CLASS__, 'notifyAdd'),
				'im.notify.system.add' => array(__CLASS__, 'notifyAdd'),
				'im.notify.delete' => array(__CLASS__, 'notifyDelete'),

				'im.notify.read' => array(__CLASS__, 'notifyRead'),

				'im.counters.get' =>  array(__CLASS__, 'counterGet'),

				'im.mobile.config.get' =>  array('callback' => array(__CLASS__, 'mobileConfigGet'), 'options' => array('private' => true)),
			),
			'imbot' => Array(
				'imbot.register' => array(__CLASS__, 'botRegister'),
				'imbot.unregister' => array(__CLASS__, 'botUnRegister'),
				'imbot.update' => array(__CLASS__, 'botUpdate'),

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

				'imbot.sendTyping' => array(__CLASS__, 'botSendTyping'),

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

	public static function revisionGet($arParams, $n, CRestServer $server)
	{
		return Array(
			'im_revision' => IM_REVISION,
			'im_revision_mobile' => IM_REVISION_MOBILE,
			'im_call_revision' => IM_CALL_REVISION,
			'im_call_revision_mobile' => IM_CALL_REVISION_MOBILE,
		);
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
		if (!$user->isExists())
		{
			throw new Bitrix\Rest\RestException("User is not exists", "USER_NOT_EXISTS", CRestServer::STATUS_WRONG_REQUEST);
		}

		$currentUserId = \Bitrix\Im\User::getInstance()->getId();
		$isExtranet = \Bitrix\Im\User::getInstance()->isExtranet();

		$extranetUsers = Array($currentUserId => $currentUserId);
		if ($isExtranet && !\Bitrix\Im\Integration\Socialnetwork\Extranet::isUserInGroup($userId, $currentUserId))
		{
			throw new Bitrix\Rest\RestException("You can request only users who consist of your extranet group", "ACCESS_DENIED", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = $user->getArray(Array('JSON' => 'Y'));

		$result['desktop_last_date'] = \CIMMessenger::GetDesktopStatusOnline($userId);
		$result['desktop_last_date'] = $result['desktop_last_date']? date('c', $result['desktop_last_date']): false;

		return $result;
	}

	public static function userList($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$users = Array();
		if (is_string($arParams['USERS']))
		{
			$arParams['USERS'] = \CUtil::JsObjectToPhp($arParams['USERS']);
		}
		if (is_array($arParams['USERS']))
		{
			foreach ($arParams['USERS'] as $userId)
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
			throw new Bitrix\Rest\RestException("A wrong format for the USERS field is passed", "INVALID_FORMAT", CRestServer::STATUS_WRONG_REQUEST);
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
				$result[$userId] = null;
			}
			else
			{
				$result[$userId] = \Bitrix\Im\User::getInstance($userId)->getArray(Array('JSON' => 'Y'));
			}
		}

		return $result;
	}

	/* Dialog api */

	public static function dialogMessagesGet($arParams, $offset, CRestServer $server)
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

		if (isset($arParams['FIRST_ID']))
		{
			$options['FIRST_ID'] = intval($arParams['FIRST_ID']);
		}
		else
		{
			$options['LAST_ID'] = isset($arParams['LAST_ID']) && intval($arParams['LAST_ID']) > 0? intval($arParams['LAST_ID']): 0;
		}
		$options['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 20;
		$options['JSON'] = 'Y';

		return \Bitrix\Im\Chat::getMessages($chatId, null, $options);
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

		$filter = \Bitrix\Im\User::getListFilter($params);
		if (is_null($filter))
		{
			throw new Bitrix\Rest\RestException("Too short a search phrase.", "FIND_SHORT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$counter = \Bitrix\Main\UserTable::getList(array(
			'filter' => $filter,
			'select' => array("CNT" => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')),
		))->fetch();

		$result = Array();
		if ($counter && $counter["CNT"] > 0)
		{
			$params['OFFSET'] = isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): intval($offset);
			$params['LIMIT'] = isset($arParams['LIMIT'])? (intval($arParams['LIMIT']) > 50? 50: intval($arParams['LIMIT'])): 10;

			$result = \Bitrix\Im\User::getList($params);

			return self::setNavData(
				$result,
				array(
					"count" => $counter['CNT'],
					"offset" => $params['OFFSET']
				)
			);
		}

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

		return \Bitrix\Im\Recent::get(null, $config);
	}

	public static function recentPin($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\Recent::pin($arParams['DIALOG_ID'], $arParams['ACTION'] != 'N');
	}

	public static function recentHide($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (!\Bitrix\Im\Common::isDialogId($arParams['DIALOG_ID']))
		{
			throw new Bitrix\Rest\RestException("Dialog ID can't be empty", "DIALOG_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\Recent::hide($arParams['DIALOG_ID']);
	}

	/* Department api */

	public static function departmentList($arParams, $n, CRestServer $server)
	{
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

		if (isset($arParams['ID']) && empty($ids))
		{
			throw new Bitrix\Rest\RestException("A wrong format for the ID field is passed", "INVALID_FORMAT", CRestServer::STATUS_WRONG_REQUEST);
		}

		$options = Array();
		if ($arParams['USER_DATA'] == 'Y')
		{
			$options['USER_DATA'] = 'Y';
		}

		if (isset($arParams['ID']))
		{
			$list = \Bitrix\Im\Department::getStructure($options);

			$result = Array();
			foreach ($ids as $id)
			{
				if (!isset($list[$id]))
				{
					$result[$id] = null;
					continue;
				}

				if ($list[$id]['MANAGER_USER_DATA'] instanceof \Bitrix\Im\User)
				{
					$list[$id]['MANAGER_USER_DATA'] = $list[$id]['MANAGER_USER_DATA']->getArray(Array('JSON' => 'Y'));
				}

				$result[$id] = array_change_key_case($list[$id], CASE_LOWER);
			}

			$result = array_values($result);
		}
		else
		{
			$result = \Bitrix\Im\Department::getStructure($options);

			foreach ($result as $id => $department)
			{
				if ($department['MANAGER_USER_DATA'] instanceof \Bitrix\Im\User)
				{
					$department['MANAGER_USER_DATA'] = $department['MANAGER_USER_DATA']->getArray(Array('JSON' => 'Y'));
				}

				$result[$id] = array_change_key_case($department, CASE_LOWER);
			}

			$result = array_values($result);
		}

		return $result;
	}

	public static function departmentGet($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$id = intval($arParams['ID']);
		if ($id <= 0)
		{
			throw new Bitrix\Rest\RestException("Department ID can't be empty", "ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$list = \Bitrix\Im\Department::getStructure();
		if (!isset($list[$id]))
		{
			throw new Bitrix\Rest\RestException("Department is not exists", "DEPARTMENT_NOT_EXISTS", CRestServer::STATUS_WRONG_REQUEST);
		}

		return array_change_key_case($list[$id], CASE_LOWER);
	}

	public static function departmentManagersGet($arParams, $n, CRestServer $server)
	{
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

		return \Bitrix\Im\Department::getManagers(empty($ids)? null: $ids, array('JSON' => 'Y', 'USER_DATA' => $withUserData? 'Y': 'N'));
	}

	public static function departmentEmployeesGet($arParams, $n, CRestServer $server)
	{
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

	public static function departmentColleaguesGet($arParams, $offset, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$withUserData = $arParams['USER_DATA'] == 'Y';

		$params['OFFSET'] = isset($arParams['OFFSET']) && intval($arParams['OFFSET']) > 0? intval($arParams['OFFSET']): intval($offset);
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

	public static function chatCreate($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['USERS']) && !is_array($arParams['USERS']))
		{
			throw new Bitrix\Rest\RestException("Please select users before creating a new chat", "USERS_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		if (isset($arParams['AVATAR']) && $arParams['AVATAR'])
		{
			$arParams['AVATAR'] = CRestUtil::saveFile($arParams['AVATAR']);
			if (!$arParams['AVATAR'] || strpos($arParams['AVATAR']['type'], "image/") !== 0)
			{
				$arParams['AVATAR'] = 0;
			}
			else
			{
				$arParams['AVATAR'] = CFile::saveFile($arParams['AVATAR'], 'im');
			}
		}
		else
		{
			$arParams['AVATAR'] = 0;
		}

		$add = Array(
			'TYPE' => $arParams['TYPE'] == 'OPEN'? IM_MESSAGE_OPEN: IM_MESSAGE_CHAT,
			'USERS' => $arParams['USERS'],
		);
		if ($arParams['AVATAR'] > 0)
		{
			$add['AVATAR_ID'] = $arParams['AVATAR'];
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
		if (isset($arParams['ENTITY_TYPE']))
		{
			$add['ENTITY_TYPE'] = $arParams['ENTITY_TYPE'];
		}
		if (isset($arParams['ENTITY_ID']))
		{
			$add['ENTITY_ID'] = $arParams['ENTITY_ID'];
		}
		if (isset($arParams['OWNER_ID']))
		{
			$add['OWNER_ID'] = $arParams['OWNER_ID'];
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
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (
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
		if ($server->getMethod() == strtolower("imbot.chat.setOwner"))
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
		if ($server->getMethod() == strtolower("imbot.chat.setManager"))
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
		if ($server->getMethod() == strtolower("imbot.chat.updateColor"))
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
		if ($server->getMethod() == strtolower("imbot.chat.updateTitle"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$chat = new CIMChat($userId);
		$result = $chat->Rename($arParams['CHAT_ID'], $arParams['TITLE']);

		if (!$result)
		{
			throw new Bitrix\Rest\RestException("This title currently set or chat isn't exists", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUpdateAvatar($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$userId = $USER->GetId();
		if ($server->getMethod() == strtolower("imbot.chat.updateAvatar"))
		{
			$userId = self::getBotId($arParams, $server);
		}

		if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($userId))
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		$arParams['AVATAR'] = CRestUtil::saveFile($arParams['AVATAR']);
		if (!$arParams['AVATAR'] || strpos($arParams['AVATAR']['type'], "image/") !== 0)
		{
			throw new Bitrix\Rest\RestException("Avatar incorrect", "AVATAR_ERROR", CRestServer::STATUS_WRONG_REQUEST);
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
		if ($server->getMethod() == "imbot.chat.user.add")
		{
			$userId = self::getBotId($arParams, $server);
		}

		$CIMChat = new CIMChat($userId);
		$result = $CIMChat->AddUser($arParams['CHAT_ID'], $arParams['USERS'], $arParams['HIDE_HISTORY'] != "N");
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
			throw new Bitrix\Rest\RestException("You don't have access or user isn't member in chat", "WRONG_REQUEST", CRestServer::STATUS_WRONG_REQUEST);
		}

		return true;
	}

	public static function chatUserList($arParams, $n, CRestServer $server)
	{
		global $USER;

		$arParams = array_change_key_case($arParams, CASE_UPPER);

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

	public static function chatSendTyping($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);

		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		$result = CIMMessenger::StartWriting('chat'.$arParams['CHAT_ID']);
		if (!$result)
		{
			throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
		}

		return true;
	}

	public static function chatMute($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
		if ($arParams['CHAT_ID'] <= 0)
		{
			throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
		}

		return \Bitrix\Im\Chat::mute($arParams['CHAT_ID'], $arParams['ACTION'] != 'N');
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

		$arParams['MESSAGE'] = trim($arParams['MESSAGE']);
		if (strlen($arParams['MESSAGE']) <= 0)
		{
			throw new Bitrix\Rest\RestException("Message can't be empty", "MESSAGE_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
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
				"TO_USER_ID" => $arParams['USER_ID'],
				"MESSAGE" 	 => $arParams['MESSAGE'],
			);
		}
		else if (isset($arParams['CHAT_ID']))
		{
			$arParams['CHAT_ID'] = intval($arParams['CHAT_ID']);
			if ($arParams['CHAT_ID'] <= 0)
			{
				throw new Bitrix\Rest\RestException("Chat ID can't be empty", "CHAT_ID_EMPTY", CRestServer::STATUS_WRONG_REQUEST);
			}
			if (CIMChat::GetGeneralChatId() == $arParams['CHAT_ID'] && !CIMChat::CanSendMessageToGeneralChat($arParams['FROM_USER_ID']))
			{
				throw new Bitrix\Rest\RestException("Action unavailable", "ACCESS_ERROR", CRestServer::STATUS_FORBIDDEN);
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
						);

					$arParams['MESSAGE'] = "[b]".$moduleName."[/b]\n".$arParams['MESSAGE'];
				}
			}
			else
			{
				$arRelation = CIMChat::GetRelationById($arParams['CHAT_ID']);
				if (!isset($arRelation[$arParams['FROM_USER_ID']]))
				{
					throw new Bitrix\Rest\RestException("You don't have access or user isn't member in chat", "ACCESS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
				}
			}

			$arMessageFields = Array(
				"MESSAGE_TYPE" => IM_MESSAGE_CHAT,
				"FROM_USER_ID" => $arParams['FROM_USER_ID'],
				"TO_CHAT_ID" => $arParams['CHAT_ID'],
				"MESSAGE" 	 => $arParams['MESSAGE'],
			);
		}
		else
		{
			throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
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

		if (isset($arParams['SYSTEM']) && $arParams['SYSTEM'] == 'Y')
		{
			$arMessageFields['SYSTEM'] = 'Y';
		}
		if (isset($arParams['URL_PREVIEW']) && $arParams['URL_PREVIEW'] == 'N')
		{
			$arMessageFields['URL_PREVIEW'] = 'N';
		}

		$id = CIMMessenger::Add($arMessageFields);
		if (!$id)
		{
			throw new Bitrix\Rest\RestException("Message isn't added", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		return $id;

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

		$arParams['ACTION'] = strtolower($arParams['ACTION']);
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
		if (strlen($arParams['MESSAGE']) <= 0)
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
				$moduleName = isset($result['APP_NAME'])? $result['APP_NAME']: $result['CODE'];
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
				$appKey = substr(md5($server->getClientId()), 0, 5);
				$arMessageFields['NOTIFY_TAG'] = 'MP|'.$appKey.'|'.$arParams['TAG'];
			}
			if (!empty($arParams['SUB_TAG']))
			{
				$appKey = substr(md5($server->getClientId()), 0, 5);
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

	public static function notifyDelete($arParams, $n, CRestServer $server)
	{
		if ($server->getAuthType() == \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
		{
			throw new \Bitrix\Rest\RestException("Access for this method not allowed by session authorization.", "WRONG_AUTH_TYPE", \CRestServer::STATUS_FORBIDDEN);
		}

		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
		{
			$CIMNotify = new CIMNotify();
			$result = $CIMNotify->DeleteWithCheck($arParams['ID']);
		}
		else
		{
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
				$appKey = substr(md5($clientId), 0, 5);
				$result = CIMNotify::DeleteByTag('MP|'.$appKey.'|'.$arParams['TAG']);
			}
			else if (!empty($arParams['SUB_TAG']))
			{
				$appKey = substr(md5($clientId), 0, 5);
				$result = CIMNotify::DeleteBySubTag('MP|'.$appKey.'|'.$arParams['SUB_TAG']);
			}
			else
			{
				throw new Bitrix\Rest\RestException("Incorrect params", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
			}
		}

		return $result;
	}

	public static function notifyRead($arParams, $n, CRestServer $server)
	{
		$arParams = array_change_key_case($arParams, CASE_UPPER);

		if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
		{
			$CIMNotify = new CIMNotify();
			$CIMNotify->MarkNotifyRead($arParams['ID'], $arParams['ONLY_CURRENT'] != 'Y');
		}

		return true;
	}

	public static function counterGet($arParams, $n, CRestServer $server)
	{
		global $USER;

		return \Bitrix\Im\Counter::get($USER->GetID());
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

		$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
		$arApp = $dbRes->fetch();

		if (isset($arParams['EVENT_HANDLER']) && !empty($arParams['EVENT_HANDLER']))
		{
			$arParams['EVENT_MESSAGE_ADD'] = $arParams['EVENT_MESSAGE_UPDATE'] = $arParams['EVENT_MESSAGE_DELETE'] = $arParams['EVENT_WELCOME_MESSAGE'] = $arParams['EVENT_BOT_DELETE']	= $arParams['EVENT_HANDLER'];
		}

		if (isset($arParams['EVENT_MESSAGE_ADD']) && !empty($arParams['EVENT_MESSAGE_ADD']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_MESSAGE_ADD'] = $arParams['EVENT_MESSAGE_ADD'].(strpos($arParams['EVENT_MESSAGE_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$arParams['EVENT_MESSAGE_UPDATE'] = $arParams['EVENT_MESSAGE_UPDATE'].(strpos($arParams['EVENT_MESSAGE_UPDATE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$arParams['EVENT_MESSAGE_DELETE'] = $arParams['EVENT_MESSAGE_DELETE'].(strpos($arParams['EVENT_MESSAGE_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$arParams['EVENT_WELCOME_MESSAGE'] = $arParams['EVENT_WELCOME_MESSAGE'].(strpos($arParams['EVENT_WELCOME_MESSAGE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$arParams['EVENT_BOT_DELETE'] = $arParams['EVENT_BOT_DELETE'].(strpos($arParams['EVENT_BOT_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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

		// TODO: uncomment this after start of 05.2018
		//$counter = \Bitrix\Im\Model\BotTable::getCount(array('=APP_ID' => $clientId));
		//if ($counter >= 5)
		//{
			//throw new Bitrix\Rest\RestException("Has reached the maximum number of bots for application (max: 5)", "MAX_COUNT_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		//}

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
			$avatar = CRestUtil::saveFile($arParams['PROPERTIES']['PERSONAL_PHOTO'], $arParams['CODE'].'.png');
			if (isset($avatar) && strpos($avatar['type'], "image/") === 0)
			{
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
			self::bindEvent($arApp['ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', $arParams['EVENT_MESSAGE_ADD']);

			if ($arParams['EVENT_MESSAGE_UPDATE'])
			{
				self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', true);
				self::bindEvent($arApp['ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', $arParams['EVENT_MESSAGE_UPDATE']);
			}

			if ($arParams['EVENT_MESSAGE_DELETE'])
			{
				self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', true);
				self::bindEvent($arApp['ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', $arParams['EVENT_MESSAGE_UPDATE']);
			}

			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', true);
			self::bindEvent($arApp['ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', $arParams['EVENT_WELCOME_MESSAGE']);

			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', true);
			self::bindEvent($arApp['ID'], 'im', 'onImBotDelete', 'OnImBotDelete', $arParams['EVENT_BOT_DELETE']);
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

		$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
		$arApp = $dbRes->fetch();

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
				$updateEvents['EVENT_MESSAGE_ADD'] = $updateEvents['EVENT_MESSAGE_ADD'].(strpos($updateEvents['EVENT_MESSAGE_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$updateEvents['EVENT_MESSAGE_UPDATE'] = $updateEvents['EVENT_MESSAGE_UPDATE'].(strpos($updateEvents['EVENT_MESSAGE_UPDATE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$updateEvents['EVENT_MESSAGE_DELETE'] = $updateEvents['EVENT_MESSAGE_DELETE'].(strpos($updateEvents['EVENT_MESSAGE_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$updateEvents['EVENT_WELCOME_MESSAGE'] = $updateEvents['EVENT_WELCOME_MESSAGE'].(strpos($updateEvents['EVENT_WELCOME_MESSAGE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
				$updateEvents['EVENT_BOT_DELETE'] = $updateEvents['EVENT_BOT_DELETE'].(strpos($updateEvents['EVENT_BOT_DELETE'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
			$avatar = CRestUtil::saveFile($arParams['FIELDS']['PROPERTIES']['PERSONAL_PHOTO'], $bots[$arParams['BOT_ID']]['CODE'].'.png');
			if ($avatar && strpos($avatar['type'], "image/") === 0)
			{
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
			self::bindEvent($arApp['ID'], 'im', 'onImBotMessageAdd', 'OnImBotMessageAdd', $updateEvents['EVENT_MESSAGE_ADD']);
		}
		if (isset($updateEvents['EVENT_MESSAGE_UPDATE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', true);
			self::bindEvent($arApp['ID'], 'im', 'onImBotMessageUpdate', 'OnImBotMessageUpdate', $updateEvents['EVENT_MESSAGE_UPDATE']);
		}
		if (isset($updateEvents['EVENT_MESSAGE_DELETE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', true);
			self::bindEvent($arApp['ID'], 'im', 'onImBotMessageDelete', 'OnImBotMessageDelete', $updateEvents['EVENT_MESSAGE_DELETE']);
		}
		if (isset($updateEvents['EVENT_WELCOME_MESSAGE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', true);
			self::bindEvent($arApp['ID'], 'im', 'onImBotJoinChat', 'OnImBotJoinChat', $updateEvents['EVENT_WELCOME_MESSAGE']);
		}
		if (isset($updateEvents['EVENT_BOT_DELETE']))
		{
			self::unbindEvent($arApp['ID'], $arApp['CLIENT_ID'], 'im', 'onImBotDelete', 'OnImBotDelete', true);
			self::bindEvent($arApp['ID'], 'im', 'onImBotDelete', 'OnImBotDelete', $updateEvents['EVENT_BOT_DELETE']);
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
		if (strlen($arMessageFields['MESSAGE']) <= 0)
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
			$skipConnector = isset($arParams['SKIP_CONNECTOR']) && $arParams['SKIP_CONNECTOR'] == "Y"? true: false;

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

		$arParams['ACTION'] = strtolower($arParams['ACTION']);
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

		$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
		$arApp = $dbRes->fetch();

		if (isset($arParams['EVENT_COMMAND_ADD']) && !empty($arParams['EVENT_COMMAND_ADD']))
		{
			if ($customClientId)
			{
				$arParams['EVENT_COMMAND_ADD'] = $arParams['EVENT_COMMAND_ADD'].(strpos($arParams['EVENT_COMMAND_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
			self::bindEvent($arApp['ID'], 'im', 'onImCommandAdd', 'onImCommandAdd', $arParams['EVENT_COMMAND_ADD']);
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

		$dbRes = \Bitrix\Rest\AppTable::getList(array('filter' => array('=CLIENT_ID' => $clientId)));
		$arApp = $dbRes->fetch();

		$updateEvents = Array();
		if (isset($arParams['FIELDS']['EVENT_COMMAND_ADD']) && !empty($arParams['FIELDS']['EVENT_COMMAND_ADD']))
		{
			$updateEvents['EVENT_COMMAND_ADD'] = $arParams['FIELDS']['EVENT_COMMAND_ADD'];
			if ($customClientId)
			{
				$updateEvents['EVENT_COMMAND_ADD'] = $updateEvents['EVENT_COMMAND_ADD'].(strpos($updateEvents['EVENT_COMMAND_ADD'], '?') === false? '?': '&').'CLIENT_ID='.$arParams['CLIENT_ID'];
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
			self::bindEvent($arApp['ID'], 'im', 'onImCommandAdd', 'onImCommandAdd', $updateEvents['EVENT_COMMAND_ADD']);
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
		if (strlen($arMessageFields['MESSAGE']) <= 0)
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
			$hash = substr($arParams['HASH'], 0, 32);
		}

		if (!$iframe && !$js)
		{
			throw new Bitrix\Rest\RestException("Iframe or JS method isn't specified", "PARAMS_ERROR", CRestServer::STATUS_WRONG_REQUEST);
		}

		$iconId = 0;
		if (isset($arParams['ICON_FILE']) && $arParams['ICON_FILE'])
		{
			$iconFile = CRestUtil::saveFile($arParams['ICON_FILE']);
			if ($iconFile && strpos($iconFile['type'], "image/") === 0)
			{
				$iconId = \CFile::SaveFile($iconFile, 'imbot');
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
			$iconFile = CRestUtil::saveFile($arParams['FIELDS']['ICON_FILE']);
			if ($iconFile && strpos($iconFile['type'], "image/") === 0)
			{
				$updateFields['ICON_FILE_ID'] = \CFile::SaveFile($iconFile, 'imbot');
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
			array('EVENT_SESSION' => $session)
		);
		return $auth? $auth: Array();
	}

	private static function bindEvent($appId, $bitrixEventModule, $bitrixEventName, $restEventName, $restEventHandler)
	{
		$res = \Bitrix\Rest\EventTable::getList(array(
			'filter' => array(
				'=EVENT_NAME' => toUpper($restEventName),
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
			),
			'select' => array('ID')
		));
		$eventFound = false;
		while($handler = $res->fetch())
		{
			$eventFound = true;
			\Bitrix\Rest\EventTable::delete($handler['ID']);
		}
		if ($eventFound)
		{
			\Bitrix\Rest\Event\Sender::unbind($bitrixEventModule, $bitrixEventName);
		}

		return true;
	}

	public static function mobileConfigGet($params, $n, \CRestServer $server)
	{
		if ($server->getAuthType() != \Bitrix\Rest\SessionAuth\Auth::AUTH_TYPE)
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
}
?>