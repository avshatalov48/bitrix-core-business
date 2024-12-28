<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule\Enum;

enum Type: string
{
	case Integer = 'is_int';
	case String = 'is_string';
	case Float = 'is_float';
}
