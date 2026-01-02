<?php

namespace Bitrix\Main\DI\Exception;

use Bitrix\Main\SystemException;
use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends SystemException implements ContainerExceptionInterface
{
}
