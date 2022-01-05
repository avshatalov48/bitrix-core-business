<?php

namespace Bitrix\Rest\Configuration;

/**
 * Class Notification
 * @package Bitrix\Rest\Configuration
 */
class Notification
{
	public const TYPE_NOTICE = 'notice';
	public const TYPE_ERROR = 'error';
	public const TYPE_EXCEPTION = 'exception';

	private $setting;
	private $notificationList;
	private $holdSaveToBase = false;

	/**
	 * Notification constructor.
	 *
	 * @param Setting $setting
	 */
	public function __construct(Setting $setting)
	{
		$this->setting = $setting;
		$this->notificationList = $this->setting->get(Setting::SETTING_NOTICE_COLLECTION) ?? [];
	}

	/**
	 * Saves all notification from event array
	 * @param $result
	 *
	 * @return bool
	 */
	public function save($result): bool
	{
		$this->holdSaveToBase = true;

		if ($result['ERROR_ACTION'])
		{
			$this->add($result['ERROR_ACTION'], '', self::TYPE_NOTICE);
		}

		if ($result['ERROR_MESSAGES'])
		{
			$this->add($result['ERROR_MESSAGES'], '', self::TYPE_ERROR);
		}

		if ($result['ERROR_EXCEPTION'])
		{
			$this->add($result['ERROR_EXCEPTION'], '', self::TYPE_EXCEPTION);
		}

		$this->holdSaveToBase = false;

		return $this->setting->set(Setting::SETTING_NOTICE_COLLECTION, $this->notificationList);
	}

	/**
	 * Adds new notification to instance of action
	 * @param $message
	 * @param $code
	 * @param $type
	 *
	 * @return bool
	 */
	public function add($message, $code, $type): bool
	{
		if (is_array($message))
		{
			foreach ($message as $mess)
			{
				$this->notificationList[] = [
					'code' => $code,
					'message' => $mess,
					'type' => $type,
				];
			}
		}
		else
		{
			$this->notificationList[] = [
				'code' => $code,
				'message' => $message,
				'type' => $type,
			];
		}

		return
			!$this->holdSaveToBase
			&& $this->setting->set(
				Setting::SETTING_NOTICE_COLLECTION,
				$this->notificationList
			);
	}

	/**
	 * Returns list of notification to instance of action
	 * @param array $filter
	 *
	 * @return array|null
	 */
	public function list(array $filter = []): ?array
	{
		$result = $this->notificationList;

		if ($filter['type'] !== null)
		{
			foreach ($result as $key => $item)
			{
				if ($item['type'] !== $filter['type'])
				{
					unset($result[$key]);
				}
			}
		}

		return $result;
	}

	/**
	 * Cleans all notification
	 * @return bool
	 */
	public function clean(): bool
	{
		return $this->setting->delete(Setting::SETTING_NOTICE_COLLECTION);
	}
}