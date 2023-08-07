<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Grid\Panel\Snippet;

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
}
