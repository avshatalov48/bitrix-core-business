<?
namespace Bitrix\Main\Composite;

use Bitrix\Main;
use Bitrix\Main\Composite\Debug\Logger;

/**
 * Class Responder
 * @package \Bitrix\Main\Composite
 */
class Responder
{
	private static $error = null;
	private static $errorMessage = null;
	/**
	 * Checks many conditions to enable HTML Cache and tries to send
	 *
	 * @return void
	 */
	public static function respond()
	{
		require_once(__DIR__."/helper.php");

		self::setErrorHandler(); //avoid possible PHP warnings or notices
		self::registerAutoloader();

		self::modifyHttpHeaders();

		if (self::isValidRequest())
		{
			if (!Helper::isCompositeRequest())
			{
				self::trySendResponse();
			}

			define("USE_HTML_STATIC_CACHE", true);
		}

		self::unregisterAutoloader();
		self::restoreErrorHandler();
	}

	private static function isValidRequest()
	{
		if (
			isset($_SERVER["HTTP_BX_AJAX"]) ||
			isset($_GET["bxajaxid"]) ||
			(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] === "XMLHttpRequest")
		)
		{
			self::$error = Logger::TYPE_AJAX_REQUEST;
			return false;
		}

		if (isset($_GET["ncc"]))
		{
			self::$error = Logger::TYPE_NCC_PARAMETER;
			return false;
		}

		if (Helper::isBitrixFolder())
		{
			self::$error = Logger::TYPE_BITRIX_FOLDER;
			return false;
		}

		if (preg_match("#^/index_controller\\.php#", $_SERVER["REQUEST_URI"]) > 0)
		{
			self::$error = Logger::TYPE_CONTROLLER_FILE;
			return false;
		}

		//to warm up localStorage
		define("ENABLE_HTML_STATIC_CACHE_JS", true);

		if ($_SERVER["REQUEST_METHOD"] !== "GET")
		{
			self::$error = Logger::TYPE_GET_METHOD_ONLY;
			self::$errorMessage = "Request Method: ".$_SERVER["REQUEST_METHOD"];
			return false;
		}

		if (isset($_GET["sessid"]))
		{
			self::$error = Logger::TYPE_SESSID_PARAMETER;
			return false;
		}

		$compositeOptions = Helper::getOptions();

		//NCC cookie exists
		if (isset($compositeOptions["COOKIE_NCC"]) &&
			array_key_exists($compositeOptions["COOKIE_NCC"], $_COOKIE) &&
			$_COOKIE[$compositeOptions["COOKIE_NCC"]] === "Y"
		)
		{
			self::$error = Logger::TYPE_NCC_COOKIE;
			return false;
		}

		//A stored authorization exists, but CC cookie doesn't exist
		if (
			isset($compositeOptions["STORE_PASSWORD"]) &&
			$compositeOptions["STORE_PASSWORD"] == "Y" &&
			isset($_COOKIE[$compositeOptions["COOKIE_LOGIN"]]) &&
			$_COOKIE[$compositeOptions["COOKIE_LOGIN"]] !== "" &&
			isset($_COOKIE[$compositeOptions["COOKIE_PASS"]]) &&
			$_COOKIE[$compositeOptions["COOKIE_PASS"]] !== ""
		)
		{
			if (
				!isset($compositeOptions["COOKIE_CC"]) ||
				!array_key_exists($compositeOptions["COOKIE_CC"], $_COOKIE) ||
				$_COOKIE[$compositeOptions["COOKIE_CC"]] !== "Y"
			)
			{
				self::$error = Logger::TYPE_CC_COOKIE_NOT_FOUND;
				return false;
			}
		}

		$queryPos = strpos($_SERVER["REQUEST_URI"], "?");
		$requestUri = $queryPos === false ? $_SERVER["REQUEST_URI"] : substr($_SERVER["REQUEST_URI"], 0, $queryPos);

		//Checks excluded masks
		if (isset($compositeOptions["~EXCLUDE_MASK"]) && is_array($compositeOptions["~EXCLUDE_MASK"]))
		{
			foreach ($compositeOptions["~EXCLUDE_MASK"] as $mask)
			{
				if (preg_match($mask, $requestUri) > 0)
				{
					self::$error = Logger::TYPE_EXCLUDE_MASK;
					self::$errorMessage = "Mask: ".$mask;
					return false;
				}
			}
		}

