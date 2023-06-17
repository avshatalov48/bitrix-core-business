<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Config;

class Migrator
{
	public static function wnc()
	{
		$ar = array(
			"utf_mode" => array("value" => defined('BX_UTF'), "readonly" => true),
			"default_charset" => array("value" => defined('BX_DEFAULT_CHARSET') ? BX_DEFAULT_CHARSET : null, "readonly" => false),
			"no_accelerator_reset" => array("value" => defined('BX_NO_ACCELERATOR_RESET'), "readonly" => false),
			"http_status" => array("value" => (defined('BX_HTTP_STATUS') && BX_HTTP_STATUS), "readonly" => false),
		);

		$cache = array();
		if (defined('BX_CACHE_SID'))
			$cache["sid"] = BX_CACHE_SID;
		if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/memcache.php"))
		{
			$arList = null;
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/cluster/memcache.php");
			if (defined("BX_MEMCACHE_CLUSTER") && is_array($arList))
			{
				foreach ($arList as $listKey => $listVal)
				{
					$bOtherGroup = defined("BX_CLUSTER_GROUP") && ($listVal["GROUP_ID"] !== BX_CLUSTER_GROUP);

					if (($listVal["STATUS"] !== "ONLINE") || $bOtherGroup)
						unset($arList[$listKey]);
				}

				if (!empty($arList))
				{
					$cache["type"] = array(
						"extension" => "memcache",
						"required_file" => "modules/cluster/classes/general/memcache_cache.php",
						"class_name" => "CPHPCacheMemcacheCluster",
					);
				}
			}
		}
		if (!isset($cache["type"]))
		{
			if (defined('BX_CACHE_TYPE'))
			{
				$cache["type"] = BX_CACHE_TYPE;

				switch ($cache["type"])
				{
					case "memcache":
					case "CPHPCacheMemcache":
						$cache["type"] = "memcache";
						break;
					case "eaccelerator":
					case "CPHPCacheEAccelerator":
						$cache["type"] = "eaccelerator";
						break;
					case "apc":
					case "CPHPCacheAPC":
						$cache["type"] = "apc";
						break;
					case "xcache":
					case "CPHPCacheXCache":
						$cache["type"] = array(
							"extension" => "xcache",
							"required_file" => "modules/main/classes/general/cache_xcache.php",
							"class_name" => "CPHPCacheXCache",
						);
						break;
					default:
						if (defined("BX_CACHE_CLASS_FILE") && file_exists(BX_CACHE_CLASS_FILE))
						{
							$cache["type"] = array(
								"required_remote_file" => BX_CACHE_CLASS_FILE,
								"class_name" => BX_CACHE_TYPE
							);
						}
						else
						{
							$cache["type"] = "files";
						}
						break;
				}
			}
			else
			{
				$cache["type"] = "files";
			}
		}
		if (defined("BX_MEMCACHE_PORT"))
			$cache["memcache"]["port"] = intval(BX_MEMCACHE_PORT);
		if (defined("BX_MEMCACHE_HOST"))
			$cache["memcache"]["host"] = BX_MEMCACHE_HOST;
		$ar["cache"] = array("value" => $cache, "readonly" => false);

		$cacheFlags = array();
		$arCacheConsts = array("CACHED_b_option" => "config_options", "CACHED_b_lang_domain" => "site_domain");
		foreach ($arCacheConsts as $const => $name)
			$cacheFlags[$name] = defined($const) ? constant($const) : 0;
		$ar["cache_flags"] = array("value" => $cacheFlags, "readonly" => false);

		$ar["cookies"] = array("value" => array("secure" => false, "http_only" => true), "readonly" => false);

		$ar["exception_handling"] = array(
			"value" => array(
				"debug" => true,
				"handled_errors_types" => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE,
				"exception_errors_types" => E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_WARNING & ~E_COMPILE_WARNING & ~E_DEPRECATED,
				"ignore_silence" => false,
				"assertion_throws_exception" => true,
				"assertion_error_type" => E_USER_ERROR,
				"log" => array(
					/*"class_name" => "...",
					"extension" => "...",
					"required_file" => "...",*/
					"settings" => array(
						"file" => "bitrix/modules/error.log",
						"log_size" => 1000000
					)
				),
			),
			"readonly" => false
		);

		global $DBHost, $DBName, $DBLogin, $DBPassword;

		$dbClassName = defined('BX_USE_MYSQLI') && BX_USE_MYSQLI === true ? "\\Bitrix\\Main\\DB\\MysqliConnection" : "\\Bitrix\\Main\\DB\\MysqlConnection";

		$ar['connections']['value']['default'] = array(
			'className' => $dbClassName,
			'host' => $DBHost,
			'database' => $DBName,
			'login' => $DBLogin,
			'password' => $DBPassword,
			'options' =>  ((!defined("DBPersistent") || DBPersistent) ? 1 : 0) | ((defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT === true) ? 2 : 0)
		);
		$ar['connections']['readonly'] = true;

		$configuration = Configuration::getInstance();

		foreach ($ar as $k => $v)
		{
			if ($configuration->get($k) === null)
			{
				if ($v["readonly"])
					$configuration->addReadonly($k, $v["value"]);
				else
					$configuration->add($k, $v["value"]);
			}
		}

		$configuration->saveConfiguration();

		$filename1 = $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/after_connect.php";
		$filename2 = $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/after_connect_d7.php";
		if (file_exists($filename1) && !file_exists($filename2))
		{
			$source = file_get_contents($filename1);
			$source = trim($source);
			$source = preg_replace("#\\\$DB->Query\(#i", "\$this->queryExecute(", $source);
			file_put_contents($filename2, $source);
		}
	}
}
