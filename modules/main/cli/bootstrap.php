<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true);
define("BX_NO_ACCELERATOR_RESET", true);
define("BX_CRONTAB", true);
define("STOP_STATISTICS", true);
define("NO_AGENT_STATISTIC", "Y");
define("DisableEventsCheck", true);
define("NO_AGENT_CHECK", true);

if (empty($_SERVER["DOCUMENT_ROOT"]))
{
	$_SERVER["DOCUMENT_ROOT"] = realpath(__DIR__.'/../../../../');
}

$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

require_once(__DIR__."/../include/prolog_before.php");
@session_destroy();