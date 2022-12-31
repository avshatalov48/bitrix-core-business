<?php

namespace Bitrix\Calendar\Core\Queue\Rule;

use Bitrix\Calendar\Core\Base\EntityMap;
use Bitrix\Calendar\Core\Base\Map;
use Bitrix\Calendar\Core\Queue\Message\HandledMessage;
use Bitrix\Calendar\Core\Queue\Message\HandledMessageMapper;
use Bitrix\Calendar\Core\Queue\Message\Message;
use Bitrix\Calendar\Core\Queue\Message\MessageMapper;
use Bitrix\Calendar\Core\Queue\QueueListener;
use Bitrix\Calendar\Internals\Mutex;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\EventManager;
use Bitrix\Main\SystemException;
use COption;
use Generator;
use Throwable;
use Bitrix;

class RuleMaster
{
	public const ON_QUEUE_PUSHED_EVENT_NAME = 'OnPushToTargetQueue';

	private const PACK_SIZE = 100;

	private const LAST_PROCESSED_OPTION_NAME = 'queue_last_processed_id';

	private MessageMapper $messageMapper;

	private HandledMessageMapper $handledMessageMapper;

	private Map $routedQueues;

	private Mutex $mutex;

	/**
	 * @return void
	 */
	public function run()
	{
		if ($this->getMutex()->lock())
		{
			try
			{
				$this->handleMessages();

				$this->sendSystemEvents();
			}
			catch(Throwable $e)
			{
				// TODO: log it
			}
			finally
			{
				$this->getMutex()->unlock();
			}
		}
	}

	/**
	 * @return Generator
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	private function getMessages(): Generator
	{
		do
		{
			$messages = $this->getMessageMapper()->getMap(
				[
					'>ID' => $this->getLastProcessedId(),
				],
				self::PACK_SIZE,
				[
					'ID' => 'ASC',
				],
			);
			/** @var Message $message */
			foreach ($messages as $message)
			{
				yield $message;
				$this->setLastProcessedId($message->getId());
			}
		}

		while($messages->count());
	}

	/**
	 * @param Message $message
	 *
	 * @return bool
	 */
	private function routeMessage(Message $message): bool
	{
		$rules = Registry::getInstance()->getRules();
		$isRouted = false;
		foreach ($rules as $rule)
		{
			try
			{
				if ($handledMessage = $rule->route($message))
				{
					/** @var HandledMessage $handledMessage */
					$handledMessage = $this->getHandledMessageMapper()->create($handledMessage);
					$this->getRoutedQueues()->add($handledMessage->getQueue());
					$isRouted = true;
				}
			}
			catch(Throwable $e)
			{
				// TODO: log error
			}
		}

		return $isRouted;
	}

	/**
	 * @return int
	 */
	private function getLastProcessedId(): int
	{
		return COption::GetOptionInt("calendar", self::LAST_PROCESSED_OPTION_NAME, 0);
	}

	/**
	 * @param int $id
	 *
	 * @return void
	 */
	private function setLastProcessedId(int $id = 0)
	{
		COption::SetOptionInt("calendar", self::LAST_PROCESSED_OPTION_NAME, $id);
	}

	/**
	 * @return void
	 */
	public function sendSystemEvents(): void
	{
		// TODO: move it to right place
		QueueListener\Dispatcher::register();

		foreach ($this->getRoutedQueues() as $queue)
		{
			$event = new Bitrix\Main\Event(
				"calendar",
				self::ON_QUEUE_PUSHED_EVENT_NAME,
				[
					'queue' => $queue,
				],
			);
			EventManager::getInstance()->send($event);
		}
	}

	/**
	 * @return MessageMapper
	 */
	private function getMessageMapper(): MessageMapper
	{
		if (empty($this->messageMapper))
		{
			$this->messageMapper = new MessageMapper();
		}

		return $this->messageMapper;
	}

	/**
	 * @return HandledMessageMapper
	 */
	private function getHandledMessageMapper(): HandledMessageMapper
	{
		if (empty($this->handledMessageMapper))
		{
			$this->handledMessageMapper = new HandledMessageMapper();
		}

		return $this->handledMessageMapper;
	}

	/**
	 * @return Map
	 */
	public function getRoutedQueues(): Map
	{
		if (empty($this->routedQueues))
		{
			$this->routedQueues = new EntityMap();
		}

		return $this->routedQueues;
	}

	/**
	 * @return Mutex
	 */
	private function getMutex(): Mutex
	{
		if (empty($this->mutex))
		{
			$this->mutex = new Mutex(self::class);
		}

		return $this->mutex;
	}

	/**
	 * @return void
	 *
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function handleMessages(): void
	{
		/** @var Message $message */
		foreach ($this->getMessages() as $message)
		{
			$isRouted = $this->routeMessage($message);
			if (!$isRouted)
			{
				$this->getMessageMapper()->delete($message);
			}
		}
	}
}