<?php

declare(strict_types=1);

namespace Bitrix\Rest\Public\Provider\OAuth;

use Bitrix\Rest\Internal\Service\OAuth\AuthorizationServerUrlResolver;
use Bitrix\Rest\Internal\Integration\Main\License;

class AuthorizationServerProvider
{
	public function getCurrentAuthorizationUrl(): string
	{
		$region = (new License())->getRegion();

		return (new AuthorizationServerUrlResolver())->resolveByRegion($region);
	}
}
