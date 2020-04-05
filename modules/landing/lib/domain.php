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
	 * Create current domain and return new id..
	 * @return int
	 */
	public static function createDefault()
	{
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$server = $context->getServer();
		$res = self::add(array(
			'ACTIVE' => 'Y',
			'DOMAIN' => $server->getServerName()
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
		$context = \Bitrix\Main\Application::getInstance()->getContext();
		$server = $context->getServer();

		$res = self::getList(array(
			'filter' => array(
				'=ACTIVE' => 'Y',
				'DOMAIN' => $server->getServerName()
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