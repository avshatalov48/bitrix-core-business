<?php

namespace Bitrix\Im;

use Bitrix\Im\Model\LinkReminderTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Message\ViewedService;
use Bitrix\Main\Application, Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ExpressionField;

Loc::loadMessages(__FILE__);

class Recent
{
	private const PINNED_CHATS_LIMIT = 25;

	public static function get($userId = null, $options = [])
	{
		$onlyOpenlinesOption = $options['ONLY_OPENLINES'] ?? null;
		$skipOpenlinesOption = $options['SKIP_OPENLINES'] ?? null;
		$skipChat = $options['SKIP_CHAT'] ?? null;
		$skipDialog = $options['SKIP_DIALOG'] ?? null;

		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$showOpenlines = (
			\Bitrix\Main\Loader::includeModule('imopenlines')
			&& ($onlyOpenlinesOption === 'Y' || $skipOpenlinesOption !== 'Y')
		);

		$generalChatId = \CIMChat::GetGeneralChatId();

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'SHOW_OPENLINES' => $showOpenlines,
			'WITHOUT_COMMON_USERS' => true
		]);

		$lastSyncDateOption = $options['LAST_SYNC_DATE'] ?? null;
		if ($lastSyncDateOption)
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
		if ($onlyOpenlinesOption === 'Y')
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
			if ($skipChat === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_OPEN;
				$skipTypes[] = IM_MESSAGE_CHAT;
			}
			if ($skipDialog === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_PRIVATE;
			}
			if (!empty($skipTypes))
			{
				$ormParams['filter'][] = [
					'!@ITEM_TYPE' => $skipTypes
				];
			}
		}

		if (!isset($options['LAST_SYNC_DATE']))
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
		$rows = $orm->fetchAll();
		$rows = self::prepareRows($rows, $userId);
		foreach ($rows as $row)
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
				'GET_ORIGINAL_TEXT' => $options['GET_ORIGINAL_TEXT'] ?? null,
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

		$viewCommonUsers = (bool)\CIMSettings::GetSetting(\CIMSettings::SETTINGS, 'viewCommonUsers');

		$onlyOpenlinesOption = $options['ONLY_OPENLINES'] ?? null;
		$skipChatOption = $options['SKIP_CHAT'] ?? null;
		$skipDialogOption = $options['SKIP_DIALOG'] ?? null;
		$lastMessageDateOption = $options['LAST_MESSAGE_DATE'] ?? null;
		$withoutCommonUsers = !$viewCommonUsers || $onlyOpenlinesOption === 'Y';
		$unreadOnly = isset($options['UNREAD_ONLY']) && $options['UNREAD_ONLY'] === 'Y';
		$shortInfo = isset($options['SHORT_INFO']) && $options['SHORT_INFO'] === 'Y';

		$showOpenlines = (
			\Bitrix\Main\Loader::includeModule('imopenlines')
			&& (
				$onlyOpenlinesOption === 'Y'
				|| $options['SKIP_OPENLINES'] !== 'Y'
			)
		);

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'SHOW_OPENLINES' => $showOpenlines,
			'WITHOUT_COMMON_USERS' => $withoutCommonUsers,
			'UNREAD_ONLY' => $unreadOnly,
			'SHORT_INFO' => $shortInfo,
		]);

		if ($onlyOpenlinesOption === 'Y')
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
			if ($skipChatOption === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_OPEN;
				$skipTypes[] = IM_MESSAGE_CHAT;
			}
			if ($skipDialogOption === 'Y')
			{
				$skipTypes[] = IM_MESSAGE_PRIVATE;
			}
			if (!empty($skipTypes))
			{
				$ormParams['filter'][] = [
					'!@ITEM_TYPE' => $skipTypes
				];
			}
		}

		if ($lastMessageDateOption instanceof \Bitrix\Main\Type\DateTime)
		{
			$ormParams['filter']['<=DATE_MESSAGE'] = $lastMessageDateOption;
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
		$files = [];

		$rows = $orm->fetchAll();
		$rows = self::prepareRows($rows, $userId);
		foreach ($rows as $row)
		{
			$counter++;
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
				'WITHOUT_COMMON_USERS' => $withoutCommonUsers,
				'GET_ORIGINAL_TEXT' => $options['GET_ORIGINAL_TEXT'] ?? null,
				'SHORT_INFO' => $shortInfo,
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

			$objectToReturn = [
				'items' => $result,
				'hasMorePages' => $ormParams['limit'] == $counter, // TODO remove this later
				'hasMore' => $ormParams['limit'] == $counter
			];

			if (!isset($options['LAST_MESSAGE_DATE']))
			{
				$objectToReturn['birthdayList'] = \Bitrix\Im\Integration\Intranet\User::getBirthdayForToday();
			}

			return $objectToReturn;
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

		$generalChatId = \CIMChat::GetGeneralChatId();

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'SHOW_OPENLINES' => $itemType === IM_MESSAGE_OPEN_LINE,
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
		$rows = $orm->fetchAll();
		$rows = self::prepareRows($rows, $userId);
		foreach ($rows as $row)
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
			]);
			if (!$item)
			{
				continue;
			}

			$result = $item;
		}
		$result = self::prepareRows([$result], $userId)[0];

		if ($options['JSON'])
		{
			$result = self::jsonRow($result);
		}

		return $result;
	}

	private static function getOrmParams($params)
	{
		$userId = (int)$params['USER_ID'];
		$showOpenlines = \Bitrix\Main\Loader::includeModule('imopenlines') && $params['SHOW_OPENLINES'] !== false;
		$isIntranet = \Bitrix\Main\Loader::includeModule('intranet') && \Bitrix\Intranet\Util::isIntranetUser($userId);
		$withoutCommonUsers = $params['WITHOUT_COMMON_USERS'] === true || !$isIntranet;
		$unreadOnly = isset($params['UNREAD_ONLY']) && $params['UNREAD_ONLY'] === true;
		$shortInfo = isset($params['SHORT_INFO']) && $params['SHORT_INFO'] === true;

		$shortInfoFields = [
			'*',
			'RELATION_USER_ID' => 'RELATION.USER_ID',
			'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
			'RELATION_IS_MANAGER' => 'RELATION.MANAGER',
			'CHAT_ID' => 'CHAT.ID',
			'CHAT_TITLE' => 'CHAT.TITLE',
			'CHAT_TYPE' => 'CHAT.TYPE',
			'CHAT_AVATAR' => 'CHAT.AVATAR',
			'CHAT_AUTHOR_ID' => 'CHAT.AUTHOR_ID',
			'CHAT_EXTRANET' => 'CHAT.EXTRANET',
			'CHAT_COLOR' => 'CHAT.COLOR',
			'CHAT_ENTITY_TYPE' => 'CHAT.ENTITY_TYPE',
			'CHAT_ENTITY_ID' => 'CHAT.ENTITY_ID',
			'CHAT_ENTITY_DATA_1' => 'CHAT.ENTITY_DATA_1',
			'CHAT_ENTITY_DATA_2' => 'CHAT.ENTITY_DATA_2',
			'CHAT_ENTITY_DATA_3' => 'CHAT.ENTITY_DATA_3',
			'CHAT_DATE_CREATE' => 'CHAT.DATE_CREATE',
			'CHAT_USER_COUNT' => 'CHAT.USER_COUNT',
			'MESSAGE_CODE' => 'CODE.PARAM_VALUE',
			'USER_LAST_ACTIVITY_DATE' => 'USER.LAST_ACTIVITY_DATE',
			'MESSAGE_DATE' => 'MESSAGE.DATE_CREATE',
		];

		$additionalInfoFields = [
			'MESSAGE_ID' => 'MESSAGE.ID',
			'MESSAGE_AUTHOR_ID' => 'MESSAGE.AUTHOR_ID',
			'MESSAGE_TEXT' => 'MESSAGE.MESSAGE',
			'MESSAGE_FILE' => 'FILE.PARAM_VALUE',
			'MESSAGE_ATTACH' => 'ATTACH.PARAM_VALUE',
			'MESSAGE_ATTACH_JSON' => 'ATTACH.PARAM_JSON',
			'MESSAGE_USER_LAST_ACTIVITY_DATE' => 'MESSAGE.AUTHOR.LAST_ACTIVITY_DATE',
			'MESSAGE_USER_IDLE' => 'MESSAGE.STATUS.IDLE',
			'MESSAGE_USER_MOBILE_LAST_DATE' => 'MESSAGE.STATUS.MOBILE_LAST_DATE',
			'MESSAGE_USER_DESKTOP_LAST_DATE' => 'MESSAGE.STATUS.DESKTOP_LAST_DATE',
			'USER_EMAIL' => 'USER.EMAIL',
			'USER_IDLE' => 'STATUS.IDLE',
			'USER_MOBILE_LAST_DATE' => 'STATUS.MOBILE_LAST_DATE',
			'USER_DESKTOP_LAST_DATE' => 'STATUS.DESKTOP_LAST_DATE',
			'MESSAGE_UUID_VALUE' => 'MESSAGE_UUID.UUID',
			'HAS_REMINDER' => 'HAS_REMINDER',
		];

		$shortRuntime = [
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
		];

		$reminderTable = LinkReminderTable::getTableName();
		$unreadTable = MessageUnreadTable::getTableName();

		$additionalRuntime = [
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
				'STATUS',
				'\Bitrix\Im\Model\StatusTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.USER_ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			),
			new ExpressionField(
				'HAS_REMINDER',
				"CASE WHEN EXISTS (
					SELECT 1
					FROM {$reminderTable}
					WHERE CHAT_ID = %s AND AUTHOR_ID = %s AND IS_REMINDED = 'Y'
				) THEN 'Y' ELSE 'N' END",
				['ITEM_CID', 'USER_ID'],
				['data_type' => 'boolean', 'values' => ['N', 'Y']]
			),
			new ExpressionField(
				'HAS_UNREAD_MESSAGE',
				"EXISTS(SELECT 1 FROM {$unreadTable} WHERE CHAT_ID = %s AND USER_ID = %s)",
				['ITEM_CID', 'USER_ID']
			)
		];

		$select = $shortInfo ? $shortInfoFields : array_merge($shortInfoFields, $additionalInfoFields);
		$runtime = $shortInfo ? $shortRuntime : array_merge($shortRuntime, $additionalRuntime);

		if (!$withoutCommonUsers)
		{
			$select['INVITATION_ORIGINATOR_ID'] = 'INVITATION.ORIGINATOR_ID';
		}
		if ($showOpenlines)
		{
			$select['LINES_ID'] = 'LINES.ID';
			$select['LINES_STATUS'] = 'LINES.STATUS';
			$select['LINES_DATE_CREATE'] = 'LINES.DATE_CREATE';
		}

		if (!$withoutCommonUsers)
		{
			$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
				'INVITATION',
				'\Bitrix\Intranet\Internals\InvitationTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.USER_ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			);
		}
		if ($showOpenlines)
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

		if ($unreadOnly)
		{
			$filter[] = [
				'LOGIC' => 'OR',
				['==HAS_UNREAD_MESSAGE' => true],
				['=UNREAD' => true],
			];
		}

		return [
			'select' => $select,
			'filter' => $filter,
			'runtime' => $runtime,
		];
	}

	private static function formatRow($row, $options = []): ?array
	{
		$generalChatId = (int)$options['GENERAL_CHAT_ID'];
		$withoutCommonUsers = isset($options['WITHOUT_COMMON_USERS']) && $options['WITHOUT_COMMON_USERS'] === true;
		$shortInfo = isset($options['SHORT_INFO']) && $options['SHORT_INFO'];

		$chatOwner = $row['CHAT_OWNER'] ?? null;

		$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
		$id = $isUser? (int)$row['ITEM_ID']: 'chat'.$row['ITEM_ID'];
		$row['MESSAGE_ID'] ??= null;

		if (!$isUser && ((!$row['MESSAGE_ID'] && !$shortInfo) || !$row['RELATION_USER_ID'] || !$row['CHAT_ID']))
		{
			return null;
		}

		if ($row['ITEM_MID'] > 0 && $row['MESSAGE_ID'] > 0)
		{
			$attach = false;
			if ($row['MESSAGE_ATTACH'] || $row["MESSAGE_ATTACH_JSON"])
			{
				if (preg_match('/^(\d+)$/', $row['MESSAGE_ATTACH']))
				{
					$attach = true;
				}
				else if ($row['MESSAGE_ATTACH'] === \CIMMessageParamAttach::FIRST_MESSAGE)
				{
					try
					{
						$value = \Bitrix\Main\Web\Json::decode($row["MESSAGE_ATTACH_JSON"]);
						$attachRestored = \CIMMessageParamAttach::PrepareAttach($value);
						$attach = $attachRestored['DESCRIPTION'];
					}
					catch (\Bitrix\Main\SystemException $e)
					{
						$attach = true;
					}
				}
				else if (!empty($row['MESSAGE_ATTACH']))
				{
					$attach = $row['MESSAGE_ATTACH'];
				}
				else
				{
					$attach = true;
				}
			}

			$text = $row['MESSAGE_TEXT'] ?? '';

			$getOriginalTextOption = $options['GET_ORIGINAL_TEXT'] ?? null;
			if ($getOriginalTextOption === 'Y')
			{
				$text = Text::populateUserBbCode($text);
			}
			else
			{
				$text = Text::removeBbCodes(
					str_replace("\n", " ", $text),
					$row['MESSAGE_FILE'] > 0,
					$attach
				);
			}

			$message = [
				'ID' => (int)$row['ITEM_MID'],
				'TEXT' => $text,
				'FILE' => $row['MESSAGE_FILE'],
				'AUTHOR_ID' =>  (int)$row['MESSAGE_AUTHOR_ID'],
				'ATTACH' => $attach,
				'DATE' => $row['MESSAGE_DATE']?: $row['DATE_UPDATE'],
				'STATUS' => $row['CHAT_LAST_MESSAGE_STATUS'],
				'UUID' => $row['MESSAGE_UUID_VALUE'],
			];
		}
		else
		{
			$row['MESSAGE_DATE'] ??= null;
			$message = [
				'ID' => 0,
				'TEXT' => "",
				'FILE' => false,
				'AUTHOR_ID' =>  0,
				'ATTACH' => false,
				'DATE' => $row['MESSAGE_DATE']?: $row['DATE_UPDATE'],
				'STATUS' => $row['CHAT_LAST_MESSAGE_STATUS'],
			];
		}

		$item = [
			'ID' => $id,
			'CHAT_ID' => (int)$row['CHAT_ID'],
			'TYPE' => $isUser ? 'user' : 'chat',
			'AVATAR' => [],
			'TITLE' => [],
			'MESSAGE' => $message,
			'COUNTER' => (int)$row['COUNTER'],
			'PINNED' => $row['PINNED'] === 'Y',
			'UNREAD' => $row['UNREAD'] === 'Y',
			'HAS_REMINDER' => isset($row['HAS_REMINDER']) && $row['HAS_REMINDER'] === 'Y',
			'DATE_UPDATE' => $row['DATE_UPDATE']
		];

		if ($isUser)
		{
			if (
				$withoutCommonUsers
				&& ($row['USER_ID'] == 0 || $row['MESSAGE_CODE'] === 'USER_JOIN')
			)
			{
				return null;
			}

			$row['INVITATION_ORIGINATOR_ID'] ??= null;
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
				$chatOwner == $row['RELATION_USER_ID']
				|| $row['RELATION_IS_MANAGER'] == 'Y'
			)
			{
				$managerList = [(int)$row['RELATION_USER_ID']];
			}

			if ($row['RELATION_NOTIFY_BLOCK'] == 'Y')
			{
				$muteList = [$row['RELATION_USER_ID'] => true];
			}

			$chatOptions = \CIMChat::GetChatOptions();
			$restrictions = $chatOptions['DEFAULT'];
			if ($row['CHAT_ENTITY_TYPE'] && array_key_exists($row['CHAT_ENTITY_TYPE'], $chatOptions))
			{
				$restrictions = $chatOptions[$row['CHAT_ENTITY_TYPE']];
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
				'USER_COUNTER' => (int)$row['CHAT_USER_COUNT'],
				'RESTRICTIONS' => $restrictions
			];
			if ($row["CHAT_ENTITY_TYPE"] == 'LINES')
			{
				$item['LINES'] = [
					'ID' => (int)$row['LINES_ID'],
					'STATUS' => (int)$row['LINES_STATUS'],
					'DATE_CREATE' => $row['LINES_DATE_CREATE'] ?? $row['DATE_UPDATE'],
				];
			}
			$item['USER'] = [
				'ID' => (int)($row['MESSAGE_AUTHOR_ID'] ?? 0),
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
					(!$user['ACTIVE'] && $item['COUNTER'] <= 0)
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

				$row['USER_LAST_ACTIVITY_DATE'] ??= null;
				$row['USER_DESKTOP_LAST_DATE'] ??= null;
				$row['USER_MOBILE_LAST_DATE'] ??= null;
				$row['USER_IDLE'] ??= null;

				$user['LAST_ACTIVITY_DATE'] = $row['USER_LAST_ACTIVITY_DATE']?: false;
				$user['DESKTOP_LAST_DATE'] = $row['USER_DESKTOP_LAST_DATE']?: false;
				$user['MOBILE_LAST_DATE'] = $row['USER_MOBILE_LAST_DATE']?: false;
				$user['IDLE'] = $row['USER_IDLE']?: false;
			}
			else
			{
				$row['MESSAGE_USER_LAST_ACTIVITY_DATE'] ??= null;
				$row['MESSAGE_USER_DESKTOP_LAST_DATE'] ??= null;
				$row['MESSAGE_USER_MOBILE_LAST_DATE'] ??= null;
				$row['MESSAGE_USER_IDLE'] ??= null;

				$user['LAST_ACTIVITY_DATE'] = $row['MESSAGE_USER_LAST_ACTIVITY_DATE']?: false;
				$user['DESKTOP_LAST_DATE'] = $row['MESSAGE_USER_DESKTOP_LAST_DATE']?: false;
				$user['MOBILE_LAST_DATE'] = $row['MESSAGE_USER_MOBILE_LAST_DATE']?: false;
				$user['IDLE'] = $row['MESSAGE_USER_IDLE']?: false;
			}

			$item['USER'] = $user;
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
					'select' => ['ID', 'CHAT.LAST_MESSAGE_ID' => 'LAST_MESSAGE_ID'],
					'filter' => [
						'=CHAT_ID' => $chatId,
						'=USER_ID' => $userId,
					]
				]
			)->fetchAll()[0];

			$messageId = $relationData['LAST_MESSAGE_ID'];
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

	public static function unread($dialogId, $unread, $userId = null, ?int $markedId = null)
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
		else
		{
			$itemTypes = IM_MESSAGE_PRIVATE;
		}

		$element = \Bitrix\Im\Model\RecentTable::getList([
			'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'UNREAD', 'MUTED' => 'RELATION.NOTIFY_BLOCK', 'ITEM_CID', 'MARKED_ID'],
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
		if ($element['UNREAD'] === $unread && !isset($markedId))
		{
			return true;
		}

		$updatedFields = [
			'UNREAD' => $unread,
			'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
		];

		if ($unread === 'N')
		{
			$markedId = 0;
		}
		if (isset($markedId))
		{
			$updatedFields['MARKED_ID'] = $markedId;
		}

		\Bitrix\Im\Model\RecentTable::update(
			[
				'USER_ID' => $element['USER_ID'],
				'ITEM_TYPE' => $element['ITEM_TYPE'],
				'ITEM_ID' => $element['ITEM_ID'],
			],
			$updatedFields
		);

		self::clearCache($element['USER_ID']);
		//\Bitrix\Im\Counter::clearCache($element['USER_ID']);
		CounterService::clearCache((int)$element['USER_ID']);

		$pullInclude = \Bitrix\Main\Loader::includeModule("pull");
		if ($pullInclude)
		{
			$chatId = (int)$element['ITEM_CID'];
			$readService = new ReadService($userId);
			$counter = $readService->getCounterService()->getByChat($chatId);
			//$readService->sendPush($chatId, [$userId], $counter, $time);

			\Bitrix\Pull\Event::add(
				$userId,
				[
					'module_id' => 'im',
					'command' => 'chatUnread',
					'expiry' => 3600,
					'params' => [
						'chatId' => $chatId,
						'dialogId' => $dialogId,
						'active' => $unread === 'Y',
						'muted' => $element['MUTED'] === 'Y',
						'counter' => $counter,
						'markedId' => $markedId ?? $element['MARKED_ID'],
						'lines' => $element['ITEM_TYPE'] === IM_MESSAGE_OPEN_LINE,
					],
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
		}

		return true;
	}

	public static function readAll(int $userId): void
	{
		\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_recent R
			SET R.UNREAD = 'N', R.MARKED_ID = 0
			WHERE R.UNREAD = 'Y'
			AND R.USER_ID = {$userId}"
		);
	}

	public static function isUnread(int $userId, string $itemType, string $dialogId): bool
	{
		$id = mb_strpos($dialogId, 'chat') === 0 ? mb_substr($dialogId, 4) : $dialogId;
		$element = \Bitrix\Im\Model\RecentTable::getList([
			'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'UNREAD', 'MUTED' => 'RELATION.NOTIFY_BLOCK', 'ITEM_CID'],
			'filter' => [
				'=USER_ID' => $userId,
				'=ITEM_TYPE' => $itemType,
				'=ITEM_ID' => $id
			]
		])->fetch();
		if (!$element)
		{
			return false;
		}

		return ($element['UNREAD'] ?? 'N') === 'Y';
	}

	public static function getMarkedId(int $userId, string $itemType, string $dialogId): int
	{
		$id = mb_strpos($dialogId, 'chat') === 0 ? mb_substr($dialogId, 4) : $dialogId;
		$element = \Bitrix\Im\Model\RecentTable::getList([
			'select' => ['MARKED_ID'],
			'filter' => [
				'=USER_ID' => $userId,
				'=ITEM_TYPE' => $itemType,
				'=ITEM_ID' => $id
			]
		])->fetch();
		if (!$element)
		{
			return 0;
		}

		return (int)($element['MARKED_ID'] ?? 0);
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

	protected static function prepareRows(array $rows, int $userId): array
	{
		$rows = static::fillCounters($rows, $userId);
		$rows = static::fillFiles($rows);

		return static::fillLastMessageStatuses($rows, $userId);
	}

	protected static function fillCounters(array $rows, int $userId): array
	{
		$chatIds = [];

		foreach ($rows as $row)
		{
			$chatIds[] = (int)$row['CHAT_ID'];
		}

		$counters = (new CounterService($userId))->getForEachChat($chatIds);

		foreach ($rows as $key => $row)
		{
			$rows[$key]['COUNTER'] = (int)($counters[(int)$row['CHAT_ID']] ?? 0);
		}

		return $rows;
	}

	protected static function fillLastMessageStatuses(array $rows, int $userId): array
	{
		$messageIds = [];

		foreach ($rows as $row)
		{
			if (isset($row['MESSAGE_AUTHOR_ID']) && (int)$row['MESSAGE_AUTHOR_ID'] === $userId)
			{
				$messageIds[] = (int)$row['MESSAGE_ID'];
			}
		}

		$messageStatuses = (new ViewedService($userId))->getMessageStatuses($messageIds);

		foreach ($rows as $key => $row)
		{
			$rows[$key]['CHAT_LAST_MESSAGE_STATUS'] = $messageStatuses[(int)($row['MESSAGE_ID'] ?? 0)] ?? \IM_MESSAGE_STATUS_RECEIVED;
		}

		return $rows;
	}

	protected static function fillFiles(array $rows): array
	{
		$fileIds = [];

		foreach ($rows as $row)
		{
			if (isset($row['MESSAGE_FILE']) && $row['MESSAGE_FILE'] > 0)
			{
				$fileIds[] = (int)$row['MESSAGE_FILE'];
			}
		}

		$files = FileCollection::initByDiskFilesIds($fileIds);

		foreach ($rows as $key => $row)
		{
			$fileId = $row['MESSAGE_FILE'] ?? null;
			$rows[$key]['MESSAGE_FILE'] = false;
			if (isset($fileId) && $fileId > 0)
			{
				$file = $files->getById((int)$fileId);
				if ($file !== null)
				{
					/** @var FileItem $file */
					$rows[$key]['MESSAGE_FILE'] = [
						'TYPE' => $file->getContentType(),
						'NAME' => $file->getDiskFile()->getName(),
					];
				}
			}
		}

		return $rows;
	}
}
