<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("MAIN_ADMIN_SECTION_TITLE"));
include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
?>