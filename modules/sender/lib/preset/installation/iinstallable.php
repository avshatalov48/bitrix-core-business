<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Preset\Installation;

/**
 * Interface iInstallable
 * @package Bitrix\Sender\Preset\Installation
 */
interface iInstallable
{
	const EVENT_NAME = 'onSenderPresetList';

	/**
	 * Get installable ID.
	 *
	 * @return string
	 */
	public function getId();

	/**
	 * Return true if it is installed.
	 *
	 * @return bool
	 */
	public function isInstalled();

	/**
	 * Install.
	 *
	 * @return bool
	 */
	public function install();

	/**
	 * Uninstall.
	 *
	 * @return bool
	 */
	public function uninstall();
}