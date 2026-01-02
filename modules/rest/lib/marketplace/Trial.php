<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Localization\Loc;

/**
* @deprecated
 */
class Trial
{
	/**
	 * @deprecated
	 * @use \Bitrix\Market\Subscription\Trial::isAvailable
	 */
	public static function isAvailable(): bool
	{
		return Client::isSubscriptionDemoAvailable() && Client::getSubscriptionFinalDate() === null;
	}

	/**
	 * @deprecated
	 * @use \Bitrix\Market\Subscription\Trial::activate
	 */
	public static function activate(): array
	{
		return self::getError(Loc::getMessage('REST_MARKET_CONFIG_ACTIVATE_ERROR'));
	}

	private static function getError(string $message, string $description = '', $code = null): array
	{
		$error = ['error' => $message];

		if ($description !== '')
		{
			$error['error_description'] = $description;
		}

		if (!is_null($code))
		{
			$error['error_code'] = $code;
		}

		return $error;
	}
}