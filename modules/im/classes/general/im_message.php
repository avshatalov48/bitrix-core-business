<?

use Bitrix\Im\V2\Chat;
use Bitrix\Main\Application;
use Bitrix\Im\V2\Sync;
use Bitrix\Main\Engine\Response\Converter;

IncludeModuleLangFile(__FILE__);

class CIMMessage
{
	private $user_id = 0;
	private $bHideLink = false;

	function __construct($user_id = false, $arParams = Array())
	{
		global $USER;
		$this->user_id = intval($user_id);
		if ($this->user_id == 0)
			$this->user_id = intval($USER->GetID());
		if (isset($arParams['HIDE_LINK']) && $arParams['HIDE_LINK'] == 'Y')
			$this->bHideLink = true;
	}

	public static function Add($arFields)
	{
		if (!isset($arFields['MESSAGE_TYPE']) || !in_array($arFields['MESSAGE_TYPE'], Array(IM_MESSAGE_CHAT, IM_MESSAGE_OPEN, IM_MESSAGE_OPEN_LINE, Chat::IM_TYPE_COPILOT, Chat::IM_TYPE_CHANNEL, Chat::IM_TYPE_OPEN_CHANNEL, Chat::IM_TYPE_COMMENT)))
			$arFields['MESSAGE_TYPE'] = IM_MESSAGE_PRIVATE;

		if (isset($arFields['MESSAGE_MODULE']))
			$arFields['NOTIFY_MODULE'] = $arFields['MESSAGE_MODULE'];
		else
			$arFields['NOTIFY_MODULE'] = "im";

		return CIMMessenger::Add($arFields);
	}

	public function GetMessage($id, $files = false)
	{
		global $DB;

		$id = intval($id);

		$query = "SELECT 
					M.*, 
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE, 
					R.MESSAGE_TYPE,
					C.TITLE as CHAT_TITLE,
					C.COLOR as CHAT_COLOR,
					C.AVATAR as CHAT_AVATAR 
				FROM 
					b_im_message M
					INNER JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$this->user_id."
					INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
				WHERE 
					M.ID = ".$id."";

		$result = $DB->Query($query);
		$message = $result->Fetch();

		if (!$message)
			return false;

		if ($files)
		{
			$files = CIMMessageParam::Get($id, 'FILE_ID');
			$message['FILES'] = CIMDisk::GetFiles($message['CHAT_ID'], $files, false);
		}

		return $message;
	}

	public static function UpdateMessageOut($id, $messageOut)
	{
		$id = intval($id);
		if ($id <= 0)
			return false;

		\Bitrix\Im\Model\MessageTable::update($id, array(
			"MESSAGE_OUT" => $messageOut,
		));

		return true;
	}

	public function GetUnreadMessage($arParams = Array())
	{
		global $DB;

		$bSpeedCheck = isset($arParams['SPEED_CHECK']) && $arParams['SPEED_CHECK'] == 'N'? false: true;
		//$lastId = !isset($arParams['LAST_ID']) || $arParams['LAST_ID'] == null? null: intval($arParams['LAST_ID']);
		$loadDepartment = isset($arParams['LOAD_DEPARTMENT']) && $arParams['LOAD_DEPARTMENT'] == 'N'? false: true;
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;
		$bGroupByChat = isset($arParams['GROUP_BY_CHAT']) && $arParams['GROUP_BY_CHAT'] == 'Y'? true: false;
		$bUserLoad = isset($arParams['USER_LOAD']) && $arParams['USER_LOAD'] == 'N'? false: true;
		$bFileLoad = isset($arParams['FILE_LOAD']) && $arParams['FILE_LOAD'] == 'N'? false: true;
		$arExistUserData = isset($arParams['EXIST_USER_DATA']) && is_array($arParams['EXIST_USER_DATA'])? $arParams['EXIST_USER_DATA']: Array();

		$arMessages = Array();
		$arUnreadMessage = Array();
		$arUsersMessage = Array();

		$arResult = Array(
			'message' => Array(),
			'unreadMessage' => Array(),
			'usersMessage' => Array(),
			'users' => Array(),
			'files' => Array(),
			'userInGroup' => Array(),
			'countMessage' => 0,
			'result' => false
		);
		$bLoadMessage = $bSpeedCheck? CIMMessenger::SpeedFileExists($this->user_id, IM_SPEED_MESSAGE): false;
		$count = CIMMessenger::SpeedFileGet($this->user_id, IM_SPEED_MESSAGE);
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
						R1.USER_ID = ".$this->user_id." AND R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."' ".$ssqlStatus."
				";
				$dbSubRes = $DB->Query($strSql);
				while ($arRes = $dbSubRes->Fetch())
				{
					$arRelations[] = $arRes;
				}
			}*/

			$arLastMessage = Array();
			$arMark = Array();
			$arMessageId = Array();
			$arMessageChatId = Array();

			$diskFolderId = 0;

			if (!$bTimeZone)
				CTimeZone::Disable();
			$strSql ="
					SELECT
						M.ID,
						M.CHAT_ID,
						C.TYPE as CHAT_TYPE,
						C.DISK_FOLDER_ID,
						M.MESSAGE,
						".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
						M.AUTHOR_ID,
						M.NOTIFY_EVENT,
						R1.USER_ID as R1_USER_ID,
						M.AUTHOR_ID as R2_USER_ID
					FROM b_im_message M
						LEFT JOIN b_im_chat C ON C.ID = M.CHAT_ID
						INNER JOIN b_im_relation R1 ON M.CHAT_ID = R1.CHAT_ID AND R1.USER_ID != M.AUTHOR_ID
						INNER JOIN b_im_message_unread MU ON M.ID = MU.MESSAGE_ID AND MU.USER_ID = " . $this->user_id . "
					WHERE R1.USER_ID = ".$this->user_id." AND R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
				";
			if (!$bTimeZone)
				CTimeZone::Enable();

			$strSql = $DB->TopSql($strSql, 500);

			$dbRes = $DB->Query($strSql);

