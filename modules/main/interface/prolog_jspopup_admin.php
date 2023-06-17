<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

header('Content-Type: text/html; charset='.LANG_CHARSET);

if (isset($_REQUEST['suffix']) && $_REQUEST['suffix'] && !preg_match('/[^a-zA-Z0-9_]/is', $_REQUEST['suffix']))
{
	$obJSPopup = new CJSPopup($APPLICATION->GetTitle(false, true), array('SUFFIX' => $_REQUEST['suffix']));
}
else
{
	$obJSPopup = new CJSPopup($APPLICATION->GetTitle(false, true));
}

$obJSPopup->ShowTitlebar();
?>
<div id="bx_admin_form">
