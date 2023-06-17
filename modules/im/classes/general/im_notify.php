<?
IncludeModuleLangFile(__FILE__);

use Bitrix\Im as IM;
use Bitrix\Im\User;
use Bitrix\Im\V2\Chat\NotifyChat;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;

class CIMNotify
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
				$sqlLimit = '';
				if ($DB->type == "MYSQL")
					$sqlLimit = " AND M.DATE_CREATE > DATE_SUB(NOW(), INTERVAL 30 DAY)";
				elseif ($DB->type == "MSSQL")
					$sqlLimit = " AND M.DATE_CREATE > dateadd(day, -30, getdate())";
				elseif ($DB->type == "ORACLE")
					$sqlLimit = " AND M.DATE_CREATE > SYSDATE-30";

				$strSql = $DB->TopSql($strSql, 100);
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

			$arNotify = $this->fillReadStatuses($arNotify);
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
			/*$strSql =
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
				return $arNotify;*/

			$result = IM\Model\MessageUnreadTable::query()
				->setSelect(['MESSAGE_ID'])
				->where('USER_ID', $this->user_id)
				->where('CHAT_TYPE', \IM_MESSAGE_SYSTEM) //todo add index
				->setLimit(100)
				->fetchAll()
			;

			$messageIds = array_column($result, 'MESSAGEE_ID');

			if (empty($messageIds))
			{
				return $arNotify;
			}

			$implodeMessageIds = implode(',', $messageIds);

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
				WHERE M.ID IN ({$implodeMessageIds})
				ORDER BY ID DESC
			";
			if (!$bTimeZone)
				CTimeZone::Enable();
			$strSql = $DB->TopSql($strSql, 100);
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			//$arMark = Array();
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

				/*if ($chatStatus == IM_STATUS_UNREAD && (!isset($arMark[$arRes["CHAT_ID"]]) || $arMark[$arRes["CHAT_ID"]] < $arRes["ID"]))
					$arMark[$arRes["CHAT_ID"]] = $arRes["ID"];*/

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

			/*foreach ($arMark as $chatId => $lastSendId)
				CIMNotify::SetLastSendId($chatId, $lastSendId);*/

			$arNotify['countNotify'] = $this->GetNotifyCounter($arNotify);
			CIMMessenger::SpeedFileCreate($this->user_id, array('counter' => $arNotify['countNotify'], 'maxId' => $arNotify['maxNotify']), IM_SPEED_NOTIFY);

			$arUsers = CIMContactList::GetUserData(Array('ID' => $arGetUsers, 'DEPARTMENT' => 'N', 'USE_CACHE' => 'Y', 'CACHE_TTL' => 86400));
			$arGetUsers = $arUsers['users'] ?? null;

			$counters = self::GetCounters($arChatId);

			$arNotify['notify'] = $this->fillReadStatuses($arNotify['notify']);
			foreach ($arNotify['notify'] as $id => $value)
			{
				$value['FROM_USER_DATA'] = $arGetUsers;
				$value['COUNTER'] = $counters[$value['CHAT_ID']];
				if (!$bTimeZone)
				{
					$dateTime = \Bitrix\Main\Type\DateTime::createFromTimestamp($value['DATE_CREATE']);
					$value['DATE_CREATE'] = $dateTime->toUserTime()->getTimestamp();
				}
				$arNotify['notify'][$id] = self::GetFormatNotify($value);
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

	public static function GetUnsendNotify() //todo refactor mail send
	{
		global $DB;

		$mailService = new IM\V2\Mail();
		$unsendIds = $mailService->getNotificationIdsToSend(5000);

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
					".$DB->DatetimeToTimestampFunction('M.DATE_CREATE')." DATE_CREATE,
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
					U2.SECOND_NAME FROM_USER_SECOND_NAME,
					U2.EXTERNAL_AUTH_ID FROM_EXTERNAL_AUTH_ID,
					C.AUTHOR_ID TO_USER_ID,
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
				FROM b_im_message M
				LEFT JOIN b_user U2 ON U2.ID = M.AUTHOR_ID
				LEFT JOIN b_im_chat C ON M.CHAT_ID = C.ID
				LEFT JOIN b_user U1 ON U1.ID = C.AUTHOR_ID
				WHERE M.ID IN ({$implodeUnsendIds})
				ORDER BY M.ID DESC
			";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		CTimeZone::Enable();

		$arNotify = Array();

		while ($arRes = $dbRes->Fetch())
		{
			$arRes["DATE_CREATE"] = $arRes["DATE_CREATE"] + CIMMail::GetUserOffset($arRes);
			$arNotify[$arRes['ID']] = $arRes;
		}

		return $arNotify;
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

	public static function GetFormatNotify(array $arFields): array
	{
		$messageText = \Bitrix\Im\Text::parse(
			\Bitrix\Im\Text::convertHtmlToBbCode($arFields['MESSAGE']),
			[
				'LINK' => (isset($arFields['HIDE_LINK']) && $arFields['HIDE_LINK'] === 'Y') ? 'N' : 'Y',
				'LINK_TARGET_SELF' => 'Y',
				'SAFE' => 'N',
				'FONT' => 'Y',
				'SMILES' => 'N',
			]
		);
		$users = [];
		if ((int)$arFields['FROM_USER_ID'] > 0)
		{
			$users[] = User::getInstance((int)$arFields['FROM_USER_ID'])->getArray([
				'JSON' => 'Y',
				'SKIP_ONLINE' => 'Y'
			]);
		}

		$arNotify = [
			'id' => $arFields['ID'],
			'type' => $arFields['NOTIFY_TYPE'],
			'date' => \Bitrix\Main\Type\DateTime::createFromTimestamp($arFields['DATE_CREATE']),
			'silent' => ($arFields['NOTIFY_SILENT'] ?? null) ? 'Y' : 'N',
			'onlyFlash' => (bool)($arFields['NOTIFY_ONLY_FLASH'] ?? false),
			'link' => (string)($arFields['NOTIFY_LINK'] ?? ''),
			'text' => $messageText,
			'tag' => $arFields['NOTIFY_TAG'] != '' ? md5($arFields['NOTIFY_TAG']): '',
			'originalTag' => $arFields['NOTIFY_TAG'],
			'original_tag' => $arFields['NOTIFY_TAG'],
			'read' => $arFields['NOTIFY_READ'] ?? null,
			'settingName' => $arFields['NOTIFY_MODULE'] . '|' . $arFields['NOTIFY_EVENT'],
			'params' => $arFields['PARAMS'] ?? [],
			'counter' => isset($arFields['COUNTER']) ? (int)$arFields['COUNTER'] : 0,
			'users' => $users
		];
		if (!isset($arFields["FROM_USER_DATA"]))
		{
			$arUsers = CIMContactList::GetUserData(Array('ID' => $arFields['FROM_USER_ID'], 'DEPARTMENT' => 'N', 'USE_CACHE' => 'Y', 'CACHE_TTL' => 86400));
			$arFields["FROM_USER_DATA"] = $arUsers['users'] ?? null;
		}

		$arNotify['userId'] = $arFields["FROM_USER_ID"];
		$arNotify['userName'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['name'] ?? null;
		$arNotify['userColor'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['color'] ?? null;
		$arNotify['userAvatar'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['avatar'] ?? null;
		$arNotify['userLink'] = $arFields["FROM_USER_DATA"][$arFields["FROM_USER_ID"]]['profile'] ?? null;

		if ($arFields['NOTIFY_TYPE'] == IM_NOTIFY_CONFIRM)
		{
			$arNotify['buttons'] = unserialize($arFields['NOTIFY_BUTTONS'], ['allowed_classes' => false]);
		}
		else
		{
			$arNotify['title'] = htmlspecialcharsbx($arFields['NOTIFY_TITLE']);
		}

		return $arNotify;
	}

	public function MarkNotifyRead($id = 0, $setThisAndHigher = false, $appId = 'Bitrix24')
	{
		$id = intval($id);
		if ($id < 0)
			return false;
		/*global $DB;



		if ($id === 0)
		{
			$query = [
				'select' => ['CHAT_ID'],
				'filter' => [
					'=USER_ID' => $this->user_id,
					'=MESSAGE_TYPE' => IM_MESSAGE_SYSTEM
				]
			];

			$chatResult = \Bitrix\Im\Model\RelationTable::getList($query)->fetch();
			$chatId = (int)$chatResult['CHAT_ID'];
			if (!$chatId)
			{
				return false;
			}
		}
		else
		{
			$query = [
				'select' => ['ID', 'CHAT_ID', 'NOTIFY_TAG'],
				'filter' => [
					'=RELATION.MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
					'=RELATION.USER_ID' => $this->user_id,
					'=ID' => $id
				]
			];

			$message = \Bitrix\Im\Model\MessageTable::getList($query)->fetch();
			if (!$message)
			{
				return false;
			}

			$chatId = (int)$message['CHAT_ID'];
		}

		$messages = array();

		$filter = [
			'=CHAT_ID' => $chatId,
			'=NOTIFY_READ' => 'N',
		];
		if ($id > 0)
		{
			$filterId = ($setThisAndHigher? '>=': '=').'ID';
			$filter[$filterId] = $id;
		}
		$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => ['ID', 'CHAT_ID', 'NOTIFY_TAG'],
			'filter' => $filter
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

		self::SetLastId($chatId, $this->user_id, $lastId);*/

		$findResult = NotifyChat::find(['TO_USER_ID' => $this->user_id]);

		if (!$findResult->isSuccess())
		{
			return false;
		}

		$chatId = (int)$findResult->getResult()['ID'];

		$readService = new IM\V2\Message\ReadService($this->user_id);
		$counterService = $readService->getCounterService();

		if ($id === 0)
		{
			$readService->readAllInChat($chatId);
			if (CModule::IncludeModule("pull"))
			{
				\Bitrix\Pull\Event::add($this->user_id, Array(
					'module_id' => 'im',
					'command' => 'notifyReadAll',
					'params' => Array(
						'chatId' => $chatId,
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));

				\Bitrix\Pull\MobileCounter::send($this->user_id, $appId);
			}

			return true;
		}

		$messages = [];

		$operator = $setThisAndHigher ? '>=' : '=';
		$query = IM\Model\MessageUnreadTable::query()
			->setSelect(['MESSAGE_ID'])
			->where('USER_ID', $this->user_id)
			->where('CHAT_ID', $chatId)
			->where('MESSAGE_ID', $operator, $id)
			->exec()
		;

		$messageCollection = new IM\V2\MessageCollection();

		while ($row = $query->fetch())
		{
			$messages[] = (int)$row['MESSAGE_ID'];
			$message = new Bitrix\Im\V2\Message();
			$message->setMessageId((int)$row['MESSAGE_ID'])->setChatId($chatId)->setAuthorId(0);
			$messageCollection->add($message);
		}

		if (empty($messages))
		{
			return false;
		}

		$counter = $readService->readNotifications($messageCollection, [$chatId => $this->user_id])->getResult()['COUNTERS'][$chatId];

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'notifyRead',
				'params' => Array(
					'chatId' => $chatId,
					'list' => $messages,
					'counter' => $counter
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
			return false;
		}

		if (!is_array($subTagList))
		{
			$subTagList = array($subTagList);
		}

		$result = IM\Model\MessageTable::query()
			->setSelect(['ID', 'CHAT_ID', 'USER_ID' => 'RELATION.USER_ID'])
			->whereIn('NOTIFY_SUB_TAG', $subTagList)
			->withUnreadOnly()
			->exec()
		;

		/*$sqlTags = Array();
		foreach ($subTagList as $value)
		{
			$value = (string)$value;
			$sqlTags[] = "'".$DB->ForSQL($value)."'";
		}*/

		$users = array();
		$chats = array();
		$messages = array();
		$messagesByUser = array();

		$messageCollection = new IM\V2\MessageCollection();

		while ($row = $result->fetch())
		{
			$messages[] = (int)$row['ID'];
			$users[$row['CHAT_ID']] = $row['USER_ID'];
			$chats[$row['CHAT_ID']] = $row['CHAT_ID'];
			$messagesByUser[$row['CHAT_ID']][] = $row['ID'];
			$message = new IM\V2\Message();
			$message->setMessageId((int)$row['ID'])->setChatId((int)$row['CHAT_ID']);
			$messageCollection->add($message);
		}

		if (empty($messages))
		{
			return true;
		}

		$readService = new IM\V2\Message\ReadService();
		$counters = $readService->readNotifications($messageCollection, $users)->getResult()['COUNTERS'];

		$isLoadPull = Loader::includeModule("pull");
		foreach ($messagesByUser as $chatId => $messagesList)
		{
			//\Bitrix\Im\Counter::clearCache($users[$chatId]);
			CIMMessenger::SpeedFileDelete($users[$chatId], IM_SPEED_NOTIFY);

			if ($isLoadPull)
			{
				\Bitrix\Pull\Event::add($users[$chatId], Array(
					'module_id' => 'im',
					'command' => 'notifyRead',
					'params' => Array(
						'chatId' => $chatId,
						'list' => array_values($messagesList),
						'counter' => (int)$counters[$chatId]
					),
					'extra' => \Bitrix\Im\Common::getPullExtra()
				));
			}
		}

		/*$strSql ="
			SELECT M.ID, M.CHAT_ID, R.USER_ID
			FROM b_im_message M
			LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID
			WHERE
				M.NOTIFY_SUB_TAG IN (".implode(",", $sqlTags).")
				AND M.NOTIFY_READ='N'";
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

			$counters = \CIMNotify::GetRealCounters($chats);
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
						'command' => 'notifyRead',
						'params' => Array(
							'chatId' => $chatId,
							'list' => array_values($messagesList),
							'counter' => (int)$counters[$chatId]
						),
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
				}
			}
		}*/

		return true;
	}

	public function MarkNotifyUnRead($id = 0, $setThisAndHigher = false, $appId = 'Bitrix24')
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			return false;

		$startNotify = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TAG'),
			'filter' => Array(
				'=ID' => $id,
				'=RELATION.MESSAGE_TYPE' => IM_MESSAGE_SYSTEM,
				'=RELATION.USER_ID' => $this->user_id,
			)
		))->fetch();
		if (!$startNotify)
		{
			return false;
		}

		$notifyTag = $startNotify['NOTIFY_TAG'] ?? null;

		$chatId = intval($startNotify['CHAT_ID']);

		$operator = $setThisAndHigher ? '>=' : '=';
		$notifyByIdResult = IM\Model\MessageTable::query()
			->setSelect(['ID'])
			->where('CHAT_ID', $chatId)
			->where('ID', $operator, $id)
			->withReadOnly()
			->exec()
		;

		$messages = new IM\V2\MessageCollection();

		while ($notifyById = $notifyByIdResult->fetch())
		{
			$message = new IM\V2\Message();
			$message->setMessageId((int)$notifyById['ID'])->setChatId($chatId)->setAuthorId(0);
			$messages->add($message);
		}

		if ($notifyTag !== null && $notifyTag !== '')
		{
			$notifyByTagResult = IM\Model\MessageTable::query()
				->setSelect(['ID'])
				->where('CHAT_ID', $chatId)
				->where('NOTIFY_TAG', $notifyTag)
				->withReadOnly()
				->exec()
			;

			while ($notifyByTag = $notifyByTagResult->fetch())
			{
				$message = new IM\V2\Message();
				$message->setMessageId((int)$notifyByTag['ID'])->setChatId($chatId)->setAuthorId(0);
				$messages->add($message);
			}
		}

		$readService = new IM\V2\Message\ReadService($this->user_id);

		$relation = new IM\V2\Relation();
		$relation->setChatId($chatId)->setUserId($this->user_id)->setMessageType('S')->setNotifyBlock(false);

		$counter = $readService->unreadNotifications($messages, $relation)->getResult()['COUNTER'];

/*		$filterId = ($setThisAndHigher? '>=': '=').'ID';
		$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
			'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TAG'),
			'filter' => Array(
				'=CHAT_ID' => $chatId,
				'=NOTIFY_READ' => 'Y',
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

		if (!empty($startNotify['NOTIFY_TAG']))
		{
			$orm = \Bitrix\Im\Model\MessageTable::getList(Array(
				'select' => Array('ID', 'CHAT_ID', 'NOTIFY_TYPE', 'NOTIFY_READ'),
				'filter' => Array(
					'=CHAT_ID' => $chatId,
					'=NOTIFY_READ' => 'Y',
					'=NOTIFY_TAG' => $startNotify['NOTIFY_TAG'],
				)
			));
			$tagIds = Array();
			while ($row = $orm->fetch())
			{
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

		self::SetLastId($chatId, $this->user_id, $lastId);*/

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'notifyUnread',
				'params' => Array(
					'chatId' => $chatId,
					'list' => $messages->getIds(),
					'counter' => $counter
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
		/*global $DB;

		if (intval($chatId) <= 0 || intval($userId) <= 0)
			return false;

		$ssqlLastId = "";
		if (!is_null($lastId))
		{
			$ssqlLastId = "LAST_ID = (case when LAST_ID < ".intval($lastId)." then ".intval($lastId)." else LAST_ID end),";
		}

		$counter = \CIMNotify::GetRealCounter($chatId);

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

		\Bitrix\Im\Counter::clearCache($userId);*/

		return true;
	}

	/**
	 * @deprecated
	 * @use ...
	 * @param $chatId
	 * @param $lastSendId
	 * @return bool
	 */
	public static function SetLastSendId($chatId, $lastSendId)
	{
		/*global $DB;

		if (intval($chatId) <= 0 || intval($lastSendId) <= 0)
			return false;

		$strSql = "
		UPDATE b_im_relation SET
			LAST_SEND_ID = (case when LAST_SEND_ID < ".intval($lastSendId)." then ".intval($lastSendId)." else LAST_SEND_ID end),
			STATUS = ".IM_STATUS_NOTIFY."
		WHERE CHAT_ID = ".intval($chatId);
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);*/

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
		$arRes['NOTIFY_BUTTONS'] = unserialize($arRes['NOTIFY_BUTTONS'], ['allowed_classes' => false]);

		$resultMessages = Array();
		if ($arRes['NOTIFY_TAG'] <> '')
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
				else if (is_array($resultEvent) && $resultEvent['text'] || is_string($resultEvent) && $resultEvent <> '')
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

		if ($arRes['NOTIFY_TAG'] <> '')
		{
			foreach(GetModuleEvents("im", "OnAfterConfirmNotify", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arRes['NOTIFY_MODULE'], $arRes['NOTIFY_TAG'], $value, $arRes, $resultMessages));
		}

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add($this->user_id, Array(
				'module_id' => 'im',
				'command' => 'notifyConfirm',
				'params' => Array(
					'id' => $id,
					'chatId' => intval($arRes['CHAT_ID']),
					'confirmMessages' => $resultMessages,
					'counter' => self::GetRealCounter($arRes['CHAT_ID']),
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
		if ($id <= 0 || $text == '')
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
			else if (is_array($resultEvent) && $resultEvent['text'] || is_string($resultEvent) && $resultEvent <> '')
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

	public static function Delete($id)
	{
		global $DB;

		$id = (int)$id;
		$notification = self::getNotificationById($id);
		if (!$notification)
		{
			return false;
		}

		$recentRow = \Bitrix\Im\Model\RecentTable::getRow([
			'select' => ['ITEM_MID'],
			'filter' => [
				'=USER_ID' => $notification['RELATION_USER_ID'],
				'=ITEM_TYPE' => IM_MESSAGE_SYSTEM,
				'=ITEM_ID' => $notification['RELATION_USER_ID'],
			],
			'limit' => 1
		]);
		if (!$recentRow)
		{
			$needToUpdateRecent = false;
		}
		else
		{
			$lastIdFromRecent = (int)$recentRow['ITEM_MID'];
			$needToUpdateRecent = $lastIdFromRecent === $id;
		}

		self::deleteInternal($id, (int)$notification['RELATION_USER_ID']);
		$counter = self::GetRealCounter($notification['CHAT_ID']);
		$time = microtime(true);
		if (Loader::includeModule('pull'))
		{
			\Bitrix\Pull\Event::add((int)$notification['RELATION_USER_ID'], [
				'module_id' => 'im',
				'command' => 'chatCounterChange',
				'params' => [
					'chatId' => (int)$notification['CHAT_ID'],
					'counter' => $counter,
					'time' => $time
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
		$chatId = (int)$notification['CHAT_ID'];
		// update unread counter
		//$DB->Query("UPDATE b_im_relation SET COUNTER = {$counter} WHERE CHAT_ID = ".$chatId);
		//\Bitrix\Im\Counter::clearCache($notification['RELATION_USER_ID']);

		self::updateStateAfterDelete($chatId, $notification['RELATION_USER_ID'], $needToUpdateRecent);

		foreach(GetModuleEvents("im", "OnAfterDeleteNotify", true) as $arEvent)
		{
			ExecuteModuleEventEx($arEvent, [$id, $notification]);
		}

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add(
				$notification['RELATION_USER_ID'],
				[
					'module_id' => 'im',
					'command' => 'notifyDelete',
					'params' => [
						'id' => [$id => $notification['NOTIFY_TYPE']],
						'counter' => $counter
					],
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
		}

		return true;
	}

	/**
	 *
	 * Deletes batch of notifications.
	 * Use only to delete notifications of one user!
	 *
	 * @param array $ids Array of notification IDs.
	 *
	 * @return bool
	 */
	private static function deleteList(array $ids): bool
	{
		global $DB;

		$cnt = count($ids);
		if ($cnt <= 0)
		{
			return false;
		}

		if ($cnt === 1)
		{
			self::Delete($ids[0]);

			return true;
		}

		$chatId = null;
		$relationUserId = null;
		$resultDataForPushAndPull = [];

		foreach ($ids as $id)
		{
			$arRes = self::getNotificationById($id);
			if (!$arRes)
			{
				continue;
			}

			self::deleteInternal($id, (int)$arRes['RELATION_USER_ID']);

			foreach(GetModuleEvents("im", "OnAfterDeleteNotify", true) as $arEvent)
			{
				ExecuteModuleEventEx($arEvent, [$id, $arRes]);
			}

			if (!$chatId)
			{
				$chatId = $arRes['CHAT_ID'];
			}
			if (!$relationUserId)
			{
				$relationUserId = $arRes['RELATION_USER_ID'];
			}
			$resultDataForPushAndPull[$id] = $arRes['NOTIFY_TYPE'];
		}

		if (!$chatId || !$relationUserId || count($resultDataForPushAndPull) === 0)
		{
			return false;
		}

		$recentRow = \Bitrix\Im\Model\RecentTable::getRow([
			'select' => ['ITEM_MID'],
			'filter' => [
				'=USER_ID' => $relationUserId,
				'=ITEM_TYPE' => IM_MESSAGE_SYSTEM,
				'=ITEM_ID' => $relationUserId,
			],
			'limit' => 1
		]);
		if (!$recentRow)
		{
			$needToUpdateRecent = false;
		}
		else
		{
			$lastIdFromRecent = (int)$recentRow['ITEM_MID'];
			$needToUpdateRecent = $lastIdFromRecent === max($ids);
		}

		$chatId = (int)$chatId;
		$counter = self::GetRealCounter($chatId);
		$time = microtime(true);
		if (Loader::includeModule('pull'))
		{
			\Bitrix\Pull\Event::add((int)$relationUserId, [
				'module_id' => 'im',
				'command' => 'chatCounterChange',
				'params' => [
					'chatId' => $chatId,
					'counter' => $counter,
					'time' => $time
				],
				'extra' => \Bitrix\Im\Common::getPullExtra()
			]);
		}
		// update unread counter
		//$DB->Query("UPDATE b_im_relation SET COUNTER = {$counter} WHERE CHAT_ID = ".$chatId);
		\Bitrix\Im\Counter::clearCache($relationUserId);

		self::updateStateAfterDelete($chatId, $relationUserId, $needToUpdateRecent);

		if (CModule::IncludeModule("pull"))
		{
			\Bitrix\Pull\Event::add(
				$relationUserId,
				[
					'module_id' => 'im',
					'command' => 'notifyDelete',
					'params' => [
						'id' => $resultDataForPushAndPull,
						'counter' => $counter
					],
					'extra' => \Bitrix\Im\Common::getPullExtra()
				]
			);
			\Bitrix\Pull\Event::send();
		}

		return true;
	}

	/*
	 * Updates counters (unread and total) and last message for recent list.
	 */
	private static function updateStateAfterDelete(int $chatId, int $userId, bool $needToUpdateRecent): void
	{
		global $DB;
		CIMMessenger::SpeedFileDelete($userId, IM_SPEED_NOTIFY);

		// update total amount of notifications
		$date = new DateTime();
		$date->add('-60 days'); // sync with \Bitrix\Im\Notify::cleanNotifyAgent
		$messageCount = \Bitrix\Im\Model\MessageTable::getList([
			'select' => ['CNT'],
			'filter' => [
				'=CHAT_ID' => $chatId,
				'>DATE_CREATE' => $date
			],
			'runtime' => [
				new \Bitrix\Main\ORM\Fields\ExpressionField('CNT', 'COUNT(*)')
			]
		])->fetch();

		IM\Model\ChatTable::update($chatId, [
			'MESSAGE_COUNT' => $messageCount['CNT'],
		]);

		// update the preview of last message in recent list
		if ($needToUpdateRecent)
		{
			$ormParams = [
				'select' => ['ID'],
				'filter' => [
					'=CHAT_ID' => $chatId,
				],
				'order' => ['DATE_CREATE' => 'DESC'],
				'limit' => 1,
			];
			$lastNotification = \Bitrix\Im\Model\MessageTable::getRow($ormParams);
			if ($lastNotification !== null)
			{
				$DB->Query("
					UPDATE b_im_recent 
						SET ITEM_MID = {$lastNotification['ID']}
					WHERE
						USER_ID = {$userId}
					AND
						ITEM_TYPE = 'S'
					AND
						ITEM_ID = {$userId}
				");
			}
		}
	}

	private static function deleteInternal(int $id, int $userId): void
	{
		CIMMessageParam::DeleteAll($id, true);
		\Bitrix\Im\Model\MessageTable::delete($id);
		(new IM\V2\Message\ReadService())->deleteByMessageId($id, [$userId]);
	}

	private static function getNotificationById(int $id): ?array
	{
		global $DB;

		$sqlQuery = "
			SELECT M.*, R.USER_ID RELATION_USER_ID
			FROM b_im_message M
			LEFT JOIN b_im_relation R ON R.CHAT_ID = M.CHAT_ID
			WHERE M.ID = ".$id." AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."'
		";
		$dbRes = $DB->Query($sqlQuery, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$result = $dbRes->Fetch();
		if (!$result)
		{
			return null;
		}

		return $result;
	}

	private function fillReadStatuses(array $notifications): array
	{
		$messageIds = array_keys($notifications);

		$readStatuses = (new IM\V2\Message\ReadService($this->user_id))->getReadStatusesByMessageIds($messageIds);

		foreach ($notifications as $id => $notification)
		{
			$notifications[$id]['NOTIFY_READ'] = $readStatuses[$id] ? 'Y' : 'N';
		}

		return $notifications;
	}

	public function DeleteWithCheck($id)
	{
		global $DB;

		if (is_array($id) && count($id) === 1)
		{
			$id = $id[0];
		}

		if (is_array($id) && count($id) > 1)
		{
			$ids = array_map(static function($item) {
				return (int)$item;
			}, $id);

			$sqlWhere = "M.ID IN (" . implode(',', $ids) . ")";
		}
		else
		{
			$id = (int)$id;
			$sqlWhere = "M.ID = " . $id;
		}

		$strSql = "
			SELECT M.* FROM b_im_relation R, b_im_message M 
			WHERE ". $sqlWhere ." 
			AND R.USER_ID = ".$this->user_id." 
			AND R.MESSAGE_TYPE = '".IM_MESSAGE_SYSTEM."' 
			AND R.CHAT_ID = M.CHAT_ID
		";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$notificationsToDelete = [];
		while ($arRes = $dbRes->Fetch())
		{
			$notificationsToDelete[] = (int)$arRes['ID'];
		}

		if (count($notificationsToDelete) === 0)
		{
			return false;
		}

		if (count($notificationsToDelete) === 1)
		{
			self::Delete($notificationsToDelete[0]);
		}
		else
		{
			self::deleteList($notificationsToDelete);
		}

		return true;
	}

	public static function DeleteByTag($notifyTag, $authorId = false)
	{
		global $DB;

		$notifyTag = (string)$notifyTag;
		if ($notifyTag == '')
		{
			return false;
		}

		$sqlUser = "";
		if ($authorId !== false)
		{
			$sqlUser = " AND M.AUTHOR_ID = ".intval($authorId);
		}

		$dbRes = $DB->Query("SELECT M.ID, M.NOTIFY_TYPE, R.USER_ID FROM b_im_relation R, b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.NOTIFY_TAG = '".$DB->ForSQL($notifyTag)."'".$sqlUser, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arUsers = Array();
		$messages = Array();
		while ($row = $dbRes->Fetch())
		{
			$messages[$row['ID']] = $row['NOTIFY_TYPE'];
			$arUsers[$row['USER_ID']] = $row['USER_ID'];
		}

		$pullActive = false;
		if (CModule::IncludeModule("pull"))
			$pullActive = true;

		foreach ($arUsers as $userId => $count)
		{
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_NOTIFY);
			if ($pullActive)
			{
				CPushManager::DeleteFromQueueByTag($userId, $notifyTag);
			}
		}

		if (count($messages) > 0)
		{
			self::deleteList(array_keys($messages));
		}

		return true;
	}

	public static function ConfirmBySubTag($notifySubTag, $resultMessages)
	{
		global $DB;

		$notifySubTag = (string)$notifySubTag;
		if ($notifySubTag == '')
			return false;

		$dbRes = $DB->Query("
			SELECT M.ID, M.NOTIFY_TYPE, R.USER_ID, R.STATUS, R.CHAT_ID 
			FROM b_im_relation R, b_im_message M 
			WHERE M.CHAT_ID = R.CHAT_ID AND M.NOTIFY_SUB_TAG = '".$DB->ForSQL($notifySubTag)."'", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arUsers = Array();
		$arChatId = Array();
		$messages = Array();
		while ($row = $dbRes->Fetch())
		{
			$messages[$row['ID']] = $row;
			$arUsers[$row['USER_ID']] = $row['USER_ID'];
			$arChatId[$row['CHAT_ID']] = $row['CHAT_ID'];
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

		$counters = self::GetCounters(array_keys($arChatId));

		if (count($messages) > 0)
		{
			foreach ($messages as $messageId => $message)
			{
				self::Delete($messageId);
				if ($pullActive)
				{
					\Bitrix\Pull\Event::add($message['USER_ID'], Array(
						'module_id' => 'im',
						'command' => 'notifyConfirm',
						'params' => Array(
							'id' => $messageId,
							'chatId' => $message['CHAT_ID'],
							'confirmMessages' => $resultMessages,
							'counter' => $counters[$message['CHAT_ID']],
						),
						'extra' => \Bitrix\Im\Common::getPullExtra()
					));
				}
			}
		}

		return true;
	}

	public static function DeleteBySubTag($notifySubTag, $authorId = false, $pullActive = true)
	{
		global $DB;

		$notifySubTag = (string)$notifySubTag;
		if ($notifySubTag == '')
			return false;

		$sqlUser = "";
		if ($authorId !== false)
		{
			$sqlUser = " AND M.AUTHOR_ID = ".intval($authorId);
		}

		$dbRes = $DB->Query("SELECT M.ID, M.NOTIFY_TYPE, R.USER_ID FROM b_im_relation R, b_im_message M WHERE M.CHAT_ID = R.CHAT_ID AND M.NOTIFY_SUB_TAG = '".$DB->ForSQL($notifySubTag)."'".$sqlUser, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$arUsers = Array();
		$messages = Array();
		while ($row = $dbRes->Fetch())
		{
			$messages[$row['ID']] = $row['NOTIFY_TYPE'];
			$arUsers[$row['USER_ID']] = $row['USER_ID'];
		}

		$pullIncluded = $pullActive && CModule::IncludeModule("pull");

		foreach ($arUsers as $userId => $count)
		{
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_NOTIFY);
			if ($pullIncluded)
			{
				CPushManager::DeleteFromQueueBySubTag($userId, $notifySubTag);
			}
		}

		if (count($messages) > 0)
		{
			self::deleteList(array_keys($messages));
		}

		return true;
	}

	public static function DeleteByModule($moduleId, $moduleEvent = '')
	{
		global $DB;
		$moduleId = (string)$moduleId;
		if ($moduleId == '')
			return false;

		$sqlEvent = '';
		$sqlEventRead = '';
		$moduleEvent = (string)$moduleEvent;
		if ($moduleEvent <> '')
		{
			$sqlEvent = " AND NOTIFY_EVENT = '".$DB->ForSQL($moduleEvent)."'";
			$sqlEventRead = " AND M.NOTIFY_EVENT = '".$DB->ForSQL($moduleEvent)."'";
		}

		$strSql = "DELETE U FROM b_im_message M INNER JOIN b_im_message_unread U ON M.ID = U.MESSAGE_ID WHERE M.NOTIFY_MODULE = '".$DB->ForSQL($moduleId)."'".$sqlEvent;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$strSql = "DELETE V FROM b_im_message M INNER JOIN b_im_message_viewed V ON M.ID = V.MESSAGE_ID WHERE M.NOTIFY_MODULE = '".$DB->ForSQL($moduleId)."'".$sqlEvent;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

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

	public static function GetRealCounter($chatId)
	{
		return \Bitrix\Im\Notify::getRealCounter($chatId);
	}

	public static function GetRealCounters($chatId)
	{
		return \Bitrix\Im\Notify::getRealCounters($chatId);
	}

	public static function GetCounter($chatId)
	{
		return \Bitrix\Im\Notify::getCounter($chatId);
	}

	public static function GetCounters($chatIds)
	{
		return \Bitrix\Im\Notify::getCounters($chatIds);
	}
}
?>
