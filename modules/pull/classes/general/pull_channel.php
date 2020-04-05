<?
use Bitrix\Main\Security\Sign;
IncludeModuleLangFile(__FILE__);

class CPullChannel
{
	const TYPE_PRIVATE = 'private';
	const TYPE_SHARED = 'shared';

	public static function GetNewChannelId()
	{
		global $APPLICATION;
		return md5(uniqid().$_SERVER["REMOTE_ADDR"].$_SERVER["SERVER_NAME"].(is_object($APPLICATION)? $APPLICATION->GetServerUniqID(): ''));
	}

	public static function GetChannelShared($channelType = self::TYPE_SHARED, $cache = true, $reOpen = false)
	{
		return self::GetShared($cache, $reOpen, $channelType);
	}

	public static function GetShared($cache = true, $reOpen = false, $channelType = self::TYPE_SHARED)
	{
		return self::Get(0, $cache, $reOpen, $channelType);
	}

	public static function GetChannel($userId, $channelType = self::TYPE_PRIVATE, $cache = true, $reOpen = false)
	{
		return self::Get($userId, $cache, $reOpen, $channelType);
	}

	public static function Get($userId, $cache = true, $reOpen = false, $channelType = self::TYPE_PRIVATE)
	{
		global $DB, $CACHE_MANAGER;

		$nginxStatus = CPullOptions::GetQueueServerStatus();

		$arResult = false;
		$userId = intval($userId);
		$cache_id="b_pchc_".$userId.'_'.$channelType;

		if ($nginxStatus && $cache)
		{
			$res = $CACHE_MANAGER->Read(43200, $cache_id, "b_pull_channel");
			if ($res)
				$arResult = $CACHE_MANAGER->Get($cache_id);
		}
		if(!is_array($arResult) || !isset($arResult['CHANNEL_ID']))
		{
			$arResult = Array();
			CTimeZone::Disable();
			$strSql = "
					SELECT C.CHANNEL_ID, C.CHANNEL_TYPE, ".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." DATE_CREATE, C.LAST_ID
					FROM b_pull_channel C
					WHERE C.USER_ID = ".$userId." AND C.CHANNEL_TYPE = '".$DB->ForSQL($channelType)."'
			";
			CTimeZone::Enable();
			$res = $DB->Query($strSql);
			if ($arResult = $res->Fetch())
			{
				if ($nginxStatus)
				{
					self::SaveToCache($cache_id, $arResult);
				}
			}
		}
		if (empty($arResult) || intval($arResult['DATE_CREATE'])+43200 < time())
		{
			$arChannel = Array(
				'CHANNEL_ID' => self::GetNewChannelId(),
				'CHANNEL_TYPE' => $channelType,
				'DATE_CREATE' => time(),
				'LAST_ID' => 0,
			);
			self::SaveToCache($cache_id, $arChannel);

			if (isset($arResult['CHANNEL_ID']))
			{
				$strSql = "DELETE FROM b_pull_channel WHERE CHANNEL_ID = '".$DB->ForSQL($arResult['CHANNEL_ID'])."'";
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}

			$channelId = self::Add($userId, $arChannel['CHANNEL_ID'], $arChannel['CHANNEL_TYPE']);

			if (isset($arResult['CHANNEL_ID']) && $channelId != $arResult['CHANNEL_ID'])
			{
				$params = Array(
					'action' => $channelType != self::TYPE_PRIVATE? 'reconnect': 'get_config',
					'channel' => Array(
						'id' => self::SignChannel($arResult['CHANNEL_ID']),
						'type' => $channelType,
					),
				);
				if ($channelType != self::TYPE_PRIVATE)
				{
					$params['new_channel'] = Array(
						'id' => self::SignChannel($channelId),
						'start' => date('c', time()),
						'end' => date('c', time()+43205),
						'type' => $channelType,
					);
				}
				$arMessage = Array(
					'module_id' => 'pull',
					'command' => 'channel_expire',
					'params' => $params
				);
				CPullStack::AddByChannel($arResult['CHANNEL_ID'], $arMessage);
			}

			return $channelId? Array(
				'CHANNEL_ID' => $channelId,
				'CHANNEL_TYPE' => $channelType,
				'CHANNEL_DT' => time(),
				'LAST_ID' => 0,
			): false;
		}
		else
		{
			if ($nginxStatus && $reOpen && CPullOptions::GetQueueServerVersion() < 3)
			{
				self::Send($arResult['CHANNEL_ID'], \Bitrix\Pull\Common::jsonEncode(Array(
					'module_id' => 'pull',
					'command' => 'reopen',
					'expiry' => 1,
					'params' => Array(),
					'extra' => Array(
						'server_time' => date('c'),
						'server_name' => COption::GetOptionString('main', 'server_name', $_SERVER['SERVER_NAME']),
						'revision_web' => PULL_REVISION_WEB,
						'revision_mobile' => PULL_REVISION_MOBILE,
					),
				)));
			}
			return Array(
				'CHANNEL_ID' => $arResult['CHANNEL_ID'],
				'CHANNEL_TYPE' => $arResult['CHANNEL_TYPE'],
				'CHANNEL_DT' => $arResult['DATE_CREATE'],
				'LAST_ID' => $arResult['LAST_ID'],
			);
		}
	}

