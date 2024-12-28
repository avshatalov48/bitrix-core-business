<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Link;

enum LinkType
{
	case Tasks;
	case Calendar;
	case Disk;
}