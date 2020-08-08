<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access\Rule;

use Bitrix\Main\Access\AccessibleController;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\User\AccessibleUser;

abstract class AbstractRule implements RuleInterface
{
	/* @var AccessibleController $controller */
	protected $controller;

	/* @var AccessibleUser $user */
	protected $user;

	public function __construct(AccessibleController $controller)
	{
		$this->controller = $controller;
		$this->user = $controller->getUser();
	}

	abstract public function execute(AccessibleItem $item = null, $params = null): bool;
}