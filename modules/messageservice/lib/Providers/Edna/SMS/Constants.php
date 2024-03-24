<?php

namespace Bitrix\MessageService\Providers\Edna\SMS;

use Bitrix\MessageService\Providers\Constants\InternalOption;

class Constants extends InternalOption
{
	public const ID = 'smsednaru';

	public const API_ENDPOINT = 'https://app.edna.ru/api/';
	public const API_ENDPOINT_IO = 'https://app.edna.io/api/';
	public const API_KEY = 'apiKey';
}
