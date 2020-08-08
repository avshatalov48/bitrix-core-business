<?php
namespace Bitrix\Sale\Exchange\Integration\OAuth;


use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;

class Client
{
	private $httpClient;
	protected $accessTokenUrl;
	protected $clientId;
	protected $clientSecret;

	public function __construct(array $options = [])
	{
		foreach ($options as $option => $value)
		{
			if (property_exists(self::class, $option))
			{
				$this->{$option} = $value;
			}
		}

		$this->httpClient = new HttpClient();
		$this->httpClient->setHeader("User-Agent", "Bitrix IntegrationB24");
		$this->httpClient->setCharset("UTF-8");
	}

	public function getAccessTokenUrl(array $params)
	{
		$url = $this->getBaseAccessTokenUrl();
		if ($this->getAccessTokenMethod() === HttpClient::HTTP_GET)
		{
			$query = http_build_query($params, null, "&");
			return $url."?".$query;
		}

		return $url;
	}

	/**
	 * @param $grant
	 * @param array $options
	 *
	 * @return array
	 */
	public function getAccessToken($grant, array $options = [])
	{
		$params = [
			"grant_type" => $grant,
			"client_id" => $this->getClientId(),
			"client_secret" => $this->getClientSecret(),
		];

		$params = array_merge($params, $options);
		$success = $this->httpClient->query($this->getAccessTokenMethod(), $this->getAccessTokenUrl($params));

		$response = [];
		if ($success)
		{
			try
			{
				$response = Json::decode($this->httpClient->getResult());
			}
			catch (\Bitrix\Main\ArgumentException $exception)
			{

			}
		}

		return $response;
	}

	protected function getAccessTokenMethod()
	{
		return HttpClient::HTTP_GET;
	}

	protected function getBaseAccessTokenUrl()
	{
		return $this->accessTokenUrl;
	}

	protected function getClientId()
	{
		return $this->clientId;
	}

	protected function getClientSecret()
	{
		return $this->clientSecret;
	}
}