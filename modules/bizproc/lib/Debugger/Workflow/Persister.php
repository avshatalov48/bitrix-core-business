<?php

namespace Bitrix\Bizproc\Debugger\Workflow;

class Persister extends \CBPWorkflowPersister
{
	private static $debugInstance = null;

	public static function getPersister(): self
	{
		if (!isset(static::$debugInstance))
		{
			static::$debugInstance = new static();
		}

		return static::$debugInstance;
	}
}