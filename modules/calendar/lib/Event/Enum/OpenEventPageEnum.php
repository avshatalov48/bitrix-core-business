<?php

namespace Bitrix\Calendar\Event\Enum;

enum OpenEventPageEnum: string
{
	case CATEGORIES = 'categories';
	case EVENTS = 'events';
	case MY_EVENTS = 'my_events';
	case ONLY_WITH_COMMENTS = 'only_with_comments';
	case NEW_EVENTS = 'new_events';
}
