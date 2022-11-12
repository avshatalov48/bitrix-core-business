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

interface RuleInterface
{
	public function __construct(AccessibleController $controller);

	public function execute(AccessibleItem $item = null, $params = null): bool;
}
