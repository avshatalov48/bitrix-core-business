<?php
namespace Bitrix\Pull;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Security\Random;
use Bitrix\Pull\SharedServer\Client;


class Config
{
	public const ONE_YEAR = 31536000; //60 * 60 * 24 * 365;

	public static function get(array $params = [])
	{
		if (!\CPullOptions::GetQueueServerStatus())
			return false;

		$userId = (int)($params['USER_ID'] ?? 0);
		if (isset($params['CHANNEL']) && !($params['CHANNEL'] instanceof \Bitrix\Pull\Model\Channel))
		{
			throw new ArgumentException('$params["CHANNEL"] should be instance of \Bitrix\Pull\Model\Channel');
		}

		if ($userId === 0 && !isset($params['CHANNEL']))
		{
			global $USER;
			$userId = (int)$USER->GetID();
			if ($userId === 0)
			{
				return false;
			}
		}

		$params['CACHE'] = (bool)($params['CACHE'] ?? true);
		$params['REOPEN'] = (bool)($params['REOPEN'] ?? false);

		$cache = $params['CACHE'] !== false;
		$reopen = $params['REOPEN'] !== false;

		if ($userId !== 0)
		{
			$privateChannelType = $params['CUSTOM_TYPE'] ?? \CPullChannel::TYPE_PRIVATE;
			$privateChannel = \CPullChannel::Get($userId, $cache, $reopen, $privateChannelType);
			$sharedChannelType = $params['CUSTOM_TYPE'] ?? \CPullChannel::TYPE_SHARED;
			$sharedChannel = \CPullChannel::GetShared($cache, $reopen, $sharedChannelType);
		}
		else // isset($params['CHANNEL'])
		{
			$privateChannel = [
				'CHANNEL_ID' => $params['CHANNEL']->getPrivateId(),
				'CHANNEL_PUBLIC_ID' => $params['CHANNEL']->getPublicId(),
				'CHANNEL_DT' => $params['CHANNEL']->getDateCreate()->getTimestamp(),
				'CHANNEL_DT_END' => $params['CHANNEL']->getDateCreate()->getTimestamp() + static::ONE_YEAR,
			];
			$sharedChannel = null;
		}

		$domain = defined('BX24_HOST_NAME')? BX24_HOST_NAME: $_SERVER['SERVER_NAME'];

		$isSharedMode = \CPullOptions::IsServerShared();
		$serverConfig = [
			'VERSION' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getServerVersion(): \CPullOptions::GetQueueServerVersion(),
			'SERVER_ENABLED' => \CPullOptions::GetQueueServerStatus(),
			'MODE' => \CPullOptions::GetQueueServerMode(),
			'LONG_POLLING' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getLongPollingUrl(): \CPullOptions::GetListenUrl(),
			'LONG_POOLING_SECURE' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getLongPollingUrl() : \CPullOptions::GetListenSecureUrl(),
			'WEBSOCKET_ENABLED' => $isSharedMode ? true : \CPullOptions::GetWebSocket(),
			'WEBSOCKET' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getWebSocketUrl() : \CPullOptions::GetWebSocketUrl(),
			'WEBSOCKET_SECURE' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getWebSocketUrl() : \CPullOptions::GetWebSocketSecureUrl(),
			'PUBLISH_ENABLED' => $isSharedMode ? true : \CPullOptions::GetPublishWebEnabled(),
			'PUBLISH' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getWebPublishUrl() : \CPullOptions::GetPublishWebUrl(),
			'PUBLISH_SECURE' => $isSharedMode ? \Bitrix\Pull\SharedServer\Config::getWebPublishUrl() : \CPullOptions::GetPublishWebSecureUrl(),
			'CONFIG_TIMESTAMP' => \CPullOptions::GetConfigTimestamp(),
		];
		foreach ($serverConfig as $key => $value)
		{
			if(is_string($value) && mb_strpos($value, '#DOMAIN#') !== false)
			{
				$serverConfig[$key] = str_replace('#DOMAIN#', $domain, $value);
			}
		}
		$config['SERVER'] = $serverConfig;
		if($isSharedMode)
		{
			$config['CLIENT_ID'] = Client::getPublicLicenseCode();
		}

		$config['API'] = Array(
			'REVISION_WEB' => PULL_REVISION_WEB,
			'REVISION_MOBILE' => PULL_REVISION_MOBILE,
		);

		$config['CHANNELS'] = [];
		if ($sharedChannel)
		{
			$config['CHANNELS']['SHARED'] = [
				'ID' => \CPullChannel::SignChannel($sharedChannel["CHANNEL_ID"]),
				'START' => $sharedChannel['CHANNEL_DT'],
				'END' => $sharedChannel['CHANNEL_DT']+\CPullChannel::CHANNEL_TTL,
			];
		}
		if ($privateChannel)
		{
			if (\CPullOptions::GetQueueServerVersion() > 3)
			{
				$privateId = $privateChannel['CHANNEL_PUBLIC_ID']
					? "{$privateChannel['CHANNEL_ID']}:{$privateChannel['CHANNEL_PUBLIC_ID']}"
					: $privateChannel['CHANNEL_ID']
				;
				$privateId = \CPullChannel::SignChannel($privateId);

				$publicId = \CPullChannel::SignPublicChannel($privateChannel['CHANNEL_PUBLIC_ID']);
			}
			else
			{
				$privateId = \CPullChannel::SignChannel($privateChannel['CHANNEL_ID']);
				$publicId = '';
			}

			$config['CHANNELS']['PRIVATE'] = [
				'ID' => $privateId,
				'PUBLIC_ID' => $publicId,
				'START' => $privateChannel['CHANNEL_DT'],
				'END' => $privateChannel['CHANNEL_DT_END'] ?? $privateChannel['CHANNEL_DT']+\CPullChannel::CHANNEL_TTL,
			];
		}

		$params['JSON'] = (bool)($params['JSON'] ?? false);

		$config['PUBLIC_CHANNELS'] = \Bitrix\Pull\Channel::getPublicIds(['JSON' => $params['JSON']]);
		if (\CPullOptions::GetQueueServerVersion() >= 5)
		{
			$channelsForToken = [];
			if ($sharedChannel)
			{
				$channelsForToken[] = $sharedChannel['CHANNEL_ID'];
			}
			if ($privateChannel && $userId == 0)
			{
				$channelsForToken[] = $privateChannel['CHANNEL_ID'];
				if ($privateChannel['CHANNEL_PUBLIC_ID'] != '')
				{
					$channelsForToken[] = $privateChannel['CHANNEL_PUBLIC_ID'];
				}
			}
			[$config['JWT'], $config['EXP']] = \Bitrix\Pull\Auth\Jwt::create($channelsForToken, $userId, [
				'ttl' => \CPullOptions::GetConfigTtl()
			]);
		}
		if (\CPullOptions::GetConfigTtl() > 0 && !isset($config['EXP']))
		{
			$config['EXP'] = time() + \CPullOptions::GetConfigTtl();
		}

		if ($params['JSON'])
		{
			$result['server'] = array_change_key_case($config['SERVER'], CASE_LOWER);
			$result['api'] = array_change_key_case($config['API'], CASE_LOWER);

			foreach ($config['CHANNELS'] as $type => $channel)
			{
				$type = mb_strtolower($type);
				$result['channels'][$type] = array_change_key_case($channel, CASE_LOWER);
				$result['channels'][$type]['type'] = $type;
				$result['channels'][$type]['start'] = date('c', $channel['START']);
				$result['channels'][$type]['end'] = date('c', $channel['END']);
			}

			if($isSharedMode)
			{
				$result['clientId'] = $config['CLIENT_ID'];
			}
			if (isset($config['JWT']))
			{
				$result['jwt'] = $config['JWT'];
			}
			if (isset($config['EXP']))
			{
				$result['exp'] = $config['EXP'];
			}

			$result['publicChannels'] = $config['PUBLIC_CHANNELS'];

			$config = $result;
		}

		return $config;

	}

