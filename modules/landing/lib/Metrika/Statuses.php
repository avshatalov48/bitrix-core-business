<?php
declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

enum Statuses: string
{
	case Success = 'success';
	case Error = 'error';
	case ErrorContentPolicy = 'error_content_policy';
	case ErrorB24 = 'error_b24';
	case ErrorProvider = 'error_provider';
	case ErrorLimitDaily = 'error_limit_daily';
	case ErrorLimitMonthly = 'error_limit_monthly';
	case ErrorLimitBaas = 'error_limit_baas';
	case ErrorMarket = 'error_market';
	case ErrorTurnedOff = 'error_turnedoff';
	case UnsupportedBlock = 'unsupported_block';
}
