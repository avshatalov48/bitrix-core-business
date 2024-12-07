<?php

namespace Bitrix\Calendar\Event\Enum;

enum PushCommandEnum
{
	/** @deprecated */
	case OPEN_EVENT_ATTENDEE_STATUS;
	case OPEN_EVENT_CREATED;
	case OPEN_EVENT_UPDATED;
	case OPEN_EVENT_DELETED;
	case OPEN_EVENT_SCORER_UPDATED;
}
