<?php

use Bitrix\Main\Security\Sign;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UserTable;

class CPullChannel
{
	const TYPE_PRIVATE = 'private';
	const TYPE_SHARED = 'shared';

	const CHANNEL_TTL = 43205;

	private const CACHE_TABLE = "b_pull_channel";

	public static function GetNewChannelId($suffix = '')
	{
		global $APPLICATION;
		return md5(uniqid().$_SERVER["REMOTE_ADDR"].$_SERVER["SERVER_NAME"].(is_object($APPLICATION)? $APPLICATION->GetServerUniqID(): '').$suffix);
	}

	public static function GetNewChannelIdByTag(string $tag, string $suffix = '')
	{
		global $APPLICATION;
		return md5($tag.(is_object($APPLICATION)? $APPLICATION->GetServerUniqID(): '').$suffix);
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

	public static function Get(int $userId, $cache = true, $reOpen = false, $channelType = self::TYPE_PRIVATE)
	{
		if (!CPullOptions::GetQueueServerStatus())
		{
			return false;
		}
		$channelType = (string)$channelType ?: self::TYPE_PRIVATE;
		$lockId = self::getLockKey($userId, $channelType);

		$arResult = static::getInternal($userId, $channelType);
		if ($arResult && intval($arResult['DATE_CREATE']) + self::CHANNEL_TTL > time())
		{
			return [
				'CHANNEL_ID' => $arResult['CHANNEL_ID'],
				'CHANNEL_PUBLIC_ID' => $arResult['CHANNEL_PUBLIC_ID'],
				'CHANNEL_TYPE' => $arResult['CHANNEL_TYPE'],
				'CHANNEL_DT' => $arResult['DATE_CREATE'],
				'LAST_ID' => $arResult['LAST_ID'],
			];
		}

		$connection = \Bitrix\Main\Application::getConnection();
		if (!$connection->lock($lockId, 2))
		{
			trigger_error("Could not get lock for creating a new channel", E_USER_WARNING);

			return false;
		}

		// try reading once again, because DB state could be changed in a concurrent process
		$arResult = static::getInternal($userId, $channelType);
		if ($arResult && intval($arResult['DATE_CREATE']) + self::CHANNEL_TTL > time())
		{
			$connection->unlock($lockId);

			return [
				'CHANNEL_ID' => $arResult['CHANNEL_ID'],
				'CHANNEL_PUBLIC_ID' => $arResult['CHANNEL_PUBLIC_ID'],
				'CHANNEL_TYPE' => $arResult['CHANNEL_TYPE'],
				'CHANNEL_DT' => $arResult['DATE_CREATE'],
				'LAST_ID' => $arResult['LAST_ID'],
			];
		}

		if ($userId && !self::isUserActive($userId))
		{
			$connection->unlock($lockId);

			return false;
		}

		$channelId = self::GetNewChannelId();
		$publicChannelId = $userId>0? self::GetNewChannelId('public'): '';

		if ($arResult)
		{
			$result = self::Update($userId, $arResult['CHANNEL_ID'], $channelId, $publicChannelId, $channelType);
		}
		else
		{
			$result = self::Add($userId, $channelId, $publicChannelId, $channelType);
		}

		$connection->unlock($lockId);
		if (!$result->isSuccess())
		{
			return false;
		}

		if (isset($arResult['CHANNEL_ID']))
		{
			self::sendChannelExpired($userId, $channelType, $arResult['CHANNEL_ID'], $channelId);
		}

		return [
			'CHANNEL_ID' => $channelId,
			'CHANNEL_PUBLIC_ID' => $publicChannelId,
			'CHANNEL_TYPE' => $channelType,
			'CHANNEL_DT' => time(),
			'LAST_ID' => 0,
		];
	}

	private static function getInternal(int $userId, $channelType = self::TYPE_PRIVATE)
	{
		global $DB;

		$arResult = false;

		if(!is_array($arResult) || !isset($arResult['CHANNEL_ID']) || ($userId > 0 && !isset($arResult['CHANNEL_PUBLIC_ID'])))
		{
			CTimeZone::Disable();
			$strSql = "
					SELECT C.CHANNEL_ID, C.CHANNEL_PUBLIC_ID, C.CHANNEL_TYPE, ".$DB->DatetimeToTimestampFunction('C.DATE_CREATE')." AS DATE_CREATE, C.LAST_ID
					FROM b_pull_channel C
					WHERE C.USER_ID = ".$userId." AND C.CHANNEL_TYPE = '".$DB->ForSQL($channelType)."'
			";
			CTimeZone::Enable();
			$res = $DB->Query($strSql);
			$arResult = $res->Fetch();
		}

		return $arResult;
	}

	private static function isUserActive(int $userId): bool
	{
		$userData = UserTable::query()
			->setSelect(['ACTIVE'])
			->where('ID', $userId)
			->fetch()
		;

		return $userData && $userData['ACTIVE'] === 'Y';
	}

	public static function SignChannel($channelId)
	{
		$signatureKey = \Bitrix\Pull\Config::getSignatureKey();
		if (!is_string($channelId))
		{
			trigger_error("Channel ID must be the string", E_USER_WARNING);

			return $channelId;
		}
		if ($signatureKey === "")
		{
			return $channelId;
		}

		return $channelId.".".static::GetSignature($channelId);
	}

	public static function SignPublicChannel($channelId)
	{
		$signatureKey = \Bitrix\Pull\Config::getSignatureKey();
		if ($signatureKey === "" || !is_string($channelId))
		{
			return "";
		}

		return $channelId.".".static::GetPublicSignature($channelId);
	}

	public static function GetPublicSignature($value)
	{
		return static::GetSignature("public:".$value);
	}

	public static function GetSignature($value, $signatureKey = null)
	{
		if(!$signatureKey)
		{
			$signatureKey = \Bitrix\Pull\Config::getSignatureKey();
		}
		$signatureAlgo = \CPullOptions::GetSignatureAlgorithm();
		$hmac = new Sign\HmacAlgorithm();
		$hmac->setHashAlgorithm($signatureAlgo);
		$signer = new Sign\Signer($hmac);
		$signer->setKey($signatureKey);

		return $signer->getSignature($value);
	}

	// create a channel for the user
	public static function Add(int $userId, string $channelId, string $publicChannelId, string $channelType = self::TYPE_PRIVATE): \Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Result();

		$channelFields = [
			'USER_ID' => $userId,
			'CHANNEL_ID' => $channelId,
			'CHANNEL_PUBLIC_ID' => $publicChannelId,
			'CHANNEL_TYPE' => $channelType,
			'LAST_ID' => 0,
			'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
		];

		$insertResult = \Bitrix\Pull\ChannelTable::add($channelFields);
		if (!$insertResult->isSuccess())
		{
			$result->addErrors($insertResult->getErrors());
		}

		return $result;
	}

