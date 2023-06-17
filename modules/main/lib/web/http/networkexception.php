<?php

namespace Bitrix\Main\Web\Http;

use Psr\Http\Client\NetworkExceptionInterface;

class NetworkException extends ClientException implements NetworkExceptionInterface
{
}
