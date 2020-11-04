<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal;

/**
 * Class Event
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
abstract class Event
{
	/** @var string */
	protected $claimId;

	/** @var string */
	protected $updatedTs;

	/**
	 * @return string
	 */
	abstract public function getCode(): string;

	/**
	 * @return string
	 */
	public function getClaimId()
	{
		return $this->claimId;
	}

	/**
	 * @param string $claimId
	 * @return Event
	 */
	public function setClaimId(string $claimId): Event
	{
		$this->claimId = $claimId;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUpdatedTs()
	{
		return $this->updatedTs;
	}

	/**
	 * @param string $updatedTs
	 * @return Event
	 */
	public function setUpdatedTs(string $updatedTs): Event
	{
		$this->updatedTs = $updatedTs;

		return $this;
	}

	/**
	 * @return array
	 */
	abstract public function provideUpdateFields(): array;
}
