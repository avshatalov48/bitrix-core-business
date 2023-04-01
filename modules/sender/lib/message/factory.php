<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

use Bitrix\Sender\Integration;
use Bitrix\Sender\Internals\CodeBasedFactory;

/**
 * Class Factory
 * @package Bitrix\Sender\Message
 */
class Factory extends CodeBasedFactory
{
	/**
	 * Get interface.
	 *
	 * @return string
	 */
	protected static function getInterface()
	{
		return __NAMESPACE__ . '\iBase';
	}

	protected static function getClasses()
	{
		return array(
			iBase::EVENT_NAME => Integration\EventHandler::onSenderMessageList(),
		);
	}

	/**
	 * Get ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getAdsMessages()
	{
		$list = array();
		foreach (static::getMessages() as $message)
		{
			if (!($message instanceof iAds))
			{
				continue;
			}

			$list[] = $message;
		}

		return $list;
	}

	/**
	 * Get ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getMarketingMessages()
	{
		$list = array();
		foreach (static::getMessages() as $message)
		{
			if (!($message instanceof iMarketing))
			{
				continue;
			}

			$list[] = $message;
		}

		return $list;
	}

	/**
	 * Get ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getTolokaMessages()
	{
		$list = array();
		foreach (static::getMessages() as $message)
		{
			if (!($message instanceof iToloka))
			{
				continue;
			}

			$list[] = $message;
		}

		return $list;
	}

	/**
	 * Get yandex messages.
	 *
	 * @return iYandex[]
	 */
	public static function getYandexMessages(bool $withToloka): array
	{
		$list = [];
		foreach (static::getMessages() as $message)
		{
			if (!$message instanceof iYandex)
			{
				continue;
			}
			if (!$withToloka && $message instanceof iToloka)
			{
				continue;
			}

			$list[] = $message;
		}

		return $list;
	}

	/**
	 * Get non ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getMailingMessages()
	{
		$list = array();
		foreach (static::getMessages() as $message)
		{
			if (!($message instanceof iMailable))
			{
				continue;
			}

			$list[] = $message;
		}

		return $list;
	}

	/**
	 * Get non ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getReturnCustomerMessages()
	{
		$list = array();
		foreach (static::getMessages() as $message)
		{
			if (!($message instanceof iReturnCustomer))
			{
				continue;
			}

			$list[] = $message;
		}

		return $list;
	}

	/**
	 * Get ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getAdsMessageCodes()
	{
		return array_map(
			function ($message)
			{
				/** @var iBase $message */
				return $message->getCode();
			},
			static::getAdsMessages()
		);
	}

	/**
	 * Get marketing message instances.
	 *
	 * @return iBase[]
	 */
	public static function getMarketingMessageCodes()
	{
		return array_map(
			function ($message)
			{
				/** @var iBase $message */
				return $message->getCode();
			},
			static::getMarketingMessages()
		);
	}

	/**
	 * Get non ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getMailingMessageCodes()
	{
		return array_map(
			function ($message)
			{
				/** @var iBase $message */
				return $message->getCode();
			},
			static::getMailingMessages()
		);
	}

	/**
	 * Get non ads message instances.
	 *
	 * @return iBase[]
	 */
	public static function getTolokaMessageCodes()
	{
		return array_map(
			function ($message)
			{
				/** @var iBase $message */
				return $message->getCode();
			},
			static::getTolokaMessages()
		);
	}

	/**
	 * Get non ads message instances.
	 *
	 * @return string[]
	 */
	public static function getReturnCustomerMessageCodes()
	{
		return array_map(
			function ($message)
			{
				/** @var iBase $message */
				return $message->getCode();
			},
			static::getReturnCustomerMessages()
		);
	}

	/**
	 * Get message instances.
	 *
	 * @return iBase[]
	 */
	public static function getMessages()
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
	public static function getMessage($code)
	{
		return static::getObjectInstance(static::getInterface(), $code);
	}
}