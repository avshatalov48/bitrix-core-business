<?php

namespace Bitrix\Rest\Marketplace;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Rest\OAuthService;
use CRestUtil;

class Trial
{
	public static function isAvailable(): bool
	{
		return Client::isSubscriptionDemoAvailable() && Client::getSubscriptionFinalDate() === null;
	}

	public static function activate(): array
	{
		if (!self::isAvailable())
		{
			return self::getError(Loc::getMessage('REST_MARKET_ACTIVATE_DEMO_NOT_AVAILABLE'));
		}

		if (!CRestUtil::isAdmin())
		{
			return self::getError(Loc::getMessage('REST_MARKET_ACTIVATE_DEMO_ACCESS_DENIED'));
		}

		
		if (!OAuthService::getEngine()->isRegistered())
		{
			try
			{
				OAuthService::register();
			}
			catch (SystemException $e)
			{
				return self::getError(
					Loc::getMessage('REST_MARKET_CONFIG_ACTIVATE_ERROR'),
					$e->getMessage(),
					$e->getCode()
				);
			}
		}

		try
		{
			OAuthService::getEngine()->getClient()->getApplicationList();
		}
		catch (SystemException $e)
		{
			return self::getError(
				Loc::getMessage('REST_MARKET_CONFIG_ACTIVATE_ERROR'),
				$e->getMessage(),
				4
			);
		}


		if (!OAuthService::getEngine()->isRegistered())
		{
			return self::getError(
				Loc::getMessage('REST_MARKET_CONFIG_ACTIVATE_ERROR'),
				'',
				1
			);
		}

		$loadedBitrix24 = Loader::includeModule('bitrix24');
		$queryFields = $loadedBitrix24 ? self::getB24Fields() : self::getCPFields();

		if (empty($queryFields))
		{
			return [];
		}

		$httpClient = new HttpClient();
		$response = $httpClient->post('https://www.1c-bitrix.ru/buy_tmp/b24_coupon.php', $queryFields);
		if (!$response)
		{
			return [];
		}

		$result = [
			'result' => true,
		];
		if (mb_strpos($response, 'OK') === false)
		{
			$result = self::getError(
				Loc::getMessage('REST_MARKET_CONFIG_ACTIVATE_ERROR'),
				'',
				2
			);
		}

		if (!$loadedBitrix24)
		{
			require_once($_SERVER['DOCUMENT_ROOT']
				. '/bitrix/modules/main/classes/general/update_client.php');
			$errorMessage = '';
			\CUpdateClient::GetUpdatesList($errorMessage, LANG);
		}

		return $result;
	}

	private static function getB24Fields(): array
	{
		$server = Context::getCurrent()->getServer();

		$queryFields = [
			'DEMO' => 'subscription',
			'SITE' => (defined('BX24_HOST_NAME')) ? BX24_HOST_NAME : $server->getHttpHost(),
		];

		if (function_exists('bx_sign'))
		{
			$queryFields['hash'] = bx_sign(md5(implode('|', $queryFields)));
		}

		return $queryFields;
	}

	private static function getCPFields(): array
	{
		$queryFields = [];

		$LicenseKeyHash = \Bitrix\Main\Application::getInstance()->getLicense()->getHashLicenseKey();

		if (!is_null($LicenseKeyHash))
		{
			$queryFields = [
				'DEMO' => 'subscription',
				'SITE' => 'cp',
				'key' => $LicenseKeyHash,
				'hash' => md5('cp' . '|' . 'subscription' . '|' . $LicenseKeyHash),
			];
		}

		return $queryFields;
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