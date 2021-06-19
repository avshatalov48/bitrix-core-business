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

		$isOperator = \Bitrix\Im\Integration\Imopenlines\User::isOperator();
		$generalChatId = \CIMChat::GetGeneralChatId();

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'IS_OPERATOR' => $isOperator,
			'WITHOUT_COMMON_USERS' => true,
		]);

		if ($options['LAST_SYNC_DATE'])
		{
			$maxLimit = (new \Bitrix\Main\Type\DateTime())->add('-7 days');
			if ($maxLimit > $options['LAST_SYNC_DATE'])
			{
				$options['LAST_SYNC_DATE'] = $maxLimit;
			}
			$ormParams['filter']['>=DATE_UPDATE'] = $options['LAST_SYNC_DATE'];
		}
		else if ($options['ONLY_OPENLINES'] !== 'Y')
		{
			$ormParams['filter']['>=DATE_UPDATE'] = (new \Bitrix\Main\Type\DateTime())->add('-30 days');
		}

		$skipTypes = [];
		if ($options['ONLY_OPENLINES'] === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => IM_MESSAGE_OPEN_LINE
			];
		}
		else
		{
			if ($options['SKIP_OPENLINES'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_OPEN_LINE;
			}
			if ($options['SKIP_CHAT'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_OPEN;
				$skipTypes[] = IM_MESSAGE_CHAT;
			}
			if ($options['SKIP_DIALOG'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_PRIVATE;
			}
			if ($options['SKIP_NOTIFICATION'] !== 'N')
			{
				$skipTypes[] = IM_MESSAGE_SYSTEM;
			}
			if (!empty($skipTypes))
			{
				$ormParams['filter'][] = [
					'!@ITEM_TYPE' => $skipTypes
				];
			}
		}

		if (!isset($options['LAST_UPDATE']))
		{
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
		}

		$result = [];
		$orm = \Bitrix\Im\Model\RecentTable::getList($ormParams);
		while ($row = $orm->fetch())
		{
			$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
			$id = $isUser? (int)$row['ITEM_ID']: 'chat'.$row['ITEM_ID'];

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

			$item = self::formatRow($row, [
				'GENERAL_CHAT_ID' => $generalChatId,
				'IS_OPERATOR' => $isOperator,
			]);
			if (!$item)
			{
				continue;
			}

			$result[$id] = $item;
		}
		$result = array_values($result);

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
				$result[$index] = self::jsonRow($item);
			}
		}

		return $result;
	}

	public static function getList($userId = null, $options = [])
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$generalChatId = \CIMChat::GetGeneralChatId();

		if ($options['SKIP_OPENLINES'] === 'Y')
		{
			$isOperator = false;
		}
		else if (isset($options['IS_OPERATOR']))
		{
			$isOperator = $options['IS_OPERATOR'] === 'Y';
		}
		else
		{
			$isOperator = \Bitrix\Im\Integration\Imopenlines\User::isOperator();
		}

		$viewCommonUsers = (bool)\CIMSettings::GetSetting(\CIMSettings::SETTINGS, 'viewCommonUsers');

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'IS_OPERATOR' => $isOperator,
			'WITHOUT_COMMON_USERS' => !$viewCommonUsers || $options['ONLY_OPENLINES'] === 'Y'
		]);

		if ($options['ONLY_OPENLINES'] === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => IM_MESSAGE_OPEN_LINE
			];
		}
		else
		{
			$skipTypes = [];
			if ($options['SKIP_OPENLINES'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_OPEN_LINE;
			}
			if ($options['SKIP_CHAT'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_OPEN;
				$skipTypes[] = IM_MESSAGE_CHAT;
			}
			if ($options['SKIP_DIALOG'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_PRIVATE;
			}
			if ($options['SKIP_NOTIFICATION'] === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_SYSTEM;
			}
			if (!empty($skipTypes))
			{
				$ormParams['filter'][] = [
					'!@ITEM_TYPE' => $skipTypes
				];
			}
		}

		if ($options['LAST_MESSAGE_DATE'] instanceof \Bitrix\Main\Type\DateTime)
		{
			$ormParams['filter']['<=DATE_MESSAGE'] = $options['LAST_MESSAGE_DATE'];
		}
		else if (isset($options['OFFSET']))
		{
			$ormParams['offset'] = $options['OFFSET'];
		}

		if (isset($options['LIMIT']))
		{
			$ormParams['limit'] = (int)$options['LIMIT'];
		}
		else
		{
			$ormParams['limit'] = 50;
		}

		$ormParams['order'] = [
			'PINNED' => 'DESC',
			'DATE_MESSAGE' => 'DESC',
		];

		$orm = \Bitrix\Im\Model\RecentTable::getList($ormParams);

		$counter = 0;
		$result = [];

		while ($row = $orm->fetch())
		{
			$counter++;
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

			$item = self::formatRow($row, [
				'GENERAL_CHAT_ID' => $generalChatId,
				'IS_OPERATOR' => $isOperator,
			]);
			if (!$item)
			{
				continue;
			}

			$result[$id] = $item;
		}

		$result = array_values($result);

		if ($options['JSON'])
		{
			foreach ($result as $index => $item)
			{
				$result[$index] = self::jsonRow($item);
			}

			return [
				'items' => $result,
				'hasMorePages' => $ormParams['limit'] == $counter, // TODO remove this later
				'hasMore' => $ormParams['limit'] == $counter
			];
		}

		return [
			'ITEMS' => $result,
			'HAS_MORE_PAGES' => $ormParams['limit'] == $counter, // TODO remove this later
			'HAS_MORE' => $ormParams['limit'] == $counter
		];
	}

	public static function getElement($itemType, $itemId, $userId = null, $options = [])
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$isOperator = \Bitrix\Im\Integration\Imopenlines\User::isOperator($userId);
		$generalChatId = \CIMChat::GetGeneralChatId();

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'IS_OPERATOR' => $isOperator,
			'WITHOUT_COMMON_USERS' => true,
		]);

		$ormParams['filter']['=ITEM_TYPE'] = $itemType;
		$ormParams['filter']['=ITEM_ID'] = $itemId;

		$orm = \Bitrix\Im\Model\RecentTable::getList([
			'select' => $ormParams['select'],
			'filter' => $ormParams['filter'],
			'runtime' => $ormParams['runtime'],
		]);

		$result = null;
		while ($row = $orm->fetch())
		{
			$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
			if ($isUser)
			{
				if ($result && !$row['ITEM_MID'])
				{
					continue;
				}
			}
			else if ($result)
			{
				continue;
			}

			$item = self::formatRow($row, [
				'GENERAL_CHAT_ID' => $generalChatId,
				'IS_OPERATOR' => $isOperator,
			]);
			if (!$item)
			{
				continue;
			}

			$result = $item;
		}

		if ($options['JSON'])
		{
			$result = self::jsonRow($result);
		}

		return $result;
	}

	private static function getOrmParams($params)
	{
		$userId = (int)$params['USER_ID'];
		$isOperator = \Bitrix\Main\Loader::includeModule('imopenlines') && (bool)$params['IS_OPERATOR'];
		$isIntranet = \Bitrix\Main\Loader::includeModule('intranet') && \Bitrix\Intranet\Util::isIntranetUser($userId);
		$withoutCommonUsers = $params['WITHOUT_COMMON_USERS'] === true || !$isIntranet;

		$select = [
			'*',
			'COUNTER' => 'RELATION.COUNTER',
			'MESSAGE_ID' => 'MESSAGE.ID',
			'MESSAGE_AUTHOR_ID' => 'MESSAGE.AUTHOR_ID',
			'MESSAGE_TEXT' => 'MESSAGE.MESSAGE',
			'MESSAGE_FILE' => 'FILE.ID',
			'MESSAGE_DATE' => 'MESSAGE.DATE_CREATE',
			'MESSAGE_ATTACH' => 'ATTACH.ID',
			'MESSAGE_CODE' => 'CODE.PARAM_VALUE',
			'MESSAGE_USER_LAST_ACTIVITY_DATE' => 'MESSAGE.AUTHOR.LAST_ACTIVITY_DATE',
			'MESSAGE_USER_IDLE' => 'MESSAGE.STATUS.IDLE',
			'MESSAGE_USER_MOBILE_LAST_DATE' => 'MESSAGE.STATUS.MOBILE_LAST_DATE',
			'MESSAGE_USER_DESKTOP_LAST_DATE' => 'MESSAGE.STATUS.DESKTOP_LAST_DATE',
			'RELATION_USER_ID' => 'RELATION.USER_ID',
			'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
			'RELATION_IS_MANAGER' => 'RELATION.MANAGER',
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
			'USER_EMAIL' => 'USER.EMAIL',
			'USER_LAST_ACTIVITY_DATE' => 'USER.LAST_ACTIVITY_DATE',
			'USER_IDLE' => 'STATUS.IDLE',
			'USER_MOBILE_LAST_DATE' => 'STATUS.MOBILE_LAST_DATE',
			'USER_DESKTOP_LAST_DATE' => 'STATUS.DESKTOP_LAST_DATE',
		];
		if (!$withoutCommonUsers)
		{
			$select['INVITATION_ORIGINATOR_ID'] = 'INVITATION.ORIGINATOR_ID';
		}
		if ($isOperator)
		{
			$select['LINES_ID'] = 'LINES.ID';
			$select['LINES_STATUS'] = 'LINES.STATUS';
			$select['LINES_DATE_CREATE'] = 'LINES.DATE_CREATE';
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
				'CODE',
				'\Bitrix\Im\Model\MessageParamTable',
				[
					"=ref.MESSAGE_ID" => "this.ITEM_MID",
					"ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "CODE")
				],
				["join_type" => "LEFT"]
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'USER',
				'\Bitrix\Main\UserTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			),
			new \Bitrix\Main\Entity\ReferenceField(
				'STATUS',
				'\Bitrix\Im\Model\StatusTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.USER_ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			)
		];
		if (!$withoutCommonUsers)
		{
			$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
				'INVITATION',
				'\Bitrix\Intranet\Internals\InvitationTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.USER_ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			);
		}
		if ($isOperator)
		{
			$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
				'LINES',
				'\Bitrix\ImOpenlines\Model\SessionTable',
				[">this.ITEM_OLID" => new \Bitrix\Main\DB\SqlExpression("0"), "=ref.ID" => "this.ITEM_OLID"],
				["join_type" => "LEFT"]
			);
		}

		if ($withoutCommonUsers)
		{
			$filter = ['=USER_ID' => $userId];
		}
		else
		{
			$filter = ['@USER_ID' => [$userId, 0]];
		}

		return [
			'select' => $select,
			'filter' => $filter,
			'runtime' => $runtime,
		];
	}

	private static function formatRow($row, $options = []):? array
	{
		$generalChatId = (int)$options['GENERAL_CHAT_ID'];
		$isOperator = (bool)$options['IS_OPERATOR'];

		$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
		$isNotification = $row['ITEM_TYPE'] == IM_MESSAGE_SYSTEM;
		$id = $isUser? (int)$row['ITEM_ID']: ($isNotification? 'notify' : 'chat'.$row['ITEM_ID']);

		if (!$isUser && (!$row['MESSAGE_ID'] || !$row['RELATION_USER_ID'] || !$row['CHAT_ID']))
		{
			return null;
		}

		$item = [
			'ID' => $id,
			'CHAT_ID' => (int)$row['CHAT_ID'],
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
				'FILE' => $row['MESSAGE_FILE'] > 0,
				'AUTHOR_ID' =>  (int)$row['MESSAGE_AUTHOR_ID'],
				'ATTACH' => $row['MESSAGE_ATTACH'] > 0,
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
			if (!$row['USER_LAST_ACTIVITY_DATE'] && $row['INVITATION_ORIGINATOR_ID'])
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
			$parser = new \CTextParser();

			$item['ID'] = 'notify';
			$item['USER'] = [
				'ID' => (int)$row['MESSAGE_AUTHOR_ID'],
			];
			$item['MESSAGE']['TEXT'] = $parser->convert4mail($item['MESSAGE']['TEXT']);
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

			$managerList = [];
			if (
				$row['CHAT_OWNER'] == $row['RELATION_USER_ID']
				|| $row['RELATION_IS_MANAGER'] == 'Y'
			)
			{
				$managerList = [(int)$row['RELATION_USER_ID']];
			}

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
				'MANAGER_LIST' => $managerList,
				'DATE_CREATE' => $row['CHAT_DATE_CREATE'],
				'MESSAGE_TYPE' => $row["CHAT_TYPE"],
			];
			if ($row["CHAT_ENTITY_TYPE"] == 'LINES' && $isOperator)
			{
				$item['LINES'] = [
					'ID' => (int)$row['LINES_ID'],
					'STATUS' => (int)$row['LINES_STATUS'],
					'DATE_CREATE' => $row['LINES_DATE_CREATE'],
				];
			}
			$item['USER'] = [
				'ID' => (int)$row['MESSAGE_AUTHOR_ID'],
			];
		}

		if ($item['USER']['ID'] > 0)
		{
			$user = User::getInstance($item['USER']['ID'])->getArray(['SKIP_ONLINE' => 'Y']);
			if (!$user)
			{
				$user = ['ID' => 0];
			}
			else if ($item['TYPE'] == 'user')
			{
				if (
					!$user['ACTIVE']
					&& !$user['BOT']
					&& !$user['CONNECTOR']
					&& !$user['NETWORK']
				)
				{
					return null;
				}

				$item['AVATAR'] = [
					'URL' => $user['AVATAR'],
					'COLOR' => $user['COLOR']
				];

				$item['TITLE'] = $user['NAME'];

				$user['LAST_ACTIVITY_DATE'] = $row['USER_LAST_ACTIVITY_DATE']?: false;
				$user['DESKTOP_LAST_DATE'] = $row['USER_DESKTOP_LAST_DATE']?: false;
				$user['MOBILE_LAST_DATE'] = $row['USER_MOBILE_LAST_DATE']?: false;
				$user['IDLE'] = $row['USER_IDLE']?: false;
			}
			else
			{
				$user['LAST_ACTIVITY_DATE'] = $row['MESSAGE_USER_LAST_ACTIVITY_DATE']?: false;
				$user['DESKTOP_LAST_DATE'] = $row['MESSAGE_USER_DESKTOP_LAST_DATE']?: false;
				$user['MOBILE_LAST_DATE'] = $row['MESSAGE_USER_MOBILE_LAST_DATE']?: false;
				$user['IDLE'] = $row['MESSAGE_USER_IDLE']?: false;
			}

			$item['USER'] = $user;

			if ($item['MESSAGE']['ID'] == 0)
			{
				$item['MESSAGE']['TEXT'] = $user['WORK_POSITION'];
			}
		}

		$item['OPTIONS'] = [];
		if ($row['USER_ID'] == 0 || $row['MESSAGE_CODE'] === 'USER_JOIN')
		{
			$item['OPTIONS']['DEFAULT_USER_RECORD'] = true;
		}

		return $item;
	}

	private static function jsonRow($item)
	{
		if (!is_array($item))
		{
			return $item;
		}

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
					else if (
						is_string($subValue)
						&& $subValue
						&& in_array($subKey, ['URL', 'AVATAR'])
						&& mb_strpos($subValue, 'http') !== 0
					)
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

		return array_change_key_case($item, CASE_LOWER);
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

		$element = \Bitrix\Im\Model\RecentTable::getList([
			'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'UNREAD', 'COUNTER' => 'RELATION.COUNTER', 'MUTED' => 'RELATION.NOTIFY_BLOCK'],
			'filter' => [
				'=USER_ID' => $userId,
				'=ITEM_TYPE' => $itemTypes,
				'=ITEM_ID' => $id
			]
		])->fetch();
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
			$counter = (int)$element['COUNTER'];

			\Bitrix\Pull\Event::add(
				$userId,
				[
					'module_id' => 'im',
					'command' => 'chatUnread',
					'expiry' => 3600,
					'params' => [
						'dialogId' => $dialogId,
						'active' => $unread === 'Y',
						'muted' => $element['MUTED'] === 'Y',
						'counter' => $counter,
						'lines' => $element['ITEM_TYPE'] === IM_MESSAGE_OPEN_LINE,
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

	public static function show($dialogId, $options = [], $userId = null)
	{
		$userId = Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$chatId = Dialog::getChatId($dialogId, $userId);
		if (Common::isChatId($dialogId))
		{
			$entityId = $chatId;
		}
		else
		{
			$entityId = (int)$dialogId;
		}

		$relation = \Bitrix\Im\Model\RelationTable::getList([
			'select' => [
				'ID',
				'TYPE' => 'CHAT.TYPE',
				'LAST_MESSAGE_ID' => 'CHAT.LAST_MESSAGE_ID',
				'LAST_MESSAGE_DATE' => 'MESSAGE.DATE_CREATE'
			],
			'filter' => [
				'=CHAT_ID' => $chatId,
				'=USER_ID' => $userId
			],
			'runtime' => [
				new \Bitrix\Main\Entity\ReferenceField(
					'MESSAGE',
					'\Bitrix\Im\Model\MessageTable',
					["=ref.ID" => "this.CHAT.LAST_MESSAGE_ID"],
					["join_type" => "LEFT"]
				),
			]
		])->fetch();

		if ($relation)
		{
			$relationId = $relation['ID'];
			$entityType = $relation['TYPE'];
			$messageId = $relation['LAST_MESSAGE_ID'];
			$messageDate = $relation['LAST_MESSAGE_DATE'];
		}
		else if (
			isset($options['CHAT_DATA']['TYPE'])
			&& isset($options['CHAT_DATA']['LAST_MESSAGE_ID'])
		)
		{
			$relationId = 0;
			$entityType = $options['CHAT_DATA']['TYPE'];
			$messageId = $options['CHAT_DATA']['LAST_MESSAGE_ID'];
			$messageDate = $options['CHAT_DATA']['LAST_MESSAGE_DATE'];
		}
		else
		{
			$chat = \Bitrix\Im\Model\ChatTable::getList([
				'select' => [
					'TYPE',
					'LAST_MESSAGE_ID',
					'LAST_MESSAGE_DATE' => 'MESSAGE.DATE_CREATE'
				],
				'filter' => [
					'=ID' => $chatId,
				],
				'runtime' => [
					new \Bitrix\Main\Entity\ReferenceField(
						'MESSAGE',
						'\Bitrix\Im\Model\MessageTable',
						["=ref.ID" => "this.LAST_MESSAGE_ID"],
						["join_type" => "LEFT"]
					),
				]
			])->fetch();
			if (!$chat)
			{
				return false;
			}

			$relationId = 0;
			$entityType = $chat['TYPE'];
			$messageId = $chat['LAST_MESSAGE_ID'];
			$messageDate = $chat['LAST_MESSAGE_DATE'];
		}

		$sessionId = 0;
		if ($entityType == IM_MESSAGE_OPEN_LINE)
		{
			if (isset($options['SESSION_ID']))
			{
				$sessionId = (int)$options['SESSION_ID'];
			}
			else if (\Bitrix\Main\Loader::includeModule('imopenlines'))
			{
				$session = \Bitrix\ImOpenLines\Model\SessionTable::getList([
					'select' => ['ID'],
					'filter' => ['=CHAT_ID' => $chatId],
					'order' => ['ID' => 'DESC'],
					'limit' => 1,
				])->fetch();
				if ($session)
				{
					$sessionId = $session['ID'];
				}
			}
		}

		\CIMContactList::SetRecent($temp = [
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
			'MESSAGE_ID' => $messageId,
			'MESSAGE_DATE' => $messageDate,
			'CHAT_ID' => $chatId,
			'RELATION_ID' => $relationId,
			'SESSION_ID' => $sessionId,
			'USER_ID' => $userId,
		]);

		if (!\Bitrix\Main\Loader::includeModule("pull"))
		{
			return true;
		}

		$data = \Bitrix\Im\Recent::getElement($entityType, $entityId, $userId, ['JSON' => true]);
		if ($data)
		{
			\Bitrix\Pull\Event::add($userId, [
				'module_id' => 'im',
				'command' => 'chatShow',
				'params' => $data,
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}

		return true;
	}

	public static function clearCache($userId = null)
	{
		$cache = Application::getInstance()->getCache();
		$cache->cleanDir('/bx/imc/recent'.($userId ? Common::getCacheUserPostfix($userId) : ''));
	}
}
