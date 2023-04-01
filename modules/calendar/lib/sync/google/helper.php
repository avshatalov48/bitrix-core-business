<?php


namespace Bitrix\Calendar\Sync\Google;


use Bitrix\Main\Loader;
use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Config\Option;
use DateTimeInterface;

class Helper
{
	public const DEFAULT_HTTPS_PORT = 443;
	public const GOOGLE_ACCOUNT_TYPE_CALDAV= 'caldav_google_oauth';
	public const GOOGLE_ACCOUNT_TYPE_API = 'google_api_oauth';
	public const HTTP_SCHEME_DEFAULT = 'https';
	public const HTTP_SCHEME_SEPARATOR = '://';
	public const GOOGLE_API_URL = 'www.googleapis.com';
	public const GOOGLE_CALDAV_URL = 'apidata.googleusercontent.com';
	public const GOOGLE_API_V3_URI = '/calendar/v3';
	public const GOOGLE_API_V2_URI = '/calendar/v2/';
	public const GOOGLE_SERVER_PATH_V3 = self::HTTP_SCHEME_DEFAULT . self::HTTP_SCHEME_SEPARATOR . self::GOOGLE_API_URL . self::GOOGLE_API_V3_URI;
	public const GOOGLE_SERVER_PATH_V2 = self::HTTP_SCHEME_DEFAULT . self::HTTP_SCHEME_SEPARATOR . self::GOOGLE_CALDAV_URL . self::GOOGLE_API_V2_URI;
	public const DATE_TIME_FORMAT = DateTimeInterface::ATOM;
	public const DATE_TIME_FORMAT_WITH_MICROSECONDS = 'Y-m-d\TH:i:s\.vP';
	public const DATE_TIME_FORMAT_RFC_3339 = 'Y-m-d\TH:i:s\Z';
	public const DATE_TIME_FORMAT_WITH_UTC_TIMEZONE = 'Ymd\THis\Z';
	public const EXCLUDED_DATE_TIME_FORMAT = self::DATE_TIME_FORMAT_WITH_UTC_TIMEZONE;
	public const EXCLUDED_DATE_FORMAT = 'Ymd';
	public const DATE_FORMAT = 'Y-m-d';
	public const VERSION_DIFFERENCE = 1;
	public const END_OF_TIME = "01.01.2038";

	/**
	 * @param $accountType
	 * @return bool
	 */
	public function isGoogleConnection($accountType): bool
	{
		return in_array($accountType, [self::GOOGLE_ACCOUNT_TYPE_CALDAV, self::GOOGLE_ACCOUNT_TYPE_API], true);
	}

	public function isDeletedResource($errorText): bool
	{
		return !empty($errorText) && preg_match("/^(\[410\] Resource has been deleted)/i", $errorText);
	}

	public function isNotFoundError(string $errorText = null): bool
	{
		return !empty($errorText) && preg_match("/^\[(404)\][a-z0-9 _]*/i", $errorText);
	}

	public function isNotValidSyncTokenError(string $errorText = null): bool
	{
		return !empty($errorText)
			&& (preg_match("/^(\[410\] The requested minimum modification time lies too far in the past.)/i", $errorText)
			|| preg_match("/^(\[410\] Sync token is no longer valid, a full sync is required.)/i", $errorText))
		;
	}

	public function isMissingRequiredAuthCredential(string $errorText = null): bool
	{
		return !empty($errorText)
			&& preg_match("/^\[401\] Request is missing required authentication credential.[a-z0-9 _]*/i", $errorText)
		;
	}


	/**
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ArgumentOutOfRangeException
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function getApiKey(): ?string
	{
		if (
			Loader::includeModule('socialservices')
			&& ($apiKey = Option::get('socialservices', 'google_api_key', null))
		)
		{
			return $apiKey;
		}

		if (Loader::includeModule('fileman'))
		{
			$apiKey = AddressType::getApiKey();
			if (!empty($apiKey))
			{
				return $apiKey;
			}
		}

		return Option::get('fileman', 'google_map_api_key', null)
			?? Option::get('bitrix24', 'google_map_api_key', null)
		;
	}
}