		//Checks excluded GET params
		if (isset($compositeOptions["~EXCLUDE_PARAMS"]) && is_array($compositeOptions["~EXCLUDE_PARAMS"]))
		{
			foreach ($compositeOptions["~EXCLUDE_PARAMS"] as $param)
			{
				if (array_key_exists($param, $_GET))
				{
					self::$error = Logger::TYPE_EXCLUDE_PARAMETER;
					self::$errorMessage = "Parameter: ".$param;
					return false;
				}
			}
		}

		//Checks included masks
		$isRequestInMask = false;
		if (isset($compositeOptions["~INCLUDE_MASK"]) && is_array($compositeOptions["~INCLUDE_MASK"]))
		{
			foreach ($compositeOptions["~INCLUDE_MASK"] as $mask)
			{
				if (preg_match($mask, $requestUri) > 0)
				{
					$isRequestInMask = true;
					break;
				}
			}
		}

		if (!$isRequestInMask)
		{
			self::$error = Logger::TYPE_INCLUDE_MASK;
			return false;
		}

		//Checks hosts
		$host = Helper::getHttpHost();
		if (!in_array($host, Helper::getDomains()))
		{
			self::$error = Logger::TYPE_INVALID_HOST;
			self::$errorMessage = "Host: ".$host;
			return false;
		}

		if (!self::isValidQueryString($compositeOptions))
		{
			self::$error = Logger::TYPE_INVALID_QUERY_STRING;
			return false;
		}

