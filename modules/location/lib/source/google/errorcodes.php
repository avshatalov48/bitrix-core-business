<?php

namespace Bitrix\Location\Source\Google;

final class ErrorCodes
{
	public const CONVERTER_BYQUERY_ERROR = 3001;
	public const CONVERTER_BYCOORDS_ERROR = 3002;
	public const CONVERTER_BYID_ERROR = 3003;

	public const REQUESTER_BASE_HTTP_ERROR = 3100;
	public const REQUESTER_BASE_JSON_ERROR = 3101;
	public const REQUESTER_BASE_STATUS_ERROR = 3102;

	public const FINDER_ERROR = 3200;

	public const REPOSITORY_FIND_API_KEY_ERROR = 3300;
}