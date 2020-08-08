<?php

class CMailDomain2
{

	public function __construct()
	{
	}

	public static function getImapData()
	{
		$result = CMailYandex2::getImapData();

		return array(
			'server' => $result['server'],
			'port'   => $result['port'],
			'secure' => $result['secure'] ? 'Y' : 'N',
		);
	}

	public static function addDomain($token, $domain, &$error)
	{
		$result = CMailYandex2::addDomain($token, $domain, $error);

		if ($result !== false)
		{
			return array(
				'domain'  => $result['domain'],
				'stage'   => $result['stage'],
				'secrets' => $result['secrets']
			);
		}
		else
		{
			$error = self::getErrorCode2($error);
			return null;
		}
	}

	public static function getDomainStatus($token, $domain, &$error)
	{
		$result = CMailYandex2::getDomainStatus($token, $domain, $error);

		if ($result !== false)
		{
			return array(
				'domain' => $result['domain'],
				'stage'  => $result['stage']
			);
		}
		else
		{
			$error = self::getErrorCode2($error);
			return null;
		}
	}

	public static function checkDomainStatus($token, $domain, &$error)
	{
		$result = CMailYandex2::checkDomainStatus($token, $domain, $error);

		if ($result !== false)
		{
			return array(
				'domain'     => $result['domain'],
				'stage'      => $result['stage'],
				'secrets'    => $result['secrets'],
				'last_check' => $result['last_check'],
				'next_check' => $result['next_check']
			);
		}
		else
		{
			$error = self::getErrorCode2($error);
			return null;
		}
	}

	public static function deleteDomain($token, $domain, &$error)
	{
		$result = CMailYandex2::deleteDomain($token, $domain, $error);

		if ($result !== false)
		{
			return true;
		}
		else
		{
			$error = self::getErrorCode2($error);
			return null;
		}
	}

	public static function isUserExists($token, $domain, $login, &$error)
	{
		$result = CMailYandex2::checkUser($token, $domain, $login, $error);

		switch ($result)
		{
			case 'exists':
				return true;
			case 'nouser':
				return false;
			default:
				$error = self::getErrorCode($error);
				return null;
		}
	}

	public static function addUser($token, $domain, $login, $password, &$error)
	{
		$domain = trim(mb_strtolower($domain));
		$login  = trim(mb_strtolower($login));

		if (empty($domain))
		{
			$error = self::getErrorCode('no_domain');
			return null;
		}

		if (empty($login))
		{
			$error = self::getErrorCode('no_login');
			return null;
		}

		$result = CMailYandex2::addUser($token, $domain, $login, $password, $error);

		if ($result !== false)
		{
			\Bitrix\Mail\Internals\DomainEmailTable::add(array(
				'DOMAIN' => $domain,
				'LOGIN'  => $login,
			));

			// @TODO: temporary fix for simple passwords
			\CMailYandex2::editUser($token, $domain, $login, array('password' => $password), $dummy);

			return true;
		}
		else
		{
			$error = self::getErrorCode($error);
			return null;
		}
	}

	public static function getRedirectUrl($locale, $token, $domain, $login, $errorUrl, &$error)
	{
		$result = CMailYandex2::getOAuthToken($token, $domain, $login, $error);

		if ($result !== false)
		{
			return CMailYandex2::passport($locale, $result, $errorUrl);
		}
		else
		{
			$error = self::getErrorCode($error);
			return null;
		}
	}

	public static function getUnreadMessagesCount($token, $domain, $login, &$error)
	{
		$result = CMailYandex2::getMailInfo($token, $domain, $login, $error);

		if ($result !== false)
		{
			return intval($result);
		}
		else
		{
			$error = self::getErrorCode($error);
			return null;
		}
	}

	public static function changePassword($token, $domain, $login, $password, &$error)
	{
		$result = CMailYandex2::editUser($token, $domain, $login, array('password' => $password), $error);

		if ($result !== false)
		{
			return true;
		}
		else
		{
			$error = self::getErrorCode($error);
			return null;
		}
	}

	public static function getDomainUsers($token, $domain, &$error, $resync = false)
	{
		global $DB;

		$domain = trim(mb_strtolower($domain));
		$users = array();

		if (empty($domain))
		{
			$error = self::getErrorCode('no_domain');
			return null;
		}

		if (!$resync)
		{
			$res = \Bitrix\Mail\Internals\DomainEmailTable::getList(array(
				'filter' => array(
					'=DOMAIN' => $domain,
				),
			));
			if ($res !== false)
			{
				while ($item = $res->fetch())
					$users[] = $item['LOGIN'];
			}
		}

		if ($resync || empty($users))
		{
			$users = array();

			$page = 0;
			do
			{
				$result = CMailYandex2::getDomainUsers($token, $domain, $per_page = 30, ++$page, $error);

				if ($result === false)
					break;

				foreach ($result['accounts'] as $email)
				{
					list($login, $emailDomain) = explode('@', $email['login'], 2);
					if ($emailDomain == $domain)
						$users[] = trim(mb_strtolower($login));
				}
			}
			while ($result['total'] > $per_page*$page);

			$users = array_unique($users);

			if (!$error)
			{
				$cached = array();

				$res = \Bitrix\Mail\Internals\DomainEmailTable::getList(array(
					'filter' => array(
						'=DOMAIN' => $domain,
					),
				));
				if ($res !== false)
				{
					while ($item = $res->fetch())
						$cached[] = $item['LOGIN'];
				}

				foreach (array_diff($cached, $users) as $login)
				{
					\Bitrix\Mail\Internals\DomainEmailTable::delete(array(
						'DOMAIN' => $domain,
						'LOGIN'  => $login,
					));
				}
				foreach (array_diff($users, $cached) as $login)
				{
					\Bitrix\Mail\Internals\DomainEmailTable::add(array(
						'DOMAIN' => $domain,
						'LOGIN'  => $login,
					));
				}
			}
		}

		if (empty($users) && $error)
		{
			$error = self::getErrorCode($error);
			return null;
		}
		else
		{
			sort($users);
			return $users;
		}
	}

