<?

use Bitrix\Main\Config\Option;

IncludeModuleLangFile(__FILE__);
require_once('pushservices/services_descriptions.php');


/**
 * Class CPullPush
 * @deprecated use Bitrix\Pull\Model\PushTable
 * @see \Bitrix\Pull\Model\PushTable
 */
class CPullPush
{
	/**
	 * @deprecated use Bitrix\Pull\Model\PushTable::getList
	 * @see Bitrix\Pull\Model\PushTable::getList
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param array $arSelect
	 * @param array $arNavStartParams
	 * @return \Bitrix\Main\DB\Result
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function GetList($arOrder = [], $arFilter = [], $arSelect = [], $arNavStartParams = [])
	{
		$params = [
			"filter" => $arFilter,
			"order" => $arOrder
		];
		if (!empty($arSelect))
		{
			$params["select"] = $arSelect;
		}

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"]) > 0)
		{
			$params["limit"] = intval($arNavStartParams["nTopCount"]);
		}

		$res = \Bitrix\Pull\Model\PushTable::getList($params);

		return $res;
	}


	/**
	 * @deprecated use Bitrix\Pull\Model\PushTable::add
	 * @see Bitrix\Pull\Model\PushTable::add
	 * @param array $arFields
	 * @return int
	 * @throws Exception
	 */
	public static function Add($arFields = Array())
	{
		$result = \Bitrix\Pull\Model\PushTable::add($arFields);

		return $result->getId();
	}

	public static function getUniqueHash($user_id, $app_id)
	{
		return md5($user_id . $app_id);
	}

	/**
	 * @deprecated use Bitrix\Pull\Model\PushTable::update
	 * @see Bitrix\Pull\Model\PushTable::update
	 * @param $ID
	 * @param array $arFields
	 * @return int
	 * @throws Exception
	 */
	public static function Update($ID, $arFields = Array())
	{
		$result = \Bitrix\Pull\Model\PushTable::update($ID, $arFields);
		return $result->getId();
	}

	/**
	 * @deprecated use Bitrix\Pull\Model\PushTable::delete
	 * @see Bitrix\Pull\Model\PushTable::delete
	 * @param bool $ID
	 * @return bool
	 * @throws Exception
	 */
	public static function Delete($ID = false)
	{
		$result = \Bitrix\Pull\Model\PushTable::delete(intval($ID));
		return $result->isSuccess();
	}

	public static function cleanTokens()
	{
		global $DB;

		/**
		 * @var $DB CAllDatabase
		 */
		$killTime = ConvertTimeStamp(getmicrotime() - 24 * 3600 * 14, "FULL");
		$sqlString = "DELETE FROM b_pull_push WHERE DATE_AUTH < " . $DB->CharToDateFunction($killTime);

		$DB->Query($sqlString, false, "FILE: " . __FILE__ . "<br> LINE: " . __LINE__);

		return "CPullPush::cleanTokens();";
	}
}


class CPushManager
{
	const SEND_IMMEDIATELY = 'IMMEDIATELY';
	const SEND_IMMEDIATELY_SILENT = 'IMMEDIATELY_SILENT';
	const SEND_DEFERRED = 'DEFERRED';
	const SEND_SKIP = 'SKIP';
	const RECORD_NOT_FOUND = 'NOT_FOUND';

	public const DEFAULT_APP_ID = "Bitrix24";

	public static $pushServices = false;
	protected static $appAliases = [];
	private $remoteProviderUrl ;

	public function __construct()
	{
		if (!is_array(self::$pushServices))
		{
			self::$pushServices = [];

			foreach (GetModuleEvents("pull", "OnPushServicesBuildList", true) as $arEvent)
			{
				$res = ExecuteModuleEventEx($arEvent);
				if (is_array($res))
				{
					if (!is_array($res[0]))
					{
						$res = [$res];
					}
					foreach ($res as $serv)
						self::$pushServices[$serv["ID"]] = $serv;
				}
			}
		}
		$this->remoteProviderUrl = Option::get("pull", "push_service_url");
	}

