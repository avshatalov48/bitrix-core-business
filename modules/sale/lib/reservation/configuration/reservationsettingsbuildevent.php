<?php

namespace Bitrix\Sale\Reservation\Configuration;

use Bitrix\Main\Event;

/**
 * Event for building reservation settings.
 *
 * If you need to change the reservation settings, while you cannot edit the configuration (@see \Bitrix\Main\Configuration\Option),
 * you can use this event to change the settings.
 *
 * For example (realization event handler):
 * ```php
	public static function OnReservationSettingsBuild(\Bitrix\Sale\Reservation\Configuration\ReservationSettingsBuildEvent $event)
	{
		if (self::isEnabledCrmReservation())
		{
			$event->getSettings()->setReserveCondition(
				\Bitrix\Sale\Reservation\Configuration\ReserveCondition::ON_CREATE
			);
		}
	}
 * ```
 */
class ReservationSettingsBuildEvent extends Event
{
	/**
	 * Event name.
	 */
	public const NAME = 'OnReservationSettingsBuild';

	private ReservationSettings $settings;

	/**
	 * @param ReservationSettings $settings
	 */
	public function __construct(ReservationSettings $settings)
	{
		parent::__construct('sale', self::NAME);

		$this->settings = $settings;
	}

	/**
	 * Reservation settings.
	 *
	 * @return ReservationSettings
	 */
	public function getSettings(): ReservationSettings
	{
		return $this->settings;
	}
}
