<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Helper\Analytics;

use Bitrix\Main\Analytics\AnalyticsEvent;

class Analytics
{
	protected static ?array $instances = [];

	public const STATUS_SUCCESS = 'success';
	public const STATUS_ERROR = 'error';

	public static function getInstance(): static
	{
		if (!isset(static::$instances[static::class]))
		{
			static::$instances[static::class] = new static();
		}

		return static::$instances[static::class];
	}

	protected function sendAnalytics(
		AnalyticsEvent $analyticsEvent,
		?string $type = null,
		?string $section = null,
		?string $element = null,
		?string $subSection = null,
		bool $status = true,
		array $params = [],
	): void
	{
		$analyticsEvent->setStatus($status ? self::STATUS_SUCCESS : self::STATUS_ERROR);

		if (!empty($type))
		{
			$analyticsEvent->setType($type);
		}

		if (!empty($section))
		{
			$analyticsEvent->setSection($section);
		}
		if (!empty($element))
		{
			$analyticsEvent->setElement($element);
		}
		if (!empty($subSection))
		{
			$analyticsEvent->setSubSection($subSection);
		}

		foreach ($params as $pName => $pValue)
		{
			$setter = 'set' . ucfirst($pName);
			if (!is_string($pValue) || empty($pValue))
			{
				continue;
			}

			if (!method_exists($analyticsEvent, $setter))
			{
				continue;
			}

			$analyticsEvent->$setter($pValue);
		}

		$analyticsEvent->send();
	}
}