<?php

namespace Bitrix\Im\V2\Async\Promise;

use Bitrix\Im\V2\Async\Promise;
use Bitrix\Im\V2\Async\Queue;
use Closure;
use Throwable;

class BackgroundJobPromise extends Promise
{
	private Queue $queue;
	private readonly Closure $backgroundPart;
	private bool $isBackgroundPartStarted = false;
	private bool $immediateIfBackground;

	public function __construct(callable $job, ?callable $backgroundPart = null, bool $immediateIfBackground = true)
	{
		$this->immediateIfBackground = $immediateIfBackground;
		$this->queue = Queue::getInstance();
		if ($backgroundPart)
		{
			$this->backgroundPart = $this->wrapBackgroundPart($backgroundPart);
		}
		$decoratedJob = $this->wrapForQueue($job);
		parent::__construct($decoratedJob);
	}

	public function wait(): mixed
	{
		if ($this->getState() !== State::Pending)
		{
			return $this->getResult();
		}

		return $this->queue->wait($this);
	}

	public function onWait(): mixed
	{
		if ($this->getState() !== State::Pending)
		{
			return $this->getResult();
		}

		if (isset($this->backgroundPart) && !$this->isBackgroundPartStarted)
		{
			$this->isBackgroundPartStarted = true;
			($this->backgroundPart)();
		}

		return $this->getResult();
	}

	public static function deferJob(callable $deferredJob, bool $runImmediatelyIfInBackground = true): static
	{
		return new static(static function () {}, $deferredJob, $runImmediatelyIfInBackground);
	}

	public function immediateIfBackground(): bool
	{
		return $this->immediateIfBackground;
	}

	private function wrapForQueue(callable $job): callable
	{
		return function (callable $fulfill, callable $reject) use ($job): void {
			if (isset($this->backgroundPart))
			{
				$this->queue->add($this);
			}
			$job($fulfill, $reject);
		};
	}

	private function wrapBackgroundPart(callable $backgroundPart): callable
	{
		return function () use ($backgroundPart): void {
			$result = null;
			try
			{
				$result = $backgroundPart();
			}
			catch (Throwable $e)
			{
				$this->reject($e);
			}
			$this->fulfill($result);
		};
	}

	protected function complete(State $newState, mixed $result): void
	{
		try
		{
			parent::complete($newState, $result);
		}
		finally
		{
			$this->queue->onAfterPromiseComplete($this);
		}
	}

}
