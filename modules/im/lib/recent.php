<?php

namespace Bitrix\Im;

use Bitrix\Main\Application, Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Recent
{
	private const PINNED_CHATS_LIMIT = 25;

	public static function get($userId = null, $options = [])
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$result = [];
		$isOperator = \Bitrix\Im\Integration\Imopenlines\User::isOperator();

		$generalChatId = \CIMChat::GetGeneralChatId();

		$select = [
			'*',
			'COUNTER' => 'RELATION.COUNTER',
			'MESSAGE_ID' => 'MESSAGE.ID',
			'MESSAGE_AUTHOR_ID' => 'MESSAGE.AUTHOR_ID',
			'MESSAGE_TEXT' => 'MESSAGE.MESSAGE',
			'MESSAGE_FILE' => 'FILE.ID',
			'MESSAGE_DATE' => 'MESSAGE.DATE_CREATE',
			'MESSAGE_ATTACH' => 'ATTACH.ID',
			'RELATION_USER_ID' => 'RELATION.USER_ID',
			'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
			'INVITATION_ORIGINATOR_ID' => 'INVITATION.ORIGINATOR_ID',
			'USER_EMAIL' => 'USER.EMAIL',
			'CHAT_ID' => 'CHAT.ID',
			'CHAT_TITLE' => 'CHAT.TITLE',
			'CHAT_TYPE' => 'CHAT.TYPE',
			'CHAT_AVATAR' => 'CHAT.AVATAR',
			'CHAT_LAST_MESSAGE_STATUS' => 'CHAT.LAST_MESSAGE_STATUS',
			'CHAT_AUTHOR_ID' => 'CHAT.AUTHOR_ID',
			'CHAT_EXTRANET' => 'CHAT.EXTRANET',
			'CHAT_COLOR' => 'CHAT.COLOR',
			'CHAT_ENTITY_TYPE' => 'CHAT.ENTITY_TYPE',
			'CHAT_ENTITY_ID' => 'CHAT.ENTITY_ID',
			'CHAT_ENTITY_DATA_1' => 'CHAT.ENTITY_DATA_1',
			'CHAT_ENTITY_DATA_2' => 'CHAT.ENTITY_DATA_2',
			'CHAT_ENTITY_DATA_3' => 'CHAT.ENTITY_DATA_3',
			'CHAT_DATE_CREATE' => 'CHAT.DATE_CREATE',
		];
		if ($isOperator)
		{
			$select['LINES_ID'] = 'LINES.ID';
			$select['LINES_STATUS'] = 'LINES.STATUS';
		}

		$runtime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'ATTACH',
				'\Bitrix\Im\Model\MessageParamTable',
				[
					"=ref.MESSAGE_ID" => "this.ITEM_MID",
					"ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "ATTACH")
				],
				["join_type" => "LEFT"]
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'FILE',
				'\Bitrix\Im\Model\MessageParamTable',
				[
					"=ref.MESSAGE_ID" => "this.ITEM_MID",
					"ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "FILE_ID")
				],
				["join_type" => "LEFT"]
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'INVITATION',
				'\Bitrix\Intranet\Internals\InvitationTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.USER_ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'USER',
				'\Bitrix\Main\UserTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			)
		];
		if ($isOperator)
		{
			$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
				'LINES',
				'\Bitrix\ImOpenlines\Model\SessionTable',
				[">this.ITEM_OLID" => new \Bitrix\Main\DB\SqlExpression("0"), "=ref.ID" => "this.ITEM_OLID"],
				["join_type" => "LEFT"]
			);
		}

		$isIntranet = false;
		if (\Bitrix\Main\Loader::includeModule('intranet'))
		{
			$isIntranet = \Bitrix\Intranet\Util::isIntranetUser($userId);
		}

		if ($isIntranet)
		{
			$filter = ['=USER_ID' => [$userId, 0]];
		}
		else
		{
			$filter = ['=USER_ID' => $userId];
		}

		if (!$options['IS_RECENT_GET'])
		{
			$filter['=PINNED'] = $options['GET_PINNED'];
		}

		if ($options['LAST_UPDATE'])
		{
			$filter['>=DATE_UPDATE'] = $options['LAST_UPDATE'];
		}
		if ($options['LAST_MESSAGE_UPDATE'])
		{
			$filter['<DATE_UPDATE'] = $options['LAST_MESSAGE_UPDATE'];
		}
		if ($options['IS_RECENT_GET'] && !$options['LAST_UPDATE'])
		{
			$filter['>=DATE_UPDATE'] = (new \Bitrix\Main\Type\DateTime())->add('-30 days');
		}

		if ($options['SKIP_OPENLINES'] === 'Y')
		{
			$filter[] = [
				'!=ITEM_TYPE' => IM_MESSAGE_OPEN_LINE
			];
		}
		if ($options['SKIP_CHAT'] === 'Y')
		{
			$filter[] = [
				'!=ITEM_TYPE' => IM_MESSAGE_OPEN
			];
			$filter[] = [
				'!=ITEM_TYPE' => IM_MESSAGE_CHAT
			];
		}
		if ($options['SKIP_DIALOG'] === 'Y')
		{
			$filter[] = [
				'!=ITEM_TYPE' => IM_MESSAGE_PRIVATE
			];
		}
		if ($options['SKIP_NOTIFICATION'] === 'Y')
		{
			$filter[] = [
				'!=ITEM_TYPE' => IM_MESSAGE_SYSTEM
			];
		}

		$ormParams = [
			'select' => $select,
			'filter' => $filter,
			'runtime' => $runtime,
		];

		if (isset($options['OFFSET']))
		{
			$ormParams['offset'] = $options['OFFSET'];
		}
		if (isset($options['LIMIT']))
		{
			$ormParams['limit'] = $options['LIMIT'];
		}
		if (isset($options['ORDER']))
		{
			$ormParams['order'] = $options['ORDER'];
		}

		$orm = \Bitrix\Im\Model\RecentTable::getList($ormParams);
		while ($row = $orm->fetch())
		{
			$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
			$isNotification = $row['ITEM_TYPE'] == IM_MESSAGE_SYSTEM;
			$id = $isUser? (int)$row['ITEM_ID']: ($isNotification? 'notify' : 'chat'.$row['ITEM_ID']);

			if ($isUser)
			{
				if (isset($result[$id]) && !$row['ITEM_MID'])
				{
					continue;
				}
			}
			else if (isset($result[$id]))
			{
				continue;
			}

			if (!$isUser && (!$row['MESSAGE_ID'] || !$row['RELATION_USER_ID'] || !$row['CHAT_ID']))
			{
				continue;
			}

			$item = [
				'ID' => $id,
				'TYPE' => $isUser ? 'user' : ($isNotification ? 'notification' : 'chat'),
				'AVATAR' => [],
				'TITLE' => [],
				'MESSAGE' => [
					'ID' => (int)$row['ITEM_MID'],
					'TEXT' => str_replace(
						"\n",
						" ",
						Text::removeBbCodes(
							$row['MESSAGE_TEXT'],
							$row['MESSAGE_FILE'] > 0,
							$row['MESSAGE_ATTACH'] > 0
						)
					),
					'FILE' => $row['MESSAGE_FILE'] > 0? true: false,
					'AUTHOR_ID' =>  (int)$row['MESSAGE_AUTHOR_ID'],
					'ATTACH' => $row['MESSAGE_ATTACH'] > 0? true: false,
					'DATE' => $row['MESSAGE_DATE'] > 0? $row['MESSAGE_DATE']: $row['DATE_UPDATE'],
					'STATUS' => $row['CHAT_LAST_MESSAGE_STATUS'],
				],
				'COUNTER' => (int)$row['COUNTER'],
				'PINNED' => $row['PINNED'] === 'Y',
				'UNREAD' => $row['UNREAD'] === 'Y',
				'DATE_UPDATE' => $row['DATE_UPDATE']
			];

			if ($isUser)
			{
				if ($row['INVITATION_ORIGINATOR_ID'])
				{
					$item['INVITED'] = [
						'ORIGINATOR_ID' => (int)$row['INVITATION_ORIGINATOR_ID'],
						'CAN_RESEND' => !empty($row['USER_EMAIL'])
					];
				}
				$item['USER'] = [
					'ID' => (int)$row['ITEM_ID'],
				];
			}
			else if ($isNotification)
			{
				$item['ID'] = 'notify';
				$item['USER'] = [
					'ID' => (int)$row['MESSAGE_AUTHOR_ID'],
				];
				$item['MESSAGE']['TEXT'] = \CTextParser::convert4mail($item['MESSAGE']['TEXT']);
			}
			else
			{
				$avatar = \CIMChat::GetAvatarImage($row['CHAT_AVATAR'], 200, false);
				$color = $row['CHAT_COLOR'] <> ''
					? Color::getColor($row['CHAT_COLOR'])
					: Color::getColorByNumber(
						$row['ITEM_ID']
					);
				$chatType = \Bitrix\Im\Chat::getType($row);

				if ($generalChatId == $row['ITEM_ID'])
				{
					$row["CHAT_ENTITY_TYPE"] = 'GENERAL';
				}

				$muteList = [];
				if ($row['RELATION_NOTIFY_BLOCK'] == 'Y')
				{
					$muteList = [$row['RELATION_USER_ID'] => true];
				}

				$item['AVATAR'] = [
					'URL' => $avatar,
					'COLOR' => $color
				];
				$item['TITLE'] = $row['CHAT_TITLE'];
				$item['CHAT'] = [
					'ID' => (int)$row['ITEM_CID'],
					'NAME' => $row['CHAT_TITLE'],
					'OWNER' => (int)$row['CHAT_AUTHOR_ID'],
					'EXTRANET' => $row['CHAT_EXTRANET'] == 'Y',
					'AVATAR' => $avatar,
					'COLOR' => $color,
					'TYPE' => $chatType,
					'ENTITY_TYPE' => (string)$row['CHAT_ENTITY_TYPE'],
					'ENTITY_ID' => (string)$row['CHAT_ENTITY_ID'],
					'ENTITY_DATA_1' => (string)$row['CHAT_ENTITY_DATA_1'],
					'ENTITY_DATA_2' => (string)$row['CHAT_ENTITY_DATA_2'],
					'ENTITY_DATA_3' => (string)$row['CHAT_ENTITY_DATA_3'],
					'MUTE_LIST' => $muteList,
					'DATE_CREATE' => $row['CHAT_DATE_CREATE'],
					'MESSAGE_TYPE' => $row["CHAT_TYPE"],
				];
				if ($row["CHAT_ENTITY_TYPE"] == 'LINES' && $isOperator)
				{
					$item['LINES'] = [
						'ID' => (int)$row['LINES_ID'],
						'STATUS' => (int)$row['LINES_STATUS'],
					];
				}
				$item['USER'] = [
					'ID' => (int)$row['MESSAGE_AUTHOR_ID'],
				];
			}

			if ($item['USER']['ID'] > 0)
			{
				$user = User::getInstance($item['USER']['ID'])->getArray();
				if (!$user)
				{
					$user = ['ID' => 0];
				}
				else if ($item['TYPE'] == 'user')
				{
					$item['AVATAR'] = [
						'URL' => $user['AVATAR'],
						'COLOR' => $user['COLOR']
					];
					$item['TITLE'] = $user['NAME'];
				}

				$item['USER'] = $user;

				if ($item['MESSAGE']['ID'] == 0)
				{
					$item['MESSAGE']['TEXT'] = $user['WORK_POSITION'];
				}
			}

			$item['OPTIONS'] = [];
			if ($row['USER_ID'] == 0)
			{
				$item['OPTIONS']['DEFAULT_USER_RECORD'] = true;
			}

			$result[] = $item;
		}

		\Bitrix\Main\Type\Collection::sortByColumn(
			$result,
			['PINNED' => SORT_DESC, 'MESSAGE' => SORT_DESC, 'ID' => SORT_DESC],
			[
				'ID' => function($row) {
					return $row;
				},
				'MESSAGE' => function($row) {
					return $row['DATE'] instanceof \Bitrix\Main\Type\DateTime ? $row['DATE']->getTimeStamp() : 0;
				},
			]
		);

		if ($options['JSON'])
		{
			foreach ($result as $index => $item)
			{
				foreach ($item as $key => $value)
				{
					if ($value instanceof \Bitrix\Main\Type\DateTime)
					{
						$item[$key] = date('c', $value->getTimestamp());
					}
					else if (is_array($value))
					{
						foreach ($value as $subKey => $subValue)
						{
							if ($subValue instanceof \Bitrix\Main\Type\DateTime)
							{
								$value[$subKey] = date('c', $subValue->getTimestamp());
							}
							else if (is_string($subValue) &&
									 $subValue &&
									 in_array($subKey, ['URL', 'AVATAR']) &&
								mb_strpos($subValue, 'http') !== 0)
							{
								$value[$subKey] = \Bitrix\Im\Common::getPublicDomain().$subValue;
							}
							else if (is_array($subValue))
							{
								$value[$subKey] = array_change_key_case($subValue, CASE_LOWER);
							}
						}
						$item[$key] = array_change_key_case($value, CASE_LOWER);
					}
				}
				$result[$index] = array_change_key_case($item, CASE_LOWER);
			}
		}

		return $result;
	}

	public static function getUser($userId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
		{
			return false;
		}

		$user = User::getInstance($userId);

		$result = [
			'ID' => $userId,
			'NAME' => $user->getFullName(false),
			'FIRST_NAME' => $user->getName(false),
			'LAST_NAME' => $user->getLastName(false),
			'WORK_POSITION' => $user->getWorkPosition(false),
			'COLOR' => $user->getColor(),
			'AVATAR' => $user->getAvatar(),
			'GENDER' => $user->getGender(),
			'BIRTHDAY' => (string)$user->getBirthday(),
			'EXTRANET' => $user->isExtranet(),
			'NETWORK' => $user->isNetwork(),
			'BOT' => $user->isBot(),
			'CONNECTOR' => $user->isConnector(),
			'EXTERNAL_AUTH_ID' => $user->getExternalAuthId(),
			'STATUS' => $user->getStatus(),
			'IDLE' => $user->getIdle(),
			'LAST_ACTIVITY_DATE' => $user->getLastActivityDate(),
			'MOBILE_LAST_DATE' => $user->getMobileLastDate(),
			'ABSENT' => $user->isAbsent(),
		];

		return $result;
	}

	public static function pin($dialogId, $pin, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$ormParams = [];
		$ormParams['select'] = ["CNT" => new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(1)')];
		$ormParams['filter'] = ['=USER_ID' => $userId, '=PINNED' => 'Y'];

		$pinnedCount = \Bitrix\Im\Model\RecentTable::getRow($ormParams)['CNT'];

		if ($pin && (int)$pinnedCount > self::PINNED_CHATS_LIMIT)
		{
			//TODO: Explain what went wrong
			return false;
		}

		$pin = $pin === true? 'Y': 'N';

		$id = $dialogId;
		if (mb_substr($dialogId, 0, 4) == 'chat')
		{
			$itemTypes = \Bitrix\Im\Chat::getTypes();
			$id = mb_substr($dialogId, 4);
		}
		else if ($dialogId === 'notify')
		{
			$itemTypes = IM_MESSAGE_SYSTEM;
			$id = $userId;
		}
		else
		{
			$itemTypes = IM_MESSAGE_PRIVATE;
		}

		$element = \Bitrix\Im\Model\RecentTable::getList(
			[
				'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'PINNED'],
				'filter' => [
					'=USER_ID' => $userId,
					'=ITEM_TYPE' => $itemTypes,
					'=ITEM_ID' => $id
				]
			]
		)->fetch();
		if (!$element)
		{
//			if (mb_substr($dialogId, 0, 4) == 'chat')
//			{
//				if (!\Bitrix\Im\Dialog::hasAccess($dialogId))
//				{
//					return false;
//				}
//
//				$missingChat = \Bitrix\Im\Model\ChatTable::getRowById($id);
//				$itemTypes = $missingChat['TYPE'];
//			}

//			$messageId = 0;
//			$relationId = 0;
//			if ($itemTypes !== IM_MESSAGE_OPEN)
//			{

//			}

			$chatId = \Bitrix\Im\Dialog::getChatId($id);

			$relationData = \Bitrix\Im\Model\RelationTable::getList(
				[
					'select' => ['ID', 'LAST_SEND_ID'],
					'filter' => [
						'=CHAT_ID' => $chatId,
						'=USER_ID' => $userId,
					]
				]
			)->fetchAll()[0];

			$messageId = $relationData['LAST_SEND_ID'];
			$relationId = $relationData['ID'];

			$addResult = \Bitrix\Im\Model\RecentTable::add(
				[
					'USER_ID' => $userId,
					'ITEM_TYPE' => $itemTypes,
					'ITEM_ID' => $id,
					'ITEM_MID' => $messageId,
					'ITEM_RID' => $relationId,
					'ITEM_CID' => $chatId,
					'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime()
				]
			);
			if (!$addResult->isSuccess())
			{
				return false;
			}

//			self::show($id);

			$element['USER_ID'] = $userId;
			$element['ITEM_TYPE'] = $itemTypes;
			$element['ITEM_ID'] = $id;
		}

		if ($element['PINNED'] == $pin)
		{
			return true;
		}

		\Bitrix\Im\Model\RecentTable::update(
			[
				'USER_ID' => $element['USER_ID'],
				'ITEM_TYPE' => $element['ITEM_TYPE'],
				'ITEM_ID' => $element['ITEM_ID'],
			],
			[
				'PINNED' => $pin,
				'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime()
			]
		);

		self::clearCache($element['USER_ID']);

		$pullInclude = \Bitrix\Main\Loader::includeModule("pull");
		if ($pullInclude)
		{
			\Bitrix\Pull\Event::add(
				$userId,
				[
					'module_id' => 'im',
					'command' => 'chatPin',
					'expiry' => 3600,
					'params' => [
						'dialogId' => $dialogId,
						'active' => $pin == 'Y'
					],
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
		}

		return true;
	}

	public static function unread($dialogId, $unread, $userId = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$unread = $unread === true? 'Y': 'N';

		$id = $dialogId;
		if (mb_substr($dialogId, 0, 4) === 'chat')
		{
			$itemTypes = \Bitrix\Im\Chat::getTypes();
			$id = mb_substr($dialogId, 4);
		}
		else if ($dialogId === 'notify')
		{
			$itemTypes = IM_MESSAGE_SYSTEM;
			$id = $userId;
		}
		else
		{
			$itemTypes = IM_MESSAGE_PRIVATE;
		}

		$element = \Bitrix\Im\Model\RecentTable::getList(
			[
				'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'UNREAD'],
				'filter' => [
					'=USER_ID' => $userId,
					'=ITEM_TYPE' => $itemTypes,
					'=ITEM_ID' => $id
				]
			]
		)->fetch();
		if (!$element)
		{
			return false;
		}
		if ($element['UNREAD'] == $unread)
		{
			return true;
		}

		\Bitrix\Im\Model\RecentTable::update(
			[
				'USER_ID' => $element['USER_ID'],
				'ITEM_TYPE' => $element['ITEM_TYPE'],
				'ITEM_ID' => $element['ITEM_ID'],
			],
			[
				'UNREAD' => $unread,
				'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime()
			]
		);

		self::clearCache($element['USER_ID']);
		\Bitrix\Im\Counter::clearCache($element['USER_ID']);

		$pullInclude = \Bitrix\Main\Loader::includeModule("pull");
		if ($pullInclude)
		{
			\Bitrix\Pull\Event::add(
				$userId,
				[
					'module_id' => 'im',
					'command' => 'chatUnread',
					'expiry' => 3600,
					'params' => [
						'dialogId' => $dialogId,
						'active' => $unread === 'Y'
					],
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
		}

		return true;
	}

	public static function hide($dialogId, $userId = null)
	{
		return \CIMContactList::DialogHide($dialogId, $userId);
	}

	public static function show($dialogId, $userId = null)
	{
		$result = false;
		$userId = Common::getUserId($userId);

		if ($userId)
		{
			$relation = Chat::makeRelationShow($dialogId, $userId);

			if (!empty($relation['ID']))
			{
				$recent = [
					'ENTITY_ID' => $dialogId,
					'MESSAGE_ID' => $relation['UNREAD_ID'],
					'CHAT_TYPE' => $relation['MESSAGE_TYPE'],
					'USER_ID' => $userId,
					'CHAT_ID' => $dialogId,
					'RELATION_ID' => $relation['ID']
				];

				if ($relation['PARAMS']['SESSION_ID'])
				{
					$recent['SESSION_ID'] = $relation['PARAMS']['SESSION_ID'];
				}

				$result = \CIMContactList::SetRecent($recent);

				$pullInclude = \Bitrix\Main\Loader::includeModule("pull");

				if ($pullInclude)
				{
					$chat = \CIMChat::GetChatData(
						[
							'ID' => $dialogId,
							'USE_CACHE' => 'N',
						]
					);

					$imMessage = new \CIMMessage($userId);
					$message = $imMessage->GetMessage($relation['LAST_ID']);

					if (!empty($chat))
					{
						$pullParams = [
							'module_id' => 'im',
							'command' => 'chatShow',
							'params' => \CIMMessage::GetFormatMessage(
								[
									'ID' => $relation['LAST_ID'],
									'CHAT_ID' => $dialogId,
									'TO_CHAT_ID' => $dialogId,
									'FROM_USER_ID' => $message['AUTHOR_ID'],
									'SYSTEM' => 'Y',
									'MESSAGE' => $message['MESSAGE'],
									'DATE_CREATE' => time(),
									//'PARAMS' => self::PrepareParamsForPull($arFields['PARAMS']),
									//'FILES' => $arFields['FILES'],
									'NOTIFY' => true,
									'COUNTER' => 1
								]
							),
							'extra' => \Bitrix\Im\Common::getPullExtra()
						];
						$result = \Bitrix\Pull\Event::add($userId, $pullParams);
					}
				}
			}
		}

		return $result;
	}

	public static function clearCache($userId = null)
	{
		$cache = Application::getInstance()->getCache();
		$cache->cleanDir('/bx/imc/recent'.($userId ? Common::getCacheUserPostfix($userId) : ''));
	}
}
