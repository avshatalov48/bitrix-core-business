<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

class RecepientCollection implements \Iterator, \Countable
{
	/** @var Recepient[]  */
	private array $recepients;

	public function __construct(Recepient ...$recepients)
	{
		$this->recepients = $recepients;
	}

	public function add(Recepient $recepient): void
	{
		$this->recepients[] = $recepient;
	}

	public function count(): int
	{
		return count($this->recepients);
	}

	public function current(): Recepient
	{
		return current($this->recepients);
	}

	public function next(): void
	{
		next($this->recepients);
	}

	public function key(): mixed
	{
		return key($this->recepients);
	}

	public function valid(): bool
	{
		$key = key($this->recepients);
		return ($key !== null && $key !== false);
	}

	public function rewind(): void
	{
		reset($this->recepients);
	}
}