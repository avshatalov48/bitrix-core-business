<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Control\Enum;

enum ViewMode: string
{
	case OPEN = 'open';
	case SECRET = 'secret';
}