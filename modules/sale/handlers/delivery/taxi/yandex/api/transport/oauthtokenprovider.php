<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport;

/**
 * Class OauthTokenProvider
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\Transport
 */
class OauthTokenProvider
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
