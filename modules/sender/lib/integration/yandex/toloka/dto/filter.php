<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class Filter implements TolokaTransferObject
{
	/**
	 * @var string
	 */
	private $operator = 'IN';

	/**
	 * @var string
	 */
	private $category = 'computed';

	/**
	 * @var string
	 */
	private $key;

	/**
	 * @var string
	 */
	private $value;

	/**
	 * @return string
	 */
	public function getOperator(): string
	{
		return $this->operator;
	}

	/**
	 * @param string $operator
	 *
	 * @return Filter
	 */
	public function setOperator(string $operator): Filter
	{
		$this->operator = $operator;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCategory(): string
	{
		return $this->category;
	}

	/**
	 * @param string $category
	 *
	 * @return Filter
	 */
	public function setCategory(string $category): Filter
	{
		$this->category = $category;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getKey(): string
	{
		return $this->key;
	}

	/**
	 * @param string $key
	 *
	 * @return Filter
	 */
	public function setKey(string $key): Filter
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getValue(): string
	{
		return $this->value;
	}

	/**
	 * @param string $value
	 *
	 * @return Filter
	 */
	public function setValue(string $value): Filter
	{
		$this->value = $value;

		return $this;
	}

	public function ableFilters()
	{
		return [
			'region_by_ip',
			'region_by_phone'
		];
	}

	public function toArray(): array
	{
		return [
			'operator' => $this->operator,
			'category' => $this->category,
			'key'      => $this->key,
			'value'    => $this->value
		];
	}
}