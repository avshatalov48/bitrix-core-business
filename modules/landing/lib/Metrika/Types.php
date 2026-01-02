<?php
declare(strict_types=1);

namespace Bitrix\Landing\Metrika;

enum Types: string
{
	case ai = 'ai';
	case template = 'template';
	case widgetSystem = 'system';
	case widgetPartner = 'partner';
	case widgetLocal = 'local';
}
