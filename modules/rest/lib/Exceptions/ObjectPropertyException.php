<?php

declare(strict_types=1);

namespace Bitrix\Rest\Exceptions;

final class ObjectPropertyException extends ArgumentException
{
	public function __construct(string $parameter = '', \Exception $previous = null)
	{
		parent::__construct("Object property \"{$parameter}\" not found.", $parameter, $previous);
	}
}