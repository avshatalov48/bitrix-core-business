<?php

namespace Bitrix\Socialnetwork\Internals\Space\Counter;

class ProviderCollection implements \IteratorAggregate, \Countable
{
	/** @var ProviderInterface[]  */
	private array $providers;

	public function __construct(ProviderInterface ...$providers)
	{
		$this->providers = $providers;
	}

	/**
	 * @return ProviderInterface[]
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->providers);
	}

	public function add(ProviderInterface $provider): void
	{
		$this->providers[] = $provider;
	}

	public function isEmpty(): bool
	{
		return empty($this->providers);
	}

	public function count(): int
	{
		return count($this->providers);
	}
}