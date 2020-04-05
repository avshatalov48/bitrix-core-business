<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\Im;

use Bitrix\Main\Loader;
use Bitrix\ImOpenLines;
use Bitrix\ImConnector;

/**
 * Class Service
 * @package Bitrix\Sender\Integration\Im
 */
class Service
{
	/**
	 * Can use.
	 *
	 * @return bool|null
	 */
	public static function canUse()
	{
		if (!Loader::includeModule('im') || !Loader::includeModule('imopenlines') || !Loader::includeModule('imconnector'))
		{
			return false;
		}
		else
		{
			return true;
		}
	}

	/**
	 * Get excluded channel codes.
	 *
	 * @return array
	 */
	public static function getExcludedChannelCodes()
	{
		if (!static::canUse())
		{
			return array();
		}

		static $codes = null;
		if ($codes === null)
		{
			$codes = ImConnector\Connector::getListConnectorNotNewsletter();
		}

		return $codes;
	}

	/**
	 * Send.
	 *
	 * @param string $to To number. Like: livechat|12323|213123, without imol|.
	 * @param string $text Text.
	 * @return bool
	 */
	public static function send($to, $text)
	{
		if (!static::canUse())
		{
			return false;
		}

		$result = ImOpenLines\Im::addMessagesNewsletter(array(
			$to => array('MESSAGE' => $text,	'SYSTEM' => 'Y')
		));

		return isset($result[$to]) ? (bool) $result[$to] : false;
	}
}