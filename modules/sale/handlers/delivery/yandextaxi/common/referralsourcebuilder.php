<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Common;

/**
 * Class ReferralSourceBuilder
 * @package Sale\Handlers\Delivery\YandexTaxi\Common
 * @internal
 */
final class ReferralSourceBuilder
{
	/** @var RegionFinder */
	protected $regionFinder;

	/**
	 * ReferralSourceBuilder constructor.
	 * @param RegionFinder $regionFinder
	 */
	public function __construct(RegionFinder $regionFinder)
	{
		$this->regionFinder = $regionFinder;
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getReferralSourceValue(): string
	{
		$region = $this->regionFinder->getCurrentRegion();

		if ($region === 'kz')
		{
			return 'api_1c-bitrix_kz';
		}

		if ($region === 'by')
		{
			return 'api_1c-bitrix_by';
		}

		return 'api_1c-bitrix';
	}
}