	/**
	 * @param string $channelId
	 * @return bool|string|null
	 */
	public static function getPublishUrl($channelId = "")
	{
		$params = [];
		if(\CPullOptions::IsServerShared())
		{
			$result = \Bitrix\Pull\SharedServer\Config::getPublishUrl();
			$params["clientId"] = \Bitrix\Pull\SharedServer\Client::getPublicLicenseCode();
		}
		else
		{
			$result = \CPullOptions::GetPublishUrl();
		}

		if($channelId != "")
		{
			$params["CHANNEL_ID"] = $channelId;
		}

		return \CHTTP::urlAddParams($result, $params);
	}

	public static function getJsonRpcUrl()
	{
		$params = [];
		if(\CPullOptions::IsServerShared())
		{
			$result = \Bitrix\Pull\SharedServer\Config::getJsonRpcUrl();
			$params["clientId"] = \Bitrix\Pull\SharedServer\Client::getPublicLicenseCode();
		}
		else
		{
			$result = \CPullOptions::GetJsonRpcUrl();
		}

		return \CHTTP::urlAddParams($result, $params);
	}

	public static function getHostId()
	{
		static $hostId = null;

		if ($hostId === null)
		{
			$hostId = Option::get("pull", "host_id", "");
		}
		if ($hostId == '')
		{
			$hostId = Random::getString(32);
			Option::set("pull", "host_id", $hostId);
		}

		return $hostId;
	}

	public static function getSignatureKey()
	{
		if(\CPullOptions::IsServerShared())
		{
			return \Bitrix\Pull\SharedServer\Config::getSignatureKey();
		}
		else
		{
			return \CPullOptions::GetSignatureKey();
		}
	}

	public static function isProtobufUsed()
	{
		$result =
			\CPullOptions::IsServerShared() ||
			(
				\CPullOptions::GetQueueServerVersion() == 4 &&
				\CPullOptions::IsProtobufSupported() &&
				\CPullOptions::IsProtobufEnabled()
			);

		return $result;
	}

	public static function isJsonRpcUsed(): bool
	{
		return \CPullOptions::GetQueueServerVersion() >= 5;
	}
}
