<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI\Type;

enum LimitType: string
{
	case None = 'none';
	case Baas = 'baas';
	case Promo = 'promo';
	case Daily = 'daily';
	case Monthly = 'monthly';
	case Rate = 'rate';
	case Market = 'market';
	case Unregistered = 'unregistered';
}
