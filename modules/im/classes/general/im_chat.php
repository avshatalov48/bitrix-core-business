<?php
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Entity\User\UserError;
use Bitrix\Imopenlines\Model\SessionTable;
use Bitrix\Im\V2\Sync;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;

class CIMChat
{
	const CHAT_ALL = 'all';
	const GENERAL_MESSAGE_TYPE_JOIN = IM\V2\Chat\GeneralChat::GENERAL_MESSAGE_TYPE_JOIN;
	const GENERAL_MESSAGE_TYPE_LEAVE = IM\V2\Chat\GeneralChat::GENERAL_MESSAGE_TYPE_LEAVE;

	private $user_id = 0;
	private $bHideLink = false;
	public $lastAvatarId = 0;
	private static $entityOption = null;

	function __construct($user_id = null, $arParams = Array())
	{
		if (is_null($user_id))
		{
			global $USER;
			if (is_object($USER))
			{
				$this->user_id = intval($USER->GetID());
			}
		}
		else
		{
			$this->user_id = intval($user_id);
		}

		if (isset($arParams['HIDE_LINK']) && $arParams['HIDE_LINK'] == 'Y')
		{
			$this->bHideLink = true;
		}
	}

	public function GetMessage($ID)
	{
		global $DB;

		$strSql = "
			SELECT
				M.*, C.TYPE as CHAT_TYPE, R.USER_ID as RID
			FROM
				b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
				LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$this->user_id."
			WHERE
				M.ID = ".intval($ID)."
		";

		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
			if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				if (intval($arRes['RID']) <= 0 && IM\User::getInstance($this->user_id)->isExtranet())
				{
					return false;
				}
			}
			else if (intval($arRes['RID']) <= 0)
			{
				return false;
			}
			unset($arRes['CHAT_TYPE']);
			unset($arRes['RID']);

