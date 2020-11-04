<?php

namespace Bitrix\Sale\Delivery\Services\Crm;

/**
 * Class EstimationMessage
 * @package Bitrix\Sale\Delivery\Services\Crm
 * @internal
 */
final class EstimationMessage
{
	/** @var int|null */
	private $typeId;

	/** @var int|null */
	private $authorId;

	/** @var array */
	private $fields = [];

	/** @var array */
	private $bindings = [];

	/**
	 * @return int|null
	 */
	public function getTypeId(): ?int
	{
		return $this->typeId;
	}

	/**
	 * @param int|null $typeId
	 * @return EstimationMessage
	 */
	public function setTypeId(?int $typeId): EstimationMessage
	{
		$this->typeId = $typeId;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getAuthorId(): ?int
	{
		return $this->authorId;
	}

	/**
	 * @param int|null $authorId
	 * @return EstimationMessage
	 */
	public function setAuthorId(?int $authorId): EstimationMessage
	{
		$this->authorId = $authorId;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getFields(): array
	{
		return $this->fields;
	}

	/**
	 * @param array $fields
	 * @return EstimationMessage
	 */
	public function setFields(array $fields): EstimationMessage
	{
		$this->fields = $fields;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getBindings(): array
	{
		return $this->bindings;
	}

	/**
	 * @param array $bindings
	 * @return EstimationMessage
	 */
	public function setBindings(array $bindings): EstimationMessage
	{
		$this->bindings = $bindings;

		return $this;
	}
}
