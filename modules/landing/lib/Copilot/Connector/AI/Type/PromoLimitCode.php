<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI\Type;

enum PromoLimitCode: string
{
	case Daily = 'Daily';
	case Monthly = 'Monthly';
}