			return $arRes;
		}

		return false;
	}

	/**
	 * @param $toChatId
	 * @param bool $fromUserId
	 * @param bool $loadExtraData
	 * @param bool $bTimeZone
	 * @param bool $limit
	 * @return array|bool
	 */
	function GetLastMessage($toChatId, $fromUserId = false, $loadExtraData = false, $bTimeZone = true, $limit = true)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			$fromUserId = $this->user_id;

		$toChatId = intval($toChatId);
		if ($toChatId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_CHAT_ID"), "ERROR_TO_CHAT_ID");
			return false;
		}

		$orm = IM\Model\ChatTable::getById($toChatId);
		if (!($chatData = $orm->fetch()))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_CHAT_NOT_EXISTS"), "ERROR_CHAT_NOT_EXISTS");
			return false;
		}

		if ($chatData['TYPE'] == IM_MESSAGE_OPEN && !CIMMessenger::CheckEnableOpenChat())
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_CHAT_NOT_EXISTS"), "ERROR_CHAT_NOT_EXISTS");
			return false;
		}

		$sqlLimit = '';
		if ($limit)
		{
			if ($DB->type == "MSSQL")
			{
				$sqlLimit = " AND M.DATE_CREATE > dateadd(day, -30, getdate())";
			}
			elseif ($DB->type == "ORACLE")
			{
				$sqlLimit = " AND M.DATE_CREATE > SYSDATE-30";
			}
			else
			{
				//$sqlLimit = " AND M.DATE_CREATE > DATE_SUB(NOW(), INTERVAL 30 DAY)";
				$connection = \Bitrix\Main\Application::getInstance()->getConnection();
				$helper = $connection->getSqlHelper();
				$sqlLimit = " AND M.DATE_CREATE > ".$helper->addDaysToDateTime(-30);
			}
		}

		$readService = new IM\V2\Message\ReadService($fromUserId);
		$lastMessageIdInChat = $readService->getLastMessageIdInChat($toChatId);
		$limitById = '';
		$limitFetchMessages = 30;
		$relations = \CIMChat::GetRelationById($toChatId, false, $bTimeZone, false);
		if (isset($relations[$fromUserId]))
		{
			if ($relations[$fromUserId]['START_ID'] > 0)
			{
				$limitById = 'AND M.ID >= '.intval($relations[$fromUserId]['START_ID']);
			}

			//if ($relations[$fromUserId]['STATUS'] != IM_STATUS_READ && $relations[$fromUserId]['COUNTER'] > $limitFetchMessages)
			$messageCountFilter = \Bitrix\Main\ORM\Query\Query::filter()
				->where('ID', '>=', (int)$relations[$fromUserId]['START_ID'])
				->where('ID', '>=', (int)$relations[$fromUserId]['LAST_ID'])
				->where('ID', '<=', $lastMessageIdInChat)
				->where('CHAT_ID', $toChatId)
			;
			$messageCount = \Bitrix\Im\Model\MessageTable::getCount($messageCountFilter);
			$limitFetchMessages = max($messageCount, 30);
		}

		if (!$bTimeZone)
			CTimeZone::Disable();

		$lineId = 0;
		$crmEntityType = null;
		$crmEntityId = null;
		if (
			$chatData['TYPE'] == IM_MESSAGE_OPEN_LINE
			&& \Bitrix\Main\Loader::includeModule('imopenlines')
		)
		{
			$explodeData = explode('|', $chatData['ENTITY_DATA_1']);
			$crmEntityType = ($explodeData[0] == 'Y') ? $explodeData[1] : null;
			$crmEntityId = ($explodeData[0] == 'Y') ? intval($explodeData[2]) : null;

			$lineId = \Bitrix\ImOpenLines\Chat::parseLinesChatEntityId($chatData['ENTITY_ID'])['lineId'];
		}

		$strSql = "";
		if ($chatData['TYPE'] == IM_MESSAGE_OPEN)
		{
			$strSql = "
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
					M.AUTHOR_ID,
					C.TYPE as CHAT_TYPE,
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
					R.USER_ID as RID
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.TYPE = '".IM_MESSAGE_OPEN."'
				LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$fromUserId."
				WHERE
					M.CHAT_ID = ".$toChatId."
					".$limitById."
					#LIMIT#
				ORDER BY M.DATE_CREATE DESC
			";
		}
		else if (
			$chatData['TYPE'] == IM_MESSAGE_OPEN_LINE
			&& \Bitrix\Main\Loader::includeModule('imopenlines')
			&& \Bitrix\ImOpenLines\Config::canJoin($lineId, $crmEntityType, $crmEntityId)
		)
		{
			$strSql = "
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
					M.AUTHOR_ID,
					C.TYPE as CHAT_TYPE,
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
					R.USER_ID as RID
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.TYPE = '".IM_MESSAGE_OPEN_LINE."'
				LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$fromUserId."
				WHERE
					M.CHAT_ID = ".$toChatId."
					".$limitById."
					#LIMIT#
				ORDER BY M.DATE_CREATE DESC
			";

			\Bitrix\Im\Disk\NoRelationPermission::add($toChatId, $fromUserId);
		}
		else if (isset($relations[$fromUserId]))
		{
			$strSql = "
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
					M.AUTHOR_ID,
					C.TYPE as CHAT_TYPE,
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
					'".$fromUserId."' as RID
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
				WHERE
					M.CHAT_ID = ".$toChatId."
					".$limitById."
					#LIMIT#
				ORDER BY M.DATE_CREATE DESC
			";
		}
		else
		{
			if (
				$chatData['TYPE'] == IM_MESSAGE_OPEN_LINE
				&& \Bitrix\Main\Loader::includeModule('imopenlines')
				&& \Bitrix\ImOpenLines\Model\RecentTable::getCount([
					'=CHAT_ID' => $toChatId,
					'=USER_ID' => $fromUserId,
				])
			)
			{
				$strSql = "
					SELECT
						M.ID,
						M.CHAT_ID,
						M.MESSAGE,
						".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
						M.AUTHOR_ID,
						C.TYPE as CHAT_TYPE,
						C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
						'".$fromUserId."' as RID
					FROM b_im_message M
					INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
					WHERE
						M.CHAT_ID = ".$toChatId."
						".$limitById."
						#LIMIT#
					ORDER BY M.DATE_CREATE DESC
				";
			}
		}

		if (!$bTimeZone)
			CTimeZone::Enable();

		$chatType = $chatData['TYPE'];
		$chatRelationUserId = 0;

		$arUsers = Array();
		$arMessages = Array();
		$arMessageId = Array();
		$arUsersMessage = Array();
		$arUnreadMessages = Array();
		$readedList = Array();

		if ($strSql)
		{
			$strSql = $DB->TopSql($strSql, $limitFetchMessages);
			//LEFT JOIN b_im_message_param MP on MP.MESSAGE_ID = M.ID and MP.PARAM_NAME = 'FOR_USER_ID'
			//and (MP.PARAM_VALUE is null or MP.PARAM_VALUE = '".$fromUserId."')

			if ($limit)
			{
				$dbRes = $DB->Query(str_replace("#LIMIT#", $sqlLimit, $strSql));
				if (!$dbRes->SelectedRowsCount())
					$dbRes = $DB->Query(str_replace("#LIMIT#", "", $strSql));
			}
			else
			{
				$dbRes = $DB->Query(str_replace("#LIMIT#", "", $strSql));
			}

			$lastReads = $readService
				->getViewedService()
				->getDateViewedByMessageIdForEachUser($lastMessageIdInChat, array_keys($relations))
			;
			while ($arRes = $dbRes->Fetch())
			{
				if ($arRes['CHAT_ENTITY_TYPE'] != 'LIVECHAT' && \Bitrix\Im\User::getInstance($fromUserId)->isConnector())
				{
					return false;
				}
				$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

				$chatType = $arRes['CHAT_TYPE'];
				$chatRelationUserId = intval($arRes['RID']);

				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['AUTHOR_ID'],
					'recipientId' => $arRes['CHAT_ID'],
					'date' => DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
					'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE']),
				);

				$arMessageId[] = $arRes['ID'];
				if ($arRes['AUTHOR_ID'] > 0)
				{
					$arUsers[] = $arRes['AUTHOR_ID'];
				}
				if (isset($relations[$fromUserId]) && $relations[$fromUserId]['LAST_ID'] < $arRes['ID'])
				{
					$arUnreadMessages['chat'.$arRes['CHAT_ID']][] = $arRes['ID'];
				}
				$arUsersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];

				foreach ($relations as $userId => $relation)
				{
					$readedList['chat'.$arRes['CHAT_ID']][$relation['USER_ID']] = Array(
						'messageId' => $relation['LAST_ID'],
						'date' => $lastReads[$userId] ?? null,
					);
				}
			}

			foreach ($arUsersMessage as $chatId => $messageIds)
			{
				$arUsersMessage[$chatId] = array_values(array_unique($messageIds));
			}
		}

		if (
			$chatType == IM_MESSAGE_OPEN && $chatRelationUserId <= 0
			|| $chatType == IM_MESSAGE_OPEN_LINE && $chatRelationUserId <= 0
		)
		{
			if (IM\User::getInstance($fromUserId)->isExtranet())
			{
				$arMessages = Array();
				$arMessageId = Array();
				$arUsersMessage = Array();
				$loadExtraData = false;
			}
			else if (
				CModule::IncludeModule('pull')
				&& \Bitrix\Im\V2\Chat::getInstance($toChatId)->checkAccess($fromUserId)->isSuccess()
			)
			{
				CPullWatch::Add($fromUserId, 'IM_PUBLIC_'.$toChatId, true);
			}
		}

		$params = CIMMessageParam::Get($arMessageId);

		$arFiles = Array();
		foreach ($params as $messageId => $param)
		{
			$arMessages[$messageId]['params'] = $param;

			if (
				mb_strlen($arMessages[$messageId]['text']) <= 0
				&& !isset($param['FILE_ID'])
				&& !isset($param['KEYBOARD'])
				&& !isset($param['ATTACH'])
			)
			{
				$arMessages[$messageId]['text'] = GetMessage('IM_MESSAGE_DELETED');
				$arMessages[$messageId]['params']['IS_DELETED'] = 'Y';
			}

			if (isset($param['FILE_ID']))
			{
				foreach ($param['FILE_ID'] as $fileId)
				{
					$arFiles[$fileId] = $fileId;
				}
			}
		}

		$arChatFiles = CIMDisk::GetFiles($toChatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		$arResult = Array(
			'chatId' => $toChatId,
			'message' => $arMessages,
			'usersMessage' => $arUsersMessage,
			'unreadMessage' => $arUnreadMessages,
			'users' => Array(),
			'userInGroup' => Array(),
			'readedList' => $readedList,
			'files' => $arChatFiles
		);

		if (is_array($loadExtraData) || is_bool($loadExtraData) && $loadExtraData == true)
		{
			$bDepartment = true;
			if (is_array($loadExtraData) && $loadExtraData['DEPARTMENT'] == 'N')
				$bDepartment = false;

			$arChat = self::GetChatData(array(
				'ID' => $toChatId,
				'USE_CACHE' => 'N'
			));
			if (
				isset($arChat['chat'][$toChatId]) && $arChat['chat'][$toChatId]['message_type'] == IM_MESSAGE_OPEN
				|| isset($arChat['chat'][$toChatId]) && $arChat['chat'][$toChatId]['message_type'] == IM_MESSAGE_OPEN_LINE
				|| isset($arChat['userInChat'][$toChatId]) && in_array($fromUserId, $arChat['userInChat'][$toChatId])
			)
			{
				$arResult['lines']  = $arChat['lines'];
				$arResult['userInChat']  = $arChat['userInChat'];
				$arResult['userChatBlockStatus'] = $arChat['userChatBlockStatus'];

				$ar = CIMContactList::GetUserData(array(
						'ID' => array_values(array_merge($arUsers, $arChat['userInChat'][$toChatId])),
						'DEPARTMENT' => ($bDepartment? 'Y': 'N'),
						'USE_CACHE' => 'N'
					)
				);
				$arResult['users'] = $ar['users'];
				$arResult['userInGroup']  = $ar['userInGroup'];

				if ($arChat['chat'][$toChatId]['extranet'] === "")
				{
					$isExtranet = false;
					foreach ($ar['users'] as $userData)
					{
						if ($userData['extranet'])
						{
							$isExtranet = true;
							break;
						}
					}
					IM\Model\ChatTable::update($toChatId, Array('EXTRANET' => $isExtranet? "Y":"N"));

					$arChat['chat'][$toChatId]['extranet'] = $isExtranet;
				}
				$arResult['chat'] = $arChat['chat'];
			}
		}

		if ($chatData['ENTITY_TYPE'] == 'LINES' && $chatData['ENTITY_ID'] && CModule::IncludeModule('imopenlines'))
		{
			[, $lineId] = explode('|', $chatData["ENTITY_ID"]);
			$configManager = new \Bitrix\ImOpenLines\Config();
			$arResult['openlines']['canVoteAsHead'][$lineId] = $configManager->canVoteAsHead($lineId);
		}
		else if ($chatData['ENTITY_TYPE'] == 'LIVECHAT' && $chatData['ENTITY_ID'] && CModule::IncludeModule('imopenlines'))
		{
			[$lineId, $userId] = explode('|', $chatData["ENTITY_ID"]);
			$userCode = 'livechat|' . $lineId . '|' . $chatData['ID'] . '|' . $userId;
			unset($lineId, $userId);
			foreach ($arResult['users'] as $userId => $userData)
			{
				$arResult['users'][$userId] = \Bitrix\ImOpenLines\Connector::getOperatorInfo($lineId, $userId, $userCode);
			}
		}

		return $arResult;
	}

	function GetLastMessageLimit($chatId, $messageStartId, $messageEndId = 0, $loadExtraData = false, $bTimeZone = true, $order = 'DESC')
	{
		$messageStartId = intval($messageStartId);
		$messageEndId = intval($messageEndId);
		$chatId = intval($chatId);

		if ($chatId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_CHAT_ID"), "ERROR_TO_CHAT_ID");
			return false;
		}

		$orm = IM\Model\ChatTable::getById($chatId);
		if (!($chatData = $orm->fetch()))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_CHAT_NOT_EXISTS"), "ERROR_CHAT_NOT_EXISTS");
			return false;
		}

		global $DB;
		if (!$bTimeZone)
			CTimeZone::Disable();

		$strSql = "
			SELECT
				M.ID,
				M.CHAT_ID,
				M.MESSAGE,
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
				M.AUTHOR_ID,
				C.TYPE as CHAT_TYPE
			FROM b_im_message M
			INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
			WHERE
				M.CHAT_ID = ".$chatId."
				AND M.ID >= ".$messageStartId."
				".($messageEndId > 0? "AND M.ID <= ".$messageEndId: "")."
			ORDER BY M.DATE_CREATE ".($order == 'ASC'? 'ASC': 'DESC')."
		";

		if (!$bTimeZone)
			CTimeZone::Enable();

		$dbRes = $DB->Query($strSql);

		$arMessages = Array();
		$arMessageId = Array();
		$arUsers = Array();
		$arUsersMessage = Array();
		while ($arRes = $dbRes->Fetch())
		{
			$arMessages[$arRes['ID']] = Array(
				'id' => $arRes['ID'],
				'chatId' => $arRes['CHAT_ID'],
				'senderId' => $arRes['AUTHOR_ID'],
				'recipientId' => $arRes['CHAT_ID'],
				'date' => DateTime::createFromTimestamp($arRes['DATE_CREATE']),
				'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
				'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE']),
			);

			$arMessageId[] = $arRes['ID'];
			$arUsersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];
			$arUsers[$arRes['CHAT_ID']][] = $arRes['AUTHOR_ID'];
		}

		if ($chatData['TYPE'] == IM_MESSAGE_OPEN_LINE)
		{
			\Bitrix\Im\Disk\NoRelationPermission::add($chatId, $this->user_id);
		}

		$params = CIMMessageParam::Get($arMessageId);

		$arFiles = Array();
		foreach ($params as $messageId => $param)
		{
			$arMessages[$messageId]['params'] = $param;
			if (isset($param['FILE_ID']))
			{
				foreach ($param['FILE_ID'] as $fileId)
				{
					$arFiles[$fileId] = $fileId;
				}
			}
		}

		$arChatFiles = CIMDisk::GetFiles($chatId, $arFiles); // TODO get files for sessions
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		$arResult = Array(
			'chatId' => $chatId,
			'message' => $arMessages,
			'usersMessage' => $arUsersMessage,
			'users' => Array(),
			'userInGroup' => Array(),
			'files' => $arChatFiles
		);

		if (is_array($loadExtraData) || is_bool($loadExtraData) && $loadExtraData == true)
		{
			$bDepartment = true;
			if (is_array($loadExtraData) && $loadExtraData['DEPARTMENT'] == 'N')
				$bDepartment = false;

			$arChat = self::GetChatData(array(
				'ID' => $chatId,
				'USE_CACHE' => 'N'
			));

			$arResult['lines']  = $arChat['lines'];
			$arResult['userInChat']  = $arChat['userInChat'];
			$arResult['userChatBlockStatus'] = $arChat['userChatBlockStatus'];

			$ar = CIMContactList::GetUserData(array(
					'ID' => $arUsers[$chatId],
					'DEPARTMENT' => ($bDepartment? 'Y': 'N'),
					'USE_CACHE' => 'N'
				)
			);
			$arResult['users'] = $ar['users'];
			$arResult['userInGroup']  = $ar['userInGroup'];
			$arResult['chat'] = $arChat['chat'];
		}

		return $arResult;
	}

	public static function getChatType($chatData)
	{
		return \Bitrix\Im\Chat::getType($chatData);
	}

	public function GetLastSendMessage($arParams)
	{
		global $DB;

		if (!isset($arParams['ID']))
			return false;

		$chatId = $arParams['ID'];

		$fromUserId = isset($arParams['FROM_USER_ID']) && intval($arParams['FROM_USER_ID'])>0? intval($arParams['FROM_USER_ID']): $this->user_id;
		$limit = isset($arParams['LIMIT']) && intval($arParams['LIMIT'])>0? intval($arParams['LIMIT']): false;
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;

		$arChatId = Array();
		if (is_array($chatId))
		{
			foreach ($chatId as $val)
				$arChatId[] = intval($val);
		}
		else
		{
			$arChatId[] = intval($chatId);
		}
		if (empty($arChatId))
			return Array();

		$sqlLimit = '';
		if ($limit)
		{
			if ($DB->type == "MSSQL")
			{
				$sqlLimit = " AND M.DATE_CREATE > dateadd(day, -".$limit.", getdate())";
			}
			elseif ($DB->type == "ORACLE")
			{
				$sqlLimit = " AND M.DATE_CREATE > SYSDATE-".$limit;
			}
			else
			{
				$connection = \Bitrix\Main\Application::getInstance()->getConnection();
				$helper = $connection->getSqlHelper();
				$sqlLimit = " AND M.DATE_CREATE > ". $helper->addDaysToDateTime(-1 * $limit);
			}
		}
		if (!$bTimeZone)
			CTimeZone::Disable();

		$strSql = "
			SELECT
				M.ID,
				M.CHAT_ID,
				M.MESSAGE,
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
				M.AUTHOR_ID,
				C.TITLE as CHAT_TITLE,
				C.COLOR as CHAT_COLOR,
				C.ENTITY_TYPE as CHAT_ENTITY_TYPE,
				C.ENTITY_DATA_1 as CHAT_ENTITY_DATA_1,
				C.ENTITY_DATA_2 as CHAT_ENTITY_DATA_2,
				C.ENTITY_DATA_3 as CHAT_ENTITY_DATA_3,
				".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." as CHAT_DATE_CREATE,
				C.TYPE as CHAT_TYPE,
				R.ID as RID
			FROM b_im_message M
			INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.LAST_MESSAGE_ID = M.ID
			LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$fromUserId."
			WHERE
				M.ID = C.LAST_MESSAGE_ID
				AND M.CHAT_ID IN (".implode(",",$arChatId).")
				".$sqlLimit."
		";
		if (!$bTimeZone)
			CTimeZone::Enable();

		$arMessages = Array();
		$enableOpenChat = CIMMessenger::CheckEnableOpenChat();
		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->Fetch())
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

			if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				if (!$enableOpenChat)
				{
					continue;
				}
				else if (intval($arRes['RID']) <= 0 && IM\User::getInstance($this->user_id)->isExtranet())
				{
					continue;
				}
			}
			else if (intval($arRes['RID']) <= 0)
			{
				continue;
			}

			$chatType = \Bitrix\Im\Chat::getType($arRes);

			$arMessages[$arRes['CHAT_ID']] = Array(
				'id' => $arRes['ID'],
				'senderId' => $arRes['AUTHOR_ID'],
				'recipientId' => $arRes['CHAT_ID'],
				'chatTitle' => \Bitrix\Im\Text::decodeEmoji($arRes['CHAT_TITLE']),
				'date' => DateTime::createFromTimestamp($arRes['DATE_CREATE']),
				'color' => $arRes["CHAT_COLOR"] == ""? IM\Color::getColorByNumber($arRes['CHAT_ID']): IM\Color::getColor($arRes['CHAT_COLOR']),
				'type' => $chatType,
				'messageType' => $arRes["CHAT_TYPE"],
				'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
				'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE']),
			);
		}

		return $arMessages;
	}

	public static function GetRelationById($ID, $userId = false, $timezone = true, $withCounter = true)
	{
		global $DB;

		$ID = intval($ID);
		$userId = intval($userId);
		$arResult = Array();

		if (!$timezone)
		{
			CTimeZone::Disable();
		}

		$strSql = "
			SELECT
				R.*,
				U.EXTERNAL_AUTH_ID
			FROM b_im_relation R
			LEFT JOIN b_user U ON U.ID = R.USER_ID
			WHERE R.CHAT_ID = ".$ID." ".($userId>0? "AND R.USER_ID = ".$userId: "");

		if (!$timezone)
		{
			CTimeZone::Enable();
		}

		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->Fetch())
			$arResult[$arRes['USER_ID']] = $arRes;

		if ($userId > 0)
			$arResult = isset($arResult[$userId])? $arResult[$userId]: false;

		if ($arResult === false)
		{
			return $arResult;
		}

		// region New counter
		if ($withCounter)
		{
			$readService = new IM\V2\Message\ReadService($userId);
			if ($userId > 0)
			{
				$arResult['COUNTER'] = $readService->getCounterService()->getByChat($ID);
				$lastRead =  $readService->getViewedService()->getDateViewedByMessageId($arResult['LAST_ID']);
				$arResult['LAST_READ'] = isset($lastRead) ? $lastRead->format('Y-m-d H:i:s') : null;
			}
			else
			{
				$userIds = array_keys($arResult);
				$counters = $readService->getCounterService()->getByChatForEachUsers($ID, $userIds);
				$lastIdInChat = $readService->getLastMessageIdInChat($ID);
				$lastReads = $readService->getViewedService()->getDateViewedByMessageIdForEachUser($lastIdInChat, $userIds);
				foreach ($arResult as $id => $user)
				{
					$arResult[$id]['COUNTER'] = $counters[$id] ?? 0;
					$arResult[$id]['LAST_READ'] = isset($lastReads[$id]) ? $lastReads[$id]->format('Y-m-d H:i:s') : null;
				}
			}
		}
		// endregion

		return $arResult;
	}

	public function GetPersonalChat(?int $userId = null)
	{
		if (!$userId)
		{
			$userId = $this->user_id;
		}

		$favoriteChatResult = \Bitrix\IM\V2\Chat\FavoriteChat::find(['TO_USER_ID' => $userId]);
		if (!$favoriteChatResult->hasResult())
		{
			$favoriteChatResult = IM\V2\Chat\ChatFactory::getInstance()->addChat([
				'TYPE' => \Bitrix\Im\V2\Chat::IM_TYPE_PRIVATE,
				'ENTITY_TYPE' => \Bitrix\Im\V2\Chat::ENTITY_TYPE_FAVORITE,
				'AUTHOR_ID' => $userId,
				'USERS' => [$userId],
				'MESSAGE' => GetMessage('IM_PERSONAL_DESCRIPTION')
			]);

			return $favoriteChatResult->getResult()['CHAT_ID'];
		}

		return $favoriteChatResult->getResult()['ID'];
	}

	/**
	 * @deprecated
	 * @use ...
	 * @param $fromUserId
	 * @param $toUserId
	 * @return array
	 */
	public static function GetPrivateRelation($fromUserId, $toUserId)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		$toUserId = intval($toUserId);

		$arResult = Array();
		$strSql = "
			SELECT
				RF.*
			FROM
				b_im_relation RF
				INNER JOIN b_im_relation RT on RF.CHAT_ID = RT.CHAT_ID
			WHERE
				RF.USER_ID = ".$fromUserId."
			and RT.USER_ID = ".$toUserId."
			and RF.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
		";
		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
			$arResult = $arRes;

		return $arResult;
	}

	// region General Chat
	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::getGeneralChatId()
	 */
	public static function GetGeneralChatId()
	{
		return IM\V2\Chat\GeneralChat::getGeneralChatId() ?? 0;
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::add()
	 */
	public static function InstallGeneralChat($agentMode = false)
	{
		(new IM\V2\Chat\GeneralChat())->add([]);
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::getAutoMessageStatus()
	 */
	public static function GetGeneralChatAutoMessageStatus($type)
	{
		return \Bitrix\Im\V2\Chat\GeneralChat::getAutoMessageStatus((string)$type);
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::hasPostAccess()
	 */
	public static function CanSendMessageToGeneralChat($userId = null)
	{
		$generalChat = IM\V2\Chat\ChatFactory::getInstance()->getGeneralChat();
		if ($generalChat instanceof Chat\GeneralChat)
		{
			return $generalChat->hasManageMessagesAccess($userId);
		}

		$generalChatId = \COption::GetOptionString('im', 'general_chat_id');
		$generalChat = IM\V2\Chat::getInstance((int)$generalChatId);
		if ($generalChat instanceof IM\V2\Chat\GeneralChat)
		{
			return $generalChat->hasManageMessagesAccess($userId);
		}

		return false;
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat::setManageMessages()
	 */
	public static function SetAccessToGeneralChat($allowAll = true, $allowCodes = Array())
	{
		$prevAllow = COption::GetOptionString("im", "allow_send_to_general_chat_all");
		$prevCodes = COption::GetOptionString("im", "allow_send_to_general_chat_rights");

		if ($allowAll)
		{
			$allow = 'Y';
			$codes = 'AU';
		}
		else
		{
			$allow = 'N';

			if (is_array($allowCodes) && count($allowCodes) > 0)
			{
				$codes = implode(",", $allowCodes);
			}
			else
			{
				$codes = "";
			}
		}
		COption::SetOptionString("im", "allow_send_to_general_chat_all", $allow);
		COption::SetOptionString("im", "allow_send_to_general_chat_rights", $codes);

		if ($prevAllow != $allow || $prevCodes != $codes)
		{
			if (CModule::IncludeModule('pull'))
			{
				CPullStack::AddShared(Array(
					'module_id' => 'im',
					'command' => 'generalChatAccess',
					'params' => Array(
						"status" => $prevAllow == 'Y' && $allow == 'N'? 'blocked': 'allowed'
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));
			}
		}

		return true;
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::canJoinGeneralChat()
	 */
	public static function CanJoinGeneralChatId($userId)
	{
		$generalChat = IM\V2\Chat\ChatFactory::getInstance()->getGeneralChat();

		if ($generalChat)
		{
			return $generalChat->canJoinGeneralChat((int)$userId);
		}

		return false;
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::linkGeneralChat()
	 */
	public static function linkGeneralChatId($chatId)
	{
		return IM\V2\Chat\GeneralChat::linkGeneralChat((int) $chatId);
	}

	/**
	 * @deprecated Use \Bitrix\Im\V2\Chat\GeneralChat::unlinkGeneralChat()
	 */
	public static function UnlinkGeneralChatId()
	{
		return IM\V2\Chat\GeneralChat::unlinkGeneralChat();
	}

	public static function AddGeneralMessage($arFields)
	{
		$arFields['MESSAGE_TYPE'] = IM_MESSAGE_OPEN;
		$arFields['TO_CHAT_ID'] = self::GetGeneralChatId();

		return CIMMessenger::Add($arFields);
	}
	// endregion

	public static function GetChatData($arParams = Array())
	{
		global $DB;

		$arParams['PHOTO_SIZE'] = isset($arParams['PHOTO_SIZE'])? intval($arParams['PHOTO_SIZE']): 200;

		$from = "
			FROM b_im_chat C
			LEFT JOIN b_im_alias A ON A.ENTITY_ID = C.ID AND A.ENTITY_TYPE = C.ENTITY_TYPE
			LEFT JOIN b_im_relation R1 ON C.ID = R1.CHAT_ID
		";
		$groupTypes = self::implodeTypesForQuery(self::getGroupTypes());

		if (isset($arParams['SKIP_PRIVATE']) && $arParams['SKIP_PRIVATE'] == 'Y')
		{
			$from .= " AND C.TYPE IN ({$groupTypes})";
		}

		$innerJoin = $whereUser = "";
		if (isset($arParams['GET_LIST']) && $arParams['GET_LIST'] == 'Y')
		{
			if (!isset($arParams['USER_ID']))
				return false;

			$innerJoin = "INNER JOIN b_im_relation R2 ON R2.CHAT_ID = C.ID";
			$whereGeneral = "WHERE R2.USER_ID = ".intval($arParams['USER_ID']);
		}
		else
		{
			$arFilter = Array();
			if (isset($arParams['ID']) && is_array($arParams['ID']))
			{
				foreach ($arParams['ID'] as $key => $value)
					$arFilter['ID'][$key] = intval($value);
			}
			else if (isset($arParams['ID']) && intval($arParams['ID']) > 0)
			{
				$arFilter['ID'][] = intval($arParams['ID']);
			}

			if (empty($arFilter['ID']))
			{
				return false;
			}

			if (isset($arParams['USER_ID']))
			{
				$innerJoin = "LEFT JOIN b_im_relation R2 ON R2.CHAT_ID = C.ID AND R2.USER_ID = ".intval($arParams['USER_ID']);
			}
			$whereGeneral = "WHERE C.ID IN (".implode(',', $arFilter['ID']).") ";
		}

		$connection = \Bitrix\Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$strSql = "
			SELECT
				C.ID as CHAT_ID,
				C.PARENT_ID as CHAT_PARENT_ID,
				C.PARENT_MID as CHAT_PARENT_MID,
				C.TITLE as CHAT_TITLE,
				C.CALL_TYPE as CHAT_CALL_TYPE,
				C.AUTHOR_ID as CHAT_OWNER_ID,
				C.CALL_NUMBER as CHAT_CALL_NUMBER,
				C.EXTRANET as CHAT_EXTRANET,
				C.COLOR as CHAT_COLOR,
				C.TYPE as CHAT_TYPE,
				C.AVATAR,
				C.ENTITY_TYPE,
				C.ENTITY_DATA_1 as ENTITY_DATA_1,
				C.ENTITY_DATA_2 as ENTITY_DATA_2,
				C.ENTITY_DATA_3 as ENTITY_DATA_3,
				C.MESSAGE_COUNT as CHAT_MESSAGE_COUNT,
				C.MANAGE_USERS_ADD,
				C.MANAGE_USERS_DELETE,
				C.MANAGE_UI,
				C.MANAGE_SETTINGS,
				C.CAN_POST,
				A.ALIAS ALIAS_NAME,
				".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." as CHAT_DATE_CREATE,
				C.ENTITY_ID,
				R1.NOTIFY_BLOCK as RELATION_NOTIFY_BLOCK,
				R1.USER_ID as RELATION_USER_ID,
				R1.CALL_STATUS,
				R1.MANAGER as RELATION_MANAGER
				".(isset($arParams['USER_ID'])? ", R2.ID as RID": "")."
			".$from."
			".$innerJoin."
			".$whereGeneral."
		";

		$arChat = Array();
		$arLines = Array();
		$linesChatIds = [];
		$multidialogChats = [];
		$multidialogs = [];
		$arUserInChat = Array();
		$arUserCallStatus = Array();
		$arUserChatBlockStatus = Array();
		$arManagerList = Array();
		$enableOpenChat = CIMMessenger::CheckEnableOpenChat();
		$generalChatId = CIMChat::GetGeneralChatId();

		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->GetNext(true, false))
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

			if (isset($arParams['USER_ID']))
			{
				if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
				{
					if (!$enableOpenChat)
					{
						continue;
					}
					else if (intval($arRes['RID']) <= 0 && IM\User::getInstance($arParams['USER_ID'])->isExtranet())
					{
						continue;
					}
				}
				else if (intval($arRes['RID']) <= 0)
				{
					if ($arRes['CHAT_TYPE'] === IM_MESSAGE_OPEN_LINE && CModule::IncludeModule('imopenlines'))
					{
						$userCodeParts = \Bitrix\ImOpenLines\Session\Common::parseUserCode($arRes['ENTITY_ID']);
						if(!\Bitrix\ImOpenLines\Config::canJoin($userCodeParts['CONFIG_ID']))
						{
							continue;
						}
					}
					else
					{
						continue;
					}
				}
			}

			if (!isset($arChat[$arRes["CHAT_ID"]]))
			{
				$avatar = '/bitrix/js/im/images/blank.gif';
				if (intval($arRes["AVATAR"]) > 0)
				{
					$avatar = self::GetAvatarImage($arRes["AVATAR"], $arParams['PHOTO_SIZE']);
				}

				$chatType = \Bitrix\Im\Chat::getType($arRes);

				if ($arRes["ENTITY_TYPE"] == 'LINES')
				{
					$fieldData = explode("|", $arRes['ENTITY_DATA_1']);
					if ($fieldData[5] > 0)
					{
						$arLines[$arRes["CHAT_ID"]] = $fieldData[5];
					}
					else
					{
						$linesChatIds[] = (int)$arRes['CHAT_ID'];
					}
				}
				elseif (\Bitrix\Main\Loader::includeModule('imbot'))
				{
					if (in_array($arRes["ENTITY_TYPE"], [\Bitrix\ImBot\Bot\Support24::CHAT_ENTITY_TYPE, \Bitrix\ImBot\Bot\Network::CHAT_ENTITY_TYPE], true))
					{
						$multidialogChats[$arRes['CHAT_ID']] = [
							'type' => $arRes['ENTITY_TYPE'],
							'botId' => (int)$arRes['CHAT_OWNER_ID'],
						];
					}
				}
				else if ($generalChatId == $arRes['CHAT_ID'])
				{
					$arRes["ENTITY_TYPE"] = 'GENERAL';
				}

				$publicOption = '';
				if ($arRes['ALIAS_NAME'])
				{
					$publicOption = [
						'code' => $arRes['ALIAS_NAME'],
						'link' => IM\Alias::getPublicLink($arRes['ENTITY_TYPE'], $arRes['ALIAS_NAME'])
					];
				}

				$dateCreate = $arRes["CHAT_DATE_CREATE"]? DateTime::createFromTimestamp($arRes["CHAT_DATE_CREATE"]): false;

				$arChat[$arRes["CHAT_ID"]] = Array(
					'id' => $arRes["CHAT_ID"],
					'parent_chat_id' => (int)($arRes['CHAT_PARENT_ID'] ?? 0),
					'parent_message_id' => (int)($arRes['CHAT_PARENT_MID'] ?? 0),
					'name' => \Bitrix\Im\Text::decodeEmoji($arRes["CHAT_TITLE"]),
					'owner' => $arRes["CHAT_OWNER_ID"],
					'color' => $arRes["CHAT_COLOR"] == ""? IM\Color::getColorByNumber($arRes['CHAT_ID']): IM\Color::getColor($arRes['CHAT_COLOR']),
					'extranet' => $arRes["CHAT_EXTRANET"] == ""? "": ($arRes["CHAT_EXTRANET"] == "Y"? true: false),
					'avatar' => $avatar,
					'call' => trim($arRes["CHAT_CALL_TYPE"]),
					'call_number' => trim($arRes["CHAT_CALL_NUMBER"]),
					'entity_type' => trim($arRes["ENTITY_TYPE"]),
					'entity_id' => trim($arRes["ENTITY_ID"]),
					'entity_data_1' => trim($arRes["ENTITY_DATA_1"]),
					'entity_data_2' => trim($arRes["ENTITY_DATA_2"]),
					'entity_data_3' => trim($arRes["ENTITY_DATA_3"]),
					'message_count' => (int)($arRes['CHAT_MESSAGE_COUNT'] ?? 0),
					'public' => $publicOption,
					'mute_list' => array(),
					'manager_list' => array(),
					'date_create' => $dateCreate,
					'type' => $chatType,
					'entity_link' => IM\V2\Chat\EntityLink::getInstance(self::initChatByArray($arRes))->toRestFormat(),
					'permissions' => [
						'manage_users_add' => mb_strtolower($arRes['MANAGE_USERS_ADD']),
						'manage_users_delete' => mb_strtolower($arRes['MANAGE_USERS_DELETE']),
						'manage_ui' => mb_strtolower($arRes['MANAGE_UI']),
						'manage_settings' => mb_strtolower($arRes['MANAGE_SETTINGS']),
						'manage_messages' => mb_strtolower($arRes['CAN_POST']),
						'can_post' => mb_strtolower($arRes['CAN_POST']),
					],
					'message_type' => $arRes["CHAT_TYPE"],
					'is_new' => CIMChat::isNewChat($arRes['CHAT_TYPE'], $dateCreate ?: null),
				);
				$arUserInChat[$arRes["CHAT_ID"]] = [];
				$arUserChatBlockStatus[$arRes["CHAT_ID"]] = [];
				$arUserCallStatus[$arRes["CHAT_ID"]] = [];
				$arManagerList[$arRes["CHAT_ID"]] = [];
			}
			if (isset($arRes['RELATION_USER_ID']))
			{
				$arRes["RELATION_USER_ID"] =  (int)$arRes["RELATION_USER_ID"];
				$arUserInChat[$arRes["CHAT_ID"]][] = $arRes["RELATION_USER_ID"];
				$arUserChatBlockStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = $arRes["RELATION_NOTIFY_BLOCK"] == 'Y';
				$arUserCallStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = trim($arRes["CALL_STATUS"]);
				$arChat[$arRes["CHAT_ID"]]['mute_list'] = $arUserChatBlockStatus[$arRes["CHAT_ID"]];
				if ($arRes["RELATION_MANAGER"] == 'Y')
				{
					$arManagerList[$arRes["CHAT_ID"]][] = (int)$arRes["RELATION_USER_ID"];
				}
				$arChat[$arRes["CHAT_ID"]]['manager_list'] = $arManagerList[$arRes["CHAT_ID"]] ?? null;
			}
		}

		foreach ($arUserInChat as $chatId => $userIds)
		{
			$arUserInChat[$chatId] = array_values(array_unique($userIds));

			if (isset($multidialogChats[$chatId]) && count($arUserInChat[$chatId]) == 2)
			{
				foreach ($arUserInChat[$chatId] as $userId)
				{
					if ($userId != $multidialogChats[$chatId]['botId'])
					{
						$multidialog = self::getMultidialog(
							$multidialogChats[$chatId]['type'],
							$chatId,
							$userId,
							$multidialogChats[$chatId]['botId']
						);

						if ($multidialog)
						{
							$multidialogs[$chatId] = $multidialog;
						}

						break;
					}
				}
			}
		}

		$lines = Array();
		if (CModule::IncludeModule('imopenlines'))
		{
			if (!empty($arLines))
			{
				$orm = SessionTable::getList(Array(
					'select' => Array(
						'CHAT_ID',
						'ID',
						'STATUS',
						'DATE_CREATE',
						'CONFIG_ID',
						'PAUSE',
						'CLOSED',
						'OPERATOR_ID',
					),
					'filter' => Array(
						'=ID' => array_values($arLines)
					)
				));
				while($row = $orm->fetch())
				{
					$statusGroup = null;

					if (isset($row['STATUS']))
					{
						$statusGroup = Bitrix\ImOpenLines\V2\Status\StatusGroup::getFromNumericalCode((int)$row['STATUS']);
					}

					$lines[$row['CHAT_ID']] = Array(
						'id' => (int)$row['ID'],
						'status' => (int)$row['STATUS'],
						'date_create' => $row['DATE_CREATE'],
						'statusGroup' => $statusGroup->name,
						'queueId' => $row['CONFIG_ID'],
						'pinned' => $row['PAUSE'] === 'Y',
						'isClosed' => $row['CLOSED'] === 'Y',
						'operatorId' => (int)$row['OPERATOR_ID'],
					);
				}
			}

			if (!empty($linesChatIds))
			{
				$orm = SessionTable::getList([
					'select' => [
						new ExpressionField('ID', 'MAX(ID)'),
					],
					'filter' => [
						'=CHAT_ID' => array_values($linesChatIds),
					],
					'group' => ['CHAT_ID'],
				]);

				$ids = [];

				while($row = $orm->fetch())
				{
					$ids[] = $row['ID'];
				}

				$orm = SessionTable::getList([
					'select' => [
						'CHAT_ID',
						'ID',
						'STATUS',
						'DATE_CREATE',
						'CONFIG_ID',
						'PAUSE',
						'CLOSED',
						'OPERATOR_ID',
					],
					'filter' => [
						'=ID' => $ids,
					],
				]);

				while($row = $orm->fetch())
				{
					$statusGroup = null;

					if (isset($row['STATUS']))
					{
						$statusGroup = Bitrix\ImOpenLines\V2\Status\StatusGroup::getFromNumericalCode((int)$row['STATUS']);
					}

					$lines[$row['CHAT_ID']] = [
						'id' => (int)$row['ID'],
						'status' => (int)$row['STATUS'],
						'date_create' => $row['DATE_CREATE'],
						'statusGroup' => $statusGroup->name,
						'queueId' => $row['CONFIG_ID'],
						'pinned' => $row['PAUSE'] === 'Y',
						'isClosed' => $row['CLOSED'] === 'Y',
						'operatorId' => (int)$row['OPERATOR_ID'],
					];
				}
			}
		}

		$result = [
			'chat' => $arChat,
			'lines' => $lines,
			'multidialogs' => $multidialogs,
			'userInChat' => $arUserInChat,
			'userCallStatus' => $arUserCallStatus,
			'userChatBlockStatus' => $arUserChatBlockStatus
		];

		return $result;
	}

	public static function getMultidialog($type, $chatId, $userId, $botId)
	{
		if ($type === \Bitrix\ImBot\Bot\Support24::CHAT_ENTITY_TYPE)
		{
			/** @var \Bitrix\Imbot\Bot\SupportBot $classSupport */
			if (
				\Bitrix\Main\Loader::includeModule('bitrix24')
				&& \Bitrix\ImBot\Bot\Support24::isEnabled()
			)
			{
				$classSupport = \Bitrix\ImBot\Bot\Support24::class;
			}
			elseif (\Bitrix\ImBot\Bot\SupportBox::isEnabled())
			{
				$classSupport = \Bitrix\ImBot\Bot\SupportBox::class;
			}

			$multidialog = $classSupport::getMultidialog((int)$chatId, (int)$botId, $userId);
		}
		elseif($type === \Bitrix\ImBot\Bot\Network::CHAT_ENTITY_TYPE)
		{
			$multidialog = \Bitrix\ImBot\Bot\Network::getMultidialog((int)$chatId, (int)$botId, $userId);
		}

		return $multidialog;
	}

	public static function GetOpenChatData($arParams = Array())
	{
		global $DB;

		$arParams['PHOTO_SIZE'] = isset($arParams['PHOTO_SIZE'])? intval($arParams['PHOTO_SIZE']): 100;

		$strSql = "
			SELECT
				C.ID as CHAT_ID,
				C.TITLE as CHAT_TITLE,
				C.CALL_TYPE as CHAT_CALL_TYPE,
				C.AUTHOR_ID as CHAT_OWNER_ID,
				C.CALL_NUMBER as CHAT_CALL_NUMBER,
				C.EXTRANET as CHAT_EXTRANET,
				C.COLOR as CHAT_COLOR,
				C.TYPE as CHAT_TYPE,
				C.AVATAR,
				C.ENTITY_TYPE,
				C.ENTITY_DATA_1,
				C.ENTITY_DATA_2,
				C.ENTITY_DATA_3,
				".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." as CHAT_DATE_CREATE,
				C.ENTITY_ID,
				R2.NOTIFY_BLOCK as RELATION_NOTIFY_BLOCK,
				R2.USER_ID as RELATION_USER_ID,
				R2.USER_ID as RELATION_MANAGER,
				R2.CALL_STATUS,
				R2.ID as RID
			FROM 
				b_im_chat C
				LEFT JOIN b_im_relation R2 ON R2.CHAT_ID = C.ID AND R2.USER_ID = ".(int)$arParams['USER_ID']."
			WHERE 
				C.TYPE = '".IM_MESSAGE_OPEN."' 
				AND EXISTS(SELECT R3.ID FROM b_im_relation R3 WHERE R3.CHAT_ID = C.ID LIMIT 1)
		";

		$arChat = Array();
		$arUserInChat = Array();
		$arUserCallStatus = Array();
		$arUserChatBlockStatus = Array();
		$arManagerList = Array();

		$generalChatId = CIMChat::GetGeneralChatId();

		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->GetNext(true, false))
		{
			if (intval($arRes['RID']) <= 0 && IM\User::getInstance($arParams['USER_ID'])->isExtranet())
			{
				continue;
			}

			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

			if (!isset($arChat[$arRes["CHAT_ID"]]))
			{
				$avatar = '/bitrix/js/im/images/blank.gif';
				if (intval($arRes["AVATAR"]) > 0)
				{
					$avatar = self::GetAvatarImage($arRes["AVATAR"], $arParams['PHOTO_SIZE']);
				}

				$chatType = \Bitrix\Im\Chat::getType($arRes);

				if ($generalChatId == $arRes['CHAT_ID'])
				{
					$arRes["ENTITY_TYPE"] = 'GENERAL';
				}

				$arChat[$arRes["CHAT_ID"]] = Array(
					'id' => $arRes["CHAT_ID"],
					'name' => \Bitrix\Im\Text::decodeEmoji($arRes["CHAT_TITLE"]),
					'owner' => $arRes["CHAT_OWNER_ID"],
					'color' => $arRes["CHAT_COLOR"] == ""? IM\Color::getColorByNumber($arRes['CHAT_ID']): IM\Color::getColor($arRes['CHAT_COLOR']),
					'extranet' => $arRes["CHAT_EXTRANET"] == ""? "": ($arRes["CHAT_EXTRANET"] == "Y"? true: false),
					'avatar' => $avatar,
					'call' => trim($arRes["CHAT_CALL_TYPE"]),
					'call_number' => trim($arRes["CHAT_CALL_NUMBER"]),
					'entity_type' => trim($arRes["ENTITY_TYPE"]),
					'entity_data_1' => trim($arRes["ENTITY_DATA_1"]),
					'entity_data_2' => trim($arRes["ENTITY_DATA_2"]),
					'entity_data_3' => trim($arRes["ENTITY_DATA_3"]),
					'mute_list' => array(),
					'manager_list' => array(),
					'date_create' => $arRes["CHAT_DATE_CREATE"]? DateTime::createFromTimestamp($arRes["CHAT_DATE_CREATE"]): false,
					'entity_id' => trim($arRes["ENTITY_ID"]),
					'type' => $chatType,
					'message_type' => $arRes["CHAT_TYPE"],
				);

			}
			$arUserChatBlockStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = $arRes['RELATION_NOTIFY_BLOCK'] == 'Y';
			$arUserInChat[$arRes["CHAT_ID"]][] = $arRes["RELATION_USER_ID"];
			$arUserCallStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = trim($arRes["CALL_STATUS"] ?? '');
			$arChat[$arRes["CHAT_ID"]]['mute_list'] = $arUserChatBlockStatus[$arRes["CHAT_ID"]];

			if ($arRes["RELATION_MANAGER"] == 'Y')
			{
				$arManagerList[$arRes["CHAT_ID"]][] = (int)$arRes["RELATION_USER_ID"];
			}
			$arChat[$arRes["CHAT_ID"]]['manager_list'] = $arManagerList[$arRes["CHAT_ID"]] ?? null;
		}

		$result = array(
			'chat' => $arChat,
			'userInChat' => $arUserInChat,
			'userCallStatus' => $arUserCallStatus,
			'userChatBlockStatus' => $arUserChatBlockStatus
		);

		return $result;
	}

	public function SetReadMessage($chatId, $lastId = null, $byEvent = false)
	{
		global $DB;

		$chatId = intval($chatId);
		if ($chatId <= 0)
			return false;

		CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_GROUP);

		$chat = IM\V2\Chat::getInstance($chatId);

		if (
			$chat->getType() == IM_MESSAGE_OPEN_LINE
			&& $chat->getAuthorId() == 0
		)
		{
			return false;
		}

		$readService = new IM\V2\Message\ReadService($this->user_id);

		$startId = $readService->getLastIdByChatId($chatId);
		$counter = 0;
		$viewedMessages = [];

		if (isset($lastId))
		{
			$message = new \Bitrix\Im\V2\Message();
			$message->setMessageId((int)$lastId)->setChatId($chatId);
			$readResult = $readService->readTo($message);
			$counter = $readResult->getResult()['COUNTER'];
			$viewedMessages = $readResult->getResult()['VIEWED_MESSAGES'];
		}
		else
		{
			$counter = $readService->readAllInChat($chatId)->getResult()['COUNTER'];
		}

		$relation = CIMMessage::SetLastId($chatId, $this->user_id, 0);

		if (!$relation)
		{
			return false;
		}

		$endId = (int)($relation['LAST_ID'] ?? 0);

		/*\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_recent SET DATE_UPDATE = NOW() WHERE USER_ID = ".$this->user_id." AND ITEM_CID = ".intval($chatId)
		);*/

		if (CModule::IncludeModule("pull"))
		{
			CIMNotify::DeleteBySubTag("IM_MESS_".$chatId.'_'.$this->user_id, false, false);
			CPushManager::DeleteFromQueueBySubTag($this->user_id, 'IM_MESS');

			if (
				$chat->getEntityType() == 'LIVECHAT'
				|| !\Bitrix\Im\User::getInstance($this->user_id)->isConnector()
			)
			{
				\Bitrix\Pull\Event::add($this->user_id, Array(
					'module_id' => 'im',
					'command' => 'readMessageChat',
					'params' => Array(
						'dialogId' => 'chat'.$chatId,
						'chatId' => (int)$chatId,
						'parentChatId' => $chat->getParentChatId(),
						'type' => $chat->getExtendedType(),
						'lastId' => $endId,
						'counter' => $counter,
						'muted' => $relation['NOTIFY_BLOCK'] === 'Y',
						'unread' => Im\Recent::isUnread($this->user_id, $relation['MESSAGE_TYPE'], 'chat'.$chatId),
						'lines' => $relation['MESSAGE_TYPE'] === IM_MESSAGE_OPEN_LINE,
						'viewedMessages' => $viewedMessages,
						'counterType' => $chat->getCounterType()->value,
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));
			}

			$arRelation = self::GetRelationById($chatId, false, true, false);
			unset($arRelation[$this->user_id]);

			$pushMessage = Array(
				'module_id' => 'im',
				'command' => 'readMessageChatOpponent',
				'expiry' => 600,
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
					'chatId' => (int)$chatId,
					'userId' => (int)$this->user_id,
					'userName' => \Bitrix\Im\User::getInstance($this->user_id)->getFullName(false),
					'lastId' => $endId,
					'date' => date('c', time()),
					'viewedMessages' => $viewedMessages,
					'chatMessageStatus' => $relation['CHAT_MESSAGE_STATUS'],
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			);
			if ($chat->getEntityType() == 'LINES')
			{
				foreach ($arRelation as $rel)
				{
					if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($arRelation[$rel["USER_ID"]]);
					}
				}
			}
			if (count($arRelation) < 200)
			{
				\Bitrix\Pull\Event::add(array_keys($arRelation), $pushMessage);
				if ($chat->needToSendPublicPull())
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
				}
			}
		}

		foreach(GetModuleEvents("im", "OnAfterChatRead", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(Array(
				'CHAT_ID' => $chat->getChatId(),
				'CHAT_ENTITY_TYPE' => $chat->getEntityType(),
				'CHAT_ENTITY_ID' => $chat->getEntityId(),
				'START_ID' => $startId,
				'END_ID' => $endId,
				'COUNT' => $relation['COUNTER'],
				'USER_ID' => $this->user_id,
				'BY_EVENT' => $byEvent
			)));
		}

		return Array(
			'DIALOG_ID' => 'chat'.$chatId,
			'CHAT_ID' => (int)$chatId,
			'LAST_ID' => $endId,
			'COUNTER' => (int)$relation['COUNTER']
		);
	}

	public function SetUnReadMessage($chatId, $lastId)
	{
		$chatId = intval($chatId);
		if ($chatId <= 0)
			return false;

		$lastId = intval($lastId);
		if (intval($lastId) <= 0)
			return false;

		/*$result = Bitrix\Im\V2\Chat::getInstance($chatId)->unreadToMessage(new IM\V2\Message($lastId));

		return $result->isSuccess();*/
		$readService = new IM\V2\Message\ReadService($this->user_id);
		$endId = $readService->getLastMessageIdInChat($chatId);
		$relation = CIMMessage::SetLastIdForUnread($chatId, $this->user_id, $lastId);
		if ($relation)
		{
			$chat = Bitrix\Im\V2\Chat::getInstance($chatId);
			\Bitrix\Main\Application::getConnection()->query(
				"UPDATE b_im_recent SET DATE_UPDATE = NOW() WHERE USER_ID = ".$this->user_id." AND ITEM_CID = ".intval($chatId)
			);

			CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_GROUP);

			if (CModule::IncludeModule("pull"))
			{
				\Bitrix\Pull\Event::add($this->user_id, Array(
					'module_id' => 'im',
					'command' => 'unreadMessageChat',
					'params' => Array(
						'dialogId' => 'chat'.$chatId,
						'chatId' => (int)$chatId,
						'lastId' => $endId,
						'date' => new DateTime(),
						'counter' => (int)$relation['COUNTER'],
						'muted' => $relation['NOTIFY_BLOCK'] === 'Y',
						'lines' => $relation['MESSAGE_TYPE'] === IM_MESSAGE_OPEN_LINE,
						'unreadTo' => $lastId,
						'unread' => Im\Recent::isUnread($this->user_id, $relation['MESSAGE_TYPE'], 'chat'.$chatId),
						'lastMessageViews' => Im\Common::toJson($chat->getLastMessageViews()),
						'counterType' => $chat->getCounterType()->value,
					),
					'push' => Array('badge' => 'Y'),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));

				$arRelation = self::GetRelationById($chatId, false, true, false);
				unset($arRelation[$this->user_id]);

				$pushMessage = Array(
					'module_id' => 'im',
					'command' => 'unreadMessageChatOpponent',
					'expiry' => 600,
					'params' => Array(
						'dialogId' => 'chat'.$chatId,
						'chatId' => (int)$chatId,
						'userId' => (int)$this->user_id,
						'chatMessageStatus' => $relation['CHAT_MESSAGE_STATUS'],
						'unreadTo' => $lastId,
						'lastMessageStatuses' => $lastMessageStatuses,
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				);
				if ($chat->getEntityType() == 'LINES')
				{
					foreach ($arRelation as $rel)
					{
						if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
						{
							unset($arRelation[$rel["USER_ID"]]);
						}
					}
				}
				$viewsByGroups = $chat->getLastMessageViewsByGroups();
				$usersForPush = array_keys($arRelation);
				$viewers = array_intersect($usersForPush, $viewsByGroups['USERS'] ?? []);
				$notViewers = array_diff($usersForPush, $viewers);
				$pushMessage['params']['lastMessageViews'] = Im\Common::toJson($viewsByGroups['FOR_VIEWERS'] ?? []);
				\Bitrix\Pull\Event::add($viewers, $pushMessage);
				$pushMessage['params']['lastMessageViews'] = Im\Common::toJson($viewsByGroups['FOR_NOT_VIEWERS'] ?? []);
				\Bitrix\Pull\Event::add($notViewers, $pushMessage);
				/*\Bitrix\Pull\Event::add(array_keys($arRelation), $pushMessage);
				if ($chat->getType() == IM_MESSAGE_OPEN || $chat->getType() == IM_MESSAGE_OPEN_LINE)
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
				}*/
			}

			Sync\Logger::getInstance()->add(
				new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, $chat->getChatId()),
				$this->user_id,
				$chat->getType()
			);


			return true;
		}

		return false;
	}

	/**
	 * @deprecated
	 * @use ..
	 * @param $arParams
	 * @return array
	 */
	public function GetUnreadMessage($arParams = Array())
	{
		global $DB;

		$bSpeedCheck = isset($arParams['SPEED_CHECK']) && $arParams['SPEED_CHECK'] == 'N'? false: true;
		$lastId = !isset($arParams['LAST_ID']) || $arParams['LAST_ID'] == null? null: intval($arParams['LAST_ID']);
		$loadDepartment = isset($arParams['LOAD_DEPARTMENT']) && $arParams['LOAD_DEPARTMENT'] == 'N'? false: true;
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;
		$bGroupByChat = isset($arParams['GROUP_BY_CHAT']) && $arParams['GROUP_BY_CHAT'] == 'Y'? true: false;
		$bUserLoad = isset($arParams['USER_LOAD']) && $arParams['USER_LOAD'] == 'N'? false: true;
		$bFileLoad = isset($arParams['FILE_LOAD']) && $arParams['FILE_LOAD'] == 'N'? false: true;
		$arExistUserData = isset($arParams['EXIST_USER_DATA']) && is_array($arParams['EXIST_USER_DATA'])? $arParams['EXIST_USER_DATA']: Array();
		$messageType = isset($arParams['MESSAGE_TYPE']) && in_array($arParams['MESSAGE_TYPE'], self::getGroupTypes())? $arParams['MESSAGE_TYPE']: 'ALL';

		$arMessages = Array();
		$arUnreadMessage = Array();
		$arUsersMessage = Array();

		$arResult = Array(
			'message' => Array(),
			'unreadMessage' => Array(),
			'usersMessage' => Array(),
			'users' => Array(),
			'userInGroup' => Array(),
			'files' => Array(),
			'countMessage' => 0,
			'chat' => Array(),
			'userChatBlockStatus' => Array(),
			'userInChat' => Array(),
			'result' => false
		);
		$bLoadMessage = $bSpeedCheck? CIMMessenger::SpeedFileExists($this->user_id, IM_SPEED_GROUP): false;
		$count = CIMMessenger::SpeedFileGet($this->user_id, IM_SPEED_GROUP);
		if (!$bLoadMessage || ($bLoadMessage && intval($count) > 0))
		{
			/*$ssqlLastId = "R1.LAST_ID";
			$ssqlStatus = " AND R1.STATUS < ".IM_STATUS_READ;
			if (!is_null($lastId) && intval($lastId) > 0 && !CIMMessenger::CheckXmppStatusOnline())
			{
				$ssqlLastId = intval($lastId);
				$ssqlStatus = "";
			}

			$arRelations = Array();
			if ($ssqlStatus <> '')
			{
				$strSql ="
					SELECT
						R1.USER_ID,
						R1.CHAT_ID,
						R1.LAST_ID
					FROM
						b_im_relation R1
					WHERE
						R1.USER_ID = ".$this->user_id."
						".($messageType == 'ALL'? "AND R1.MESSAGE_TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."')": "AND R1.MESSAGE_TYPE = '".$messageType."'")."
						".$ssqlStatus."
				";
				$dbSubRes = $DB->Query($strSql);
				while ($arRes = $dbSubRes->Fetch())
				{
					//$ssqlLastId = intval($arRes['LAST_ID']);
					$arRelations[] = $arRes;
				}
			}*/

			$arMessageId = Array();
			$arMessageChatId = Array();
			$arLastMessage = Array();
			$arMark = Array();
			$arChat = Array();

			$arPrepareResult = Array();
			$arFilteredResult = Array();

			if (!$bTimeZone)
				CTimeZone::Disable();
			$groupTypes = self::implodeTypesForQuery(self::getGroupTypes());
			$strSql = "
					SELECT
						M.ID,
						M.CHAT_ID,
						M.MESSAGE,
						".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
						M.AUTHOR_ID,
						R1.MESSAGE_TYPE as MESSAGE_TYPE
					FROM b_im_message M
					INNER JOIN b_im_relation R1 ON M.CHAT_ID = R1.CHAT_ID AND R1.USER_ID != M.AUTHOR_ID
					INNER JOIN b_im_message_unread MU ON M.ID = MU.MESSAGE_ID AND MU.USER_ID = " . $this->user_id . "
					WHERE
						R1.USER_ID = ".$this->user_id."
						".($messageType == 'ALL'? "AND R1.MESSAGE_TYPE IN ({$groupTypes})": "AND R1.MESSAGE_TYPE = '".$messageType."'")."
				";
			if (!$bTimeZone)
				CTimeZone::Enable();

			$strSql = $DB->TopSql($strSql, 1000);

			$dbRes = $DB->Query($strSql);

			while ($arRes = $dbRes->Fetch())
			{
				$arPrepareResult[$arRes['CHAT_ID']][$arRes['ID']] = $arRes;
			}
			foreach ($arPrepareResult as $chatId => $arRes)
			{
				if (count($arPrepareResult[$chatId]) > 100)
				{
					$arPrepareResult[$chatId] = array_slice($arRes, -100, 100);
				}
				$arFilteredResult = array_merge($arFilteredResult, $arPrepareResult[$chatId]);
			}
			unset($arPrepareResult);

			foreach ($arFilteredResult as $arRes)
			{
				$arUsers[] = $arRes['AUTHOR_ID'];

				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['AUTHOR_ID'],
					'recipientId' => $arRes['CHAT_ID'],
					'date' => DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'text' => $arRes['MESSAGE'],
					'messageType' => $arRes['MESSAGE_TYPE'],
				);
				if ($bGroupByChat)
				{
					$arMessages[$arRes['ID']]['conversation'] = $arRes['CHAT_ID'];
					$arMessages[$arRes['ID']]['unread'] = $this->user_id != $arRes['AUTHOR_ID']? 'Y': 'N';
				}
				else
				{
					$arUsersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];
				}

				/*if ($arRes['R1_STATUS'] == IM_STATUS_UNREAD && (!isset($arMark[$arRes["CHAT_ID"]]) || $arMark[$arRes["CHAT_ID"]] < $arRes["ID"]))
					$arMark[$arRes["CHAT_ID"]] = $arRes["ID"];*/

				if (!isset($arLastMessage[$arRes["CHAT_ID"]]) || $arLastMessage[$arRes["CHAT_ID"]] < $arRes["ID"])
					$arLastMessage[$arRes["CHAT_ID"]] = $arRes["ID"];

				$arChat[$arRes["CHAT_ID"]] = $arRes["CHAT_ID"];
				$arMessageId[] = $arRes['ID'];
				$arMessageChatId[$arRes['CHAT_ID']][$arRes["ID"]] = $arRes["ID"];
			}
			$params = CIMMessageParam::Get($arMessageId);

			if ($bFileLoad)
			{
				foreach ($arMessageChatId as $chatId => $messages)
				{
					$files = Array();
					foreach ($messages as $messageId)
					{
						$arMessages[$messageId]['params'] = $params[$messageId];

						if (isset($params[$messageId]['FILE_ID']))
						{
							foreach ($params[$messageId]['FILE_ID'] as $fileId)
							{
								$files[$fileId] = $fileId;
							}
						}
					}

					$arMessageFiles = CIMDisk::GetFiles($chatId, $files);
					foreach ($arMessageFiles as $key => $value)
					{
						$arResult['files'][$chatId][$key] = $value;
					}
				}
			}
			else
			{
				foreach ($params as $messageId => $param)
				{
					$arMessages[$messageId]['params'] = $param;
				}
			}

			if (!empty($arMessages))
			{
				foreach ($arMark as $chatId => $lastSendId)
					CIMMessage::SetLastSendId($chatId, $this->user_id, $lastSendId);
			}

			if ($bGroupByChat)
			{
				foreach ($arMessages as $key => $value)
				{
					$arMessages[$arLastMessage[$value['conversation']]]['counter']++;
					if ($arLastMessage[$value['conversation']] != $value['id'])
					{
						unset($arMessages[$key]);
					}
					else
					{
						$arMessages[$key]['text'] = \Bitrix\Im\Text::parse($value['text']);
						$arMessages[$key]['textLegacy'] = \Bitrix\Im\Text::parseLegacyFormat($value['text']);

						$arUsersMessage[$value['conversation']][] = $value['id'];

						if ($value['params']['NOTIFY'] === 'N' || is_array($value['params']['NOTIFY']) && !in_array($this->user_id, $value['params']['NOTIFY']))
						{
							// skip unread
						}
						else if ($value['unread'] == 'Y')
						{
							$arUnreadMessage[$value['conversation']][] = $value['id'];
						}

						unset($arMessages[$key]['conversation']);
						unset($arMessages[$key]['unread']);
					}
				}
			}
			else
			{
				foreach ($arMessages as $key => $value)
				{
					$arMessages[$key]['text'] = \Bitrix\Im\Text::parse($value['text']);
					$arMessages[$key]['textLegacy'] = \Bitrix\Im\Text::parseLegacyFormat($value['text']);

					if ($value['params']['NOTIFY'] === 'N' || is_array($value['params']['NOTIFY']) && !in_array($this->user_id, $value['params']['NOTIFY']))
					{
						// skip unread
					}
					else if ($this->user_id != $value['senderId'])
					{
						$arUnreadMessage[$value['chatId']][] = $value['id'];
					}
				}
			}

			$arResult['message'] = $arMessages;
			$arResult['unreadMessage'] = $arUnreadMessage;
			$arResult['usersMessage'] = $arUsersMessage;

			$arChat = self::GetChatData(array(
				'ID' => $arChat,
				'USE_CACHE' => 'N'
			));
			if (!empty($arChat))
			{
				$arResult['lines'] = $arChat['lines'];
				$arResult['chat'] = $arChat['chat'];
				$arResult['userChatBlockStatus'] = $arChat['userChatBlockStatus'];
				$arResult['userInChat']  = $arChat['userInChat'];

				foreach ($arChat['userInChat'] as $value)
					$arUsers[] = $value;
			}

			if ($bUserLoad && !empty($arUsers))
			{
				$arUserData = CIMContactList::GetUserData(Array('ID' => array_diff(array_unique($arUsers), $arExistUserData), 'DEPARTMENT' => ($loadDepartment? 'Y': 'N')));
				$arResult['users'] = $arUserData['users'];
				$arResult['userInGroup'] = $arUserData['userInGroup'];
			}
			else
			{
				$arResult['users'] = Array();
				$arResult['userInGroup'] = Array();
				$arResult['userInGroup'] = Array();
			}

			$arResult['countMessage'] = CIMMessenger::GetMessageCounter($this->user_id, $arResult);
			if (!$bGroupByChat)
				CIMMessenger::SpeedFileCreate($this->user_id, $arResult['countMessage'], IM_SPEED_GROUP);
			$arResult['result'] = true;
		}
		else
		{
			$arResult['countMessage'] = CIMMessenger::GetMessageCounter($this->user_id, $arResult);
		}

		return $arResult;
	}

	public function SetOwner($chatId, $userId, $checkPermission = true)
	{
		$chatId = intval($chatId);
		$userId = intval($userId);

		$chat = IM\Model\ChatTable::getById($chatId)->fetch();
		if (!$chat)
		{
			return false;
		}

		if ($checkPermission && $chat['AUTHOR_ID'] != $this->user_id)
		{
			return false;
		}

		if ($userId == $chat['AUTHOR_ID'])
		{
			return true;
		}

		$arRelation = self::GetRelationById($chatId, false, true, false);
		if (!isset($arRelation[$userId]))
		{
			return false;
		}

		if ($this->user_id !== 0 && !self::canDo($chat, $arRelation, IM\V2\Permission\Action::ChangeOwner))
		{
			return false;
		}

		IM\Model\ChatTable::update($chatId, Array('AUTHOR_ID' => $userId));
		IM\Model\RelationTable::update($arRelation[$userId]['ID'], Array('MANAGER' => 'Y'));
		if (isset($arRelation[$chat['AUTHOR_ID']]))
		{
			IM\Model\RelationTable::update($arRelation[$chat['AUTHOR_ID']]['ID'], Array('MANAGER' => 'N'));
		}

		if (CModule::IncludeModule('pull'))
		{
			\Bitrix\Pull\Event::add(array_keys($arRelation), Array(
				'module_id' => 'im',
				'command' => 'chatOwner',
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
					'chatId' => (int)$chatId,
					'userId' => $userId
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public function SetDescription($chatId, $description)
	{
		$chatId = intval($chatId);
		$chat = Chat\GroupChat::getInstance($chatId);

		if (!$chat instanceof Chat\GroupChat)
		{
			return false;
		}

		if ($this->user_id !== 0 && !$chat->canDo(IM\V2\Permission\Action::ChangeDescription))
		{
			return false;
		}

		$fields = Chat\Update\UpdateFields::create(['DESCRIPTION' => $description]);
		(new Chat\Update\UpdateService($chat, $fields))->updateChat();
		$arRelation = $chat->getRelations()->getUserIds();

		if (CModule::IncludeModule('pull'))
		{
			\Bitrix\Pull\Event::add(array_keys($arRelation), Array(
				'module_id' => 'im',
				'command' => 'chatDescription',
				'params' => Array(
					'chatId' => $chatId,
					'description' => \Bitrix\Im\Text::parse($description)
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public static function SetChatParams($chatId, $params)
	{
		$update = [];
		if (isset($params['ENTITY_TYPE']))
			$update['ENTITY_TYPE'] = $params['ENTITY_TYPE'];

		if (isset($params['ENTITY_ID']))
			$update['ENTITY_ID'] = $params['ENTITY_ID'];

		if (isset($params['ENTITY_DATA_1']))
			$update['ENTITY_DATA_1'] = $params['ENTITY_DATA_1'];

		if (isset($params['ENTITY_DATA_2']))
			$update['ENTITY_DATA_2'] = $params['ENTITY_DATA_2'];

		if (isset($params['ENTITY_DATA_3']))
			$update['ENTITY_DATA_3'] = $params['ENTITY_DATA_3'];

		\Bitrix\Im\Model\ChatTable::update($chatId, $update);

		if (CModule::IncludeModule('pull'))
		{
			if (isset($update['NAME']))
			{
				$update['NAME'] = htmlspecialcharsbx($update['NAME']);
			}

			$arRelation = self::GetRelationById($chatId, false, true, false);
			\Bitrix\Pull\Event::add(array_keys($arRelation), Array(
				'module_id' => 'im',
				'command' => 'chatUpdateParams',
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
					'chatId' => (int)$chatId,
					'params' => array_change_key_case($update)
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public function SetManager($chatId, $userId, $isManager = true, $checkPermission = true)
	{
		return $this->SetManagers($chatId, Array($userId => $isManager), $checkPermission);
	}

	public function SetManagers($chatId, $users, $checkPermission = true)
	{
		$chatId = (int)$chatId;
		$chat = Chat::getInstance($chatId);

		if (!$chat instanceof Chat\GroupChat)
		{
			return false;
		}

		if (
			$checkPermission
			&& $this->user_id !== 0
			&& $chat->getAuthorId() !== $this->user_id
			&& !$chat->canDo(IM\V2\Permission\Action::ChangeManagers))
		{
			return false;
		}

		$chat->changeManagersByMap($users);

		return true;
	}

	public function SetColor($chatId, $color)
	{
		global $DB;
		$chatId = intval($chatId);
		$color = mb_strtoupper($color);

		if ($chatId <= 0 || !IM\Color::isSafeColor($color))
		{
			return false;
		}

		$groupTypes = self::implodeTypesForQuery(self::getGroupTypes());

		$strSql = "
			SELECT 
				R.CHAT_ID, 
				C.COLOR as CHAT_COLOR, 
				C.AUTHOR_ID as CHAT_AUTHOR_ID, 
				C.TYPE as CHAT_TYPE,
				C.ENTITY_TYPE as CHAT_ENTITY_TYPE, 
				C.ENTITY_ID as CHAT_ENTITY_ID,
				C.MANAGE_UI, 
				C.MANAGE_USERS_ADD, 
				C.MANAGE_USERS_DELETE, 
				C.MANAGE_SETTINGS 
			FROM 
				b_im_relation R 
				LEFT JOIN b_im_chat C 
					ON R.CHAT_ID = C.ID
			WHERE 
				R.USER_ID = ".$this->user_id." 
				AND R.MESSAGE_TYPE IN ({$groupTypes}) 
				AND R.CHAT_ID = ".$chatId;
		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
			if ($arRes['CHAT_COLOR'] == $color)
				return false;

			$ar = CIMChat::GetRelationById($chatId, false, true, false);

			if ($this->user_id !== 0 && !self::canDo($arRes, $ar, IM\V2\Permission\Action::ChangeColor))
			{
				return false;
			}

			IM\Model\ChatTable::update($chatId, array('COLOR' => $color));

			CIMChat::AddSystemMessage(Array(
				'CHAT_ID' => $chatId,
				'USER_ID' => $this->user_id,
				'MESSAGE_CODE' => 'IM_CHAT_CHANGE_COLOR_',
				'MESSAGE_REPLACE' => Array('#CHAT_COLOR#' => IM\Color::getName($color))
			));

			if ($arRes['CHAT_ENTITY_TYPE'] == 'LINES')
			{
				foreach ($ar as $rel)
				{
					if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($ar[$rel["USER_ID"]]);
					}
				}
			}

			if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				CIMContactList::CleanAllChatCache();
			}
			else
			{
				foreach ($ar as $rel)
				{
					CIMContactList::CleanChatCache($rel['USER_ID']);
				}
			}
			if (CModule::IncludeModule("pull"))
			{
				$arPushMessage = Array(
					'module_id' => 'im',
					'command' => 'chatChangeColor',
					'expiry' => 3600,
					'params' => Array(
						'chatId' => $chatId,
						'color' => IM\Color::getColor($color),
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				);
				\Bitrix\Pull\Event::add(array_keys($ar), $arPushMessage);
				if (CIMMessenger::needToSendPublicPull($arRes['CHAT_TYPE']))
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $arPushMessage);
				}
			}

			return true;
		}
		return false;
	}

	public function SetAvatarId($chatId, $fileId)
	{
		if ($chatId <= 0)
		{
			return false;
		}

		$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
		$chat = $orm->fetch();
		if (!$chat)
		{
			return false;
		}

		$relation = self::GetRelationById($chatId, false, true, false);

		if ($this->user_id !== 0 && !self::canDo($chat, $relation, IM\V2\Permission\Action::ChangeAvatar))
		{
			return false;
		}

		if ($fileId > 0)
		{
			$orm = \Bitrix\Main\FileTable::getById($fileId);
			$file = $orm->fetch();
			if (!$file)
				return false;

			if ($file['HEIGHT'] <= 0 || $file['WIDTH'] <= 0)
				return false;
		}

		IM\Model\ChatTable::update($chatId, Array('AVATAR' => $fileId));

		if (CModule::IncludeModule('pull'))
		{
			$users = [];
			foreach ($relation as $rel)
			{
				if ($rel["EXTERNAL_AUTH_ID"] != 'imconnector')
				{
					$users[$rel['USER_ID']] = $rel['USER_ID'];
				}
			}
			\Bitrix\Pull\Event::add($users, Array(
				'module_id' => 'im',
				'command' => 'chatAvatar',
				'params' => Array(
					'chatId' => $chatId,
					'avatar' => self::GetAvatarImage($fileId),
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public function Rename($chatId, $title, $checkPermission = true, $sendMessage = true)
	{
		global $DB;
		$chatId = intval($chatId);
		$title = mb_substr(trim($title), 0, 255);

		if ($chatId <= 0 || $title == '')
		{
			return false;
		}

		$groupTypes = self::implodeTypesForQuery(self::getGroupTypes());
		if ($checkPermission)
		{
			$strSql = "
				SELECT 
					R.CHAT_ID, 
					C.TITLE as CHAT_TITLE, 
					C.AUTHOR_ID as CHAT_AUTHOR_ID, 
					C.TYPE as CHAT_TYPE, 
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE, 
					C.ENTITY_ID as CHAT_ENTITY_ID, 
					R.MANAGER as IS_MANAGER, 
					C.MANAGE_UI, 
					C.MANAGE_USERS_ADD, 
					C.MANAGE_USERS_DELETE, 
					C.MANAGE_SETTINGS 
				FROM b_im_relation R LEFT JOIN b_im_chat C ON R.CHAT_ID = C.ID
				WHERE 
					R.USER_ID = ".$this->user_id." 
					AND R.MESSAGE_TYPE IN ({$groupTypes}) 
					AND R.CHAT_ID = ".$chatId;
		}
		else
		{
			$strSql = "
				SELECT 
					C.ID as CHAT_ID, 
					C.TITLE as CHAT_TITLE, 
					C.AUTHOR_ID as CHAT_AUTHOR_ID, 
					C.TYPE as CHAT_TYPE, 
					C.ENTITY_TYPE as CHAT_ENTITY_TYPE, 
					C.ENTITY_ID as CHAT_ENTITY_ID, 
					'Y' as IS_MANAGER, 
					C.MANAGE_UI, 
					C.MANAGE_USERS_ADD, 
					C.MANAGE_USERS_DELETE, 
					C.MANAGE_SETTINGS 
				FROM b_im_chat C
				WHERE 
					C.ID = ".$chatId." 
					AND C.TYPE IN ({$groupTypes})";
		}
		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
		{
			$ar = CIMChat::GetRelationById($chatId, false, true, false);

			if ($this->user_id !== 0 && $checkPermission && !self::canDo($arRes, $ar, IM\V2\Permission\Action::Rename))
			{
				return false;
			}

			$arRes['CHAT_TITLE'] = \Bitrix\Im\Text::decodeEmoji($arRes['CHAT_TITLE']);

			if ($arRes['CHAT_TITLE'] == $title)
				return false;

			if ($arRes['CHAT_ENTITY_TYPE'] === 'ANNOUNCEMENT' && $arRes['IS_MANAGER'] !== 'Y')
			{
				return false;
			}

			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

			IM\Model\ChatTable::update($chatId, array('TITLE' => $title));

			if ($sendMessage)
			{
				if ($checkPermission)
				{
					if (
						$arRes['CHAT_TYPE'] === Chat::IM_TYPE_CHANNEL
						|| $arRes['CHAT_TYPE'] === Chat::IM_TYPE_OPEN_CHANNEL
					)
					{
						$type = 'CHANNEL';
						$messageCodeVersion = '';
					}
					else
					{
						$type = 'CHAT';
						$messageCodeVersion = '_MSGVER_1';
					}

					CIMChat::AddSystemMessage(Array(
						'CHAT_ID' => $chatId,
						'USER_ID' => $this->user_id,
						'MESSAGE_CODE' => "IM_{$type}_CHANGE_TITLE_",
						'MESSAGE_REPLACE' => Array('#CHAT_TITLE#' => $title),
						'MESSAGE_CODE_VERSION' => $messageCodeVersion,
					));
				}
				else
				{
					self::AddMessage(Array(
						"TO_CHAT_ID" => $chatId,
						"MESSAGE" => GetMessage("IM_CHAT_CHANGE_TITLE", Array('#CHAT_TITLE#' => $title)),
						"SYSTEM" => 'Y',
						"SKIP_USER_CHECK" => 'Y',
					));
				}
			}

			if ($arRes['CHAT_ENTITY_TYPE'] == 'LINES')
			{
				foreach ($ar as $rel)
				{
					if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($ar[$rel["USER_ID"]]);
					}
				}
			}

			if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				CIMContactList::CleanAllChatCache();
			}
			else
			{
				foreach ($ar as $rel)
				{
					CIMContactList::CleanChatCache($rel['USER_ID']);
				}
			}
			if (CModule::IncludeModule("pull"))
			{
				$pushMessage = Array(
					'module_id' => 'im',
					'command' => 'chatRename',
					'params' => Array(
						'chatId' => $chatId,
						'name' => \Bitrix\Main\Text\Emoji::decode($title),
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				);
				\Bitrix\Pull\Event::add(array_keys($ar), $pushMessage);
				if (CIMMessenger::needToSendPublicPull($arRes['CHAT_TYPE']))
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
				}
				if ($arRes['CHAT_TYPE'] === Chat::IM_TYPE_OPEN_CHANNEL)
				{
					Chat\OpenChannelChat::sendSharedPull($pushMessage);
				}
			}

			foreach(GetModuleEvents("im", "OnChatRename", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, array($chatId, $title, $arRes['CHAT_ENTITY_TYPE'], $arRes['CHAT_ENTITY_ID'], $this->user_id));
			}

			return true;
		}
		return false;
	}

	/**
	 * @param $arParams
	 * @return array|false|int
	 * @throws Exception
	 *
	 * @deprecated Use Bitrix\Im\V2\Chat\ChatFactory::addChat()
	 * @see Bitrix\Im\V2\Chat\ChatFactory::addChat()
	 */
	public function Add($arParams)
	{
		$type = '';
		if (isset($arParams['MESSAGE_TYPE']))
		{
			$type = $arParams['MESSAGE_TYPE'];
		}
		elseif (isset($arParams['TYPE']))
		{
			$type = $arParams['MESSAGE_TYPE'] = $arParams['TYPE'];
		}

		$arParams['USER_ID'] = $this->user_id;

		$authorId = $arParams['USER_ID'];
		if (isset($arParams['AUTHOR_ID']))
		{
			$authorId = intval($arParams['AUTHOR_ID']);
		}

		if (isset($arParams['OWNER_ID']))
		{
			$authorId = intval($arParams['OWNER_ID']);
		}

		if ($authorId)
		{
			$arParams['AUTHOR_ID'] = $authorId;
		}

		if (isset($arParams['AVATAR_ID']))
		{
			$arParams['AVATAR'] = (int) $arParams['AVATAR_ID'];
		}

		$chatResult = IM\V2\Chat\ChatFactory::getInstance()->addChat($arParams);
		if (!$chatResult->isSuccess() || !$chatResult->hasResult())
		{
			return false;
		}

		if (isset($arParams['MESSAGE']))
		{
			$message = trim($arParams['MESSAGE']);
		}

		if (isset($message) && $message)
		{
			if ($type === IM\V2\Chat::IM_TYPE_PRIVATE)
			{
				CIMMessage::Add([
					"FROM_USER_ID" => $this->user_id,
					"TO_USER_ID" => $this->user_id,
					"MESSAGE" => $message,
				]);
			}
			else
			{
				self::AddMessage([
					"TO_CHAT_ID" => $chatResult->getResult()['CHAT_ID'],
					"FROM_USER_ID" => $this->user_id,
					"SYSTEM" => $this->user_id ? 'N' : 'Y',
					"MESSAGE" => $message,
				]);
			}
		}

		$chat = $chatResult->getResult()['CHAT'];

		CIMContactList::CleanAllChatCache();

		return $chat->getChatId();
	}

	public static function AddMessage($arFields)
	{
		$arFields['MESSAGE_TYPE'] = IM_MESSAGE_CHAT;

		return CIMMessenger::Add($arFields);
	}

	public function Join($chatId)
	{
		$chatId = intval($chatId);
		if ($chatId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_CHAT_ID"), "EMPTY_CHAT_ID");
			return false;
		}

		$orm = IM\Model\ChatTable::getById($chatId);
		if (!($chatData = $orm->fetch()))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_CHAT_NOT_EXISTS"), "ERROR_CHAT_NOT_EXISTS");
			return false;
		}

		if ($chatData['TYPE'] != IM_MESSAGE_OPEN)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_ACCESS_JOIN"), "ACCESS_JOIN");
			return false;
		}

		if (IM\User::getInstance($this->user_id)->isExtranet())
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_ACCESS_JOIN"), "ACCESS_JOIN");
			return false;
		}

		$chat = new CIMChat(0);
		$chat->AddUser($chatId, $this->user_id);

		return true;
	}

	public function JoinParent($chatId, $messageId)
	{
		$chatId = intval($chatId);
		if ($chatId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_CHAT_ID"), "EMPTY_CHAT_ID");
			return false;
		}

		$messageId = intval($messageId);
		if ($messageId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_CHAT_ID"), "EMPTY_CHAT_ID");
			return false;
		}

		$CIMMessage = new CIMMessage($this->user_id);
		$message = $CIMMessage->GetMessage($messageId, true);
		if (!$message)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_ACCESS_JOIN"), "ACCESS_JOIN");
			return false;
		}

		$relations = IM\Chat::getRelation($chatId, ['WITHOUT_COUNTERS' => 'Y']);
		if (!isset($relations[$this->user_id]))
		{
			$chat = new CIMChat(0);
			$chat->AddUser($chatId, $this->user_id);
		}

		return true;
	}

	public function AddUser($chatId, $userId, $hideHistory = null, $skipMessage = false, $skipRecent = false, $skipRelation = false)
	{
		$chatId = intval($chatId);
		if ($chatId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_CHAT_ID"), "EMPTY_CHAT_ID");
			return false;
		}

		$arUserId = Array();
		if (is_array($userId))
		{
			$arUserId = \CIMContactList::PrepareUserIds($userId);
		}
		else if (intval($userId) > 0)
		{
			$arUserId[intval($userId)] = intval($userId);
		}
		if ($this->user_id > 0)
		{
			$arUserId[$this->user_id] = $this->user_id;
		}

		if (count($arUserId) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_USER_ID"), "EMPTY_USER_ID");
			return false;
		}

		$chat = Chat::getInstance($chatId)->withContextUser($this->user_id);

		if ($this->user_id !== 0 && !$chat->canDo(IM\V2\Permission\Action::Extend))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_CHAT_ACCESS_DENIED_EXTEND_USERS"), "ACCESS_DENIED_EXTEND");
			return false;
		}

		$config = new IM\V2\Relation\AddUsersConfig([], $hideHistory, !$skipMessage, $skipRecent, $skipRelation);
		$chat->addUsers($arUserId, $config);

		return true;
	}

	public function MuteNotify($chatId, $mute = true)
	{
		return \Bitrix\Im\Chat::mute($chatId, $mute, $this->user_id);
	}

	public function DeleteUser(
		$chatId,
		$userId,
		$checkPermission = true,
		$skipMessage = false,
		$skipRecent = false,
		$withoutRead = false,
		$additionalParams = []
	)
	{
		$chatId = (int)$chatId;
		$userId = (int)$userId;

		if ($chatId <= 0 || $userId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_USER_OR_CHAT"), "EMPTY_USER_OR_CHAT");

			return false;
		}

		$chat = Chat::getInstance($chatId)->withContextUser($this->user_id);

		if ($this->user_id !== 0 && !$chat->canDo(IM\V2\Permission\Action::Kick, $userId))
		{
			if ($this->user_id === $userId && $chat->getAuthorId() === $userId)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_LEAVE_OWNER_FORBIDDEN"), "LEAVE_OWNER_FORBIDDEN");
			}
			else
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_CHAT_ACCESS_DENIED_KICK_USERS"), "ACCESS_DENIED_KICK");
			}

			return false;
		}

		if ($checkPermission && $this->user_id !== $userId && $chat->getAuthorId() !== $this->user_id)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_KICK"), "IM_ERROR_KICK");

			return false;
		}

		$config = new IM\V2\Relation\DeleteUserConfig(
			!$skipMessage,
			$skipRecent,
			true,
			false,
			$withoutRead
		);
		$result = $chat->deleteUser($userId, $config);

		if ($result->getError() !== null)
		{
			if ($result->getError()?->getCode() === UserError::NOT_FOUND)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_USER_NOT_FOUND"), UserError::NOT_FOUND);

				return false;
			}

			$GLOBALS["APPLICATION"]->ThrowException($result->getError()->getMessage(), $result->getError()->getCode());

			return false;
		}

		return true;
	}

	public static function GetAvatarImage($id, $size = 200, $addBlankPicture = true)
	{
		$url = $addBlankPicture? '/bitrix/js/im/images/blank.gif': '';

		$id = intval($id);
		if ($id > 0 && $size > 0)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$id,
				array('width' => $size, 'height' => $size),
				BX_RESIZE_IMAGE_EXACT,
				false,
				false,
				true
			);
			if (!empty($arFileTmp['src']))
			{
				$url = $arFileTmp['src'];
			}
		}
		return $url;
	}

	public static function AddSystemMessage($params)
	{
		$chatId = intval($params['CHAT_ID']);
		if ($chatId <= 0)
			return false;

		$arUser = false;
		$userId = intval($params['USER_ID']);
		if ($userId > 0)
		{
			$arSelect = Array("ID", "LAST_NAME", "NAME", "LOGIN", "SECOND_NAME", "PERSONAL_GENDER");
			$dbUsers = CUser::GetList('', '', array('ID_EQUAL_EXACT' => $userId), array('FIELDS' => $arSelect));
			if ($arUser = $dbUsers->Fetch())
			{
				$arUser['NAME'] = \Bitrix\Im\User::formatFullNameFromDatabase($arUser);
				$arUser['PERSONAL_GENDER'] = $arUser["PERSONAL_GENDER"] == 'F'? 'F': 'M';
			}
		}

		if (isset($params['MESSAGE_CODE']))
		{
			$messageCodeVersion = $params['MESSAGE_CODE_VERSION'] ?? '';
			$messageReplace = is_array($params['MESSAGE_REPLACE'])? $params['MESSAGE_REPLACE']: Array();
			if ($arUser)
			{
				$messageReplace['#USER_NAME#'] = $arUser['NAME'];
				$message = GetMessage($params['MESSAGE_CODE'].$arUser['PERSONAL_GENDER'].$messageCodeVersion, $messageReplace);
			}
			else
			{
				$message = GetMessage($params['MESSAGE_CODE'], $messageReplace);
			}
		}
		else
		{
			$messageReplace = is_array($params['MESSAGE_REPLACE'])? $params['MESSAGE_REPLACE']: Array();
			$message = trim($params['MESSAGE']);
			if ($message <> '' && !empty($messageReplace))
			{
				$message = str_replace(array_keys($messageReplace), array_values($messageReplace), $message);
			}
		}
		if ($message == '')
			return false;

		return self::AddMessage(Array(
			"TO_CHAT_ID" => $chatId,
			"FROM_USER_ID" => $userId,
			"MESSAGE" => $message,
			"SYSTEM" => 'Y',
			"SKIP_USER_CHECK" => 'Y',
		));
	}

	public static function CheckRestriction($chatId, $action)
	{
		if (is_int($chatId))
		{
			if (self::GetGeneralChatId() == $chatId)
			{
				$chat['ENTITY_TYPE'] = 'GENERAL';
			}
			else
			{
				$chat = IM\Model\ChatTable::getById($chatId)->fetch();
				if (!$chat)
					return true;
			}
		}
		else if(is_array($chatId))
		{
			$chat = $chatId;
		}
		else
		{
			return true;
		}

		$options = self::GetChatOptions();
		if (isset($options[$chat['ENTITY_TYPE']][$action]) && !self::$entityOption[$chat['ENTITY_TYPE']][$action])
		{
			return true;
		}

		return false;
	}

	public static function GetChatOptions()
	{
		if (!is_null(self::$entityOption))
		{
			return self::$entityOption;
		}

		global $USER;

		self::$entityOption = [];

		$default = [
			'AVATAR' => true,
			'RENAME' => true,
			'EXTEND' => true,
			'CALL' => true,
			'MUTE' => true,
			'LEAVE' => true,
			'LEAVE_OWNER' => true,
			'SEND' => true,
			'USER_LIST' => true,
		];

		self::$entityOption['GENERAL'] = [
			'AVATAR' => false,
			'RENAME' => false,
			'EXTEND' => false,
			'CALL' => true,
			'LEAVE' => false,
			'LEAVE_OWNER' => false,
			'SEND' => CIMChat::CanSendMessageToGeneralChat(($USER instanceof \CUser) ? (int)$USER->GetID() : null)
		];

		if (\Bitrix\Main\Loader::includeModule('imbot'))
		{
			self::$entityOption[\Bitrix\ImBot\Service\Notifier::CHAT_ENTITY_TYPE] = [
				'AVATAR' => false,
				'RENAME' => false,
				'LEAVE_OWNER' => false,
			];
			self::$entityOption[\Bitrix\ImBot\Bot\Support24::CHAT_ENTITY_TYPE] = [
				'AVATAR' => false,
				'RENAME' => true,
				'EXTEND' => false,
				'CALL' => false,
				'MUTE' => false,
				'LEAVE' => false,
				'LEAVE_OWNER' => false,
				'USER_LIST' => false,
			];
			self::$entityOption[\Bitrix\ImBot\Bot\Network::CHAT_ENTITY_TYPE] =
				self::$entityOption[\Bitrix\ImBot\Bot\Support24::CHAT_ENTITY_TYPE]
			;
		}

		if (\Bitrix\Main\ModuleManager::isModuleInstalled('socialnetwork'))
		{
			$path = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);
			$path = $path.'group/#ID#/';

			self::$entityOption['SONET_GROUP'] = [
				'AVATAR' => false,
				'RENAME' => false,
				'EXTEND' => false,
				'LEAVE' => false,
				'LEAVE_OWNER' => false,
				'PATH' => $path,
				'PATH_TITLE' => GetMessage('IM_PATH_TITLE_SONET')
			];
		}

		if (\Bitrix\Main\Loader::includeModule('tasks'))
		{
			$path = \CTasksTools::GetOptionPathTaskUserEntry(SITE_ID, "/company/personal/user/#user_id#/tasks/task/view/#task_id#/");
			$path = str_replace(Array('#user_id#', '#task_id#'), Array(($USER instanceof \CUser) ? (int)$USER->GetId() : 0, '#ID#'), mb_strtolower($path));

			self::$entityOption['TASKS'] = Array(
				'AVATAR' => true,
				'RENAME' => true,
				'EXTEND' => true,
				'LEAVE' => true,
				'LEAVE_OWNER' => true,
				'PATH' => $path,
				'PATH_TITLE' => GetMessage('IM_PATH_TITLE_TASKS')
			);
		}

		if (\Bitrix\Main\Loader::includeModule('calendar'))
		{
			$path = \CCalendar::GetPathForCalendarEx(($USER instanceof \CUser) ? (int)$USER->GetId() : 0);
			$path = \CHTTP::urlAddParams($path, ['EVENT_ID' => '#ID#']);

			self::$entityOption[\CCalendar::CALENDAR_CHAT_ENTITY_TYPE] = Array(
				'AVATAR' => true,
				'RENAME' => true,
				'EXTEND' => true,
				'LEAVE' => true,
				'LEAVE_OWNER' => true,
				'PATH' => $path,
				'PATH_TITLE' => GetMessage('IM_PATH_TITLE_CALENDAR_EVENT')
			);
		}

		if (\Bitrix\Main\Loader::includeModule('crm'))
		{
			self::$entityOption['CRM'] = Array(
				'AVATAR' => false,
				'RENAME' => false,
				'EXTEND' => true,
				'LEAVE' => true,
				'LEAVE_OWNER' => false,
				'PATH' => '',
				'PATH_TITLE' => ''
			);
		}

		if (\Bitrix\Main\Loader::includeModule('voximplant'))
		{
			self::$entityOption['CALL'] = Array(
				'AVATAR' => true,
				'RENAME' => true,
				'EXTEND' => false,
				'CALL' => false,
				'MUTE' => false,
				'LEAVE' => false,
				'LEAVE_OWNER' => false,
				'SEND' => true,
				'USER_LIST' => true,
			);
		}

		foreach (self::$entityOption as $code => $value)
		{
			self::$entityOption[$code] = array_merge($default, $value);
		}

		self::$entityOption['DEFAULT'] = $default;

		return self::$entityOption;
	}

	public static function GetSonetGroupChatId($groupId)
	{
		if (!CModule::IncludeModule('socialnetwork'))
			return false;

		$chatData = \Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup::getChatData(Array(
			'group_id' => $groupId,
			'skipAvailabilityCheck' => true
		));
		if (!empty($chatData[$groupId]) && intval($chatData[$groupId]) > 0)
		{
			$chatId = $chatData[$groupId];
		}
		else
		{
			$chatId = \Bitrix\Socialnetwork\Integration\Im\Chat\Workgroup::createChat(Array(
				'group_id' => $groupId
			));
		}

		return $chatId;
	}

	public static function GetCrmChatId($code)
	{
		if (!CModule::IncludeModule('crm'))
			return false;

		[$entityType, $entityId] = explode('|', $code);

		global $USER;

		$chatId = \Bitrix\Crm\Integration\Im\Chat::joinChat(Array(
			'ENTITY_TYPE' => $entityType,
			'ENTITY_ID' => $entityId,
			'USER_ID' => $USER->GetId()
		));

		return $chatId;
	}

	public static function GetUserCount($chatId)
	{
		$result = \Bitrix\Im\Model\ChatTable::getList(
			[
				'select' => ['USER_COUNT'],
				'filter' => ['=ID' => $chatId]
			]
		)->fetch();

		if (!$result)
		{
			return false;
		}

		return (int)$result['USER_COUNT'];
	}

	public static function GetEntityChat($entityType, $entityId)
	{
		$entityType = trim($entityType);
		$entityId = trim($entityId);

		if (empty($entityType) || empty($entityId))
		{
			return false;
		}

		$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
			'select' => ['ID'],
			'filter' => [
				'=ENTITY_TYPE' => $entityType,
				'=ENTITY_ID' => $entityId,
			]
		))->fetch();

		if (!$chatData)
		{
			return false;
		}

		return $chatData['ID'];
	}

	public static function DeleteEntityChat($entityType, $entityId)
	{
		$entityType = trim($entityType);
		$entityId = trim($entityId);

		if (empty($entityType) || empty($entityId))
		{
			return false;
		}

		$chatData = \Bitrix\Im\Model\ChatTable::getList(Array(
			'select' => ['ID', 'DISK_FOLDER_ID'],
			'filter' => [
				'=ENTITY_TYPE' => $entityType,
				'=ENTITY_ID' => $entityId,
			],
		))->fetch();
		if (!$chatData)
		{
			return false;
		}

		self::deleteChat($chatData);

		return true;
	}

	public static function deleteChat(array $chatData): void
	{
		global $DB;

		self::hide($chatData['ID']);

		$strSQL = "DELETE FROM b_im_chat WHERE ID = ".$chatData['ID'];
		$DB->Query($strSQL, true);

		$strSQL = "DELETE FROM b_im_relation WHERE CHAT_ID = ".$chatData['ID'];
		$DB->Query($strSQL, true);

		$strSQL = "DELETE FROM b_im_message WHERE CHAT_ID = ".$chatData['ID'];
		$DB->Query($strSQL, true);

		IM\V2\Link\Url\UrlCollection::deleteByChatsIds([(int)$chatData['ID']]);
		IM\V2\Chat::cleanCache((int)$chatData['ID']);

		if ($chatData['DISK_FOLDER_ID'])
		{
			$folderModel = \Bitrix\Disk\Folder::getById($chatData['DISK_FOLDER_ID']);
			if ($folderModel)
			{
				$folderModel->deleteTree(\Bitrix\Disk\SystemUser::SYSTEM_USER_ID);
			}
		}
	}

	public static function hide($chatId)
	{
		$pushList = [];
		$lines = false;
		$relations = \CIMChat::GetRelationById($chatId, false, true, false);
		foreach($relations as $userId => $relation)
		{
			if ($relation['MESSAGE_TYPE'] === Chat::IM_TYPE_OPEN_LINE)
			{
				$lines = true;
			}
			\CIMContactList::DeleteRecent($chatId, true, $userId);

			if (!\Bitrix\Im\User::getInstance($userId)->isConnector())
			{
				$pushList[] = $userId;
			}
		}

		if (
			!empty($pushList)
			&& \Bitrix\Main\Loader::includeModule("pull")
		)
		{
			\Bitrix\Pull\Event::add($pushList, Array(
				'module_id' => 'im',
				'command' => 'chatHide',
				'expiry' => 3600,
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
					'chatId' => $chatId,
					'lines' => $lines
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public static function index($chatId)
	{
		$index =
			\Bitrix\Im\Internals\ChatIndex::create()
				->setChatId((int)$chatId)
		;
		static::fillChatIndexWithUserFullNames($index);

		\Bitrix\Im\Model\ChatTable::indexRecord($index);

		return true;
	}

	public static function addChatIndex(int $chatId, string $chatTitle)
	{
		$index =
			\Bitrix\Im\Internals\ChatIndex::create()
				->setChatId($chatId)
				->setTitle($chatTitle)
		;
		static::fillChatIndexWithUserFullNames($index);

		\Bitrix\Im\Model\ChatTable::addIndexRecord($index);

		return true;
	}

	public static function updateChatIndex($chatId)
	{
		$index =
			\Bitrix\Im\Internals\ChatIndex::create()
				->setChatId((int)$chatId)
		;
		static::fillChatIndexWithUserFullNames($index);

		\Bitrix\Im\Model\ChatTable::updateIndexRecord($index);

		return true;
	}

	public static function fillChatIndexWithUserFullNames(\Bitrix\Im\Internals\ChatIndex $index)
	{
		$query =
			\Bitrix\Im\Model\RelationTable::query()
				->addSelect('USER_ID')
				->where('CHAT_ID', $index->getChatId())
				->setLimit(100)
		;

		$users = [];
		foreach ($query->exec() as $relation)
		{
			$users[] = \Bitrix\Im\User::getInstance($relation['USER_ID'])->getFullName(false);
		}

		$index->setUserList($users);
	}

	public static function getNextConferenceDefaultTitle()
	{
		$counter = CGlobalCounter::GetValue('im_videoconf_count', CGlobalCounter::ALL_SITES) + 1;

		return GetMessage('IM_VIDEOCONF_NAME_FORMAT_NEW', [
			'#NUMBER#' => $counter
		]);
	}

	public static function isNewChat(string $type, ?DateTime $dateCreate): bool
	{
		if ($type !== Chat::IM_TYPE_OPEN_CHANNEL || $dateCreate === null)
		{
			return false;
		}

		$lastDay = (new DateTime())->add('-1 day');

		return $dateCreate->getTimestamp() > $lastDay->getTimestamp();
	}

	public static function canDo(array $chatData, array $relations, IM\V2\Permission\Action $action, mixed $target = null): bool
	{
		$chatData['RELATIONS'] = new IM\V2\RelationCollection($relations);

		return self::initChatByArray($chatData)->canDo($action, $target);
	}

	public static function initChatByArray(array $chatData): Chat
	{
		$preparedChatData = $chatData;
		$preparedChatData['ID'] = $chatData['ID'] ?? $chatData['CHAT_ID'] ?? $chatData['ITEM_CID'] ?? null;
		$preparedChatData['TYPE'] = $chatData['TYPE'] ?? $chatData['CHAT_TYPE'] ?? null;
		$preparedChatData['ENTITY_TYPE'] = $chatData['ENTITY_TYPE'] ?? $chatData['CHAT_ENTITY_TYPE'] ?? null;
		$preparedChatData['ENTITY_ID'] = $chatData['ENTITY_ID'] ?? $chatData['CHAT_ENTITY_ID'] ?? null;
		$preparedChatData['ENTITY_DATA_1'] = $chatData['ENTITY_DATA_1'] ?? $chatData['CHAT_ENTITY_DATA_1'] ?? null;
		$preparedChatData['ENTITY_DATA_2'] = $chatData['ENTITY_DATA_2'] ?? $chatData['CHAT_ENTITY_DATA_2'] ?? null;
		$preparedChatData['ENTITY_DATA_3'] = $chatData['ENTITY_DATA_3'] ?? $chatData['CHAT_ENTITY_DATA_3'] ?? null;
		$preparedChatData['DATE_CREATE'] = $chatData['DATE_CREATE'] ?? $chatData['CHAT_DATE_CREATE'] ?? null;

		return Chat\ChatFactory::getInstance()->initChat($preparedChatData);
	}

	public static function getGroupTypes(): array
	{
		return [
			Chat::IM_TYPE_OPEN,
			Chat::IM_TYPE_CHAT,
			Chat::IM_TYPE_OPEN_LINE,
			Chat::IM_TYPE_COPILOT,
			Chat::IM_TYPE_CHANNEL,
			Chat::IM_TYPE_OPEN_CHANNEL,
			Chat::IM_TYPE_COLLAB,
		];
	}

	public static function getGroupTypesExtra(): array
	{
		$types = self::getGroupTypes();
		$types[] = Chat::IM_TYPE_COMMENT;

		return $types;
	}

	public static function implodeTypesForQuery(array $types): string
	{
		return "'" . implode("','", $types) . "'";
	}
}
