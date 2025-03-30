<?php

declare(strict_types=1);

namespace Bitrix\Rest\Enum\Integration;

enum ElementCodeType: string
{
	case IN_WEBHOOK = 'in-hook';
	case OUT_WEBHOOK = 'out-hook';
	case APPLICATION = 'application';
}
