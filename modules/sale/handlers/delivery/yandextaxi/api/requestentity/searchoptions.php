<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity;

/**
 * Class SearchOptions
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\RequestEntity
 * @internal
 */
final class SearchOptions extends RequestEntity
{
	/** @var int */
	protected $offset = 0;

	/** @var int */
	protected $limit = 100;

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
