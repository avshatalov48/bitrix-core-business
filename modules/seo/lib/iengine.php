<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage seo
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Seo;

interface IEngine
{
	public function getCode();

	public function getInterface();

	public function getAuthSettings();

	public function setAuthSettings($settings);
}