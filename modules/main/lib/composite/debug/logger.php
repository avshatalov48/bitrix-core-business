<?
namespace Bitrix\Main\Composite\Debug;

use Bitrix\Main\Composite\Helper;
use Bitrix\Main\Composite\Debug\Model\LogTable;
use Bitrix\Main\Composite\Engine;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

class Logger
{
	const TYPE_CACHE_REWRITING = "CACHE_REWRITING";
	const TYPE_COMPONENT_VOTING = "COMPONENT_VOTING";
	const TYPE_BUFFER_RESTART = "BUFFER_RESTART";
	const TYPE_CACHE_RESET = "CACHE_RESET";

	const TYPE_GET_METHOD_ONLY = "GET_METHOD_ONLY";
	const TYPE_NCC_PARAMETER = "NCC_PARAMETER";
	const TYPE_NCC_COOKIE = "NCC_COOKIE";
	const TYPE_EXCLUDE_MASK = "EXCLUDE_MASK";
	const TYPE_EXCLUDE_PARAMETER = "EXCLUDE_PARAMETER";
	const TYPE_INCLUDE_MASK = "INCLUDE_MASK";
	const TYPE_INVALID_HOST = "INVALID_HOST";
	const TYPE_INVALID_QUERY_STRING = "INVALID_QUERY_STRING";

	const TYPE_LOCAL_REDIRECT = "LOCAL_REDIRECT";
	const TYPE_ADMIN_PANEL = "ADMIN_PANEL";
	const TYPE_PHP_SHUTDOWN = "PHP_SHUTDOWN";
	const TYPE_PAGE_NOT_CACHEABLE = "PAGE_NOT_CACHEABLE";
	const TYPE_COMPOSITE_NOT_INJECTED = "COMPOSITE_NOT_INJECTED";

	const TYPE_CC_COOKIE_NOT_FOUND = "CC_COOKIE_NOT_FOUND";
	const TYPE_SESSID_PARAMETER = "SESSID_PARAMETER";
	const TYPE_AJAX_REQUEST = "AJAX_REQUEST";
	const TYPE_BITRIX_FOLDER = "BITRIX_FOLDER";
	const TYPE_CONTROLLER_FILE = "CONTROLLER_FILE";
	const TYPE_MESSAGE = "MESSAGE";

	const END_TIME_OPTION = "composite_debug_end_time";

	public static function isOn()
	{
		return time() < Option::get("main", static::END_TIME_OPTION, 0);
	}

	public static function enable($endTime = 0)
	{
		Option::set("main", static::END_TIME_OPTION, intval($endTime));
	}

	public static function disable()
	{
		Option::delete("main", array("name" => static::END_TIME_OPTION));
	}

	public static function getEndTime()
	{
		return intval(Option::get("main", static::END_TIME_OPTION, 0));
	}

	public static function log(array $params = array())
	{
		if (!static::isOn())
		{
			return null;
		}

		$pageTitle = $params["TITLE"] ?? $GLOBALS["APPLICATION"]->getTitle();
		$pageTitle = mb_substr($pageTitle, 0, 250);

		$pageHost = isset($params["HOST"]) && mb_strlen($params["HOST"]) ? $params["HOST"] : Helper::getHttpHost();
		$pageHost = mb_substr($pageHost, 0, 100);

		$pageUri = isset($params["URI"]) && mb_strlen($params["URI"]) ? $params["URI"] : Helper::getRequestUri();
		$pageUri = mb_substr($pageUri, 0, 2000);

		$userId = 0;
		if (isset($params["USER_ID"]))
		{
			$userId = intval($params["USER_ID"]);
		}
		else if (isset($GLOBALS["USER"]) && $GLOBALS["USER"]->isAuthorized())
		{
			$userId = intval($GLOBALS["USER"]->getId());
		}

		$data = array(
			"TITLE" => $pageTitle,
			"MESSAGE" => isset($params["MESSAGE"]) && is_string($params["MESSAGE"]) ? $params["MESSAGE"] : null,
			"TYPE" =>
				isset($params["TYPE"]) && in_array($params["TYPE"], self::getTypes())
				? $params["TYPE"]
				: self::TYPE_MESSAGE,
			"URI" => $pageUri,
			"HOST" => $pageHost,
			"USER_ID" => $userId,
			"AJAX" => Engine::isAjaxRequest() ? "Y" : "N",
			"PAGE_ID" => isset($params["PAGE_ID"]) ? intval($params["PAGE_ID"]) : 0
		);

		$GLOBALS["DB"]->StartUsingMasterOnly();

		$result = LogTable::add($data);

		$GLOBALS["DB"]->StopUsingMasterOnly();

		return $result;
	}

	/**
	 * Returns logger types
	 * @return array
	 */
	public static function getTypes()
	{
		static $types = null;
		if ($types !== null)
		{
			return $types;
		}

		$types = array();
		$refClass = new \ReflectionClass(__CLASS__);
		foreach ($refClass->getConstants() as $name => $value)
		{
			if (mb_substr($name, 0, 4) === "TYPE")
			{
				$types[] = $value;
			}
		}

		return $types;
	}

	/**
	 * Returns name for specific type. Returns null if type is invalid .
	 * @param string $type Log Message Type.
	 * @return null|string
	 */
	public static function getTypeName($type)
	{
		static $messagesLoaded = false;

		if (!$messagesLoaded)
		{
			Loc::loadMessages(__FILE__);
			$messagesLoaded = true;
		}

		$types = static::getTypes();
		if (!in_array($type, $types))
		{
			return null;
		}

		return Loc::getMessage("MAIN_COMPOSITE_LOG_{$type}") ?: $type;
	}
}