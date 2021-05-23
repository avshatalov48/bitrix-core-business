<?php

namespace Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders;

/**
 * Class Order
 * @package Bitrix\Sale\Delivery\Internals\Analytics\OrderProviders
 * @internal
 */
final class Order implements \JsonSerializable
{
	/** @var string|null */
	private $id;

	/** @var string|null */
	private $status;

	/** @var bool|null */
	private $isSuccessful;

	/** @var int|null */
	private $createdAt;

	/** @var float|null */
	private $amount;

	/** @var string|null */
	private $currency;

	/**
	 * @return string|null
	 */
	public function getId(): ?string
	{
		return $this->id;
	}

	/**
	 * @param string|null $id
	 * @return Order
	 */
	public function setId(?string $id): Order
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getStatus(): ?string
	{
		return $this->status;
	}

	/**
	 * @param string|null $status
	 * @return Order
	 */
	public function setStatus(?string $status): Order
	{
		$this->status = $status;

		return $this;
	}

	/**
	 * @return bool|null
	 */
	public function isSuccessful(): ?bool
	{
		return $this->isSuccessful;
	}

	/**
	 * @param bool|null $isSuccessful
	 * @return Order
	 */
	public function setIsSuccessful(?bool $isSuccessful): Order
	{
		$this->isSuccessful = $isSuccessful;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getCreatedAt(): ?int
	{
		return $this->createdAt;
	}

	/**
	 * @param int|null $createdAt
	 * @return Order
	 */
	public function setCreatedAt(?int $createdAt): Order
	{
		$this->createdAt = $createdAt;

		return $this;
	}

	/**
	 * @return float|null
	 */
	public function getAmount(): ?float
	{
		return $this->amount;
	}

	/**
	 * @param float|null $amount
	 * @return Order
	 */
	public function setAmount(?float $amount): Order
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCurrency(): ?string
	{
		return $this->currency;
	}

	/**
	 * @param string|null $currency
	 * @return Order
	 */
	public function setCurrency(?string $currency): Order
	{
		$this->currency = $currency;

		return $this;
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize()
	{
		return [
			'id' => $this->id,
			'is_successful' => $this->isSuccessful,
			'status' => $this->status,
			'created_at' => $this->createdAt,
			'amount' => $this->amount,
			'currency' => $this->currency,
		];
	}
}
