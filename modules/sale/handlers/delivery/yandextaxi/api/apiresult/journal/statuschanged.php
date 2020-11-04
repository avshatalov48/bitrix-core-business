<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal;

/**
 * Class StatusChanged
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult
 * @internal
 */
final class StatusChanged extends Event
{
	public const EVENT_CODE = 'status_changed';

	/** @var string */
	protected $newStatus;

	/** @var string */
	protected $resolution;

	/**
	 * @return string
	 */
	public function getCode(): string
	{
		return static::EVENT_CODE;
	}

	/**
	 * @return string
	 */
	public function getNewStatus()
	{
		return $this->newStatus;
	}

	/**
	 * @param string $newStatus
	 * @return StatusChanged
	 */
	public function setNewStatus(string $newStatus): StatusChanged
	{
		$this->newStatus = $newStatus;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getResolution()
	{
		return $this->resolution;
	}

	/**
	 * @param string $resolution
	 * @return StatusChanged
	 */
	public function setResolution(string $resolution): StatusChanged
	{
		$this->resolution = $resolution;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function provideUpdateFields(): array
	{
		return [
			'EXTERNAL_STATUS' => $this->newStatus,
			'EXTERNAL_RESOLUTION' => $this->resolution,
		];
	}
}
