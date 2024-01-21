<?php

namespace Bitrix\Main\Grid\Panel\Action;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;

final class ForAllCheckboxAction implements Action
{
	public static function getId(): string
	{
		return 'for_all_checkbox';
	}

	public function getControl(): ?array
	{
		return (new Snippet)->getForAllCheckbox();
	}

	public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result
	{
		// pass
		return null;
	}
}
