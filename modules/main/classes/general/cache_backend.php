<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

/**
 * @deprecated
 */
interface ICacheBackend
{
	function clean($basedir, $initdir = false, $filename = false);
	function read(&$arAllVars, $basedir, $initdir, $filename, $TTL);
	function write($arAllVars, $basedir, $initdir, $filename, $TTL);
}
