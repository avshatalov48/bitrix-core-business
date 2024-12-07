<?php

namespace Bitrix\Calendar\Internals\EventManager\EventSubscriber;

use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

trait DependentEventSubscriberTrait
{
	use EventSubscriberResponseTrait;

	abstract static function getDependencies(): array;

	abstract public function handle(Event $event): EventResult;

	public function __invoke(Event $event): EventResult
	{
		if (is_a(static::class, DependentEventSubscriberInterface::class, true))
		{
			$this->checkDependencies($event, static::getDependencies());
		}

		return $this->handle($event);
	}

	protected function getResultFromSubscriber(Event $event, string $subscriberClass): mixed
	{
		foreach ($event->getResults() as $subscriberResult)
		{
			if (
				$subscriberResult->getHandler() !== $subscriberClass
				|| $subscriberResult->getType() !== EventResult::SUCCESS
			)
			{
				continue;
			}

			return $subscriberResult->getParameters();
		}

		return null;
	}

	private function checkDependencies(Event $event, array $dependencies): void
	{
		$executedSubscribers = array_map(
			fn (EventResult $eventResult) => $eventResult->getHandler(),
			$event->getResults()
		);

		if (!empty(array_diff($dependencies, $executedSubscribers)))
		{
			throw new \RuntimeException('Dependencies not executed');
		}
	}
}