<?php

declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Connector\AI\Type;

enum SliderCode: string
{
	case BoostCopilot = 'limit_boost_copilot';
	case Daily = 'limit_copilot_max_number_daily_requests';
	case Monthly = 'limit_copilot_requests';
	case BoostCopilotBox = 'limit_boost_copilot_box';
	case RequestBox = 'limit_copilot_requests_box';
	case Box = 'limit_copilot_box';
	case Market = 'limit_subscription_market_access_buy_marketplus';
}
