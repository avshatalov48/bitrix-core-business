<?php

namespace Bitrix\Sale\Helpers\Rest;

use Bitrix\Main;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\Json;
use Bitrix\Sale;

/**
 * Class Http
 * @package Bitrix\Sale\Helpers\Rest
 */
class Http
{
	/**
	 * @param string $url
	 * @param array $params
	 * @param array $options
	 * @return Sale\Result
	 */
	public static function sendRequest(string $url, array $params, array $options = []): Sale\Result
	{
		$result = new Sale\Result();

		$httpClientOptions = [];
		if (array_key_exists('HTTP_CLIENT_OPTIONS', $options) && is_array($options['HTTP_CLIENT_OPTIONS']))
		{
			$httpClientOptions = $options['HTTP_CLIENT_OPTIONS'];
		}

		$httpClient = new HttpClient($httpClientOptions);

		$isJsonRequest = isset($options['JSON_REQUEST']) && $options['JSON_REQUEST'] === true;

		if ($isJsonRequest)
		{
			$httpClient->setHeader('Content-Type', 'application/json');
		}

		$response = $httpClient->post(
			$url,
			$isJsonRequest ? Json::encode($params) : $params
		);
		if ($response === false)
		{
			$errors = $httpClient->getError();
			foreach ($errors as $code => $message)
			{
				$result->addError(new Main\Error($message, $code));
			}

			return $result;
		}

		$httpStatus = $httpClient->getStatus();
		if ($httpStatus === 200)
		{
			try
			{
				$response = Json::decode($response);
				$response = array_change_key_case($response, CASE_UPPER);
				$response = Main\Text\Encoding::convertEncoding($response, 'UTF-8', LANG_CHARSET);
			}
			catch (Main\ArgumentException $exception)
			{
				$response = [];
				$result->addError(
					new Main\Error('Response decoding error', 'RESPONSE_DECODING_ERROR')
				);
			}

			$result->setData($response);
		}

		return $result;
	}
}