	public static function DeleteFromQueueByTag($userId, $tag, $appId = self::DEFAULT_APP_ID)
	{
		global $DB;
		if ($tag == '' || intval($userId) == 0)
		{
			return false;
		}

		$strSql = "DELETE FROM b_pull_push_queue WHERE USER_ID = " . intval($userId) . " AND TAG = '" . $DB->ForSQL($tag) . "'";
		$DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

		\Bitrix\Pull\Push::add($userId, [
			'module_id' => 'pull',
			'push' => [
				'advanced_params' => [
					"notificationsToCancel" => [$tag],
				],
				'send_immediately' => 'Y',
				'app_id' => $appId
			]
		]);

		return true;
	}

	/**
	 * @param $arParams
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function AddQueue($arParams)
	{
		if (!CPullOptions::GetPushStatus())
		{
			return false;
		}

		global $DB;

		if (is_array($arParams['USER_ID']))
		{
			foreach ($arParams['USER_ID'] as $key => $userId)
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$arFields['USER_ID'][$userId] = $userId;
				}
			}
			if (empty($arFields['USER_ID']))
			{
				return false;
			}
		}
		else
		{
			if (isset($arParams['USER_ID']) && intval($arParams['USER_ID']) > 0)
			{
				$userId = intval($arParams['USER_ID']);
				$arFields['USER_ID'][$userId] = $userId;
			}
			else
			{
				return false;
			}
		}

		$arFields['SKIP_USERS'] = array();
		if (is_array($arParams['SKIP_USERS']))
		{
			foreach ($arParams['SKIP_USERS'] as $key => $userId)
			{
				$userId = intval($userId);
				if ($userId > 0)
				{
					$arFields['SKIP_USERS'][] = $userId;
				}
			}
		}

		if (isset($arParams['MESSAGE']) && trim($arParams['MESSAGE']) <> '')
		{
			$arFields['MESSAGE'] = str_replace(["\r\n", "\n\r", "\n", "\r"], " ", trim($arParams['MESSAGE']));
		}

		$arFields['TAG'] = '';
		if (isset($arParams['TAG']) && trim($arParams['TAG']) <> '' && mb_strlen(trim($arParams['TAG'])) <= 255)
		{
			$arFields['TAG'] = trim($arParams['TAG']);
		}

		$arFields['SUB_TAG'] = '';
		if (isset($arParams['SUB_TAG']) && trim($arParams['SUB_TAG']) <> '' && mb_strlen(trim($arParams['SUB_TAG'])) <= 255)
		{
			$arFields['SUB_TAG'] = trim($arParams['SUB_TAG']);
		}

		$arFields['BADGE'] = -1;
		if (isset($arParams['BADGE']) && $arParams['BADGE'] != '' && intval($arParams['BADGE']) >= 0)
		{
			$arFields['BADGE'] = intval($arParams['BADGE']);
		}

		$arFields['PARAMS'] = '';
		if (isset($arParams['PARAMS']))
		{
			if (is_array($arParams['PARAMS']) || trim($arParams['PARAMS']) <> '')
			{
				$arFields['PARAMS'] = $arParams['PARAMS'];
			}
		}

		$arFields['ADVANCED_PARAMS'] = [];
		if (isset($arParams['ADVANCED_PARAMS']) && is_array($arParams['ADVANCED_PARAMS']))
		{
			$arFields['ADVANCED_PARAMS'] = $arParams['ADVANCED_PARAMS'];
		}
		if (!isset($arParams['ADVANCED_PARAMS']['id']) && $arFields['SUB_TAG'] <> '')
		{
			$arFields['ADVANCED_PARAMS']['id'] = $arFields['SUB_TAG'];
		}
		if (!isset($arFields['ADVANCED_PARAMS']['extra']['server_time']))
		{
			$arFields['ADVANCED_PARAMS']['extra']['server_time'] = date('c');
		}
		if (!isset($arFields['ADVANCED_PARAMS']['extra']['server_time_unix']))
		{
			$arFields['ADVANCED_PARAMS']['extra']['server_time_unix'] = microtime(true);
		}

		$arFields['EXPIRY'] = 43200;
		if (isset($arParams['EXPIRY']) && intval($arParams['EXPIRY']) >= 0)
		{
			$arFields['EXPIRY'] = intval($arParams['EXPIRY']);
		}

		if ($arParams['SOUND'] <> '')
		{
			$arFields['SOUND'] = $arParams['SOUND'];
		}

		$arFields['APP_ID'] = ($arParams['APP_ID'] <> '') ? $arParams['APP_ID'] : self::DEFAULT_APP_ID;

		$groupMode = [
			self::SEND_IMMEDIATELY => [],
			self::SEND_IMMEDIATELY_SILENT => [],
			self::SEND_DEFERRED => [],
			self::SEND_SKIP => [],
		];

		$devices = [];

		$options = [];
		if (isset($arParams['IMPORTANT']) && $arParams['IMPORTANT'] === 'Y')
		{
			$options['IMPORTANT'] = 'Y';
		}

		$info = self::GetDeviceInfo($arFields['USER_ID'], $options, $arFields['APP_ID']);
		foreach ($info as $userId => $params)
		{
			if (in_array($userId, $arFields['SKIP_USERS']))
			{
				$params['mode'] = self::SEND_SKIP;
			}
			else if ($params['mode'] == self::SEND_DEFERRED && isset($arParams['SEND_IMMEDIATELY']) && $arParams['SEND_IMMEDIATELY'] === 'Y')
			{
				$params['mode'] = self::SEND_IMMEDIATELY;
			}
			elseif (
				in_array($params['mode'], [self::SEND_IMMEDIATELY, self::SEND_IMMEDIATELY_SILENT])
				&& isset($arParams['SEND_DEFERRED']) && $arParams['SEND_DEFERRED'] === 'Y'
			)
			{
				$params['mode'] = self::SEND_DEFERRED;
			}

			if ($params['mode'] != self::RECORD_NOT_FOUND)
			{
				foreach(GetModuleEvents("pull", "OnBeforeSendPush", true) as $arEvent)
				{
					$resultEvent = ExecuteModuleEventEx($arEvent, [$userId, $params['mode'], $arFields]);
					if ($resultEvent)
					{
						$resultEvent = mb_strtoupper($resultEvent);
						if (in_array($resultEvent, [
							self::SEND_IMMEDIATELY,
							self::SEND_IMMEDIATELY_SILENT,
							self::SEND_DEFERRED,
							self::SEND_SKIP
						]))
						{
							$params['mode'] = $resultEvent;
						}
					}
				}
			}

			if (isset($groupMode[$params['mode']]))
			{
				$groupMode[$params['mode']][$userId] = $userId;
			}
			if (
				in_array($params['mode'], [self::SEND_IMMEDIATELY, self::SEND_IMMEDIATELY_SILENT])
				&& !empty($params['device'])
				&& !(isset($arParams['SEND_IMMEDIATELY']) && $arParams['SEND_IMMEDIATELY'] == 'Y')
			)
			{
				$devices = array_merge($devices, $params['device']);
			}
		}

		$pushImmediately = [];
		foreach ($groupMode as $type => $users)
		{
			foreach ($users as $userId)
			{
				$pushImmediately[] = self::prepareSend($userId, $arFields, $type);
			}
		}
		if (!empty($pushImmediately))
		{
			$CPushManager = new CPushManager();
			$CPushManager->SendMessage($pushImmediately, $devices);
		}

		foreach ($groupMode[self::SEND_DEFERRED] as $userId)
		{
			$arAdd = [
				'USER_ID' => $userId,
				'TAG' => $arFields['TAG'],
				'SUB_TAG' => $arFields['SUB_TAG'],
				'~DATE_CREATE' => $DB->CurrentTimeFunction()
			];

			if ($arFields['MESSAGE'] <> '')
			{
				$arAdd['MESSAGE'] = $arFields['MESSAGE'];
			}
			if (is_array($arFields['ADVANCED_PARAMS']))
			{
				$arAdd['ADVANCED_PARAMS'] = Bitrix\Main\Web\Json::encode($arFields['ADVANCED_PARAMS']);
			}
			if (is_array($arFields['PARAMS']))
			{
				$arAdd['PARAMS'] = Bitrix\Main\Web\Json::encode($arFields['PARAMS']);
			}
			else
			{
				if ($arFields['PARAMS'] <> '')
				{
					$arAdd['PARAMS'] = $arFields['PARAMS'];
				}
			}

			$arAdd['APP_ID'] = $arFields['APP_ID'];

			$DB->Add("b_pull_push_queue", $arAdd, ["MESSAGE", "PARAMS", "ADVANCED_PARAMS"]);

			CAgent::AddAgent("CPushManager::SendAgent();", "pull", "N", 30, "", "Y", ConvertTimeStamp(time() + CTimeZone::GetOffset() + 30, "FULL"), 100, false, false);
		}

		return true;
	}

	private static function prepareSend($userId, $fields, $type = self::SEND_IMMEDIATELY)
	{
		$result = [
			'USER_ID' => $userId,
		];

		if ($type != self::SEND_DEFERRED)
		{
			if (is_array($fields['PARAMS']))
			{
				if (isset($fields['PARAMS']['CATEGORY']))
				{
					$result['CATEGORY'] = $fields['PARAMS']['CATEGORY'];
					unset($fields['PARAMS']['CATEGORY']);
				}
				$result['PARAMS'] = Bitrix\Main\Web\Json::encode($fields['PARAMS']);
			}
			elseif ($fields['PARAMS'] <> '')
			{
				$result['PARAMS'] = $fields['PARAMS'];
			}

			if ($fields['MESSAGE'] <> '')
			{
				$result['MESSAGE'] = $fields['MESSAGE'];
			}

			if ($fields['SOUND'] <> '')
			{
				$result['SOUND'] = $fields['SOUND'];
			}
			else if ($type == self::SEND_IMMEDIATELY_SILENT)
			{
				$result['SOUND'] = 'silence.aif';
			}

			if (count($fields['ADVANCED_PARAMS']) > 0)
			{
				$result['ADVANCED_PARAMS'] = $fields['ADVANCED_PARAMS'];
			}
		}

		if ($type == self::SEND_SKIP)
		{
			unset($result['MESSAGE']);
			unset($result['ADVANCED_PARAMS']['senderName']);
		}

		if ($fields['EXPIRY'] <> '')
		{
			$result['EXPIRY'] = $fields['EXPIRY'];
		}

		if (intval($fields['BADGE']) >= 0)
		{
			$result['BADGE'] = $fields['BADGE'];
		}
		else
		{
			$result['BADGE'] = \Bitrix\Pull\MobileCounter::get($result['USER_ID']);
		}

		$result['APP_ID'] = $fields['APP_ID'];

		return $result;
	}

	/**
	 * @param $userId
	 * @param array $options
	 * @param string $appId
	 * @return array|bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function GetDeviceInfo($userId, $options = Array(), $appId = self::DEFAULT_APP_ID)
	{
		$result = [];
		if (!is_array($userId))
		{
			$userId = [$userId];
		}

		foreach ($userId as $id)
		{
			$id = intval($id);
			if ($id <= 0)
			{
				continue;
			}

			$result[$id] = [
				'mode' => self::RECORD_NOT_FOUND,
				'device' => [],
			];
		}

		if (empty($result))
		{
			return false;
		}

		$imInclude = \Bitrix\Main\Loader::includeModule('im');

		$query = new \Bitrix\Main\Entity\Query(\Bitrix\Main\UserTable::getEntity());

		$sago = Bitrix\Main\Application::getConnection()->getSqlHelper()->addSecondsToDateTime('-300');
		$query->registerRuntimeField('', new \Bitrix\Main\Entity\ExpressionField('IS_ONLINE_CUSTOM', 'CASE WHEN LAST_ACTIVITY_DATE > ' . $sago . ' THEN \'Y\' ELSE \'N\' END'));
		$query
			->addSelect('ID')
			->addSelect('ACTIVE')
			->addSelect('EMAIL')
			->addSelect('IS_ONLINE_CUSTOM');

		if ($imInclude)
		{
			$query->registerRuntimeField('', new \Bitrix\Main\Entity\ReferenceField('im', 'Bitrix\Im\Model\StatusTable', ['=this.ID' => 'ref.USER_ID']));
			$query
				->addSelect('im.IDLE', 'IDLE')
				->addSelect('im.DESKTOP_LAST_DATE', 'DESKTOP_LAST_DATE')
				->addSelect('im.MOBILE_LAST_DATE', 'MOBILE_LAST_DATE')
			;
		}

		$query->registerRuntimeField('', new \Bitrix\Main\Entity\ReferenceField('push', 'Bitrix\Pull\Model\PushTable', ['=this.ID' => 'ref.USER_ID']));
		$query->registerRuntimeField('', new \Bitrix\Main\Entity\ExpressionField('HAS_MOBILE', 'CASE WHEN main_user_push.USER_ID > 0 THEN \'Y\' ELSE \'N\' END'));
		$query
			->addSelect('HAS_MOBILE')
			->addSelect('push.APP_ID', 'APP_ID')
			->addSelect('push.UNIQUE_HASH', 'UNIQUE_HASH')
			->addSelect('push.DEVICE_TYPE', 'DEVICE_TYPE')
			->addSelect('push.DEVICE_TOKEN', 'DEVICE_TOKEN')
			->addSelect('push.VOIP_TYPE', 'VOIP_TYPE')
			->addSelect('push.VOIP_TOKEN', 'VOIP_TOKEN');

		$query->addFilter('=ID', array_keys($result));
		$queryResult = $query->exec();

		while ($user = $queryResult->fetch())
		{
			$uniqueHashes[] = CPullPush::getUniqueHash($user["ID"], $appId);
			$uniqueHashes[] = CPullPush::getUniqueHash($user["ID"], $appId . "_bxdev");

			if (in_array($user['UNIQUE_HASH'], $uniqueHashes) && $user['ACTIVE'] == 'Y')
			{
				$result[$user['ID']]['device'][] = [
					'APP_ID' => $user['APP_ID'],
					'USER_ID' => $user['ID'],
					'DEVICE_TYPE' => $user['DEVICE_TYPE'],
					'DEVICE_TOKEN' => $user['DEVICE_TOKEN'],
					'VOIP_TYPE' => $user['VOIP_TYPE'],
					'VOIP_TOKEN' => $user['VOIP_TOKEN'],
				];
			}
			else
			{
				continue;
			}

			if ($result[$user['ID']]['mode'] != self::RECORD_NOT_FOUND)
			{
				continue;
			}

			if ($user['HAS_MOBILE'] == 'N')
			{
				$result[$user['ID']]['mode'] = self::RECORD_NOT_FOUND;
				$result[$user['ID']]['device'] = [];
				continue;
			}

			if ($options['IMPORTANT'] == 'Y')
			{
				$result[$user['ID']]['mode'] = self::SEND_IMMEDIATELY;
				continue;
			}

			if (!\Bitrix\Pull\Push::getStatus($user['ID']))
			{
				$result[$user['ID']]['mode'] = self::RECORD_NOT_FOUND;
				$result[$user['ID']]['device'] = [];
				continue;
			}

			$isMobile = false;
			$isOnline = false;
			$isDesktop = false;
			$isDesktopIdle = false;

			if ($user['IS_ONLINE_CUSTOM'] == 'Y')
			{
				$isOnline = true;
			}

			if ($imInclude)
			{
				$user = CIMStatus::prepareLastDate($user);

				$mobileLastDate = $user['MOBILE_LAST_DATE']? $user['MOBILE_LAST_DATE']->getTimestamp(): 0;
				if ($mobileLastDate > 0 && $mobileLastDate + 300 > time())
				{
					$isMobile = true;
				}

				$isDesktop = CIMMessenger::CheckDesktopStatusOnline($user['ID']);
				if ($isDesktop && $isOnline && is_object($user['IDLE']))
				{
					if ($user['IDLE']->getTimestamp() > 0 )
					{
						$isDesktopIdle = true;
					}
				}
			}

			$status = self::SEND_IMMEDIATELY;
			if ($isMobile)
			{
				$status = self::SEND_IMMEDIATELY;
			}
			else if ($isOnline)
			{
				if (!\Bitrix\Pull\PushSmartfilter::getStatus($user['ID']))
				{
					$status = self::SEND_IMMEDIATELY_SILENT;
				}
				else
				{
					$status = self::SEND_DEFERRED;
					if ($isDesktop)
					{
						$status = self::SEND_SKIP;
						if ($isDesktopIdle)
						{
							$status = self::SEND_IMMEDIATELY;
						}
					}
					else
					{
						$result[$user['ID']]['device'] = [];
					}
				}
			}
			$result[$user['ID']]['mode'] = $status;
		}

		return $result;
	}

	private function getUniqueHashes($userId, $appId)
	{
		$uniqueHashes = [];
		$uniqueHashes[] = CPullPush::getUniqueHash($userId, $appId);
		$uniqueHashes[] = CPullPush::getUniqueHash($userId, $appId."_bxdev");
		$aliases = $this->getAppIDAliases($appId);
		foreach ($aliases as $appId => $data)
		{
			$uniqueHashes[] = CPullPush::getUniqueHash($userId, $appId);
		}

		return array_unique($uniqueHashes);
	}

	private function getAppIDAliases($appId)
	{
		$aliases = [];
		if(self::$appAliases[$appId])
		{
			return self::$appAliases[$appId];
		}
		else
		{
			$events = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("pull", "onAppAliasGet");
			foreach ($events as $event)
			{
				$appAliases = ExecuteModuleEventEx($event, [$appId]);
				foreach ($appAliases as $key => $value)
				{
					$aliases[$key]= $value;
				}
			}

			self::$appAliases[$appId] = $aliases;
		}

		return $aliases;
	}

	protected function shouldSendMessage(&$message)
	{
		$delegates = \Bitrix\Main\EventManager::getInstance()->findEventHandlers("pull", "ShouldMessageBeSent");
		$shouldBeSent = true;
		foreach ($delegates as $delegate)
		{
			$shouldBeSent = ExecuteModuleEventEx($delegate, [$message]);
			if (!$shouldBeSent)
			{
				break;
			}
		}
		return $shouldBeSent;
	}

	/**
	 * @param array $arMessages
	 * @param array $arDevices
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function SendMessage($arMessages = [], $arDevices = [])
	{
		if (empty($arMessages))
		{
			return false;
		}

		$uniqueHashes = [];
		$arTmpMessages = [];
		$arVoipMessages = [];
		foreach ($arMessages as $message)
		{
			$shouldBeSent = $this->shouldSendMessage($message);

			if (!$message["USER_ID"] || !$shouldBeSent)
			{
				continue;
			}

			$uniqueHashes = array_merge($uniqueHashes, $this->getUniqueHashes($message["USER_ID"], $message["APP_ID"]));

			if($message["ADVANCED_PARAMS"]["isVoip"])
			{
				if (!array_key_exists("USER_" . $message["USER_ID"], $arVoipMessages))
				{
					$arVoipMessages["USER_" . $message["USER_ID"]] = [];
				}
				$arVoipMessages["USER_" . $message["USER_ID"]][] = htmlspecialcharsback($message);
			}
			else
			{
				if (!array_key_exists("USER_" . $message["USER_ID"], $arTmpMessages))
				{
					$arTmpMessages["USER_" . $message["USER_ID"]] = [];
				}
				$arTmpMessages["USER_" . $message["USER_ID"]][] = htmlspecialcharsback($message);
			}
		}
		if (empty($arDevices))
		{
			$arDevices = \Bitrix\Pull\Model\PushTable::getList([
				'filter' => [
					"=UNIQUE_HASH" => array_unique($uniqueHashes)
				]
			])->fetchAll();
		}

		$arPushMessages = [];

		foreach ($arDevices as $arDevice)
		{
			$arDevice["APP_ID"] = \Bitrix\Main\Config\Option::get("mobileapp", "app_id_replaced_".$arDevice["APP_ID"], $arDevice["APP_ID"]);
			$aliases = $this->getAppIDAliases($arDevice["APP_ID"]);
			$mode = "PRODUCTION";
			if (
				$aliases[$arDevice["APP_ID"]] && $aliases[$arDevice["APP_ID"]]["mode"] == "dev"
				|| mb_strpos($arDevice["APP_ID"], "_bxdev") > 0
			)
			{
				$mode = "SANDBOX";
			}

			$tmpMessage = $arTmpMessages["USER_" . $arDevice["USER_ID"]];
			$voipMessage = $arVoipMessages["USER_" . $arDevice["USER_ID"]];

			if(is_array($tmpMessage))
			{
				$deviceType = $arDevice["DEVICE_TYPE"];
				$deviceToken = $arDevice["DEVICE_TOKEN"];
				$filteredMessages = static::filterMessagesBeforeSend($tmpMessage, $deviceType, $deviceToken);
				if(isset(static::$pushServices[$deviceType]) && count($filteredMessages) > 0)
				{
					$arPushMessages[$deviceType][$deviceToken] = [
						"messages" => $filteredMessages,
						"mode" => $mode
					];
				}
			}
			if(is_array($voipMessage))
			{
				$deviceType = $arDevice["VOIP_TYPE"] && $arDevice["VOIP_TOKEN"] ? $arDevice["VOIP_TYPE"]: $arDevice["DEVICE_TYPE"];
				$deviceToken = $arDevice["VOIP_TYPE"] && $arDevice["VOIP_TOKEN"] ? $arDevice["VOIP_TOKEN"] : $arDevice["DEVICE_TOKEN"];
				$filteredMessages = static::filterMessagesBeforeSend($voipMessage, $deviceType, $deviceToken);
				if(isset(static::$pushServices[$deviceType]) && count($filteredMessages) > 0)
				{
					$arPushMessages[$deviceType][$deviceToken] = [
						"messages" => $filteredMessages,
						"mode" => $mode
					];
				}
			}
		}

		if (empty($arPushMessages))
		{
			return false;
		}

		$batches = [];

		/**
		 * @var CPushService $obPush
		 */
		$batchMessageCount = CPullOptions::GetPushMessagePerHit();
		$useChunks = ($batchMessageCount > 0);
		if(!$useChunks)
		{
			$batches[0] = "";
		}
		foreach (static::$pushServices as $serviceID => $serviceFields)
		{
			$className = $serviceFields["CLASS"];
			if (!$arPushMessages[$serviceID])
			{
				continue;
			}
			if (!class_exists($className) || !method_exists($className, "getBatch"))
			{
				continue;
			}

			$obPush = new $className;

			if(!$useChunks)
			{
				$batches[0] .= $obPush->getBatch($arPushMessages[$serviceID]);
			}
			else
			{
				$offset = 0;
				$messages = null;
				while($messages = array_slice($arPushMessages[$serviceID],$offset, $batchMessageCount))
				{
					$batches[] = $obPush->getBatch($messages);
					$offset += count($messages);
				}
			}
		}

