<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\Item;

enum Status: string
{
	case DEFAULT = '';
	case NEW = 'NEW';
	case ERROR = 'ERROR';
}
