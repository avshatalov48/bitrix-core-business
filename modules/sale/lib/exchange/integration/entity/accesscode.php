<?php


namespace Bitrix\Sale\Exchange\Integration\Entity;

use Bitrix\Sale\Exchange\Integration\OAuth;

class AccessCode
{
	protected $oauthClient;

	public function __construct(OAuth\Client $oauthClient)
	{
		$this->oauthClient = $oauthClient;
	}

	public function create(array $fields)
	{
		return $this->oauthClient->getAccessToken(
			"refresh_token",
			["refresh_token" => $fields['refreshToken']]
		);
	}
}