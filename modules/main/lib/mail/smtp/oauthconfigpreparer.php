<?php

namespace Bitrix\Main\Mail\Smtp;

use Bitrix\Mail\Helper\OAuth;
use Bitrix\Main\Config\Option;

class OAuthConfigPreparer
{
	public function prepareBeforeSendIfNeed(Config $config): ?Config
	{
		if (!$config->getIsOauth())
		{
			return $config;
		}

		if (!\CModule::includeModule('mail'))
		{
			return null;
		}

		$expireGapSeconds = self::getOAuthTokenExpireGapSeconds();
		$mailOAuth = OAuth::getInstanceByMeta($config->getPassword());
		if ($mailOAuth)
		{
			$token = $mailOAuth->getStoredToken(null, $expireGapSeconds);
			if ($token)
			{
				// method should be used after retrieve token
				self::setCloudOAuthRefreshDataToConfig($config, $mailOAuth);
			}
		}
		else
		{
			// fallback for old meta version
			$token = OAuth::getTokenByMeta($config->getPassword(), $expireGapSeconds);
		}

		if (empty($token))
		{
			return null;
		}

		$config->setPassword($token);

		return $config;
	}

	public function getOAuthTokenExpireGapSeconds(): int
	{
		// we use 55 minutes because providers give tokens for 1 hour or more,
		// 5 minutes is left for not refresh token too frequent, for mass send
		$default = isModuleInstalled('bitrix24') ? 55 * 60 : 10;

		return (int)Option::get('main', '~oauth_token_expire_gap_seconds', $default);
	}

	private function setCloudOAuthRefreshDataToConfig(Config $config, OAuth $mailOAuth): void
	{
		if (!isModuleInstalled('bitrix24'))
		{
			return;
		}

		if (Option::get('main', '~cloud_refresh_enabled', 'Y') === 'N')
		{
			return;
		}

		$oauthEntity = $mailOAuth->getOAuthEntity();
		if (!is_object($oauthEntity) || !method_exists($oauthEntity, 'getTokenData'))
		{
			return;
		}

		$tokenData = $oauthEntity->getTokenData();
		if (!is_array($tokenData) || empty($tokenData['refresh_token']) || empty($tokenData['expires_in']))
		{
			return;
		}

		$expires = self::isTestCloudTokenExpiredMode() ? 0 : (int)$tokenData['expires_in'];
		$config->setCloudOAuthRefreshData(new CloudOAuthRefreshData($config->getPassword(), $expires));
	}

	private function isTestCloudTokenExpiredMode(): bool
	{
		return Option::get('main', '~oauth_expired_refresh', 'N') === 'Y';
	}
}