	private static function Update(int $userId, string $prevChannelId, string $channelId, string $publicChannelId, string $channelType = self::TYPE_PRIVATE) :\Bitrix\Main\Result
	{
		$result = new \Bitrix\Main\Result();
		$updateResult = \Bitrix\Pull\ChannelTable::updateByFilter(
			[
				'=USER_ID' => $userId,
				'=CHANNEL_ID' => $prevChannelId,
				'=CHANNEL_TYPE' => $channelType,
			],
			[
				'CHANNEL_ID' => $channelId,
				'CHANNEL_PUBLIC_ID' => $publicChannelId,
				'DATE_CREATE' => new \Bitrix\Main\Type\DateTime(),
			]
		);

		if (!$updateResult->isSuccess())
		{
			$result->addErrors($updateResult->getErrors());
		}
		else if ($updateResult->getAffectedRowsCount() != 1)
		{
			$result->addError(new \Bitrix\Main\Error("Expected to update 1 row; updated {$updateResult->getAffectedRowsCount()} rows"));
		}

		return $result;
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
			$DB->Query($strSql);

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
				if (!$result)
				{
					return true;
				}
				$params['new_channel'] = Array(
					'id' => self::SignChannel($result['CHANNEL_ID']),
					'start' => $result['CHANNEL_DT'],
					'end' => date('c', $result['CHANNEL_DT']+ self::CHANNEL_TTL),
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

		if ($channelType == '')
			$channelTypeSql = "(CHANNEL_TYPE = '' OR CHANNEL_TYPE IS NULL)";
		else
			$channelTypeSql = "CHANNEL_TYPE = '".$DB->ForSQL($channelType)."'";

		$strSql = "DELETE FROM b_pull_channel WHERE USER_ID = ".$userId." AND ".$channelTypeSql;
		$DB->Query($strSql);

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
			if (!$result)
			{
				return true;
			}
			$params['new_channel'] = Array(
				'id' => self::SignChannel($result['CHANNEL_ID']),
				'start' => $result['CHANNEL_DT'],
				'end' => date('c', $result['CHANNEL_DT']+ self::CHANNEL_TTL),
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
		if (is_array($channelId) && CPullOptions::GetQueueServerVersion() == 1 && !CPullOptions::IsServerShared())
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
					if (!isset($arGroup[$i]))
					{
						$arGroup[$i] = [];
					}
					if (count($arGroup[$i]) == $commandPerHit)
					{
						$i++;
					}

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
			if($result === false)
			{
				return $result;
			}
			$result = json_decode($result_start.$result.$result_end);
		}

		return $result;
	}

	private static function SendCommand($channelId, $message, $options = array())
	{
		if (!is_array($channelId))
			$channelId = Array($channelId);

		$channelId = implode('/', array_unique($channelId));

		if ($channelId == '' || $message == '')
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
			$nginx_error = unserialize($nginx_error, ["allowed_classes" => false]);
			if (intval($nginx_error['date'])+120 < time())
			{
				COption::SetOptionString("pull", "nginx_error", "N");
				CAdminNotify::DeleteByTag("PULL_ERROR_SEND");
				$nginx_error = "N";
			}
			else if ($nginx_error['count'] >= 10)
			{
				$ar = Array(
					"MESSAGE" => Loc::getMessage('PULL_ERROR_SEND'),
					"TAG" => "PULL_ERROR_SEND",
					"MODULE_ID" => "pull",
				);
				CAdminNotify::Add($ar);
				return false;
			}
		}

		$postdata = CHTTP::PrepareData($message);

		$httpClient = new \Bitrix\Main\Web\HttpClient([
			"socketTimeout" => (int)$options["timeout"],
			"streamTimeout" => (int)$options["timeout"],
			"waitResponse" => !$options["dont_wait_answer"]
		]);
		if (isset($options["expiry"]) && (int)$options["expiry"])
		{
			$httpClient->setHeader("Message-Expiry", (int)$options["expiry"]);
		}
		$url = \Bitrix\Pull\Config::getPublishUrl($channelId);
		if(CPullOptions::IsServerShared())
		{
			$signature = static::GetSignature($postdata);
			$url = \CHTTP::urlAddParams($url, ["signature" => $signature]);
		}

		$httpClient->disableSslVerification();//todo: remove

		$sendResult = $httpClient->query($options["method"], $url, $postdata);

		if ($sendResult)
		{
			$result = $options["dont_wait_answer"] ? '{}': $httpClient->getResult();
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

		$CACHE_MANAGER->Clean($cacheId, self::CACHE_TABLE);
		$CACHE_MANAGER->Read(self::CHANNEL_TTL, $cacheId, self::CACHE_TABLE);
		$CACHE_MANAGER->SetImmediate($cacheId, $data);
	}

	public static function UpdateLastId($channelId, $lastId)
	{
		global $DB;

		$strSql = "UPDATE b_pull_channel SET LAST_ID = ".intval($lastId)." WHERE CHANNEL_ID = '".$DB->ForSQL($channelId)."'";
		$DB->Query($strSql);

		return true;
	}

	// check channels that are older than 12 hours, remove them.
	public static function CheckExpireAgent()
	{
		global $DB;

		$connection = \Bitrix\Main\Application::getConnection();
		$sqlHelper = $connection->getSqlHelper();
		$sqlDateFunction = $sqlHelper->addSecondsToDateTime(-13 * 3600);

		$strSql = "
			SELECT USER_ID, CHANNEL_ID, CHANNEL_TYPE
			FROM b_pull_channel
			WHERE DATE_CREATE < {$sqlDateFunction}
		";
		$dbRes = $DB->Query($strSql);
		while ($arRes = $dbRes->Fetch())
		{
			$lockId = self::getLockKey((int)$arRes['USER_ID'], $arRes['CHANNEL_TYPE']);

			if ($connection->lock($lockId, 0))
			{
				self::DeleteByUser($arRes['USER_ID'], $arRes['CHANNEL_ID'], $arRes['CHANNEL_TYPE']);
				$connection->unlock($lockId);
			}
		}

		return __METHOD__. '();';
	}

	public static function CheckOnlineChannel()
	{
		if (!CPullOptions::GetQueueServerStatus())
		{
			return "CPullChannel::CheckOnlineChannel();";
		}

		$channels = Array();

		$orm = \Bitrix\Pull\ChannelTable::getList([
			'select' => [
				'USER_ID',
				'CHANNEL_ID'
			],
			'filter' => [
				'=CHANNEL_TYPE' => 'private',
				'=USER.IS_ONLINE' => 'Y',
				'=USER.IS_REAL_USER' => 'Y',
			]
		]);

		while ($res = $orm->fetch())
		{
			$channels[$res['CHANNEL_ID']] = $res['USER_ID'];
		}

		if (count($channels) == 0)
		{
			return "CPullChannel::CheckOnlineChannel();";
		}

		$arOnline = static::getOnlineUsers($channels);
		if (count($arOnline) > 0)
		{
			ksort($arOnline);
			CUser::SetLastActivityDateByArray($arOnline);
		}

		return "CPullChannel::CheckOnlineChannel();";
	}

	/**
	 * @param array $channels Maps channelId => userId
	 * @return array
	 */
	private static function getOnlineUsers(array $channels): array
	{
		$arOnline = [];

		global $USER;
		$agentUserId = 0;
		if (is_object($USER) && $USER->GetId() > 0)
		{
			$agentUserId = $USER->GetId();
			$arOnline[$agentUserId] = $agentUserId;
		}

		if (\Bitrix\Pull\Config::isJsonRpcUsed())
		{
			$userList = array_map("intval", array_values($channels));
			$result = (new \Bitrix\Pull\JsonRpcTransport())->getUsersLastSeen($userList);
			if (!$result->isSuccess())
			{
				return [];
			}
			foreach ($result->getData() as $userId => $lastSeen)
			{
				if ($lastSeen == 0)
				{
					$arOnline[$userId] = $userId;
				}
			}
		}
		else
		{
			if (\Bitrix\Pull\Config::isProtobufUsed())
			{
				$channelsStatus = \Bitrix\Pull\ProtobufTransport::getOnlineChannels(array_keys($channels));
			}
			else
			{
				$channelsStatus = self::GetOnlineChannels(array_keys($channels));
			}

			foreach ($channelsStatus as $channelId => $onlineStatus)
			{
				$userId = $channels[$channelId];
				if ($userId == 0 || $agentUserId == $userId)
				{
					continue;
				}

				if ($onlineStatus)
				{
					$arOnline[$userId] = $userId;
				}
			}
		}


		return $arOnline;
	}

	/**
	 * Deprecated method, use \Bitrix\Pull\Config::get() insted.
	 *
	 * @deprecated
	 * @see \Bitrix\Pull\Config::get()
	 */
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

		$arChannels = [];

		if (CPullOptions::GetQueueServerVersion() > 3)
		{
			if ($arChannel["CHANNEL_PUBLIC_ID"])
			{
				$arChannels[] = self::SignChannel($arChannel["CHANNEL_ID"].":".$arChannel["CHANNEL_PUBLIC_ID"]);
			}
			else
			{
				$arChannels[] = self::SignChannel($arChannel["CHANNEL_ID"]);
			}
		}
		else
		{
			$arChannels[] = self::SignChannel($arChannel["CHANNEL_ID"]);
		}

		$nginxStatus = CPullOptions::GetQueueServerStatus();
		$webSocketStatus = false;

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
					$arChannels[] = self::SignChannel($arChannelShared["CHANNEL_ID"]);
					$arChannel['CHANNEL_DT'] = $arChannel['CHANNEL_DT'].'/'.$arChannelShared['CHANNEL_DT'];
				}
			}
		}

		$pullPath = ($nginxStatus? (CMain::IsHTTPS()? CPullOptions::GetListenSecureUrl($arChannels): CPullOptions::GetListenUrl($arChannels)): '/bitrix/components/bitrix/pull.request/ajax.php?UPDATE_STATE');
		$pullPathWs = ($nginxStatus && $webSocketStatus? (CMain::IsHTTPS()? CPullOptions::GetWebSocketSecureUrl($arChannels): CPullOptions::GetWebSocketUrl($arChannels)): '');
		$pullPathPublish = ($nginxStatus && \CPullOptions::GetPublishWebEnabled()? (CMain::IsHTTPS()? CPullOptions::GetPublishWebSecureUrl($arChannels): CPullOptions::GetPublishWebUrl($arChannels)): '');

		return $pullConfig+Array(
			'CHANNEL_ID' => implode('/', $arChannels),
			'CHANNEL_PUBLIC_ID' => CPullOptions::GetQueueServerVersion() > 3 && $arChannel["CHANNEL_PUBLIC_ID"]? self::SignPublicChannel($arChannel["CHANNEL_PUBLIC_ID"]): '',
			'CHANNEL_DT' => $arChannel['CHANNEL_DT'],
			'USER_ID' => $userId,
			'LAST_ID' => $arChannel['LAST_ID'],
			'PATH' => $pullPath,
			'PATH_PUB' => $pullPathPublish,
			'PATH_WS' => $pullPathWs,
			'PATH_COMMAND' => defined('BX_PULL_COMMAND_PATH')? BX_PULL_COMMAND_PATH: '',
			'METHOD' => ($nginxStatus? 'LONG': 'PULL'),
			'REVISION' => PULL_REVISION_WEB,
			'ERROR' => '',
		);
	}

	public static function GetOnlineChannels(array $channels)
	{
		$options = array(
			"method" => "GET",
			"dont_wait_answer" => false
		);

		$command = implode('/', array_unique($channels));
		$serverResult = self::Send($channels, $command, $options);

		$result = [];

		if (is_object($serverResult) && isset($serverResult->infos))
		{
			foreach ($serverResult->infos as $info)
			{
				$result[$info->channel] = ($info->subscribers > 0);
			}
		}

		return $result;
	}

	private static function getLockKey(int $userId, $channelType): string
	{
		return "b_pchc_{$userId}_{$channelType}";
	}

	public static function sendChannelExpired(int $userId, string $channelType, string $oldChannelId, string $newChannelId): void
	{
		$params = [
			'action' => $channelType === self::TYPE_SHARED ? 'reconnect' : 'get_config',
			'channel' => [
				'id' => self::SignChannel($oldChannelId),
				'type' => $channelType,
			],
		];
		if ($userId == 0)
		{
			$params['new_channel'] = [
				'id' => self::SignChannel($newChannelId),
				'start' => date('c', time()),
				'end' => date('c', time() + self::CHANNEL_TTL),
				'type' => $channelType,
			];
		}
		$arMessage = [
			'module_id' => 'pull',
			'command' => 'channel_expire',
			'params' => $params
		];

		CPullStack::AddByChannel($oldChannelId, $arMessage);
	}
}
