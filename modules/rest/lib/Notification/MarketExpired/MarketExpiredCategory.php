<?php

namespace Bitrix\Rest\Notification\MarketExpired;

enum MarketExpiredCategory: string
{
	case TRANSITION = 'transition';
	case SUBSCRIPTION = 'subscription';
	case TRIAL = 'trial';
}
