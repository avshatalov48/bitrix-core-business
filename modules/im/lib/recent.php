<?php

namespace Bitrix\Im;

use Bitrix\Im\Model\MessageParamTable;
use Bitrix\Im\Model\MessageUnreadTable;
use Bitrix\Im\Model\RecentTable;
use Bitrix\Im\V2\Chat\Copilot\CopilotPopupItem;
use Bitrix\Im\V2\Chat\EntityLink;
use Bitrix\Im\V2\Message\Counter\CounterType;
use Bitrix\Im\V2\Permission;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Im\V2\Integration\Socialnetwork\Group;
use Bitrix\Im\V2\Message;
use Bitrix\Im\V2\Message\CounterService;
use Bitrix\Im\V2\Entity\File\FileCollection;
use Bitrix\Im\V2\Entity\File\FileItem;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Im\V2\Message\MessagePopupItem;
use Bitrix\Im\V2\Message\ReadService;
use Bitrix\Im\V2\Message\Send\PushService;
use Bitrix\Im\V2\RelationCollection;
use Bitrix\Im\V2\Rest\RestAdapter;
use Bitrix\Im\V2\Settings\UserConfiguration;
use Bitrix\Im\V2\Sync;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Application, Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Bitrix\Pull\Event;

Loc::loadMessages(__FILE__);

class Recent
{
	private static array $unreadElementCache = [];
	private const PINNED_CHATS_LIMIT = 45;

	static private bool $limitError = false;

	public static function get($userId = null, $options = [])
	{
		$onlyOpenlinesOption = $options['ONLY_OPENLINES'] ?? null;
		$onlyCopilotOption = $options['ONLY_COPILOT'] ?? null;
		$skipOpenlinesOption = $options['SKIP_OPENLINES'] ?? null;
		$skipChat = $options['SKIP_CHAT'] ?? null;
		$skipDialog = $options['SKIP_DIALOG'] ?? null;
		$byChatIds = isset($options['CHAT_IDS']);

		if (isset($options['FORCE_OPENLINES']) && $options['FORCE_OPENLINES'] === 'Y')
		{
			$forceOpenlines = 'Y';
		}
		else
		{
			$forceOpenlines = 'N';
		}

		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$showOpenlines = (
			\Bitrix\Main\Loader::includeModule('imopenlines')
			&& ($onlyOpenlinesOption === 'Y' || $skipOpenlinesOption !== 'Y')
		);

		if (
			$showOpenlines
			&& $forceOpenlines !== 'Y'
			&& class_exists('\Bitrix\ImOpenLines\Recent')
		)
		{
			return \Bitrix\ImOpenLines\Recent::getRecent($userId, $options);
		}

		$generalChatId = \CIMChat::GetGeneralChatId();

		$ormParams = self::getOrmParams([
			'USER_ID' => $userId,
			'SHOW_OPENLINES' => $showOpenlines,
			'WITHOUT_COMMON_USERS' => true,
			'CHAT_IDS' => $options['CHAT_IDS'] ?? null,
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
		else if ($options['ONLY_OPENLINES'] !== 'Y' && !$byChatIds)
		{
			$ormParams['filter']['>=DATE_UPDATE'] = (new \Bitrix\Main\Type\DateTime())->add('-30 days');
		}

		$skipTypes = [];
		if ($onlyCopilotOption === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => \Bitrix\Im\V2\Chat::IM_TYPE_COPILOT
			];
		}
		elseif ($onlyOpenlinesOption === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => IM_MESSAGE_OPEN_LINE
			];
		}
		elseif (!$byChatIds)
		{
			$skipTypes[] = \Bitrix\Im\V2\Chat::IM_TYPE_COPILOT;
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

		if (
			$showOpenlines
			&& !$options['ONLY_OPENLINES']
			&& class_exists('\Bitrix\ImOpenLines\Recent')
		)
		{
			$options['ONLY_IN_QUEUE'] = true;
			$chatsInQueue = \Bitrix\ImOpenLines\Recent::getRecent($userId, $options);
			$result = array_merge($result, $chatsInQueue);
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

		$viewCommonUsers = Option::get('im', 'view_common_users', 'Y') === 'N'
			? false
			: (bool)\CIMSettings::GetSetting(\CIMSettings::SETTINGS, 'viewCommonUsers')
		;

		$onlyOpenlinesOption = $options['ONLY_OPENLINES'] ?? null;
		$onlyCopilotOption = $options['ONLY_COPILOT'] ?? null;
		$onlyChannelOption = $options['ONLY_CHANNEL'] ?? null;
		$canManageMessagesOption = $options['CAN_MANAGE_MESSAGES'] ?? null;
		$skipChatOption = $options['SKIP_CHAT'] ?? null;
		$skipDialogOption = $options['SKIP_DIALOG'] ?? null;
		$skipCollabOption = $options['SKIP_COLLAB'] ?? null;
		$lastMessageDateOption = $options['LAST_MESSAGE_DATE'] ?? null;
		$withoutCommonUsers = !$viewCommonUsers || $onlyOpenlinesOption === 'Y';
		$unreadOnly = isset($options['UNREAD_ONLY']) && $options['UNREAD_ONLY'] === 'Y';
		$shortInfo = isset($options['SHORT_INFO']) && $options['SHORT_INFO'] === 'Y';
		$parseText = $options['PARSE_TEXT'] ?? null;

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

		if ($onlyCopilotOption === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => \Bitrix\Im\V2\Chat::IM_TYPE_COPILOT
			];
		}
		elseif ($onlyOpenlinesOption === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => IM_MESSAGE_OPEN_LINE
			];
		}
		elseif ($onlyChannelOption === 'Y')
		{
			$ormParams['filter'][] = [
				'=ITEM_TYPE' => [\Bitrix\Im\V2\Chat::IM_TYPE_OPEN_CHANNEL, \Bitrix\Im\V2\Chat::IM_TYPE_CHANNEL],
			];
		}
		else
		{
			$skipTypes = [];
			$skipTypes[] = \Bitrix\Im\V2\Chat::IM_TYPE_COPILOT;
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
			if ($skipCollabOption === 'Y')
			{
				$skipTypes[] = \Bitrix\Im\V2\Chat::IM_TYPE_COLLAB;
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
			$ormParams['filter']['<=DATE_LAST_ACTIVITY'] = $lastMessageDateOption;
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

		$sortOption = (new UserConfiguration((int)$userId))->getGeneralSettings()['pinnedChatSort'];
		if ($sortOption === 'byCost')
		{
			$ormParams['order'] = [
				'PINNED' => 'DESC',
				'PIN_SORT' => 'ASC',
				'DATE_LAST_ACTIVITY' => 'DESC',
			];
		}
		else
		{
			$ormParams['order'] = [
				'PINNED' => 'DESC',
				'DATE_LAST_ACTIVITY' => 'DESC',
			];
		}

		if ($canManageMessagesOption === 'Y')
		{
			$ormParams = Permission::getRoleGetListFilter($ormParams, Permission\ActionGroup::ManageMessages, 'RELATION', 'CHAT');
		}

		$orm = \Bitrix\Im\Model\RecentTable::getList($ormParams);

		$counter = 0;
		$result = [];
		$messageIdsWithCopilotRole = [];
		$copilotData = [];

		$rows = $orm->fetchAll();
		$rows = self::prepareRows($rows, $userId, $shortInfo);
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
				'PARSE_TEXT' => $parseText,
			]);
			if (!$item)
			{
				continue;
			}

			if (!$shortInfo && $onlyCopilotOption === 'Y')
			{
				$copilotChatRole = (new RoleManager())->getMainRole((int)$item['CHAT_ID']);
				if (isset($copilotChatRole))
				{
					$copilotData['chats'][$item['ID']] = $copilotChatRole;
				}
			}

			if (
				!$shortInfo
				&& (isset($item['USER']['BOT']) && $item['USER']['BOT'] === true)
				&& Loader::includeModule('imbot')
				&& (int)$item['MESSAGE']['AUTHOR_ID'] === CopilotChatBot::getBotId()
			)
			{
				$messageIdsWithCopilotRole[] = (int)$item['MESSAGE']['ID'];
			}

			if ($shortInfo && $options['JSON'])
			{
				$result[$id] = self::jsonRow($item);
			}
			else
			{
				$result[$id] = $item;
			}
		}

