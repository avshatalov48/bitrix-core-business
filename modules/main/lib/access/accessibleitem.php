<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Access;


interface AccessibleItem
{
	public static function createFromId(int $itemId): AccessibleItem;

	public function getId(): int;

}