	public static function SignChannel($channelId)
	{
		$signatureKey = \CPullOptions::GetSignatureKey();
		if ($signatureKey === "" || !is_string($channelId))
		{
			return $channelId;
		}

		$signatureAlgo = \CPullOptions::GetSignatureAlgorithm();
		$hmac = new Sign\HmacAlgorithm();
		$hmac->setHashAlgorithm($signatureAlgo);
		$signer = new Sign\Signer($hmac);
		$signer->setKey($signatureKey);

		return $signer->sign($channelId);
	}

	// create a channel for the user
	public static function Add($userId, $channelId = null, $channelType = self::TYPE_PRIVATE)
	{
		global $DB;

		$userId = intval($userId);
		$cache_id="b_pchc_".$userId."_".$channelType;

		if ($userId)
		{
			$user = \Bitrix\Main\UserTable::getById($userId)->fetch();
			if ($user['ACTIVE'] == 'N')
			{
				return false;
			}
		}

		$channelId = is_null($channelId)? self::GetNewChannelId(): $channelId;
		$arParams = Array(
			'USER_ID' => intval($userId),
			'CHANNEL_ID' => $channelId,
			'CHANNEL_TYPE' => $channelType,
			'LAST_ID' => 0,
			'~DATE_CREATE' => $DB->CurrentTimeFunction(),
		);
		$result = intval($DB->Add("b_pull_channel", $arParams, Array(), "", true));
		if ($result > 0)
		{
			$arChannel = Array(
				'CHANNEL_ID' => $channelId,
				'CHANNEL_TYPE' => $channelType,
				'DATE_CREATE' => time(),
				'LAST_ID' => 0,
			);
			self::SaveToCache($cache_id, $arChannel);

			if (CPullOptions::GetQueueServerStatus() && CPullOptions::GetQueueServerVersion() < 3)
			{
				self::Send($channelId, \Bitrix\Pull\Common::jsonEncode(Array(
					'module_id' => 'pull',
					'command' => 'open',
					'expiry' => 1,
					'params' => Array(),
					'extra' => Array(
						'server_time' => date('c'),
						'server_time_unix' => microtime(true),
						'server_name' => COption::GetOptionString('main', 'server_name', $_SERVER['SERVER_NAME']),
						'revision_web' => PULL_REVISION_WEB,
						'revision_mobile' => PULL_REVISION_MOBILE,
					),
				)));
			}
		}
		else
		{
			CTimeZone::Disable();
			$strSql = "
					SELECT CHANNEL_ID, ".$DB->DatetimeToTimestampFunction('DATE_CREATE')." DATE_CREATE, LAST_ID
					FROM b_pull_channel
					WHERE USER_ID = ".$userId." AND CHANNEL_TYPE = '".$DB->ForSQL($channelType)."'
			";
			CTimeZone::Enable();
			$res = $DB->Query($strSql);
			$arChannel = $res->Fetch();
			$channelId = $arChannel['CHANNEL_ID'];
			self::SaveToCache($cache_id, $arChannel);

			if (CPullOptions::GetQueueServerStatus() && CPullOptions::GetQueueServerVersion() < 3)
			{
				self::Send($channelId, \Bitrix\Pull\Common::jsonEncode(Array(
					'module_id' => 'pull',
					'command' => 'open_exists',
					'expiry' => 1,
					'params' => Array(),
					'extra' => Array(
						'server_time' => date('c'),
						'server_time_unix' => microtime(true),
						'server_name' => COption::GetOptionString('main', 'server_name', $_SERVER['SERVER_NAME']),
						'revision_web' => PULL_REVISION_WEB,
						'revision_mobile' => PULL_REVISION_MOBILE,
					),
				)));
			}
		}

		return $channelId;
	}

