<?php

namespace Bitrix\Bizproc\Debugger\Session;

class DocumentStatus
{
	public const INTERCEPTED = 0;
	public const REMOVED = 1;
	public const IN_DEBUG = 2;
	public const FINISHED = 3;

	public static function isStatus($status): bool
	{
		$ref =  new \ReflectionClass(__CLASS__);
		$constants = array_flip($ref->getConstants());

		return isset($constants[$status]);
	}
}
