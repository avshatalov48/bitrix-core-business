<?php

namespace Bitrix\Main\DI\Exception;

use Bitrix\Main\ObjectNotFoundException;
use Psr\Container\ContainerExceptionInterface;

class ServiceNotFoundException extends ObjectNotFoundException implements ContainerExceptionInterface
{
}
