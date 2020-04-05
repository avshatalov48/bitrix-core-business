<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Transport;

use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\CodeBasedFactory;

/**
 * Class Factory
 * @package Bitrix\Sender\Transport
 */
class Factory extends CodeBasedFactory
{
	/**
	 * Get transport instances.
	 *
	 * @return iBase[]
	 */
	public static function getTransports()
	{
		return static::getObjectInstances(static::getInterface());
	}

	/**
	 * Get transport instance by code.
	 *
	 * @param string $code Transport code.
	 *
	 * @return null|iBase
	 */
	public static function getTransport($code)
	{
		return static::getObjectInstance(static::getInterface(), $code);
	}

	protected static function getInterface()
	{
		return __NAMESPACE__ . '\iBase';
	}

	protected static function getClasses()
	{
		return array(
			iBase::EVENT_NAME => Integration\EventHandler::onSenderTransportList(),
		);
	}
}