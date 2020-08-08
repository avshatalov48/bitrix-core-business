<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

return array(
	'css' => array(
		'/bitrix/js/mail/selector/mail.selector.css',
		'/bitrix/js/mail/selector/callback.css'
	),
	'js' => array(
		'/bitrix/js/mail/selector/mail.selector.js'
	),
	'rel' => array('ui.selector')
);