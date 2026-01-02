<?php
namespace Bitrix\Im;

use Bitrix\Im\V2\Promotion\Entity;
use Bitrix\Im\V2\Promotion\Internals\DeviceType;
use Bitrix\Im\V2\Promotion\Service\UIPromotionService;
use Bitrix\Main\Config\Option;

/**
* @deprecated since 13.03.2025
* @see \Bitrix\Im\V2\Promotion\Promotion
*/
class Promotion
{
	const DEVICE_TYPE_WEB = "web"; // browser + desktop
	const DEVICE_TYPE_BROWSER = "browser";
	const DEVICE_TYPE_DESKTOP = "desktop";
	const DEVICE_TYPE_MOBILE = "mobile";
	const DEVICE_TYPE_ALL = "all";

	const USER_TYPE_OLD = "OLD";
	const USER_TYPE_NEW = "NEW";
	const USER_TYPE_ALL = "ALL";

	private const ONE_MONTH = 3600 * 24 * 30;
	private const ENDLESS_LIFETIME = 0;

	public static function getActive($type = self::DEVICE_TYPE_ALL)
	{
		if (self::isDisable())
		{
			return [];
		}

		$promoType = DeviceType::tryFrom($type);
		if (null === $promoType)
		{
			return [];
		}

		$promoService = UIPromotionService::getInstance();
		$promoList = $promoService->getActive($promoType);

		$result = [];
		foreach ($promoList as $promo)
		{
			$result[] = $promo->getId();
		}

		return $result;
	}

	public static function read($id)
	{
		$promoService = UIPromotionService::getInstance();
		$promotion = new Entity\Promotion($id);

		return $promoService->markAsViewed($promotion)->isSuccess();
	}

	public static function getDeviceTypes()
	{
		return [
			self::DEVICE_TYPE_ALL,
			self::DEVICE_TYPE_WEB,
			self::DEVICE_TYPE_BROWSER,
			self::DEVICE_TYPE_DESKTOP,
			self::DEVICE_TYPE_MOBILE,
		];
	}

	private static function isDisable(): bool
	{
		return Option::get('im', 'promo_disabled', 'N') === 'Y';
	}
}
