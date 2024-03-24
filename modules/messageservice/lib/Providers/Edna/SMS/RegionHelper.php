<?php
namespace Bitrix\MessageService\Providers\Edna\SMS;

class RegionHelper extends \Bitrix\MessageService\Providers\Edna\RegionHelper
{
	public static function getApiEndPoint(): string
	{
		return self::isInternational() ? Constants::API_ENDPOINT_IO : Constants::API_ENDPOINT;
	}
}
