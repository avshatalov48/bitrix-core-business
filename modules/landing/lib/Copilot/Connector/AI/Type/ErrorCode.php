<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI\Type;

enum ErrorCode: string
{
	/** @see \Bitrix\AI\Engine::ERRORS (key 'LIMIT_IS_EXCEEDED') */
	case LimitCloud = 'LIMIT_IS_EXCEEDED';
	/** @see \Bitrix\AI\Engine\Cloud\CloudEngine::ERROR_CODE_LIMIT_BAAS */
	case LimitBaasCloud = 'LIMIT_IS_EXCEEDED_BAAS';
	case RateLimit = 'RATE_LIMIT';
	case Daily = 'LIMIT_IS_EXCEEDED_DAILY';
	case Monthly = 'LIMIT_IS_EXCEEDED_MONTHLY';
	case BaasRateLimit = 'LIMIT_IS_EXCEEDED_BAAS_RATE_LIMIT';
}
