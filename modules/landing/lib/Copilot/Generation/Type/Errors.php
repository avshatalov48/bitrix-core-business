<?php
declare(strict_types=1);

namespace Bitrix\Landing\Copilot\Generation\Type;

enum Errors: string
{
	case requestEmpty = 'REQUEST_EMPTY';
	case requestFail = 'REQUEST_FAIL';
	case requestError = 'REQUEST_ERROR';
	case requestInvalid = 'REQUEST_INVALID';
	case requestNotAllowed = 'REQUEST_NOT_ALLOWED';
	case requestTimeout = 'REQUEST_TIMEOUT';
}
