<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;

class CIMChat
{
	const CHAT_ALL = 'all';
	const GENERAL_MESSAGE_TYPE_JOIN = 'join';
	const GENERAL_MESSAGE_TYPE_LEAVE = 'leave';

	private $user_id = 0;
	private $bHideLink = false;
	public $lastAvatarId = 0;
	private static $entityOption = null;

	function __construct($user_id = null, $arParams = Array())
	{
		if (is_null($user_id))
		{
			global $USER;
			$this->user_id = IntVal($USER->GetID());
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
				M.*, C.TYPE CHAT_TYPE, R.USER_ID RID
			FROM
				b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
				LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID AND R.USER_ID = ".$this->user_id."
			WHERE
				M.ID = ".intval($ID)."
		";

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
			if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				if (intval($arRes['RID']) <= 0 && IM\User::getInstance($this->userId)->isExtranet())
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

	function GetLastMessage($toChatId, $fromUserId = false, $loadExtraData = false, $bTimeZone = true, $limit = true)
	{
		global $DB;

		$fromUserId = IntVal($fromUserId);
		if ($fromUserId <= 0)
			$fromUserId = $this->user_id;

		$toChatId = IntVal($toChatId);
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

		if ($limit)
		{
			$dbType = strtolower($DB->type);
			if ($dbType== "mysql")
				$sqlLimit = " AND M.DATE_CREATE > DATE_SUB(NOW(), INTERVAL 30 DAY)";
			else if ($dbType == "mssql")
				$sqlLimit = " AND M.DATE_CREATE > dateadd(day, -30, getdate())";
			else if ($dbType == "oracle")
				$sqlLimit = " AND M.DATE_CREATE > SYSDATE-30";
		}

		$limitById = '';
		$relations = \CIMChat::GetRelationById($toChatId);
		if (isset($relations[$fromUserId]) && $relations[$fromUserId]['START_ID'] > 0)
		{
			$limitById = 'AND M.ID >= '.intval($relations[$fromUserId]['START_ID']);
		}

		if (!$bTimeZone)
			CTimeZone::Disable();

		$strSql = "";
		if ($chatData['TYPE'] == IM_MESSAGE_OPEN)
		{
			$strSql = "
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					C.TYPE CHAT_TYPE,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					R.USER_ID RID
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
		else if ($chatData['TYPE'] == IM_MESSAGE_OPEN_LINE && \Bitrix\Main\Loader::includeModule('imopenlines') && \Bitrix\ImOpenLines\Config::canJoin($toChatId))
		{
			$strSql = "
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					C.TYPE CHAT_TYPE,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					R.USER_ID RID
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
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.AUTHOR_ID,
					C.TYPE CHAT_TYPE,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					'".$fromUserId."' RID
				FROM b_im_message M
				INNER JOIN b_im_chat C ON C.ID = M.CHAT_ID
				WHERE
					M.CHAT_ID = ".$toChatId."
					".$limitById."
					#LIMIT#
				ORDER BY M.DATE_CREATE DESC
			";
		}

		if (!$bTimeZone)
			CTimeZone::Enable();

		CIMStatus::Set($fromUserId, Array('IDLE' => null));

		$chatType = $chatData['TYPE'];
		$chatRelationUserId = 0;

		$arUsers = Array();
		$arMessages = Array();
		$arMessageId = Array();
		$arUsersMessage = Array();
		$readedList = Array();

		if ($strSql)
		{
			$strSql = $DB->TopSql($strSql, 20);
			//LEFT JOIN b_im_message_param MP on MP.MESSAGE_ID = M.ID and MP.PARAM_NAME = 'FOR_USER_ID'
			//and (MP.PARAM_VALUE is null or MP.PARAM_VALUE = '".$fromUserId."')

			if ($limit)
			{
				$dbRes = $DB->Query(str_replace("#LIMIT#", $sqlLimit, $strSql), false, "File: ".__FILE__."<br>Line: ".__LINE__);
				if (!$dbRes->SelectedRowsCount())
					$dbRes = $DB->Query(str_replace("#LIMIT#", "", $strSql), false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			else
			{
				$dbRes = $DB->Query(str_replace("#LIMIT#", "", $strSql), false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

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
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
					'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE'])
				);

				$arMessageId[] = $arRes['ID'];
				if ($arRes['AUTHOR_ID'] > 0)
				{
					$arUsers[] = $arRes['AUTHOR_ID'];
				}
				$arUsersMessage[$arRes['CHAT_ID']][] = $arRes['ID'];

				foreach ($relations as $relation)
				{
					$readedList['chat'.$arRes['CHAT_ID']][$relation['USER_ID']] = Array(
						'messageId' => $relation['LAST_ID'],
						'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($relation['LAST_READ']),
					);
				}
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
			else if (CModule::IncludeModule('pull'))
			{
				CPullWatch::Add($fromUserId, 'IM_PUBLIC_'.$toChatId, true);
			}
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

		$arChatFiles = CIMDisk::GetFiles($toChatId, $arFiles);
		$arMessages = CIMMessageLink::prepareShow($arMessages, $params);

		$arResult = Array(
			'chatId' => $toChatId,
			'message' => $arMessages,
			'usersMessage' => $arUsersMessage,
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
			list(, $lineId) = explode('|', $chatData["ENTITY_ID"]);
			$configManager = new \Bitrix\ImOpenLines\Config();
			$arResult['openlines']['canVoteAsHead'][$lineId] = $configManager->canVoteAsHead($lineId);
		}
		else if ($chatData['ENTITY_TYPE'] == 'LIVECHAT' && $chatData['ENTITY_ID'] && CModule::IncludeModule('imopenlines'))
		{
			list($lineId) = explode('|', $chatData["ENTITY_ID"]);
			foreach ($arResult['users'] as $userId => $userData)
			{
				$arResult['users'][$userId] = \Bitrix\ImOpenLines\Connector::getOperatorName($lineId, $userId, true);
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
				".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
				M.AUTHOR_ID,
				C.TYPE CHAT_TYPE
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

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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
				'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
				'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE'])
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
		if ($chatData["CHAT_TYPE"] == IM_MESSAGE_PRIVATE)
		{
			$chatType = 'private';
		}
		else if ($chatData["CHAT_ENTITY_TYPE"] == 'CALL')
		{
			$chatType = 'call';
		}
		else if ($chatData["CHAT_ENTITY_TYPE"] == 'LINES')
		{
			$chatType = 'lines';
		}
		else if ($chatData["CHAT_ENTITY_TYPE"] == 'LIVECHAT')
		{
			$chatType = 'livechat';
		}
		else
		{
			$chatType = $chatData["CHAT_TYPE"] == IM_MESSAGE_OPEN? 'open': 'chat';
		}

		return $chatType;
	}

	public function GetLastSendMessage($arParams)
	{
		global $DB;

		if (!isset($arParams['ID']))
			return false;

		$chatId = $arParams['ID'];

		$fromUserId = isset($arParams['FROM_USER_ID']) && IntVal($arParams['FROM_USER_ID'])>0? IntVal($arParams['FROM_USER_ID']): $this->user_id;
		$limit = isset($arParams['LIMIT']) && IntVal($arParams['LIMIT'])>0? IntVal($arParams['LIMIT']): false;
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
			$dbType = strtolower($DB->type);
			if ($dbType== "mysql")
				$sqlLimit = " AND M.DATE_CREATE > DATE_SUB(NOW(), INTERVAL ".$limit." DAY)";
			else if ($dbType == "mssql")
				$sqlLimit = " AND M.DATE_CREATE > dateadd(day, -".$limit.", getdate())";
			else if ($dbType == "oracle")
				$sqlLimit = " AND M.DATE_CREATE > SYSDATE-".$limit;
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
				C.TITLE CHAT_TITLE,
				C.COLOR CHAT_COLOR,
				C.ENTITY_TYPE CHAT_ENTITY_TYPE,
				C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
				C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
				C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
				".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." CHAT_DATE_CREATE,
				C.TYPE CHAT_TYPE,
				R.ID RID
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
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

			if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
			{
				if (!$enableOpenChat)
				{
					continue;
				}
				else if (intval($arRes['RID']) <= 0 && IM\User::getInstance($this->userId)->isExtranet())
				{
					continue;
				}
			}
			else if (intval($arRes['RID']) <= 0)
			{
				continue;
			}

			$chatType = self::getChatType($arRes);

			$arMessages[$arRes['CHAT_ID']] = Array(
				'id' => $arRes['ID'],
				'senderId' => $arRes['AUTHOR_ID'],
				'recipientId' => $arRes['CHAT_ID'],
				'chatTitle' => $arRes['CHAT_TITLE'],
				'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
				'color' => $arRes["CHAT_COLOR"] == ""? IM\Color::getColorByNumber($arRes['CHAT_ID']): IM\Color::getColor($arRes['CHAT_COLOR']),
				'type' => $chatType,
				'messageType' => $arRes["CHAT_TYPE"],
				'text' => \Bitrix\Im\Text::parse($arRes['MESSAGE'])
			);
		}

		return $arMessages;
	}

	public static function GetRelationById($ID, $userId = false)
	{
		global $DB;

		$ID = intval($ID);
		$userId = intval($userId);
		$arResult = Array();

		$strSql = "
			SELECT 
				R.*,
				".$DB->DatetimeToTimestampFunction('R.LAST_READ')." LAST_READ,
				U.EXTERNAL_AUTH_ID
			FROM b_im_relation R
			LEFT JOIN b_user U ON U.ID = R.USER_ID
			WHERE R.CHAT_ID = ".$ID." ".($userId>0? "AND R.USER_ID = ".$userId: "");
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($arRes = $dbRes->Fetch())
			$arResult[$arRes['USER_ID']] = $arRes;

		if ($userId > 0)
			$arResult = isset($arResult[$userId])? $arResult[$userId]: false;

		return $arResult;
	}

	public function GetPersonalChat()
	{
		$chatId = CUserOptions::GetOption('im', 'personalChat', 0);
		if ($chatId > 0)
			return $chatId;

		$chatId = $this->Add(Array(
			'TYPE' => IM_MESSAGE_PRIVATE,
			'USERS' => Array($this->user_id),
			'ENTITY_TYPE' => 'PERSONAL',
			'MESSAGE' => GetMessage('IM_PERSONAL_DESCRIPTION')
		));
		if ($chatId)
		{
			CUserOptions::SetOption('im', 'personalChat', $chatId, false, $this->user_id);
		}

		return $chatId;
	}

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
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
			$arResult = $arRes;

		return $arResult;
	}

	public static function GetGeneralChatId()
	{
		return COption::GetOptionString("im", "general_chat_id");
	}

	public static function InstallGeneralChat($agentMode = false)
	{
		$chatId = self::GetGeneralChatId();
		if ($chatId > 0)
			return $agentMode? false: true;

		if (!IsModuleInstalled('intranet'))
			return false;

		global $DB;

		$userCount = 0;

		$sqlCounter = "SELECT COUNT(ID) as CNT FROM b_user WHERE ACTIVE = 'Y' AND b_user.EXTERNAL_AUTH_ID NOT IN ('email', 'network', 'bot')";
		$dbRes = $DB->Query($sqlCounter, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($row = $dbRes->Fetch())
		{
			$userCount = $row['CNT'];
			if ($userCount > 50)
			{
				if (IsModuleInstalled('bitrix24'))
				{
					$perms = Array();
					$admins = \CBitrix24::getAllAdminId();
					foreach ($admins as $userId)
					{
						$perms[] = 'U'.$userId;
					}
					CIMChat::SetAccessToGeneralChat(false, $perms);
				}
				else if ($userCount > 500)
				{
					return false;
				}
			}
		}

		$res = $DB->Query("select ID from b_user_field where field_name='UF_DEPARTMENT'");
		if ($result = $res->Fetch())
		{
			$fieldId = intval($result['ID']);
		}
		else
		{
			return false;
		}

		$CIMChat = new self(0);
		$chatId = $CIMChat->Add(Array(
			'TYPE' => IM_MESSAGE_OPEN,
			'COLOR' => "AZURE",
			'USERS' => false,
			'TITLE' => GetMessage('IM_GENERAL_TITLE'),
			'DESCTIPTION' => GetMessage('IM_GENERAL_DESCRIPTION')
		));
		if (!$chatId)
			return false;

		$res = $DB->Query("select ID from b_user_field where field_name='UF_DEPARTMENT'");
		if ($result = $res->Fetch())
		{
			$fieldId = intval($result['ID']);
		}
		else
		{
			return false;
		}
		$sql = "
			insert into b_im_relation (USER_ID, MESSAGE_TYPE, CHAT_ID)
			select distinct b_user.ID, '".IM_MESSAGE_OPEN."', ".intval($chatId)."
			from b_user
			inner join b_utm_user on b_utm_user.VALUE_ID = b_user.ID and b_utm_user.FIELD_ID = ".$fieldId." and b_utm_user.VALUE_INT > 0
			WHERE b_user.ACTIVE = 'Y' AND (b_user.EXTERNAL_AUTH_ID NOT IN ('email', 'network', 'bot') OR b_user.EXTERNAL_AUTH_ID IS NULL OR b_user.EXTERNAL_AUTH_ID = '')
		";
		$result = $DB->Query($sql);
		if (!$result)
			return false;

		self::linkGeneralChatId($chatId);

		$CIMChat->AddMessage(Array(
			"TO_CHAT_ID" => $chatId,
			"MESSAGE" => GetMessage('IM_GENERAL_DESCRIPTION'),
			"FROM_USER_ID" => 0,
			"SYSTEM" => 'Y',
		));

		return $agentMode? false: true;
	}

	public static function GetGeneralChatAutoMessageStatus($type)
	{
		$status = false;
		if ($type == self::GENERAL_MESSAGE_TYPE_JOIN)
		{
			$status = COption::GetOptionString("im", "general_chat_message_join");
		}
		else if ($type == self::GENERAL_MESSAGE_TYPE_LEAVE)
		{
			$status = COption::GetOptionString("im", "general_chat_message_leave");
		}

		return $status;
	}

	public static function CanSendMessageToGeneralChat($userId)
	{
		global $USER;

		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		if (COption::GetOptionString("im", "allow_send_to_general_chat_all", "Y") == "Y")
			return true;

		$chatRights = COption::GetOptionString("im", "allow_send_to_general_chat_rights");

		if (!empty($chatRights))
		{
			$arUserGroupCode = $USER->GetAccessCodes();
			$chatRights = explode(",", $chatRights);

			foreach($chatRights as $right)
			{
				if (in_array($right, $arUserGroupCode))
				{
					return true;
				}
			}

			return false;
		}

		return false;
	}

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
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					)
				));
			}
		}

		return true;
	}

	public static function CanJoinGeneralChatId($userId)
	{
		$userId = intval($userId);
		if ($userId <= 0)
			return false;

		$chatId = self::GetGeneralChatId();
		if ($chatId <= 0)
			return false;

		if (!IsModuleInstalled('intranet'))
			return false;

		global $DB;

		$res = $DB->Query("select ID from b_user_field where field_name='UF_DEPARTMENT'");
		if ($result = $res->Fetch())
		{
			$fieldId = intval($result['ID']);
		}
		else
		{
			return false;
		}

		$result = false;

		$sql = "
			SELECT UU.ID, U.ACTIVE, U.EXTERNAL_AUTH_ID
			FROM b_utm_user UU LEFT JOIN b_user U ON U.ID = UU.VALUE_ID
			WHERE UU.VALUE_ID = ".$userId." and UU.FIELD_ID = ".$fieldId." and UU.VALUE_INT > 0";
		$res = $DB->Query($sql);
		if ($row = $res->Fetch())
		{
			$result = $row['ACTIVE'] == 'Y';
		}

		return $result;
	}

	public static function linkGeneralChatId($chatId)
	{
		COption::SetOptionInt("im", "general_chat_id", $chatId);

		if (CModule::IncludeModule('pull'))
		{
			CPullStack::AddShared(Array(
				'module_id' => 'im',
				'command' => 'generalChatId',
				'params' => Array(
					"id" => $chatId
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				)
			));
		}

		return true;
	}

	public static function UnlinkGeneralChatId()
	{
		COption::RemoveOption("im", "general_chat_id");

		if (CModule::IncludeModule('pull'))
		{
			CPullStack::AddShared(Array(
				'module_id' => 'im',
				'command' => 'generalChatId',
				'params' => Array(
					"id" => 0
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				)
			));
		}

		return true;
	}

	public static function GetChatData($arParams = Array())
	{
		global $DB;

		$arParams['PHOTO_SIZE'] = isset($arParams['PHOTO_SIZE'])? intval($arParams['PHOTO_SIZE']): 100;

		$from = "
			FROM b_im_relation R1
			INNER JOIN b_im_chat C ON C.ID = R1.CHAT_ID
		";

		if (isset($arParams['SKIP_PRIVATE']) && $arParams['SKIP_PRIVATE'] == 'Y')
		{
			$from .= " AND C.TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."')";
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
			$whereGeneral = "WHERE R1.CHAT_ID IN (".implode(',', $arFilter['ID']).") ";
		}

		$strSql = "
			SELECT
				C.ID CHAT_ID,
				C.TITLE CHAT_TITLE,
				C.CALL_TYPE CHAT_CALL_TYPE,
				C.AUTHOR_ID CHAT_OWNER_ID,
				C.CALL_NUMBER CHAT_CALL_NUMBER,
				C.EXTRANET CHAT_EXTRANET,
				C.COLOR CHAT_COLOR,
				C.TYPE CHAT_TYPE,
				C.AVATAR,
				C.ENTITY_TYPE,
				C.ENTITY_DATA_1 ENTITY_DATA_1,
				C.ENTITY_DATA_2 ENTITY_DATA_2,
				C.ENTITY_DATA_3 ENTITY_DATA_3,
				".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." CHAT_DATE_CREATE,
				C.ENTITY_ID,
				R1.NOTIFY_BLOCK RELATION_NOTIFY_BLOCK,
				R1.USER_ID RELATION_USER_ID,
				R1.CALL_STATUS
				".(isset($arParams['USER_ID'])? ", R2.ID RID": "")."
			".$from."
			".$innerJoin."
			".$whereGeneral."
		";

		$arChat = Array();
		$arLines = Array();
		$arUserInChat = Array();
		$arUserCallStatus = Array();
		$arUserChatBlockStatus = Array();
		$enableOpenChat = CIMMessenger::CheckEnableOpenChat();
		$generalChatId = CIMChat::GetGeneralChatId();

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
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
					continue;
				}
			}

			$arRes["RELATION_USER_ID"] =  (int)$arRes["RELATION_USER_ID"];

			if (!isset($arChat[$arRes["CHAT_ID"]]))
			{
				$avatar = '/bitrix/js/im/images/blank.gif';
				if (intval($arRes["AVATAR"]) > 0)
				{
					$avatar = self::GetAvatarImage($arRes["AVATAR"], $arParams['PHOTO_SIZE']);
				}
				if ($arRes["CHAT_TYPE"] == IM_MESSAGE_PRIVATE)
				{
					$chatType = 'private';
				}
				else if ($arRes["ENTITY_TYPE"] == 'CALL')
				{
					$chatType = 'call';
				}
				else if ($arRes["ENTITY_TYPE"] == 'LINES')
				{
					$chatType = 'lines';

					$fieldData = explode("|", $arRes['ENTITY_DATA_1']);
					$arLines[$arRes["CHAT_ID"]] = $fieldData[5];
				}
				else if ($arRes["ENTITY_TYPE"] == 'LIVECHAT')
				{
					$chatType = 'livechat';
				}
				else
				{
					if ($generalChatId == $arRes['CHAT_ID'])
					{
						$arRes["ENTITY_TYPE"] = 'GENERAL';
					}
					$chatType = $arRes["CHAT_TYPE"] == IM_MESSAGE_OPEN? 'open': 'chat';
				}

				$arChat[$arRes["CHAT_ID"]] = Array(
					'id' => $arRes["CHAT_ID"],
					'name' => $arRes["CHAT_TITLE"],
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
					'mute_list' => array(),
					'date_create' => $arRes["CHAT_DATE_CREATE"]? \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes["CHAT_DATE_CREATE"]): false,
					'type' => $chatType,
					'message_type' => $arRes["CHAT_TYPE"],
				);
			}
			$arUserInChat[$arRes["CHAT_ID"]][] = $arRes["RELATION_USER_ID"];
			$arUserChatBlockStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = $arRes["RELATION_NOTIFY_BLOCK"] == 'Y';
			$arUserCallStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = trim($arRes["CALL_STATUS"]);

			$arChat[$arRes["CHAT_ID"]]['mute_list'] = $arUserChatBlockStatus[$arRes["CHAT_ID"]];
		}

		$lines = Array();
		if (!empty($arLines) && CModule::IncludeModule('imopenlines'))
		{
			$orm = \Bitrix\Imopenlines\Model\SessionTable::getList(Array(
				'select' => Array('CHAT_ID', 'ID', 'STATUS'),
				'filter' => Array(
					'=ID' => array_values($arLines)
				)
			));
			while($row = $orm->fetch())
			{
				$lines[$row['CHAT_ID']] = Array(
					'id' => (int)$row['ID'],
					'status' => (int)$row['STATUS'],
				);
			}
		}


		$result = array(
			'chat' => $arChat,
			'lines' => $lines,
			'userInChat' => $arUserInChat,
			'userCallStatus' => $arUserCallStatus,
			'userChatBlockStatus' => $arUserChatBlockStatus
		);

		return $result;
	}

	public static function GetOpenChatData($arParams = Array())
	{
		global $DB;

		$arParams['PHOTO_SIZE'] = isset($arParams['PHOTO_SIZE'])? intval($arParams['PHOTO_SIZE']): 100;

		$existsSql = "SELECT R3.ID FROM b_im_relation R3 WHERE R3.CHAT_ID = C.ID";
		if ($DB->type == "MYSQL")
		{
			$existsSql .= ' LIMIT 1';
		}
		$strSql = "
			SELECT
				C.ID CHAT_ID,
				C.TITLE CHAT_TITLE,
				C.CALL_TYPE CHAT_CALL_TYPE,
				C.AUTHOR_ID CHAT_OWNER_ID,
				C.CALL_NUMBER CHAT_CALL_NUMBER,
				C.EXTRANET CHAT_EXTRANET,
				C.COLOR CHAT_COLOR,
				C.TYPE CHAT_TYPE,
				C.AVATAR,
				C.ENTITY_TYPE,
				C.ENTITY_DATA_1,
				C.ENTITY_DATA_2,
				C.ENTITY_DATA_3,
				".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." CHAT_DATE_CREATE,
				C.ENTITY_ID,
				R2.NOTIFY_BLOCK RELATION_NOTIFY_BLOCK,
				R2.USER_ID RELATION_USER_ID,
				R2.CALL_STATUS,
				R2.ID RID
			FROM b_im_chat C
			LEFT JOIN b_im_relation R2 ON R2.CHAT_ID = C.ID AND R2.USER_ID = ".intval($arParams['USER_ID'])."
			WHERE C.TYPE = '".IM_MESSAGE_OPEN."' AND EXISTS(".$existsSql.")
		";

		$arChat = Array();
		$arUserInChat = Array();
		$arUserCallStatus = Array();
		$arUserChatBlockStatus = Array();

		$generalChatId = CIMChat::GetGeneralChatId();

		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
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
				if ($arRes["CHAT_TYPE"] == IM_MESSAGE_PRIVATE)
				{
					$chatType = 'private';
				}
				else if ($arRes["ENTITY_TYPE"] == 'CALL')
				{
					$chatType = 'call';
				}
				else if ($arRes["ENTITY_TYPE"] == 'LINES')
				{
					$chatType = 'lines';
				}
				else if ($arRes["ENTITY_TYPE"] == 'LIVECHAT')
				{
					$chatType = 'livechat';
				}
				else
				{
					if ($generalChatId == $arRes['CHAT_ID'])
					{
						$arRes["ENTITY_TYPE"] = 'GENERAL';
					}
					$chatType = $arRes["CHAT_TYPE"] == IM_MESSAGE_OPEN? 'open': 'chat';
				}

				$arRes['RELATION_NOTIFY_BLOCK'] = (int)$arRes['RELATION_NOTIFY_BLOCK'];

				$arChat[$arRes["CHAT_ID"]] = Array(
					'id' => $arRes["CHAT_ID"],
					'name' => $arRes["CHAT_TITLE"],
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
					'date_create' => $arRes["CHAT_DATE_CREATE"]? \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes["CHAT_DATE_CREATE"]): false,
					'entity_id' => trim($arRes["ENTITY_ID"]),
					'type' => $chatType,
					'message_type' => $arRes["CHAT_TYPE"],
				);

			}
			$arUserChatBlockStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = $arRes['RELATION_NOTIFY_BLOCK'] == 'Y';
			$arUserInChat[$arRes["CHAT_ID"]][] = $arRes["RELATION_USER_ID"];
			$arUserCallStatus[$arRes["CHAT_ID"]][$arRes["RELATION_USER_ID"]] = trim($arRes["CALL_STATUS"]);

			$arChat[$arRes["CHAT_ID"]]['mute_list'] = $arUserChatBlockStatus[$arRes["CHAT_ID"]];
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

		$sqlLastId = '';
		if (intval($lastId) > 0)
			$sqlLastId = "AND M.ID <= ".intval($lastId);

		CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_GROUP);

		$strSql = "
			SELECT
				COUNT(M.ID) CNT,
				MAX(M.ID) END_ID,
				R1.LAST_ID START_ID,
				C.TYPE CHAT_TYPE,
				C.ID CHAT_ID,
				C.ENTITY_TYPE CHAT_ENTITY_TYPE,
				C.ENTITY_ID CHAT_ENTITY_ID
			FROM b_im_message M
			INNER JOIN b_im_relation R1 ON M.ID > R1.LAST_ID ".$sqlLastId." AND M.CHAT_ID = R1.CHAT_ID
			LEFT JOIN b_im_chat C ON R1.CHAT_ID = C.ID
			WHERE R1.CHAT_ID = ".$chatId." AND R1.USER_ID = ".$this->user_id."
			GROUP BY M.CHAT_ID, R1.LAST_ID, C.ID, C.ENTITY_TYPE, C.ENTITY_ID
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$relation = CIMMessage::SetLastId($chatId, $this->user_id, $arRes['END_ID']);
			if ($relation)
			{
				if (CModule::IncludeModule("pull"))
				{
					CPushManager::DeleteFromQueueBySubTag($this->user_id, 'IM_MESS');
					\Bitrix\Pull\Event::add($this->user_id, Array(
						'module_id' => 'im',
						'command' => 'readMessageChat',
						'params' => Array(
							'dialogId' => 'chat'.$chatId,
							'chatId' => $chatId,
							'lastId' => $arRes['END_ID'],
							'counter' => $relation['COUNTER']
						),
						'extra' => Array(
							'im_revision' => IM_REVISION,
							'im_revision_mobile' => IM_REVISION_MOBILE,
						)
					));

					$arRelation = self::GetRelationById($chatId);
					unset($arRelation[$this->user_id]);

					$pushMessage = Array(
						'module_id' => 'im',
						'command' => 'readMessageChatOpponent',
						'expiry' => 600,
						'params' => Array(
							'dialogId' => 'chat'.$chatId,
							'chatId' => $chatId,
							'userId' => $this->user_id,
							'lastId' => $arRes['END_ID'],
							'date' => date('c', time()),
							'chatMessageStatus' => $relation['CHAT_MESSAGE_STATUS'],
						),
						'extra' => Array(
							'im_revision' => IM_REVISION,
							'im_revision_mobile' => IM_REVISION_MOBILE,
						)
					);
					if ($arRes['CHAT_ENTITY_TYPE'] == 'LINES')
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
						if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN  || $arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE)
						{
							CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
						}
					}
				}
				foreach(GetModuleEvents("im", "OnAfterChatRead", true) as $arEvent)
				{
					ExecuteModuleEventEx($arEvent, array(Array(
						'CHAT_ID' => $arRes['CHAT_ID'],
						'CHAT_ENTITY_TYPE' => $arRes['CHAT_ENTITY_TYPE'],
						'CHAT_ENTITY_ID' => $arRes['CHAT_ENTITY_ID'],
						'START_ID' => $arRes['START_ID'],
						'END_ID' => $arRes['END_ID'],
						'COUNT' => $relation['COUNT'],
						'USER_ID' => $this->user_id,
						'BY_EVENT' => $byEvent
					)));
				}

				return true;
			}
		}

		return false;
	}

	public function SetUnReadMessage($chatId, $lastId)
	{
		global $DB;

		$chatId = intval($chatId);
		if ($chatId <= 0)
			return false;

		$sqlLastId = '';
		$lastId = intval($lastId);
		if (intval($lastId) <= 0)
			return false;

		$strSql = "
			SELECT
				COUNT(M.ID) CNT,
				MAX(M.ID) END_ID,
				'$lastId' START_ID,
				C.ID CHAT_ID,
				C.ENTITY_TYPE CHAT_ENTITY_TYPE,
				C.ENTITY_ID CHAT_ENTITY_ID
			FROM b_im_message M
			INNER JOIN b_im_relation R1 ON M.ID >= ".$lastId." AND M.CHAT_ID = R1.CHAT_ID
			LEFT JOIN b_im_chat C ON R1.CHAT_ID = C.ID
			WHERE R1.CHAT_ID = ".$chatId." AND R1.USER_ID = ".$this->user_id."
			GROUP BY M.CHAT_ID, R1.LAST_ID, C.ID, C.ENTITY_TYPE, C.ENTITY_ID
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$relation = CIMMessage::SetLastIdForUnread($chatId, $this->user_id, $lastId);
			if ($relation)
			{
				CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_GROUP);

				if (CModule::IncludeModule("pull"))
				{
					\Bitrix\Pull\Event::add($this->user_id, Array(
						'module_id' => 'im',
						'command' => 'unreadMessageChat',
						'params' => Array(
							'dialogId' => 'chat'.$chatId,
							'chatId' => $chatId,
							'lastId' => $arRes['END_ID'],
							'date' => new \Bitrix\Main\Type\DateTime(),
							'counter' => $relation['COUNTER']
						),
						'push' => Array('badge' => 'Y'),
						'extra' => Array(
							'im_revision' => IM_REVISION,
							'im_revision_mobile' => IM_REVISION_MOBILE,
						)
					));

					$arRelation = self::GetRelationById($chatId);
					unset($arRelation[$this->user_id]);

					$pushMessage = Array(
						'module_id' => 'im',
						'command' => 'unreadMessageChatOpponent',
						'expiry' => 600,
						'params' => Array(
							'dialogId' => 'chat'.$chatId,
							'chatId' => $chatId,
							'userId' => $this->user_id,
							'chatMessageStatus' => $relation['CHAT_MESSAGE_STATUS'],
						),
						'extra' => Array(
							'im_revision' => IM_REVISION,
							'im_revision_mobile' => IM_REVISION_MOBILE,
						),
					);
					if ($arRes['CHAT_ENTITY_TYPE'] == 'LINES')
					{
						foreach ($arRelation as $rel)
						{
							if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
							{
								unset($arRelation[$rel["USER_ID"]]);
							}
						}
					}
					\Bitrix\Pull\Event::add(array_keys($arRelation), $pushMessage);
					if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN || $arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE)
					{
						CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
					}
				}

				return true;
			}
		}

		return false;
	}

	public function GetUnreadMessage($arParams = Array())
	{
		global $DB;

		$bSpeedCheck = isset($arParams['SPEED_CHECK']) && $arParams['SPEED_CHECK'] == 'N'? false: true;
		$lastId = !isset($arParams['LAST_ID']) || $arParams['LAST_ID'] == null? null: IntVal($arParams['LAST_ID']);
		$loadDepartment = isset($arParams['LOAD_DEPARTMENT']) && $arParams['LOAD_DEPARTMENT'] == 'N'? false: true;
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;
		$bGroupByChat = isset($arParams['GROUP_BY_CHAT']) && $arParams['GROUP_BY_CHAT'] == 'Y'? true: false;
		$bUserLoad = isset($arParams['USER_LOAD']) && $arParams['USER_LOAD'] == 'N'? false: true;
		$bFileLoad = isset($arParams['FILE_LOAD']) && $arParams['FILE_LOAD'] == 'N'? false: true;
		$arExistUserData = isset($arParams['EXIST_USER_DATA']) && is_array($arParams['EXIST_USER_DATA'])? $arParams['EXIST_USER_DATA']: Array();
		$messageType = isset($arParams['MESSAGE_TYPE']) && in_array($arParams['MESSAGE_TYPE'], Array(IM_MESSAGE_OPEN, IM_MESSAGE_CHAT, IM_MESSAGE_OPEN_LINE))? $arParams['MESSAGE_TYPE']: 'ALL';

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
			$ssqlLastId = "R1.LAST_ID";
			$ssqlStatus = " AND R1.STATUS < ".IM_STATUS_READ;
			if (!is_null($lastId) && intval($lastId) > 0 && !CIMMessenger::CheckXmppStatusOnline())
			{
				$ssqlLastId = intval($lastId);
				$ssqlStatus = "";
			}

			$arRelations = Array();
			if (strlen($ssqlStatus) > 0)
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
				$dbSubRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while ($arRes = $dbSubRes->Fetch())
				{
					$arRelations[] = $arRes;
				}
			}

			$arMessageId = Array();
			$arMessageChatId = Array();
			$arLastMessage = Array();
			$arMark = Array();
			$arChat = Array();

			$arPrepareResult = Array();
			$arFilteredResult = Array();

			if (!empty($arRelations))
			{
				if (!$bTimeZone)
					CTimeZone::Disable();
				$strSql = "
					SELECT
						M.ID,
						M.CHAT_ID,
						M.MESSAGE,
						".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
						M.AUTHOR_ID,
						R1.STATUS R1_STATUS,
						R1.MESSAGE_TYPE MESSAGE_TYPE
					FROM b_im_message M
					INNER JOIN b_im_relation R1 ON M.ID > ".$ssqlLastId." AND M.CHAT_ID = R1.CHAT_ID AND R1.USER_ID != M.AUTHOR_ID
					WHERE
						R1.USER_ID = ".$this->user_id."
						".($messageType == 'ALL'? "AND R1.MESSAGE_TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."')": "AND R1.MESSAGE_TYPE = '".$messageType."'")."
						".$ssqlStatus."
				";
				if (!$bTimeZone)
					CTimeZone::Enable();

				$strSql = $DB->TopSql($strSql, 1000);

				$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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
			}

			foreach ($arFilteredResult as $arRes)
			{
				$arUsers[] = $arRes['AUTHOR_ID'];

				$arMessages[$arRes['ID']] = Array(
					'id' => $arRes['ID'],
					'chatId' => $arRes['CHAT_ID'],
					'senderId' => $arRes['AUTHOR_ID'],
					'recipientId' => $arRes['CHAT_ID'],
					'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arRes['DATE_CREATE']),
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
					if ($this->user_id != $arRes['AUTHOR_ID'])
						$arUnreadMessage[$arRes['CHAT_ID']][] = $arRes['ID'];
				}

				if ($arRes['R1_STATUS'] == IM_STATUS_UNREAD && (!isset($arMark[$arRes["CHAT_ID"]]) || $arMark[$arRes["CHAT_ID"]] < $arRes["ID"]))
					$arMark[$arRes["CHAT_ID"]] = $arRes["ID"];

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
			else
			{
				foreach ($arRelations as $relation)
					CIMMessage::SetLastId($relation['CHAT_ID'], $relation['USER_ID']);
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
			return false;

		if ($checkPermission && $chat['AUTHOR_ID'] != $this->user_id)
			return false;

		if ($userId == $chat['AUTHOR_ID'])
			return true;

		$arRelation = self::GetRelationById($chatId);
		if (!isset($arRelation[$userId]))
			return false;

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
					'chatId' => $chatId,
					'userId' => $userId
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}

	public function SetDescription($chatId, $description)
	{
		\Bitrix\Im\Model\ChatTable::update($chatId, Array(
			'DESCRIPTION' => $description
		));

		if (CModule::IncludeModule('pull'))
		{
			$arRelation = self::GetRelationById($chatId);
			\Bitrix\Pull\Event::add(array_keys($arRelation), Array(
				'module_id' => 'im',
				'command' => 'chatDescription',
				'params' => Array(
					'chatId' => $chatId,
					'description' => \Bitrix\Im\Text::parse($description)
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
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
		$chatId = intval($chatId);
		$chat = IM\Model\ChatTable::getById($chatId)->fetch();
		if (!$chat)
			return false;

		if ($checkPermission && $chat['AUTHOR_ID'] != $this->user_id)
			return false;

		$relation = self::GetRelationById($chatId);

		$statuses = Array();
		foreach ($users as $userId => $status)
		{
			$userId = intval($userId);
			if ($userId == $chat['AUTHOR_ID'] || $userId <= 0)
				continue;

			if (!isset($relation[$userId]))
				continue;

			$statuses[$userId] = $status? 'Y': 'N';

			IM\Model\RelationTable::update($relation[$userId]['ID'], Array('MANAGER' => $statuses[$userId]));
		}

		if (CModule::IncludeModule('pull'))
		{
			\Bitrix\Pull\Event::add(array_keys($relation), Array(
				'module_id' => 'im',
				'command' => 'chatManagers',
				'params' => Array(
					'chatId' => $chatId,
					'status' => $statuses
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}

	public function SetColor($chatId, $color)
	{
		global $DB;
		$chatId = intval($chatId);
		$color = ToUpper($color);

		if ($chatId <= 0 || !IM\Color::isSafeColor($color))
			return false;

		$strSql = "
			SELECT R.CHAT_ID, C.COLOR CHAT_COLOR, C.AUTHOR_ID CHAT_AUTHOR_ID, C.TYPE CHAT_TYPE, C.ENTITY_TYPE CHAT_ENTITY_TYPE, C.ENTITY_ID CHAT_ENTITY_ID
			FROM b_im_relation R LEFT JOIN b_im_chat C ON R.CHAT_ID = C.ID
			WHERE R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."') AND R.CHAT_ID = ".$chatId;
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
			if ($arRes['CHAT_COLOR'] == $color)
				return false;

			IM\Model\ChatTable::update($chatId, array('COLOR' => $color));

			CIMChat::AddSystemMessage(Array(
				'CHAT_ID' => $chatId,
				'USER_ID' => $this->user_id,
				'MESSAGE_CODE' => 'IM_CHAT_CHANGE_COLOR_',
				'MESSAGE_REPLACE' => Array('#CHAT_COLOR#' => IM\Color::getName($color))
			));

			$ar = CIMChat::GetRelationById($chatId);
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
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				);
				\Bitrix\Pull\Event::add(array_keys($ar), $arPushMessage);
				if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN || $arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE)
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
			return false;

		$orm = \Bitrix\Im\Model\ChatTable::getById($chatId);
		$chat = $orm->fetch();
		if (!$chat)
			return false;

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
			$relation = self::GetRelationById($chatId);
			\Bitrix\Pull\Event::add(array_keys($relation), Array(
				'module_id' => 'im',
				'command' => 'chatAvatar',
				'params' => Array(
					'chatId' => $chatId,
					'avatar' => self::GetAvatarImage($fileId),
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}

	public function Rename($chatId, $title, $checkPermission = true, $sendMessage = true)
	{
		global $DB;
		$chatId = intval($chatId);
		$title = substr(trim($title), 0, 255);

		if ($chatId <= 0 || strlen($title) <= 0)
			return false;

		if ($checkPermission)
		{
			$strSql = "
				SELECT R.CHAT_ID, C.TITLE CHAT_TITLE, C.AUTHOR_ID CHAT_AUTHOR_ID, C.TYPE CHAT_TYPE, C.ENTITY_TYPE CHAT_ENTITY_TYPE, C.ENTITY_ID CHAT_ENTITY_ID
				FROM b_im_relation R LEFT JOIN b_im_chat C ON R.CHAT_ID = C.ID
				WHERE R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."') AND R.CHAT_ID = ".$chatId;
		}
		else
		{
			$strSql = "
				SELECT C.ID CHAT_ID, C.TITLE CHAT_TITLE, C.AUTHOR_ID CHAT_AUTHOR_ID, C.TYPE CHAT_TYPE, C.ENTITY_TYPE CHAT_ENTITY_TYPE, C.ENTITY_ID CHAT_ENTITY_ID
				FROM b_im_chat C
				WHERE C.ID = ".$chatId." AND C.TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."')";
		}
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			if ($arRes['CHAT_TITLE'] == $title)
				return false;

			$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);

			IM\Model\ChatTable::update($chatId, array('TITLE' => $title));

			if ($sendMessage)
			{
				if ($checkPermission)
				{
					CIMChat::AddSystemMessage(Array(
						'CHAT_ID' => $chatId,
						'USER_ID' => $this->user_id,
						'MESSAGE_CODE' => 'IM_CHAT_CHANGE_TITLE_',
						'MESSAGE_REPLACE' => Array('#CHAT_TITLE#' => $title)
					));
				}
				else
				{
					self::AddMessage(Array(
						"TO_CHAT_ID" => $chatId,
						"MESSAGE" => GetMessage("IM_CHAT_CHANGE_TITLE", Array('#CHAT_TITLE#' => $title)),
						"SYSTEM" => 'Y',
					));
				}
			}

			$ar = CIMChat::GetRelationById($chatId);
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
						'name' => $title,
					),
					'extra' => Array(
						'im_revision' => IM_REVISION,
						'im_revision_mobile' => IM_REVISION_MOBILE,
					),
				);
				\Bitrix\Pull\Event::add(array_keys($ar), $pushMessage);
				if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN || $arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE)
				{
					CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
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

	public function Add($arParams)
	{
		global $DB;

		$chatTitle = '';
		if (isset($arParams['TITLE']))
			$chatTitle = trim($arParams['TITLE']);

		$chatDescription = '';
		if (isset($arParams['DESCRIPTION']))
			$chatDescription = trim($arParams['DESCRIPTION']);

		$userId = Array();
		if (isset($arParams['USERS']))
			$userId = $arParams['USERS'];

		$callNumber = '';
		if (isset($arParams['CALL_NUMBER']))
			$callNumber = $arParams['CALL_NUMBER'];

		$avatarId = 0;
		if (isset($arParams['AVATAR_ID']))
			$avatarId = intval($arParams['AVATAR_ID']);

		$authorId = $this->user_id;
		if (isset($arParams['AUTHOR_ID']))
			$authorId = intval($arParams['AUTHOR_ID']);

		if (isset($arParams['OWNER_ID']))
			$authorId = intval($arParams['OWNER_ID']);

		$parentId = 0;
		if (isset($arParams['PARENT_ID']))
			$parentId = intval($arParams['PARENT_ID']);

		$parentMid = 0;
		if (isset($arParams['PARENT_MID']))
			$parentMid = intval($arParams['PARENT_MID']);

		$pinMessageId = 0;
		if (isset($arParams['PIN_MESSAGE_ID']))
			$pinMessageId = intval($arParams['PIN_MESSAGE_ID']);

		$managers = array();
		if (isset($arParams['MANAGERS']))
			$managers = $arParams['MANAGERS'];

		$entityType = '';
		if (isset($arParams['ENTITY_TYPE']))
			$entityType = $arParams['ENTITY_TYPE'];

		$entityId = '';
		if (isset($arParams['ENTITY_ID']))
			$entityId = $arParams['ENTITY_ID'];

		$message = '';
		if (isset($arParams['MESSAGE']))
			$message = trim($arParams['MESSAGE']);

		$color = '';
		if (isset($arParams['COLOR']) && IM\Color::isSafeColor($arParams['COLOR']))
			$color = $arParams['COLOR'];

		$skipAddMessage = isset($arParams['SKIP_ADD_MESSAGE']) && $arParams['SKIP_USER_CHECK'] == 'Y';

		$type = IM_MESSAGE_CHAT;

		if (isset($arParams['TYPE']) && in_array($arParams['TYPE'], Array(IM_MESSAGE_OPEN, IM_MESSAGE_CHAT, IM_MESSAGE_PRIVATE, IM_MESSAGE_OPEN_LINE)))
		{
			if (!CIMMessenger::CheckEnableOpenChat() && $arParams['TYPE'] == IM_MESSAGE_OPEN)
			{
				$type = IM_MESSAGE_CHAT;
			}
			else if ($this->user_id == 0 || !IM\User::getInstance($this->user_id)->isExtranet())
			{
				$type = $arParams['TYPE'];
			}
		}

		$skipUserAdd = false;
		if ($userId === false)
		{
			$skipUserAdd = true;
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

		if (!$skipUserAdd)
		{
			if (count($arUserId) < 1)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MIN_USER"), "MIN_USER");
				return false;
			}

			if (false && count($arUserId) > 500)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MAX_USER", Array('#COUNT#' => 500)), "MAX_USER");
				return false;
			}

			if (!IsModuleInstalled('intranet') && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed())
			{
				global $USER;

				$arFriendUsers = Array();
				$dbFriends = CSocNetUserRelations::GetList(array(),array("USER_ID" => $USER->GetID(), "RELATION" => SONET_RELATIONS_FRIEND), false, false, array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY"));
				while ($arFriends = $dbFriends->Fetch())
				{
					$friendId = $USER->GetID() == $arFriends["FIRST_USER_ID"]? $arFriends["SECOND_USER_ID"]: $arFriends["FIRST_USER_ID"];
					$arFriendUsers[$friendId] = $friendId;
				}
				foreach ($arUserId as $id => $userId)
				{
					if ($userId == $USER->GetID())
						continue;

					if (!isset($arFriendUsers[$userId]) && CIMSettings::GetPrivacy(CIMSettings::PRIVACY_CHAT, $userId) == CIMSettings::PRIVACY_RESULT_CONTACT)
						unset($arUserId[$id]);
				}

				if (count($arUserId) == 2)
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_MIN_USER_BY_PRIVACY"), "MIN_USER_BY_PRIVACY");
					return false;
				}
			}
		}

		$arUsers = CIMContactList::GetUserData(array(
			'ID' => array_values($arUserId),
			'DEPARTMENT' => 'N',
			'USE_CACHE' => 'N'
		));
		$arUsers = is_array($arUsers['users'])? $arUsers['users']: Array();

		$arUsersName = Array();

		if (strlen($chatDescription) <= 0 && $type == IM_MESSAGE_OPEN)
		{
			$chatDescription = $message;
		}

		$chatColorCode = "";
		if (IM\Color::isEnabled())
		{
			if ($color)
			{
				$chatColorCode = $color;
			}
			else
			{
				CGlobalCounter::Increment('im_chat_color_id', CGlobalCounter::ALL_SITES, false);
				$chatColorId = CGlobalCounter::GetValue('im_chat_color_id', CGlobalCounter::ALL_SITES);
				$chatColorCode = \Bitrix\Im\Color::getCodeByNumber($chatColorId);
				CGlobalCounter::Increment('im_chat_color_'.$chatColorCode, CGlobalCounter::ALL_SITES, false);
			}

			$chatColorCodeCount = CGlobalCounter::GetValue('im_chat_color_'.$chatColorCode, CGlobalCounter::ALL_SITES);
			if ($chatColorCodeCount == 99)
			{
				CGlobalCounter::Set('im_chat_color_'.$chatColorCode, 1, CGlobalCounter::ALL_SITES, '', false);
			}
		}

		if ($chatTitle == "")
		{
			if (IM\Color::isEnabled())
			{
				$chatTitle = GetMessage('IM_CHAT_NAME_FORMAT', Array(
					'#COLOR#' => \Bitrix\Im\Color::getName($chatColorCode),
					'#NUMBER#' => $chatColorCodeCount+1,
				));
			}
			else
			{
				foreach ($arUserId as $userId)
				{
					$arUsersName[$userId] = htmlspecialcharsback($arUsers[$userId]['name']);
				}

				$chatTitle = implode(', ', $arUsersName);
			}
		}

		$isExtranet = false;
		if (!in_array($entityType, Array('LINES', 'LIVECHAT')))
		{
			foreach ($arUsers as $userData)
			{
				if ($userData['extranet'])
				{
					$isExtranet = true;
					break;
				}
			}
		}

		$result = IM\Model\ChatTable::add(Array(
			"PARENT_ID"	=> $parentId,
			"PARENT_MID" => $parentMid,
			"PIN_MESSAGE_ID" => $pinMessageId,
			"TITLE"	=> substr($chatTitle, 0, 255),
			"DESCRIPTION" => $chatDescription,
			"TYPE"	=> $type,
			"COLOR"	=> $chatColorCode,
			"AVATAR"	=> $avatarId,
			"AUTHOR_ID"	=> $authorId,
			"ENTITY_TYPE" => $entityType,
			"ENTITY_ID" => $entityId,
			"EXTRANET" => $isExtranet? 'Y': 'N',
			"CALL_NUMBER" => $callNumber,
		));

		$chatId = $result->getId();
		if ($chatId > 0)
		{
			$params = $result->getData();

			if (intval($params['AVATAR']) > 0)
				$this->lastAvatarId = $params['AVATAR'];

			$arUsersName = Array();
			foreach ($arUserId as $userId)
			{
				if ($userId != $this->user_id)
					$arUsersName[$userId] = htmlspecialcharsback($arUsers[$userId]['name']);

				Im\Model\RelationTable::add(array(
					"CHAT_ID" => $chatId,
					"MESSAGE_TYPE" => $params['TYPE'],
					"USER_ID" => $userId,
					"STATUS" => IM_STATUS_READ,
					"MANAGER" => $authorId == $userId || $managers[$userId]? 'Y': 'N',
				));

				if ($params['TYPE'] != IM_MESSAGE_OPEN)
				{
					CIMContactList::CleanChatCache($userId);
				}
			}
			if ($params['TYPE'] == IM_MESSAGE_OPEN)
			{
				CIMContactList::CleanAllChatCache();
			}

			$generalChatId = self::GetGeneralChatId();
			if (false && $params['TYPE'] == IM_MESSAGE_OPEN && $generalChatId > 0) // disabled auto-posting in general chat about new open chan
			{
				$attach = new CIMMessageParamAttach(null, Bitrix\Im\Color::getColor($chatColorCode));
				$attach->AddChat(Array(
					"NAME" => $params['TITLE'],
					"CHAT_ID" => $chatId
				));
				$attach->AddMessage($params['DESCRIPTION']);

				if ($this->user_id > 0 && !$skipAddMessage)
				{
					$createText = GetMessage("IM_GENERAL_CREATE_BY_USER", Array('#USER_NAME#' => htmlspecialcharsback($arUsers[$this->user_id]['name'])));
				}
				else
				{
					$createText = GetMessage("IM_GENERAL_CREATE");
				}

				self::AddMessage(Array(
					"TO_CHAT_ID" => $generalChatId,
					"FROM_USER_ID" => $this->user_id,
					"MESSAGE" => $createText,
					"SYSTEM" => 'Y',
					"ATTACH" => $attach
				));
			}

			if ($message)
			{
				if ($params['TYPE'] == IM_MESSAGE_PRIVATE)
				{
					if ($params['ENTITY_TYPE'] == 'PERSONAL')
					{
						CUserOptions::SetOption('im', 'personalChat', $chatId, false, $this->user_id);
					}
					CIMMessage::Add(Array(
						"FROM_USER_ID" => $this->user_id,
						"TO_USER_ID" => $this->user_id,
						"MESSAGE" 	 => $message,
					));
				}
				else
				{
					self::AddMessage(Array(
						"TO_CHAT_ID" => $chatId,
						"FROM_USER_ID" => $this->user_id,
						"MESSAGE" 	 => $message,
					));
				}
			}
			else if ($params['TYPE'] == IM_MESSAGE_OPEN && !$skipUserAdd)
			{
				if ($this->user_id > 0)
				{
					$createText = GetMessage("IM_CHAT_CREATE_OPEN_".$arUsers[$this->user_id]['gender'], Array('#USER_NAME#' => htmlspecialcharsback($arUsers[$this->user_id]['name']), '#CHAT_TITLE#' => $params['TITLE']));
				}
				else
				{
					$createText = GetMessage("IM_CHAT_CREATE_OPEN", Array('#CHAT_TITLE#' => $params['TITLE']));
				}

				self::AddMessage(Array(
					"TO_CHAT_ID" => $chatId,
					"FROM_USER_ID" => $this->user_id,
					"MESSAGE" 	 => $createText,
					"SYSTEM"	 => 'Y',
				));
			}

			if (count($arUsersName) >= 1 && !$skipUserAdd && !$skipAddMessage)
			{
				self::AddMessage(Array(
					"TO_CHAT_ID" => $chatId,
					"FROM_USER_ID" => $this->user_id,
					"MESSAGE" 	 => GetMessage("IM_CHAT_JOIN_".$arUsers[$this->user_id]['gender'], Array('#USER_1_NAME#' => htmlspecialcharsback($arUsers[$this->user_id]['name']), '#USER_2_NAME#' => implode(', ', $arUsersName))),
					"SYSTEM"	 => 'Y',
				));
			}

			foreach ($arUserId as $userId)
			{
				if (IM\User::getInstance($userId)->isBot())
				{
					IM\Bot::changeChatMembers($chatId, $userId);
					IM\Bot::onJoinChat('chat'.$chatId, Array(
						'CHAT_TYPE' => $type,
						'MESSAGE_TYPE' => $type,
						'BOT_ID' => $userId,
						'USER_ID' => $this->user_id,
						"CHAT_AUTHOR_ID"	=> $authorId,
						"CHAT_ENTITY_TYPE" => $entityType,
						"CHAT_ENTITY_ID" => $entityId,
						"ACCESS_HISTORY" => true,
					));
				}
			}
		}
		else
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_IM_ERROR_EMPTY_USER_OR_CHAT"), "ERROR_OF_CREATE_CHAT");
			return false;
		}
		return $chatId;
	}

	public static function AddMessage($arFields)
	{
		$arFields['MESSAGE_TYPE'] = IM_MESSAGE_CHAT;

		return CIMMessenger::Add($arFields);
	}

	public static function AddGeneralMessage($arFields)
	{
		$arFields['MESSAGE_TYPE'] = IM_MESSAGE_OPEN;
		$arFields['TO_CHAT_ID'] = self::GetGeneralChatId();

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

		$relations = IM\Chat::getRelation($chatId);
		if (!isset($relations[$this->user_id]))
		{
			$chat = new CIMChat(0);
			$chat->AddUser($chatId, $this->user_id);
		}

		return true;
	}

	public function AddUser($chatId, $userId, $hideHistory = null, $skipMessage = false, $skipRecent = false)
	{
		global $DB;

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

		if ($this->user_id > 0 && !IsModuleInstalled('intranet') && CModule::IncludeModule('socialnetwork') && CSocNetUser::IsFriendsAllowed())
		{
			$arFriendUsers = Array();
			$dbFriends = CSocNetUserRelations::GetList(array(),array("USER_ID" => $this->user_id, "RELATION" => SONET_RELATIONS_FRIEND), false, false, array("ID", "FIRST_USER_ID", "SECOND_USER_ID", "DATE_CREATE", "DATE_UPDATE", "INITIATED_BY"));
			while ($arFriends = $dbFriends->Fetch())
			{
				$friendId = $this->user_id == $arFriends["FIRST_USER_ID"]? $arFriends["SECOND_USER_ID"]: $arFriends["FIRST_USER_ID"];
				$arFriendUsers[$friendId] = $friendId;
			}
			foreach ($arUserId as $id => $userId)
			{
				if ($userId == $this->user_id)
					continue;

				if (!isset($arFriendUsers[$userId]) && CIMSettings::GetPrivacy(CIMSettings::PRIVACY_CHAT, $userId) == CIMSettings::PRIVACY_RESULT_CONTACT)
					unset($arUserId[$id]);
			}

			if (count($arUserId) <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_USER_ID_BY_PRIVACY"), "EMPTY_USER_ID_BY_PRIVACY");
				return false;
			}
		}

		if ($this->user_id > 0)
		{
			$strSql = "
				SELECT
					C.ID CHAT_ID,
					C.PARENT_MID CHAT_PARENT_MID,
					C.TITLE CHAT_TITLE,
					C.AUTHOR_ID CHAT_AUTHOR_ID,
					C.EXTRANET CHAT_EXTRANET,
					C.TYPE CHAT_TYPE,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
					C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
					C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
					".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." CHAT_DATE_CREATE
				FROM b_im_chat C
				WHERE C.TYPE = '".IM_MESSAGE_OPEN."' AND C.ID = ".$chatId."
			";
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arRes = $dbRes->Fetch();
			if (!$arRes)
			{
				$strSql = "
					SELECT
						R.CHAT_ID,
						C.PARENT_MID CHAT_PARENT_MID,
						C.TITLE CHAT_TITLE,
						C.AUTHOR_ID CHAT_AUTHOR_ID,
						C.EXTRANET CHAT_EXTRANET,
						C.TYPE CHAT_TYPE,
						C.ENTITY_TYPE CHAT_ENTITY_TYPE,
						C.ENTITY_ID CHAT_ENTITY_ID,
						C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
						C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
						C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
						".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." CHAT_DATE_CREATE
					FROM b_im_relation R
					LEFT JOIN b_im_chat C ON R.CHAT_ID = C.ID
					WHERE
						".($this->user_id > 0? "R.USER_ID = ".$this->user_id." AND ": "")."
						R.MESSAGE_TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."')
						AND R.CHAT_ID = ".$chatId."
				";
				$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$arRes = $dbRes->Fetch();
			}
		}
		else
		{
			$strSql = "
				SELECT
					C.ID CHAT_ID,
					C.PARENT_MID CHAT_PARENT_MID,
					C.TITLE CHAT_TITLE,
					C.AUTHOR_ID CHAT_AUTHOR_ID,
					C.EXTRANET CHAT_EXTRANET,
					C.TYPE CHAT_TYPE,
					C.ENTITY_TYPE CHAT_ENTITY_TYPE,
					C.ENTITY_ID CHAT_ENTITY_ID,
					C.ENTITY_DATA_1 CHAT_ENTITY_DATA_1,
					C.ENTITY_DATA_2 CHAT_ENTITY_DATA_2,
					C.ENTITY_DATA_3 CHAT_ENTITY_DATA_3,
					".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." CHAT_DATE_CREATE
				FROM b_im_chat C
				WHERE C.TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."') AND C.ID = ".$chatId."
			";
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$arRes = $dbRes->Fetch();
		}
		if (!$arRes)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_AUTHORIZE_ERROR"), "AUTHORIZE_ERROR");
			return false;
		}

		$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
		$chatEntityType = $arRes['CHAT_ENTITY_TYPE'] ;
		$chatParentMessageId = $arRes['CHAT_PARENT_MID'] ;

		if ($chatEntityType == 'LINES')
		{
			foreach ($arUserId as $id => $userId)
			{
				if (
					!\Bitrix\Im\User::getInstance($userId)->isConnector() &&
					(\Bitrix\Im\User::getInstance($userId)->isExtranet() || \Bitrix\Im\User::getInstance($userId)->isNetwork())
				)
				{
					unset($arUserId[$id]);
				}
			}
		}

		$extranetFlag = false;
		if (!in_array($chatEntityType, Array('LINES', 'LIVECHAT')))
		{
			$extranetFlag = $arRes["CHAT_EXTRANET"] == ""? "": ($arRes["CHAT_EXTRANET"] == "Y"? true: false);
		}
		$chatTitle = $arRes['CHAT_TITLE'];
		$chatAuthorId = intval($arRes['CHAT_AUTHOR_ID']);
		$chatType = $arRes['CHAT_TYPE'];

		$arRelation = self::GetRelationById($chatId);
		$arExistUser = Array();
		foreach ($arRelation as $relation)
			$arExistUser[] = $relation['USER_ID'];

		$arUserId = array_diff($arUserId, $arExistUser);
		if (empty($arUserId))
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_NOTHING_TO_ADD"), "NOTHING_TO_ADD");
			return false;
		}

		$arUserSelect = $arUserId;
		if ($this->user_id > 0)
		{
			$arUserSelect[] = $this->user_id;
		}

		$arUsers = CIMContactList::GetUserData(array(
			'ID' => array_values($arUserSelect),
			'DEPARTMENT' => 'N',
			'USE_CACHE' => 'N'
		));
		$arUsers = $arUsers['users'];

		if ($extranetFlag !== true)
		{
			$isExtranet = false;
			foreach ($arUsers as $userData)
			{
				if ($userData['extranet'])
				{
					$isExtranet = true;
					break;
				}
			}
			if ($isExtranet || $extranetFlag === "")
			{
				IM\Model\ChatTable::update($chatId, Array('EXTRANET' => $isExtranet? "Y":"N"));
			}
			$extranetFlag = $isExtranet;
		}

		$arUsersName = Array();
		foreach ($arUserId as $userId)
		{
			$arUsersName[] = '[USER='.$userId.']'.htmlspecialcharsback($arUsers[$userId]['name']).'[/USER]';
		}

		$message = '';
		if ($this->user_id > 0)
		{
			$message = GetMessage("IM_CHAT_JOIN_".$arUsers[$this->user_id]['gender'], Array('#USER_1_NAME#' => htmlspecialcharsback($arUsers[$this->user_id]['name']), '#USER_2_NAME#' => implode(', ', $arUsersName)));
		}
		else
		{
			if ($skipMessage)
			{
				$message = '';
			}
			else if ($chatId == self::GetGeneralChatId())
			{
				if (self::GetGeneralChatAutoMessageStatus(self::GENERAL_MESSAGE_TYPE_JOIN))
				{
					if (count($arUsersName) > 1)
					{
						$message = GetMessage("IM_CHAT_GENERAL_JOIN_PLURAL", Array('#USERS_NAME#' => implode(', ', $arUsersName)));
					}
					else
					{
						$arUserList = array_values($arUserId);
						$joinMessage = "IM_CHAT_GENERAL_JOIN";
						if ($arUsers[$arUserList[0]]['gender'] == 'F')
						{
							$joinMessage .= '_F';
						}
						$message = GetMessage($joinMessage, Array('#USER_NAME#' => implode(', ', $arUsersName)));
					}
				}
			}
			else
			{
				if (count($arUsersName) > 1)
				{
					$message = GetMessage("IM_CHAT_SELF_JOIN", Array('#USERS_NAME#' => implode(', ', $arUsersName)));
				}
				else
				{
					$arUserList = array_values($arUserId);
					$message = GetMessage("IM_CHAT_SELF_JOIN_".$arUsers[$arUserList[0]]['gender'], Array('#USER_NAME#' => implode(', ', $arUsersName)));
				}
			}
		}

		$fileMaxId = CIMDisk::GetMaxFileId($chatId);

		$replicaUpdate = true;
		$startId = 0;
		$maxId = 0;
		$strSql = "SELECT MAX(ID) ID FROM b_im_message WHERE CHAT_ID = ".$chatId." GROUP BY CHAT_ID";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arMax = $dbRes->Fetch())
		{
			$startId = $arMax['ID']+1;
			$maxId = $arMax['ID'];
		}

		$update = Array();

		$publicPullWatch = false;
		if (($chatType == IM_MESSAGE_OPEN || $chatType == IM_MESSAGE_OPEN_LINE) && CModule::IncludeModule("pull"))
		{
			$publicPullWatch = true;
		}

		if ($chatEntityType == 'LINES')
		{
			$hideHistory = false;
		}
		else if (is_null($hideHistory))
		{
			$hideHistory = CIMSettings::GetStartChatMessage() == CIMSettings::START_MESSAGE_LAST && ($arRes['CHAT_TYPE'] == IM_MESSAGE_CHAT && $arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN_LINE);
		}

		if ($this->user_id > 0 && !$hideHistory && $arRelation[$this->user_id]['START_ID'] > 0)
		{
			$hideHistory = true;
			$startId = $arRelation[$this->user_id]['START_ID'];
			$replicaUpdate = false;
		}

		foreach ($arUserId as $userId)
		{
			if ($publicPullWatch)
			{
				CPullWatch::Delete($userId, 'IM_PUBLIC_'.$chatId);
			}

			$hideHistoryFlag = $hideHistory;
			if ($chatEntityType != 'LINES' && $arRes['CHAT_TYPE'] != IM_MESSAGE_PRIVATE && \Bitrix\Im\User::getInstance($userId)->isExtranet())
			{
				$hideHistoryFlag = true;
			}
			$orm = IM\Model\RelationTable::add(array(
				"CHAT_ID" => $chatId,
				"MESSAGE_TYPE" => $arRes['CHAT_TYPE'],
				"USER_ID" => $userId,
				"START_ID" => $hideHistoryFlag? $startId: 0,
				"LAST_ID" => $maxId,
				"LAST_SEND_ID" => $maxId,
				"LAST_FILE_ID" => $hideHistoryFlag? $fileMaxId: 0,
			));
			$relationId = $orm->getId();

			if ($hideHistoryFlag)
			{
				$update[] = $relationId;
			}

			if ($arRes['CHAT_TYPE'] != IM_MESSAGE_OPEN)
			{
				CIMContactList::CleanChatCache($userId);
			}
		}
		if ($arRes['CHAT_TYPE'] == IM_MESSAGE_OPEN)
		{
			CIMContactList::CleanAllChatCache();
		}

		if ($chatParentMessageId)
		{
			$relations = IM\Chat::getRelation($chatId);
			CIMMessageParam::set($chatParentMessageId, Array('CHAT_USER' => array_keys($relations)));
			CIMMessageParam::SendPull($chatParentMessageId, Array('CHAT_USER'));
		}

		if (CModule::IncludeModule("pull"))
		{
			$pushMessage = Array(
				'module_id' => 'im',
				'command' => 'chatUserAdd',
				'params' => Array(
					'chatId' => $chatId,
					'chatTitle' => $chatTitle,
					'chatOwner' => $chatAuthorId,
					'chatExtranet' => $extranetFlag == 'Y',
					'users' => $arUsers,
					'newUsers' => $arUserId
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			);
			if ($chatEntityType == 'LINES')
			{
				foreach ($arRelation as $rel)
				{
					if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
					{
						unset($arRelation[$rel["USER_ID"]]);
					}
				}
			}
			\Bitrix\Pull\Event::add(array_merge(array_keys($arRelation), array_keys($arUsers)), $pushMessage);
			if ($chatType == IM_MESSAGE_OPEN  || $chatType == IM_MESSAGE_OPEN_LINE)
			{
				CPullWatch::AddToStack('IM_PUBLIC_'.$chatId, $pushMessage);
			}
		}

		if ($message)
		{
			$lastId = self::AddMessage(Array(
				"TO_CHAT_ID" => $chatId,
				"MESSAGE" => $message,
				"FROM_USER_ID" => $this->user_id,
				"SYSTEM" => 'Y',
				"RECENT_ADD" => $skipRecent? 'N': 'Y',
				"PARAMS" => Array(
					"CODE" => 'CHAT_JOIN'
				),
				"INCREMENT_COUNTER" => $arUserId,
				"PUSH" => 'N'
			));
		}
		else
		{
			$lastId = 0;
		}

		CIMDisk::ChangeFolderMembers($chatId, $arUserId);

		foreach ($arUserId as $userId)
		{
			if (IM\User::getInstance($userId)->isBot())
			{
				IM\Bot::changeChatMembers($chatId, $userId);
				IM\Bot::onJoinChat('chat'.$chatId, Array(
					'CHAT_TYPE' => $chatType,
					'MESSAGE_TYPE' => $chatType,
					'BOT_ID' => $userId,
					'USER_ID' => $this->user_id,
					'CHAT_ID' => $chatId,
					"CHAT_AUTHOR_ID" => $arRes['CHAT_AUTHOR_ID'],
					"CHAT_ENTITY_TYPE" => $arRes['CHAT_ENTITY_TYPE'],
					"CHAT_ENTITY_ID" => $arRes['CHAT_ENTITY_ID'],
					"ACCESS_HISTORY" => $hideHistoryFlag? false: true,
				));
			}
		}

		if (IsModuleInstalled('replica') && $replicaUpdate && $startId != 0)
		{
			foreach ($update as $relId)
			{
				IM\Model\RelationTable::update($relId, Array('START_ID' => $lastId));
			}
		}

		return true;
	}

	public function MuteNotify($chatId, $mute = true)
	{
		return \Bitrix\Im\Chat::mute($chatId, $mute, $this->user_id);
	}

	public function DeleteUser($chatId, $userId, $checkPermission = true, $skipMessage = false, $skipRecent = false)
	{
		global $DB;
		$chatId = intval($chatId);
		$userId = intval($userId);
		if ($chatId <= 0 || $userId <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_EMPTY_USER_OR_CHAT"), "EMPTY_USER_OR_CHAT");
			return false;
		}

		$pullLoad = CModule::IncludeModule('pull');

		$strSql = "
			SELECT
				R.CHAT_ID,
				C.PARENT_MID CHAT_PARENT_MID,
				C.TITLE CHAT_TITLE,
				C.AUTHOR_ID CHAT_AUTHOR_ID,
				C.EXTRANET CHAT_EXTRANET,
				C.ENTITY_TYPE CHAT_ENTITY_TYPE,
				C.ENTITY_ID CHAT_ENTITY_ID,
				C.TYPE CHAT_TYPE
			FROM b_im_relation R LEFT JOIN b_im_chat C ON R.CHAT_ID = C.ID
			WHERE R.USER_ID = ".$userId." AND R.MESSAGE_TYPE IN ('".IM_MESSAGE_OPEN."','".IM_MESSAGE_CHAT."','".IM_MESSAGE_OPEN_LINE."') AND R.CHAT_ID = ".$chatId;
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arRes = $dbRes->Fetch();
		if (!$arRes)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_USER_NOT_FOUND"), "USER_NOT_FOUND");
			return false;
		}

		$chatParentMessageId = $arRes['CHAT_PARENT_MID'];

		$arRes['CHAT_TYPE'] = trim($arRes['CHAT_TYPE']);
		$chatEntityType = trim($arRes['CHAT_ENTITY_TYPE']);
		$chatEntityId = trim($arRes['CHAT_ENTITY_ID']);

		$extranetFlag = false;
		if (!in_array($arRes['CHAT_ENTITY_TYPE'], Array('LINES', 'LIVECHAT')))
		{
			$extranetFlag = $arRes["CHAT_EXTRANET"] == ""? "": ($arRes["CHAT_EXTRANET"] == "Y"? true: false);
		}
		$chatTitle = $arRes['CHAT_TITLE'];
		$chatType = $arRes['CHAT_TYPE'];
		$chatAuthorId = intval($arRes['CHAT_AUTHOR_ID']);
		if ($chatAuthorId == $userId)
		{
			$strSql = "
				SELECT R.USER_ID
				FROM b_im_relation R
				WHERE R.CHAT_ID = ".$chatId." AND R.USER_ID <> ".$chatAuthorId;
			$strSql = $DB->TopSql($strSql, 1);
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($res = $dbRes->Fetch())
			{
				self::SetOwner($chatId, $res['USER_ID']);
			}
		}

		$bSelf = true;
		$arUsers = Array($userId);
		if($this->user_id != $userId)
		{
			if ($checkPermission && $chatAuthorId != $this->user_id)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("IM_ERROR_KICK"), "IM_ERROR_KICK");
				return false;
			}

			$bSelf = false;
			$arUsers[] = $this->user_id;
		}

		$arOldRelation = CIMChat::GetRelationById($chatId);

		$arUsers = CIMContactList::GetUserData(array(
			'ID' => array_keys($arOldRelation),
			'DEPARTMENT' => 'N',
			'USE_CACHE' => 'N'
		));
		$arUsers = $arUsers['users'];

		$message = '';
		if ($skipMessage)
		{
			$message = '';
		}
		else if ($bSelf)
		{
			if ($chatId == self::GetGeneralChatId())
			{
				if (self::GetGeneralChatAutoMessageStatus(self::GENERAL_MESSAGE_TYPE_LEAVE))
				{
					$message = GetMessage("IM_CHAT_GENERAL_LEAVE", Array('#USER_NAME#' => htmlspecialcharsback($arUsers[$userId]['name'])));
				}
			}
			else
			{
				$message = GetMessage("IM_CHAT_LEAVE_".$arUsers[$userId]['gender'], Array('#USER_NAME#' => htmlspecialcharsback($arUsers[$userId]['name'])));
			}
		}
		else
		{
			$message = GetMessage("IM_CHAT_KICK_".$arUsers[$chatAuthorId]['gender'], Array('#USER_1_NAME#' => htmlspecialcharsback($arUsers[$chatAuthorId]['name']), '#USER_2_NAME#' => htmlspecialcharsback($arUsers[$userId]['name'])));
		}

		CIMContactList::DeleteRecent($chatId, true, $userId);

		\Bitrix\Im\LastSearch::delete('chat'.$chatId, $userId);

		$relationList = IM\Model\RelationTable::getList(array(
			"select" => array("ID", "USER_ID"),
			"filter" => array(
				"=CHAT_ID" => $chatId,
				"=USER_ID" => $userId,
			),
		));

		while ($relation = $relationList->fetch())
		{
			IM\Model\RelationTable::delete($relation["ID"]);

			if ($extranetFlag !== false)
			{
				$isExtranet = false;
				foreach ($arUsers as $userData)
				{
					if ($userData['id'] == $userId)
						continue;

					if ($userData['extranet'])
					{
						$isExtranet = true;
						break;
					}
				}
				if (!$isExtranet || $extranetFlag === "")
				{
					IM\Model\ChatTable::update($chatId, Array('EXTRANET' => $isExtranet? "Y":"N"));
				}
				$extranetFlag = $isExtranet;
			}
		}

		if (IM\User::getInstance($userId)->isBot())
		{
			IM\Bot::changeChatMembers($chatId, $userId, false);
			IM\Bot::onLeaveChat('chat'.$chatId, Array(
				'CHAT_TYPE' => $chatType,
				'MESSAGE_TYPE' => $chatType,
				'BOT_ID' => $userId,
				'USER_ID' => $this->user_id,
				"CHAT_AUTHOR_ID"	=> $chatAuthorId,
				"CHAT_ENTITY_TYPE" => $chatEntityType,
				"CHAT_ENTITY_ID" => $chatEntityId,
			));
		}

		CIMDisk::ChangeFolderMembers($chatId, $userId, false);

		if ($message)
		{
			self::AddMessage(Array(
				"TO_CHAT_ID" => $chatId,
				"MESSAGE" 	 => $message,
				"FROM_USER_ID" => $this->user_id,
				"SYSTEM"	 => 'Y',
				"RECENT_ADD" => $skipRecent? 'N': 'Y',
				"PARAMS" => Array(
					"CODE" => 'CHAT_LEAVE'
				),
				"INCREMENT_COUNTER" => 'N',
				"PUSH" => 'N'
			));
		}

		if ($chatParentMessageId)
		{
			$relations = IM\Chat::getRelation($chatId);
			CIMMessageParam::set($chatParentMessageId, Array('CHAT_USER' => array_keys($relations)));
			CIMMessageParam::SendPull($chatParentMessageId, Array('CHAT_USER'));
		}

		if ($chatType == IM_MESSAGE_OPEN)
		{
			CIMContactList::CleanAllChatCache();
		}
		else
		{
			CIMContactList::CleanChatCache($userId);
		}

		$pushMessage = Array(
			'module_id' => 'im',
			'command' => 'chatUserLeave',
			'params' => Array(
				'chatId' => $chatId,
				'chatTitle' => $chatTitle,
				'userId' => $userId,
				'message' => $bSelf? '': htmlspecialcharsbx($message),
			),
			'extra' => Array(
				'im_revision' => IM_REVISION,
				'im_revision_mobile' => IM_REVISION_MOBILE,
			),
		);
		if ($arRes['CHAT_ENTITY_TYPE'] == 'LINES' )
		{
			foreach ($arOldRelation as $rel)
			{
				if ($rel["EXTERNAL_AUTH_ID"] == 'imconnector')
				{
					unset($arOldRelation[$rel["USER_ID"]]);
				}
			}
		}

		if ($pullLoad)
		{
			\Bitrix\Pull\Event::add(array_keys($arOldRelation), $pushMessage);
		}

		return true;

	}

	public static function GetAvatarImage($id, $size = 100, $addBlankPicture = true)
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
			$dbUsers = CUser::GetList(($sort_by = false), ($dummy=''), array('ID' => $userId), array('FIELDS' => $arSelect));
			if ($arUser = $dbUsers->Fetch())
			{
				$arUser['NAME'] = CUser::FormatName(CSite::GetNameFormat(false), $arUser, true, false);
				$arUser['PERSONAL_GENDER'] = $arUser["PERSONAL_GENDER"] == 'F'? 'F': 'M';
			}
		}

		if (isset($params['MESSAGE_CODE']))
		{
			$messageReplace = is_array($params['MESSAGE_REPLACE'])? $params['MESSAGE_REPLACE']: Array();
			if ($arUser)
			{
				$messageReplace['#USER_NAME#'] = $arUser['NAME'];
				$message = GetMessage($params['MESSAGE_CODE'].$arUser['PERSONAL_GENDER'], $messageReplace);
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
			if (strlen($message) > 0 && !empty($messageReplace))
			{
				$message = str_replace(array_keys($messageReplace), array_values($messageReplace), $message);
			}
		}
		if (strlen($message) <= 0)
			return false;

		return self::AddMessage(Array(
			"TO_CHAT_ID" => $chatId,
			"FROM_USER_ID" => $userId,
			"MESSAGE" => $message,
			"SYSTEM" => 'Y',
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

		self::GetChatOptions();

		if (isset(self::$entityOption[$chat['ENTITY_TYPE']][$action]) && !self::$entityOption[$chat['ENTITY_TYPE']][$action])
		{
			return true;
		}

		return false;
	}

	public static function GetChatOptions()
	{
		if (!is_null(self::$entityOption))
			return self::$entityOption;

		global $USER;

		self::$entityOption = Array(
			'GENERAL' => Array('AVATAR' => false, 'RENAME' => false, 'EXTEND' => false, 'LEAVE' => false)
		);

		if (IsModuleInstalled('socialnetwork'))
		{
			$path = COption::GetOptionString("socialnetwork", "workgroups_page", "/workgroups/", SITE_ID);
			$path = $path.'group/#ID#/';

			self::$entityOption['SONET_GROUP'] = Array('AVATAR' => false, 'RENAME' => false, 'EXTEND' => false, 'LEAVE' => false, 'PATH' => $path, 'PATH_TITLE' => GetMessage('IM_PATH_TITLE_SONET'));
		}

		if (CModule::IncludeModule('tasks'))
		{
			$path = CTasksTools::GetOptionPathTaskUserEntry(SITE_ID, "/company/personal/user/#user_id#/tasks/task/view/#task_id#/");
			$path = str_replace(Array('#user_id#', '#task_id#'), Array($USER->GetId(), '#ID#'), $path);

			self::$entityOption['TASKS'] = Array('AVATAR' => false, 'RENAME' => false, 'EXTEND' => false, 'LEAVE' => false, 'PATH' => $path, 'PATH_TITLE' => GetMessage('IM_PATH_TITLE_TASKS'));
		}

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

	public static function hide($chatId)
	{
		$relations = \CIMChat::GetRelationById($chatId);
		foreach($relations as $userId => $relation)
		{
			\CIMContactList::DeleteRecent($chatId, true, $userId);
		}

		if (\Bitrix\Main\Loader::includeModule("pull"))
		{
			\Bitrix\Pull\Event::add(array_keys($relations), Array(
				'module_id' => 'im',
				'command' => 'chatHide',
				'expiry' => 3600,
				'params' => Array(
					'dialogId' => 'chat'.$chatId,
				),
				'extra' => Array(
					'im_revision' => IM_REVISION,
					'im_revision_mobile' => IM_REVISION_MOBILE,
				),
			));
		}

		return true;
	}
}
