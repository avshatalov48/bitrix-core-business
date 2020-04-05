<?
require_once(dirname(__FILE__)."/../bx_root.php");

if (file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/html_pages/.enabled"))
{
	require_once(dirname(__FILE__)."/../lib/composite/responder.php");
	Bitrix\Main\Composite\Responder::respond();
}

require_once(dirname(__FILE__)."/prolog_before.php");
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
