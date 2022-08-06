<?php

namespace Bitrix\Bizproc\Debugger\Session;

class Mode
{
	public const EXPERIMENTAL = 0;
	public const INTERCEPTION = 1;

	public static function isMode($mode): bool
	{
		$ref =  new \ReflectionClass(__CLASS__);
		$constants = array_flip($ref->getConstants());

		return isset($constants[$mode]);
	}
}