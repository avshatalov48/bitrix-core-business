<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\HttpRequest;

abstract class RemoveAction implements Action
{
	public static function getId(): string
	{
		return 'remove';
	}

	public function getControl(): ?array
	{
		return (new Snippet)->getRemoveButton();
	}

	protected function getRequestRows(HttpRequest $request): ?array
	{
		$ids = $request->getPost('ID');
		if (!is_array($ids))
		{
			return null;
		}

		return $ids;
	}
}