	public static function setDomainLogo($token, $domain, $logo, $replace, &$error)
	{
		if (!$replace)
		{
			$result = CMailYandex2::checkLogo($token, $domain, $error);

			if ($result !== false)
			{
				if (preg_match('~^https?://avatars\.yandex\.net/get-for-domain/~i', $result))
					return false;
			}
			else
			{
				$error = self::getErrorCode2($error);
				return null;
			}
		}

		$result = CMailYandex2::setLogo($token, $domain, $logo, $error);

		if ($result !== false)
		{
			return $result;
		}
		else
		{
			$error = self::getErrorCode2($error);
			return null;
		}
	}

	public static function selLocale($token, $domain, $locale, &$error)
	{
		$result = CMailYandex2::setCountry($token, $domain, $locale, $error);

		if ($result !== false)
		{
			return true;
		}
		else
		{
			$error = self::getErrorCode2($error);
			return null;
		}
	}

	public static function deleteUser($token, $domain, $login, &$error)
	{
		$domain = trim(mb_strtolower($domain));
		$login  = trim(mb_strtolower($login));

		if (empty($domain))
		{
			$error = self::getErrorCode('no_domain');
			return null;
		}

		if (empty($login))
		{
			$error = self::getErrorCode('no_login');
			return null;
		}

		$result = CMailYandex2::deleteUser($token, $domain, $login, $error);

		if ($result !== false)
		{
			\Bitrix\Mail\Internals\DomainEmailTable::delete(array(
				'DOMAIN' => $domain,
				'LOGIN'  => $login,
			));

			return true;
		}
		else
		{
			$error = self::getErrorCode($error);
			return null;
		}
	}

	private static function getErrorCode($error)
	{
		$errorsList = array(
			'unknown'           => CMail::ERR_API_DEFAULT,
			'no_token'          => CMail::ERR_API_DENIED,
			'no_domain'         => CMail::ERR_API_EMPTY_DOMAIN,
			'no_ip'             => CMail::ERR_API_DEFAULT,
			'bad_domain'        => CMail::ERR_API_BAD_DOMAIN,
			'prohibited'        => CMail::ERR_API_PROHIBITED_DOMAIN,
			'bad_token'         => CMail::ERR_API_DENIED,
			'no_auth'           => CMail::ERR_API_DENIED,
			'bad_oauth'         => CMail::ERR_API_DENIED,
			'not_allowed'       => CMail::ERR_API_DENIED,
			'blocked'           => CMail::ERR_API_DENIED,
			'no_uid_or_login'   => CMail::ERR_API_EMPTY_NAME,
			'passwd-tooshort'   => CMail::ERR_API_SHORT_PASSWORD,
			'passwd-toolong'    => CMail::ERR_API_LONG_PASSWORD,
			'badpasswd'         => CMail::ERR_API_BAD_PASSWORD,
			'user_blocked'      => CMail::ERR_API_DENIED,
			'account_not_found' => CMail::ERR_API_USER_NOTFOUND,
			'no_login'          => CMail::ERR_API_EMPTY_NAME,
			'no_password'       => CMail::ERR_API_EMPTY_PASSWORD,
			'occupied'          => CMail::ERR_API_NAME_OCCUPIED,
			'login_reserved'    => CMail::ERR_API_NAME_OCCUPIED,
			'login-empty'       => CMail::ERR_API_EMPTY_NAME,
			'passwd-empty'      => CMail::ERR_API_EMPTY_PASSWORD,
			'login-toolong'     => CMail::ERR_API_LONG_NAME,
			'badlogin'          => CMail::ERR_API_BAD_NAME,
			'not_master_admin'  => CMail::ERR_API_DENIED,
			'get_new_token_please' => CMail::ERR_API_OLD_TOKEN,
		);

		$error = explode(',', $error);
		$error = trim($error[count($error)-1]);

		return array_key_exists($error, $errorsList) ? $errorsList[$error] : CMail::ERR_API_DEFAULT;
	}

	private static function getErrorCode2($error)
	{
		$errorsList = array(
			'unknown'           => CMail::ERR_API_DEFAULT,
			'no_token'          => CMail::ERR_API_DENIED,
			'no_domain'         => CMail::ERR_API_EMPTY_DOMAIN,
			'no_ip'             => CMail::ERR_API_DEFAULT,
			'bad_domain'        => CMail::ERR_API_BAD_DOMAIN,
			'prohibited'        => CMail::ERR_API_PROHIBITED_DOMAIN,
			'bad_token'         => CMail::ERR_API_DENIED,
			'no_auth'           => CMail::ERR_API_DENIED,
			'bad_oauth'         => CMail::ERR_API_DENIED,
			'not_allowed'       => CMail::ERR_API_DENIED,
			'blocked'           => CMail::ERR_API_DENIED,
			'occupied'          => CMail::ERR_API_DOMAIN_OCCUPIED,
			'not_master_admin'  => CMail::ERR_API_DENIED,
			'bad_country'       => CMail::ERR_API_DEFAULT,
			'status_none'       => CMail::ERR_API_DEFAULT,
			'get_new_token_please' => CMail::ERR_API_OLD_TOKEN,
		);

		$error = explode(',', $error);
		$error = trim($error[count($error)-1]);

		return array_key_exists($error, $errorsList) ? $errorsList[$error] : CMail::ERR_API_DEFAULT;
	}

}
