<?
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Config\Option;

class CPullOptions
{
	static $optionDefaultConfig = null;
	static $optionDefaultModule = null;

	const PROTOBUF_ENABLED = 'enable_protobuf';
	const MAX_CHANNELS_PER_REQUEST = 'limit_max_channels_per_request';
	const MAX_MESSAGES_PER_REQUEST = 'limit_max_messages_per_request';
	const MAX_PAYLOAD = 'limit_max_payload';

	const SERVER_MODE_SHARED = 'shared';
	const SERVER_MODE_PERSONAL = 'personal';

	public static function CheckNeedRun($bGetSectionStatus = true)
	{
		$arExcludeSites = CPullOptions::GetExcludeSites();
		if (isset($arExcludeSites[SITE_ID]))
			return false;

		global $CACHE_MANAGER;

		$bAdminSection = false;
		if(defined("ADMIN_SECTION") && ADMIN_SECTION == true)
			$bAdminSection = true;

		$arResult = Array();
		$res = $CACHE_MANAGER->Read(2592000, "pull_cnr");
		if ($res)
			$arResult = $CACHE_MANAGER->Get("pull_cnr");

		if(!$res)
		{
			$arResult = Array(
				'ADMIN_SECTION' => false,
				'PUBLIC_SECTION' => false
			);

			$arModule = self::GetDependentModule();
			foreach ($arModule as $moduleId => $options)
			{
				if (isset($options['ADMIN_SECTION']) && $options['ADMIN_SECTION'] == 'Y')
					$arResult['ADMIN_SECTION'] = true;
				if (isset($options['PUBLIC_SECTION']) && $options['PUBLIC_SECTION'] == 'Y')
					$arResult['PUBLIC_SECTION'] = true;
			}

			$CACHE_MANAGER->Set("pull_cnr", $arResult);
		}

		return $bGetSectionStatus? $arResult[$bAdminSection? 'ADMIN_SECTION': 'PUBLIC_SECTION']: $arResult;
	}

	public static function ModuleEnable()
	{
		$arResult = self::CheckNeedRun(false);
		return ($arResult['ADMIN_SECTION'] || $arResult['PUBLIC_SECTION'])? true: false;
	}

	public static function GetDependentModule()
	{
		$arModule = Array();
		foreach(GetModuleEvents("pull", "OnGetDependentModule", true) as $arEvent)
		{
			$ar = ExecuteModuleEventEx($arEvent);
			if (isset($ar['MODULE_ID']))
			{
				$arModule[$ar['MODULE_ID']] = Array(
					'MODULE_ID' => $ar['MODULE_ID'],
					'ADMIN_SECTION' => isset($ar['USE']) && in_array('ADMIN_SECTION', $ar['USE'])? true: false,
					'PUBLIC_SECTION' => isset($ar['USE']) && in_array('PUBLIC_SECTION', $ar['USE'])? true: false,
				);
			}
		}

		return $arModule;
	}

	public static function GetExcludeSites()
	{
		$result = COption::GetOptionString("pull", "exclude_sites", "a:0:{}", self::GetDefaultOption("exclude_sites"));
		return unserialize($result, ["allowed_classes" => false]);
	}

	public static function SetExcludeSites($sites)
	{
		if (!is_array($sites))
			return false;

		COption::SetOptionString("pull", "exclude_sites", serialize($sites));

		return true;
	}

	/*
	 * @deprecated No longer used by internal code and not recommended. Use CPullOptions::GetQueueServerStatus()
	 */
	public static function GetNginxStatus()
	{
		return self::GetQueueServerStatus();
	}
	public static function GetQueueServerStatus()
	{
		if(static::IsServerShared())
		{
			return \Bitrix\Pull\SharedServer\Config::isRegistered();
		}
		else
		{
			return COption::GetOptionString("pull", "nginx", self::GetDefaultOption("nginx")) == "Y";
		}
	}
	public static function GetQueueServerHeaders()
	{
		$result = COption::GetOptionString("pull", "nginx_headers", self::GetDefaultOption("nginx_headers"));
		return $result == 'Y' && self::GetQueueServerVersion() < 3? true: false;
	}

	/*
	 * @deprecated No longer used by internal code and not recommended. Use CPullOptions::SetQueueServerStatus()
	 */
	public static function SetNginxStatus($flag = "N")
	{
		return self::SetQueueServerStatus($flag);
	}
	public static function SetQueueServerStatus($flag = "N")
	{
		$currentValue = COption::GetOptionString("pull", "nginx");
		if($currentValue === $flag)
		{
			return true;
		}

		COption::SetOptionString("pull", "nginx", $flag=='Y'?'Y':'N');
		if ($flag=='Y')
		{
			CAgent::AddAgent("CPullChannel::CheckOnlineChannel();", "pull", "N", 240, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+240, "FULL"));
		}
		else
		{
			CAgent::RemoveAgent("CPullChannel::CheckOnlineChannel();", "pull");
		}

