<?php

namespace Bitrix\Calendar\Internals\EventManager;

use Bitrix\Main\Event;

final class EventManager implements EventManagerInterface
{
	private array $handlers = [];

	public function addEventHandler($fromModuleId, $eventType, $callback, $includeFile = false, $sort = 100)
	{
		$this->handlers[$eventType][] = ['callback' => $callback, 'sort' => $sort];
	}

	public function send(Event $event): void
	{
		$handlers = $this->getEventHandlers($event->getEventType());
		usort($handlers, static fn ($h1, $h2) => $h1['sort'] <=> $h2['sort']);
		foreach ($handlers as $handler)
		{
			$this->sendToEventHandler($handler['callback'], $event);
		}
	}

	private function getEventHandlers(string $eventType): array
	{
		return $this->handlers[$eventType];
	}

	private function sendToEventHandler(callable $handler, Event $event): void
	{
		try
		{
			$result = call_user_func($handler, $event);

			if ($result != null)
			{
				$event->addResult($result);
			}
		}
		catch (\Throwable $e)
		{
			throw $e;
		}
	}
}
