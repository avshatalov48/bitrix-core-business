<?php declare(strict_types=1);

namespace Bitrix\UI\Counter;

enum CounterStyle: string
{
	case FILLED_EXTRA = '--style-filled-extra';
	case FILLED = '--style-filled';
	case FILLED_INVERTED = '--style-filled-inverted';
	case FILLED_ALERT = '--style-filled-alert';
	case FILLED_ALERT_INVERTED = '--style-filled-alert-inverted';
	case FILLED_WARNING = '--style-filled-warning';
	case FILLED_SUCCESS = '--style-filled-success';
	case FILLED_SUCCESS_INVERTED = '--style-filled-success-inverted';
	case FILLED_NO_ACCENT = '--style-filled-no-accent';
	case FILLED_NO_ACCENT_INVERTED = '--style-filled-no-accent-inverted';
	case TINTED_NO_ACCENT = '--style-tinted-no-accent';
	case OUTLINE_NO_ACCENT = '--style-outline-no-accent';
}
