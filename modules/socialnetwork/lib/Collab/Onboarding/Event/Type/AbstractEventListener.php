<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Event\Type;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Socialnetwork\Collab\Onboarding\OnboardingFeature;
use Bitrix\Socialnetwork\Collab\Onboarding\Service\AbstractQueueService;

abstract class AbstractEventListener
{
	private static array $instances = [];
	protected AbstractQueueService $queueService;

	public function __construct()
	{
		$this->queueService = ServiceLocator::getInstance()->get('socialnetwork.onboarding.queue.service');
	}

	public static function getInstance(): FakeEventListener|static
	{
		if (!OnboardingFeature::isAvailable())
		{
			return new FakeEventListener();
		}

		if (!isset(static::$instances[static::class]))
		{
			static::$instances[static::class] = new static();
		}

		return static::$instances[static::class];
	}
}