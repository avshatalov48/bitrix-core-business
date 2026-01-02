<?php

namespace Bitrix\Main\Engine\View\Exception;

use Bitrix\Main\SystemException;

final class InvalidPathOnViewException extends SystemException
{
	public function __construct(string $path)
	{
		parent::__construct(
			"Invalid path to view file: {$path}"
		);
	}
}
