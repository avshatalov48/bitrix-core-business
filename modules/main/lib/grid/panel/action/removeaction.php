<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Grid\Panel\Snippet;

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
}
