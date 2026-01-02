<?php

namespace Bitrix\Bizproc\Starter;

use Bitrix\Bizproc\Starter\Enum\Face;
use Bitrix\Main\ModuleManager;

final class Context
{
	public readonly string $moduleId;
	public readonly Face $face;

	public function __construct(string $moduleId, Face $face)
	{
		$this->moduleId = $moduleId;
		$this->face = $face;
	}
}