	// remove channel by identifier
	// before removing need to send a message to change channel
	public static function Delete($channelId)
	{
		global $DB, $CACHE_MANAGER;

		$strSql = "SELECT ID, USER_ID, CHANNEL_TYPE FROM b_pull_channel WHERE CHANNEL_ID = '".$DB->ForSQL($channelId)."'";
		$res = $DB->Query($strSql);
		if ($arRes = $res->Fetch())
		{
			$strSql = "DELETE FROM b_pull_channel WHERE USER_ID = ".$arRes['USER_ID']." AND CHANNEL_TYPE = '".$DB->ForSql($arRes['CHANNEL_TYPE'])."'";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$CACHE_MANAGER->Clean("b_pchc_".$arRes['USER_ID']."_".$arRes['CHANNEL_TYPE'], "b_pull_channel");

			$channelType = $arRes['CHANNEL_TYPE'];

			$params = Array(
				'action' => $channelType != self::TYPE_PRIVATE? 'reconnect': 'get_config',
				'channel' => Array(
					'id' => self::SignChannel($channelId),
					'type' => $channelType,
				),
			);
			if ($channelType != self::TYPE_PRIVATE)
			{
				$result = self::GetShared(false);
				$params['new_channel'] = Array(
					'id' => self::SignChannel($result['CHANNEL_ID']),
					'start' => $result['CHANNEL_DT'],
					'end' => date('c', $result['CHANNEL_DT']+43205),
					'type' => $channelType,
				);
			}
			$arMessage = Array(
				'module_id' => 'pull',
				'command' => 'channel_expire',
				'params' => $params
			);
			CPullStack::AddByChannel($channelId, $arMessage);
		}

		return true;
	}

	public static function DeleteByUser($userId, $channelId = null, $channelType = self::TYPE_PRIVATE)
	{
		global $DB, $CACHE_MANAGER;

		$userId = intval($userId);
		if ($userId == 0 && $channelType == self::TYPE_PRIVATE)
		{
			$channelType = self::TYPE_SHARED;
		}

		if (is_null($channelId))
		{
			$strSql = "SELECT CHANNEL_ID, CHANNEL_TYPE FROM b_pull_channel WHERE USER_ID = ".$userId." AND CHANNEL_TYPE = '".$DB->ForSQL($channelType)."'";
			$res = $DB->Query($strSql);
			if ($arRes = $res->Fetch())
			{
				$channelId = $arRes['CHANNEL_ID'];
				$channelType = $arRes['CHANNEL_TYPE'];
			}
		}

		if (strlen($channelType) <= 0)
			$channelTypeSql = "(CHANNEL_TYPE = '' OR CHANNEL_TYPE IS NULL)";
		else
			$channelTypeSql = "CHANNEL_TYPE = '".$DB->ForSQL($channelType)."'";

		$strSql = "DELETE FROM b_pull_channel WHERE USER_ID = ".$userId." AND ".$channelTypeSql;
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$CACHE_MANAGER->Clean("b_pchc_".$userId."_".$channelType, "b_pull_channel");

		$params = Array(
			'action' => $channelType != self::TYPE_PRIVATE? 'reconnect': 'get_config',
			'channel' => Array(
				'id' => self::SignChannel($channelId),
				'type' => $channelType,
			),
		);
		if ($channelType != self::TYPE_PRIVATE)
		{
			$result = self::GetShared(false);
			$params['new_channel'] = Array(
				'id' => self::SignChannel($result['CHANNEL_ID']),
				'start' => $result['CHANNEL_DT'],
				'end' => date('c', $result['CHANNEL_DT']+43205),
				'type' => $channelType,
			);
		}
		$arMessage = Array(
			'module_id' => 'pull',
			'command' => 'channel_expire',
			'params' => $params
		);

		CPullStack::AddByChannel($channelId, $arMessage);

		return true;
	}