			while ($arRes = $dbRes->Fetch())
			{
				if ($arRes['CHAT_TYPE'] && $arRes['CHAT_TYPE'] != IM_MESSAGE_PRIVATE)
					continue;

				$diskFolderId = $arRes['DISK_FOLDER_ID'];
				$arUsers[] = $arRes['R1_USER_ID'];
				$arUsers[] = $arRes['R2_USER_ID'];
				if ($this->user_id == $arRes['AUTHOR_ID'])
				{
					$arRes['TO_USER_ID'] = $arRes['R2_USER_ID'];
					$arRes['FROM_USER_ID'] = $arRes['R1_USER_ID'];
					$convId = $arRes['TO_USER_ID'];
				}
				else
				{
					$arRes['TO_USER_ID'] = $arRes['R1_USER_ID'];
					$arRes['FROM_USER_ID'] = $arRes['R2_USER_ID'];
					$convId = $arRes['FROM_USER_ID'];
				}

				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['FROM_USER_ID'],
					'recipientId' => $arRes['TO_USER_ID'],
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'system' => $arRes['NOTIFY_EVENT'] == 'private'? 'N': 'Y',
					'text' => $arRes['MESSAGE'],
				);
				if ($bGroupByChat)
				{
					$arMessages[$arRes['ID']]['conversation'] = $convId;
					$arMessages[$arRes['ID']]['unread'] = $this->user_id != $arRes['AUTHOR_ID']? 'Y': 'N';
				}
				else
				{
					$arMessages[$arRes['ID']]['conversation'] = $convId;
					$arUsersMessage[$convId][] = $arRes['ID'];
				}

				/*if ($arRes['R1_STATUS'] == IM_STATUS_UNREAD && (!isset($arMark[$arRes["CHAT_ID"]]) || $arMark[$arRes["CHAT_ID"]] < $arRes["ID"]))
					$arMark[$arRes["CHAT_ID"]] = $arRes["ID"];*/

				if (!isset($arLastMessage[$convId]) || $arLastMessage[$convId] < $arRes["ID"])
					$arLastMessage[$convId] = $arRes["ID"];

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
					self::SetLastSendId($chatId, $this->user_id, $lastSendId);
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

