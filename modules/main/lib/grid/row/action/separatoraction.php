<?php

namespace Bitrix\Main\Grid\Row\Action;

use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;

/**
 * Separator between another controls.
 */
final class SeparatorAction implements Action
{
	public static function getId(): ?string
	{
		return null;
	}

	public function processRequest(HttpRequest $request): ?Result
	{
		return null;
	}

	public function getControl(array $rawFields): array
	{
		return [
			'SEPARATOR' => true,
		];
	}
}