	public static function Send($channelId, $message, $options = array())
	{
		$result_start = '{"infos": ['; $result_end = ']}';
		if (is_array($channelId) && CPullOptions::GetQueueServerVersion() == 1)
		{
			$results = Array();
			foreach ($channelId as $channel)
			{
				$results[] = self::SendCommand($channel, $message, $options);
			}
			$result = json_decode($result_start.implode(',', $results).$result_end);
		}
		else if (is_array($channelId))
		{
			$commandPerHit = CPullOptions::GetCommandPerHit();
			if (count($channelId) > $commandPerHit)
			{
				$arGroup = Array();
				$i = 0;
				foreach($channelId as $channel)
				{
					if (count($arGroup[$i]) == $commandPerHit)
						$i++;

					$arGroup[$i][] = $channel;
				}
				$results = Array();
				foreach($arGroup as $channels)
				{
					$result = self::SendCommand($channels, $message, $options);
					$subresult = json_decode($result);
					if (is_array($subresult->infos))
					{
						$results = array_merge($results, $subresult->infos);
					}
				}
				$result = json_decode('{"infos":'.json_encode($results).'}');
			}
			else
			{
				$result = self::SendCommand($channelId, $message, $options);
				$result = json_decode($result);
			}
		}
		else
		{
			$result = self::SendCommand($channelId, $message, $options);
			$result = json_decode($result_start.$result.$result_end);
		}

		return $result;
	}

	private static function SendCommand($channelId, $message, $options = array())
	{
		if (!is_array($channelId))
			$channelId = Array($channelId);

		$channelId = implode('/', array_unique($channelId));

		if (strlen($channelId) <=0 || strlen($message) <= 0)
			return false;

		$defaultOptions = array(
			"method" => "POST",
			"timeout" => 5,
			"dont_wait_answer" => true
		);

		$options = array_merge($defaultOptions, $options);

		if (!in_array($options["method"], Array('POST', 'GET')))
			return false;

		$nginx_error = COption::GetOptionString("pull", "nginx_error", "N");
		if ($nginx_error != "N")
		{
			$nginx_error = unserialize($nginx_error);
			if (intval($nginx_error['date'])+120 < time())
			{
				COption::SetOptionString("pull", "nginx_error", "N");
				CAdminNotify::DeleteByTag("PULL_ERROR_SEND");
				$nginx_error = "N";
			}
			else if ($nginx_error['count'] >= 10)
			{
				$ar = Array(
					"MESSAGE" => GetMessage('PULL_ERROR_SEND'),
					"TAG" => "PULL_ERROR_SEND",
					"MODULE_ID" => "pull",
				);
				CAdminNotify::Add($ar);
				return false;
			}
		}

		$postdata = CHTTP::PrepareData($message);

		$CHTTP = new CHTTP();
		$CHTTP->http_timeout = intval($options["timeout"]);
		if (isset($options["expiry"]))
		{
			$CHTTP->SetAdditionalHeaders(array("Message-Expiry" => intval($options["expiry"])));
		}

		$arUrl = $CHTTP->ParseURL(CPullOptions::GetPublishUrl($channelId), false);
		try
		{
			$sendResult = $CHTTP->Query($options["method"], $arUrl['host'], $arUrl['port'], $arUrl['path_query'], $postdata, $arUrl['proto'], 'N', $options["dont_wait_answer"]);
		}
		catch(Exception $e)
		{
			$sendResult = false;
		}

		if ($sendResult)
		{
			$result = $options["dont_wait_answer"] ? '{}': $CHTTP->result;
		}
		else
		{
			if ($nginx_error == "N")
			{
				$nginx_error = Array(
					'count' => 1,
					'date' => time(),
					'date_increment' => time(),
				);
			}
			else if (intval($nginx_error['date_increment'])+1 < time())
			{
				$nginx_error['count'] = intval($nginx_error['count'])+1;
				$nginx_error['date_increment'] = time();
			}
			COption::SetOptionString("pull", "nginx_error", serialize($nginx_error));
			$result = false;
		}

		return $result;
	}

	public static function SaveToCache($cacheId, $data)
	{
		global $CACHE_MANAGER;

		$CACHE_MANAGER->Clean($cacheId, "b_pull_channel");
		$CACHE_MANAGER->Read(43200, $cacheId, "b_pull_channel");
		$CACHE_MANAGER->SetImmediate($cacheId, $data);
	}

	public static function UpdateLastId($channelId, $lastId)
	{
		global $DB;

		$strSql = "UPDATE b_pull_channel SET LAST_ID = ".intval($lastId)." WHERE CHANNEL_ID = '".$DB->ForSQL($channelId)."'";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return true;
	}

