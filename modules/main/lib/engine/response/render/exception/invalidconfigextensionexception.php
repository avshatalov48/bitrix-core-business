<?php

namespace Bitrix\Main\Engine\Response\Render\Exception;

final class InvalidConfigExtensionException extends RenderException
{
	public function __construct(string $extension, string $reason)
	{
		parent::__construct(
			"Invalid config format for extension `{$extension}`: {$reason}",
		);
	}
}
