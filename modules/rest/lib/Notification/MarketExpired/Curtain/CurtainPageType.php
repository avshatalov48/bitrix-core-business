<?php

namespace Bitrix\Rest\Notification\MarketExpired\Curtain;

enum CurtainPageType: string
{
	case INTEGRATION = 'Integration';
	case APPLICATION = 'Application';
	case ANY_PAGE = 'AnyPage';
}
