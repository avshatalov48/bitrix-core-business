<?php

namespace Bitrix\Main\Engine\View\Exception;

use Bitrix\Main\SystemException;

final class FileNotExistsException extends SystemException
{
	public function __construct(string $path)
	{
		parent::__construct(
			"File `{$path}` not exists"
		);
	}
}
