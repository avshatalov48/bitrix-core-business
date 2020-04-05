<?php
namespace Bitrix\Main\Web;

use Bitrix\Main\Config;

class Cookie
{
	const SPREAD_SITES = 1;
	const SPREAD_DOMAIN = 2;

	protected $domain;
	protected $expires;
	protected $httpOnly = true;
	protected $spread;
	protected $name;
	protected $path = '/';
	protected $secure = false;
	protected $value;

	/**
	 * Cookie constructor.
	 * @param string $name The cooke name
	 * @param string|null $value The cooke value
	 * @param int $expires Timestamp
	 * @param bool $addPrefix Name prefix, usually BITRIX_SM_
	 */
	public function __construct($name, $value, $expires = null, $addPrefix = true)
	{
		if($addPrefix)
		{
			$this->name = static::generateCookieName($name);
		}
		else
		{
			$this->name = $name;
		}
		$this->value = $value;
		$this->expires = $expires;
		if ($this->expires === null)
		{
			$this->expires = time() + 31104000; //60*60*24*30*12;
		}
		$this->path = "/";
		$this->spread = static::SPREAD_DOMAIN | static::SPREAD_SITES;
		$this->setDefaultsFromConfig();
	}

	protected static function generateCookieName($name)
	{
		static $cookiePrefix = null;

		if($cookiePrefix === null)
		{
			$cookiePrefix = Config\Option::get("main", "cookie_name", "BITRIX_SM")."_";
		}
		if (strpos($name, $cookiePrefix) !== 0)
		{
			$name = $cookiePrefix.$name;
		}
		return $name;
	}

	protected function setDefaultsFromConfig()
	{
		$cookiesSettings = Config\Configuration::getValue("cookies");

		$this->secure = (isset($cookiesSettings["secure"])? $cookiesSettings["secure"] : false);
		$this->httpOnly = (isset($cookiesSettings["http_only"])? $cookiesSettings["http_only"] : true);
	}

	public function setDomain($domain)
	{
		$this->domain = $domain;
		return $this;
	}

	public function getDomain()
	{
		if ($this->domain === null)
		{
			$this->domain = static::getCookieDomain();
		}

		return $this->domain;
	}

	public function setExpires($expires)
	{
		$this->expires = $expires;
		return $this;
	}

	public function getExpires()
	{
		return $this->expires;
	}

	public function setHttpOnly($httpOnly)
	{
		$this->httpOnly = $httpOnly;
		return $this;
	}

	public function getHttpOnly()
	{
		return $this->httpOnly;
	}

	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	public function getPath()
	{
		return $this->path;
	}

	public function setSecure($secure)
	{
		$this->secure = $secure;
		return $this;
	}

	public function getSecure()
	{
		return $this->secure;
	}

	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	public function getValue()
	{
		return $this->value;
	}

	public function setSpread($spread)
	{
		$this->spread = $spread;
		return $this;
	}

	public function getSpread()
	{
		return $this->spread;
	}

	/**
	 * Returns the domain from the sites settings to use with cookies.
	 *
	 * @return string
	 * @throws \Bitrix\Main\Db\SqlQueryException
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function getCookieDomain()
	{
		static $domain = null;

		if($domain !== null)
		{
			return $domain;
		}

		$server = \Bitrix\Main\Context::getCurrent()->getServer();

		$cacheFlags = Config\Configuration::getValue("cache_flags");
		$cacheTtl = (isset($cacheFlags["site_domain"]) ? $cacheFlags["site_domain"] : 0);

		if ($cacheTtl === false)
		{
			$connection = \Bitrix\Main\Application::getConnection();
			$sqlHelper = $connection->getSqlHelper();

			$sql = "SELECT DOMAIN ".
				"FROM b_lang_domain ".
				"WHERE '".$sqlHelper->forSql('.'.$server->getHttpHost())."' like ".$sqlHelper->getConcatFunction("'%.'", "DOMAIN")." ".
				"ORDER BY ".$sqlHelper->getLengthFunction("DOMAIN")." ";
			$recordset = $connection->query($sql);
			if ($record = $recordset->fetch())
			{
				$domain = $record['DOMAIN'];
			}
		}
		else
		{
			$managedCache = \Bitrix\Main\Application::getInstance()->getManagedCache();

			if ($managedCache->read($cacheTtl, "b_lang_domain", "b_lang_domain"))
			{
				$arLangDomain = $managedCache->get("b_lang_domain");
			}
			else
			{
				$arLangDomain = array("DOMAIN" => array(), "LID" => array());

				$connection = \Bitrix\Main\Application::getConnection();
				$sqlHelper = $connection->getSqlHelper();

				$recordset = $connection->query(
					"SELECT * ".
					"FROM b_lang_domain ".
					"ORDER BY ".$sqlHelper->getLengthFunction("DOMAIN")
				);
				while ($record = $recordset->fetch())
				{
					//it's a bit tricky, the cache is used somewhere else, that's why we have the LID key here.
					$arLangDomain["DOMAIN"][] = $record;
					$arLangDomain["LID"][$record["LID"]][] = $record;
				}
				$managedCache->set("b_lang_domain", $arLangDomain);
			}

			foreach ($arLangDomain["DOMAIN"] as $record)
			{
				if (strcasecmp(substr('.'.$server->getHttpHost(), -(strlen($record['DOMAIN']) + 1)), ".".$record['DOMAIN']) == 0)
				{
					$domain = $record['DOMAIN'];
					break;
				}
			}
		}

		if($domain === null)
		{
			$domain = "";
		}

		return $domain;
	}
}
