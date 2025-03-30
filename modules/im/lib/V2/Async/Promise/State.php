<?php

namespace Bitrix\Im\V2\Async\Promise;

enum State
{
	case Pending;
	case Fulfilled;
	case Rejected;
}
