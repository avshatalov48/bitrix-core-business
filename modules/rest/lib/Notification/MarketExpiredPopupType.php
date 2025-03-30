<?php

namespace Bitrix\Rest\Notification;

enum MarketExpiredPopupType: string
{
	case WARNING = 'WARNING';
	case FINAL = 'FINAL';
}
