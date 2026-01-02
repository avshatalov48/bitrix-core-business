<?php declare(strict_types=1);

namespace Bitrix\UI\Counter;

enum CounterSize: string
{
	case SMALL = 'ui-counter-sm';
	case LARGE = 'ui-counter-lg';
	case MEDIUM = 'ui-counter-md';
}
