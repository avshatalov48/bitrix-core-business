<?php

namespace Bitrix\Sale\Reservation\Configuration;

use Bitrix\Main\Config\Option;
use Bitrix\Main\DI\ServiceLocator;

/**
 * Service for work with reservation settings.
 */
class ReservationSettingsService
{
	private const OPTION_RESERVE_CONDITION = 'product_reserve_condition';
	private const OPTION_CLEAR_PERIOD = 'product_reserve_clear_period';

	/**
	 * Service instance.
	 *
	 * @return self
	 */
	public static function getInstance(): self
	{
		return ServiceLocator::getInstance()->get('sale.reservation.settings');
	}

	/**
	 * Get reservation settings.
	 *
	 * Settings load from options, but may be changed with the help of building event.
	 * @see \Bitrix\Sale\Reservation\Configuration\ReservationSettingsBuildEvent.
	 *
	 * @return ReservationSettings
	 */
	public function get(): ReservationSettings
	{
		$settings = new ReservationSettings(
			(int)Option::get('sale', self::OPTION_CLEAR_PERIOD),
			(string)Option::get('sale', self::OPTION_RESERVE_CONDITION) ?: null
		);

		$event = new ReservationSettingsBuildEvent($settings);
		$event->send();

		return $settings;
	}
}
