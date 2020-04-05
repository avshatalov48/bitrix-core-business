<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
{
	die();
}

$context = \Bitrix\Main\Application::getInstance()->getContext();
$request = $context->getRequest();

// iframe footer
if ($request->get('IFRAME') == 'Y')
{
	include 'slider_footer.php';
	\CMain::finalActions();
	die();
}
// ajax
elseif ($request->get('IS_AJAX') == 'Y')
{
	\CMain::finalActions();
	die();
}