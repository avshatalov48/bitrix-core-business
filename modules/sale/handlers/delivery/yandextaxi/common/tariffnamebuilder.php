<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

use Bitrix\Main\Localization\Loc;

/**
 * Class TariffNameBuilder
 * @package Sale\Handlers\Delivery\YandexTaxi\Common
 * @internal
 */
final class TariffNameBuilder
{
	/** @var RegionFinder */
	private $regionFinder;

	/**
	 * TariffNameBuilder constructor.
	 * @param RegionFinder $regionFinder
	 */
	public function __construct(RegionFinder $regionFinder)
	{
		$this->regionFinder = $regionFinder;
	}

	/**
	 * @param array $tariff
	 * @return string|null
	 */
	public function getTariffName(array $tariff): ?string
	{
		$result = null;

		$lang = $this->regionFinder->getCurrentRegion();
		if ($lang)
		{
			$result = Loc::getMessage(
				sprintf(
					'SALE_YANDEX_TAXI_TARIFF_%s_%s',
					mb_strtoupper($tariff['name']),
					mb_strtoupper($lang)
				)
			);
		}

		if (!$result)
		{
			$result = Loc::getMessage(
				sprintf(
					'SALE_YANDEX_TAXI_TARIFF_%s',
					mb_strtoupper($tariff['name'])
				)
			);
		}

		return $result;
	}
}
