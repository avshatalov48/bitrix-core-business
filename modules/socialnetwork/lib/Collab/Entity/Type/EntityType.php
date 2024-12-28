<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Entity\Type;

enum EntityType: string
{
	case Task = 'TASKS_TASK';
	case TaskCheckList = 'TASKS_TASK_CHECKLIST';
	case CalendarEvent = 'CALENDAR_EVENT';
	case CalendarSection = 'CALENDAR_SECTION';
	case Comment = 'FORUM_MESSAGE';
	case File = 'DISK_FILE';
}
