<?php

class CMailDomainRegistrar
{
	/**
	 * Allowed registrar classes by zones.
	 */
	const REGISTRAR_CLASSES = [
		'ru' => '\Bitrix\Mail\Registrar\RegRu',
		'ua' => '\Bitrix\Mail\Registrar\Omnilance'
	];

	/**
	 * Current registrar class.
	 * @var string
	 */
	private static $classRegistrar = '\Bitrix\Mail\Registrar\RegRu';

	/**
	 * Sets new class registrar.
	 * @param string $className Class name.
	 * @return void
	 */
	public static function setRegistrarClass(string $className): void
	{
		if (in_array($className, self::REGISTRAR_CLASSES))
		{
			self::$classRegistrar = $className;
		}
	}

	/**
	 * Checks domain available.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string $domain Domain name.
	 * @param string|null &$error Error message if occurred.
	 * @return bool|null Returns true if domain exists.
	 */
	public static function isDomainExists(string $user, string $password, string $domain, ?string &$error): ?bool
	{
		$result = self::$classRegistrar::checkDomain($user, $password, $domain, $error);

		if ($result === null)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Suggests domains by query words.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string $word1 Query word 1.
	 * @param string $word2 Query word 2.
	 * @param array $tlds Query tlds.
	 * @param string|null &$error Error message if occurred.
	 * @return array|null
	 */
	public static function suggestDomain(string $user, string $password, ?string $word1, ?string $word2, array $tlds, ?string &$error): ?array
	{
		$result = self::$classRegistrar::suggestDomain($user, $password, $word1, $word2, $tlds, $error);

		if ($result === null)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Creates new domain.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string $domain Domain name.
	 * @param array $params Additional params.
	 * @param string|null &$error Error message if occurred.
	 * @return bool|null Returns true on success.
	 */
	public static function createDomain(string $user, string $password, string $domain, array $params, ?string &$error): ?bool
	{
		$result = self::$classRegistrar::createDomain($user, $password, $domain, $params, $error);

		if ($result === null)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			return $result;
		}
	}

	public static function checkDomain($user, $password, $domain, &$error)
	{
		$result = CMailRegru::checkDomainInfo($user, $password, $domain, $error);

		if ($result !== false)
		{
			if (isset($result['dname']) && mb_strtolower($result['dname']) == mb_strtolower($domain))
				return $result;
			else
				$error = 'unknown';
		}

		$error = self::getErrorCode($result['error_code']);
		return null;
	}

	/**
	 * Renews exists domain.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string $domain Domain name.
	 * @param string|null &$error Error message if occurred.
	 * @return bool|null Returns true on success.
	 */
	public static function renewDomain(string $user, string $password, string $domain, ?string &$error): ?bool
	{
		$result = self::$classRegistrar::renewDomain($user, $password, $domain, $error);

		if ($result === null)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Updates domain DNS.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string $domain Domain name.
	 * @param array $params Additional params.
	 * @param string|null &$error Error message if occurred.
	 * @return bool|null Returns true on success.
	 */
	public static function updateDns(string $user, string $password, string $domain, array $params, ?string &$error): ?bool
	{
		$result = self::$classRegistrar::updateDns($user, $password, $domain, $params, $error);

		if ($result === null)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Returns domain's list.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param array $filter Filter array (not used).
	 * @param string|null &$error Error message if occurred.
	 * @return array|null Returns true on success.
	 */
	public static function getDomainsList(string $user, string $password, $filter = array(), &$error): ?array
	{
		$result = self::$classRegistrar::getDomainsList($user, $password, $error);

		if ($result === null)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			return $result;
		}
	}

	/**
	 * Returns standard mail error code by error.
	 * @param string|null $error Error.
	 * @return int
	 */
	private static function getErrorCode(?string $error): int
	{
		$errorsList = [
			'unknown'                      => CMail::ERR_API_DEFAULT,
			'INVALID_DOMAIN_NAME_PUNYCODE' => CMail::ERR_API_DEFAULT,
			'TLD_DISABLED'                 => CMail::ERR_API_DEFAULT,
			'DOMAIN_BAD_NAME'              => CMail::ERR_API_DEFAULT,
			'INVALID_DOMAIN_NAME_FORMAT'   => CMail::ERR_API_DEFAULT,
			'DOMAIN_INVALID_LENGTH'        => CMail::ERR_API_DEFAULT,
			'HAVE_MIXED_CODETABLES'        => CMail::ERR_API_DEFAULT
		];

		return array_key_exists($error, $errorsList) ? $errorsList[$error] : CMail::ERR_API_DEFAULT;
	}
}
