<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

if (!defined("START_EXEC_PROLOG_AFTER_1"))
{
	define("START_EXEC_PROLOG_AFTER_1", microtime(true));
}

$GLOBALS["BX_STATE"] = "PA";

if (!$GLOBALS['USER']->IsAuthorized())
{
	die();
}

if (!defined("START_EXEC_PROLOG_AFTER_2"))
{
	define("START_EXEC_PROLOG_AFTER_2", microtime(true));
}

$GLOBALS["BX_STATE"] = "WA";
