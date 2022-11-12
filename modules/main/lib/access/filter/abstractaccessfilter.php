<?php

namespace Bitrix\Main\Access\Filter;

use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\User\AccessibleUser;

abstract class AbstractAccessFilter implements AccessFilter
{
	protected AccessibleController $controller;
	protected AccessibleUser $user;

	/**
	 * @param AccessibleController $controller
	 */
	public function __construct(AccessibleController $controller)
	{
		$this->controller = $controller;
		$this->user = $controller->getUser();
	}

	/**
	 * @inheritDoc
	 */
	abstract public function getFilter(string $entity, array $params = []): array;
}
