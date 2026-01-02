<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Promotion\Internals;

enum UserType: string
{
	case OLD = 'OLD';
	case NEW = 'NEW';
	case ALL = 'ALL';
}