	// check channels that are older than 12 hours, remove them.
	public static function CheckExpireAgent()
	{
		global $DB;
		$sqlDateFunction = null;
		$dbType = strtolower($DB->type);
		if ($dbType== "mysql")
			$sqlDateFunction = "DATE_SUB(NOW(), INTERVAL 13 HOUR)";
		else if ($dbType == "mssql")
			$sqlDateFunction = "dateadd(HOUR, -13, getdate())";
		else if ($dbType == "oracle")
			$sqlDateFunction = "SYSDATE-1/13";

		if (!is_null($sqlDateFunction))
		{
			$strSql = "
					SELECT USER_ID, CHANNEL_ID, CHANNEL_TYPE
					FROM b_pull_channel
					WHERE DATE_CREATE < ".$sqlDateFunction;
			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while ($arRes = $dbRes->Fetch())
			{
				self::DeleteByUser($arRes['USER_ID'], $arRes['CHANNEL_ID'], $arRes['CHANNEL_TYPE']);
			}
		}

		return "CPullChannel::CheckExpireAgent();";
	}

	public static function CheckOnlineChannel()
	{
		if (!CPullOptions::GetQueueServerStatus())
			return "CPullChannel::CheckOnlineChannel();";

		$users = Array();
		$channels = Array();

		$isImInstalled = CModule::IncludeModule('im');
		if ($isImInstalled)
		{
			$orm = \Bitrix\Main\UserTable::getList(array(
				'select' => Array(
					'USER_ID' => 'ID',
					'CHANNEL_ID' =>	'CHANNEL.CHANNEL_ID',
					'STATUS' =>	'ST.STATUS',
					'COLOR' =>	'ST.COLOR',
					'IDLE' => 'ST.IDLE',
					'MOBILE_LAST_DATE' => 'ST.MOBILE_LAST_DATE',
				 ),
				'runtime' => Array(
					new \Bitrix\Main\Entity\ReferenceField(
						'CHANNEL',
						'\Bitrix\Pull\Model\ChannelTable',
						array(
							"=ref.USER_ID" => "this.ID",
							"=ref.CHANNEL_TYPE" => new \Bitrix\Main\DB\SqlExpression('?s', 'private'),
						),
						array("join_type"=>"LEFT")
					),
					new \Bitrix\Main\Entity\ReferenceField(
						'ST',
						'\Bitrix\Im\Model\StatusTable',
						array("=ref.USER_ID" => "this.ID"),
						array("join_type"=>"LEFT")
					),
				),
				'filter' => Array(
					'=IS_ONLINE' => 'Y',
					'=IS_REAL_USER' => 'Y'
				)
			));
		}
		else
		{
			$orm = \Bitrix\Main\UserTable::getList(array(
				'select' => Array(
					'USER_ID' => 'ID',
					'CHANNEL_ID' =>	'CHANNEL.CHANNEL_ID'
				 ),
				'runtime' => Array(
					new \Bitrix\Main\Entity\ReferenceField(
						'CHANNEL',
						'\Bitrix\Pull\Model\ChannelTable',
						array(
							"=ref.USER_ID" => "this.ID",
							"=ref.CHANNEL_TYPE" => new \Bitrix\Main\DB\SqlExpression('?s', 'private'),
						),
						array("join_type"=>"LEFT")
					)
				),
				'filter' => Array(
					'=IS_ONLINE' => 'Y',
					'=IS_REAL_USER' => 'Y'
				)
			));
		}

		while ($res = $orm->fetch())
		{
			$channels[$res['CHANNEL_ID']] = $res['USER_ID'];
			$users[$res['USER_ID']] = $res;
		}

		if (count($users) > 0)
		{
			$arOnline = Array();

			global $USER;
			$agentUserId = 0;
			if (is_object($USER) && $USER->GetId() > 0)
			{
				$agentUserId = $USER->GetId();
				$arOnline[$agentUserId] = $agentUserId;
			}

			$options = array(
				"method" => "GET",
				"dont_wait_answer" => false
			);
			$result = self::Send(array_keys($channels), 'ping', $options);
			if (is_object($result) && isset($result->infos))
			{
				foreach ($result->infos as $info)
				{
					$userId = $channels[$info->channel];
					if ($userId == 0 || $agentUserId == $userId)
						continue;

					if ($info->subscribers > 0)
						$arOnline[$userId] = $userId;
				}
			}

			if (count($arOnline) > 0)
			{
				ksort($arOnline);
				CUser::SetLastActivityDateByArray($arOnline);
			}

			$arSend = Array();
			if ($isImInstalled)
			{
				foreach ($arOnline as $userId)
				{
					$arSend[$userId] = Array(
						'id' => $userId,
						'status' => $users[$userId]['STATUS'],
						'color' => $users[$userId]['COLOR']? \Bitrix\Im\Color::getColor($users[$userId]['COLOR']): '',
						'idle' => $users[$userId]['IDLE'] instanceof \Bitrix\Main\Type\DateTime? $users[$userId]['IDLE']: false,
						'mobile_last_date' => $users[$userId]['MOBILE_LAST_DATE'] instanceof \Bitrix\Main\Type\DateTime? $users[$userId]['MOBILE_LAST_DATE']: false,
						'last_activity_date' => new \Bitrix\Main\Type\DateTime(),
					);
				}
			}
			else
			{
				foreach ($arOnline as $userId)
				{
					$arSend[$userId] = Array(
						'id' => $userId,
						'status' => 'online',
						'color' => '',
						'idle' => false,
						'mobile_last_date' => false,
						'last_activity_date' => new \Bitrix\Main\Type\DateTime(),
					);
				}
			}

			CPullStack::AddShared(Array(
				'module_id' => 'online',
				'command' => 'list',
				'expiry' => 240,
				'params' => Array(
					'users' => $arSend
				),
			));
		}

		return "CPullChannel::CheckOnlineChannel();";
	}

