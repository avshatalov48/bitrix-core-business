<?php

declare(strict_types=1);

namespace Bitrix\Rest\Internal\Service\OAuth;

class AuthorizationServerUrlResolver
{
	public function resolveByRegion(?string $region = null): string
	{
		$savedUrl = (string) \Bitrix\Main\Config\Option::get('rest', 'oauth_server', '');
		if (!empty($savedUrl))
		{
			return $savedUrl;
		}

		return match ($region)
		{
			'ru', 'am', 'az', 'ge', 'kz', 'kg', 'uz', 'by' => 'https://oauth.bitrix24.tech',
			default => 'https://oauth.bitrix.info',
		};
	}
}
