<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

require_once(__DIR__."/../include/prolog_admin_before.php");
if(!$USER->CanDoOperation('edit_php'))
{
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}
else
{
	phpinfo();
}
?>