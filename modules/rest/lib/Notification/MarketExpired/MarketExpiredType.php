<?php

namespace Bitrix\Rest\Notification\MarketExpired;

enum MarketExpiredType: string
{
	case WARNING = 'WARNING';
	case FINAL = 'FINAL';
}
