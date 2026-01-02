<?php

namespace Bitrix\Main\Engine\View\Exception;

use Bitrix\Main\SystemException;

final class PathOutsideDocumentRootException extends SystemException
{
	public function __construct(string $path)
	{
		parent::__construct(
			"Path `{$path}` is outside the document root"
		);
	}
}


