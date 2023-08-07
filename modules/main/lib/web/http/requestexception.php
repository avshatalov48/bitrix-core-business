<?php

namespace Bitrix\Main\Web\Http;

use Psr\Http\Client\RequestExceptionInterface;

class RequestException extends ClientException implements RequestExceptionInterface
{
}
