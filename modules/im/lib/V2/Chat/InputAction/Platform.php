<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Chat\InputAction;

enum Platform: string
{
    case WEB = 'web';
    case MOBILE = 'mobile';
}
