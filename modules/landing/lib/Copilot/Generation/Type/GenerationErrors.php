<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

enum GenerationErrors: int
{
	case default = 0;
	case notExistResponse = 10;
	case restrictedRequest = 20;
	case notFullyResponse = 30;
	case dataValidation = 40;
	case notCorrectResponse = 50;
	case requestQuotaExceeded = 60;
	case notSendRequest = 70;
	case errorInRequest = 80;
}