		return true;
	}

	public static function SetQueueServerHeaders($flag = "Y")
	{
		COption::SetOptionString("pull", "nginx_headers", $flag=='Y'?'Y':'N');
		return true;
	}

	/**
	 * Return operation mode of the associated server.
	 * @return string
	 */
	public static function GetQueueServerMode()
	{
		return Option::get("pull", "server_mode");
	}
	/**
	 * Sets operation mode of the associated server.
	 * @param string $mode Operation mode of the server.
	 */
	public static function SetQueueServerMode($mode)
	{
		Option::set("pull", "server_mode", $mode);
	}
	public static function IsServerShared()
	{
		return static::GetQueueServerMode() == static::SERVER_MODE_SHARED;
	}

	public static function GetPushStatus()
	{
		$result = COption::GetOptionString("pull", "push", self::GetDefaultOption("push"));
		return $result == 'N'? false: true;
	}

	public static function SetPushStatus($flag = "N")
	{
		COption::SetOptionString("pull", "push", $flag=='Y'?'Y':'N');
		if ($flag == 'Y')
			CAgent::AddAgent("CPushManager::SendAgent();", "pull", "N", 30);
		else
			CAgent::RemoveAgent("CPushManager::SendAgent();", "pull");

		return true;
	}

	public static function GetPushMessagePerHit()
	{
		return intval(COption::GetOptionInt("pull", "push_message_per_hit", self::GetDefaultOption("push_message_per_hit")));
	}

	public static function SetPushMessagePerHit($count)
	{
		COption::SetOptionInt("pull", "push_message_per_hit", intval($count));

		return true;
	}

	public static function GetGuestStatus()
	{
		return COption::GetOptionString("pull", "guest", self::GetDefaultOption("guest")) == 'Y' && IsModuleInstalled('statistic');
	}

	public static function SetGuestStatus($flag = "N")
	{
		COption::SetOptionString("pull", "guest", IsModuleInstalled('statistic') && $flag=='Y'?'Y':'N');

		return true;
	}


	public static function GetPublishUrl($channelId = "")
	{
		$url = COption::GetOptionString("pull", "path_to_publish", self::GetDefaultOption("path_to_publish"));
		return $url;
	}

	public static function SetSignatureKey($signature)
	{
		COption::SetOptionString("pull", "signature_key", $signature);

		return true;
	}

	public static function GetSignatureKey()
	{
		$url = COption::GetOptionString("pull", "signature_key", self::GetDefaultOption("signature_key"));
		return $url;
	}

	public static function GetSignatureAlgorithm()
	{
		$url = COption::GetOptionString("pull", "signature_algo", self::GetDefaultOption("signature_algo"));
		return $url;
	}

	public static function SetPublishUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_publish');
		}
		COption::SetOptionString("pull", "path_to_publish", $path);
		return true;
	}

	public static function GetListenUrl($channelId = "")
	{
		if (!is_array($channelId) && $channelId <> '')
			$channelId = Array($channelId);
		else if (!is_array($channelId))
			$channelId = Array();

		$optionName = "path_to_modern_listener";
		$url = COption::GetOptionString("pull", $optionName, self::GetDefaultOption($optionName)).(count($channelId)>0?'?CHANNEL_ID='.implode('/', $channelId):'');
		$url = str_replace('#PORT#', self::GetQueueServerVersion()>1? '': ':8893', $url);

		return $url;
	}

	public static function SetListenUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_modern_listener');
		}
		COption::SetOptionString("pull", 'path_to_modern_listener', $path);
		return true;
	}

	public static function GetPublishWebEnabled()
	{
		return \CPullOptions::GetQueueServerVersion() > 3;
	}

	public static function GetPublishWebUrl($channelId = "")
	{
		if (!is_array($channelId) && $channelId <> '')
			$channelId = Array($channelId);
		else if (!is_array($channelId))
			$channelId = Array();

		$optionName = "path_to_publish_web";
		$url = COption::GetOptionString("pull", $optionName, self::GetDefaultOption($optionName)).(count($channelId)>0?'?CHANNEL_ID='.implode('/', $channelId):'');

		return $url;
	}

	public static function SetPublishWebUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_publish_web');
		}
		COption::SetOptionString("pull", 'path_to_publish_web', $path);

		return true;
	}

	public static function GetPublishWebSecureUrl($channelId = "")
	{
		if (!is_array($channelId) && $channelId <> '')
			$channelId = Array($channelId);
		else if (!is_array($channelId))
			$channelId = Array();

		$optionName = "path_to_publish_web_secure";
		$url = COption::GetOptionString("pull", $optionName, self::GetDefaultOption($optionName)).(count($channelId)>0?'?CHANNEL_ID='.implode('/', $channelId):'');

		return $url;
	}

	public static function SetPublishWebSecureUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_publish_web_secure');
		}
		COption::SetOptionString("pull", 'path_to_publish_web_secure', $path);

		return true;
	}

	public static function GetListenSecureUrl($channelId = "")
	{
		if (!is_array($channelId) && $channelId <> '')
			$channelId = Array($channelId);
		else if (!is_array($channelId))
			$channelId = Array();

		$optionName = "path_to_modern_listener_secure";
		$url = COption::GetOptionString("pull", $optionName, self::GetDefaultOption($optionName)).(count($channelId)>0?'?CHANNEL_ID='.implode('/', $channelId):'');
		$url = str_replace('#PORT#', self::GetQueueServerVersion()>1? '': ':8894', $url);

		return $url;
	}

	public static function SetListenSecureUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_modern_listener_secure');
		}
		COption::SetOptionString("pull", 'path_to_modern_listener_secure', $path);
		return true;
	}

	/*
	 * Get version of QueueServer
	 * 1 version - nginx-push-stream-module 0.3.4
	 * 2 version - nginx-push-stream-module 0.4.0
	 * 3 version - Bitrix Push & Pull server 1.0
	 * 4 version - Bitrix Push & Pull server 2.0
	 */
	public static function GetQueueServerVersion()
	{
		return static::IsServerShared() ? \Bitrix\Pull\SharedServer\Config::getServerVersion() : intval(COption::GetOptionInt("pull", "nginx_version", self::GetDefaultOption("nginx_version")));
	}

	public static function SetQueueServerVersion($version)
	{
		COption::SetOptionInt("pull", "nginx_version", intval($version));

		return true;
	}

	public static function GetCommandPerHit()
	{
		return intval(COption::GetOptionInt("pull", "nginx_command_per_hit", self::GetDefaultOption("nginx_command_per_hit")));
	}

	public static function SetCommandPerHit($count)
	{
		COption::SetOptionInt("pull", "nginx_command_per_hit", intval($count));

		return true;
	}

	public static function GetWebSocketStatus()
	{
		return self::GetWebSocket() && self::GetQueueServerVersion()>1? true: false;
	}

	public static function GetWebSocket()
	{
		$result = false;

		if (
			CPullOptions::GetQueueServerVersion() >= 3
			|| COption::GetOptionString("pull", "websocket", self::GetDefaultOption("websocket")) == 'Y'
		)
		{
			$result = true;
		}

		return $result;
	}

	public static function SetWebSocket($flag = "N")
	{
		COption::SetOptionString("pull", "websocket", $flag=='Y'?'Y':'N');
		return true;
	}

	public static function GetWebSocketUrl($channelId = "")
	{
		if (!is_array($channelId) && $channelId <> '')
			$channelId = Array($channelId);
		else if (!is_array($channelId))
			$channelId = Array();

		$url = COption::GetOptionString("pull", "path_to_websocket", self::GetDefaultOption("path_to_websocket")).(count($channelId)>0?'?CHANNEL_ID='.implode('/', $channelId):'');
		return $url;
	}

	public static function SetWebSocketUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_websocket');
		}

		COption::SetOptionString("pull", "path_to_websocket", $path);
		return true;
	}

	public static function GetWebSocketSecureUrl($channelId = "")
	{
		if (!is_array($channelId) && $channelId <> '')
			$channelId = Array($channelId);
		else if (!is_array($channelId))
			$channelId = Array();

		$url = COption::GetOptionString("pull", "path_to_websocket_secure", self::GetDefaultOption("path_to_websocket_secure")).(count($channelId)>0?'?CHANNEL_ID='.implode('/', $channelId):'');
		return $url;
	}

	public static function SetWebSocketSecureUrl($path = "")
	{
		if ($path == '')
		{
			$path = self::GetDefaultOption('path_to_websocket_secure');
		}

		COption::SetOptionString("pull", "path_to_websocket_secure", $path);
		return true;
	}

	public static function SetConfigTimestamp($timestamp = 0)
	{
		if(!$timestamp)
		{
			$timestamp = time();
		}
		COption::SetOptionInt("pull", "config_timestamp", $timestamp);
	}

	public static function GetConfigTimestamp()
	{
		return COption::GetOptionInt("pull", "config_timestamp");
	}

	public static function GetMaxPayload()
	{
		$maxPayload = (int)Option::get('pull', static::MAX_PAYLOAD);
		if(!$maxPayload === 0)
		{
			$maxPayload = static::GetDefaultOption(static::MAX_PAYLOAD);
		}
		return $maxPayload;
	}

	public static function GetMaxChannelsPerRequest()
	{
		$maxChannelsPerRequest = (int)Option::get('pull', static::MAX_CHANNELS_PER_REQUEST);
		if(!$maxChannelsPerRequest === 0)
		{
			$maxChannelsPerRequest = static::GetDefaultOption(static::MAX_CHANNELS_PER_REQUEST);
		}
		return $maxChannelsPerRequest;
	}

	public static function GetMaxMessagesPerRequest()
	{
		$maxMessagesPerRequest = (int)Option::get('pull', static::MAX_MESSAGES_PER_REQUEST);
		if(!$maxMessagesPerRequest === 0)
		{
			$maxMessagesPerRequest = static::GetDefaultOption(static::MAX_MESSAGES_PER_REQUEST);
		}
		return $maxMessagesPerRequest;
	}

	public static function IsProtobufSupported()
	{
		// google's protobuf library requires php x64 or bc_math extension.
		return (PHP_INT_SIZE >= 8 || function_exists('bcadd'));
	}

	public static function IsProtobufEnabled()
	{
		return (Option::get('pull', static::PROTOBUF_ENABLED) === 'Y');
	}

	/* UTILITY */

	public static function SendConfigDie()
	{
		$arMessage = Array(
			'module_id' => 'pull',
			'command' => 'config_expire',
			'params' => Array()
		);
		CPullStack::AddBroadcast($arMessage);
	}

	public static function GetDefaultOption($optionName)
	{
		if (is_null(self::$optionDefaultConfig))
		{
			$config = \Bitrix\Main\Config\Configuration::getValue('pull');
			self::$optionDefaultConfig = is_null($config) ? Array() : $config;
		}

		if (is_null(self::$optionDefaultModule))
		{
			include($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/pull/default_option.php');
			self::$optionDefaultModule = $pull_default_option;
		}

		if (array_key_exists($optionName, self::$optionDefaultConfig))
		{
			return self::$optionDefaultConfig[$optionName];
		}

		return array_key_exists($optionName, self::$optionDefaultModule)? self::$optionDefaultModule[$optionName]: null;
	}

	public static function ClearCheckCache()
	{
		// init module cache
		$CModule = new CModule();
		$CModule->IsInstalled();

		CAgent::RemoveAgent("CPullOptions::ClearAgent();", "pull");
		CAgent::AddAgent("CPullOptions::ClearAgent();", "pull", "N", 30, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+30, "FULL"));
	}

	public static function ClearAgent()
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->Clean("pull_cnr");

		if (self::ModuleEnable())
		{
			CAgent::AddAgent("CPullChannel::CheckOnlineChannel();", "pull", "N", 240, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+100, "FULL"));
			CAgent::AddAgent("CPullChannel::CheckExpireAgent();", "pull", "N", 43200, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset() + 43200, "FULL"));
			CAgent::AddAgent("CPullWatch::CheckExpireAgent();", "pull", "N", 600, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset() + 600, "FULL"));
		}
		else
		{
			CAgent::RemoveAgent("CPullChannel::CheckOnlineChannel();", "pull");
			CAgent::RemoveAgent("CPullChannel::CheckExpireAgent();", "pull");
			CAgent::RemoveAgent("CPullWatch::CheckExpireAgent();", "pull");
			CAgent::RemoveAgent("CPushManager::SendAgent();", "pull");
		}
	}

	public static function OnProlog()
	{
		\Bitrix\Main\UI\Extension::load('pull');
	}

	public static function OnEpilog()
	{
		$userId = 0;
		if (defined('PULL_USER_ID'))
		{
			$userId = PULL_USER_ID;
		}
		else if (is_object($GLOBALS['USER']) && intval($GLOBALS['USER']->GetID()) > 0)
		{
			$userId = intval($GLOBALS['USER']->GetID());
		}
		else if (IsModuleInstalled('statistic') && intval($_SESSION["SESS_SEARCHER_ID"]) <= 0 && intval($_SESSION["SESS_GUEST_ID"]) > 0 && COption::GetOptionString("pull", "guest", self::GetDefaultOption("guest")) == 'Y')
		{
			$userId = intval($_SESSION["SESS_GUEST_ID"])*-1;
		}

		if (!defined('BX_PULL_SKIP_INIT') && !(isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y') && $userId != 0 && CModule::IncludeModule('pull'))
		{
			define("BX_PULL_SKIP_INIT", true);

			if (CPullOptions::CheckNeedRun())
			{
				Asset::getInstance()->addString('<script type="text/javascript">BX.bind(window, "load", function(){BX.PULL.start();});</script>');
			}
		}
	}
}