		foreach ($batches as $chunkBatch)
		{
			$this->sendBatch($chunkBatch);
		}

		return true;
	}

	public function sendBatch($batch)
	{
 		require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/update_client.php");
		$key = CUpdateClient::GetLicenseKey();
		if ($key <> '' && $batch <> '')
		{
			$postData = [
				"Action" => "SendMessage",
				"MessageBody" => $batch
			];
			$httpClient = new \Bitrix\Main\Web\HttpClient(["waitResponse" => true]);
			$httpClient->query("POST", $this->remoteProviderUrl . "?key=" . md5($key), $postData);

			return true;
		}

		return false;
	}

	protected static function filterMessagesBeforeSend(array $messages, string $deviceType, string $deviceToken)
	{
		foreach ($messages as $k => $message)
		{
			if (isset($message['ADVANCED_PARAMS']['filterCallback']) && is_callable($message['ADVANCED_PARAMS']['filterCallback']))
			{
				$filterResult = call_user_func_array(
					$message['ADVANCED_PARAMS']['filterCallback'],
					[
						'message' => $message,
						'deviceType' => $deviceType,
						'deviceToken' => $deviceToken
					]
				);
				if (!$filterResult)
				{
					unset($messages[$k]);
				}
				else
				{
					unset($messages[$k]['ADVANCED_PARAMS']['filterCallback']);
				}
			}
		}
		return $messages;
	}

	public static function DeleteFromQueueBySubTag($userId, $tag, $appId = self::DEFAULT_APP_ID)
	{
		global $DB;
		if ($tag == '' || intval($userId) == 0)
		{
			return false;
		}

		$strSql = "DELETE FROM b_pull_push_queue WHERE USER_ID = " . intval($userId) . " AND SUB_TAG = '" . $DB->ForSQL($tag) . "'";
		$DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

		\Bitrix\Pull\Push::add($userId, [
			'module_id' => 'pull',
			'push' => [
				'advanced_params' => [
					"notificationsToCancel" => [$tag],
				],
				'send_immediately' => 'Y',
				'app_id' => $appId
			]
		]);

		return true;
	}

	public static function SendAgent()
	{
		global $DB;

		if (!CPullOptions::GetPushStatus())
		{
			return false;
		}

		$count = 0;
		$maxId = 0;
		$pushLimit = 10;
		$arPush = [];

		$sqlDate = "";
		if ($DB->type == "MYSQL")
		{
			$sqlDate = " WHERE DATE_CREATE < DATE_SUB(NOW(), INTERVAL 15 SECOND) ";
		}
		else
		{
			if ($DB->type == "MSSQL")
			{
				$sqlDate = " WHERE DATE_CREATE < dateadd(SECOND, -15, getdate()) ";
			}
			else
			{
				if ($DB->type == "ORACLE")
				{
					$sqlDate = " WHERE DATE_CREATE < SYSDATE-(1/24/60/60*15) ";
				}
			}
		}

		$strSql = $DB->TopSql("SELECT ID, USER_ID, MESSAGE, PARAMS, ADVANCED_PARAMS, BADGE, APP_ID FROM b_pull_push_queue" . $sqlDate, 280);
		$dbRes = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
		while ($arRes = $dbRes->Fetch())
		{
			if ($arRes['BADGE'] == '')
			{
				$arRes['BADGE'] = \Bitrix\Pull\MobileCounter::get($arRes['USER_ID']);
			}

			try
			{
				$arRes['PARAMS'] = $arRes['PARAMS'] ? Bitrix\Main\Web\Json::decode($arRes['PARAMS']) : "";
			}
			catch (Exception $e)
			{
				$arRes['PARAMS'] = "";
			}
			if (is_array($arRes['PARAMS']))
			{
				if (isset($arRes['PARAMS']['CATEGORY']))
				{
					$arRes['CATEGORY'] = $arRes['PARAMS']['CATEGORY'];
					unset($arRes['PARAMS']['CATEGORY']);
				}
				$arRes['PARAMS'] = Bitrix\Main\Web\Json::encode($arRes['PARAMS']);
			}
			try
			{
				$arRes['ADVANCED_PARAMS'] = $arRes['ADVANCED_PARAMS'] <> '' ? Bitrix\Main\Web\Json::decode($arRes['ADVANCED_PARAMS']) : [];
			}
			catch (Exception $e)
			{
				$arRes['ADVANCED_PARAMS'] = [];
			}

			$arPush[$count][] = $arRes;
			if ($pushLimit <= count($arPush[$count]))
			{
				$count++;
			}

			$maxId = $maxId < $arRes['ID'] ? $arRes['ID'] : $maxId;
		}

		if ($maxId > 0)
		{
			$strSql = "DELETE FROM b_pull_push_queue WHERE ID <= " . $maxId;
			$DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
		}

		$CPushManager = new CPushManager();
		foreach ($arPush as $arStack)
		{
			$CPushManager->SendMessage($arStack);
		}

		$strSql = "SELECT COUNT(ID) CNT FROM b_pull_push_queue";
		$dbRes = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			global $pPERIOD;
			if ($arRes['CNT'] > 280)
			{
				$pPERIOD = 10;
				return "CPushManager::SendAgent();";
			}
			else
			{
				if ($arRes['CNT'] > 0)
				{
					$pPERIOD = 30;
					return "CPushManager::SendAgent();";
				}
			}
		}

		return false;
	}

	public function getServices()
	{
		return self::$pushServices;
	}

	public function sendBadges($userId = null, $appId = self::DEFAULT_APP_ID)
	{
		return \Bitrix\Pull\MobileCounter::send($userId, $appId);
	}
}
?>