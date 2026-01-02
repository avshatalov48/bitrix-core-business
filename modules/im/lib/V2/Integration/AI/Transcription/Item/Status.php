<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Integration\AI\Transcription\Item;

enum Status: string
{
	case Error = 'Error';
	case Success = 'Success';
	case Pending = 'Pending';
}
