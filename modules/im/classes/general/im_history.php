<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;

class CIMHistory
{
	private $user_id = 0;
	private $bHideLink = false;

	function __construct($user_id = false, $arParams = Array())
	{
		global $USER;
		$this->user_id = intval($user_id);
		if ($user_id == 0)
			$this->user_id = intval($USER->GetID());
		if (isset($arParams['HIDE_LINK']) && $arParams['HIDE_LINK'] == 'Y')
			$this->bHideLink = true;
	}

	function SearchMessage($searchText, $toUserId, $fromUserId = false, $bTimeZone = true)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			$fromUserId = $this->user_id;

		$toUserId = intval($toUserId);
		if ($toUserId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_ERROR_TO_USER_ID"), "ERROR_TO_USER_ID");
			return false;
		}

		$searchText = trim($searchText);
		if (mb_strlen($searchText) < 3)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_SEARCH_EMPTY"), "ERROR_SEARCH_EMPTY");
			return false;
		}

		$chatId = 0;
		$arMessages = Array();
		$arMessageId = Array();
		$arUnreadMessage = Array();
		$arMessageFiles = Array();
		$arUsers = Array();

		$limitById = '';
		if ($toUserId == $fromUserId)
		{
			$chat = new CIMChat();
			$chatId = $chat->GetPersonalChat();
			if (!$chatId)
			{
				return false;
			}
			$startId = 0;
		}
		else
		{
			$arRelation = \CIMChat::GetPrivateRelation($fromUserId, $toUserId);
			$chatId = $arRelation['CHAT_ID'];
			$startId = $arRelation['START_ID'];
		}
		if ($chatId > 0)
		{
			$where = [
				'=CHAT_ID'	=> $chatId,
				'*%MESSAGE'	=> $searchText,
			];
			if ($this->checkFullText($searchText))
			{
				$searchText = \Bitrix\Main\Search\Content::prepareStringToken($searchText);
				$where = [
					'=CHAT_ID' => $chatId,
					'*INDEX.SEARCH_CONTENT' => $searchText,
				];
			}
			if ($startId)
			{
				$where['>=ID'] = intval($startId);
			}

			$orm = \Bitrix\Im\Model\MessageTable::getList(
				[
					'select' => [
						'ID',
						'CHAT_ID',
						'MESSAGE',
						'AUTHOR_ID',
						'NOTIFY_EVENT',
						'DATE_CREATE'
					],
					'filter' => $where,
					'order' => ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
					'limit' => 1000,
				]
			);

			while ($arRes = $orm->fetch())
			{
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
					'date' => $arRes['DATE_CREATE'],
					'system' => $arRes['NOTIFY_EVENT'] == 'private'? 'N': 'Y',
					'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
					'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
				);

				$arUsers[$convId][] = $arRes['ID'];
				$arMessageId[] = $arRes['ID'];
				$chatId = $arRes['CHAT_ID'];
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
			$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
			$arMessages = CIMMessageLink::prepareShow($arMessages, $params);
		}

		return Array('chatId' => $chatId, 'message' => $arMessages, 'unreadMessage' => $arUnreadMessage, 'usersMessage' => $arUsers, 'files' => $arMessageFiles);
	}

	function SearchDateMessage($searchDate, $toUserId, $fromUserId = false, $bTimeZone = true)
	{
		global $DB;

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			$fromUserId = $this->user_id;

		$toUserId = intval($toUserId);
		if ($toUserId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_ERROR_TO_USER_ID"), "ERROR_TO_USER_ID");
			return false;
		}

		$sqlHelper = Bitrix\Main\Application::getInstance()->getConnection()->getSqlHelper();

		try
		{
			$dateStart = \Bitrix\Main\Type\DateTime::createFromUserTime($searchDate);
			$sqlDateStart = $sqlHelper->getCharToDateFunction($dateStart->format("Y-m-d H:i:s"));

			$dateEnd = $dateStart->add('1 DAY');
			$sqlDateEnd = $sqlHelper->getCharToDateFunction($dateEnd->format("Y-m-d H:i:s"));
		}
		catch(\Bitrix\Main\ObjectException $e)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_SEARCH_DATE_EMPTY"), "ERROR_SEARCH_EMPTY");
			return false;
		}

		$chatId = 0;
		$arMessages = Array();
		$arMessageId = Array();
		$arUnreadMessage = Array();
		$arMessageFiles = Array();
		$arUsers = Array();

		$limitById = '';
		if ($toUserId == $fromUserId)
		{
			$chat = new CIMChat();
			$chatId = $chat->GetPersonalChat();
			if (!$chatId)
			{
				return false;
			}
			$startId = 0;
		}
		else
		{
			$arRelation = \CIMChat::GetPrivateRelation($fromUserId, $toUserId);
			$chatId = $arRelation['CHAT_ID'];
			$startId = $arRelation['START_ID'];
		}
		if ($chatId > 0)
		{
			if ($startId > 0)
			{
				$limitById = 'AND M.ID >= '.intval($startId);
			}

			if (!$bTimeZone)
				CTimeZone::Disable();
			$strSql = "
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					".$fromUserId." R1_USER_ID,
					".$toUserId." R2_USER_ID,
					M.NOTIFY_EVENT
				FROM b_im_message M
				WHERE
					M.CHAT_ID = ".$chatId."
				AND M.DATE_CREATE >= ".$sqlDateStart." AND M.DATE_CREATE <=  ".$sqlDateEnd."
					".$limitById."
				ORDER BY M.DATE_CREATE DESC, M.ID DESC
			";
			if (!$bTimeZone)
				CTimeZone::Enable();
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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

				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['FROM_USER_ID'],
					'recipientId' => $arRes['TO_USER_ID'],
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'system' => $arRes['NOTIFY_EVENT'] == 'private' ? 'N' : 'Y',
					'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
					'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
				);

				$arUsers[$convId][] = $arRes['ID'];
				$arMessageId[] = $arRes['ID'];
				$chatId = $arRes['CHAT_ID'];
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
			$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
			$arMessages = CIMMessageLink::prepareShow($arMessages, $params);
		}

		return Array('chatId' => $chatId, 'message' => $arMessages, 'unreadMessage' => $arUnreadMessage, 'usersMessage' => $arUsers, 'files' => $arMessageFiles);
	}

	function GetMoreMessage($pageId, $toUserId, $fromUserId = false, $bTimeZone = true)
	{
		global $DB;

		$iNumPage = 1;
		if (intval($pageId) > 0)
			$iNumPage = intval($pageId);

		$fromUserId = intval($fromUserId);
		if ($fromUserId <= 0)
			$fromUserId = $this->user_id;

		$toUserId = intval($toUserId);
		if ($toUserId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_ERROR_TO_USER_ID"), "ERROR_TO_USER_ID");
			return false;
		}

		$chatId = 0;
		$arMessages = Array();
		$arMessageId = Array();
		$arUnreadMessage = Array();
		$arMessageFiles = Array();
		$arUsers = Array();

		$limitById = '';

		if ($toUserId == $fromUserId)
		{
			$chat = new CIMChat();
			$chatId = $chat->GetPersonalChat();
			if (!$chatId)
			{
				return false;
			}
			$startId = 0;
		}
		else
		{
			$arRelation = \CIMChat::GetPrivateRelation($fromUserId, $toUserId);
			$chatId = $arRelation['CHAT_ID'] ?? null;
			$startId = $arRelation['START_ID'];
		}

		if ($chatId > 0)
		{
			if ($startId > 0)
			{
				$limitById = 'AND M.ID >= '.intval($startId);
			}
			$sqlStr = "
				SELECT COUNT(M.ID) as CNT
				FROM b_im_message M
				WHERE M.CHAT_ID = ".$chatId."
				".$limitById."
			";
			$res_cnt = $DB->Query($sqlStr);
			$res_cnt = $res_cnt->Fetch();
			$cnt = $res_cnt["CNT"];

			if ($cnt > 0 && ceil($cnt/30) >= $iNumPage)
			{
				if (!$bTimeZone)
					CTimeZone::Disable();
				$strSql ="
					SELECT
						M.ID,
						M.CHAT_ID,
						M.MESSAGE,
						".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
						M.AUTHOR_ID,
						M.NOTIFY_EVENT,
						".$fromUserId." R1_USER_ID,
						".$toUserId." R2_USER_ID
					FROM b_im_message M
					WHERE
						M.CHAT_ID = ".$chatId."
						".$limitById."
					ORDER BY M.DATE_CREATE DESC, M.ID DESC
				";
				if (!$bTimeZone)
					CTimeZone::Enable();
				$dbRes = new CDBResult();
				$dbRes->NavQuery($strSql, $cnt, Array('iNumPage' => $iNumPage, 'nPageSize' => 30));

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
					$arMessages[$arRes['ID']] = Array(
						'id' => $arRes['ID'],
						'chatId' => $arRes['CHAT_ID'],
						'senderId' => $arRes['FROM_USER_ID'],
						'recipientId' => $arRes['TO_USER_ID'],
						'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
						'system' => $arRes['NOTIFY_EVENT'] == 'private'? 'N': 'Y',
						'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
						'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
					);
					$arUsers[$convId][] = $arRes['ID'];
					$arMessageId[] = $arRes['ID'];
					$chatId = $arRes['CHAT_ID'];
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
				$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
				$arMessages = CIMMessageLink::prepareShow($arMessages, $params);
			}
		}


		return Array('chatId' => $chatId, 'message' => $arMessages, 'usersMessage' => $arUsers, 'files' => $arMessageFiles);
	}

	function RemoveMessage($messageId)
	{
		global $DB;

		return false;
	}

	function RemoveAllMessage($userId)
	{
		global $DB;

		$userId = intval($userId);

		if ($this->user_id == $userId)
		{
			return false;
		}

		$strSql ="
			SELECT
				MAX(M.ID)+1 MAX_ID,
				M.CHAT_ID,
				R1.ID R1_ID,
				R1.START_ID R1_START_ID,
				R2.ID R2_ID,
				R2.START_ID R2_START_ID
			FROM b_im_relation R1
			INNER JOIN b_im_relation R2 on R2.CHAT_ID = R1.CHAT_ID
			INNER JOIN b_im_message M ON M.ID >= R1.START_ID AND M.CHAT_ID = R1.CHAT_ID
			WHERE
				R1.USER_ID = ".$this->user_id."
			AND R2.USER_ID = ".$userId."
			AND R1.MESSAGE_TYPE = '".IM_MESSAGE_PRIVATE."'
			GROUP BY M.CHAT_ID, R1.ID, R1.START_ID, R2.ID, R2.START_ID
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$strSql = "UPDATE b_im_relation SET START_ID = ".intval($arRes['MAX_ID'])." WHERE ID = ".intval($arRes['R1_ID']);
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$counterService = new IM\V2\Message\CounterService($this->user_id);

			$counterService->deleteByChatId((int)$arRes['CHAT_ID']);

			if ($arRes['MAX_ID'] >= $arRes['R2_START_ID'] && $arRes['R2_START_ID'] > 0)
			{
				$messages = IM\Model\MessageTable::getList(array(
															   'select' => array('ID'),
															   'filter' => array(
																   '<ID' => $arRes['R2_START_ID'],
																   '=CHAT_ID' => $arRes['CHAT_ID'],
															   ),
														   ));
				while ($messageInfo = $messages->fetch())
				{
					IM\Model\MessageTable::delete($messageInfo['ID']);
				}
			}

			$primaryKey = ['USER_ID' => $this->user_id, 'ITEM_TYPE' => IM_MESSAGE_PRIVATE, 'ITEM_ID' => $userId];
			IM\Model\RecentTable::update($primaryKey, ['ITEM_MID' => 0]);
		}

		return true;
	}

	/* CHAT */
	function HideAllChatMessage($chatId)
	{
		global $DB;
		$chatId = intval($chatId);

		$limitById = '';
		$ar = \CIMChat::GetRelationById($chatId, $this->user_id, true, false);
		if ($ar && $ar['START_ID'] > 0)
		{
			$limitById = 'AND M.ID >= '.intval($ar['START_ID']);
		}

		$strSql ="
			SELECT
				MAX(M.ID)+1 MAX_ID,
				R1.ID R1_ID
			FROM b_im_relation R1
			INNER JOIN b_im_message M ON M.CHAT_ID = R1.CHAT_ID
			WHERE
				R1.CHAT_ID = ".$chatId."
				AND R1.USER_ID = ".$this->user_id."
				".$limitById."
			GROUP BY M.CHAT_ID, R1.ID
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$strSql = "UPDATE b_im_relation SET START_ID = ".intval($arRes['MAX_ID'])." WHERE ID = ".intval($arRes['R1_ID']);
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$counterService = new IM\V2\Message\CounterService($this->user_id);

			$counterService->deleteByChatId((int)$arRes['CHAT_ID']);

			$chatType = $ar ? $ar['MESSAGE_TYPE'] : IM_MESSAGE_CHAT;
			$primaryKey = ['USER_ID' => $this->user_id, 'ITEM_TYPE' => $chatType, 'ITEM_ID' => $chatId];
			IM\Model\RecentTable::update($primaryKey, ['ITEM_MID' => 0]);
		}

		return true;
	}

	function SearchChatMessage($searchText, $chatId, $bTimeZone = true)
	{
		$chatId = intval($chatId);
		$searchText = trim($searchText);

		if ($searchText == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_SEARCH_EMPTY"), "ERROR_SEARCH_EMPTY");
			return false;
		}

		if (!\Bitrix\Im\Dialog::hasAccess('chat'.$chatId, $this->user_id))
		{
			return false;
		}

		$where = Array(
			'=CHAT_ID' => $chatId,
			'*%MESSAGE' => $searchText,
		);

		if($this->checkFullText($searchText))
		{
			$where = Array(
				'=CHAT_ID' => $chatId,
				'*INDEX.SEARCH_CONTENT' => \Bitrix\Main\Search\Content::prepareStringToken($searchText),
			);
		}

		$ar = \CIMChat::GetRelationById($chatId, $this->user_id, true, false);
		if ($ar && $ar['START_ID'] > 0)
		{
			$where['>=ID'] = intval($ar['START_ID']);
		}

		$orm = \Bitrix\Im\Model\MessageTable::getList(
			[
				'select' => [
					'ID',
					'CHAT_ID',
					'MESSAGE',
					'AUTHOR_ID',
					'DATE_CREATE'
				],
				'filter' => $where,
				'order' => ['DATE_CREATE' => 'DESC', 'ID' => 'DESC'],
				'limit' => 1000,
			]
		);

		$arMessages = Array();
		$arMessageId = Array();
		$arUnreadMessage = Array();
		$usersMessage = Array();

		while ($arRes = $orm->fetch())
		{
			$arMessages[$arRes['ID']] = Array(
				'id' => $arRes['ID'],
				'chatId' => $arRes['CHAT_ID'],
				'senderId' => $arRes['AUTHOR_ID'],
				'recipientId' => $arRes['CHAT_ID'],
				'date' => $arRes['DATE_CREATE'],
				'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
				'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
			);

			$usersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];
			$arMessageId[] = $arRes['ID'];
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
		$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		return Array('chatId' => $chatId, 'message' => $arMessages, 'unreadMessage' => $arUnreadMessage, 'usersMessage' => $usersMessage, 'files' => $arMessageFiles);
	}

	function SearchDateChatMessage($searchDate, $chatId, $bTimeZone = true)
	{
		global $DB;

		$chatId = intval($chatId);

		$sqlHelper = Bitrix\Main\Application::getInstance()->getConnection()->getSqlHelper();
		try
		{
			$dateStart = \Bitrix\Main\Type\DateTime::createFromUserTime($searchDate);
			$sqlDateStart = $sqlHelper->getCharToDateFunction($dateStart->format("Y-m-d H:i:s"));

			$dateEnd = $dateStart->add('1 DAY');
			$sqlDateEnd = $sqlHelper->getCharToDateFunction($dateEnd->format("Y-m-d H:i:s"));
		}
		catch(\Bitrix\Main\ObjectException $e)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_HISTORY_SEARCH_DATE_EMPTY"), "ERROR_SEARCH_EMPTY");
			return false;
		}

		$limitById = '';
		$ar = \CIMChat::GetRelationById($chatId, $this->user_id, true, false);
		if ($ar && $ar['START_ID'] > 0)
		{
			$limitById = 'AND M.ID >= '.intval($ar['START_ID']);
		}

		if (!$bTimeZone)
			CTimeZone::Disable();
		$strSql ="
			SELECT
				M.ID,
				M.CHAT_ID,
				M.MESSAGE,
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
				M.AUTHOR_ID
			FROM b_im_relation R1
			INNER JOIN b_im_message M ON M.CHAT_ID = R1.CHAT_ID
			WHERE
				R1.CHAT_ID = ".$chatId."
			AND	R1.USER_ID = ".$this->user_id."
			AND M.DATE_CREATE >= ".$sqlDateStart." 
			AND M.DATE_CREATE <=  ".$sqlDateEnd."
				".$limitById."
			ORDER BY M.DATE_CREATE DESC, M.ID DESC
		";
		if (!$bTimeZone)
			CTimeZone::Enable();
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$arMessages = Array();
		$arMessageId = Array();
		$arUnreadMessage = Array();
		$usersMessage = Array();

		while ($arRes = $dbRes->Fetch())
		{
			$arMessages[$arRes['ID']] = Array(
				'id' => $arRes['ID'],
				'chatId' => $arRes['CHAT_ID'],
				'senderId' => $arRes['AUTHOR_ID'],
				'recipientId' => $arRes['CHAT_ID'],
				'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
				'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
				'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
			);

			$usersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];
			$arMessageId[] = $arRes['ID'];
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
		$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		return Array('chatId' => $chatId, 'message' => $arMessages, 'unreadMessage' => $arUnreadMessage, 'usersMessage' => $usersMessage, 'files' => $arMessageFiles);
	}

	/**
	 * @param $pageId
	 * @param $chatId
	 * @param bool $bTimeZone
	 * @return array
	 */
	function GetMoreChatMessage($pageId, $chatId, $bTimeZone = true)
	{
		global $DB;

		$iNumPage = 1;
		if (intval($pageId) > 0)
			$iNumPage = intval($pageId);

		$chatId = intval($chatId);

		$orm = IM\Model\ChatTable::getById($chatId);
		if (!($chatData = $orm->fetch()))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_CHAT_NOT_EXISTS"), "ERROR_CHAT_NOT_EXISTS");
			return false;
		}

		$limitById = '';
		$ar = \CIMChat::GetRelationById($chatId, $this->user_id, true, false);
		if ($ar && $ar['START_ID'] > 0)
		{
			$limitById = 'AND M.ID >= '.intval($ar['START_ID']);
		}

		if (!$bTimeZone)
			CTimeZone::Disable();
		if ($chatData['TYPE'] == IM_MESSAGE_OPEN)
		{
			$strCountSql ="
				SELECT COUNT(M.ID) as CNT
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.TYPE = '".IM_MESSAGE_OPEN."'
				WHERE M.CHAT_ID = ".$chatId." ".$limitById."
			";

			$strResultSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.TYPE = '".IM_MESSAGE_OPEN."'
				LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$this->user_id."
				WHERE 
					M.CHAT_ID = ".$chatId."
					".$limitById."
				ORDER BY M.DATE_CREATE DESC, M.ID DESC
			";
		}
		else if ($chatData['TYPE'] == IM_MESSAGE_OPEN_LINE && \Bitrix\Main\Loader::includeModule('imopenlines') && \Bitrix\ImOpenLines\Config::canJoin($chatId))
		{
			$strCountSql ="
				SELECT COUNT(M.ID) as CNT
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.TYPE = '".IM_MESSAGE_OPEN_LINE."'
				WHERE M.CHAT_ID = ".$chatId." ".$limitById."
			";

			$strResultSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID AND C.TYPE = '".IM_MESSAGE_OPEN_LINE."'
				LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$this->user_id."
				WHERE 
					M.CHAT_ID = ".$chatId."
					".$limitById."
				ORDER BY M.DATE_CREATE DESC, M.ID DESC
			";
		}
		else
		{
			$strCountSql ="
				SELECT COUNT(M.ID) as CNT
				FROM b_im_message M
				INNER JOIN b_im_relation R1 ON M.CHAT_ID = R1.CHAT_ID
				WHERE R1.CHAT_ID = ".$chatId." AND R1.USER_ID = ".$this->user_id." ".$limitById."
			";

			$strResultSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE
				FROM b_im_message M
				LEFT JOIN b_im_chat C ON M.CHAT_ID = C.ID
				WHERE 
					M.CHAT_ID = ".$chatId."
					".$limitById."
				ORDER BY M.DATE_CREATE DESC, M.ID DESC
			";
		}
		if (!$bTimeZone)
			CTimeZone::Enable();

		$res_cnt = $DB->Query($strCountSql);
		$res_cnt = $res_cnt->Fetch();
		$cnt = $res_cnt["CNT"];

		$arMessages = Array();
		$arMessageFiles = Array();
		$arMessageId = Array();
		$usersMessage = Array();
		if ($cnt > 0 && ceil($cnt/30) >= $iNumPage)
		{
			$dbRes = new CDBResult();
			$dbRes->NavQuery($strResultSql, $cnt, Array('iNumPage' => $iNumPage, 'nPageSize' => 30));

			while ($arRes = $dbRes->Fetch())
			{
				if ($arRes['CHAT_ENTITY_TYPE'] != 'LIVECHAT' && \Bitrix\Im\User::getInstance($this->user_id)->isConnector())
				{
					return false;
				}
				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['AUTHOR_ID'],
					'recipientId' => $arRes['CHAT_ID'],
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'system' => $arRes['AUTHOR_ID'] > 0? 'N': 'Y',
					'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE']),
					'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
				);

				$usersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];
				$arMessageId[] = $arRes['ID'];
				$arUsersIds[] = $arRes['AUTHOR_ID'];
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
			$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
			$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		}

		$users = CIMContactList::GetUserData(array(
			 'ID' => $arUsersIds,
			 'DEPARTMENT' => 'Y',
			 'USE_CACHE' => 'N',
			 'PHONES' => IsModuleInstalled('voximplant')? 'Y': 'N'
		 ));

		return Array(
			'chatId' => $chatId,
			'message' => $arMessages,
			'usersMessage' => $usersMessage,
			'users' => $users['users'],
			'userInGroup' => $users['userInGroup'],
			'phones' => $users['phones'],
			'files' => $arMessageFiles
		);
	}


	/* COMMON */
	public function GetMessagesByDate($chatId, $date, $messageStart = 0, $timezone = true)
	{
		$chatId = intval($chatId);
		$messageStart = intval($messageStart);

		if ($date instanceof \Bitrix\Main\Type\DateTime)
		{
			$checkDate = $date;
			$dateSql = $checkDate->format("Y-m-d H:i:s");
		}
		else
		{
			$checkDate = new \Bitrix\Main\Type\DateTime($date, "Y-m-d H:i:s");
			$dateSql = $checkDate->format("Y-m-d H:i:s");
			if ($dateSql != $date)
			{
				return false;
			}
		}

		$chatData = CIMChat::GetChatData(Array('ID' => $chatId, 'USER_ID' => $this->user_id));
		if (empty($chatData['chat']))
			return false;

		$dialogId = 0;
		$chatType = IM_MESSAGE_CHAT;
		if ($chatData['chat'][$chatId]['type'] == 'private')
		{
			$chatType = IM_MESSAGE_PRIVATE;
			foreach ($chatData['userInChat'][$chatId] as $userId)
			{
				if ($userId != $this->user_id)
				{
					$dialogId = $userId;
					break;
				}
			}
			if (!$dialogId)
			{
				return false;
			}
		}
		else
		{
			$dialogId = 'chat'.$chatId;
		}

		$chatSql = str_pad($chatId, 11, '0', STR_PAD_LEFT);

		global $DB;

		if (!$timezone)
			CTimeZone::Disable();

		$sql = "
			SELECT 
				IF (M.ID > 0, 'Y', 'N') MESSAGE_EXISTS, 
				IF (M.ID > R.LAST_ID, 'N', 'Y') MESSAGE_READ,
				C.TYPE CHAT_TYPE,
				MS.MESSAGE_ID ID,
				M.CHAT_ID,
				M.MESSAGE,
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
				M.AUTHOR_ID
			FROM b_im_message_param MS
			LEFT JOIN b_im_message M ON M.ID = MS.MESSAGE_ID
			LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID and R.USER_ID = ".$this->user_id."
			LEFT JOIN b_im_chat C ON C.ID = M.CHAT_ID
			WHERE 
				MS.PARAM_NAME='TS' and 
				MS.PARAM_VALUE between '".$chatSql." ".$dateSql."' and '".$chatSql." 9999-99-99 99:99:99'
				".($messageStart > 0? 'MS.MESSAGE_ID >= '.$messageStart: "")."
		";

		if (!$timezone)
			CTimeZone::Enable();

		$result = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$arMessages = Array();
		$arMessageUnread = Array();
		$arMessageDelete = Array();
		$arMessageId = Array();
		$usersMessage = Array();
		$arUsersIds = Array();

		while ($message = $result->Fetch())
		{
			if ($message['MESSAGE_EXISTS'] == 'N')
			{
				$arMessageDelete[] = $message['ID'];
				continue;
			}
			if ($message['MESSAGE_READ'] == 'N')
			{
				$arMessageUnread[$dialogId][] = $message['ID'];
			}
			if ($message['CHAT_TYPE'] == IM_MESSAGE_PRIVATE)
			{
				$recipientId = $message['AUTHOR_ID'] == $this->user_id? $dialogId: $this->user_id;
			}
			else
			{
				$recipientId = $message['CHAT_ID'];
			}
			$arMessages[$message['ID']] = Array(
				'id' => $message['ID'],
				'chatId' => $message['CHAT_ID'],
				'senderId' => $message['AUTHOR_ID'],
				'recipientId' => $recipientId,
				'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($message['DATE_CREATE']),
				'system' => $message['AUTHOR_ID'] > 0? 'N': 'Y',
				'text' => \Bitrix\Im\Text::parse($message['MESSAGE']),
				'textLegacy' => \Bitrix\Im\Text::parseLegacyFormat($arRes['MESSAGE'])
			);

			$usersMessage[$dialogId][] = $message['ID'];
			$arMessageId[] = $message['ID'];
			$arUsersIds[] = $message['AUTHOR_ID'];
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
		$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		$users = CIMContactList::GetUserData(array(
			 'ID' => $arUsersIds,
			 'DEPARTMENT' => 'Y',
			 'USE_CACHE' => 'N',
			 'PHONES' => IsModuleInstalled('voximplant')? 'Y': 'N'
		 ));

		return Array(
			'chatId' => $chatId,
			'dialogId' => $dialogId,
			'message' => $arMessages,
			'usersMessage' => $usersMessage,
			'unreadMessage' => $arMessageUnread,
			'messageDelete' => $arMessageDelete,
			'files' => $arMessageFiles,
			'users' => $users['users'],
			'userInGroup' => $users['userInGroup'],
			'phones' => $users['phones'],
			'lines' => $chatType == IM_MESSAGE_PRIVATE? Array(): $chatData['lines'],
			'chat' => $chatType == IM_MESSAGE_PRIVATE? Array(): $chatData['chat'],
			'userInChat' => $chatType == IM_MESSAGE_PRIVATE? Array(): $chatData['userInChat'],
			'userCallStatus' => $chatType == IM_MESSAGE_PRIVATE? Array(): $chatData['userCallStatus'],
			'userChatBlockStatus' => $chatType == IM_MESSAGE_PRIVATE? Array(): $chatData['userChatBlockStatus'],
		);
	}

	public function GetRelatedMessages($messageId, $previous = 10, $next = 10, $timezone = true, $textParser = true)
	{
		$message = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array(
				'ID','DATE_CREATE', 'CHAT_ID', 'AUTHOR_ID',
				'CHAT_TYPE' => 'CHAT.TYPE',
				'CHAT_ENTITY_TYPE' => 'CHAT.ENTITY_TYPE',
				'CHAT_ENTITY_DATA_1' => 'CHAT.ENTITY_DATA_1',
			),
			'filter' => Array(
				'=ID' => $messageId
			))
		)->fetch();
		if (!$message)
		{
			return false;
		}

		$relations = CIMChat::GetRelationById($message['CHAT_ID'], false, true, false);
		if (!isset($relations[$this->user_id]))
		{
			if (
				$message['CHAT_ENTITY_TYPE'] == 'LINES'
				&& \Bitrix\Main\Loader::includeModule('imopenlines')
			)
			{
				$explodeData = explode('|', $message['CHAT_ENTITY_DATA_1']);
				$crmEntityType = ($explodeData[0] == 'Y') ? $explodeData[1] : null;
				$crmEntityId = ($explodeData[0] == 'Y') ? intval($explodeData[2]) : null;

				if (!\Bitrix\ImOpenLines\Config::canJoin($message['CHAT_ID'], $crmEntityType, $crmEntityId))
				{
					return false;
				}
			}
			else
			{
				return false;
			}
		}
		elseif ((int)$messageId < (int)$relations[$this->user_id]['START_ID'])
		{
			return false;
		}

		$dialogId = 0;
		if ($message['CHAT_TYPE'] == IM_MESSAGE_PRIVATE)
		{
			if($message['CHAT_ENTITY_TYPE'] == IM_CHAT_TYPE_PERSONAL)
			{
				$dialogId = $this->user_id;
			}
			if ($message['AUTHOR_ID'] != $this->user_id)
			{
				$dialogId = $message['AUTHOR_ID'];
			}
			else
			{
				foreach ($relations as $userId => $data)
				{
					if ($userId != $this->user_id)
					{
						$dialogId = $userId;
						break;
					}
				}
				if (!$dialogId)
				{
					return false;
				}
			}
		}
		else
		{
			$dialogId = 'chat'.$message['CHAT_ID'];
		}

		$previousMessages = $this->GetPreviousMessages($messageId, $message['CHAT_ID'], $message['DATE_CREATE'], $previous, $timezone);
		$nextMessages = $this->GetNextMessages($messageId, $message['CHAT_ID'], $message['DATE_CREATE'], $next, $timezone);

		$startId = (int)($relations[$this->user_id]['START_ID'] ?? 0);
		if ($startId > 0)
		{
			$previousMessages = array_filter($previousMessages, static fn (array $message) => (int)$message['ID'] >= $startId);
		}

		$messages = array_replace($previousMessages, $nextMessages);

		$chatId = $message['CHAT_ID'];

		$arMessages = Array();
		$arMessageFiles = Array();
		$arMessageId = Array();
		$usersMessage = Array();
		$arUsersIds = Array();

		foreach ($messages as $mess)
		{
			if ($message['CHAT_TYPE'] == IM_MESSAGE_PRIVATE)
			{
				$recipientId = $mess['AUTHOR_ID'] == $this->user_id? $dialogId: $this->user_id;
			}
			else
			{
				$recipientId = $mess['CHAT_ID'];
			}
			$arMessages[$mess['ID']] = Array(
				'id' => $mess['ID'],
				'chatId' => $mess['CHAT_ID'],
				'senderId' => $mess['AUTHOR_ID'],
				'recipientId' => $recipientId,
				'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($mess['DATE_CREATE']),
				'system' => $mess['AUTHOR_ID'] > 0? 'N': 'Y',
				'text' => $textParser? \Bitrix\Im\Text::parse($mess['MESSAGE']): $mess['MESSAGE'],
				'textLegacy' => $textParser? \Bitrix\Im\Text::parseLegacyFormat($mess['MESSAGE']): $mess['MESSAGE']
			);

			$usersMessage[$dialogId][] = $mess['ID'];
			$arMessageId[] = $mess['ID'];
			$arUsersIds[] = $mess['AUTHOR_ID'];
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
		$arMessageFiles = CIMDisk::GetFiles($chatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		$users = CIMContactList::GetUserData(array(
												 'ID' => $arUsersIds,
												 'DEPARTMENT' => 'Y',
												 'USE_CACHE' => 'N',
												 'PHONES' => IsModuleInstalled('voximplant')? 'Y': 'N'
											 ));

		return Array(
			'chatId' => $chatId,
			'dialogId' => $dialogId,
			'message' => $arMessages,
			'usersMessage' => $usersMessage,
			'files' => $arMessageFiles,
			'users' => $users['users'],
			'userInGroup' => $users['userInGroup'],
			'phones' => $users['phones'],
		);
	}

	private function GetPreviousMessages($messageId, $chatId = null, $dateCreate = null, $limit = 10, $timezone = true)
	{
		global $DB;

		if (is_null($chatId) || !is_object($dateCreate))
		{
			$message = \Bitrix\Im\Model\MessageTable::getList(Array('select' => Array('DATE_CREATE', 'CHAT_ID'), 'filter' => Array('=ID' => $messageId)))->fetch();
			if (!$message)
				return false;

			$chatId = $message['CHAT_ID'];
			$dateCreate = $message['DATE_CREATE'];
		}
		else
		{
			$chatId = intval($chatId);
		}

		$limit = intval($limit)+1;

		if (!$timezone)
			CTimeZone::Disable();

		$sql =
			"SELECT 
				ID,
				CHAT_ID,
				MESSAGE,
				".$DB->DatetimeToTimestampFunction('DATE_CREATE')." DATE_CREATE,
				AUTHOR_ID
			FROM b_im_message
			WHERE
				CHAT_ID = ".$chatId." 
				and DATE_CREATE <= '".$dateCreate->format('Y-m-d H:i:s')."'
			ORDER BY CHAT_ID, DATE_CREATE desc 
			LIMIT ".$limit;

		if (!$timezone)
			CTimeZone::Enable();

		$result = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$messages = array();
		while ($message = $result->Fetch())
		{
			$messages[$message['ID']] = $message;
		}
		asort($messages);

		return $messages;
	}

	private function GetNextMessages($messageId, $chatId = null, $dateCreate = null, $limit = 10, $timezone = true)
	{
		global $DB;

		if (is_null($chatId) || !is_object($dateCreate))
		{
			$message = \Bitrix\Im\Model\MessageTable::getList(Array('select' => Array('DATE_CREATE', 'CHAT_ID'), 'filter' => Array('=ID' => $messageId)))->fetch();
			if (!$message)
				return false;

			$chatId = $message['CHAT_ID'];
			$dateCreate = $message['DATE_CREATE'];
		}
		else
		{
			$chatId = intval($chatId);
		}

		$limit = intval($limit)+1;

		if (!$timezone)
			CTimeZone::Disable();

		$sql =
			"SELECT 
				ID,
				CHAT_ID,
				MESSAGE,
				".$DB->DatetimeToTimestampFunction('DATE_CREATE')." DATE_CREATE,
				AUTHOR_ID
			FROM b_im_message
			WHERE
				CHAT_ID = ".$chatId." 
				and DATE_CREATE >= '".$dateCreate->format('Y-m-d H:i:s')."'
			ORDER BY CHAT_ID, DATE_CREATE asc 
			LIMIT ".$limit;

		if (!$timezone)
			CTimeZone::Enable();

		$result = $DB->Query($sql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$messages = array();
		while ($message = $result->Fetch())
		{
			$messages[$message['ID']] = $message;
		}

		return $messages;
	}

	private function checkFullText($searchText)
	{
		$indexEnabled = \Bitrix\Main\Config\Option::get('im', 'message_history_index');

		if (
			$indexEnabled &&
			\CIMMessenger::IsMysqlDb() &&
			\Bitrix\Main\Search\Content::canUseFulltextSearch($searchText) &&
			\Bitrix\Im\Model\MessageIndexTable::getEntity()->fullTextIndexEnabled("SEARCH_CONTENT")
		)
		{
			return true;
		}

		return false;
	}
}
?>