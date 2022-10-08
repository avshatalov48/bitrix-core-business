<?php

namespace Bitrix\UI\FileUploader;

class RemoveResultCollection implements \IteratorAggregate, \JsonSerializable
{
	/** @var RemoveResult[] */
	private array $results = [];

	public function add(RemoveResult $result): void
	{
		$this->results[] = $result;
	}

	/**
	 * @return RemoveResult[]
	 */
	public function getAll(): array
	{
		return $this->results;
	}

	/**
	 * @return \ArrayIterator|RemoveResult[]
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