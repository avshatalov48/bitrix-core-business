<?php

namespace Sale\Handlers\Delivery\YandexTaxi\Api\Transport;

/**
 * Class OauthTokenProvider
 * @package Sale\Handlers\Delivery\YandexTaxi\Api\Transport
 * @internal
 */
final class OauthTokenProvider
{
	/** @var string */
	private $token;

	/**
	 * @param string $token
	 * @return OauthTokenProvider
	 */
	public function setToken(string $token): OauthTokenProvider
	{
		$this->token = $token;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getToken()
	{
		return $this->token;
	}
}
