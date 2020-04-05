<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;
use Bitrix\Main\Loader;

class CIMNotify
{
	private $user_id = 0;
	private $bHideLink = false;

	function __construct($user_id = false, $arParams = Array())
	{
		global $USER;
		$this->user_id = intval($user_id);
		if ($this->user_id == 0)
			$this->user_id = IntVal($USER->GetID());
		if (isset($arParams['HIDE_LINK']) && $arParams['HIDE_LINK'] == 'Y')
			$this->bHideLink = true;
	}

	public static function Add($arFields)
	{
		$arFields['MESSAGE_TYPE'] = IM_MESSAGE_SYSTEM;

		return CIMMessenger::Add($arFields);
	}

	public function GetNotifyList($arParams = array())
	{
		global $DB;

		$iNumPage = 1;
		if (isset($arParams['PAGE']) && intval($arParams['PAGE']) >= 0)
			$iNumPage = intval($arParams['PAGE']);

		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;

		$sqlStr = "
			SELECT COUNT(M.ID) as CNT, M.CHAT_ID
			FROM b_im_relation R
			INNER JOIN b_im_message M ON M.CHAT_ID = R.CHAT_ID
			WHERE R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."'
			GROUP BY M.CHAT_ID
			ORDER BY M.CHAT_ID
		";
		$res_cnt = $DB->Query($sqlStr);
		$res_cnt = $res_cnt->Fetch();
		$cnt = $res_cnt["CNT"];
		$chatId = $res_cnt["CHAT_ID"];

		$arNotify = Array();
		if ($cnt > 0)
		{
			if (!$bTimeZone)
				CTimeZone::Disable();

			$strSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					M.MESSAGE_OUT,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.NOTIFY_TYPE,
					M.NOTIFY_MODULE,
					M.NOTIFY_EVENT,
					M.NOTIFY_TITLE,
					M.NOTIFY_BUTTONS,
					M.NOTIFY_TAG,
					M.NOTIFY_SUB_TAG,
					M.NOTIFY_READ,
					$this->user_id TO_USER_ID,
					M.AUTHOR_ID FROM_USER_ID
				FROM b_im_message M
				WHERE M.CHAT_ID = ".$chatId." #LIMIT#
				ORDER BY M.DATE_CREATE DESC, M.ID DESC
			";
			if (!$bTimeZone)
				CTimeZone::Enable();

			if ($iNumPage == 0)
			{
				$dbType = strtolower($DB->type);
				$sqlLimit = '';
				if ($dbType== "mysql")
					$sqlLimit = " AND M.DATE_CREATE > DATE_SUB(NOW(), INTERVAL 30 DAY)";
				else if ($dbType == "mssql")
					$sqlLimit = " AND M.DATE_CREATE > dateadd(day, -30, getdate())";
				else if ($dbType == "oracle")
					$sqlLimit = " AND M.DATE_CREATE > SYSDATE-30";

				$strSql = $DB->TopSql($strSql, 20);
				$dbRes = $DB->Query(str_replace("#LIMIT#", $sqlLimit, $strSql), false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			else
			{
				$dbRes = new CDBResult();
				$dbRes->NavQuery(str_replace("#LIMIT#", "", $strSql), $cnt, Array('iNumPage' => $iNumPage, 'nPageSize' => 20));
			}

			$arGetUsers = Array();
			$arNotifyId = Array();
			$arChatId = Array();
			while ($arRes = $dbRes->Fetch())
			{
				if ($this->bHideLink)
					$arRes['HIDE_LINK'] = 'Y';

				$arNotify[$arRes['ID']] = $arRes;
				$arNotifyId[$arRes['ID']] = $arRes['ID'];
				$arGetUsers[] = $arRes['FROM_USER_ID'];
				$arChatId[$arRes['CHAT_ID']] = $arRes['CHAT_ID'];
			}
			if (empty($arNotify))
				return $arNotify;

			$counters = self::GetCounters($arChatId);

			$params = CIMMessageParam::Get(array_keys($arNotifyId));
			foreach ($params as $notifyId => $param)
			{
				$arNotify[$notifyId]['PARAMS'] = $param;
			}

			$arUsers = CIMContactList::GetUserData(Array('ID' => $arGetUsers, 'DEPARTMENT' => 'N', 'USE_CACHE' => 'Y', 'CACHE_TTL' => 86400));
			$arGetUsers = $arUsers['users'];

			foreach ($arNotify as $key => $value)
			{
				$value['COUNTER'] = $counters[$value['CHAT_ID']];
				$value['FROM_USER_DATA'] = $arGetUsers;
				$arNotify[$key] = self::GetFormatNotify($value);
			}
		}
		return $arNotify;
	}

	public function GetUnreadNotify($arParams = Array())
	{
		global $DB;

		$order = isset($arParams['ORDER']) && $arParams['ORDER'] == 'ASC'? 'ASC': 'DESC';
		$bSpeedCheck = isset($arParams['SPEED_CHECK']) && $arParams['SPEED_CHECK'] == 'N'? false: true;
		$bTimeZone = isset($arParams['USE_TIME_ZONE']) && $arParams['USE_TIME_ZONE'] == 'N'? false: true;
		$bGetOnlyFlash = isset($arParams['GET_ONLY_FLASH']) && $arParams['GET_ONLY_FLASH'] == 'Y'? true: false;

		$arNotify['result'] = false;
		$arNotify['notify'] = Array();
		$arNotify['unreadNotify'] = Array();
		$arNotify['loadNotify'] = false;
		$arNotify['countNotify'] = 0;
		$arNotify['maxNotify'] = 0;

		$bLoadNotify = $bSpeedCheck? !CIMMessenger::SpeedFileExists($this->user_id, IM_SPEED_NOTIFY): true;
		if ($bLoadNotify)
		{
			$strSql =
				"SELECT
					CHAT_ID,
					STATUS
				FROM
					b_im_relation
				WHERE
					USER_ID = ".$this->user_id."
					AND MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."'
				ORDER BY ID ASC";
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				$chatId = intval($arRes['CHAT_ID']);
				$chatStatus = $arRes['STATUS'];
			}
			else
				return $arNotify;

			if (!$bTimeZone)
				CTimeZone::Disable();
			$strSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					M.MESSAGE_OUT,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
					M.NOTIFY_TYPE,
					M.NOTIFY_MODULE,
					M.NOTIFY_EVENT,
					M.NOTIFY_TITLE,
					M.NOTIFY_BUTTONS,
					M.NOTIFY_TAG,
					M.NOTIFY_SUB_TAG,
					M.NOTIFY_READ,
					$this->user_id TO_USER_ID,
					M.AUTHOR_ID FROM_USER_ID
				FROM b_im_message M
				WHERE M.CHAT_ID = ".$chatId." AND M.NOTIFY_READ = 'N'
			";
			if (!$bTimeZone)
				CTimeZone::Enable();
			$strSql = $DB->TopSql($strSql, 500);
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$arMark = Array();
			$arGetUsers = Array();
			$arNotifyId = Array();
			$arChatId = Array();
			while ($arRes = $dbRes->Fetch())
			{
				if ($this->bHideLink)
					$arRes['HIDE_LINK'] = 'Y';

				$arNotifyId[$arRes['ID']] = $arRes['ID'];
				$arNotify['original_notify'][$arRes['ID']] = $arRes;
				$arNotify['notify'][$arRes['ID']] = $arRes;
				$arNotify['unreadNotify'][$arRes['ID']] = $arRes['ID'];

				if ($chatStatus == IM_STATUS_UNREAD && (!isset($arMark[$arRes["CHAT_ID"]]) || $arMark[$arRes["CHAT_ID"]] < $arRes["ID"]))
					$arMark[$arRes["CHAT_ID"]] = $arRes["ID"];

				if ($arNotify['maxNotify'] < $arRes['ID'])
					$arNotify['maxNotify'] = $arRes['ID'];

				$arGetUsers[] = $arRes['FROM_USER_ID'];
				$arChatId[$arRes["CHAT_ID"]] = $arRes["CHAT_ID"];
			}

			$params = CIMMessageParam::Get(array_keys($arNotifyId));
			foreach ($params as $notifyId => $param)
			{
				$arNotify['notify'][$notifyId]['PARAMS'] = $param;
			}

			foreach ($arMark as $chatId => $lastSendId)
				CIMNotify::SetLastSendId($chatId, $lastSendId);

			$arNotify['countNotify'] = $this->GetNotifyCounter($arNotify);
			CIMMessenger::SpeedFileCreate($this->user_id, array('counter' => $arNotify['countNotify'], 'maxId' => $arNotify['maxNotify']), IM_SPEED_NOTIFY);

			$arUsers = CIMContactList::GetUserData(Array('ID' => $arGetUsers, 'DEPARTMENT' => 'N', 'USE_CACHE' => 'Y', 'CACHE_TTL' => 86400));
			$arGetUsers = $arUsers['users'];

			$counters = self::GetCounters($arChatId);

			if ($bGetOnlyFlash)
			{
				foreach ($arNotify['notify'] as $key => $value)
				{
					if (isset($_SESSION['IM_FLASHED_NOTIFY'][$key]))
					{
						unset($arNotify['notify'][$key]);
						unset($arNotify['original_notify'][$key]);
						$arNotify['loadNotify'] = true;
					}
					else
					{
						$value['FROM_USER_DATA'] = $arGetUsers;
						$value['COUNTER'] = $counters[$value['CHAT_ID']];
						$arNotify['notify'][$key] = self::GetFormatNotify($value);
					}
				}
			}
			else
			{
				foreach ($arNotify['notify'] as $key => $value)
				{
					$value['FROM_USER_DATA'] = $arGetUsers;
					$value['COUNTER'] = $counters[$value['CHAT_ID']];
					$arNotify['notify'][$key] = self::GetFormatNotify($value);
				}
			}

			$arNotify['result'] = true;
		}
		else
		{
			$cache = CIMMessenger::SpeedFileGet($this->user_id, IM_SPEED_NOTIFY);
			$arNotify['countNotify'] = $cache? $cache['counter']: 0;
			$arNotify['maxNotify'] = $cache? $cache['maxId']: 0;
			if ($arNotify['countNotify'] > 0)
				$arNotify['loadNotify'] = true;
		}

		return $arNotify;
	}

	public static function GetUnsendNotify()
	{
		global $DB;

		$strSqlRelation ="
			SELECT
				R.CHAT_ID,
				R.LAST_SEND_ID,
				R.USER_ID TO_USER_ID,
				U1.LOGIN TO_USER_LOGIN,
				U1.NAME TO_USER_NAME,
				U1.LAST_NAME TO_USER_LAST_NAME,
				U1.SECOND_NAME TO_USER_SECOND_NAME,
				U1.EMAIL TO_USER_EMAIL,
				U1.ACTIVE TO_USER_ACTIVE,
				U1.LID TO_USER_LID,
				U1.AUTO_TIME_ZONE AUTO_TIME_ZONE,
				U1.TIME_ZONE TIME_ZONE,
				U1.TIME_ZONE_OFFSET TIME_ZONE_OFFSET,
				U1.EXTERNAL_AUTH_ID TO_EXTERNAL_AUTH_ID
			FROM b_im_relation R
			LEFT JOIN b_user U1 ON U1.ID = R.USER_ID
			WHERE R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND R.STATUS < ".IM_STATUS_NOTIFY."
		";
		$dbResRelation = $DB->Query($strSqlRelation, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$arNotify = Array();

		CTimeZone::Disable();
		while ($arResRelation = $dbResRelation->Fetch())
		{
			$strSql ="
				SELECT
					M.ID,
					M.CHAT_ID,
					M.MESSAGE,
					M.MESSAGE_OUT,
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')."+".CIMMail::GetUserOffset($arResRelation)." DATE_CREATE,
					M.NOTIFY_TYPE,
					M.NOTIFY_MODULE,
					M.NOTIFY_EVENT,
					M.NOTIFY_TITLE,
					M.NOTIFY_BUTTONS,
					M.NOTIFY_TAG,
					M.NOTIFY_SUB_TAG,
					M.EMAIL_TEMPLATE,
					M.AUTHOR_ID FROM_USER_ID,
					U2.LOGIN FROM_USER_LOGIN,
					U2.NAME FROM_USER_NAME,
					U2.LAST_NAME FROM_USER_LAST_NAME,
					U2.SECOND_NAME FROM_USER_SECOND_NAME
				FROM b_im_message M
				LEFT JOIN b_user U2 ON U2.ID = M.AUTHOR_ID
				WHERE M.ID > ".intval($arResRelation['LAST_SEND_ID'])." AND M.CHAT_ID = ".intval($arResRelation['CHAT_ID'])."
				ORDER BY M.ID DESC
			";
			$strSql = $DB->TopSql($strSql, 200);
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			while ($arRes = $dbRes->Fetch())
			{
				$arRes = array_merge($arRes, $arResRelation);
				$arNotify[$arRes['ID']] = $arRes;
			}
			if (count($arNotify) > 5000)
			{
				break;
			}
		}
		CTimeZone::Enable();

		return $arNotify;
	}

	public static function GetFlashNotify($arUnreadNotify)
	{
		$arFlashNotify = Array();
		if (isset($_SESSION['IM_FLASHED_NOTIFY']))
		{
			foreach ($arUnreadNotify as $value)
			{
				if (!isset($_SESSION['IM_FLASHED_NOTIFY'][$value]))
				{
					$_SESSION['IM_FLASHED_NOTIFY'][$value] = $value;
					$arFlashNotify[$value] = true;
				}
				else
					$arFlashNotify[$value] = false;
			}
		}
		else
		{
			$_SESSION['IM_FLASHED_NOTIFY'] = Array();
			foreach ($arUnreadNotify as $value)
			{
				$_SESSION['IM_FLASHED_NOTIFY'][$value] = $value;
				$arFlashNotify[$value] = true;
			}
		}

		return $arFlashNotify;
	}

	public function GetNotify($ID)
	{
		global $DB;

		$ID = intval($ID);

		$strSql = "SELECT M.* FROM b_im_relation R, b_im_message M WHERE M.ID = ".$ID." AND R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND R.CHAT_ID = M.CHAT_ID";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
			return $arRes;

		return false;
	}

	public static function GetFormatNotify($arFields)
	{
		$CCTP = new CTextParser();
		$CCTP->allow["SMILES"] = "N";
		$CCTP->allow["USER"] = "N";
		$CCTP->allow["VIDEO"] = "N";

		if (isset($arFields['HIDE_LINK']) && $arFields['HIDE_LINK'] == 'Y')
			$CCTP->allow["ANCHOR"] = "N";

		$CCTP->link_target = "_self";
		$arNotify = Array(
			'id' => $arFields['ID'],
			'type' => $arFields['NOTIFY_TYPE'],
			'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arFields['DATE_CREATE']),
			'silent' => $arFields['NOTIFY_SILENT']? 'Y': 'N',
			'text' => str_replace('#BR#', '<br>', $CCTP->convertText($arFields['MESSAGE'])),
			'tag' => strlen($arFields['NOTIFY_TAG'])>0? md5($arFields['NOTIFY_TAG']): '',
			'originalTag' => $arFields['NOTIFY_TAG'],
			'original_tag' => $arFields['NOTIFY_TAG'],
			'read' => $arFields['NOTIFY_READ'],
			'settingName' => $arFields['NOTIFY_MODULE'].'|'.$arFields['NOTIFY_EVENT'],
			'params' => isset($arFields['PARAMS'])? $arFields['PARAMS']: Array(),
			'counter' => isset($arFields['COUNTER'])? (int)$arFields['COUNTER']: 0,
		);
		if (!isset($arFields["FROM_USER_DATA"]))
		{
			$arUsers = CIMContactList::GetUserData(Array('ID' => $arFields['FROM_USER_ID'], 'DEPARTMENT' => 'N', 'USE_CACHE' => 'Y', 'CACHE_TTL' => 86400));
			$arFields["FROM_USER_DATA"] = $arUsers['users'];
		}

		$arNotify['userId'] = $arFields["FROM_USER_ID"];
		$arNotify['userName'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['name'];
		$arNotify['userColor'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['color'];
		$arNotify['userAvatar'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['avatar'];
		$arNotify['userLink'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['profile'];

		if ($arFields['NOTIFY_TYPE'] == IM_NOTIFY_CONFIRM)
		{
			$arNotify['buttons'] = unserialize($arFields['NOTIFY_BUTTONS']);
		}
		else
		{
			$arNotify['title'] = htmlspecialcharsbx($arFields['NOTIFY_TITLE']);
		}

		return $arNotify;
	}

	public function MarkNotifyRead($id = 0, $setThisAndHigher = false, $appId = 'Bitrix24')
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			return false;

		$message = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TAG'),
			'filter' => Array(
				'=ID' => $id,
				'=RELATION.MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
				'=RELATION.USER_ID' => $this->user_id,
			)
		))->fetch();
		if (!$message)
		{
			return false;
		}

		$chatId = intval($message['CHAT_ID']);
		$messages = array();

		$filterId = ($setThisAndHigher? '>=': '=').'ID';
		$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TAG'),
			'filter' => Array(
				'=CHAT_ID' => $chatId,
				'=NOTIFY_READ' => 'N',
				'!=NOTIFY_TYPE' => IM_NOTIFY_CONFIRM,
				$filterId => $id,
			)
		));
		while ($row = $orm->fetch())
		{
			$messages[$row['ID']] = $row;
		}

		if ($messages)
		{
			$DB->Query("UPDATE b_im_message SET NOTIFY_READ = 'Y' WHERE ID IN (".implode(',', array_keys($messages)).")");
		}

		if (!empty($message['NOTIFY_TAG']))
		{
			$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
				'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TYPE'),
				'filter' => Array(
					'=CHAT_ID' => $chatId,
					'=NOTIFY_READ' => 'N',
					'=NOTIFY_TAG' => $message['NOTIFY_TAG'],
				)
			));
			$tagIds = Array();
			while ($row = $orm->fetch())
			{
				if ($row['NOTIFY_TYPE'] == IM_NOTIFY_CONFIRM)
					continue;

				$messages[$row['ID']] = $row;
				$tagIds[] = $row['ID'];
			}
			if ($tagIds)
			{
				$DB->Query("UPDATE b_im_message SET NOTIFY_READ = 'Y' WHERE ID IN (".implode(',', $tagIds).")");
			}
		}

		if (!$messages)
		{
			self::SetLastId($chatId, $this->user_id);
			return false;
		}

		$lastId = max(array_keys($messages));

		self::SetLastId($chatId, $this->user_id, $lastId);

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'readNotifyList',
				'params' => Array(
					'chatId' => $chatId,
					'list' => array_keys($messages),
					'counter' => (int)self::GetCounter($chatId)
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));

			\Bitrix\Pull\MobileCounter::send($this->user_id, $appId);
		}

		CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_NOTIFY);

		return true;
	}

	public function MarkNotifyReadBySubTag($subTagList = array())
	{
		global $DB;

		if (empty($subTagList))
		{
			return;
		}

		if (!is_array($subTagList))
		{
			$subTagList = array($subTagList);
		}

		$users = array();
		$chats = array();
		$messages = array();
		$messagesByUser = array();

		$strSql ="
			SELECT M.ID, M.CHAT_ID, R.USER_ID
			FROM b_im_message M
			LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID
			WHERE 
				M.NOTIFY_SUB_TAG IN (".implode(",", array_map(function($subTag) { global $DB; return "'".$DB->ForSQL($subTag)."'";}, $subTagList)).") 
				AND M.NOTIFY_READ='N' 
				AND M.NOTIFY_TYPE != '".IM_NOTIFY_CONFIRM."'";
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while ($message = $res->fetch())
		{
			$messages[$message["ID"]] = $message;
			$messagesByUser[$message["CHAT_ID"]][] = $message["ID"];
			$users[$message["CHAT_ID"]] = $message["USER_ID"];
			$chats[$message["CHAT_ID"]] = $message["CHAT_ID"];
		}

		if (!empty($messages))
		{
			$strSql ="UPDATE b_im_message SET NOTIFY_READ = 'Y' WHERE ID IN (".implode(",", array_keys($messages)).")";
			$DB->Query($strSql);

			$counters = \CIMNotify::GetCounters($chats);
			foreach ($counters as $chatId => $counter)
			{
				$DB->Query("UPDATE b_im_relation SET COUNTER = {$counter} WHERE CHAT_ID = {$chatId}");
			}

			$isLoadPull = Loader::includeModule("pull");
			foreach ($messagesByUser as $chatId => $messagesList)
			{
				\Bitrix\Im\Counter::clearCache($users[$chatId]);
				CIMMessenger::SpeedFileDelete($users[$chatId], IM_SPEED_NOTIFY);

				if ($isLoadPull)
				{
					\Bitrix\Pull\Event::add($users[$chatId], Array(
						'module_id' => 'im',
						'command' => 'readNotifyList',
						'params' => Array(
							'chatId' => $chatId,
							'list' => array_values($messagesList),
							'counter' => (int)$counters[$chatId]
						),
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
				}
			}
		}

		return true;
	}

	public function MarkNotifyUnRead($id = 0, $setThisAndHigher = false, $appId = 'Bitrix24')
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			return false;

		$message = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TAG', 'NOTIFY_READ'),
			'filter' => Array(
				'=ID' => $id,
				'=RELATION.MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
				'=RELATION.USER_ID' => $this->user_id,
			)
		))->fetch();
		if (!$message)
		{
			return false;
		}

		$chatId = intval($message['CHAT_ID']);
		$messages = array();

		$filterId = ($setThisAndHigher? '>=': '=').'ID';
		$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TAG'),
			'filter' => Array(
				'=CHAT_ID' => $chatId,
				'=NOTIFY_READ' => 'Y',
				'!=NOTIFY_TYPE' => IM_NOTIFY_CONFIRM,
				$filterId => $id,
			)
		));
		while ($row = $orm->fetch())
		{
			$messages[$row['ID']] = $row;
		}

		if ($messages)
		{
			$DB->Query("UPDATE b_im_message SET NOTIFY_READ = 'N' WHERE ID IN (".implode(',', array_keys($messages)).")");
		}

		if (!empty($message['NOTIFY_TAG']))
		{
			$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
				'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TYPE', 'NOTIFY_READ'),
				'filter' => Array(
					'=CHAT_ID' => $chatId,
					'=NOTIFY_READ' => 'Y',
					'=NOTIFY_TAG' => $message['NOTIFY_TAG'],
				)
			));
			$tagIds = Array();
			while ($row = $orm->fetch())
			{
				if ($row['NOTIFY_TYPE'] == IM_NOTIFY_CONFIRM)
					continue;

				$messages[$row['ID']] = $row;
				$tagIds[] = $row['ID'];
			}

			if ($tagIds)
			{
				$DB->Query("UPDATE b_im_message SET NOTIFY_READ = 'N' WHERE ID IN (".implode(',', $tagIds).")");
			}
		}

		if (!$messages)
		{
			return false;
		}

		$lastId = max(array_keys($messages));

		self::SetLastId($chatId, $this->user_id, $lastId);

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'unreadNotifyList',
				'params' => Array(
					'chatId' => $chatId,
					'list' => array_keys($messages),
					'counter' => (int)self::GetCounter($chatId)
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));

			\Bitrix\Pull\MobileCounter::send($this->user_id, $appId);
		}

		CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_NOTIFY);

		return true;
	}

	public static function SetLastId($chatId, $userId, $lastId = null)
	{
		global $DB;

		if (intval($chatId) <= 0 || intval($userId) <= 0)
			return false;

		$ssqlLastId = "";
		if (!is_null($lastId))
		{
			$ssqlLastId = "LAST_ID = (case when LAST_ID < ".intval($lastId)." then ".intval($lastId)." else LAST_ID end),";
		}

		$counter = \CIMNotify::GetCounter($chatId);

		$status = "STATUS = ".IM_STATUS_READ.", ";
		if ($counter > 0)
		{
			$status = "STATUS = (case when STATUS = ".IM_STATUS_READ." then ".IM_STATUS_UNREAD." else STATUS end), ";
		}

		$DB->Query("UPDATE b_im_relation SET ".$ssqlLastId." ".$status." COUNTER = ".$counter." WHERE CHAT_ID = ".intval($chatId));

		//if (!is_null($lastId))
		//{
		//	CIMNotify::SetLastSendId($chatId, $lastId);
		//}

		\Bitrix\Im\Counter::clearCache($userId);

		return true;
	}

	public static function SetLastSendId($chatId, $lastSendId)
	{
		global $DB;

		if (intval($chatId) <= 0 || intval($lastSendId) <= 0)
			return false;

		$strSql = "
		UPDATE b_im_relation SET
			LAST_SEND_ID = (case when LAST_SEND_ID < ".intval($lastSendId)." then ".intval($lastSendId)." else LAST_SEND_ID end),
			STATUS = ".IM_STATUS_NOTIFY."
		WHERE CHAT_ID = ".intval($chatId);
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	public function Confirm($id, $value)
	{
		global $DB;

		$id = intval($id);

		$strSql = "
			SELECT M.*
			FROM b_im_relation R, b_im_message M
			WHERE M.ID = ".$id." AND R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND R.CHAT_ID = M.CHAT_ID AND M.NOTIFY_TYPE = ".IM_NOTIFY_CONFIRM;
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!($arRes = $dbRes->Fetch()))
			return false;

		$arRes['RELATION_USER_ID'] = $this->user_id;
		$arRes['NOTIFY_BUTTONS'] = unserialize($arRes['NOTIFY_BUTTONS']);

		$resultMessages = Array();
		if (strlen($arRes['NOTIFY_TAG'])>0)
		{
			$CBXSanitizer = new CBXSanitizer;
			$CBXSanitizer->AddTags(array(
				'a' => array('href','style', 'target'),
				'b' => array(), 'u' => array(),
				'i' => array(), 'br' => array(),
				'span' => array('style'),
			));

			foreach(GetModuleEvents("im", "OnBeforeConfirmNotify", true) as $arEvent)
			{
				$resultEvent = ExecuteModuleEventEx($arEvent, Array($arRes['NOTIFY_MODULE'], $arRes['NOTIFY_TAG'], $value, $arRes));
				if($resultEvent===false || is_array($resultEvent) && $resultEvent['result'] === false)
				{
					$resultMessages = Array();
					if (is_array($resultEvent) && $resultEvent['text'])
					{
						$resultMessages[] = $CBXSanitizer->SanitizeHtml($resultEvent['text']);
					}
					break;
				}
				else if (is_array($resultEvent) && $resultEvent['text'] || is_string($resultEvent) && strlen($resultEvent) > 0)
				{
					$resultMessages[] = $CBXSanitizer->SanitizeHtml(is_string($resultEvent)? $resultEvent: $resultEvent['text']);
				}
			}
		}
		if (empty($resultMessages))
		{
			foreach ($arRes['NOTIFY_BUTTONS'] as $button)
			{
				if ($button['VALUE'] == $value)
				{
					$resultMessages[] = GetMessage('IM_CONFIRM_CHOICE', Array('#BUTTON#' => $button['TITLE']));
					break;
				}
			}
		}

		self::Delete($id);

		if (strlen($arRes['NOTIFY_TAG'])>0)
		{
			foreach(GetModuleEvents("im", "OnAfterConfirmNotify", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arRes['NOTIFY_MODULE'], $arRes['NOTIFY_TAG'], $value, $arRes, $resultMessages));
		}

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'confirmNotify',
				'params' => Array(
					'id' => $id,
					'chatId' => intval($arRes['CHAT_ID']),
					'confirmMessages' => $resultMessages,
					'counter' => (int)self::GetCounter($arRes['CHAT_ID']),
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		CIMMessenger::SpeedFileDelete($this->user_id, IM_SPEED_NOTIFY);

		return $resultMessages;
	}

	public function Answer($id, $text)
	{
		global $DB;

		$id = intval($id);
		$text = trim($text);
		if ($id <= 0 || strlen($text) <= 0)
			return false;

		$strSql = "
			SELECT M.*
			FROM b_im_relation R, b_im_message M
			WHERE M.ID = ".$id." AND R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND R.CHAT_ID = M.CHAT_ID
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!($arRes = $dbRes->Fetch()))
			return false;

		$mention = \Bitrix\Im\User::getInstance($arRes['AUTHOR_ID']);
		if ($mention->isExists())
		{
			$text = '[USER='.$mention->getId().']'.$mention->getFullName(false).'[/USER] '.$text;
		}

		$CBXSanitizer = new CBXSanitizer;
		$CBXSanitizer->AddTags(array(
			'a' => array('href','style', 'target'),
			'b' => array(), 'u' => array(),
			'i' => array(), 'br' => array(),
			'span' => array('style'),
		));

		foreach(GetModuleEvents("im", "OnAnswerNotify", true) as $arEvent)
		{
			$resultEvent = ExecuteModuleEventEx($arEvent, Array($arRes['NOTIFY_MODULE'], $arRes['NOTIFY_TAG'], $text, $arRes));
			if($resultEvent===false || is_array($resultEvent) && $resultEvent['result'] === false)
			{
				$resultMessages = Array();
				if (is_array($resultEvent) && $resultEvent['text'])
				{
					$resultMessages[] = $CBXSanitizer->SanitizeHtml($resultEvent['text']);
				}
				break;
			}
			else if (is_array($resultEvent) && $resultEvent['text'] || is_string($resultEvent) && strlen($resultEvent) > 0)
			{
				$resultMessages[] = $CBXSanitizer->SanitizeHtml(is_string($resultEvent)? $resultEvent: $resultEvent['text']);
			}
		}

		if (empty($resultMessages))
		{
			$resultMessages[] = GetMessage('IM_ANSWER_DONE');
		}

		return $resultMessages;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$strSql = "
			SELECT M.*, R.USER_ID RELATION_USER_ID
			FROM b_im_message M
			LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID
			WHERE M.ID = ".$ID." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."'
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arRes = $dbRes->Fetch();
		if (!$arRes)
			return false;

		CIMMessageParam::DeleteAll($ID);
		\Bitrix\Im\Model\MessageTable::delete($ID);

		$counter = \CIMNotify::GetCounter($arRes['CHAT_ID']);
		$DB->Query("UPDATE b_im_relation SET COUNTER = {$counter} WHERE CHAT_ID = ".intval($arRes['CHAT_ID']));

		\Bitrix\Im\Counter::clearCache($arRes['RELATION_USER_ID']);

		foreach(GetModuleEvents("im", "OnAfterDeleteNotify", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arRes));

		CIMMessenger::SpeedFileDelete($arRes['RELATION_USER_ID'], IM_SPEED_NOTIFY);

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($arRes['RELATION_USER_ID'], Array(
				'module_id' => 'im',
				'command' => 'deleteNotifies',
				'params' => Array(
					'id' => Array($ID => $arRes['NOTIFY_TYPE'])
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		return true;
	}

	public function DeleteWithCheck($ID)
	{
		global $DB;

		$ID = intval($ID);

		$strSql = "SELECT M.* FROM b_im_relation R, b_im_message M WHERE M.ID = ".$ID." AND R.USER_ID = ".$this->user_id." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' AND R.CHAT_ID = M.CHAT_ID";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			self::Delete($arRes['ID']);
			return true;
		}

		return false;
	}

	public static function DeleteByTag($notifyTag, $authorId = false)
	{
		global $DB;
		if (strlen($notifyTag) <= 0)
			return false;

		$sqlUser = "";
		$sqlUser2 = "";
		if ($authorId !== false)
		{
			$sqlUser = " AND M.AUTHOR_ID = ".intval($authorId);
			$sqlUser2 = " AND AUTHOR_ID = ".intval($authorId);
		}

		$dbRes = $DB->Query("SELECT M.ID, M.NOTIFY_TYPE, R.USER_ID, R.STATUS FROM b_im_relation R, b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.NOTIFY_TAG = '".$DB->ForSQL($notifyTag)."'".$sqlUser, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arUsers = Array();
		$messages = Array();
		while ($row = $dbRes->Fetch())
		{
			$messages[$row['ID']] = $row['NOTIFY_TYPE'];
			$count = $row['STATUS'] < IM_STATUS_READ? 1: 0;
			if (isset($arUsers[$row['USER_ID']]))
				$arUsers[$row['USER_ID']] += $count;
			else
				$arUsers[$row['USER_ID']] = $count;
		}

		$pullActive = false;
		if (CModule::IncludeModule("pull"))
			$pullActive = true;

		$arUsersSend = Array();
		foreach ($arUsers as $userId => $count)
		{
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_NOTIFY);
			if ($count > 0)
			{
				$arUsersSend[] = $userId;
			}
			if ($pullActive)
			{
				CPushManager::DeleteFromQueueByTag($userId, $notifyTag);
			}
		}
		if ($pullActive)
		{
			\Bitrix\Pull\Event::add(array_keys($arUsers), Array(
				'module_id' => 'im',
				'command' => 'deleteNotifies',
				'params' => Array(
					'id' => $messages
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		if (count($messages) > 0)
		{
			foreach ($messages as $messageId => $message)
			{
				self::Delete($messageId);
			}
		}

		return true;
	}

	public static function ConfirmBySubTag($notifySubTag, $resultMessages)
	{
		global $DB;
		if (strlen($notifySubTag) <= 0)
			return false;

		$dbRes = $DB->Query("
			SELECT M.ID, M.NOTIFY_TYPE, R.USER_ID, R.STATUS, R.CHAT_ID 
			FROM b_im_relation R, b_im_message M 
			WHERE M.CHAT_ID = R.CHAT_ID AND M.NOTIFY_SUB_TAG = '".$DB->ForSQL($notifySubTag)."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arUsers = Array();
		$messages = Array();
		while ($row = $dbRes->Fetch())
		{
			$messages[$row['ID']] = $row;
		}

		$pullActive = false;
		if (CModule::IncludeModule("pull"))
			$pullActive = true;

		$arUsersSend = Array();
		foreach ($arUsers as $userId => $count)
		{
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_NOTIFY);
			if ($count > 0)
			{
				$arUsersSend[] = $userId;
			}
			if ($pullActive)
			{
				CPushManager::DeleteFromQueueBySubTag($userId, $notifySubTag);
			}
		}

		$counters = self::GetCounters(array_keys($arUsers));

		if (count($messages) > 0)
		{
			foreach ($messages as $messageId => $message)
			{
				self::Delete($messageId);
				if ($pullActive)
				{
					\Bitrix\Pull\Event::add($messages[$messageId]['USER_ID'], Array(
						'module_id' => 'im',
						'command' => 'confirmNotify',
						'params' => Array(
							'id' => $messageId,
							'chatId' => $messages[$messageId]['CHAT_ID'],
							'confirmMessages' => $resultMessages,
							'counter' => (int)$counters[$messages[$messageId]['USER_ID']],
						),
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
				}
			}
		}

		return true;
	}

	public static function DeleteBySubTag($notifySubTag, $authorId = false)
	{
		global $DB;
		if (strlen($notifySubTag) <= 0)
			return false;

		$sqlUser = "";
		$sqlUser2 = "";
		if ($authorId !== false)
		{
			$sqlUser = " AND M.AUTHOR_ID = ".intval($authorId);
			$sqlUser2 = " AND AUTHOR_ID = ".intval($authorId);
		}

		$dbRes = $DB->Query("SELECT M.ID, M.NOTIFY_TYPE, R.USER_ID, R.STATUS FROM b_im_relation R, b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.NOTIFY_SUB_TAG = '".$DB->ForSQL($notifySubTag)."'".$sqlUser, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arUsers = Array();
		$messages = Array();
		while ($row = $dbRes->Fetch())
		{
			$messages[$row['ID']] = $row['NOTIFY_TYPE'];
			$count = $row['STATUS'] < IM_STATUS_READ? 1: 0;
			if (isset($arUsers[$row['USER_ID']]))
				$arUsers[$row['USER_ID']] += $count;
			else
				$arUsers[$row['USER_ID']] = $count;
		}

		$pullActive = false;
		if (CModule::IncludeModule("pull"))
			$pullActive = true;

		$arUsersSend = Array();
		foreach ($arUsers as $userId => $count)
		{
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_NOTIFY);
			if ($count > 0)
			{
				$arUsersSend[] = $userId;
			}
			if ($pullActive)
			{
				CPushManager::DeleteFromQueueBySubTag($userId, $notifySubTag);
			}
		}
		if ($pullActive)
		{
			\Bitrix\Pull\Event::add(array_keys($arUsers), Array(
				'module_id' => 'im',
				'command' => 'deleteNotifies',
				'params' => Array(
					'id' => $messages
				),
				'extra' => \Bitrix\Im\Common::getPullExtra()
			));
		}

		if (count($messages) > 0)
		{
			foreach ($messages as $messageId => $message)
			{
				self::Delete($messageId);
			}
		}

		return true;
	}

	public static function DeleteByModule($moduleId, $moduleEvent = '')
	{
		global $DB;
		if (strlen($moduleId) <= 0)
			return false;

		$sqlEvent = '';
		if (strlen($moduleEvent) > 0)
			$sqlEvent = " AND NOTIFY_EVENT = '".$DB->ForSQL($moduleEvent)."'";

		$strSql = "DELETE FROM b_im_message WHERE NOTIFY_MODULE = '".$DB->ForSQL($moduleId)."'".$sqlEvent;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	public function GetNotifyCounter($arNotify = Array())
	{
		$count = 0;
		if (isset($arNotify['unreadNotify']) && !empty($arNotify['unreadNotify']) && isset($arNotify['notify']))
		{
			foreach ($arNotify['unreadNotify'] as $key => $value)
			{
				if (!isset($arNotify['notify'][$key]))
					continue;

				$count++;
			}
		}
		else
		{
			$cache = CIMMessenger::SpeedFileGet($this->user_id, IM_SPEED_NOTIFY);
			$count = $cache? $cache['counter']: 0;
		}
		return intval($count);
	}

	public static function GetCounter($chatId)
	{
		return \Bitrix\Im\Notify::getCounterByChatId($chatId);
	}

	public static function GetCounters($chatIds)
	{
		return \Bitrix\Im\Notify::getCountersByChatId($chatIds);
	}
}
?>