		if (!$shortInfo && !empty($messageIdsWithCopilotRole))
		{
			$copilotMessageRoles = self::fillCopilotMessageRoles($messageIdsWithCopilotRole);

			foreach ($result as $item)
			{
				if (in_array((int)$item['MESSAGE']['ID'], $messageIdsWithCopilotRole, true))
				{
					$copilotData['messages'][(int)$item['MESSAGE']['ID']] =
						$copilotMessageRoles[(int)$item['MESSAGE']['ID']] ?? RoleManager::getDefaultRoleCode()
					;
				}
			}
		}

		if (!$shortInfo && $onlyCopilotOption === 'Y')
		{
			$copilotData = self::prepareCopilotData($copilotData, $userId);
		}

		if ($showOpenlines && !$onlyCopilotOption && Loader::includeModule('imopenlines'))
		{
			if (!isset($options['SKIP_UNDISTRIBUTED_OPENLINES']) || $options['SKIP_UNDISTRIBUTED_OPENLINES'] !== 'Y')
			{
				$recentOpenLines = \Bitrix\ImOpenLines\Recent::getRecent($userId, ['ONLY_IN_QUEUE' => true]);

				if (is_array($recentOpenLines))
				{
					$result = array_merge($result, $recentOpenLines);
				}
			}
		}

		$result = array_values($result);

		if ($options['JSON'])
		{
			if (!$shortInfo)
			{
				foreach ($result as $index => $item)
				{
					$result[$index] = self::jsonRow($item);
				}
			}

			$objectToReturn = [
				'items' => $result,
				'hasMorePages' => $ormParams['limit'] == $counter, // TODO remove this later
				'hasMore' => $ormParams['limit'] == $counter,
				'copilot' => !empty($copilotData) ? $copilotData : null,
			];

			if (!isset($options['LAST_MESSAGE_DATE']))
			{
				$objectToReturn['birthdayList'] = \Bitrix\Im\Integration\Intranet\User::getBirthdayForToday();
			}

			return $objectToReturn;
		}

		$converter = new Converter(Converter::TO_SNAKE | Converter::TO_UPPER | Converter::KEYS);