		return true;
	}

	/**
	 * Tries to send a response if cache exists
	 */
	private static function trySendResponse()
	{
		$cacheKey = self::getCacheKey();
		$cache = self::getHtmlCacheResponse($cacheKey);
		if ($cache === null || !$cache->exists())
		{
			return;
		}

		$compositeOptions = Helper::getOptions();

		$etag = $cache->getEtag();
		$lastModified = $cache->getLastModified();
		if ($etag !== false)
		{
			if (array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER) && $_SERVER["HTTP_IF_NONE_MATCH"] === $etag)
			{
				self::setStatus("304 Not Modified");
				self::setHeaders($etag, false, "304");
				die();
			}
		}

		if ($lastModified !== false)
		{
			$sinceModified =
				isset($_SERVER["HTTP_IF_MODIFIED_SINCE"]) ?
				strtotime($_SERVER["HTTP_IF_MODIFIED_SINCE"]) :
				false;

			if ($sinceModified && $sinceModified >= $lastModified)
			{
				self::setStatus("304 Not Modified");
				self::setHeaders($etag, false, "304");
				die();
			}
		}

		$contents = $cache->getContents();
		if ($contents !== false)
		{
			self::setHeaders($etag, $lastModified, "200", $cache->getContentType());

			//compression support
			$compress = "";
			if ($compositeOptions["COMPRESS"] && isset($_SERVER["HTTP_ACCEPT_ENCODING"]))
			{
				if (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "x-gzip") !== false)
				{
					$compress = "x-gzip";
				}
				elseif (strpos($_SERVER["HTTP_ACCEPT_ENCODING"], "gzip") !== false)
				{
					$compress = "gzip";
				}
			}

			if ($compress)
			{
				header("Content-Encoding: ".$compress);
				echo $cache->isGzipped() ? $contents : gzencode($contents, 4);
			}
			else
			{
				if ($cache->isGzipped())
				{
					$contents = Helper::gzdecode($contents);
				}

				header("Content-Length: ".Helper::getBinaryLength($contents));
				echo $contents;
			}

			die();
		}
	}

	private static function modifyHttpHeaders()
	{
		Helper::removeRandParam();

		if (isset($_SERVER["HTTP_BX_REF"]))
		{
			$_SERVER["HTTP_REFERER"] = $_SERVER["HTTP_BX_REF"];
		}
	}

	/**
	 *
	 * Sets HTTP headers
	 *
	 * @param string $etag
	 * @param int $lastModified
	 * @param bool $compositeHeader
	 * @param bool $contentType
	 */
	private static function setHeaders($etag, $lastModified, $compositeHeader = false, $contentType = false)
	{
		if ($etag !== false)
		{
			header("ETag: ".$etag);
		}

		header("Expires: Fri, 07 Jun 1974 04:00:00 GMT");

		if ($lastModified !== false)
		{
			$utc = gmdate("D, d M Y H:i:s", $lastModified)." GMT";
			header("Last-Modified: ".$utc);
		}

		if ($contentType !== false)
		{
			header("Content-type: ".$contentType);
		}

		if ($compositeHeader !== false)
		{
			header("X-Bitrix-Composite: Cache (".$compositeHeader.")");
		}
	}

	/**
	 * Sets HTTP status
	 *
	 * @param string $status
	 */
	private static function setStatus($status)
	{
		$bCgi = (stristr(php_sapi_name(), "cgi") !== false);
		$bFastCgi = ($bCgi && (array_key_exists("FCGI_ROLE", $_SERVER) || array_key_exists("FCGI_ROLE", $_ENV)));
		if ($bCgi && !$bFastCgi)
		{
			header("Status: ".$status);
		}
		else
		{
			header($_SERVER["SERVER_PROTOCOL"]." ".$status);
		}
	}

	private static function isValidQueryString($compositeOptions)
	{
		if (!isset($compositeOptions["INDEX_ONLY"]) || !$compositeOptions["INDEX_ONLY"])
		{
			return true;
		}

		$queryString = "";
		if (isset($_SERVER["REQUEST_URI"]) && ($position = strpos($_SERVER["REQUEST_URI"], "?")) !== false)
		{
			$queryString = substr($_SERVER["REQUEST_URI"], $position + 1);
			$queryString = Helper::removeIgnoredParams($queryString);
		}

		if ($queryString === "")
		{
			return true;
		}

		$queryParams = array();
		parse_str($queryString, $queryParams);
		if (isset($compositeOptions["~GET"]) &&
			!empty($compositeOptions["~GET"]) &&
			count(array_diff(array_keys($queryParams), $compositeOptions["~GET"])) === 0
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * Gets a cache key with a hostname given by $host
	 *
	 * @return string
	 */
	private static function getCacheKey()
	{
		$userPrivateKey = Helper::getUserPrivateKey();

		return Helper::convertUriToPath(
			Helper::getRequestUri(),
			Helper::getHttpHost(),
			Helper::getRealPrivateKey($userPrivateKey)
		);
	}

	/**
	 * Returns the instance of the AbstractResponse
	 *
	 * @param string $cacheKey unique cache identifier
	 *
	 * @return AbstractResponse|null
	 */
	private static function getHtmlCacheResponse($cacheKey)
	{
		$configuration = array();
		$compositeOptions = Helper::getOptions();
		$storage = isset($compositeOptions["STORAGE"]) ? $compositeOptions["STORAGE"] : false;
		if (in_array($storage, array("memcached", "memcached_cluster")))
		{
			if (extension_loaded("memcache"))
			{
				return new MemcachedResponse($cacheKey, $configuration, $compositeOptions);
			}
			else
			{
				return null;
			}
		}
		else
		{
			return new FileResponse($cacheKey, $configuration, $compositeOptions);
		}
	}

	private static function registerAutoloader()
	{
		\spl_autoload_register(array(__CLASS__, "autoLoad"), true);
	}

	private static function unregisterAutoloader()
	{
		\spl_autoload_unregister(array(__CLASS__, "autoLoad"));
	}

	public static function autoLoad($className)
	{
		$className = ltrim($className, "\\"); // fix web env
		if ($className === "Bitrix\\Main\\Composite\\Debug\\Logger")
		{
			require_once(__DIR__."/debug/logger.php");
		}
	}

	/**
	 * @internal
	 * Returns last respond error
	 * @return string
	 */
	public static function getLastError()
	{
		return self::$error;
	}

	/**
	 * @internal
	 * Returns last error message
	 * @return string
	 */
	public static function getLastErrorMessage()
	{
		return self::$errorMessage;
	}

	private static function setErrorHandler()
	{
		set_error_handler(array(__CLASS__, "handleError"));
	}

	private static function restoreErrorHandler()
	{
		restore_error_handler();
	}

	public static function handleError($code, $message, $file, $line)
	{
		return true;
	}
}

/**
 * Represents interface for the html cache response
 * Class AbstractResponse
 */
abstract class AbstractResponse
{
	protected $cacheKey = null;
	protected $configuration = array();
	protected $htmlCacheOptions = array();

	/**
	 * @param string $cacheKey unique cache identifier
	 * @param array $configuration storage configuration
	 * @param array $htmlCacheOptions html cache options
	 */
	public function __construct($cacheKey, array $configuration, array $htmlCacheOptions)
	{
		$this->cacheKey = $cacheKey;
		$this->configuration = $configuration;
		$this->htmlCacheOptions = $htmlCacheOptions;
	}

	/**
	 * Returns the cache contents
	 * @return string|false
	 */
	abstract public function getContents();

	/**
	 * Returns true if content is gzipped
	 * @return bool
	 */
	abstract public function isGzipped();

	/**
	 * Returns the time the cache was last modified
	 * @return int|false
	 */
	abstract public function getLastModified();

	/**
	 * Returns the Entity Tag of the cache
	 * @return string|int
	 */
	abstract public function getEtag();

	/**
	 * Returns the content type of the cache
	 * @return string|false
	 */
	abstract public function getContentType();

	/**
	 * Checks whether the cache exists
	 *
	 * @return bool
	 */
	abstract public function exists();

	/**
	 * Should we count a quota limit
	 * @return bool
	 */
	abstract public function shouldCountQuota();
}

final class MemcachedResponse extends AbstractResponse
{
	/**
	 * @var \stdClass
	 */
	private $props = null;

	/**
	 * @var \Memcache
	 */
	private static $memcached = null;
	private static $connected = null;
	private $contents = null;
	private $flags = 0;

	const MEMCACHED_GZIP_FLAG = 65536;

	public function __construct($cacheKey, array $configuration, array $htmlCacheOptions)
	{
		parent::__construct($cacheKey, $configuration, $htmlCacheOptions);
		self::getConnection($configuration, $htmlCacheOptions);
	}

	public function getContents()
	{
		if (self::$memcached === null)
		{
			return false;
		}

		if ($this->contents === null)
		{
			$this->contents = self::$memcached->get($this->cacheKey, $this->flags);
		}

		return $this->contents;
	}

	public function getLastModified()
	{
		return $this->getProp("mtime");
	}

	public function getEtag()
	{
		return $this->getProp("etag");
	}

	public function getContentType()
	{
		return $this->getProp("type");
	}

	public function exists()
	{
		return $this->getProps() !== false;
	}

	/**
	 * Returns true if content is gzipped
	 * @return bool
	 */
	public function isGzipped()
	{
		$this->getContents();

		return ($this->flags & self::MEMCACHED_GZIP_FLAG) === self::MEMCACHED_GZIP_FLAG;
	}

	/**
	 * Should we count a quota limit
	 * @return bool
	 */
	public function shouldCountQuota()
	{
		return false;
	}

	/**
	 * @param array $htmlCacheOptions html cache options
	 *
	 * @return array
	 */
	private static function getServers(array $htmlCacheOptions)
	{
		$arServers = array();
		if ($htmlCacheOptions["STORAGE"] === "memcached_cluster")
		{
			$groupId = isset($htmlCacheOptions["MEMCACHED_CLUSTER_GROUP"])
				? $htmlCacheOptions["MEMCACHED_CLUSTER_GROUP"] : 1;
			$arServers = self::getClusterServers($groupId);
		}
		elseif (isset($htmlCacheOptions["MEMCACHED_HOST"]) && isset($htmlCacheOptions["MEMCACHED_PORT"]))
		{
			$arServers[] = array(
				"HOST" => $htmlCacheOptions["MEMCACHED_HOST"],
				"PORT" => $htmlCacheOptions["MEMCACHED_PORT"]
			);
		}

		return $arServers;
	}

	/**
	 * Gets clusters settings
	 *
	 * @param int $groupId
	 *
	 * @return array
	 */
	private static function getClusterServers($groupId)
	{
		$arServers = array();

		$arList = false;
		if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/memcache.php"))
		{
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/cluster/memcache.php");
		}

		if (defined("BX_MEMCACHE_CLUSTER") && is_array($arList))
		{
			foreach ($arList as $arServer)
			{
				if ($arServer["STATUS"] === "ONLINE" && $arServer["GROUP_ID"] == $groupId)
				{
					$arServers[] = $arServer;
				}
			}
		}

		return $arServers;
	}

	/**
	 * Returns the object that represents the connection to the memcached server
	 *
	 * @param array $configuration memcached configuration
	 * @param array $htmlCacheOptions html cache options
	 *
	 * @return \Memcache|false
	 */
	public static function getConnection(array $configuration, array $htmlCacheOptions)
	{
		if (self::$memcached === null && self::$connected === null)
		{
			$arServers = self::getServers($htmlCacheOptions);
			$memcached = new \Memcache;
			if (count($arServers) === 1)
			{
				if ($memcached->connect($arServers[0]["HOST"], $arServers[0]["PORT"]))
				{
					self::$connected = true;
					self::$memcached = $memcached;
					register_shutdown_function(array(__CLASS__, "close"));
				}
				else
				{
					self::$connected = false;
				}
			}
			elseif (count($arServers) > 1)
			{
				self::$memcached = $memcached;
				foreach ($arServers as $arServer)
				{
					self::$memcached->addServer(
						$arServer["HOST"],
						$arServer["PORT"],
						true, //persistent
						($arServer["WEIGHT"] > 0 ? $arServer["WEIGHT"] : 1),
						1 //timeout
					);
				}
			}
			else
			{
				self::$connected = false;
			}
		}

		return self::$memcached;
	}

	/**
	 * Closes connection to the memcached server
	 */
	public static function close()
	{
		if (self::$memcached !== null)
		{
			self::$memcached->close();
			self::$memcached = null;
		}
	}

	/**
	 * Returns an array of the cache properties
	 *
	 * @return \stdClass|false
	 */
	public function getProps()
	{
		if ($this->props === null)
		{
			if (self::$memcached !== null)
			{
				$props = self::$memcached->get("~".$this->cacheKey);
				$this->props = is_object($props) ? $props : false;
			}
			else
			{
				$this->props = false;
			}
		}

		return $this->props;
	}

	/**
	 * Returns the $property value
	 *
	 * @param string $property the property name
	 *
	 * @return string|false
	 */
	public function getProp($property)
	{
		$props = $this->getProps();
		if ($props !== false && isset($props->{$property}))
		{
			return $props->{$property};
		}

		return false;
	}
}

final class FileResponse extends AbstractResponse
{
	private $cacheFile = null;
	private $lastModified = null;
	private $contents = null;

	public function __construct($cacheKey, array $configuration, array $htmlCacheOptions)
	{
		parent::__construct($cacheKey, $configuration, $htmlCacheOptions);
		$pagesPath = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/html_pages";

		if (file_exists($pagesPath.$this->cacheKey))
		{
			$this->cacheFile = $pagesPath.$this->cacheKey;
		}
	}

	public function getContents()
	{
		if ($this->cacheFile === null)
		{
			return false;
		}

		if ($this->contents === null)
		{
			$this->contents = file_get_contents($this->cacheFile);
			if ($this->contents !== false &&
				(strlen($this->contents) < 2500 || !preg_match("/^[a-f0-9]{32}$/", substr($this->contents, -35, 32)))
			)
			{
				$this->contents = false;
			}
		}

		return $this->contents;
	}

	public function getLastModified()
	{
		if ($this->cacheFile === null)
		{
			return false;
		}

		if ($this->lastModified === null)
		{
			$this->lastModified = filemtime($this->cacheFile);
		}

		return $this->lastModified;

	}

	public function getEtag()
	{
		if ($this->cacheFile === null)
		{
			return false;
		}

		return md5(
			$this->cacheFile.filesize($this->cacheFile).$this->getLastModified()
		);
	}

	public function getContentType()
	{
		$contents = $this->getContents();
		$head = strpos($contents, "</head>");
		$meta = "#<meta.*?charset\\s*=\\s*(?:[\"']?)([^\"'>]+)#im";

		if ($head !== false && preg_match($meta, substr($contents, 0, $head), $match))
		{
			return "text/html; charset=".$match[1];
		}

		return false;
	}

	public function exists()
	{
		return $this->cacheFile !== null;
	}

	/**
	 * Should we count a quota limit
	 * @return bool
	 */
	public function shouldCountQuota()
	{
		return true;
	}

	/**
	 * Returns true if content is gzipped
	 * @return bool
	 */
	public function isGzipped()
	{
		return false;
	}
}

class_alias("Bitrix\\Main\\Composite\\AbstractResponse", "StaticHtmlCacheResponse");
class_alias("Bitrix\\Main\\Composite\\MemcachedResponse", "StaticHtmlMemcachedResponse");
class_alias("Bitrix\\Main\\Composite\\FileResponse", "StaticHtmlFileResponse");