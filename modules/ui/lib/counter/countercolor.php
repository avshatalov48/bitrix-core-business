<?php declare(strict_types=1);

namespace Bitrix\UI\Counter;

enum CounterColor: string
{
	case DANGER = 'ui-counter-danger';
	case WARNING = 'ui-counter-warning';
	case SUCCESS = 'ui-counter-success';
	case PRIMARY = 'ui-counter-primary';
	case GRAY = 'ui-counter-gray';
	case LIGHT = 'ui-counter-light';
	case WHITE = 'ui-counter-white';
	case DARK = 'ui-counter-dark';
	case THEME = 'ui-counter-theme';
}
