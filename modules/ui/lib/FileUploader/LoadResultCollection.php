<?php

namespace Bitrix\UI\FileUploader;

class LoadResultCollection implements \IteratorAggregate, \JsonSerializable
{
	/** @var LoadResult[] */
	private array $results = [];

	public function add(LoadResult $result): void
	{
		$this->results[] = $result;
	}

	/**
	 * @return LoadResult[]
	 */
	public function getAll(): array
	{
		return $this->results;
	}

	/**
	 * @return \ArrayIterator|LoadResult[]
	 */
	public function getIterator(): \ArrayIterator
	{
		return new \ArrayIterator($this->results);
	}

	public function jsonSerialize(): array
	{
		return $this->getAll();
	}
}