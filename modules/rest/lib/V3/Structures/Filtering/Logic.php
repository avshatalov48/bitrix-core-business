<?php

namespace Bitrix\Rest\V3\Structures\Filtering;

enum Logic: string
{
	case And = 'and';

	case Or = 'or';
}
