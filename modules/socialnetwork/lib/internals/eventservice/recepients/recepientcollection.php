<?php

namespace Bitrix\Socialnetwork\Internals\EventService\Recepients;

use Traversable;

class RecepientCollection implements Collector, \IteratorAggregate, \Countable
{
	/** @var Recepient[]  */
	private array $recipients;

	public function __construct(Recepient ...$recipients)
	{
		$this->recipients = $recipients;
	}

	public function fetch(int $limit, int $offset): RecepientCollection
	{
		return new RecepientCollection(...array_slice($this->recipients, $offset, $limit));
	}

	public function getIterator(): Traversable
	{
		return new \ArrayIterator($this->recipients);
	}

	public function count(): int
	{
		return count($this->recipients);
	}
}