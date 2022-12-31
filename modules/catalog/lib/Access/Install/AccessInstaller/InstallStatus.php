<?php

namespace Bitrix\Catalog\Access\Install\AccessInstaller;

use Bitrix\Main\Config\Option;

/**
 * Installer status
 *
 * Used to monitor the current state of the installer.
 */
class InstallStatus
{
	private const OPTION_NAME = 'catalog_access_install_in_progress';

	/**
	 * The installer is still working.
	 *
	 * @return bool
	 */
	public static function inProgress(): bool
	{
		return Option::get('catalog', self::OPTION_NAME) === 'Y';
	}

	/**
	 * Mark installation as started.
	 *
	 * @return void
	 */
	public static function start(): void
	{
		if (self::inProgress())
		{
			return;
		}

		Option::set('catalog', self::OPTION_NAME, 'Y');
	}

	/**
	 * Mark installation as finished.
	 *
	 * @return void
	 */
	public static function finish(): void
	{
		Option::delete('catalog', [
			'name' => self::OPTION_NAME,
		]);
	}
}
