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
	 * @return Sale\Result
	 */
	public static function sendRequest(string $url, array $params): Sale\Result
	{
		$result = new Sale\Result();
		$httpClient = new HttpClient();

		$response = $httpClient->post($url, $params);
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