<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Integration\MessageService\Sms;

use Bitrix\Main\Loader;
use Bitrix\Main\Web\Uri;
use Bitrix\MessageService;

/**
 * Class Service
 * @package Bitrix\Sender\Integration\MessageService\Sms
 */
class Service
{
	public static function canUse()
	{
		return Loader::includeModule('messageservice');
	}

	/**
	 * Get providers.
	 *
	 * @return array
	 */
	public static function getProviders()
	{
		$result = array();
		$list = static::getSenderInfoList();
		foreach ($list as $item)
		{
			if (!$item['canUse'])
			{
			//	continue;
			}

			$item['from'] = static::getSenderFromList($item['id']);
			if (count($item['from']) == 0)
			{
			//	continue;
			}

			$result[] = $item;
		}

		return $result;
	}

	/**
	 * Get sender names.
	 *
	 * @return array
	 */
	public static function getSenderNames()
	{
		$list = array();
		foreach (self::getProviders() as $item)
		{
			if (!$item['canUse'] || count($item['from']) == 0)
			{
				continue;
			}
			foreach ($item['from'] as $number)
			{
				$id = $item['id'] . ':' . $number['id'];
				$name = $item['id'] === 'rest' ? $number['name'] : ($item['shortName'] ?: $item['name']);
				$list[$id] = $name;
			}
		}

		return $list;
	}

	/**
	 * Get daily limits.
	 *
	 * @return array
	 */
	public static function getDailyLimits()
	{
		return MessageService\Sender\Limitation::getDailyLimits();
	}

	/**
	 * Send.
	 *
	 * @param string $senderId Sender ID.
	 * @param string $from From number.
	 * @param string $to To number.
	 * @param string $text Text.
	 * @param integer $authorId Author ID.
	 * @return bool
	 */
	public static function send($senderId, $from, $to, $text, $authorId = 1)
	{
		if (!static::canUse())
		{
			return false;
		}

		$sender = MessageService\Sender\SmsManager::getSenderById($senderId);
		if (!$sender)
		{
			return false;
		}

		$smsMessage = MessageService\Sender\SmsManager::createMessage(array(
			'AUTHOR_ID' => $authorId,
			'MESSAGE_TO' => $to,
			'MESSAGE_BODY' => $text,
			'MESSAGE_FROM' => $from,
			'MESSAGE_HEADERS' => array(
				'module_id' => 'sender'
			)
		), $sender);

		$sendResult = $smsMessage->sendDirectly();
		return $sendResult->isSuccess();
	}

	/**
	 * Get manage url.
	 *
	 * @return string
	 */
	public static function getManageUrl()
	{
		return MessageService\Sender\SmsManager::getManageUrl();
	}

	/**
	 * Get limits url.
	 *
	 * @return string
	 */
	public static function getLimitsUrl()
	{
		return '/crm/configs/sms/?page=limits';
	}

	/**
	 * @param string $senderId Sender id.
	 * @return array Simple list of sender From aliases
	 */
	protected static function getSenderFromList($senderId)
	{
		$list = array();
		if (static::canUse())
		{
			$sender = MessageService\Sender\SmsManager::getSenderById($senderId);
			if ($sender)
			{
				$list = $sender->getFromList();
			}
		}
		return $list;
	}

	/**
	 * @param bool $getFromList
	 * @return array Senders information.
	 */
	protected static function getSenderInfoList($getFromList = false)
	{
		$info = array();
		if (static::canUse())
		{
			$uri = new Uri(self::getManageUrl());
			foreach (MessageService\Sender\SmsManager::getSenders() as $sender)
			{
				/** @var  $sender \Bitrix\MessageService\Sender\Sms\SmsRu */
				$uri->deleteParams(['sender'])->addParams(['sender' => $sender->getId()]);

				$senderInfo = array(
					'id' => $sender->getId(),
					'isConfigurable' => $sender->isConfigurable(),
					'name' => $sender->getName(),
					'shortName' => $sender->getShortName(),
					'canUse' => $sender->canUse(),
					'isDemo' => $sender->isConfigurable() ? $sender->isDemo() : null,
					'manageUrl' => $sender->isConfigurable() ?
						$uri->getLocator() : ''
				);

				if ($getFromList)
				{
					$senderInfo['fromList'] = static::getSenderFromList($sender->getId());
				}

				$info[] = $senderInfo;
			}
		}

		return $info;
	}

	public static function getFormattedOutputNumber($value)
	{
		static $numbers;
		if (null === $numbers)
		{
			$numbers = [];
			if (static::canUse())
			{
				$providers = static::getProviders();
				foreach ($providers as $provider)
				{
					foreach ($provider['from'] as $number)
					{
						$numbers[$provider['id'] . ':'. $number['id']] = $number['name'];
					}
				}
			}
		}

		return $numbers[$value] ?: $value;
	}
}