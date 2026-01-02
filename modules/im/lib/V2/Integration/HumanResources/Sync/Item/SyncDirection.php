<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Sync\Item;

enum SyncDirection: string
{
	case ADD = 'ADD';
	case DELETE = 'DELETE';
	case NODE_DELETE = 'NODE_DELETE';
}
