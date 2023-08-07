<?php

require_once(__DIR__."/../bx_root.php");

if (!defined('START_EXEC_PROLOG_BEFORE_1'))
{
	define("START_EXEC_PROLOG_BEFORE_1", microtime(true));
}

$GLOBALS["BX_STATE"] = "PB";
unset($_REQUEST["BX_STATE"]);
unset($_GET["BX_STATE"]);
unset($_POST["BX_STATE"]);
unset($_COOKIE["BX_STATE"]);
unset($_FILES["BX_STATE"]);

define("NEED_AUTH", true);

if (isset($_REQUEST['bxpublic']) && $_REQUEST['bxpublic'] == 'Y' && !defined('BX_PUBLIC_MODE'))
	define('BX_PUBLIC_MODE', 1);

if (isset($_REQUEST['public']) && $_REQUEST['public'] == 'Y' && !defined("PUBLIC_MODE"))
{
	define("PUBLIC_MODE", 1);
	if(!defined('BX_PUBLIC_MODE'))
	{
		define('BX_PUBLIC_MODE', 1);
	}
}

if (!defined('PUBLIC_MODE') || PUBLIC_MODE !== 1)
{
	if (!defined('ADMIN_SECTION'))
	{
		define("ADMIN_SECTION", true);
	}
}

require_once(__DIR__."/../include.php");
if(!headers_sent())
	header("Content-type: text/html; charset=".LANG_CHARSET);

if (defined('BX_PUBLIC_MODE') && BX_PUBLIC_MODE == 1)
{
	if ($_SERVER['REQUEST_METHOD'] == 'POST')
		require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_jspopup.php");
}

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/admin_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/interface/init_admin.php");

CMain::PrologActions();

if (!defined('START_EXEC_PROLOG_BEFORE_2'))
{
	define("START_EXEC_PROLOG_BEFORE_2", microtime(true));
}
