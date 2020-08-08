<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity;

/**
 * Class SearchOptions
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\RequestEntity
 */
class SearchOptions implements \JsonSerializable
{
	use RequestEntityTrait;

	/** @var int */
	private $offset = 0;

	/** @var int */
	private $limit = 100;

	/**
	 * @return int
	 */
	public function getOffset(): int
	{
		return $this->offset;
	}

	/**
	 * @param int $offset
	 * @return SearchOptions
	 */
	public function setOffset(int $offset): SearchOptions
	{
		$this->offset = $offset;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getLimit(): int
	{
		return $this->limit;
	}

	/**
	 * @param int $limit
	 * @return SearchOptions
	 */
	public function setLimit(int $limit): SearchOptions
	{
		$this->limit = $limit;

		return $this;
	}
}
