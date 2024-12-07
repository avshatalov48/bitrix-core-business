<?php

namespace Bitrix\Calendar\EventCategory\Enum;

enum PushCommandEnum
{
	case EVENT_CATEGORY_CREATED;
	case EVENT_CATEGORY_UPDATED;
	case EVENT_CATEGORY_DELETED;
}