						if ($value['unread'] == 'Y')
							$arUnreadMessage[$value['conversation']][] = $value['id'];

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
						$arUnreadMessage[$value['conversation']][] = $value['id'];
					}

					unset($arMessages[$key]['conversation']);
				}
			}

			$arResult['message'] = $diskFolderId;
			$arResult['message'] = $arMessages;
			$arResult['unreadMessage'] = $arUnreadMessage;
			$arResult['usersMessage'] = $arUsersMessage;

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
				CIMMessenger::SpeedFileCreate($this->user_id, $arResult['countMessage'], IM_SPEED_MESSAGE);
			$arResult['result'] = true;
		}
		else
		{
			$arResult['countMessage'] = CIMMessenger::GetMessageCounter($this->user_id, $arResult);
		}

		return $arResult;
	}

	function GetLastMessage($toUserId, $fromUserId = false, $loadUserData = false, $bTimeZone = true, $limit = true)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			$fromUserId = $this->user_id;

		$toUserId = intval($toUserId);
		if ($toUserId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_USER_ID"), "ERROR_TO_USER_ID");
			return false;
		}

		$chatId = 0;
		$startId = 0;
		$lastId = 0;
		$lastReadId = 0;
		$limitFetchMessages = 30;
		$blockNotify = false;
		$lastRead = false;
		$arMessages = Array();
		$arUsersMessage = Array();
		$arMessageId = Array();
		$arUnreadMessages = Array();
		$blockNotify = null;
		$lastRead = null;

		if (!$bTimeZone)
			CTimeZone::Disable();

		if ($toUserId == $fromUserId)
		{
			$chat = new CIMChat();
			$chatId = (int)$chat->GetPersonalChat();
		}
		else
		{
			$strSql ="
				SELECT R1.CHAT_ID, R1.START_ID, R1.LAST_ID, R2.LAST_ID as LAST_READ_ID, R1.NOTIFY_BLOCK
				FROM b_im_relation R1
				INNER JOIN b_im_relation R2 on R2.CHAT_ID = R1.CHAT_ID
				WHERE
					R1.USER_ID = ".$fromUserId."
					AND R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
					AND R2.USER_ID = ".$toUserId."
					AND R2.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			";
			if (!$bTimeZone)
				CTimeZone::Enable();
			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
			{
				$chatId = intval($arRes['CHAT_ID']);
				$startId = intval($arRes['START_ID']);
				$readService = new \Bitrix\Im\V2\Message\ReadService($fromUserId);
				$opponentReadService = new \Bitrix\Im\V2\Message\ReadService($toUserId);
				$lastId = (int)$arRes['LAST_ID'];
				//$count = $readService->getCounterService()->getByChat($chatId);
				$lastIdInChat = $readService->getLastMessageIdInChat($chatId);

				$messageCountFilter = \Bitrix\Main\ORM\Query\Query::filter()
					->where('ID', '>=', $startId)
					->where('ID', '>=', $lastId)
					->where('ID', '<=', $lastIdInChat)
					->where('CHAT_ID', $chatId)
				;
				$messageCount = \Bitrix\Im\Model\MessageTable::getCount($messageCountFilter);

				$lastReadId = (int)$arRes['LAST_READ_ID'];
				$lastRead = $opponentReadService->getViewedService()->getDateViewedByMessageId($lastReadId ?? 0);
				$limitFetchMessages = max($messageCount, 30);
				$blockNotify = $arRes['NOTIFY_BLOCK'] !== 'N';
			}
		}

		if ($chatId > 0)
		{
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
					$connection = \Bitrix\Main\Application::getInstance()->getConnection();
					$helper = $connection->getSqlHelper();
					$sqlLimit = " AND M.DATE_CREATE > ". $helper->addDaysToDateTime(-30);
				}
			}

			if (!$bTimeZone)
				CTimeZone::Disable();
			$strSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
					M.AUTHOR_ID,
					M.NOTIFY_EVENT
				FROM b_im_message M
				WHERE M.CHAT_ID = ".$chatId." #LIMIT#
				ORDER BY M.DATE_CREATE DESC, M.ID DESC
			";
			$strSql = $DB->TopSql($strSql, $limitFetchMessages);
			if (!$bTimeZone)
				CTimeZone::Enable();

			if ($limit)
			{
				$dbRes = $DB->Query(str_replace("#LIMIT#", $sqlLimit, $strSql));
			}
			else
			{
				$dbRes = $DB->Query(str_replace("#LIMIT#", "", $strSql));
			}

			while ($arRes = $dbRes->Fetch())
			{
				if ($arRes['ID'] < $startId)
					continue;

				if ($fromUserId == $arRes['AUTHOR_ID'])
				{
					$arRes['TO_USER_ID'] = $toUserId;
					$arRes['FROM_USER_ID'] = $fromUserId;
					$convId = $arRes['TO_USER_ID'];
				}
				else
				{
					$arRes['TO_USER_ID'] = $fromUserId;
					$arRes['FROM_USER_ID'] = $toUserId;
					$convId = $arRes['FROM_USER_ID'];
				}

				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['FROM_USER_ID'],
					'recipientId' => $arRes['TO_USER_ID'],
					'system' => $arRes['NOTIFY_EVENT'] == 'private'? 'N': 'Y',
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
					'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE']),
				);

				$arMessageId[] = $arRes['ID'];
				$arUsersMessage[$convId][] = $arRes['ID'];
				if ($lastId < $arRes['ID'])
				{
					$arUnreadMessages[$convId][] = $arRes['ID'];
				}
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

		$arChatFiles = CIMDisk::GetFiles($chatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		$arUserChatBlockStatus = Array();
		if ($blockNotify)
			$arUserChatBlockStatus[$chatId][$fromUserId] = 'Y';

		$arResult = Array(
			'chatId' => $chatId,
			'message' => $arMessages,
			'usersMessage' => $arUsersMessage,
			'unreadMessage' => $arUnreadMessages,
			'users' => Array(),
			'userInGroup' => Array(),
			'files' => $arChatFiles,
			'userChatBlockStatus' => $arUserChatBlockStatus
		);

		if ($lastRead)
		{
			$arResult['readedList'][$toUserId] = Array(
				'messageId' => $lastReadId,
				'date' => $lastRead,
			);
		}

		if (is_array($loadUserData) || is_bool($loadUserData) && $loadUserData == true)
		{
			$bDepartment = true;
			if (is_array($loadUserData) && $loadUserData['DEPARTMENT'] == 'N')
				$bDepartment = false;

			$ar = CIMContactList::GetUserData(array(
					'ID' => Array($fromUserId, $toUserId),
					'DEPARTMENT' => ($bDepartment? 'Y': 'N'),
					'USE_CACHE' => 'N',
					'SHOW_ONLINE' => 'Y',
					'PHONES' => IsModuleInstalled('voximplant')? 'Y': 'N'
				)
			);

			$arResult['users'] = $ar['users'];
			$arResult['userInGroup']  = $ar['userInGroup'];
			$arResult['phones']  = $ar['phones'];
		}

		return $arResult;
	}

	function GetLastSendMessage($arParams)
	{
		global $DB;

		if (!isset($arParams['TO_USER_ID']))
			return false;

		$toUserId = $arParams['TO_USER_ID'];
		$fromUserId = isset($arParams['FROM_USER_ID']) && intval($arParams['FROM_USER_ID'])>0? intval($arParams['FROM_USER_ID']): $this->user_id;
		$limit = isset($arParams['LIMIT']) && intval($arParams['LIMIT'])>0? intval($arParams['LIMIT']): false;
		$order = isset($arParams['ORDER']) && $arParams['ORDER'] == 'ASC'? 'ASC': 'DESC';
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;

		$arToUserId = Array();
		if (is_array($toUserId))
		{
			foreach ($toUserId as $userId)
				$arToUserId[] = intval($userId);
		}
		else
		{
			$arToUserId[] = intval($toUserId);
		}
		if (empty($arToUserId))
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
				//$sqlLimit = " AND M.DATE_CREATE > DATE_SUB(NOW(), INTERVAL ".$limit." DAY)";
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
				R1.USER_ID as R1_USER_ID,
				R2.USER_ID as R2_USER_ID
			FROM b_im_relation R1
			INNER JOIN b_im_relation R2 on R2.CHAT_ID = R1.CHAT_ID
			INNER JOIN b_im_message M ON M.ID >= R1.START_ID
							AND M.ID >= R1.LAST_ID
							AND M.ID >= R2.LAST_ID
							AND M.CHAT_ID = R1.CHAT_ID
							".$sqlLimit."
			WHERE
				R1.USER_ID = ".$fromUserId."
			AND R2.USER_ID IN (".implode(",",$arToUserId).")
			AND R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			".($order == 'DESC'? "ORDER BY M.DATE_CREATE DESC, M.ID DESC": "");
		if (!$bTimeZone)
			CTimeZone::Enable();

		$arMessages = Array();
		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->Fetch())
		{
			if ($fromUserId == $arRes['AUTHOR_ID'])
			{
				$arRes['TO_USER_ID'] = $arRes['R2_USER_ID'];
				$arRes['FROM_USER_ID'] = $arRes['R1_USER_ID'];
				$convId = $arRes['TO_USER_ID'];
			}
			else
			{
				$arRes['TO_USER_ID'] = $arRes['R1_USER_ID'];
				$arRes['FROM_USER_ID'] = $arRes['R2_USER_ID'];
				$convId = $arRes['FROM_USER_ID'];
			}

			if (!isset($arMessages[$convId]) || (isset($arMessages[$convId]) && $arMessages[$convId]['date'] < $arRes['DATE_CREATE']))
			{

				$arMessages[$convId] = Array(
					'id' => $arRes['ID'],
					'senderId' => $arRes['FROM_USER_ID'],
					'recipientId' => $arRes['TO_USER_ID'],
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'text' => $arRes['MESSAGE']
				);
			}
		}
		foreach ($arMessages as $key => $value)
		{
			$value['text'] = \Bitrix\Im\Text::parse($value['text']);
			$arMessages[$key] = $value;
		}
		return $arMessages;
	}

	/**
	 * @deprecated
	 * @param $order
	 * @return array
	 */
	public static function GetUnsendMessage($order = "ASC")
	{
		//todo: change send mail logic
		global $DB;

		$mailService = new \Bitrix\Im\V2\Mail();

		$unsendIds = $mailService->getMessageIdsToSend();

		if (empty($unsendIds))
		{
			return [];
		}

		$implodeUnsendIds = implode(',', $unsendIds);

		CTimeZone::Disable();
		$strSql ="
			SELECT
				M.ID,
				M.CHAT_ID,
				M.MESSAGE,
				M.MESSAGE_OUT,
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." as DATE_CREATE,
				M.EMAIL_TEMPLATE,
				R.USER_ID as TO_USER_ID,
				U1.ACTIVE as TO_USER_ACTIVE,
				U1.LOGIN as TO_USER_LOGIN,
				U1.NAME as TO_USER_NAME,
				U1.LAST_NAME as TO_USER_LAST_NAME,
				U1.EMAIL as TO_USER_EMAIL,
				U1.LID as TO_USER_LID,
				U1.AUTO_TIME_ZONE as AUTO_TIME_ZONE,
				U1.TIME_ZONE as TIME_ZONE,
				U1.TIME_ZONE_OFFSET as TIME_ZONE_OFFSET,
				U1.EXTERNAL_AUTH_ID as TO_EXTERNAL_AUTH_ID,
				M.AUTHOR_ID as FROM_USER_ID,
				U2.LOGIN as FROM_USER_LOGIN,
				U2.NAME as FROM_USER_NAME,
				U2.LAST_NAME as FROM_USER_LAST_NAME,
				U2.EXTERNAL_AUTH_ID as FROM_EXTERNAL_AUTH_ID
			FROM b_im_relation R
			INNER JOIN b_im_message M ON M.CHAT_ID = R.CHAT_ID AND IMPORT_ID IS NULL AND R.USER_ID != M.AUTHOR_ID AND M.ID IN ({$implodeUnsendIds})
			LEFT JOIN b_user U1 ON U1.ID = R.USER_ID
			LEFT JOIN b_user U2 ON U2.ID = M.AUTHOR_ID
			".($order == "DESC"? "ORDER BY M.DATE_CREATE DESC, M.ID DESC": "")."
		";

		$dbRes = $DB->Query($strSql);
		CTimeZone::Enable();

		$arMessages = Array();
		while ($arRes = $dbRes->Fetch())
		{
			$arRes["DATE_CREATE"] = $arRes["DATE_CREATE"] + CIMMail::GetUserOffset($arRes);
			$arMessages[$arRes['ID']] = $arRes;
		}

		return $arMessages;
	}

	public function SetReadMessage($fromUserId, $lastId = null, $byEvent = false)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			return false;

		CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_MESSAGE);

		$chat = \Bitrix\Im\V2\Entity\User\User::getInstance($this->user_id)->getChatWith($fromUserId, false);
		if ($chat === null)
		{
			return false;
		}
		$readService = new \Bitrix\Im\V2\Message\ReadService($this->user_id);

		$startId = $readService->getLastIdByChatId($chat->getChatId());
		$counter = 0;
		$viewedMessages = [];

		if (isset($lastId))
		{
			$message = new \Bitrix\Im\V2\Message();
			$message->setMessageId((int)$lastId)->setChatId($chat->getChatId())->setChat($chat);
			$readResult = $readService->readTo($message);
			$counter = $readResult->getResult()['COUNTER'];
			$viewedMessages = $readResult->getResult()['VIEWED_MESSAGES'];
		}
		else
		{
			$counter = $readService->readAllInChat($chat->getChatId())->getResult()['COUNTER'];
		}

		/*\Bitrix\Main\Application::getConnection()->query(
			"UPDATE b_im_recent SET DATE_UPDATE = NOW() WHERE USER_ID = ".$this->user_id." AND ITEM_CID = ".$chat->getChatId()
		);*/

		$endId = $readService->getLastIdByChatId($chat->getChatId());

		if (CModule::IncludeModule("pull"))
		{
			CPushManager::DeleteFromQueueBySubTag($this->user_id, 'IM_MESS');

			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'readMessage',
				'params' => Array(
					'dialogId' => $fromUserId,
					'chatId' => $chat->getChatId(),
					'senderId' => $this->user_id,
					'id' => $fromUserId,
					'userId' => $fromUserId,
					'lastId' => $endId,
					'counter' => $counter,
					'muted' => false,
					'unread' => \Bitrix\Im\Recent::isUnread($this->user_id, \IM_MESSAGE_PRIVATE, $fromUserId),
					'viewedMessages' => $viewedMessages,
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
			\Bitrix\Pull\Event::add($fromUserId, Array(
				'module_id' => 'im',
				'command' => 'readMessageOpponent',
				'expiry' => 3600,
				'params' => Array(
					'dialogId' => $this->user_id,
					'chatId' => $chat->getChatId(),
					'userId' => $this->user_id,
					'userName' => \Bitrix\Im\User::getInstance($this->user_id)->getFullName(false),
					'lastId' => $endId,
					'date' => date('c', time()),
					'chatMessageStatus' => (new \Bitrix\Im\V2\Message\ReadService($fromUserId))->getChatMessageStatus($chat->getChatId()),
					'viewedMessages' => $viewedMessages,
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		foreach(GetModuleEvents("im", "OnAfterUserRead", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, array(Array(
				'DIALOG_ID' => $fromUserId,
				'CHAT_ID' => $chat->getChatId(),
				'CHAT_ENTITY_TYPE' => 'USER',
				'CHAT_ENTITY_ID' => '',
				'START_ID' => $startId,
				'END_ID' => $endId,
				'COUNT' => $counter,
				'USER_ID' => $this->user_id,
				'BY_EVENT' => $byEvent
			)));
		}

		return Array(
			'DIALOG_ID' => $fromUserId,
			'CHAT_ID' => $chat->getChatId(),
			'LAST_ID' => $endId,
			'COUNTER' => $counter
		);
	}

	public function SetUnReadMessage($fromUserId, $lastId)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			return false;

		$lastId = intval($lastId);
		if (intval($lastId) <= 0)
			return false;

		/*$result = \Bitrix\Im\V2\Entity\User\User::getInstance($this->user_id)
			->getChatWith($fromUserId)
			?->unreadToMessage(new \Bitrix\Im\V2\Message($lastId))
		;

		return $result?->isSuccess() ?? false;*/

		$strSql = "
			SELECT M.CHAT_ID
			FROM b_im_relation RF
				INNER JOIN b_im_relation RT on RF.CHAT_ID = RT.CHAT_ID
				INNER JOIN b_im_message M ON M.ID = ".$lastId." AND M.CHAT_ID = RT.CHAT_ID
			WHERE RT.USER_ID = ".$this->user_id."
				and RF.USER_ID = ".$fromUserId."
				and RT.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			GROUP BY M.CHAT_ID";
		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
		{
			$relation = self::SetLastIdForUnread($arRes['CHAT_ID'], $this->user_id, $lastId);
			if ($relation)
			{
				$chat = Chat::getInstance((int)$arRes['CHAT_ID']);
				\Bitrix\Main\Application::getConnection()->query(
					"UPDATE b_im_recent SET DATE_UPDATE = NOW() WHERE USER_ID = ".$this->user_id." AND ITEM_CID = ".intval($arRes['CHAT_ID'])
				);

				CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_MESSAGE);

				if (CModule::IncludeModule("pull"))
				{
					\Bitrix\Pull\Event::add($this->user_id, Array(
						'module_id' => 'im',
						'command' => 'unreadMessage',
						'expiry' => 3600,
						'params' => Array(
							'dialogId' => $fromUserId,
							'chatId' => intval($arRes['CHAT_ID']),
							'userId' => $fromUserId,
							'date' => new \Bitrix\Main\Type\DateTime(),
							'counter' => (int)$relation['COUNTER'],
							'muted' => false,
							'unread' => \Bitrix\Im\Recent::isUnread($this->user_id, \IM_MESSAGE_PRIVATE, $fromUserId),
							'unreadTo' => $lastId,
						),
						'push' => Array('badge' => 'Y'),
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
					\Bitrix\Pull\Event::add($fromUserId, Array(
						'module_id' => 'im',
						'command' => 'unreadMessageOpponent',
						'expiry' => 3600,
						'params' => Array(
							'dialogId' => $this->user_id,
							'chatId' => intval($arRes['CHAT_ID']),
							'userId' => $this->user_id,
							'chatMessageStatus' => $relation['CHAT_MESSAGE_STATUS'],
							'unreadTo' => $lastId,
						),
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
				}

				Sync\Logger::getInstance()->add(
					new Sync\Event(Sync\Event::ADD_EVENT, Sync\Event::CHAT_ENTITY, intval($arRes['CHAT_ID'])),
					$this->user_id,
					$chat->getType()
				);

				return true;
			}
		}

		return false;
	}

	public static function SetReadMessageAll($fromUserId)
	{
		/*global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			return false;

		$strSql = "
			SELECT RT.ID, RT.USER_ID, RT.CHAT_ID
			FROM b_im_relation RF
			INNER JOIN b_im_relation RT on RF.CHAT_ID = RT.CHAT_ID AND RT.ID != RF.ID
			WHERE RF.USER_ID = ".$fromUserId."
			AND RT.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."' AND RT.STATUS < ".IM_STATUS_READ;
		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
		{
			\Bitrix\Im\Model\RelationTable::update($arRes["ID"], [
				'STATUS' => IM_STATUS_READ
			]);
			CIMMessenger::SpeedFileDelete($arRes['USER_ID'], IM_SPEED_MESSAGE);
		}*/

		return true;
	}

	/**
	 * @deprecated
	 * @param $chatId
	 * @param $userId
	 * @param $lastId
	 * @return false|mixed
	 */
	public static function SetLastId($chatId, $userId, $lastId = null)
	{
		$chatId = intval($chatId);
		$userId = intval($userId);

		$lastIdIsNull = $lastId === null;
		$lastId = intval($lastId);

		if ($chatId <= 0 || $userId <= 0)
			return false;

		$updateCounters = false;
		$relations = \Bitrix\Im\Chat::getRelation($chatId, Array(
			//'SELECT' => Array('ID', 'CHAT_ID', 'LAST_ID', 'LAST_SEND_ID', 'STATUS', 'USER_ID', 'NOTIFY_BLOCK', 'MESSAGE_TYPE'),
			'SELECT' => Array('ID', 'CHAT_ID', 'LAST_ID', 'USER_ID', 'NOTIFY_BLOCK', 'MESSAGE_TYPE'),
			'FILTER' => Array(
				'USER_ID' => $userId
			),
			'REAL_COUNTERS' => $lastIdIsNull? 'Y': Array(
				'LAST_ID' => $lastId
			)
		));
		if (isset($relations[$userId]))
		{
			$relation = $relations[$userId];
		}
		else
		{
			return false;
		}



		/*$update = array();
		if (!$lastIdIsNull)
		{
			if ($relation["LAST_ID"] < $lastId)
			{
				$relation["LAST_ID"] = $update["LAST_ID"] = $lastId;
			}
			if ($relation["LAST_SEND_ID"] < $lastId)
			{
				$relation["LAST_SEND_ID"] = $update["LAST_SEND_ID"] = $lastId;
			}
		}

		if ($relation['COUNTER'] > 0)
		{
			if ($relation['STATUS'] == IM_STATUS_READ)
			{
				$relation["STATUS"] = $update["STATUS"] = IM_STATUS_NOTIFY;
			}

			$filter = [
				'=CHAT_ID' => $relation['CHAT_ID']
			];
			if ($lastId > 0)
			{
				$filter['>ID'] = $lastId;
			}

			$firstUnreadMessage = \Bitrix\Im\Model\MessageTable::getList([
				'select' => ['ID'],
				'filter' => $filter,
				'limit' => 1,
			])->fetch();

			$update["LAST_ID"] = $relation["LAST_ID"] = $lastId;
			$update["UNREAD_ID"] = $firstUnreadMessage? $firstUnreadMessage['ID']: 0;
		}
		else
		{
			if ($relation['STATUS'] != IM_STATUS_READ)
			{
				$relation["STATUS"] = $update["STATUS"] = IM_STATUS_READ;
			}
			$relation["UNREAD_ID"] = $update["UNREAD_ID"] = 0;
		}

		if ($relation['COUNTER'] != $relation['PREVIOUS_COUNTER'])
		{
			$update["COUNTER"] = $relation['COUNTER'];
			$updateCounters = true;
		}*/

		$relation['CHAT_MESSAGE_STATUS'] = (new \Bitrix\Im\V2\Message\ReadService($userId))->getChatMessageStatus($chatId);

		/*if ($update)
		{
			if ($relation['STATUS'] == IM_STATUS_READ)
			{
				$relation["LAST_READ"] = $update["LAST_READ"] = new Bitrix\Main\Type\DateTime();
				$relation["MESSAGE_STATUS"] = $update["MESSAGE_STATUS"] = IM_MESSAGE_STATUS_DELIVERED;
			}
			else
			{
				$relation["LAST_READ"] = $update["LAST_READ"] = '';
				$relation["MESSAGE_STATUS"] = $update["MESSAGE_STATUS"] = IM_MESSAGE_STATUS_RECEIVED;
			}

			\Bitrix\Im\Model\RelationTable::update($relation["ID"], $update);

			if ($relation['MESSAGE_TYPE'] != IM_MESSAGE_OPEN_LINE)
			{
				$orm = \Bitrix\Im\Model\RelationTable::getList(array(
					'filter' => Array(
						'=CHAT_ID' => $chatId
					)
				));
				if ($relation['STATUS'] == IM_STATUS_READ)
				{
					\Bitrix\Im\Model\ChatTable::update($chatId, Array('LAST_MESSAGE_STATUS' => IM_MESSAGE_STATUS_DELIVERED));
					$relation['CHAT_MESSAGE_STATUS'] = IM_MESSAGE_STATUS_DELIVERED;

					while ($row = $orm->fetch())
					{
						if ($userId == $row['USER_ID'])
						{
							$updateCounters = true;
							continue;
						}
						\Bitrix\Im\Counter::clearCache($row['USER_ID']);
					}
				}
				else
				{
					$relations = Array();
					while ($row = $orm->fetch())
					{
						$relations[] = $row['USER_ID'];
						if ($row['MESSAGE_STATUS'] == IM_MESSAGE_STATUS_DELIVERED)
						{
							$relation['CHAT_MESSAGE_STATUS'] = IM_MESSAGE_STATUS_DELIVERED;
							$relations = Array();
							break;
						}
					}
					if ($relations)
					{
						\Bitrix\Im\Model\ChatTable::update($chatId, Array('LAST_MESSAGE_STATUS' => IM_MESSAGE_STATUS_RECEIVED));
						$relation['CHAT_MESSAGE_STATUS'] = IM_MESSAGE_STATUS_RECEIVED;
						foreach ($relations as $relationUserId)
						{
							if ($userId == $relationUserId)
							{
								$updateCounters = true;
								continue;
							}
							\Bitrix\Im\Counter::clearCache($relationUserId);
						}
					}
				}
			}
		}

		if ($updateCounters)
		{
			\Bitrix\Im\Counter::clearCache($userId);
		}*/

		return $relation;
	}

	/**
	 * @deprecated
	 * @param $chatId
	 * @param $userId
	 * @param $lastId
	 * @return false|mixed
	 */
	public static function SetLastIdForUnread($chatId, $userId, $lastId)
	{
		$message = new \Bitrix\Im\V2\Message();
		$message->setMessageId($lastId)->setChatId($chatId);
		$ownRelation = Chat::getInstance($chatId)->getSelfRelation();

		if ($ownRelation === null)
		{
			return false;
		}

		$firstUnreadMessage = \Bitrix\Im\Model\MessageTable::getList([
			'select' => ['ID'],
			'filter' => [
				'=CHAT_ID' => $chatId,
				'<ID' => $lastId
			],
			'limit' => 1,
			'order' => Array('ID' => 'DESC')
		])->fetch();

		$firstUnreadMessage = intval($firstUnreadMessage['ID']);
		$sql = "
			UPDATE b_im_relation
			SET LAST_ID=(CASE WHEN LAST_ID < {$firstUnreadMessage} THEN LAST_ID ELSE {$firstUnreadMessage} END)
			WHERE CHAT_ID={$chatId} AND USER_ID={$userId}
		";
		Application::getConnection()->queryExecute($sql);
		$readService = new \Bitrix\Im\V2\Message\ReadService($userId);
		$readService->getCounterService()->addStartingFrom($lastId, $ownRelation);
		$relation = self::SetLastId($chatId, $userId, $lastId);
		$readService->getViewedService()->deleteStartingFrom($message);

		return $relation;
	}

	/**
	 * @deprecated
	 * @param $chatId
	 * @param $userId
	 * @param $lastSendId
	 * @return bool
	 */
	public static function SetLastSendId($chatId, $userId, $lastSendId)
	{
		/*global $DB;

		if (intval($chatId) <= 0 || intval($userId) <= 0 || intval($lastSendId) <= 0)
			return false;

		$strSql = "UPDATE b_im_relation
					SET LAST_SEND_ID = (case when LAST_SEND_ID > ".intval($lastSendId)." then LAST_SEND_ID else ".intval($lastSendId)." end),
						STATUS = ".IM_STATUS_NOTIFY."
					WHERE CHAT_ID = ".intval($chatId)." AND USER_ID = ".intval($userId);
		$DB->Query($strSql);*/

		return true;
	}

	public static function Delete($id, $userId = null, $completeDelete = false, $byEvent = false)
	{
		return CIMMessenger::Delete($id, $userId, $completeDelete, $byEvent);
	}

	/**
	 * @param $arParams
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function GetFormatMessage($arParams)
	{
		$arParams['ID'] = intval($arParams['ID']);
		$arParams['TO_USER_ID'] = isset($arParams['TO_CHAT_ID'])? intval($arParams['TO_CHAT_ID']): intval($arParams['TO_USER_ID']);
		$arParams['FROM_USER_ID'] = intval($arParams['FROM_USER_ID']);
		$arParams['MESSAGE'] = trim($arParams['MESSAGE']);
		$arParams['DATE_CREATE'] = intval($arParams['DATE_CREATE']);
		$arParams['PARAMS'] = empty($arParams['PARAMS'])? Array(): $arParams['PARAMS'];
		$arParams['EXTRA_PARAMS'] = empty($arParams['EXTRA_PARAMS'])? Array(): $arParams['EXTRA_PARAMS'];
		$arParams['NOTIFY'] = $arParams['NOTIFY'] === true? true: $arParams['NOTIFY'];

		$arUsers = CIMContactList::GetUserData(Array(
			'ID' => isset($arParams['TO_CHAT_ID'])? $arParams['FROM_USER_ID']: Array($arParams['TO_USER_ID'], $arParams['FROM_USER_ID']),
			'PHONES' => 'Y',
		));

		$multidialogBot = null;
		foreach ($arUsers['users'] as $key => $user)
		{
			if (
				!empty($user['bot_data'])
				&& in_array($user['bot_data']['type'], ['support24', 'network'])
				&& \Bitrix\Main\Loader::includeModule('imbot')
				&& !isset($arParams['TO_CHAT_ID'])
				&& count($arUsers['users']) == 2
			)
			{
				$botId = (int)$user['id'];
				foreach ($arUsers['users'] as $otherUser)
				{
					if ($otherUser['id'] != $botId)
					{
						$multidialogBot = \Bitrix\ImBot\Bot\Network::getBotAsMultidialog($botId, (int)$otherUser['id']);
					}
				}
			}
		}

		$arChat = Array();
		if (isset($arParams['TO_CHAT_ID']))
		{
			$arChat = CIMChat::GetChatData(array(
				'ID' => $arParams['TO_CHAT_ID'],
				'USE_CACHE' => 'N',
			));

			$extraParamContext = $arParams['EXTRA_PARAMS']['CONTEXT'] ?? null;
			if (!empty($arUsers['users']) && $extraParamContext == 'LIVECHAT' && CModule::IncludeModule('imopenlines'))
			{
				[$lineId, $userId] = explode('|', $arChat['chat'][$arParams['TO_CHAT_ID']]['entity_id']);
				$userCode = 'livechat|' . $lineId . '|' . $arParams['TO_CHAT_ID'] . '|' . $userId;
				unset($lineId, $userId);

				foreach ($arUsers['users'] as $userId => $userData)
				{
					$arUsers['users'][$userId] = \Bitrix\ImOpenLines\Connector::getOperatorInfo($arParams['EXTRA_PARAMS']['LINE_ID'], $userId, $userCode);
				}
			}
		}

		if (isset($arParams['TEMPLATE_ID']))
		{
			$arParams['TEMPLATE_ID'] = is_numeric($arParams['TEMPLATE_ID'])? (int)$arParams['TEMPLATE_ID']: (string)$arParams['TEMPLATE_ID'];
		}
		else
		{
			$arParams['TEMPLATE_ID'] = '';
		}

		if (isset($arParams['FILE_TEMPLATE_ID']))
		{
			$arParams['FILE_TEMPLATE_ID'] = is_numeric($arParams['FILE_TEMPLATE_ID'])? (int)$arParams['FILE_TEMPLATE_ID']: (string)$arParams['FILE_TEMPLATE_ID'];
		}
		else
		{
			$arParams['FILE_TEMPLATE_ID'] = '';
		}

		if (
			isset($arParams['PARAMS'][\Bitrix\Im\V2\Message\Params::COPILOT_ROLE])
			|| $arChat['chat'][$arParams['TO_CHAT_ID']]['type'] === 'copilot'
		)
		{
			$chatId = (int)$arParams['CHAT_ID'];
			$isCopilotChat = $arChat['chat'][$arParams['TO_CHAT_ID']]['type'] === 'copilot';
			$copilotData = self::prepareCopilotData($arParams,  $chatId, $isCopilotChat);
		}

		$additionalEntitiesAdapter = new \Bitrix\Im\V2\Rest\RestAdapter();
		$additionalPopupData = new \Bitrix\Im\V2\Rest\PopupData([]);

		$forwardInfo = null;
		if (isset($arParams['PARAMS']['FORWARD_CONTEXT_ID']))
		{
			$additionalUserId = (int)$arParams['PARAMS']['FORWARD_USER_ID'];
			$additionalPopupData->add(new \Bitrix\Im\V2\Entity\User\UserPopupItem([$additionalUserId]));
			$forwardInfo = [
				'id' => $arParams['PARAMS']['FORWARD_CONTEXT_ID'],
				'userId' => (int)$arParams['PARAMS']['FORWARD_USER_ID'],
				'chatTitle' => $arParams['PARAMS']['FORWARD_CHAT_TITLE'],
				'chatType' => \Bitrix\Im\V2\Message\Forward\ForwardService::getChatTypeByContextId($arParams['PARAMS']['FORWARD_CONTEXT_ID']),
			];
			unset(
				$arParams['PARAMS']['FORWARD_CONTEXT_ID'],
				$arParams['PARAMS']['FORWARD_USER_ID'],
				$arParams['PARAMS']['FORWARD_ID'],
				$arParams['PARAMS']['FORWARD_CHAT_TITLE']
			);
		}

		$replyIds = [];
		if (isset($arParams['PARAMS']['REPLY_ID']))
		{
			$replyIds[] = (int)$arParams['PARAMS']['REPLY_ID'];
		}
		$messages = new Bitrix\Im\V2\MessageCollection($replyIds);
		$messages->fillAllForRest();
		$additionalEntitiesAdapter->addEntities($messages);
		$additionalEntitiesAdapter->setAdditionalPopupData($additionalPopupData);
		$additionalEntitiesRest = $additionalEntitiesAdapter->toRestFormat([
			'WITHOUT_OWN_REACTIONS' => true,
			'MESSAGE_ONLY_COMMON_FIELDS' => true,
		]);

		$multidialog = null;
		if (isset($arChat['multidialogs'][$arParams['CHAT_ID']]))
		{
			$multidialog = $arChat['multidialogs'][$arParams['CHAT_ID']];
		}
		elseif ($multidialogBot)
		{
			$multidialog = $multidialogBot;
		}

		return [
			'chatId' => $arParams['CHAT_ID'],
			'dateLastActivity' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arParams['DATE_CREATE']),
			'dialogId' => isset($arParams['TO_CHAT_ID'])? 'chat'.$arParams['TO_CHAT_ID']: 0,
			'chat' => $arChat['chat'] ?? [],
			'copilot' => $copilotData ?? null,
			'lines' => $arChat['lines'][$arParams['CHAT_ID']] ?? null,
			'multidialog' => $multidialog,
			'userInChat' => $arChat['userInChat'] ?? [],
			'userBlockChat' => $arChat['userChatBlockStatus'] ?? [],
			'users' => (is_array($arUsers) && is_array($arUsers['users'])) ? $arUsers['users'] : null,
			'message' => [
				'id' => $arParams['ID'],
				'templateId' => $arParams['TEMPLATE_ID'],
				'templateFileId' => $arParams['FILE_TEMPLATE_ID'],
				'prevId' => intval($arParams['PREV_ID']),
				'chatId' => $arParams['CHAT_ID'],
				'senderId' => $arParams['FROM_USER_ID'],
				'recipientId' => isset($arParams['TO_CHAT_ID'])? 'chat'.$arParams['TO_CHAT_ID']: $arParams['TO_USER_ID'],
				'system' => $arParams['SYSTEM'] == 'Y'? 'Y': 'N',
				'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arParams['DATE_CREATE']),
				'text' => \Bitrix\Im\Text::parse($arParams['MESSAGE']),
				'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arParams['MESSAGE']),
				'params' => $arParams['PARAMS'],
				'counter' => isset($arParams['COUNTER']) && (int)$arParams['COUNTER'] > 0 ? (int)$arParams['COUNTER'] : 0,
				'importantFor' => array_values($arParams['IMPORTANT_FOR'] ?? []),
				'isImportant' => isset($arParams['IS_IMPORTANT']) && $arParams['IS_IMPORTANT'] === 'Y',
				'additionalEntities' => $additionalEntitiesRest,
				'forward' => $forwardInfo,
			],
			'files' => isset($arParams['FILES'])? $arParams['FILES']: [],
			'notify' => $arParams['NOTIFY'],
		];
	}

	private static function prepareCopilotData(array $arParams, int $chatId, bool $isCopilotChat): array
	{
		$roleManager = new \Bitrix\Im\V2\Integration\AI\RoleManager();
		$messageRole = $arParams['PARAMS'][\Bitrix\Im\V2\Message\Params::COPILOT_ROLE] ?? null;

		if (
			!isset($messageRole)
			&& \Bitrix\Main\Loader::includeModule('imbot')
			&& $arParams['FROM_USER_ID'] === \Bitrix\Imbot\Bot\CopilotChatBot::getBotId()
		)
		{
			$messageRole = \Bitrix\Im\V2\Integration\AI\RoleManager::getDefaultRoleCode();
		}

		if ($isCopilotChat)
		{
			$chatRole = [[
				'dialogId' => \Bitrix\Im\Dialog::getDialogId($chatId),
				'role' => $roleManager->getMainRole($chatId),
			]];
		}

		$messageId = isset($arParams['PARAMS']['FORWARD_ID'])
			? (int)$arParams['PARAMS']['FORWARD_ID']
			: (int)$arParams['ID']
		;

		$copilotData = [
			'chats' => $chatRole ?? null,
			'messages' => !empty($messageRole) ? [['id' => $messageId, 'role' => $messageRole]] : null,
			'roles' => $roleManager->getRoles(
				$isCopilotChat ? [$roleManager->getMainRole($chatId), $messageRole] : [$messageRole],
				(int)$arParams['FROM_USER_ID']
			),
		];

		return $copilotData;
	}

	public static function GetChatId($fromUserId, $toUserId, $createIfNotExists = true)
	{
		global $DB;

		$chatId = 0;
		$fromUserId = intval($fromUserId);
		$toUserId = intval($toUserId);

		if (intval($fromUserId) <= 0 || intval($toUserId) <= 0)
		{
			return $chatId;
		}

		if ($fromUserId == $toUserId)
		{
			$chat = new CIMChat();
			$chatId = $chat->GetPersonalChat($fromUserId);
			if (!$chatId)
			{
				return 0;
			}

			return $chatId;
		}

		$strSql = "
			SELECT RF.CHAT_ID
			FROM
				b_im_chat C,
				b_im_relation RF,
				b_im_relation RT
			WHERE
				C.ID = RT.CHAT_ID
			and C.TYPE = '".IM_MESSAGE_PRIVATE."'
			and RF.USER_ID = ".$fromUserId."
			and RT.USER_ID = ".$toUserId."
			and RF.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			and RT.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			and RF.CHAT_ID = RT.CHAT_ID
		";
		$dbRes = $DB->Query($strSql);
		if ($arRes = $dbRes->Fetch())
		{
			$chatId = intval($arRes['CHAT_ID']);
		}
		if ($chatId <= 0)
		{
			if (!$createIfNotExists)
			{
				return 0;
			}

			if (!\Bitrix\Im\Dialog::hasAccess($fromUserId, $toUserId))
			{
				return 0;
			}

			$result = \Bitrix\Im\Model\ChatTable::add(Array('TYPE' => IM_MESSAGE_PRIVATE, 'AUTHOR_ID' => $fromUserId));
			$chatId = $result->getId();
			if ($chatId > 0)
			{
				\Bitrix\Im\Model\RelationTable::add(array(
					"CHAT_ID" => $chatId,
					"MESSAGE_TYPE" => IM_MESSAGE_PRIVATE,
					"USER_ID" => $fromUserId,
					//"STATUS" => IM_STATUS_READ,
				));
				\Bitrix\Im\Model\RelationTable::add(array(
					"CHAT_ID" => $chatId,
					"MESSAGE_TYPE" => IM_MESSAGE_PRIVATE,
					"USER_ID" => $toUserId,
					//"STATUS" => IM_STATUS_READ,
				));

				$botJoinFields = Array(
					"CHAT_TYPE" => IM_MESSAGE_PRIVATE,
					"MESSAGE_TYPE" => IM_MESSAGE_PRIVATE
				);
				if (\Bitrix\Im\User::getInstance($fromUserId)->isExists() && !\Bitrix\Im\User::getInstance($fromUserId)->isBot())
				{
					$botJoinFields['BOT_ID'] = $toUserId;
					$botJoinFields['USER_ID'] = $fromUserId;
					$botJoinFields['TO_USER_ID'] = $toUserId;
					$botJoinFields['FROM_USER_ID'] = $fromUserId;
					\Bitrix\Im\Bot::onJoinChat($fromUserId, $botJoinFields);
				}
				else if (\Bitrix\Im\User::getInstance($toUserId)->isExists() && !\Bitrix\Im\User::getInstance($toUserId)->isBot())
				{
					$botJoinFields['BOT_ID'] = $fromUserId;
					$botJoinFields['USER_ID'] = $toUserId;
					$botJoinFields['TO_USER_ID'] = $toUserId;
					$botJoinFields['FROM_USER_ID'] = $fromUserId;
					\Bitrix\Im\Bot::onJoinChat($toUserId, $botJoinFields);
				}
			}
		}

		return $chatId;
	}
}
?>