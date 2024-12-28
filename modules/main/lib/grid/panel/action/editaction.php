<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\HttpRequest;

abstract class EditAction implements Action
{
	public static function getId(): string
	{
		return 'edit';
	}

	public function getControl(): ?array
	{
		return (new Snippet)->getEditButton();
	}

	/**
	 * @param HttpRequest $request
	 *
	 * @return array[]
	 */
	protected function getRequestRows(HttpRequest $request): array
	{
		return (array)$request->get('FIELDS');
	}
}
