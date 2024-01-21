<?php

namespace Bitrix\Main\Grid\Panel\Action\Group;

use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\Result;

abstract class GroupChildAction
{
	abstract public static function getId(): string;

	abstract public function getName(): string;

	abstract public function processRequest(HttpRequest $request, bool $isSelectedAllRows, ?Filter $filter): ?Result;

	abstract protected function getOnchange(): Onchange;

	public function getDropdownItem(): array
	{
		return [
			'VALUE' => static::getId(),
			'NAME' => $this->getName(),
			'ONCHANGE' => $this->getOnchange()->toArray(),
		];
	}

	protected function getRequestRows(HttpRequest $request): ?array
	{
		$ids = $request->getPost('rows');
		if (!is_array($ids))
		{
			return null;
		}

		return $ids;
	}
}
