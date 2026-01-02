<?php

namespace Bitrix\Bizproc\Infrastructure\Agent;

abstract class BaseAgent
{
	abstract public static function run();

	protected static function next(): string
	{
		return static::class . "::run();";
	}
}
