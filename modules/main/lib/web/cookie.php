<?php

namespace Bitrix\Main\Web;

use Bitrix\Main\Config;
use Bitrix\Main\SiteDomainTable;

class Cookie extends Http\Cookie
{
	public const SPREAD_SITES = 1;
	public const SPREAD_DOMAIN = 2;

	protected $spread;
	protected $originalName;

	/**
	 * Cookie constructor.
	 * @param string $name The cooke name
	 * @param string|null $value The cooke value
	 * @param int $expires Timestamp
	 * @param bool $addPrefix Name prefix, usually BITRIX_SM_
	 */
	public function __construct($name, $value, $expires = null, $addPrefix = true)
	{
		$this->name = ($addPrefix ? static::generateCookieName($name) : $name);
		$this->originalName = $name;
		$this->value = $value;
		$this->expires = ($expires === null ? time() + 31104000 : $expires); //60*60*24*30*12
		$this->spread = static::SPREAD_DOMAIN | static::SPREAD_SITES;
		$this->setDefaultsFromConfig();
	}

	protected static function generateCookieName($name)
	{
		static $cookiePrefix = null;

		if ($cookiePrefix === null)
		{
			$cookiePrefix = Config\Option::get("main", "cookie_name", "BITRIX_SM") . "_";
			$cookiePrefix = static::normalizeName($cookiePrefix);
		}
		if (!str_starts_with($name, $cookiePrefix))
		{
			$name = $cookiePrefix . $name;
		}

		return $name;
	}

	protected function setDefaultsFromConfig()
	{
		$cookiesSettings = Config\Configuration::getValue('cookies');

		$this->secure = ($cookiesSettings['secure'] ?? false);
		$this->httpOnly = ($cookiesSettings['http_only'] ?? true);
		if (isset($cookiesSettings['samesite']))
		{
			$this->sameSite = $cookiesSettings['samesite'];
		}
	}

	public function getDomain(): ?string
	{
		if ($this->domain === null)
		{
			$this->domain = static::getCookieDomain();
		}

		return $this->domain;
	}

	public function getOriginalName(): string
	{
		return $this->originalName;
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

		if ($domain !== null)
		{
			return $domain;
		}

		$request = \Bitrix\Main\Context::getCurrent()->getRequest();
		$httpHost = $request->getHttpHost();

		$recordset = SiteDomainTable::getList([
			'order' => ['DOMAIN_LENGTH' => 'ASC'],
			'cache' => ['ttl' => 86400],
		]);

		while ($record = $recordset->fetch())
		{
			if (strcasecmp(mb_substr('.' . $httpHost, -(mb_strlen($record['DOMAIN']) + 1)), "." . $record['DOMAIN']) == 0)
			{
				$domain = $record['DOMAIN'];

				return $domain;
			}
		}

		$domain = '';

		return $domain;
	}

	/**
	 * Normalizes a name for a cookie.
	 * @param string $name
	 * @return string
	 */
	public static function normalizeName(string $name): string
	{
		// cookie name cannot contain "=", ",", ";", " ", "\t", "\r", "\n", "\013", or "\014"
		return preg_replace("/[=,; \\t\\r\\n\\013\\014]/", '', $name);
	}
}