		return [
			'ITEMS' => $result,
			'HAS_MORE_PAGES' => $ormParams['limit'] == $counter, // TODO remove this later
			'HAS_MORE' => $ormParams['limit'] == $counter,
			'COPILOT' => !empty($copilotData) ? $converter->process($copilotData) : null,
		];
	}

	private static function fillCopilotMessageRoles(array $messageIdsWithCopilotRole): array
	{
		$copilotMessageRoles = [];

		$collection = Param::getDataClass()::query()
			->setSelect(['MESSAGE_ID', 'PARAM_VALUE'])
			->whereIn('MESSAGE_ID', $messageIdsWithCopilotRole)
			->where('PARAM_NAME', \Bitrix\Im\V2\Message\Params::COPILOT_ROLE)
			->fetchCollection()
		;

		foreach ($collection as $item)
		{
			$copilotMessageRoles[(int)$item->getMessageId()] = $item->getParamValue();
		}

		return $copilotMessageRoles;
	}

	private static function prepareCopilotData(array $copilotData, int $userId): array
	{
		$roleManager = new RoleManager();
		$recentCopilotRoles = $roleManager->getRecentKeyRoles((int)$userId);
		$copilotRoles = array_merge($copilotData['chats'] ?? [], $copilotData['messages'] ?? [], $recentCopilotRoles);

		$chats = CopilotPopupItem::convertArrayData($copilotData['chats'] ?? [], CopilotPopupItem::ENTITIES['chat']);
		$messages = CopilotPopupItem::convertArrayData($copilotData['messages'] ?? [], CopilotPopupItem::ENTITIES['messageCollection']);
		$roles = $roleManager->getRoles(array_unique($copilotRoles), $userId);

		return [
			'chats' => !empty($chats) ? $chats : null,
			'messages' => !empty($messages) ? $messages : null,
			'roles' => !empty($roles) ? $roles : null,
			'recommendedRoles' => !empty($recentCopilotRoles) ? $recentCopilotRoles : null,
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
		$isIntranetInstalled = \Bitrix\Main\Loader::includeModule('intranet');
		$isIntranet = $isIntranetInstalled && \Bitrix\Intranet\Util::isIntranetUser($userId);
		$withoutCommonUsers = $params['WITHOUT_COMMON_USERS'] === true || !$isIntranet;
		$unreadOnly = isset($params['UNREAD_ONLY']) && $params['UNREAD_ONLY'] === true;
		$shortInfo = isset($params['SHORT_INFO']) && $params['SHORT_INFO'] === true;
		$chatIds = $params['CHAT_IDS'] ?? null;

		$shortInfoFields = [
			'USER_ID',
			'ITEM_TYPE',
			'ITEM_ID',
			'ITEM_MID',
			'ITEM_CID',
			'PINNED',
			'UNREAD',
			'DATE_MESSAGE',
			'DATE_LAST_ACTIVITY',
			'PIN_SORT',
			'RELATION_ID' => 'RELATION.ID',
			'RELATION_NOTIFY_BLOCK' => 'RELATION.NOTIFY_BLOCK',
			'RELATION_IS_MANAGER' => 'RELATION.MANAGER',
			'CHAT_ID' => 'CHAT.ID',
			'CHAT_TITLE' => 'CHAT.TITLE',
			'CHAT_TYPE' => 'CHAT.TYPE',
			'CHAT_AVATAR' => 'CHAT.AVATAR',
			'CHAT_AUTHOR_ID' => 'CHAT.AUTHOR_ID',
			'CHAT_COLOR' => 'CHAT.COLOR',
			'CHAT_ENTITY_TYPE' => 'CHAT.ENTITY_TYPE',
			'CHAT_CAN_POST' => 'CHAT.CAN_POST',
			'CHAT_EXTRANET' => 'CHAT.EXTRANET',
			'USER_LAST_ACTIVITY_DATE' => 'USER.LAST_ACTIVITY_DATE',
		];

		$additionalInfoFields = [
			'ITEM_OLID',
			'DATE_UPDATE',
			'MESSAGE_ID' => 'MESSAGE.ID',
			'MESSAGE_AUTHOR_ID' => 'MESSAGE.AUTHOR_ID',
			'MESSAGE_TEXT' => 'MESSAGE.MESSAGE',
			'MESSAGE_USER_LAST_ACTIVITY_DATE' => 'MESSAGE.AUTHOR.LAST_ACTIVITY_DATE',
			'USER_EMAIL' => 'USER.EMAIL',
			'MESSAGE_UUID_VALUE' => 'MESSAGE_UUID.UUID',
			'CHAT_MANAGE_USERS_ADD' => 'CHAT.MANAGE_USERS_ADD',
			'CHAT_MANAGE_USERS_DELETE' => 'CHAT.MANAGE_USERS_DELETE',
			'CHAT_MANAGE_UI' => 'CHAT.MANAGE_UI',
			'CHAT_MANAGE_SETTINGS' => 'CHAT.MANAGE_SETTINGS',
			'CHAT_LAST_MESSAGE_STATUS_BOOL' => 'MESSAGE.NOTIFY_READ',
			'RELATION_LAST_ID' => 'RELATION.LAST_ID',
			'CHAT_PARENT_ID' => 'CHAT.PARENT_ID',
			'CHAT_PARENT_MID' => 'CHAT.PARENT_MID',
			'CHAT_ENTITY_ID' => 'CHAT.ENTITY_ID',
			'CHAT_ENTITY_DATA_1' => 'CHAT.ENTITY_DATA_1',
			'CHAT_ENTITY_DATA_2' => 'CHAT.ENTITY_DATA_2',
			'CHAT_ENTITY_DATA_3' => 'CHAT.ENTITY_DATA_3',
			'CHAT_DATE_CREATE' => 'CHAT.DATE_CREATE',
			'CHAT_USER_COUNT' => 'CHAT.USER_COUNT',
		];

		$shortRuntime = [
			new \Bitrix\Main\Entity\ReferenceField(
				'USER',
				'\Bitrix\Main\UserTable',
				array("=this.ITEM_TYPE" => new \Bitrix\Main\DB\SqlExpression("?s", IM_MESSAGE_PRIVATE), "=ref.ID" => "this.ITEM_ID"),
				array("join_type"=>"LEFT")
			),
		];

		if ($shortInfo)
		{
			$shortRuntime[] = new \Bitrix\Main\Entity\ReferenceField(
				'CODE',
				'\Bitrix\Im\Model\MessageParamTable',
				[
					"=ref.MESSAGE_ID" => "this.ITEM_MID",
					"=ref.PARAM_NAME" => new \Bitrix\Main\DB\SqlExpression("?s", "CODE")
				],
				["join_type" => "LEFT"]
			);
			$shortInfoFields['MESSAGE_CODE'] = 'CODE.PARAM_VALUE';
		}

		$unreadTable = MessageUnreadTable::getTableName();

		$additionalRuntime = [
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
		if ($showOpenlines && !$shortInfo)
		{
			$runtime[] = new \Bitrix\Main\Entity\ReferenceField(
				'LINES',
				'\Bitrix\ImOpenlines\Model\SessionTable',
				[">this.ITEM_OLID" => new \Bitrix\Main\DB\SqlExpression("?i", 0), "=ref.ID" => "this.ITEM_OLID"],
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

		if ($isIntranetInstalled && !$isIntranet)
		{
			$subQuery = Group::getExtranetAccessibleUsersQuery($userId);
			if ($subQuery !== null)
			{
				$filter[] = [
					'LOGIC' => 'OR',
					['!=ITEM_TYPE' => 'P'],
					['@USER.ID' => new SqlExpression($subQuery->getQuery())],
				];
			}
		}

		if ($unreadOnly)
		{
			$filter[] = [
				'LOGIC' => 'OR',
				['==HAS_UNREAD_MESSAGE' => true],
				['=UNREAD' => true],
			];
		}

		if ($chatIds)
		{
			$filter['@ITEM_CID'] = $chatIds; // todo: add index
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

		$isUser = $row['ITEM_TYPE'] == IM_MESSAGE_PRIVATE;
		$id = $isUser? (int)$row['ITEM_ID']: 'chat'.$row['ITEM_ID'];
		$row['MESSAGE_ID'] ??= null;

		if (!$isUser && ((!$row['MESSAGE_ID'] && !$shortInfo) || !$row['RELATION_ID'] || !$row['CHAT_ID']))
		{
			return null;
		}

		$item = self::formatItem($row,$options, $shortInfo, $isUser, $id);

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
			$item['CHAT'] = self::formatChat($row, $shortInfo, $generalChatId);

			if (!$shortInfo)
			{
				$item['AVATAR'] = [
					'URL' => $item['CHAT']['AVATAR'],
					'COLOR' => $item['CHAT']['COLOR'],
				];
				$item['TITLE'] = $row['CHAT_TITLE'];
			}

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
			$user = self::formatUser($row, $item, $shortInfo);

			if ($user === null)
			{
				return null;
			}

			$item['USER'] = $user;

			if (!$shortInfo && $item['TYPE'] == 'user')
			{
				$item['AVATAR'] = [
					'URL' => $user['AVATAR'],
					'COLOR' => $user['COLOR']
				];
				$item['TITLE'] = $user['NAME'];
			}
		}

		$item['OPTIONS'] = [];
		if ($row['USER_ID'] == 0 || $row['MESSAGE_CODE'] === 'USER_JOIN')
		{
			$item['OPTIONS']['DEFAULT_USER_RECORD'] = true;
		}

		return $item;
	}

	private static function formatMessage(array $row, array $options, bool $shortInfo): array
	{
		$row['DATE_MESSAGE'] ??= null;

		if ($shortInfo)
		{
			return [
				'ID' => (int)($row['ITEM_MID'] ?? 0),
				'DATE' => $row['DATE_MESSAGE'] ?: $row['DATE_LAST_ACTIVITY'],
			];
		}

		if (!$row['ITEM_MID'] || !$row['MESSAGE_ID'])
		{
			return [
				'ID' => (int)($row['ITEM_MID'] ?? 0),
				'TEXT' => "",
				'FILE' => false,
				'AUTHOR_ID' =>  0,
				'ATTACH' => false,
				'DATE' => $row['DATE_MESSAGE']?: $row['DATE_UPDATE'],
				'STATUS' => $row['CHAT_LAST_MESSAGE_STATUS'],
			];
		}

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
		$parseText = $options['PARSE_TEXT'] ?? null;
		if ($parseText === 'Y')
		{
			$text = Text::parse($text);
		}
		elseif ($getOriginalTextOption === 'Y')
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

		return [
			'ID' => (int)$row['ITEM_MID'],
			'TEXT' => $text,
			'FILE' => $row['MESSAGE_FILE'],
			'AUTHOR_ID' =>  (int)$row['MESSAGE_AUTHOR_ID'],
			'ATTACH' => $attach,
			'DATE' => $row['DATE_MESSAGE']?: $row['DATE_UPDATE'],
			'STATUS' => $row['CHAT_LAST_MESSAGE_STATUS'],
			'UUID' => $row['MESSAGE_UUID_VALUE'],
		];
	}

	private static function formatItem(
		array $row,
		array $options,
		bool $shortInfo,
		bool $isUser,
		mixed $id
	): array
	{
		$message = self::formatMessage($row, $options, $shortInfo);

		if ($shortInfo)
		{
			return [
				'ID' => $id,
				'CHAT_ID' => (int)$row['CHAT_ID'],
				'TYPE' => $isUser ? 'user' : 'chat',
				'MESSAGE' => $message,
				'COUNTER' => (int)$row['COUNTER'],
				'PINNED' => $row['PINNED'] === 'Y',
				'UNREAD' => $row['UNREAD'] === 'Y',
				'DATE_LAST_ACTIVITY' => $row['DATE_LAST_ACTIVITY'],
			];
		}

		return [
			'ID' => $id,
			'CHAT_ID' => (int)$row['CHAT_ID'],
			'TYPE' => $isUser ? 'user' : 'chat',
			'AVATAR' => [],
			'TITLE' => [],
			'MESSAGE' => $message,
			'COUNTER' => (int)$row['COUNTER'],
			'LAST_ID' => (int)($row['RELATION_LAST_ID'] ?? 0),
			'PINNED' => $row['PINNED'] === 'Y',
			'UNREAD' => $row['UNREAD'] === 'Y',
			'HAS_REMINDER' => isset($row['HAS_REMINDER']) && $row['HAS_REMINDER'] === 'Y',
			'DATE_UPDATE' => $row['DATE_UPDATE'],
			'DATE_LAST_ACTIVITY' => $row['DATE_LAST_ACTIVITY'],
		];
	}

	private static function formatChat(array $row, bool $shortInfo, int $generalChatId): array
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

		if ($shortInfo)
		{
			return [
				'ID' => (int)$row['ITEM_CID'],
				'NAME' => $row['CHAT_TITLE'],
				'EXTRANET' => $row['CHAT_EXTRANET'] == 'Y',
				'AVATAR' => $avatar,
				'COLOR' => $color,
				'TYPE' => $chatType,
				'ENTITY_TYPE' => (string)$row['CHAT_ENTITY_TYPE'],
				'MUTE_LIST' => $muteList,
				'ROLE' => self::getRole($row),
				'PERMISSIONS' => [
					'MANAGE_MESSAGES' => mb_strtolower($row['CHAT_CAN_POST'] ?? ''),
				],
			];
		}

		$managerList = [];
		if ($row['CHAT_OWNER'] ?? null == $row['RELATION_USER_ID'] || $row['RELATION_IS_MANAGER'] == 'Y')
		{
			$managerList = [(int)$row['RELATION_USER_ID']];
		}

		$chatOptions = \CIMChat::GetChatOptions();
		$restrictions = $chatOptions['DEFAULT'];
		if ($row['CHAT_ENTITY_TYPE'] && array_key_exists($row['CHAT_ENTITY_TYPE'], $chatOptions))
		{
			$restrictions = $chatOptions[$row['CHAT_ENTITY_TYPE']];
		}

		return [
			'ID' => (int)$row['ITEM_CID'],
			'PARENT_CHAT_ID' => (int)$row['CHAT_PARENT_ID'],
			'PARENT_MESSAGE_ID' => (int)$row['CHAT_PARENT_MID'],
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
			'RESTRICTIONS' => $restrictions,
			'ROLE' => self::getRole($row),
			'ENTITY_LINK' => EntityLink::getInstance(\CIMChat::initChatByArray($row))->toArray(),
			'PERMISSIONS' => [
				'MANAGE_USERS_ADD' => mb_strtolower($row['CHAT_MANAGE_USERS_ADD'] ?? ''),
				'MANAGE_USERS_DELETE' => mb_strtolower($row['CHAT_MANAGE_USERS_DELETE'] ?? ''),
				'MANAGE_UI' => mb_strtolower($row['CHAT_MANAGE_UI'] ?? ''),
				'MANAGE_SETTINGS' => mb_strtolower($row['CHAT_MANAGE_SETTINGS'] ?? ''),
				'MANAGE_MESSAGES' => mb_strtolower($row['CHAT_CAN_POST'] ?? ''),
				'CAN_POST' => mb_strtolower($row['CHAT_CAN_POST'] ?? ''),
			],
		];
	}

	private static function formatUser(array $row, array $item, bool $shortInfo): ?array
	{
		$userObject = \Bitrix\Im\V2\Entity\User\User::getInstance($item['USER']['ID']);
		$user = $userObject->getArray(['WITHOUT_ONLINE' => true, 'USER_SHORT_FORMAT' => $shortInfo]);

		if ($shortInfo)
		{
			if (!$userObject->isActive())
			{
				return null;
			}

			return $user;
		}

		if ($item['TYPE'] == 'user')
		{
			if (
				!empty($user['BOT_DATA'])
				&& Loader::includeModule('imbot')
				&& $user['BOT_DATA']['code'] === CopilotChatBot::BOT_CODE
			)
			{
				return null;
			}

			if (
				(!$user['ACTIVE'] && $item['COUNTER'] <= 0)
				&& !$user['BOT']
				&& !$user['CONNECTOR']
				&& !$user['NETWORK']
			)
			{
				return null;
			}
		}

		if ($item['TYPE'] == 'user')
		{
			$lastActivityDate = $row['USER_LAST_ACTIVITY_DATE'] ?? null;
		}
		else
		{
			$lastActivityDate = $row['MESSAGE_USER_LAST_ACTIVITY_DATE'] ?? null;
		}

		$user['LAST_ACTIVITY_DATE'] = $lastActivityDate ?: false;
		$user['DESKTOP_LAST_DATE'] = false;
		$user['MOBILE_LAST_DATE'] = false;
		$user['IDLE'] = false;

		return $user;
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

		$pinnedCount = \Bitrix\Im\Model\RecentTable::getCount(['=USER_ID' => $userId, '=PINNED' => 'Y']);

		self::$limitError = false;
		if ($pin && (int)$pinnedCount >= self::PINNED_CHATS_LIMIT)
		{
			self::$limitError = true;

			return false;
		}

		$pin = $pin === true? 'Y': 'N';

		$id = $dialogId;
		$chatId = 0;
		if (mb_substr($dialogId, 0, 4) == 'chat')
		{
			$itemTypes = \Bitrix\Im\Chat::getTypes();
			$id = mb_substr($dialogId, 4);
			$chatId = (int)$id;
		}
		else
		{
			$itemTypes = IM_MESSAGE_PRIVATE;
			$chatId = \Bitrix\Im\Dialog::getChatId($dialogId);
		}

		$element = \Bitrix\Im\Model\RecentTable::getList(
			[
				'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'PINNED', 'PIN_SORT'],
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

			$relationData = \Bitrix\Im\Model\RelationTable::getList(
				[
					'select' => ['ID', 'LAST_MESSAGE_ID' => 'CHAT.LAST_MESSAGE_ID'],
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

		$connection = Application::getConnection();
		$connection->lock("PIN_SORT_CHAT_{$userId}", 10);

		if ($pin === 'Y')
		{
			self::increasePinSortCost($userId);
		}
		else
		{
			$pinSort = $element['PIN_SORT'] ? (int)$element['PIN_SORT'] : null;
			self::decreasePinSortCost($userId, $pinSort);
		}


		\Bitrix\Im\Model\RecentTable::update(
			[
				'USER_ID' => $element['USER_ID'],
				'ITEM_TYPE' => $element['ITEM_TYPE'],
				'ITEM_ID' => $element['ITEM_ID'],
			],
			[
				'PINNED' => $pin,
				'DATE_UPDATE' => new \Bitrix\Main\Type\DateTime(),
				'PIN_SORT' => ($pin === 'Y') ? 1 : null,
			]
		);

		$connection->unlock("PIN_SORT_CHAT_{$userId}");

		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chatId),
			$userId,
			$element['ITEM_TYPE']
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

	private static function increasePinSortCost(int $userId): void
	{
		$caseField = new SqlExpression('?# + 1', 'PIN_SORT');

		RecentTable::updateByFilter(
			[
				'=PINNED' => 'Y',
				'=USER_ID' => $userId,
				'>=PIN_SORT' => 1,
			],
			['PIN_SORT' => $caseField]
		);
	}

	private static function decreasePinSortCost(int $userId, ?int $pinSort)
	{
		if (!isset($pinSort))
		{
			return;
		}

		$caseField = new SqlExpression('?# - 1', 'PIN_SORT');

		RecentTable::updateByFilter(
			[
				'=PINNED' => 'Y',
				'=USER_ID' => $userId,
				'>PIN_SORT' => $pinSort,
			],
			['PIN_SORT' => $caseField]
		);
	}

	public static function sortPin(\Bitrix\Im\V2\Chat $chat, int $newPosition, int $userId): void
	{
		$connection = Application::getConnection();
		$connection->lock("PIN_SORT_CHAT_{$userId}", 10);

		$query = RecentTable::query()
			->setSelect(['PIN_SORT'])
			->setLimit(1)
			->where('USER_ID', $userId)
			->where('ITEM_CID', (int)$chat->getChatId())
			->where('PINNED', 'Y')
			->fetch()
		;

		if (!$query)
		{
			$connection->unlock("PIN_SORT_CHAT_{$userId}");

			return;
		}
		$currentCost = (int)$query['PIN_SORT'];

		$query = RecentTable::query()
			->setSelect(['PIN_SORT'])
			->setOrder(['PIN_SORT'])
			->setOffset($newPosition - 1)
			->setLimit(1)
			->where('PINNED', 'Y')
			->where('USER_ID', $userId)
			->fetch()
		;

		if (!$query)
		{
			$connection->unlock("PIN_SORT_CHAT_{$userId}");

			return;
		}
		$newCost = (int)$query['PIN_SORT'];

		if ($currentCost === $newCost)
		{
			$connection->unlock("PIN_SORT_CHAT_{$userId}");

			return;
		}

		if ($currentCost < $newCost)
		{
			$caseField = new SqlExpression(
				"CASE WHEN ?# = ?i THEN ?i WHEN ?# > ?i AND ?# <= ?i THEN ?# - 1 END",
				'PIN_SORT',
				$currentCost,
				$newCost,
				'PIN_SORT',
				$currentCost,
				'PIN_SORT',
				$newCost,
				'PIN_SORT'
			);

			$filter = [
				'=PINNED' => 'Y',
				'=USER_ID' => $userId,
				'>=PIN_SORT' => $currentCost,
				'<=PIN_SORT' => $newCost,
			];
		}
		else
		{
			$caseField = new SqlExpression(
				"CASE WHEN ?# = ?i THEN ?i WHEN ?# >= ?i AND ?# < ?i THEN ?# + 1 END",
				'PIN_SORT',
				$currentCost,
				$newCost,
				'PIN_SORT',
				$newCost,
				'PIN_SORT',
				$currentCost,
				'PIN_SORT'
			);

			$filter = [
				'=PINNED' => 'Y',
				'=USER_ID' => $userId,
				'>=PIN_SORT' => $newCost,
				'<=PIN_SORT' => $currentCost,
			];
		}

		RecentTable::updateByFilter(
			$filter,
			['PIN_SORT' => $caseField]
		);

		$connection->unlock("PIN_SORT_CHAT_{$userId}");
	}

	public static function getPinLimit(): int
	{
		return self::PINNED_CHATS_LIMIT ?? 25;
	}

	public static function updatePinSortCost(int $userId): void
	{
		$connection = Application::getConnection();
		$connection->lock("PIN_SORT_CHAT_{$userId}", 10);

		$caseField = new SqlExpression('?#', 'ITEM_MID');

		RecentTable::updateByFilter(
			[
				'=PINNED' => 'Y',
				'=USER_ID' => $userId
			],
			['PIN_SORT' => $caseField]
		);

		$connection->unlock("PIN_SORT_CHAT_{$userId}");
	}

	public static function updateByFilter(array $filter, array $fields): void
	{
		RecentTable::updateByFilter($filter, $fields);
	}

	public static function raiseChat(\Bitrix\Im\V2\Chat $chat, RelationCollection $relations, ?DateTime $lastActivity = null): void
	{
		$userIds = $relations->getUserIds();
		if (empty($userIds))
		{
			return;
		}
		$message = new Message($chat->getLastMessageId());
		$dateMessage = $message->getDateCreate() ?? new DateTime();
		$dateCreate = $lastActivity ?? $dateMessage;
		$fields = [];

		foreach ($relations as $relation)
		{
			$userId = $relation->getUserId();
			if ($userId)
			{
				$fields[] = [
					'USER_ID' => $userId,
					'ITEM_TYPE' => $chat->getType(),
					'ITEM_ID' => $chat->getId(),
					'ITEM_MID' => $chat->getLastMessageId(),
					'ITEM_CID' => $chat->getId(),
					'ITEM_RID' => $relation->getId(),
					'DATE_MESSAGE' => $dateMessage,
					'DATE_UPDATE' => $dateCreate,
					'DATE_LAST_ACTIVITY' => $dateCreate,
				];
			}
		}

		static::merge($fields, ['DATE_LAST_ACTIVITY' => $dateCreate, 'DATE_UPDATE' => $dateCreate]);
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chat->getId()),
			$userIds,
			$chat->getType()
		);

		static::sendPullRecentUpdate($chat, $userIds, $dateCreate);
	}

	public static function sendPullRecentUpdate(\Bitrix\Im\V2\Chat $chat, array $userIds, ?DateTime $lastCommentDate): void
	{
		$messages = new MessagePopupItem([$chat->getLastMessageId()], true);
		$restAdapter = new RestAdapter($messages);
		$pull = $restAdapter->toRestFormat([
			'WITHOUT_OWN_REACTIONS' => true,
			'MESSAGE_ONLY_COMMON_FIELDS' => true,
		]);
		$pull['chat'] = $chat->toPullFormat();
		$pull['lastActivityDate'] = $lastCommentDate;
		$pull['counterType'] = $chat->getCounterType()->value;

		$event = [
			'module_id' => 'im',
			'command' => 'recentUpdate',
			'params' => $pull,
			'extra' => Common::getPullExtra()
		];
		$events = PushService::getEventGroups($event, $userIds, $chat->getId());

		foreach ($events as $event)
		{
			Event::add($event['users'], $event['event']);
		}
	}

	public static function merge(array $fields, array $update): void
	{
		RecentTable::multiplyMerge($fields, $update, ['USER_ID', 'ITEM_TYPE', 'ITEM_ID']);
	}

	public static function getUsersOutOfRecent(\Bitrix\Im\V2\Chat $chat): array
	{
		$relations = $chat->getRelations()->filterActive();
		$users = $relations->getUserIds();
		$usersAlreadyInRecentRows = RecentTable::query()
			->setSelect(['USER_ID'])
			->where('ITEM_CID', $chat->getId())
			->whereIn('USER_ID', $users)
			->fetchAll()
		;
		foreach ($usersAlreadyInRecentRows as $row)
		{
			$userId = (int)$row['USER_ID'];
			unset($users[$userId]);
		}

		return $users;
	}

	public static function unread($dialogId, $unread, $userId = null, ?int $markedId = null, ?string $itemTypes = null)
	{
		$userId = \Bitrix\Im\Common::getUserId($userId);
		if (!$userId)
		{
			return false;
		}

		$unread = $unread === true? 'Y': 'N';

		$element = self::getUnreadElement($userId, $itemTypes, $dialogId);

		if (!$element)
		{
			return false;
		}
		if ($element['UNREAD'] === $unread && !isset($markedId))
		{
			return true;
		}

		self::$unreadElementCache[$userId][$dialogId] = null;

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
		$chatId = (int)$element['ITEM_CID'];
		Sync\Logger::getInstance()->add(
			new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chatId),
			$userId,
			$element['ITEM_TYPE']
		);

		$pullInclude = \Bitrix\Main\Loader::includeModule("pull");
		if ($pullInclude)
		{
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
						'counterType' => CounterType::tryFromType($element['ITEM_TYPE'])->value,
					],
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
		}

		return true;
	}

	private static function getUnreadElement(int $userId, ?string $itemTypes, $dialogId): array|false
	{
		if (self::$unreadElementCache[$userId][$dialogId] !== null)
		{
			return self::$unreadElementCache[$userId][$dialogId];
		}

		$id = $dialogId;
		if (mb_substr($dialogId, 0, 4) === 'chat')
		{
			if ($itemTypes === null)
			{
				$itemTypes = \Bitrix\Im\Chat::getTypes();
			}

			$id = mb_substr($dialogId, 4);
		}
		else
		{
			$itemTypes = IM_MESSAGE_PRIVATE;
		}

		self::$unreadElementCache[$userId][$dialogId] = \Bitrix\Im\Model\RecentTable::getList([
			'select' => ['USER_ID', 'ITEM_TYPE', 'ITEM_ID', 'UNREAD', 'MUTED' => 'RELATION.NOTIFY_BLOCK', 'ITEM_CID', 'MARKED_ID'],
			'filter' => [
				'=USER_ID' => $userId,
				'=ITEM_TYPE' => $itemTypes,
				'=ITEM_ID' => $id
			]
		])->fetch();

		return self::$unreadElementCache[$userId][$dialogId];
	}

	public static function readAll(int $userId): void
	{
		\Bitrix\Im\Model\RecentTable::updateByFilter(
			[
				'=UNREAD' => 'Y',
				'=USER_ID' => $userId,
			],
			[
				'UNREAD' => 'N',
				'MARKED_ID' => 0,
			]
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

	public static function getUnread(string $itemType, string $dialogId): array
	{
		$id = mb_strpos($dialogId, 'chat') === 0 ? mb_substr($dialogId, 4) : $dialogId;
		$queryResult = \Bitrix\Im\Model\RecentTable::getList([
			'select' => ['USER_ID', 'UNREAD',],
			'filter' => [
				'=ITEM_TYPE' => $itemType,
				'=ITEM_ID' => $id
			]
		])->fetchAll();

		$result = [];

		foreach ($queryResult as $row)
		{
			$result[(int)$row['USER_ID']] = ($row['UNREAD'] ?? 'N') === 'Y';
		}

		return $result;
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

	public static function getMarkedIdByChatIds(int $userId, array $chatIds): array
	{
		if (empty($chatIds))
		{
			return [];
		}

		$markedIdByChatIds = [];

		$result = RecentTable::query()
			->setSelect(['ITEM_CID', 'MARKED_ID'])
			->where('USER_ID', $userId)
			->whereIn('ITEM_CID', $chatIds)
			->fetchAll()
		;

		foreach ($result as $row)
		{
			$markedIdByChatIds[(int)$row['ITEM_CID']] = (int)$row['MARKED_ID'];
		}

		return $markedIdByChatIds;
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
			if (
				!isset($data['message'])
				&& $entityType === Chat::TYPE_OPEN_LINE
				&& class_exists('\Bitrix\ImOpenLines\Recent')
			)
			{
				$data = \Bitrix\ImOpenLines\Recent::getElement(
					(int)$entityId,
					(int)$userId,
					[
						'JSON' => true,
						'fakeCounter' => 1
					]
				);
			}
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

	protected static function prepareRows(array $rows, int $userId, bool $shortInfo = false): array
	{
		[$messageIds, $chatIds] = self::getKeysForFetchAdditionalEntities($rows);
		$counters = (new CounterService($userId))->getForEachChat($chatIds);
		$params = $shortInfo ? [] : self::getMessageParams($messageIds);

		return self::fillRows($rows, $params, $counters, $userId);
	}

	protected static function getKeysForFetchAdditionalEntities(array $rows): array
	{
		$messageIds = [];
		$chatIds = [];

		foreach ($rows as $row)
		{
			if (isset($row['ITEM_MID']) && $row['ITEM_MID'] > 0)
			{
				$messageIds[] = (int)$row['ITEM_MID'];
			}

			if (isset($row['ITEM_CID']) && $row['ITEM_CID'] > 0)
			{
				$chatIds[] = (int)$row['ITEM_CID'];
			}
		}

		return [$messageIds, $chatIds];
	}

	protected static function getMessageParams(array $messageIds): array
	{
		$result = [];
		$fileIds = [];

		if (empty($messageIds))
		{
			return $result;
		}

		$rows = MessageParamTable::query()
			->setSelect(['*'])
			->whereIn('MESSAGE_ID', $messageIds)
			->exec()
		;

		foreach ($rows as $item)
		{
			$messageId = (int)$item['MESSAGE_ID'];
			$paramName = $item['PARAM_NAME'];

			if ($paramName === 'CODE')
			{
				$result[$messageId]['CODE'] = $item['PARAM_VALUE'];
			}
			elseif ($paramName === 'ATTACH')
			{
				$result[$messageId]['ATTACH'] = [
					'VALUE' => $item['PARAM_VALUE'],
					'JSON' => $item['PARAM_JSON'],
				];
			}
			elseif ($paramName === 'URL_ID')
			{
				$result[$messageId]['ATTACH'] = [
					'VALUE' => "",
					'JSON' => true,
				];
			}
			elseif ($paramName === 'FILE_ID')
			{
				$fileIds[$messageId] = (int)$item['PARAM_VALUE'];
				$result[$messageId]['MESSAGE_FILE'] = true;
			}
		}

		return self::fillFiles($result, $fileIds);
	}

	protected static function fillFiles(array $params, array $fileIds): array
	{
		if (empty($fileIds))
		{
			return $params;
		}

		if (Settings::isLegacyChatActivated())
		{
			return $params;
		}

		$files = FileCollection::initByDiskFilesIds($fileIds);

		foreach ($fileIds as $messageId => $fileId)
		{
			$file = $files->getById($fileId);
			if (!$file instanceof FileItem)
			{
				$params[$messageId]['MESSAGE_FILE'] = false;
			}
			else
			{
				$params[$messageId]['MESSAGE_FILE'] = [
					'ID' => $file->getId(),
					'TYPE' => $file->getContentType(),
					'NAME' => $file->getDiskFile()->getName(),
				];
			}
		}

		return $params;
	}

	protected static function fillRows(array $rows, array $params, array $counters, int $userId): array
	{
		foreach ($rows as $key => $row)
		{
			$chatId = (int)($row['ITEM_CID'] ?? 0);
			$messageId = (int)($row['ITEM_MID'] ?? 0);
			$boolStatus = $row['CHAT_LAST_MESSAGE_STATUS_BOOL'] ?? 'N';

			$rows[$key]['COUNTER'] = $counters[$chatId] ?? 0;
			$rows[$key]['CHAT_LAST_MESSAGE_STATUS'] = $boolStatus === 'Y' ? \IM_MESSAGE_STATUS_DELIVERED : \IM_MESSAGE_STATUS_RECEIVED;
			$rows[$key]['MESSAGE_CODE'] = $rows[$key]['MESSAGE_CODE'] ?? $params[$messageId]['CODE'] ?? null;
			$rows[$key]['MESSAGE_ATTACH'] = $params[$messageId]['ATTACH']['VALUE'] ?? null;
			$rows[$key]['MESSAGE_ATTACH_JSON'] = $params[$messageId]['ATTACH']['JSON'] ?? null;
			$rows[$key]['MESSAGE_FILE'] = $params[$messageId]['MESSAGE_FILE'] ?? false;
			$rows[$key]['RELATION_USER_ID'] = $row['RELATION_ID'] ? $userId : null;
		}

		return $rows;
	}

	/**
	 * @see \Bitrix\Im\V2\Chat::getRole()
	 * @param array $row
	 * @return string
	 */
	protected static function getRole(array $row): string
	{
		if (!isset($row['RELATION_USER_ID']))
		{
			return \Bitrix\Im\V2\Chat::ROLE_GUEST;
		}
		if ((int)$row['CHAT_AUTHOR_ID'] === (int)$row['RELATION_USER_ID'])
		{
			return \Bitrix\Im\V2\Chat::ROLE_OWNER;
		}
		if ($row['RELATION_IS_MANAGER'] === 'Y')
		{
			return \Bitrix\Im\V2\Chat::ROLE_MANAGER;
		}

		return \Bitrix\Im\V2\Chat::ROLE_MEMBER;
	}

	public static function isLimitError(): bool
	{
		return self::$limitError;
	}
}
