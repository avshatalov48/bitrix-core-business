<?php

namespace Bitrix\Socialnetwork\Integration\Pull;

use Bitrix\Main;
use Bitrix\Pull\Event;

/**
 * Class PushService
 *
 * @package Bitrix\Socialnetwork\Integration\Pull
 */

class PushService
{
	public const MODULE_NAME = 'socialnetwork';

	private static $instance;
	private static $isJobOn = false;

	private $registry = [];

	/**
	 * PushService constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * @return PushService
	 */
	public static function getInstance(): PushService
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param $recipients
	 * @param array $params
	 */
	public static function addEvent($recipients, array $params): void
	{
		$parameters = [
			'RECIPIENTS' => $recipients,
			'PARAMS' => $params,
		];
		self::getInstance()->registerEvent($parameters);
		self::getInstance()->addBackgroundJob();
	}

	public static function addEventByTag(string $tag, array $params): void
	{
		$parameters = [
			'TAG' => $tag,
			'PARAMS' => $params,
		];
		self::getInstance()->registerEvent($parameters);
		self::getInstance()->addBackgroundJob();
	}

	/**
	 * @throws Main\LoaderException
	 */
	public static function proceed(): void
	{
		if (!Main\Loader::includeModule('pull'))
		{
			return;
		}

		self::getInstance()->sendEvents();
	}

	private function addBackgroundJob(): void
	{
		if (!self::$isJobOn)
		{
			$application = Main\Application::getInstance();
			$application && $application->addBackgroundJob([__CLASS__, 'proceed'], [], 0);

			self::$isJobOn = true;
		}
	}

	/**
	 * @param array $parameters
	 */
	private function registerEvent(array $parameters): void
	{
		$this->registry[] = [
			'TAG' => $parameters['TAG'] ?? '',
			'RECIPIENTS' => $parameters['RECIPIENTS'],
			'PARAMS' => $parameters['PARAMS'],
		];
	}

	private function sendEvents(): void
	{
		foreach ($this->registry as $event)
		{
			if (isset($event['TAG']) && $event['TAG'] !== '')
			{
				\CPullWatch::AddToStack($event['TAG'], $event['PARAMS']);
			}
			else
			{
				Event::add($event['RECIPIENTS'], $event['PARAMS']);
			}
		}
	}

}