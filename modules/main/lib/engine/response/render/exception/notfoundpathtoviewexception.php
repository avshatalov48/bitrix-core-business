<?php

namespace Bitrix\Main\Engine\Response\Render\Exception;

final class NotFoundPathToViewException extends RenderException
{
	/**
	 * @param string $pathOnDocumentRoot
	 */
	public function __construct(string $pathOnDocumentRoot)
	{
		parent::__construct(
			"Path to view `$pathOnDocumentRoot` not found on document root.",
		);
	}
}
