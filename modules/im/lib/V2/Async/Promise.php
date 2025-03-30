<?php

namespace Bitrix\Im\V2\Async;

use Bitrix\Im\V2\Async\Promise\State;
use Bitrix\Main\UuidGenerator;
use Closure;
use Throwable;

abstract class Promise
{
	public readonly string $id;
	private State $state = State::Pending;
	private ?Closure $onFulfilled = null;
	private ?Closure $onRejected = null;
	protected mixed $result;

	public function __construct(callable $job)
	{
		$this->id = UuidGenerator::generateV4();
		try
		{
			$job([$this, 'fulfill'], [$this, 'reject']);
		}
		catch (Throwable $exception)
		{
			$this->reject($exception);
		}
	}

	abstract public function wait(): mixed;

	abstract public function onWait(): mixed;

	public function getState(): State
	{
		return $this->state;
	}

	public function getResult(): mixed
	{
		return $this->result ?? null;
	}

	public function then(?callable $onFulfilled = null, ?callable $onRejected = null): static
	{
		return new static(function(callable $fulfill, callable $reject) use ($onFulfilled, $onRejected): void {
			$wrappedOnFulfilled = $this->wrapCompletionHandler($onFulfilled, $fulfill, $reject);
			$wrappedOnRejected = $this->wrapCompletionHandler($onRejected, $fulfill, $reject);

			if ($this->state === State::Pending)
			{
				$this->onFulfilled = $wrappedOnFulfilled;
				$this->onRejected = $wrappedOnRejected;
			}
			elseif ($this->state === State::Fulfilled)
			{
				$wrappedOnFulfilled();
			}
			else
			{
				$wrappedOnRejected();
			}
		});
	}

	public function catch(callable $onRejected): static
	{
		return $this->then(null, $onRejected);
	}

	protected function fulfill(mixed $result): void
	{
		$this->complete(State::Fulfilled, $result);
	}

	protected function reject(mixed $result): void
	{
		$this->complete(State::Rejected, $result);
	}

	protected function complete(State $newState, mixed $result): void
	{
		if ($this->state !== State::Pending)
		{
			return;
		}

		$callback = $newState === State::Fulfilled ? $this->onFulfilled : $this->onRejected;
		$this->state = $newState;
		$this->result = $result;
		if ($callback)
		{
			$callback();
		}
		elseif ($newState === State::Rejected && $result instanceof Throwable)
		{
			throw $result;
		}
	}

	private function wrapCompletionHandler(?callable $completionHandler, callable $fulfill, callable $reject): callable
	{
		return function() use ($completionHandler, $fulfill, $reject): void {
			try
			{
				$this->processCompletion($completionHandler, $fulfill, $reject);
			}
			catch (Throwable $e)
			{
				$reject($e);
			}
		};
	}

	private function processCompletion(?callable $completionHandler, callable $fulfill, callable $reject): void
	{
		if ($completionHandler)
		{
			$this->executeCompletionHandler($completionHandler, $fulfill, $reject);
		}
		else
		{
			$this->propagateState($fulfill, $reject);
		}
	}

	private function executeCompletionHandler(callable $completionHandler, callable $fulfill, callable $reject): void
	{
		$result = $completionHandler($this->result);

		if ($result instanceof self)
		{
			$result->then($fulfill, $reject);
		}
		else
		{
			$fulfill($result);
		}
	}

	private function propagateState(callable $fulfill, callable $reject): void
	{
		if ($this->state === State::Fulfilled)
		{
			$fulfill($this->getResult());
		}
		else
		{
			$reject($this->getResult());
		}
	}
}
