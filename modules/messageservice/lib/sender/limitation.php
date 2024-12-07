<?php
namespace Bitrix\MessageService\Sender;

use Bitrix\Main\Config\Option;
use Bitrix\MessageService\Internal\Entity\MessageTable;

class Limitation
{
	private static $options;
	private static $optionName = 'sender.limitation.daily';
	private static $defaultLimit = 0;

	public static function getDailyLimits()
	{
		$limits = array();
		$counts = MessageTable::getAllDailyCount();

		foreach (SmsManager::getSenders() as $sender)
		{
			$sid = $sender->getId();
			foreach ($sender->getFromList() as $from)
			{
				$key = $sid.':'.$from['id'];
				$limits[$key] = array(
					'senderId' => $sid,
					'fromId' => $from['id'],
					'limit' => static::getDailyLimit($sid, $from['id']),
					'current' => isset($counts[$key]) ? $counts[$key] : 0
				);
			}
		}

		return $limits;
	}

	public static function hasDailyLimits(): bool
	{
		foreach (SmsManager::getSenders() as $sender)
		{
			foreach ($sender->getFromList() as $from)
			{
				$limit = static::getDailyLimit($sender->getId(), $from['id']);
				if (isset($limit) && $limit > 0)
				{
					return true;
				}
			}
		}

		return false;
	}

	public static function getDailyLimit($senderId, $fromId)
	{
		$key = $senderId . ':' . $fromId;
		return static::getOption($key, static::$defaultLimit);
	}

	public static function setDailyLimit($senderId, $fromId, $limit)
	{
		$key = $senderId . ':' . $fromId;
		static::setOption($key, (int)$limit);
		MessageTable::returnDeferredToQueue($senderId, $fromId);
		return true;
	}

	public static function checkDailyLimit($senderId, $fromId)
	{
		$limit = static::getDailyLimit($senderId, $fromId);
		if ($limit > 0)
		{
			$current = MessageTable::getDailyCount($senderId, $fromId);

			return ($current < $limit);
		}
		return true;
	}

	public static function getRetryTime()
	{
		$time = static::getOption('retryTime', [
			'h' => 9,
			'i' => 0,
			'auto' => true,
			'tz' => ''
		]);

		return $time;
	}

	public static function setRetryTime(array $params)
	{
		$result = [
			'h' => (int)$params['h'],
			'i' => (int)$params['i'],
			'auto' => (bool)$params['auto'],
			'tz' => (string)$params['tz'],
		];

		static::setOption('retryTime', $result);
		return true;
	}

	/**
	 * @param array $options
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function setOptions(array $options)
	{
		static::$options = $options;
		Option::set('messageservice', static::$optionName, serialize($options));
		return true;
	}

	/**
	 * @return array
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function getOptions()
	{
		if (static::$options === null)
		{
			$optionsString = Option::get('messageservice', static::$optionName);
			if (\CheckSerializedData($optionsString))
			{
				static::$options = unserialize($optionsString, ['allowed_classes' => false]);
			}

			if (!is_array(static::$options))
			{
				static::$options = array();
			}
		}
		return static::$options;
	}

	/**
	 * @param $optionName
	 * @param $optionValue
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @internal param array $options
	 */
	private static function setOption($optionName, $optionValue)
	{
		$options = static::getOptions();
		if (!isset($options[$optionName]) || $options[$optionName] !== $optionValue)
		{
			$options[$optionName] = $optionValue;
			static::setOptions($options);
		}
		return true;
	}

	/**
	 * @param $optionName
	 * @param mixed $defaultValue
	 * @return mixed|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 */
	private static function getOption($optionName, $defaultValue = null)
	{
		$options = static::getOptions();
		return isset($options[$optionName]) ? $options[$optionName] : $defaultValue;
	}

	/**
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public static function clearOptions()
	{
		static::$options = array();
		Option::delete('messageservice', array('name' => static::$optionName));
		return true;
	}

}
