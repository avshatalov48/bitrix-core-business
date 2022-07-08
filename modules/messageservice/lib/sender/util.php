<?php

namespace Bitrix\MessageService\Sender;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\HttpClient;

class Util
{
	public static function getHttpClientError(HttpClient $httpClient): ?Error
	{
		$err = $httpClient->getError();
		if (empty($err))
		{
			return null;
		}
		$code = array_key_first($err);

		return new Error($err[$code], $code);
	}

	public static function getHttpClientErrorString(HttpClient $httpClient): ?string
	{
		$err = $httpClient->getError();
		if (empty($err))
		{
			return null;
		}
		$code = array_key_first($err);

		return "[{$code}]: {$err[$code]}";
	}
}