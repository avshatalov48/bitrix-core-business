<?php
namespace Bitrix\Landing\External;

use Bitrix\Main\Application;
use Bitrix\Main\SystemException;

class Site24
{
	/**
	 * Update domain by name.
	 * @param string $domain Current domain name.
	 * @param string $newName New domain name.
	 * @param string $url Local url of site.
	 *
	 * @return mixed
	 * @throws SystemException
	 */
	public static function updateDomain($domain, $newName, $url)
	{
		return self::Execute('update', array('domain' => $domain, 'newname' => $newName, 'url' => $url));
	}

	/**
	 * Activate domain by name.
	 * @param string $domain Domain name.
	 * @param string $active Activate (Y) or deactivate (N).
	 * @param string $lang Lang code.
	 *
	 * @return mixed
	 * @throws SystemException
	 */
	public static function activateDomain($domain, $active = 'Y', $lang = '')
	{
		return self::Execute('activate', array('domain' => $domain, 'active' => $active, 'lang' => $lang));
	}

	/**
	 * Add new domain.
	 * @param string $domain Domain name.
	 * @param string $url Local url of site.
	 * @param string string $active Activate (Y) or deactivate (N).
	 * @param string string $type Site type.
	 * @param string $lang Lang code.
	 *
	 * @return mixed
	 * @throws SystemException
	 */
	public static function addDomain($domain, $url, $active = 'Y', $type = 'site', $lang = '')
	{
		return self::Execute('add', array('domain' => $domain, 'url' => $url, 'active' => $active, 'type' => $type, 'lang' => $lang));
    }

	/**
	 * Exist or not domain.
	 * 0 - domain name is available
	 * 1 - domain name is not available, but you are owner
	 * 2 - domain name is not available
	 * @param string $domain Domain name.
	 *
	 * @return mixed
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function isDomainExists($domain)
	{
		return self::Execute('check', array('domain' => $domain));
	}

	/**
	 * Delete domain by name.
	 * @param string $domain Domain name.
	 *
	 * @return mixed
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function deleteDomain($domain)
	{
		return self::Execute('delete', array('domain' => $domain));
	}

	/**
	 * Add domain with random name.
	 * @param string $url Local url of site.
	 * @param string $type Site type.
	 * @param string $lang Lang code.
	 *
	 * @return mixed
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	public static function addRandomDomain($url, $type = 'site', $lang = '')
	{
		return self::Execute('addrandom', array('url' => $url, 'type' => $type, 'lang' => $lang));
	}

	/**
	 * General executable method.
	 * @param string $operation Operation code.
	 * @param array $params Additional params.
	 * @return mixed
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	protected static function Execute($operation, $params = array())
	{
		$params['operation'] = $operation;

		$license = Application::getInstance()->getLicense();
		$params['key'] = $license->getPublicHashKey();
		$params['keysign'] = $license->getHashLicenseKey();
		$params['host']= \Bitrix\Main\Config\Option::get('intranet', 'portal_url', null);

		if (!$params['host'])
		{
			$params['host']= \Bitrix\Main\Config\Option::get(
				'landing',
				'portal_url',
				$_SERVER['HTTP_HOST']
			);
		}

		if (!$params['host'])
		{
			$params['host'] = $_SERVER['HTTP_HOST'];
		}

		$params['host'] = trim($params['host']);

		if (
			mb_strpos($params['host'], 'http://') === 0 ||
			mb_strpos($params['host'], 'https://') === 0
		)
		{
			$parseHost = parse_url($params['host']);
			if (isset($parseHost['host']))
			{
				$params['host'] = $parseHost['host'];
				if (isset($parseHost['port']))
				{
					$params['host'] .= ':' . $parseHost['port'];
				}
			}
		}

		if (!isset($params['lang']) || !$params['lang'])
		{
			unset($params['lang']);
		}

		$httpClient = new \Bitrix\Main\Web\HttpClient(array(
			'socketTimeout' => 5,
			'streamTimeout' => 30
		));

		$httpClient->setHeader('User-Agent', 'Bitrix24 Sites');
		$answer = $httpClient->post('https://pub.bitrix24.site/pub.php', $params);

		$result = '';
		if ($answer && $httpClient->getStatus() == '200')
		{
			$result = $httpClient->getResult();
		}

		if ($result <> '')
		{
			try
			{
				$result = \Bitrix\Main\Web\Json::decode($result);
			}
			catch(\Bitrix\Main\ArgumentException $e)
			{
				throw new SystemException('Bad response');
			}

			if ($result['result'] === 'Bad license')
			{
				throw new SystemException('Bad license');
			}

			return $result['result'];
		}

		throw new SystemException('Bad response');
    }
}