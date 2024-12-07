<?php
namespace Bitrix\Mail\Registrar;

class RegRu extends Registrar
{
	/**
	 * Checks domain available.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string $domain Domain name.
	 * @param string|null &$error Error message if occurred.
	 * @return bool|null Returns true if domain exists.
	 */
	public static function checkDomain(string $user, string $password, string $domain, ?string &$error): ?bool
	{
		$domain = mb_strtolower($domain);

		$result = \CMailRegru::checkDomain($user, $password, $domain, $error);

		if ($result !== false)
		{
			if (
				isset($result['domains'][0]['dname']) &&
			    $result['domains'][0]['dname'] == $domain
			)
			{
				$result = $result['domains'][0];
				if ($result['result'] == 'Available')
				{
					return false;
				}
				else if ($result['error_code'] == 'DOMAIN_ALREADY_EXISTS')
				{
					return true;
				}
				$error = $result['error_code'];
			}
			else
			{
				$error = 'unknown';
			}
		}

		return null;
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
	public static function suggestDomain(string $user, string $password, string $word1, string $word2, array $tlds, ?string &$error): ?array
	{
		$result = \CMailRegru::suggestDomain($user, $password, $word1, $word2, $tlds, $error);

		if ($result !== false)
		{
			$suggestions = array();
			if (!empty($result['suggestions']) && is_array($result['suggestions']))
			{
				foreach ($result['suggestions'] as $entry)
				{
					foreach ($entry['avail_in'] as $tlds)
					{
						$suggestions[] = sprintf('%s.%s', $entry['name'], $tlds);
					}
				}
			}

			return $suggestions;
		}

		return null;
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
		$domain = mb_strtolower($domain);
		$params = array_merge(
			$params,
			array(
				'period' => 1,
				'nss' => array(
					'ns0' => 'ns1.reg.ru.',
					'ns1' => 'ns2.reg.ru.'
				),
			)
		);

		if (array_key_exists('ip', $params))
		{
			$params['enduser_ip'] = $params['ip'];
		}

		$result = \CMailRegru::createDomain($user, $password, $domain, $params, $error);

		if ($result !== false)
		{
			if (isset($result['dname']) && $result['dname'] == $domain)
			{
				return true;
			}
			else
			{
				$error = $result['error_code'] ?? 'unknown';
			}
		}

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
		$result = \CMailRegru::renewDomain($user, $password, $domain, ['period' => 1], $error);

		if ($result !== false)
		{
			if (isset($result['dname']) && mb_strtolower($result['dname']) == mb_strtolower($domain))
			{
				return true;
			}
			else
			{
				$error = 'unknown';
			}
		}

		return null;
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
		$domain = mb_strtolower($domain);

		foreach ($params as $k => $record)
		{
			switch ($record['type'])
			{
				case 'a':
					$params[$k] = array(
						'action' => 'add_alias',
						'subdomain' => '@',
						'ipaddr' => $record['value']
					);
					break;
				case 'alias':
					$params[$k] = array(
						'action' => 'add_alias',
						'subdomain' => $record['name'],
						'ipaddr' => $record['value']
					);
					break;
				case 'cname':
					$params[$k] = array(
						'action'         => 'add_cname',
						'subdomain'      => $record['name'],
						'canonical_name' => $record['value']
					);
					break;
				case 'mx':
					$params[$k] = array(
						'action'      => 'add_mx',
						'subdomain'   => $record['name'],
						'mail_server' => $record['value'],
						'priority'    => $record['priority']
					);
					break;
			}
		}

		$result = \CMailRegru::updateDns($user, $password, $domain, $params, $error);

		if ($result !== false)
		{
			if (isset($result['dname']) && $result['dname'] == $domain)
			{
				if (isset($result['result']) && $result['result'] == 'success')
				{
					return true;
				}
				else
				{
					$error = $result['error_code'] ?? 'unknown';
				}
			}
			else
			{
				$error = 'unknown';
			}
		}

		return null;
	}

	/**
	 * Returns domain's list.
	 * @param string $user User name.
	 * @param string $password User password.
	 * @param string|null &$error Error message if occurred.
	 * @return array|null
	 */
	public static function getDomainsList(string $user, string  $password, ?string &$error): ?array
	{
		$result = \CMailRegru::getDomainsList($user, $password, $error);

		if ($result !== false)
		{
			$list = [];
			foreach ($result as $domain)
			{
				if (!empty($domain['dname']))
				{
					$list[$domain['dname']] = [
						'domain_name' => $domain['dname'],
						'creation_date' => $domain['creation_date'],
						'expiration_date' => $domain['expiration_date'],
						'status' => $domain['state']
					];
				}
			}

			return $list;
		}

		return null;
	}
}
