<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2016 Bitrix
 */
namespace Bitrix\Sender\Preset\Installation;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class Installer
 * @package Bitrix\Sender\Preset\Installation
 */
class Installer
{
	protected $errors = array();
	protected static $version = 1;
	protected static $versionOptionName = 'sender_preset_version';

	/**
	 * Check version.
	 *
	 * @return bool
	 */
	public static function checkVersion()
	{
		return self::getVersion() > self::getInstalledVersion();
	}

	/**
	 * Return true if it has errors.
	 *
	 * @return iInstallable[]
	 */
	public static function getInstallable()
	{
		return Factory::getInstallable();
	}

	/**
	 * Install newest.
	 * @return bool
	 */
	public static function installNewest()
	{
		if (!self::checkVersion())
		{
			return false;
		}

		$instance = new self;
		return $instance->install();
	}

	/**
	 * Install.
	 * @return bool
	 */
	public function install()
	{
		if(!self::checkVersion())
		{
			return true;
		}

		foreach($this->getInstallable() as $installable)
		{
			if($installable->isInstalled())
			{
				continue;
			}

			$installable->install();
		}

		if(!$this->hasErrors())
		{
			self::updateInstalledVersion();
		}

		return $this->hasErrors();
	}

	/**
	 * Uninstall.
	 *
	 * @param iInstallable|null $installable Installable.
	 */
	public function uninstall(iInstallable $installable = null)
	{
		if($installable)
		{
			$installable->uninstall();
		}
		else
		{
			foreach($this->getInstallable() as $installable)
			{
				if(!$installable->isInstalled())
				{
					continue;
				}

				$installable->uninstall();
			}

			self::updateInstalledVersion(0);
		}
	}

	/**
	 * Update installed version.
	 *
	 * @param integer|null $version Version
	 */
	public static function updateInstalledVersion($version = null)
	{
		if($version === null)
		{
			$version = self::getVersion();
		}

		Option::set('sender', self::$versionOptionName, $version);
	}

	protected static function getVersion()
	{
		return self::$version;
	}

	protected static function getInstalledVersion()
	{
		return (int) Option::get('sender', self::$versionOptionName, 0);
	}

	/**
	 * Get errors.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Return true if it has errors.
	 *
	 * @return bool
	 */
	public function hasErrors()
	{
		return count($this->errors) > 0;
	}
}
