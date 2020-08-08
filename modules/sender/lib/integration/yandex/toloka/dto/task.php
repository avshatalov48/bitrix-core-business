<?php

namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO;

class Task implements TolokaTransferObject
{
	/**
	 * @var int
	 */
	private $id;

	/**
	 * @var int
	 */
	private $poolId;

	/**
	 * @var InputValue
	 */
	private $inputValues;
	/**
	 * @var int
	 */
	private $overlap = 3;

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param int $id
	 *
	 * @return Task
	 */
	public function setId(int $id): Task
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPoolId(): int
	{
		return $this->poolId;
	}

	/**
	 * @param int $poolId
	 *
	 * @return Task
	 */
	public function setPoolId(int $poolId): Task
	{
		$this->poolId = $poolId;

		return $this;
	}

	/**
	 * @return InputValue
	 */
	public function getInputValues(): InputValue
	{
		return $this->inputValues;
	}

	/**
	 * @param InputValue $inputValues
	 *
	 * @return Task
	 */
	public function setInputValues(InputValue $inputValues): Task
	{
		$this->inputValues = $inputValues;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getOverlap(): int
	{
		return $this->overlap;
	}

	/**
	 * @param int $overlap
	 *
	 * @return Task
	 */
	public function setOverlap(int $overlap): Task
	{
		$this->overlap = $overlap;

		return $this;
	}

	/**
	 * @return Pool
	 */
	public function getPool(): Pool
	{
		return $this->pool;
	}

	/**
	 * @param Pool $pool
	 *
	 * @return Task
	 */
	public function setPool(Pool $pool): Task
	{
		$this->pool = $pool;

		return $this;
	}

	public function toArray(): array
	{

		return [
			'pool_id'      => $this->poolId,
			'overlap'      => $this->overlap,
			'input_values' => $this->inputValues->toArray()
		];
	}
}