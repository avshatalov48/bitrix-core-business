<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI\Type;

enum MessageCode: string
{
	case Baas = 'LANDING_REQUEST_LIMITER_ERROR_BAAS';
	case Promo = 'LANDING_REQUEST_LIMITER_ERROR_PROMO';
	case Daily = 'LANDING_REQUEST_LIMITER_ERROR_DAILY';
	case Monthly = 'LANDING_REQUEST_LIMITER_ERROR_MONTHLY';
	case Rate = 'LANDING_REQUEST_LIMITER_ERROR_RATE';
	case CloudRegistration = 'LANDING_REQUEST_LIMITER_ERROR_CLOUD_REGISTRATION';
	case Market = 'LANDING_REQUEST_LIMITER_ERROR_MARKET';
	case BaasRateLimit = 'LANDING_REQUEST_LIMITER_ERROR_BAAS_RATE_LIMIT';
}
