<?php

namespace Bitrix\Sale\Reservation\Configuration;

/**
 * Reservation settings.
 * Store information about automatic redundancy scenarios and the time when reserves are rotting.
 */
class ReservationSettings
{
	/**
	 * The number of days after which the reserve is withdrawn.
	 *
	 * @var int
	 */
	private int $clearPeriod;

	/**
	 * The condition under which the reservation occurs.
	 * @see constants of this class.
	 *
	 * @var string|null
	 */
	private ?string $reserveCondition;

	/**
	 * @param int $clearPeriod
	 * @param string|null $reserveCondition
	 */
	public function __construct(
		int $clearPeriod,
		?string $reserveCondition
	)
	{
		$this->setClearPeriod($clearPeriod);
		$this->setReserveCondition($reserveCondition);
	}

	/**
	 * Set number of days after which the reserve is withdrawn.
	 *
	 * @param int $value
	 *
	 * @return void
	 */
	public function setClearPeriod(int $value): void
	{
		$this->clearPeriod = $value;
	}

	/**
	 * Get number of days after which the reserve is withdrawn.
	 *
	 * @return int
	 */
	public function getClearPeriod(): int
	{
		return $this->clearPeriod;
	}

	/**
	 * Set reserve condition.
	 *
	 * @param string|null $value constants of `Reserve Condition` or `null` if not has reserve condition.
	 *
	 * @return void
	 */
	public function setReserveCondition(?string $value): void
	{
		if (isset($value))
		{
			ReserveCondition::validate($value);
		}
		$this->reserveCondition = $value;
	}

	/**
	 * Get reserve condition.
	 *
	 * @return string|null
	 */
	public function getReserveCondition(): ?string
	{
		return $this->reserveCondition;
	}

	/**
	 * Checking is enabled automatic reservation.
	 *
	 * @return bool
	 */
	public function isEnableAutomaticReservation(): bool
	{
		return $this->getReserveCondition() !== null;
	}
}
