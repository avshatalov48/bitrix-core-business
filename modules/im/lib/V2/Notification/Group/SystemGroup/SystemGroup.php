<?php

namespace Bitrix\Im\V2\Notification\Group\SystemGroup;

enum SystemGroup: string
{
	case Confirm = 'confirm';
	case Mention = 'mention';
	case Admin = 'admin';
}