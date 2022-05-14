<?php


namespace Bitrix\Calendar\Sync\Google;


use Bitrix\Main\Loader;
use Bitrix\Fileman\UserField\Types\AddressType;
use Bitrix\Main\Config\Option;

class Helper
{
	public const GOOGLE_ACCOUNT_TYPE_CALDAV= 'caldav_google_oauth';
	public const GOOGLE_ACCOUNT_TYPE_API = 'google_api_oauth';
	public const GOOGLE_SERVER_PATH_V3 = 'https://www.googleapis.com/calendar/v3';
	public const GOOGLE_SERVER_PATH_V2 = 'https://apidata.googleusercontent.com/caldav/v2/';

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