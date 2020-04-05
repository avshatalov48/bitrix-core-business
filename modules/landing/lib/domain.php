<?php
namespace Bitrix\Landing;

class Domain extends \Bitrix\Landing\Internals\BaseTable
{
	/**
	 * Internal class.
	 * @var string
	 */
	public static $internalClass = 'DomainTable';

	/**
	 * Gets domain name.
	 * @return string.
	 */
	protected static function getDomainName()
	{
		static $domain = null;

		if (!$domain)
		{
			$context = \Bitrix\Main\Application::getInstance()->getContext();
			$server = $context->getServer();
			$domain = $server->getServerName();
			if (!$domain)
			{
				$domain = $server->getHttpHost();
			}
		}

		return $domain;
	}

	/**
	 * Create current domain and return new id..
	 * @return int
	 */
	public static function createDefault()
	{
		$res = self::add(array(
			'ACTIVE' => 'Y',
			'DOMAIN' => self::getDomainName()
		));
		if ($res->isSuccess())
		{
			return $res->getId();
		}

		return false;
	}

	/**
	 * Get current domain id.
	 * @return int
	 */
	public static function getCurrentId()
	{
		$res = self::getList(array(
			'filter' => array(
				'=ACTIVE' => 'Y',
				'DOMAIN' => self::getDomainName()
			)
		));
		if ($row = $res->fetch())
		{
			return $row['ID'];
		}
		else
		{
			return self::createDefault();
		}
	}

	/**
	 * Get available protocol list.
	 * @return array
	 */
	public static function getProtocolList()
	{
		return \Bitrix\Landing\Internals\DomainTable::getProtocolList();
	}
}