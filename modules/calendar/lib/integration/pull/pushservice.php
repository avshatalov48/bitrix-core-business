<?php

namespace Bitrix\Calendar\Integration\Pull;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Pull\Event;

class PushService
{
	public const MODULE_ID = 'calendar';

	private static ?self $instance;
	private static bool $isJobOn = false;

	private array $registry = [];

	public static function getInstance(): PushService
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	/**
	 * @throws LoaderException
	 */
	public static function proceed(): void
	{
		if (!Loader::includeModule('pull'))
		{
			return;
		}

		self::getInstance()->sendEvents();
	}

	/**
	 * @param $recipients
	 * @param array $params
	 */
	public static function addEvent($recipients, array $params): void
	{
		$params = self::preparePullManagerParams($params);

		$parameters = [
			'RECIPIENTS' => $recipients,
			'PARAMS' => $params,
		];
		self::getInstance()->registerEvent($parameters);
		self::getInstance()->addBackgroundJob();
	}

	public static function addEventByTag(string $tag, array $params): void
	{
		$params = self::preparePullManagerParams($params);

		$parameters = [
			'TAG' => $tag,
			'PARAMS' => $params,
		];
		self::getInstance()->registerEvent($parameters);
		self::getInstance()->addBackgroundJob();
	}

	private function addBackgroundJob(): void
	{
		if (!self::$isJobOn)
		{
			$application = Application::getInstance();
			$application && $application->addBackgroundJob(self::proceed(...), [], 0);

			self::$isJobOn = true;
		}
	}

	/**
	 * @param array $parameters
	 */
	private function registerEvent(array $parameters): void
	{
		$this->registry[] = [
			'TAG' => $parameters['TAG'] ?? null,
			'RECIPIENTS' => $parameters['RECIPIENTS'] ?? null,
			'PARAMS' => $parameters['PARAMS'] ?? null,
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

	private static function preparePullManagerParams(array $params): array
	{
		$pullManagerParams = [
			'eventName' => $params['command'],
			'item' => [],
			'skipCurrentUser' => false,
			'eventId' => null,
			'ignoreDelay' => false,
		];

		$params['params'] = array_merge($params['params'], $pullManagerParams);

		return $params;
	}
}