	public static function GetConfig($userId, $cache = true, $reopen = false, $mobile = false)
	{
		$pullConfig = Array();

		if (defined('BX_PULL_SKIP_LS'))
			$pullConfig['LOCAL_STORAGE'] = 'N';

		if (IsModuleInstalled('bitrix24'))
			$pullConfig['BITRIX24'] = 'Y';

		if (!CPullOptions::GetQueueServerHeaders())
			$pullConfig['HEADERS'] = 'N';

		$arChannel = CPullChannel::Get($userId, $cache, $reopen);
		if (!is_array($arChannel))
		{
			return false;
		}

		$arChannel["CHANNEL_ID"] = self::SignChannel($arChannel["CHANNEL_ID"]);
		$nginxStatus = CPullOptions::GetQueueServerStatus();
		$webSocketStatus = false;

		$arChannels = Array($arChannel['CHANNEL_ID']);
		if ($nginxStatus)
		{
			if (defined('BX_PULL_SKIP_WEBSOCKET'))
			{
				$pullConfig['WEBSOCKET'] = 'N';
			}
			else
			{
				$webSocketStatus = CPullOptions::GetWebSocketStatus();
			}

			if ($userId > 0)
			{
				$arChannelShared = CPullChannel::GetShared($cache, $reopen);
				if (is_array($arChannelShared))
				{
					$arChannelShared["CHANNEL_ID"] = self::SignChannel($arChannelShared["CHANNEL_ID"]);
					$arChannels[] = $arChannelShared['CHANNEL_ID'];
					$arChannel['CHANNEL_DT'] = $arChannel['CHANNEL_DT'].'/'.$arChannelShared['CHANNEL_DT'];
				}
			}
		}

		$pullPath = ($nginxStatus? (CMain::IsHTTPS()? CPullOptions::GetListenSecureUrl($arChannels): CPullOptions::GetListenUrl($arChannels)): '/bitrix/components/bitrix/pull.request/ajax.php?UPDATE_STATE');
		$pullPathWs = ($nginxStatus && $webSocketStatus? (CMain::IsHTTPS()? CPullOptions::GetWebSocketSecureUrl($arChannels): CPullOptions::GetWebSocketUrl($arChannels)): '');

		return $pullConfig+Array(
			'CHANNEL_ID' => implode('/', $arChannels),
			'CHANNEL_DT' => $arChannel['CHANNEL_DT'],
			'USER_ID' => $userId,
			'LAST_ID' => $arChannel['LAST_ID'],
			'PATH' => $pullPath,
			'PATH_WS' => $pullPathWs,
			'PATH_COMMAND' => defined('BX_PULL_COMMAND_PATH')? BX_PULL_COMMAND_PATH: '',
			'METHOD' => ($nginxStatus? 'LONG': 'PULL'),
			'REVISION' => PULL_REVISION_WEB,
			'ERROR' => '',
		);
	}
}
?>
