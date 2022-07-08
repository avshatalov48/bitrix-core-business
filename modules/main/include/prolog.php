<?php
require_once(__DIR__ . "/../bx_root.php");

if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/html_pages/.enabled"))
{
	require_once(__DIR__ . "/../lib/composite/responder.php");
	Bitrix\Main\Composite\Responder::respond();
}

require_once(__DIR__ . "/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
