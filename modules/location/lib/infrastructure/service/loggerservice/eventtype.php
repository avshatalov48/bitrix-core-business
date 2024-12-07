<?php

namespace Bitrix\Location\Infrastructure\Service\LoggerService;

class EventType
{
	public const SOURCE_GOOGLE_REQUESTER_URL = 1;
	public const SOURCE_GOOGLE_REQUESTER_RESULT = 2;
	public const SOURCE_GOOGLE_REQUESTER_CACHE = 3;
	public const SOURCE_GOOGLE_REQUESTER_OTHER = 0;
	public const SOURCE_OSM_TREQUESTER_TOKEN_ERROR = 4;
}
