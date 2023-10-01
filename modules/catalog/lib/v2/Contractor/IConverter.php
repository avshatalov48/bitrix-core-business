<?php

namespace Bitrix\Catalog\v2\Contractor;

interface IConverter
{
	/**
	 * @return bool
	 */
	public static function isMigrated(): bool;

	public static function runMigration(): void;

	public static function showMigrationProgress(): void;
}