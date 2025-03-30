<?php

namespace Bitrix\Im\V2\Async;

use Bitrix\Im\V2\Async\Promise\BackgroundJobPromise;
use Bitrix\Im\V2\Async\Promise\State;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Throwable;

class Queue
{
	private static self $instance;
	private ?Throwable $lastException = null;
	private bool $isScheduled = false;
	private bool $isInBackground = false;
	/**
	 * @var BackgroundJobPromise[]
	 */
	private array $promises = [];
	private array $promisesInProgress = [];
	private array $completedPromises = [];
	private array $keysToRemove = [];

	private function __construct()
	{
	}

	public static function getInstance(): static
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function add(BackgroundJobPromise $promise): void
	{
		if ($this->isInBackground && $promise->immediateIfBackground())
		{
			$promise->onWait();
			$this->completedPromises[$promise->id] = $promise;

			return;
		}

		$this->promises[$promise->id] = $promise;
		$this->schedule();
	}

	public function onAfterPromiseComplete(BackgroundJobPromise $promise): void
	{
		$this->keysToRemove[] = $promise->id;
		$this->completedPromises[$promise->id] = $promise;
	}

	/**
	 * @throws Throwable
	 */
	public function wait(?BackgroundJobPromise $target = null): mixed
	{
		if ($target && $target->getState() !== State::Pending)
		{
			return $target->getResult();
		}

		try
		{
			$result = $this->processQueue($target);
			$this->processLastException();

			return $result;
		}
		finally
		{
			$this->cleanQueue();
		}
	}

	/**
	 * @throws Throwable
	 */
	private function processQueue(?BackgroundJobPromise $target = null): mixed
	{
		while (($promise = current($this->promises)) !== false)
		{
			$this->processPromise($promise);

			if ($target && isset($this->completedPromises[$target->id]))
			{
				return $this->completedPromises[$target->id]->getResult();
			}

			next($this->promises);
		}

		if ($target)
		{
			$this->lastException = new SystemException('The target promise was not fulfilled');
		}

		return null;
	}

	/**
	 * @throws Throwable
	 */
	private function processLastException(): void
	{
		if (!$this->lastException)
		{
			return;
		}

		$exception = $this->lastException;
		$this->lastException = null;

		throw $exception;
	}

	private function processPromise(Promise $promise): void
	{
		if (isset($this->promisesInProgress[$promise->id]))
		{
			return;
		}

		$this->promisesInProgress[$promise->id] = $promise;

		try
		{
			$promise->onWait();
		}
		catch (Throwable $exception)
		{
			$this->lastException = $exception;
		}

		unset($this->promisesInProgress[$promise->id]);
	}

	private function runInBackground(): void
	{
		$this->isInBackground = true;
		$this->wait();
		$this->isScheduled = false;
	}

	private function schedule(): void
	{
		if ($this->isScheduled)
		{
			return;
		}

		Application::getInstance()->addBackgroundJob(fn () => $this->runInBackground());
		$this->isScheduled = true;
	}

	private function cleanQueue(): void
	{
		foreach ($this->keysToRemove as $key)
		{
			unset($this->promises[$key]);
		}

		$this->keysToRemove = [];
	}
}
